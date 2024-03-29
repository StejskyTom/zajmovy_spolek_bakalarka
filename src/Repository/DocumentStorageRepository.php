<?php

namespace App\Repository;

use App\Entity\DocumentStorage;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentStorage>
 *
 * @method DocumentStorage|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentStorage|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentStorage[]    findAll()
 * @method DocumentStorage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentStorageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentStorage::class);
    }

    public function add(DocumentStorage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DocumentStorage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return DocumentStorage[]
     */
    public function getPublicDocumentStorageOrderByCreatedAt():array
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.files', 'f')
            ->where('d.public = :isPublic')
            ->setParameter('isPublic', true)
            ->orderBy('d.createdAt', 'DESC')
            ->addSelect('f')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return DocumentStorage[] Returns an array of DocumentStorage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DocumentStorage
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
