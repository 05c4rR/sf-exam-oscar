<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function filterByPrice(string $order = 'asc', int $page = 1, int $limit = 10)
    {
        $order = strtolower($order);
        if ($order !== 'asc' && $order !== 'desc') {
            throw new \InvalidArgumentException('Order parameter must be "asc" or "desc".');
        }
    
        return $this->createQueryBuilder('p')
            ->orderBy('p.price', $order)
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
    
    }
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }

    public function findBySearchAndFilter(string $name, string $order, int $page = 1, int $limit = 10)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('p.price', $order)
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
    
    }

    public function findByCategory(int $categoryId, string $order = 'asc')
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('p.price', $order);
    }
    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}