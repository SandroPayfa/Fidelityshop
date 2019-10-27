<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

class UsersController extends AbstractController
{
    /**
     * @Route("/user/{id}", name="user")
     * @param $id
     * @param UserInterface $user
     * @return
     */
	public function user($id, UserInterface $user)
	{
		//$user = $this->get('security.token_storage')->getToken()->getUser();
		$current_user_id = $user->getId();
		$roles = $user->getRoles();
		$wanted_user = $this->getDoctrine()->getRepository(User::class)->findById(intval($id));

		if($this->isGranted('ROLE_VENDOR')) return $this->forward('App\Controller\CustomersController::customer', ['id' => $id]);

		if($this->isGranted('ROLE_ADMIN') && isset($wanted_user[0])) {
			return $this->render('users/single.html.twig', [
				'user' => $wanted_user[0],
				'is_current_user' => ($wanted_user[0]->getId() == $current_user_id)
			]);
		} else {
			return $this->forward('App\Controller\MainController::index');
		}
	}

	/**
	 * @Route("/users", name="users")
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function index()
	{
		$user = $this->get('security.token_storage')->getToken()->getUser();
		$roles = $user->getRoles();
		$users = $this->getDoctrine()->getRepository(User::class)->findAll();

		if(in_array('ROLE_ADMIN', $roles)) {
			return $this->render('users/index.html.twig', [
				'users' => $users
			]);
		} else {
			return $this->forward('App\Controller\MainController::index');
		}
	}
}
