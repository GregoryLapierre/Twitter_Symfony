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
        $offset = ($page - 1) * self::perPage;
        
        if($order == 'new' || $order == 'popular'){
            $orderDirection = 'DESC';
        }
        else{
            $orderDirection = 'ASC';
        }

        $req = $this->createQueryBuilder('p')
            ->addSelect('count(c.id) AS countComments')
            ->leftJoin('p.comments', 'c')
            ->groupBy('p.id')
            ->setFirstResult($offset)
            ->setMaxResults(self::perPage)
        ;

        if($order == 'new' || $order == 'old'){
            
            $req->orderBy('p.created_at', $orderDirection);
        }
        else{
            $req->orderBy('countComments', $orderDirection);
        }

        if ($search) {
            $req->where('p.title LIKE :search')
            ->orWhere('p.content LIKE :hashtag')
            ->setParameter('search', "%$search%")
            ->setParameter('hashtag', "%#$search%");
        }

        $posts = $req->getQuery()->getResult();

        foreach($posts as $post){
            $post[0]->countComments = $post['countComments'];
        }
        $posts = array_column($posts, 0); // Récupère les objets Post

        return $posts;
    }

    public function countPages($search = null){
        $req = $this->createQueryBuilder('p')
            ->select('count(p.id) AS count');

        if ($search) {
            $req->where('p.title LIKE :search')
            ->orWhere('p.content LIKE :hashtag')
            ->setParameter('search', "%$search%")
            ->setParameter('hashtag', "%#$search%");
        }

        $total = $req->getQuery()->getSingleScalarResult();
        
         // On calcule le nombre de pages total
         return ceil($total / self::perPage);
    }
}
