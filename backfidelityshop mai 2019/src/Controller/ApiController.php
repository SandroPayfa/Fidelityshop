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
use \Ovh\Api;
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

class ApiController extends AbstractController
{
    protected $return;
    protected $errors;
    private $passwordEncoder;
    private $mailer;

    public function __construct(\Swift_Mailer $mailer, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->return = [];
        $this->passwordEncoder = $passwordEncoder;
        $this->errors = [];
        $this->mailer=$mailer;
    }

    /**
     * @Route("/api", name="api")
     */
    public function index()
    {
        $this->response();
    }


    /**
     * @Route("/api/unlock/{pin}/{serial}", name="action")
     * @param $pin
     * @param $serial
     */
    public function action($pin, $serial)
    {
					$this->return ['reward'] = '';
                    $this->return ['spend'] = '';
                    $this->return ['image'] = '';
                    $this->return ['reduction_points'] = '';
                    $this->return ['reduction_montant'] = '';
					$this->return['shop'] = '';
					$this->return['data'] = "";
		
        if (!is_numeric($pin)) {
            $this->return['data'] = 'pin_is_nan';
            $this->response();
        } else {
            // $licence_repo = $this->getDoctrine()->getRepository(Licence::class)->findByPin(intval($pin));
			$licence = $this->getDoctrine()->getRepository(Licence::class)->findOneBySerial($serial);
           // foreach ($licence_repo as $licence) {
             //   $licence_serial = $licence->getSerial();
               // if ($licence_serial == $serial) {
				if($licence){

					$this->return ['reward'] = $licence->getShop()->getRewardRate();
                    $this->return ['spend'] = $licence->getShop()->getSpendRate();
                    $this->return ['image'] = $licence->getShop()->getImage();
                    $this->return ['reduction_points'] = $licence->getShop()->getReductionPoints();
                    $this->return ['reduction_montant'] = $licence->getShop()->getReductionMontant();
					$this->return ['shop'] = $licence->getShop()->getName();
                    $this->return['data'] = "success_unlocked";
					$this->response();
                }else{
					$this->return['data'] = "";
				}
            //}
        }
        $this->response();
    }


    /**
     * @param $user_id
     * @param $shop_id
     * @param $points
     * @return bool
     */
    private function update_points($user_id, $shop_id, $points)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $RAW_QUERY = 'UPDATE shop_user set points=points + :points where shop_id = :shop_id and user_id = :user_id;';
//        $RAW_QUERY = 'SELECT points FROM shop_user where shop_id = :shop_id and user_id = :user_id;';
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            // Set parameters
            $statement->bindValue('points', $points);
            $statement->bindValue('shop_id', $shop_id);
            $statement->bindValue('user_id', $user_id);
            $statement->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @Route("/api/points/use/{points}/{phone}/{serial}", name="use_points")
     * @param $points
     * @param $phone
     * @param $serial
     * @return void
     */
    public function use_points($points, $phone, $serial)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findByTel($phone);
		if(!$user){
			$this->return['data'] = 'user_not_exist';
			$this->response();
		}
        $current_licence = $this->getDoctrine()->getRepository(Licence::class)->findBySerial($serial);
        if (count($current_licence) <= 0) {
            $this->return['data'] = 'licence_not_exist';
            $this->response();
        }
        $current_shop = $current_licence[0]->getShop();
        if (empty($current_shop)) {
            $this->return['data'] = 'shop_not_exist';
            $this->response();
        }

        $shop_id = $current_shop->getId();
        $user_id = $user[0]->getId();

