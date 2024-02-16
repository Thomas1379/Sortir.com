<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use function Symfony\Component\String\s;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Sortie::class);
        $this->security = $security;
    }

    public function allTables()
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->addOrderBy('s.dateHeureDebut', 'ASC');
        $queryBuilder->join('s.organisateur', 'orga');
        $queryBuilder->join('s.Etat', 'etat');
        $queryBuilder->join('s.Participant', 'part');
        $queryBuilder->addSelect('orga');
        $queryBuilder->addSelect('etat');
        $queryBuilder->addSelect('part');

        $query = $queryBuilder->getQuery();
        return $query;
    }

    public function searchByName($search)
    {
        $currentDate = new \DateTime();
        $currentDate->setTime(0, 0, 0);
        $user = $this->security->getUser();

        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder->addOrderBy('s.dateHeureDebut', 'ASC');
        $queryBuilder->join('s.organisateur', 'orga');
        $queryBuilder->join('s.Etat', 'etat');
        $queryBuilder->LeftJoin('s.Participant', 'part');
        $queryBuilder->addSelect('orga');
        $queryBuilder->addSelect('etat');
        $queryBuilder->addSelect('part');
        if ($search['campus'] != 'ANY' && !empty($search['campus']) && $search['campus'] !== 'Choisissez un campus') {
            $queryBuilder->andWhere('s.Campus = :campus');
            $queryBuilder->setParameter('campus', $search['campus']);
        }
        $queryBuilder->andWhere('s.nom LIKE :search');
        $queryBuilder->setParameter('search', '%' . $search['search'] . '%');

        $queryBuilder->andWhere('s.dateHeureDebut BETWEEN :date1 AND :date2');
        $queryBuilder->setParameter('date1', $search['date1']);
        $queryBuilder->setParameter('date2', $search['date2']);

        if (!empty($search['orga'])) {
            $queryBuilder->andWhere('s.organisateur = :orga');
            $queryBuilder->setParameter('orga', $search['orga']);
        }
        else {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    's.Etat != 4',
                    's.organisateur = :user'
                )
            );
            $queryBuilder->setParameter('user', $user);
        }

        if (!empty($search['inscrit']) && !empty($search['noinscrit'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    ':inscrit MEMBER OF s.Participant',
                    ':noinscrit NOT MEMBER OF s.Participant'
                )
            );
            $queryBuilder->setParameter('inscrit', $search['inscrit']);
            $queryBuilder->setParameter('noinscrit', $search['noinscrit']);
        } elseif (!empty($search['inscrit'])) {
            $queryBuilder->andWhere(':inscrit MEMBER OF s.Participant');
            $queryBuilder->setParameter('inscrit', $search['inscrit']);
        } elseif (!empty($search['noinscrit'])) {
            $queryBuilder->andWhere(':noinscrit NOT MEMBER OF s.Participant');
            $queryBuilder->setParameter('noinscrit', $search['noinscrit']);
        }

        if (!empty($search['passe'])) {
            $queryBuilder->andWhere('s.dateHeureDebut < :currentDate');
            $queryBuilder->setParameter('currentDate', $currentDate->format('Y-m-d'));
        } else {
            $queryBuilder->andWhere('s.dateHeureDebut > :currentDate');
            $queryBuilder->setParameter('currentDate', $currentDate->format('Y-m-d'));
        }



        if ($search['campus'] != 'ANY' && !empty($search['campus']) && $search['campus'] !== 'Choisissez un campus') {
            $queryBuilder->andWhere('s.Campus = :campus');
            $queryBuilder->setParameter('campus', $search['campus']);
        }

        $queryBuilder->andWhere('s.nom LIKE :search');
        $queryBuilder->setParameter('search', '%' . $search['search'] . '%');

        $queryBuilder->andWhere('s.dateHeureDebut BETWEEN :date1 AND :date2');
        $queryBuilder->setParameter('date1', $search['date1']);
        $queryBuilder->setParameter('date2', $search['date2']);

        if (!empty($search['orga'])) {
            $queryBuilder->andWhere('s.organisateur = :orga');
            $queryBuilder->setParameter('orga', $search['orga']);
        }
        else {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    's.Etat != 4',
                    's.organisateur = :user'
                )
            );
            $queryBuilder->setParameter('user', $user);
        }

        if (!empty($search['inscrit']) && !empty($search['noinscrit'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    ':inscrit MEMBER OF s.Participant',
                    ':noinscrit NOT MEMBER OF s.Participant'
                )
            );
            $queryBuilder->setParameter('inscrit', $search['inscrit']);
            $queryBuilder->setParameter('noinscrit', $search['noinscrit']);
        } elseif (!empty($search['inscrit'])) {
            $queryBuilder->andWhere(':inscrit MEMBER OF s.Participant');
            $queryBuilder->setParameter('inscrit', $search['inscrit']);
        } elseif (!empty($search['noinscrit'])) {
            $queryBuilder->andWhere(':noinscrit NOT MEMBER OF s.Participant');
            $queryBuilder->setParameter('noinscrit', $search['noinscrit']);
        }

        if (!empty($search['passe'])) {
            $queryBuilder->andWhere('s.dateHeureDebut < :currentDate');
            $queryBuilder->setParameter('currentDate', $currentDate->format('Y-m-d'));
        } else {
            $queryBuilder->andWhere('s.dateHeureDebut > :currentDate');
            $queryBuilder->setParameter('currentDate', $currentDate->format('Y-m-d'));
        }



        $query = $queryBuilder->getQuery();
        return $query;
    }
}
