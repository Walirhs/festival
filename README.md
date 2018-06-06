Pour faire marcher le projet :

1rst time :
- Lancer les deux VM
- Récupérer les modifs avec git, raccourci par défaut CTRL + T (ou VCS => Update Project)
- Attendre la fin de la récup
- Exécuter la commande suivante en étant positionné dans le dossier du projet : composer update
(Vous devez avoir installé composer et l'avoir ajouté à votre PATH, voir : https://getcomposer.org/doc/00-intro.md)

- Exécuter les commandes suivantes sur le serveur apache, dans cet ordre :
    - cd /var/www/html/TD_SI
    - php bin/console doctrine:schema:update --force
    - php bin/console fos:user:create
    - php bin/console fos:user:promote VOTRE_LOGIN ROLE_SUPER_ADMIN
    
- Allez à l'adresse http://localhost:8085/TD_SI/web/app_dev.php/ et vous devriez pouvoir vous connecter.
    

A faire en cas d'erreur

- Problème de DB :
    ==> Se connecter en root sur le serveur oracle (mdp : oracle), ouvrir un terminal et taper : yum clean all

- Problème d'affichage des pages :
    ==> Sur le serveur apache, taper : php bin/console cache:clear
