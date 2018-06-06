<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Statut;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadStatutData implements FixtureInterface
{
    /**
     * Crée quelques statuts par défaut
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist((new Statut())->setLibelle("Etudiant")->setTypeIdentifiant("NumApogee"));
        $manager->persist((new Statut())->setLibelle("Professeur")->setTypeIdentifiant("IdProf"));
        $manager->persist((new Statut())->setLibelle("Intervenant")->setTypeIdentifiant("IdInter"));
        $manager->persist((new Statut())->setLibelle("Administration")->setTypeIdentifiant("IdAdmin"));

        $manager->flush();
    }
}