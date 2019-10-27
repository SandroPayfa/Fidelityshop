<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Form\FormError;

use Symfony\Component\HttpFoundation\JsonResponse;

class RegistrationController extends AbstractController
{


    private $mailer;

    public function __construct(\Swift_Mailer $mailer){
        $this->mailer=$mailer;
    }


    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder,
                             GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted()) {

            try {
                $user_ = $this->getDoctrine()->getRepository(USER::class)
                    ->findOneBy(array('tel' => str_replace(" ", "", $form->get('tel')->getData()), 'email' => null));
                if ($user_) {

//                die(json_encode("number phone exist"));
                    $entityManager = $this->getDoctrine()->getManager();
                    $data = $entityManager->getReference(User::class, $user_->getId());

                    $data->setEmail($form->get('email')->getData());
                    $data->setLastname($form->get('lastname')->getData());
                    $data->setFirstname($form->get('firstname')->getData());
                    $data->setAddress($form->get('address')->getData());
                    $data->setZip($form->get('zip')->getData());
                    $data->setCity($form->get('city')->getData());

                    $data->setCreated(new \DateTime());
                    $data->setCountrycode('be');
                    $data->setEtat(true);

                    $data->setPassword(
                        $passwordEncoder->encodePassword(
                            $data,
                            $form->get('plainPassword')->getData()
                        )
                    );

                    $data->setRoles(['ROLE_USER', 'ROLE_CUSTOMER']);
                    $entityManager->flush();
                    return $guardHandler->authenticateUserAndHandleSuccess(
                        $data,
                        $request,
                        $authenticator,
                        'main' // firewall name in security.yaml
                    );

//                $em = $this->getDoctrine()->getManager();
//                $em->getConnection()
//                    ->prepare("update user SET firstname = '".$user->getFirstname()."' where id=".$user_->getId())
//                    ->execute();
                }
            } catch (UniqueConstraintViolationException $e) {

            }

        }

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setCountrycode('be');
            $user->setEtat(true);
            $user->setRoles(['ROLE_USER', 'ROLE_CUSTOMER']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            try {
                $entityManager->flush();
                $result = $this->sendMail($user->getFirstname(), $user->getLastname(), $user->getEmail());
            } catch (UniqueConstraintViolationException $e) {
                // ....
                die(json_encode($e));
            }

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    private function sendMail($firstname,$lastname,$email)
    {
        $message = (new \Swift_Message('FidelityShop inscription'))
            ->setFrom('no-reply@fidelityshop.be')
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
}