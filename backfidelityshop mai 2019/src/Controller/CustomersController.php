<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;


class CustomersController extends AbstractController
{
	/**
	 * @Route("/customer/{id}", name="customer")
	 */
	public function customer($id, UserInterface $user)
	{
		$current_user_id = $user->getId();
		$roles = $user->getRoles();
		$wanted_user = $this->getDoctrine()->getRepository(User::class)->findById(intval($id));

		if(($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_VENDOR')) && isset($wanted_user[0])) {
			return $this->render('customers/single.html.twig', [
				'user' => $wanted_user[0],
				'is_current_user' => ($wanted_user[0]->getId() == $current_user_id)
			]);
		} else {
			return $this->forward('App\Controller\MainController::index');
		}

	}

	/**
	 * @Route("/customers", name="customers")
	 */
	public function index()
	{
		$user = $this->get('security.token_storage')->getToken()->getUser();
		$shops = $user->getShops();

		if($this->isGranted('ROLE_VENDOR')) {
			$customers = [];
			foreach ($shops as $i => $shop) {
				$custs = $shop->getCustomers();
				foreach ($custs as $j => $cust) {
					if($cust->getId() == $user->getId()) {
						unset($custs[$j]);
					}
				}
				array_push($customers, $shop->getCustomers());
			}
			return $this->render('customers/index.html.twig', [
				'customers' => $customers,
			]);
		}
		return $this->forward('App\Controller\MainController::index');

	}

}
