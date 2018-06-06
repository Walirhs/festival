<?php
namespace UserBundle\DataFixtures\ORM;

use UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserData implements FixtureInterface
{
    /**
     * Création d'utilisateurs de base pour tester l'application
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $superadmin = new User();
        $superadmin->setUsername("SuperAdmin")->setEmail("superadmin@admin.admin")->setPlainPassword("superadmin")->setSuperAdmin(true)->setEnabled(true);
        $manager->persist($superadmin);

        $admin = new User();
        $admin->setUsername("Admin")->setEmail("admin@admin.admin")->setPlainPassword("admin")->addRole('ROLE_ADMIN')->setEnabled(true);
        $manager->persist($admin);

        $user = new User();
        $user->setUsername("User")->setEmail("user@user.user")->setPlainPassword("user")->addRole('ROLE_USER')->setEnabled(true);
        $manager->persist($user);

        $manager->flush();
    }
}