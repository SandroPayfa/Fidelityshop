<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\NewPasswordFormType;
use App\Form\UserFormType;
use App\Entity\User;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResetPasswordController extends AbstractController
{

    private $mailer;

    public function __construct(\Swift_Mailer $mailer){
        $this->mailer=$mailer;
    }

    /**
     * @Route("/account/{token}/password_reset", name="post_reset_password")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param $token
     * @return RedirectResponse
     */
    public function resetPost(Request $request,$token, UserPasswordEncoderInterface $passwordEncoder){
        $error="";
        $updated = false;
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            "reset_token" => $token
        ]);


        if(!$user){
            return new RedirectResponse(
                $this->container->get('router')->generate('app_login', []));
        }else{
            $now = new \DateTime();
            $diff  =   ($now->getTimestamp()-$user->getResetTokenTime()->getTimestamp())-600;
            if($diff>0)
                return new RedirectResponse(
                    $this->container->get('router')->generate('app_login', []));
        }


        if ($request->isMethod('post') && $request->get('reset_token') && $request->get('password') && $request->get('confirm_password')){
            if($request->get('password') != $request->get('confirm_password')){
                $error = "password no identic";
            }else{
                $token_ = $request->get('reset_token');
                $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                    "reset_token" => $token_
                ]);
                if($user){

                    $user->setREsetToken(null);
                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user,
                            $request->get('password')
                        )
                    );
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    try {
                        $em->flush();
                        $sent=true;
                        $updated = true;
                    } catch (UniqueConstraintViolationException $e) {
                        die(json_encode($e));
                    }
                }
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'error' => $error,
            'token' => $token,
            'updated' => $updated
        ]);
    }

    /**
     * @Route("/account/begin_password_reset", name="begin_password_reset")
     * @param Request $request
     * @return RedirectResponse
     * @internal param Request $request
     * @internal param UserPasswordEncoderInterface $passwordEncoder
     */
    public function beginPasswordReset(Request $request){

        $exist = true;
        $error = "";
        if ($request->isMethod('post') && $request->get('email')){
            $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($request->get('email'));
            if(!$user){
                $user = $this->getDoctrine()->getRepository(User::class)->findOneByTel($request->get('email'));
                if(!$user){
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneByUsername($request->get('email'));
                    if(!$user){
                        $error ="Nous n'avons pas pu trouver votre compte avec cette information";
                    }
                }
            }
            if($user){
                return new RedirectResponse(
                    $this->container->get('router')->generate('send_password_reset', ["id" => $user->getId()]));
            }
        }

        return $this->render('security/begin_password_reset.html.twig', [
            'error' => $error
        ]);
    }

    /**
     * @Route("/account/send_password_reset/{id}", name="send_password_reset")
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @internal param Request $request
     */
    public function sendPasswordReset(Request $request,$id){
        $sent = false;
        if ($request->isMethod('post') && $request->get('email')){
            $email = $request->get('email');
            $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($email);
            if($user){
                $token = md5(md5(uniqid($email , true)));
                $user->setREsetToken($token);
                $user->setREsetTokenTime(new \DateTime());
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                try {
                    $em->flush();
                    $result = $this->sendMail($user->getFirstname(), $user->getLastname(), $user->getEmail(),$token);
                    $sent=true;
                } catch (UniqueConstraintViolationException $e) {
                    // ....
                    die(json_encode($e));
                }
            }
        }
        $user = $this->getDoctrine()->getRepository(User::class)->findOneById($id);
        if(!$user){

        }

        return $this->render('security/send_reset_password.html.twig', [
            'email' => $user->getEmail(),
            'id' => $id,
            'sent' => $sent
        ]);
    }

    private function sendMail($firstname,$lastname,$email,$token)
    {

        $message = (new \Swift_Message('FidelityShop réinitialiser mot de passe'))
            ->setFrom('no-reply@fidelityshop.be')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    'security/reset_password_mail_template.html.twig',
                    [
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'token' => $token
                    ]
                ),
                'text/html'
            );

        $mailer = $this->mailer->send($message);

        return new JsonResponse(['data' => $mailer]);
    }



}