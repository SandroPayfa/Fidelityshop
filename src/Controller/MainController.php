<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Transactions;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

class MainController extends AbstractController
{

	/**
	 * @Route("/", name="main")
	 */
	public function index()
	{
		$user = $this->get('security.token_storage')->getToken()->getUser();
		$manager = $this->getDoctrine()->getManager();
		$user_repository = $manager->getRepository(User::class);
		$transactions_repo = $manager->getRepository(Transactions::class);
		$params = [];
		$view = 'customer';

		if($this->isGranted('ROLE_ADMIN')) {
			$params = [
				'nb_customers' => $user_repository->count_customers(),
			];
			$view = 'admin';
		}

		/*
		*	Counters
		*/
		$params['nb_points'] = 0;
		$params['used_points'] = 0;
		$params['nb_shops'] = 0;
		$params['transactions'] = [];
		$params['nb_customers'] = 0;

		if($this->isGranted('ROLE_CUSTOMER')) {
			$view = 'customer';
			$params['transactions'] = $transactions_repo->findByUserId($user->getId());
			$temps_shops_ids = [];
            try {
                $em = $this->getDoctrine()->getManager();
                $RAW_QUERY = 'select * from shop_user where user_id = :user_id;';
                $statement = $em->getConnection()->prepare($RAW_QUERY);
                // Set parameters

                $statement->bindValue('user_id', $user->getId());
                $statement->execute();
                $points=$statement->fetchAll();
                $params['nb_points']=$points[0]["points"]-$points[0]["used_points"];
                $params['used_points']=$points["used_points"];

            } catch (\Exception $e) {
            }

			foreach ($params['transactions'] as $tr) {
				$curid = $tr->getShop()->getId();
				if(!in_array($curid, $temps_shops_ids)) array_push($temps_shops_ids, $curid);
//				$params['nb_points'] += intval($tr->getPoints());
			}
			$params['nb_shops'] = count($temps_shops_ids);
		}

		if($this->isGranted('ROLE_VENDOR')) {
			$view = 'vendor';

			$shops = $user->getShops();
			$params['nb_shops'] = count($shops);
			$customers = [];
			$temp_transactions = array();
			$transactions = array();
			foreach ($shops as $i => $shop) {
				$temp_customers = $shop->getCustomers();
				//
                $params['nb_customers']+=count($temp_customers);
				$customers = new ArrayCollection(
					array_merge((array)$customers, (array)$temp_customers)
				);

				$shop_transactions = $transactions_repo->findTodayByShop($shop->getId());
				if($shop_transactions){
                    $transactions =array_merge($transactions, $shop_transactions);
                }

                try {
                    $em = $this->getDoctrine()->getManager();
                    $RAW_QUERY = 'select * from shop_user where shop_id = :shop_id;';
                    $statement = $em->getConnection()->prepare($RAW_QUERY);
                    // Set parameters
                    $statement->bindValue('shop_id', $shop->getId());
                    $statement->execute();
                    $points=$statement->fetchAll();
                    foreach ($points as $point){
                        $p=$point["points"]-$point["used_points"];
                        $params['nb_points'] += $p;
                        $params['used_points']=$point["used_points"];
                    }

                } catch (\Exception $e) {
                    die("errors : ".$e->getMessage());
                }

			}
			$params['customers'] = $customers;
			$params['transactions'] = $transactions;
		}

		return $this->render('main/'.$view.'.html.twig', $params);
	}
}
