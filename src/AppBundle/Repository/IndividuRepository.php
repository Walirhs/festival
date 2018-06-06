<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Individu;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * IndividuRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class IndividuRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Permet d'effectuer une recherche tout en retournant une réponse paginée et triée
     *
     * @param int    $page
     * @param int    $nbParPage
     * @param string $recherche
     * @param array  $tris
     *
     * @return Paginator
     */
    public function search($page = 1, $nbParPage = 10, $recherche = '', array $tris = array('i.identifiant' => 'ASC'))
    {
        $queryBuilder = $this->createQueryBuilder('i')
                      ->leftJoin('i.statut', 's')->addSelect('s');

        if (!empty($recherche)) {
            $queryBuilder
                ->where("i.identifiant LIKE :recherche")
                ->orWhere("s.libelle LIKE :recherche")
                ->orWhere("i.prenom LIKE :recherche")
                ->orWhere("i.nom LIKE :recherche")
                ->orWhere("i.email LIKE :recherche")
                ->orWhere("i.dateNaissance LIKE :recherche")
                ->setParameter('recherche', "%".$recherche."%");
        }

        foreach ($tris as $tri => $ordre) {
            $queryBuilder->addOrderBy($tri, $ordre);
        }

        $query = $queryBuilder->getQuery();

        $query->setFirstResult(($page-1) * $nbParPage)
              ->setMaxResults($nbParPage);

        return new Paginator($query);
    }

    /**
     * @param string $prenom
     * @param string $nom
     * @param string $email
     * @param string $telephone
     *
     * @return Individu|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findSimilarity($prenom = '', $nom = '', $email = '', $telephone = '')
    {
        $queryBuilder = $this->createQueryBuilder('i')->setMaxResults(1);
        if (!empty($prenom) && !empty($nom)) {
            $queryBuilder->where('i.prenom LIKE :prenom AND i.nom LIKE :nom')
                         ->setParameter('prenom', $prenom)
                         ->setParameter('nom', $nom);
        }
        if (!empty($email)) {
            $queryBuilder->orWhere('i.email LIKE :email')
                         ->setParameter('email', $email);
        }
        if (!empty($telephone)) {
            $queryBuilder->orWhere('i.telephone = :telephone')
                         ->setParameter('telephone', $telephone);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
