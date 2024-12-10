<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.user_id', 'u')  // Joindre la relation user_id
            ->leftJoin('c.clientNotes', 'cn') // Si vous avez d'autres relations, les inclure ici
            ->where('u.id = :user_id')
            ->setParameter('user_id', $user->getId())
            ->getQuery()
            ->getResult();
    }

    public function findClientsByUser(User $user)
{
    return $this->createQueryBuilder('c')
        ->innerJoin('c.users', 'u')
        ->where('u.id = :userId')
        ->setParameter('userId', $user->getId())
        ->getQuery()
        ->getResult();
}



    //    /**
    //     * @return Client[] Returns an array of Client objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Client
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