        try {
            $em = $this->getDoctrine()->getManager();
            $RAW_QUERY = 'UPDATE shop_user set used_points = used_points + :points where shop_id = :shop_id and user_id = :user_id;';
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->bindValue('points', $points);
            $statement->bindValue('shop_id', $shop_id);
            $statement->bindValue('user_id', $user_id);
            $statement->execute();
            $this->reduction_transaction($current_shop,$user[0]);
            $this->return['data'] = "points_used";
            $this->response();
        } catch (\Exception $e) {
            $this->return['data'] = "cant_update_points";
            $this->response();
        }

    }


    /**
     * @Route("/api/new_transaction/{amount}/{phone}/{serial}/{firstuse}", name="new_transaction")
     * @param $amount
     * @param $phone
     * @param $serial
     * @param $firstuse
     */
    public function new_transaction($amount, $phone, $serial, $firstuse)
    {
        $this->return['points'] = '';
        $current_licence = $this->getDoctrine()->getRepository(Licence::class)->findBySerial($serial);
        if (count($current_licence) <= 0) {
            $this->return['data'] = 'licence_not_exist';
            $this->response();
        }
        $current_shop = $current_licence[0]->getShop();
        if (empty($current_shop)) {
            $this->return['data'] = 'shop_not_exist';
            $this->response();
        }

        $existing_user = $this->getDoctrine()->getRepository(User::class)->findByTel($phone);


        $user_exists = isset($existing_user[0]);
        $user = ($user_exists ? $existing_user[0] : new User());
        // echo $user ;
        if (!$user_exists && $firstuse == 'true') {
            $this->return['data'] = 'RGDP';
            $this->response();
        }

        $entityManager = $this->getDoctrine()->getManager();

        $transaction = new Transactions();
        $transaction->setAmount(intval($amount));
        $transaction->setDate(new \DateTime());
        $transaction->setShop($current_shop);
        $transaction->setPoints(intval($amount) * $current_shop->getSpendRate());
        $transaction->addUserId($user);

        if (!$user_exists) {
            $user->setPassword(uniqid());
            $user->setTel($phone);
            $user->setCreated(new \DateTime());
            $user->setFirstname('');
            $user->setLastname('');
            $user->setEtat(true);
            $user->setCountrycode('be');
            $user->setRoles(['ROLE_USER', 'ROLE_CUSTOMER']);

        } else {

            if ($user->getFirstname() == '' && $user->getLastname() == '') {

                $trans = $user->getTransactions();

                if (count($trans) >= 3) {
                    $this->return['data'] = 'register';
                    $this->response();
                }
            }
        }
        if (!$user->getMyShops()->contains($current_shop)) {
            $user->addMyShops($current_shop);
        }
        $user->setEtat(true);
        $entityManager->persist($user);
        $entityManager->persist($transaction);
        $entityManager->flush();
        $this->return['points'] = $transaction->getPoints();
        if ($this->update_points($user->getId(), $transaction->getShop()->getId(), $transaction->getPoints())){
            $this->return['data'] = "success_transaction";
            $this->response();
        }
        else {
            $this->return['data'] = "cant_update_points";
            $this->response();
        }
    }
    private function reduction_transaction($current_shop, $user)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $transaction = new Transactions();
        $transaction->setAmount(intval($current_shop->getReductionMontant()));
        $transaction->setDate(new \DateTime());
        $transaction->setShop($current_shop);
        $transaction->setPoints($current_shop->getReductionPoints());
        $transaction->setType('used');
        $transaction->addUserId($user);
        $entityManager->persist($transaction);
        $entityManager->flush();
    }

    /**
     * @Route("/api/serial/{serial}/check",name="check_serial")
     * @param $serial
     */
    public function checkSerial($serial)
    {
        $licence = $this->getDoctrine()->getRepository(Licence::class)->findOneBySerial($serial);
		$this->return['terminal_id'] = '';
		$this->return['shop'] = '';
		$this->return['status']='';
        if ($licence) {
            $this->return['shop'] = $licence->getShop()->getName();
            if ($licence->getExpiredAt() != null) {
                $day_diff = $licence->getExpiredAt()->getTimestamp() - (new \DateTime())->getTimestamp();
                $expired = floor($day_diff / (60 * 60 * 24));
                if ($expired < 0) {
                    $this->return['status'] = "expired";
                    $this->response();
                } else {
                    if (!$licence->getActived()) {
                        $this->return['status'] = "blocked";
                        $this->response();
                    } else {
                        $this->return['status'] = "success";
                        $this->return['terminal_id'] = $licence->getTerminalId();
                        $this->response();
                    }
                }
            } else {
                $this->return['status'] = "unused";
                $this->response();
            }
        } else {
            $this->return['status'] = "not_exist";
        }

        $this->response();
    }


    /**
     * @Route("/api/points/check/{phone}/{serial}/",name="check_points")
     * @param $phone
     * @param $serial
     */

    public function checkpoints($phone, $serial)
    {
		$this->return['points_earned']='';
        $em = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()->getRepository(User::class)->findByTel($phone);
        $current_licence = $this->getDoctrine()->getRepository(Licence::class)->findBySerial($serial);
        if (count($current_licence) <= 0) {
            $this->return['data'] = 'licence_not_exist';
            $this->response();
        }
        $current_shop = $current_licence[0]->getShop();
        if (empty($current_shop)) {
            $this->return['data'] = 'error2';
            $this->response();
        }
		
        $RAW_QUERY = 'select * from shop_user where shop_id = :shop_id and user_id = :user_id ';
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->bindValue('shop_id', $current_shop->getId());
        $statement->bindValue('user_id', $user[0]->getId());
        $statement->execute();
        $points = $statement->fetchall();
		$this->return['data']='success';
        $this->return['points_earned'] = $points[0]['points'];
        $this->response();
    }

    private function sendMail($firstname,$lastname,$email)
    {
        $message = (new \Swift_Message('FidelityShop inscription'))
            ->setFrom('no-reply@spbusiness.eu')
            ->setTo("harmate.hicham@gmail.com")
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
        $temp = $this->mailer->send($message);
        return $temp;
    }


    /**
     * @Route("/api/register/{tel}/{prenom}/{nom}/{ville}/{zip}/{email}/{password}/{password_rep}", name="api_register")
     * @param $tel
     * @param $prenom
     * @param $nom
     * @param $ville
     * @param $zip
     * @param $email
     * @param $password
     * @param $password_rep
     * @return void
     */
    public function register($tel, $prenom, $nom, $ville, $zip, $email, $password, $password_rep)
    {
        if ($password !== $password_rep) {
            $this->return['data'] = "password_not_identic";
            $this->response();
        } else {
            $user = $this->getDoctrine()
                ->getRepository(USER::class)
                ->findOneBy(array('tel' => $tel));
            if (!$user) {
                $this->return['data'] = "tel_exist";
                $this->response();
            }

            $email_exist = $this->getDoctrine()
                ->getRepository(USER::class)
                ->findOneBy(array('email' => $email));
            if ($email_exist) {
                $this->return['data'] = "email_exist";
                $this->response();
            }

            $user->setFirstname($prenom);
            $user->setLastname($nom);
            $user->setCity($ville);
            $user->setZip($zip);
            $user->setEmail($email);

            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $password
                )
            );
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            try{
				$result = $this->sendMail($user->getFirstname(), $user->getLastname(), $user->getEmail());
			}catch (Exception $e) {
                $this->return['data'] = "can't send Mail";
				$this->response();
            }
            $this->return['data'] = $result;
            $this->response();
        }
    }


    /**
     * @Route("/api/terminal/activate/{serial}/{terminal_id}", name="activate_terminal")
     * @param $serial
     * @param $terminal_id
     * @return void
     */
    public function activate_terminal($serial, $terminal_id)
    {
        $licence = $this->getDoctrine()->getRepository(Licence::class)->findOneBy([
            'terminal_id' => $terminal_id,
            'serial' => $serial
        ]);
        if ($licence) {
            if ($licence->getActivatedAt() != null) {
                $this->return['data'] = 'invalid';
            } else {

                $entityManager = $this->getDoctrine()->getManager();
                $licence->setActivatedAt(new \DateTime());
                $licence->setActived(1);
                $licence->setExpiredAt(
                    new \DateTime('+' . $licence->getPeriode() . ' month')
                );
                $entityManager->persist($licence);
                $entityManager->flush();
                $this->return['data'] = 'success';
            }
        } else {
            $this->return['data'] = 'invalid';
        }
        $this->response();
    }

    private function response()
    {
        die(json_encode($this->return));
    }

}
