<?php

/* Main Api Controller */

namespace App\Controller;
header("Access-Control-Allow-Origin: *");

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

use App\Entity\Transactions;
use App\Entity\Licence;
use App\Entity\User;
use App\Entity\Shop;
use App\Form\RegistrationFormType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use \Ovh\Api;
class ApiClientController extends AbstractController
{

    protected $return;
    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $mailer;

    public function __construct(\Swift_Mailer $mailer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer=$mailer;
    }

    /**
     * @Route("/api/client/{token}/get/", name="client_api")
     * @param $token
     * @return JsonResponse
     */

    // function return les infos de la page accueil points - magasin ...
    public function index($token)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($token);
        if ($user) {
            $manager = $this->getDoctrine()->getManager();

            $transactions_repo = $manager->getRepository(Transactions::class);
            $params = [];
            $params['nbr_points'] = 0;
            $params['nb_shops'] = 0;
            $params['nb_transactions'] = 0;
            $params['transactions'] = [];

            $transactions = $user->getTransactions();
            //$transactions_repo->findBy(['user_id'=> $user],array(),3,0);
            foreach ($transactions as $transaction) {
                $tran_tep = array(
                    'id' => $transaction->getId(),
                    'shop' => $transaction->getShop()->getName(),
                    'points' => $transaction->getPoints(),
                    'amount' => $transaction->getAmount(),
                    'date' => $transaction->getDate(),
                );
                $params['transactions'][] = $tran_tep;
            }
            $params['nb_transactions'] = count($transactions);
            $temps_shops_ids = [];
            try {
                $em = $this->getDoctrine()->getManager();
                $RAW_QUERY = 'select * from shop_user where user_id = :user_id;';
                $statement = $em->getConnection()->prepare($RAW_QUERY);
                // Set parameters

                $statement->bindValue('user_id', $user->getId());
                $statement->execute();
                $points = $statement->fetchAll();
                $params['nbr_points'] = $points[0]["points"] - $points[0]["used_points"];

            } catch (\Exception $e) {
            }

            foreach ($transactions as $tr) {
                $curid = $tr->getShop()->getId();
                if (!in_array($curid, $temps_shops_ids)) array_push($temps_shops_ids, $curid);
//				$params['nb_points'] += intval($tr->getPoints());
            }
            $params['nb_shops'] = count($temps_shops_ids);
            $params = json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $response = new Response($params);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
            return new JsonResponse(['data' => $params]);
        } else
            return new JsonResponse(['data' => "invalid_token"]);
    }

    /**
     * @Route("/api/client/checkauth/{token}", name="client_remember")
     * @param Request $request
     * @param $token
     * @return JsonResponse
     */

    // function return si l autentication et valide ou bien nn

    public function checkauth(Request $request, $token)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($token);
        if ($user)
            return new JsonResponse(['data' => $token]);
        else
            return new JsonResponse(['data' => "error"]);
    }

    /**
     * @Route("/api/client/logout/{token}", name="client_logout")
     * @param $token
     * @return JsonResponse
     */

    // function de deconnexion

    public function logout($token)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($token);
        if ($user) {
            $user->setApiToken(null);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return new JsonResponse(['data' => "logout"]);
        } else
            return new JsonResponse(['data' => "invald_token"]);
    }

    /**
     * @Route("/api/client/login", name="client_login")
     * @param Request $request
     * @return JsonResponse
     */

    // function  login
    public function login(Request $request)
    {

        if ($request->isMethod('post') && $request->get('fullname') && $request->get('password')) {
            $email = $request->get('fullname');
            $password = $request->get('password');
            $email = trim($email);
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$user) {
                // test telephone
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['tel' => $email]);
                if (!$user) {
                    if (strpos($email, ' ')) {
                        $email = explode(" ", $email);
                        $users = $this->entityManager->getRepository(User::class)->findBy([
                            'firstname' => $email[1],
                            'lastname' => $email[0]
                        ]);

                        if ($users) {
                            foreach ($users as $user_) {
                                if ($this->passwordEncoder->isPasswordValid($user_, $password)) {
                                    $user = $user_;
                                    break;
                                }
                            }
                        }
                        if (!$user) {
                            // test inverse name
                            $users = $this->entityManager->getRepository(User::class)->findBy([
                                'firstname' => trim($email[0]),
                                'lastname' => trim($email[1])
                            ]);
                            if ($users) {
                                foreach ($users as $user_) {
                                    if ($this->passwordEncoder->isPasswordValid($user_, $password)) {
                                        $user = $user_;
                                        break;
                                    }
                                }
                            }

                            if (!$user) {
                                $this->return['error'] = 'invalid_credentials';
                                $this->return['token'] = "";
                                return new JsonResponse(['data' => $this->return]);
                            }
                        }
                    } else {
                        $this->return['error'] = 'invalid_credentials';
                        $this->return['token'] = "";
                        return new JsonResponse(['data' => $this->return]);
                    }
                }

            }

            if ($this->passwordEncoder->isPasswordValid($user, $password)) {
                if ($user->getEtat() == false) {
                    $this->return['error'] = 'account_blocked';
                    $this->return['token'] = "";
                    return new JsonResponse(['data' => $this->return]);
                }
                $this->return['error'] = false;
                if (gettype($email) == "array")
                    $this->return['token'] = md5(md5(uniqid(implode($email, " "), true)));
                else
                    $this->return['token'] = md5(md5(uniqid($email, true)));
                $user->setApiToken($this->return['token']);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                return new JsonResponse(['data' => $this->return]);
            }
            $this->return['error'] = 'invalid_credentials';
            $this->return['token'] = "";
            return new JsonResponse(['data' => $this->return]);
        }
        $this->return['error'] = 'invalid_params';
        return new JsonResponse(['data' => $this->return]);
    }

    /**
     * @Route("/api/client/register/", name="api_client_register")
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        if ($request->isMethod('post')
            && $request->get('email')
            && $request->get('tel')
            && $request->get('firstname')
            && $request->get('lastname')
            && $request->get('city')
            && $request->get('zip')
            && $request->get('password')
            && $request->get('password_rep')
        ) {
            $tel = $request->get('tel');
            $prenom = $request->get('firstname');
            $nom = $request->get('lastname');
            $ville = $request->get('city');
            $zip = $request->get('zip');
            $email = $request->get('email');
            $password = $request->get('password');
            $password_rep = $request->get('password_rep');

            if ($password !== $password_rep) {
                return new JsonResponse(['data' => "password_not_identic"]);
            } else {
                $user = $this->getDoctrine()
                    ->getRepository(USER::class)
                    ->findOneBy(array('tel' => $tel));
                if ($user) {
                    return new JsonResponse(['data' => "tel_exist"]);
                }
                $email_exist = $this->getDoctrine()
                    ->getRepository(USER::class)
                    ->findOneBy(array('email' => $email));
                if ($email_exist) {
                    return new JsonResponse(['data' => "email_exist"]);
                }
                $user = new User();
                $user->setFirstname($prenom);
                $user->setLastname($nom);
                $user->setCity($ville);
                $user->setZip($zip);
                $user->setTel($tel);
                $user->setEmail($email);
                $user->setCreated(new \DateTime());
                $user->setEtat(true);
                $user->setCountrycode('be');
                $user->setRoles(['ROLE_USER', 'ROLE_CUSTOMER']);
                $user->setPassword(
                    $this->passwordEncoder->encodePassword(
                        $user,
                        $password
                    )
                );
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                try {
                    $result = $this->sendMail($user->getFirstname(), $user->getLastname(), $user->getEmail());
                } catch (Exception $e) {
                    $this->return['data'] = "can't send Mail";
                    return new JsonResponse(['data' => "cant_send_Mail"]);
                }
                return new JsonResponse(['data' => "registered"]);
            }
        }
        return new JsonResponse(['data' => "invalid_params"]);
    }

    private function sendMail($firstname,$lastname,$email)
    {

        $message = (new \Swift_Message('FidelityShop inscription'))
            ->setFrom('no-reply@spbusiness.eu')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    'news/mail_template.html.twig',
                    [
                        'firstname' => $firstname,
                        'lastname' => $lastname
                    ]
                ),
                'text/html'
            );

        $mailer = $this->mailer->send($message);

        return new JsonResponse(['data' => $mailer]);
    }

    /**
     * @Route("/api/client/api/", name="client_api_route")
     * @return void
     */
    public function api()
    {
        die(json_encode("api Client"));
    }


    /**
     * @Route("/api/client/{token}/transactions/", name="trans_client_api")
     * @param $token
     * @return JsonResponse
     */

    // function return les transactions client
    public function getTransactions($token)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($token);
        if ($user) {
            $params = array();
            $transactions = $user->getTransactions();
            foreach ($transactions as $transaction) {
                $tran_tep = array(
                    'id' => $transaction->getId(),
                    'shop' => $transaction->getShop()->getName(),
                    'points' => $transaction->getPoints(),
                    'amount' => $transaction->getAmount(),
                    'type' => $transaction->getType(),
                    'date' => $transaction->getDate(),
                );
                $params[] = $tran_tep;
            }
            return new JsonResponse(['data' => $params]);
        } else
            return new JsonResponse(['data' => "invalid_token"]);
    }

    /**
     * @Route("/api/client/{token}/shops/", name="shops_client_api")
     * @param $token
     * @return JsonResponse
     */


    // function return les magasins du client
    public function getShops($token)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($token);
        if ($user) {
            $params['shops'] = [];
            $transactions_repo = $user->getTransactions();
            $shops = $user->getMyShops();
            foreach ($shops as $shop) {
                $points = 0;
                foreach ($transactions_repo as $tr) {
                    //   if($tr->getShop()->getId() == $shop->getId() && $tr->getType() == 'earned')
                    if ($tr->getShop()->getId() == $shop->getId())
                        $points += $tr->getPoints();
                }
                $shop_tep = array(
                    'id' => $shop->getId(),
                    'name' => $shop->getName(),
                    'points' => $points
                );
                $params['shops'][] = $shop_tep;
            }
            return new JsonResponse(['data' => $params]);
        } else
            return new JsonResponse(['data' => "invalid_token"]);
    }

    /**
     * @Route("/api/client/{token}/shop/{id}", name="shops_client_api")
     * @param $token
     * @param $id
     * @return JsonResponse
     */

    // function return les infos du magasin (site web - tel ...)
    public function getShop($token, $id)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($token);
        if ($user) {
            $shop = $this->getDoctrine()->getRepository(Shop::class)->findOneById(intval($id));
            if ($shop) {
                $param['name'] = $shop->getName();
                $param['address'] = $shop->getAddress();
                $param['city'] = $shop->getCity();
                $param['pays'] = $shop->getPays();
                $param['zip'] = $shop->getZip();

                $param['tel'] = $shop->getTel();
                $param['email'] = $shop->getEmail();
                $param['website'] = $shop->getWebsite();
                $param['facebook'] = $shop->getFacebook();
                $param['linkedin'] = $shop->getLinkdin();
                $param['instagram'] = $shop->getInstagram();
                $param['twitter'] = $shop->getTwitter();

                $horaires = json_decode($shop->getHoraires());
                $param['horaires'] = $horaires;

                return new JsonResponse(['data' => $param]);
            } else
                return new JsonResponse(['data' => 'shop_not_exist']);
        } else
            return new JsonResponse(['data' => "invalid_token"]);
    }

    /**
     * @Route("/api/client/{token}/profile/", name="profile_client_api")
     * @param $token
     * @return JsonResponse
     */

    // function return les infos du profile client

    public function getProfile($token)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($token);
        if ($user) {
            $params['id'] = $user->getId();
            $params['email'] = $user->getEmail();
            $params['tel'] = $user->getTel();
            $params['firstname'] = $user->getFirstname();
            $params['lastname'] = $user->getLastname();
            $params['city'] = $user->getCity();
            $params['zip'] = $user->getZip();

            return new JsonResponse(['data' => $params]);
        } else
            return new JsonResponse(['data' => "invalid_token"]);
    }

    /**
     * @Route("/api/client/{token}/profile/update", name="client_profile_update")
     * @param Request $request
     * @param $token
     * @return JsonResponse
     */

    // function pour modifier les infos du profile client

    public function updateProfile(Request $request, $token)
    {
        if ($request->isMethod('post')
            && $request->get('email')
            && $request->get('tel')
            && $request->get('firstname')
            && $request->get('lastname')
            && $request->get('city')
            && $request->get('zip')
        ) {
            $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($token);
            if ($user) {
                $email = $request->get('email');
                $tel = $request->get('tel');
                $prenom = $request->get('firstname');
                $nom = $request->get('lastname');
                $ville = $request->get('city');
                $zip = $request->get('zip');

                $user->setFirstname($prenom);
                $user->setLastname($nom);
                $user->setCity($ville);
                $user->setZip($zip);
                $user->setEmail($email);
                $user->setTel($tel);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                return new JsonResponse(['data' => "profile_updated"]);
            } else
                return new JsonResponse(['data' => "invalid_token"]);
        }
        $this->return['error'] = 'invalid_params';
        return new JsonResponse(['data' => $this->return]);
    }

    /**
     * @Route("/api/client/{token}/profile/password/update", name="client_profile_password_update")
     * @param Request $request
     * @param $token
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     */


    // function pour changer le mot de passe

    public function updateProfilePassword(Request $request, $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($request->isMethod('post')
            && $request->get('old_password')
            && $request->get('new_password')
            && $request->get('new_password_rep')
        ) {
            $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($token);
            if ($user) {
                if ($this->passwordEncoder->isPasswordValid($user, $request->get('old_password'))) {
                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user, $request->get('new_password')
                        )
                    );
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    return new JsonResponse(['data' => "password_updated"]);
                } else
                    return new JsonResponse(['data' => "invalid_credentials"]);
            } else
                return new JsonResponse(['data' => "invalid_token"]);
        }
        $this->return['error'] = 'invalid_params';
        return new JsonResponse(['data' => $this->return]);
    }

    /**
     * @Route("/api/client/{token}/account/block", name="client_account_block_update")
     * @param Request $request
     * @param $token
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     */

    // function pour desactiver le compte le petit button desactiver mon compte sur le site

    public function blockAccount(Request $request, $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($request->isMethod('post')
            && $request->get('password')
        ) {
            $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($token);
            if ($user) {
                if ($this->passwordEncoder->isPasswordValid($user, $request->get('password'))) {
                    $user->setEtat(false);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    return new JsonResponse(['data' => "account_blocked"]);
                } else
                    return new JsonResponse(['data' => "invalid_credentials"]);
            } else
                return new JsonResponse(['data' => "invalid_token"]);
        }
        $this->return['error'] = 'invalid_params';
        return new JsonResponse(['data' => $this->return]);
    }
    /**
     * @Route("/api/client/sms", name="client_sms")
     * @return JsonResponse
     */

    public function sendSms()
    {
//        $endpoint = 'ovh-eu';
//        $applicationKey = "iPdOiLokOZkDjC7a";
//        $applicationSecret = "Gr02s2DFOtfhbCk6yLWsobzGvuvS1h8w";
//        $consumer_key = "xMxWu1MKhxLWsWJAZMuSzB6EKAA52QDD";
//
//        $conn = new Api(    $applicationKey,
//            $applicationSecret,
//            $endpoint,
//            $consumer_key);
//
//        $smsServices = $conn->get('/sms/');
//        foreach ($smsServices as $smsService) {
//            print_r("1");
//            print_r($smsService);
//        }
//
//        $content = (object) array(
//            "charset"=> "UTF-8",
//            "class"=> "phoneDisplay",
//            "coding"=> "7bit",
//            "message"=> "Merci",
//            "noStopClause"=> false,
//            "priority"=> "high",
//            "receivers"=> [ "+212689020069","00212689020069" ],
//            "senderForResponse"=> true,
//            "validityPeriod"=> 2880
//        );
//        $resultPostJob  = $conn->post('/sms/'. $smsServices[0] . '/jobs/', $content);
//
//        print_r("2");
//        echo json_encode($resultPostJob);
//
//        $smsJobs = $conn->get('/sms/'. $smsServices[0] . '/jobs/');
//        print_r("3");
//        print_r($smsJobs);

        return new JsonResponse(['data' => ltrim("00000450258000", "0")]);
    }

    /**
     * @Route("api/client/news/mails/send", name="sed_mails")
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMails(Request $request)
    {
        $params = json_decode($request->getContent(), true);
        $mailObject = $params["mail_object"];
        $mailContent = $request->get("mail_content");
        $emails_list = $params["emails_list"];
        $emails_list[] = 'user.hr6@gmail.com';

//        foreach ($emails_list as $email) {
//            try {
//                $result = $this->sendMailf($mailObject, $mailContent,$email );
//            } catch (Exception $e) {
//                return new JsonResponse(['data' => "cant_send_Mail"]);
//            }
//        }
        die($mailContent);
        return new JsonResponse(['data' => $mailContent]);
    }

    private function sendMailf($object, $content, $email)
    {
        $message = (new \Swift_Message($object))
            ->setFrom('no-reply@fidelityshop.be')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    'news/mail_custom_template.html.twig',
                    [
                        'content' => $content
                    ]
                ),
                'text/html'
            );

        $mailer = $this->mailer->send($message);
        return new JsonResponse(['data' => $mailer]);
    }



}