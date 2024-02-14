<?php

namespace App\Repository;

use App\Entity\Ville;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ville>
 *
 * @method Ville|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ville|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ville[]    findAll()
 * @method Ville[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ville::class);
    }

    public function getAllVille()
    {
        $queryBuilder = $this->createQueryBuilder('v');
        $queryBuilder->addOrderBy('v.nom', 'DESC');
        $query = $queryBuilder->getQuery();
        $query->setMaxResults(500);
        $results = $query->getResult();
        return $results;
    }

    //Recherche par nom ou par code Postal
    public function searchByNomOrCodePostal($searchTerm)
    {
        $queryBuilder = $this->createQueryBuilder('v');
        $queryBuilder
            ->where('v.nom LIKE :searchTerm OR v.codePostal LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $queryBuilder->getQuery()->getResult();
    }

}
