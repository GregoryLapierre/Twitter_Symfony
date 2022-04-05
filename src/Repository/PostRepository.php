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
    private const perPage = 4;

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



    public function search($page = 1, $search = '', $order = 'popular')
    {
        $offset = ($page - 1) * self::perPage; // Nombre de Posts à partir duquel les récupérer

        $req = $this->createQueryBuilder('p')
            ->addSelect('count(c.id) AS countComments')
            ->leftJoin('p.comments', 'c')
            ->groupBy('p.id')
            ->andWhere('p.status != :status')
            ->setParameter('status', "moderated")
            ->setFirstResult($offset)
            ->setMaxResults(self::perPage)
        ;

        // Tri des posts
        if($order == 'new' || $order == 'popular') {
            $orderDirection = 'DESC';
        }
        else {
            $orderDirection = 'ASC';
        }

        if($order == 'new' || $order == 'old') {
            
            $req->orderBy('p.created_at', $orderDirection);
        }
        else{
            $req->orderBy('countComments', $orderDirection);
        }


        // Recherche par titre et hashtag
        if ($search) {
            $req->andWhere('p.title LIKE :search OR p.content LIKE :hashtag')
            ->setParameter('search', "%$search%")
            ->setParameter('hashtag', "%#$search%");
        }

        $posts = $req->getQuery()->getResult();

        // Assigne le nombre de commentaires aux posts
        foreach($posts as $post) {
            $post[0]->countComments = $post['countComments'];
        }
        $posts = array_column($posts, 0); // Récupère les objets Post (colonne [0])

        return $posts;
    }


    // Compte le nombre total de pages
    public function countPages($search = null)
    {
        $req = $this->createQueryBuilder('p')
            ->select('count(p.id) AS count')
            ->andWhere('p.status != :status')
            ->setParameter('status', "moderated");

        if ($search) {
            $req->andWhere('p.title LIKE :search OR p.content LIKE :hashtag')
            ->setParameter('search', "%$search%")
            ->setParameter('hashtag', "%#$search%");
        }

        $total = $req->getQuery()->getSingleScalarResult();
        
         // On calcule le nombre de pages total
         return ceil($total / self::perPage);
    }
}
