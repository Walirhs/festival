<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Controller;


class SecurityController extends \FOS\UserBundle\Controller\SecurityController
{
    protected function renderLogin(array $data)
    {
        // Si on est déjà connecté, redirection sur la page groupsTree
        if ($this->isGranted("IS_AUTHENTICATED_REMEMBERED"))
            return $this->redirectToRoute("groupe_index");

        return $this->render('@FOSUser/Security/login.html.twig', $data);
    }
}
