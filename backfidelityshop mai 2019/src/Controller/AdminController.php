<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\User;
use App\Entity\Transactions;


class AdminController extends AbstractController
{
    /**
     * @Route("/admin/set-role/{id}/{role}", name="admin_setRole")
     * @Security("is_granted('ROLE_ADMIN')")
     * @param $id
     * @param $role
     * @return RedirectResponse
     */
	public function setRole($id, $role) {
		$user = $this->getDoctrine()
				->getRepository(User::class)
				->findOneBy(['id' => $id]);
		if(isset($user) && !is_string($user)) {
			$roles = $user->getRoles();
			if(!in_array($role, $roles)) {
				array_push($roles, $role);
			}
			$user->setRoles($roles);
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($user);
			$entityManager->flush();
		}
		return new RedirectResponse($this->container->get('router')->generate('users', [], UrlGeneratorInterface::ABSOLUTE_URL), 301);
	}

    /**
     * @Route("/admin/unset-role/{id}/{role}", name="admin_unsetRole")
     * @Security("is_granted('ROLE_ADMIN')")
     * @param $id
     * @param $role
     * @return RedirectResponse
     */
	public function unsetRole($id, $role) {
		$user = $this->getDoctrine()
				->getRepository(User::class)
				->findOneBy(['id' => $id]);
		if(isset($user) && !is_string($user)) {
			$roles = $user->getRoles();
			foreach ($roles as $i => $tmp_role) {
				if($tmp_role == $role) {
					unset($roles[$i]);
				}
			}
			$user->setRoles($roles);
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($user);
			$entityManager->flush();
		}
		return new RedirectResponse($this->container->get('router')->generate('users', [], UrlGeneratorInterface::ABSOLUTE_URL), 301);
	}

	/**
	 * @Route("/admin/delete-user/{id}", name="admin_deleteUser")
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function admin_deleteUser($id) {
		$user = $this->getDoctrine()
				->getRepository(User::class)
				->findOneBy(['id' => $id]);
		if(isset($user) && !is_string($user)) {
            $entityManager = $this->getDoctrine()->getManager();

            $user_Trans=$user->getTransactions();
            foreach ($user_Trans as $user_Tran) {
                $entityManager->remove($user_Tran);
            }   $entityManager->flush();

			$entityManager->remove($user);
			$entityManager->flush();
		}
		return new RedirectResponse($this->container->get('router')->generate('users', [], UrlGeneratorInterface::ABSOLUTE_URL), 301);
	}
}
