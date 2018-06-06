<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Association;
use AppBundle\Entity\Groupe;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadGroupeData implements FixtureInterface
{
    /**
     * Création de groupes de test
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        $tabGroupes = array('L3' => array(), 'M1' => array(), 'M2' => array());

        foreach ($tabGroupes as $niv => $grps) {
            foreach (array('APP', 'CLA') as $type) {
                $grp = (new Groupe())->setNom($niv.' MIAGE '.$type)->setDescription('Groupe '.$niv.' MIAGE '.$type);
                $manager->persist($grp);
                $tabGroupes[$niv][$type] = $grp;
            }

            $grpMaster = (new Groupe())->setNom($niv.' MIAGE')->setDescription('Groupe '.$niv.' MIAGE');
            $assoc = (new Association())->setGroupe($grpMaster)->setType('UNION');
            foreach ($tabGroupes[$niv] as $type => $grp) {
                $assoc->addGroupesAssocy($grp);
            }
            $grpMaster->addAssociation($assoc);
            $manager->persist($grpMaster);

            $tabGroupes[$niv] = $grpMaster;
        }

        // Groupe MIAGE
        $grpMiage = (new Groupe())->setNom('MIAGE')->setDescription('Formation MIAGE');
        $assoc = (new Association())->setGroupe($grpMiage)->setType('UNION');
        foreach ($tabGroupes as $niv => $grp) {
            $assoc->addGroupesAssocy($grp);
        }
        $grpMiage->addAssociation($assoc);
        $manager->persist($grpMiage);

        // Groupe Profs
        $grpProfs = (new Groupe())->setNom('Profs MIAGE')->setDescription('Professeurs de MIAGE');
        $manager->persist($grpProfs);

        // Groupe global
        $uNanterre = (new Groupe())->setNom('Université Paris Nanterre')->setDescription('Groupe par défaut');
        $assoc = (new Association())->setGroupe($uNanterre)->setType('UNION')
            ->addGroupesAssocy($grpMiage)
            ->addGroupesAssocy($grpProfs)
            ;
        $uNanterre->addAssociation($assoc);
        $manager->persist($uNanterre);

        $manager->flush();
    }
}