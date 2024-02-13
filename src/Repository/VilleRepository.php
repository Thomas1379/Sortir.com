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
  
    //Recherche par nom ou par code Postal
    public function searchByNomOrCodePostal($searchTerm)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        if ($searchTerm) {
            $queryBuilder
                ->andWhere('v.nom LIKE :searchTerm OR v.codePostal LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        return $queryBuilder->getQuery()->getResult();
    }


    // Recherche si une ville (avec le même nom et le même code postal) existe déjà
   /* public function findByNomAndCodePostal(string $nom, string $codePostal): ?Ville
    {
        return $this->findOneBy(['nom' => $nom, 'codePostal' => $codePostal]);
    }*/

}
