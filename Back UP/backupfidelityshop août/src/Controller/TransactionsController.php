<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Transactions;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;

class TransactionsController extends AbstractController
{
    /**
     * @Route("/transactions", name="transactions")
     * @param Request $request
     * @param bool $my
     * @return
     */
    public function transactions(Request $request, $my = false)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $manager = $this->getDoctrine()->getManager();

        $transactions_repo = $manager->getRepository(Transactions::class);

        if (
            $this->isGranted('ROLE_CUSTOMER')
            && !$this->isGranted('ROLE_VENDOR')
            && !$this->isGranted('ROLE_ADMIN')
        ) $my = true;

        $transactions = array();
        $temp_transactions = array();
        if ($my == false) {
            if ($this->isGranted('ROLE_ADMIN')) {
                $transactions = $transactions_repo->findBy(
                    [],
                    ['date' => 'DESC']
                );
            }
            else if ($this->isGranted('ROLE_VENDOR')) {
                $shops = $user->getShops();
                if ($request->isMethod('get') && $request->get('periode')) {
                    $periode = 0;
                    switch ($request->get('periode')) {
                        case 'today' :
                            $periode = 0;
                            break;
                        case 'yesterday' :
                            $periode = -1;
                            break;
                        case 'last_week' :
                            $periode = -7;
                            break;
                        case 'last_month' :
                            $periode=-30;
                            break;
                        case 'current_month' :
                            $periode = 5;
                            break;
                        case 'current_year' :
                            $periode = 6;
                            break;
                        case 'prev_month' :
                            $periode = 7;
                            break;
                        case 'prev_year' :
                            $periode = 8;
                            break;
                    }

                    if($periode==0){
                        foreach ($shops as $i => $shop) {
                            $shop_transactions = $transactions_repo->findTodayByShop($shop);
                            if($shop_transactions){
                                $temp_transactions =array_merge($temp_transactions, $shop_transactions);
                            }
                        }
                    }
                    else if ($periode==5 || $periode==6){

                        $from = ($periode==5)? new \DateTime('first day of this month') : new \DateTime('first day of January ' . date('Y'));
                        $to = ($periode==5)? new \DateTime('last day of this month') : new \DateTime('last day of December ' . date('Y'));

                        $temp_transactions = array();
                        foreach ($shops as $i => $shop) {
                            $curtr = $transactions_repo
                                ->createQueryBuilder('e')
                                ->select('e')
                                ->where('e.shop = :shop_id and  e.date BETWEEN :periode AND :today')
                                ->setParameter('today', $to)
                                ->setParameter('shop_id', $shop)
                                ->setParameter('periode', $from)
                                ->orderBy('e.date', 'DESC')
                                ->getQuery()
                                ->getArrayResult();
                            $temp_transactions = array_merge($temp_transactions, $curtr);
                        }
                    }
                    else if ($periode==7 || $periode==8){
                        $from = ($periode==7)? new \DateTime('first day of previous month') : new \DateTime('first day of January ' . date("Y",strtotime("-1 year")));
                        $to = ($periode==7)? new \DateTime('last day of previous month') : new \DateTime('last day of December ' . date("Y",strtotime("-1 year")));
                        $temp_transactions = array();
                        foreach ($shops as $i => $shop) {
                            $curtr = $transactions_repo
                                ->createQueryBuilder('e')
                                ->select('e')
                                ->where('e.shop = :shop_id and e.date BETWEEN :periode AND :today')
                                ->setParameter('shop_id', $shop)
                                ->setParameter('today', $to)
                                ->setParameter('periode', $from)
                                ->orderBy('e.date', 'DESC')
                                ->getQuery()
                                ->getArrayResult();
                            $temp_transactions = array_merge($temp_transactions, $curtr);
                        }
                    }
                    else{
                        $periode = date('Y-m-d h:i:s', strtotime($periode . " days"));
                        $temp_transactions = array();
                        foreach ($shops as $i => $shop) {
                            $curtr = $transactions_repo
                                ->createQueryBuilder('e')
                                ->select('e')
                                ->where('e.shop = :shop_id and  e.date BETWEEN :periode AND :today')
                                ->setParameter('shop_id', $shop)
                                ->setParameter('today', date('Y-m-d h:i:s'))
                                ->setParameter('periode', $periode)
                                ->orderBy('e.date', 'DESC')
                                ->getQuery()
                                ->getArrayResult();
                            $temp_transactions = array_merge($temp_transactions, $curtr);
                        }

                    }
                    if($periode!=0) {
                        foreach ($temp_transactions as $i => $tr) {
                            $tr_tep = $transactions_repo->findOneBy(array(
                                'id' => $tr['id'],
                                'type' => 'used'
                            ));
                            if ($tr_tep)
                                $transactions[] = $tr_tep;
                        }
                    }else{
                        foreach ($temp_transactions as $i => $tr) {
                            $tr_tep = $transactions_repo->findOneBy(array(
                                'id' => $tr->getId(),
                                'type' => 'used'
                            ));
                            if ($tr_tep)
                                $transactions[] = $tr_tep;
                        }
                    }
                }
                else if ($request->isMethod('get') && $request->get('from') &&$request->get('to') ){
                    $from = new \DateTime($request->get('from'));
                    $to = date($request->get('to'));
                    $to=date('Y-m-d H:i:s', strtotime($to . ' +1 day'));
                    $to=new \DateTime($to);

                    $temp_transactions = array();
                    foreach ($shops as $i => $shop) {
                        $curtr = $transactions_repo
                            ->createQueryBuilder('e')
                            ->select('e')
                            ->where('e.shop = :shop_id and e.date BETWEEN :from AND :to')
                            ->setParameter('shop_id', $shop)
                            ->setParameter('to', $to)
                            ->setParameter('from', $from)
                            ->orderBy('e.date', 'DESC')
                            ->getQuery()
                            ->getArrayResult();
                        $temp_transactions = array_merge($temp_transactions, $curtr);
                    }
                    foreach ($temp_transactions as $i =>  $tr){
                        $tr_tep = $transactions_repo->findOneBy(array(
                            'id' => $tr['id'],
                            'type' => 'used'
                        ));
                        if($tr_tep)
                            $transactions[]=$tr_tep;
                    }

                }
                else {
                    $transactions = array();
                    foreach ($shops as $i => $shop) {
                        $curtr = $transactions_repo->findBy(
                            ['shop' => $shop],
                            ['date' => 'DESC']
                        );
                        $transactions = array_merge($transactions, $curtr);
                    }

                }

            }
        } else {
            $transactions = $user->getTransactions();
        }

        return $this->render('transactions/index.html.twig', [
            'transactions' => $transactions,
            'my' => $my
        ]);
    }


    /**
     * @Route("/transactions/mes_transactions", name="my_transactions")
     */
    public function my_transactions()
    {
        return $this->forward('App\Controller\TransactionsController::transactions', ['my' => true]);
    }
}