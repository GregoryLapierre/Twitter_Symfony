<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Post $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Post $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findSearch($order = 'DESC')
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.created_at', $order)
            ->getQuery()
            ->getResult()
        ;
    }

    public function search($value)
    {   //SELECT * FROM post as l WHERE l.titre LIKE "%xxx%" ORDER BY l.titre, l.auteur
        return $this->createQueryBuilder('p')//le paramètre l représente la table post (comme un alias dans une requête SQL)
            ->where('p.title LIKE :val')
            ->setParameter('val', "%$value%")
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
