<?php

namespace App\Repository;

use App\Entity\Transactions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Transactions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transactions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transactions[]    findAll()
 * @method Transactions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionsRepository extends ServiceEntityRepository
{
    
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Transactions::class);
    }

    public function findByUserId($user_id) {
        $query = $this->createQueryBuilder('a')
                      ->select('a')
                      ->leftJoin('a.user_id', 'c')
                      ->addSelect('c');
 
        $query = $query->add('where', $query->expr()->in('c', ':c'))
                      ->setParameter('c', $user_id)
                      ->orderBy('a.date', 'DESC')
                      ->getQuery()
                      ->getResult();
          
        return $query;
    }

    public function findToday() {
        $date = new \DateTime();
        $date->modify('today midnight');

        return $this->createQueryBuilder('t')
            ->andWhere('t.date > :date')
            ->setParameter(':date', $date)
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTodayByShop($shop) {
        $date = new \DateTime();
        $date->modify('today midnight');

        return $this->createQueryBuilder('t')
            ->where('t.date > :date')
            ->leftJoin('t.shop', 's')
            ->andWhere('s = :shop')
            ->setParameter(':date', $date)
            ->setParameter(':shop', $shop)
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    ///////// user trans
/*
    public function count_trans($id) {
    return 
    $qb = $entityManager->createQueryBuilder();
    $qb->select('count(transactions_user.transaction_id)');
    $qb->where('transactions_user.user_id = :id')->setParameter(':id', $id);
    $qb->from('ZaysoCoreBundle:Account','transactions_user');

    $count = $qb->getQuery()->getSingleScalarResult();
  }
*/    
  public function findAllByShop($shop) {
        $query = $this->createQueryBuilder('a')
                      ->select('a')
                      ->leftJoin('a.shop', 'c')
                      ->addSelect('c');
 
        $query = $query->add('where', $query->expr()->in('c', ':c'))
                      ->setParameter('c', $shop)
                      ->orderBy('a.date', 'DESC')
                      ->getQuery()
                      ->getResult();
          
        return $query;
    }
}
