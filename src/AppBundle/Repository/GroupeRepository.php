<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Groupe;
use UserBundle\Entity\User;

/**
 * GroupeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GroupeRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param User|null $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbFindAllAccessibles($user)
    {
        $qb = $this->createQueryBuilder('g');
        // Récupère les groupes accessibles pour l'utilisateur courant
        if ($user !== null && !$user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->innerJoin('g.usersAutorises', 'u')->where('u.id = :id_user')->setParameter('id_user', $user->getId());
        }

        return $qb;
    }

    /**
     * @param User $user
     *
     * @return array|Groupe[]
     */
    public function findAllAccessibles(User $user)
    {
        return $this->qbFindAllAccessibles($user)->getQuery()->getResult();
    }
}
