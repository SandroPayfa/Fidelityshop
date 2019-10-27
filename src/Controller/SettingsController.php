<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\NewPasswordFormType;
use App\Form\UserFormType;
use App\Entity\User;

class SettingsController extends AbstractController
{
    /**
     * @Route("/settings", name="settings")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return
     */
	public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder)
	{
		$form_sent = false;
		$user = $this->get('security.token_storage')->getToken()->getUser();

		$general_form = $this->createForm(UserFormType::class, $user);
		$general_form->handleRequest($request);
		if ($general_form->isSubmitted() && $general_form->isValid()) {
			$upd_usr = $this->getDoctrine()->getRepository(User::class)->findById($user->getId())[0];
//			$upd_usr->setUsername($general_form->get('username')->getData());
			$upd_usr->setFirstname($general_form->get('firstname')->getData());
			$upd_usr->setLastname($general_form->get('lastname')->getData());
			$upd_usr->setEmail($general_form->get('email')->getData());
			$upd_usr->setTel(
                str_replace(" ", "", $general_form->get('tel')->getData())
            );
			$upd_usr->setAddress($general_form->get('address')->getData());
            $upd_usr->setZip($general_form->get('zip')->getData());
            $upd_usr->setCountrycode($general_form->get('countrycode')->getData());

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($upd_usr);
			$entityManager->flush();

			$form_sent = true;
		}

		$password_form = $this->createForm(NewPasswordFormType::class, $user);
		$password_form->handleRequest($request);
		if ($password_form->isSubmitted() && $password_form->isValid()) {
			$upd_usr = $this->getDoctrine()->getRepository(User::class)->findById($user->getId())[0];

			//$upd_usr->setUsername($password_form->get('username')->getData());
			$user->setPassword(
				$passwordEncoder->encodePassword(
					$user,
					$password_form->get('plainPassword')->getData()
				)
			);

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($upd_usr);
			$entityManager->flush();

			$form_sent = true;
		}

		return $this->render('settings/customer.html.twig', [
			'form_sent' => $form_sent,
			'general_form' => $general_form->createView(),
			'password_form' => $password_form->createView(),
		]);
	}


	/**
	 * @Route("/supprimer_mon compte", name="delete_my_account")
	 */
	public function delete_my_account() {
		$user = $this->get('security.token_storage')->getToken()->getUser();
		$this->get('security.token_storage')->setToken(null);
		$entityManager = $this->getDoctrine()->getManager();
		$entityManager->remove($user);
		$entityManager->flush();
		return $this->redirect($this->generateUrl('logout'));
	}

	/**
	 * @Route("/desactivate_mon compte", name="desactivate_my_account")
	 */

	public function desactivate_account () {

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$this->get('security.token_storage')->setToken(null);
		$user->setEtat(false);
		$entityManager = $this->getDoctrine()->getManager();
		$entityManager->persist($user);
		$entityManager->flush();
		return $this->redirect($this->generateUrl('logout'));

	}
}
