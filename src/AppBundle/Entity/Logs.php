<?php

namespace AppBundle\Entity;

use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Doctrine\ORM\Mapping as ORM;
use SosthenG\EntityPortationBundle\Annotation\EntityPortation;
use SosthenG\EntityPortationBundle\Annotation\PortableMethod;
use SosthenG\EntityPortationBundle\Annotation\PortableProperty;

/**
 * @ORM\Table(name="GRP_logs")
 * @ORM\Entity(repositoryClass="Gedmo\Loggable\Entity\Repository\LogEntryRepository")
 * @EntityPortation(csvDelimiter=";", fallBackValue="N/A", sheetTitle="Logs")
 */
class Logs extends AbstractLogEntry
{
    /**
     * @PortableMethod(label="Date / Heure", methodType="GETTER", property="loggedAt", position="0")
     */
    public function getLoggedAt()
    {
        return parent::getLoggedAt();
    }

    /**
     * @PortableMethod(label="Utilisateur", methodType="GETTER", property="username", position="1")
     */
    public function getUsername()
    {
        return parent::getUsername();
    }

    /**
     * @return string
     * @PortableMethod(label="Action", methodType="GETTER", property="action", position="2")
     */
    public function getActionLabel()
    {
        switch ($this->action) {
            case 'create':
                return 'Création';
                break;
            case 'update':
                $label = 'Modification : ';
                foreach ($this->getData() as $editedVal => $values) {
                    $label .= $editedVal . ', ';
                }
                return trim($label, ', ');
                break;
            case 'remove':
                return 'Suppression';
                break;
            default:
                return $this->action;
                break;
        }
    }

    /**
     * @return string
     * @PortableMethod(label="Objet", methodType="GETTER", property="objectClass", position="3")
     */
    public function getObjectClassName()
    {
        return explode('\\', $this->objectClass)[2];
    }

    /**
     * @return string
     * @PortableMethod(label="Identifiant / Nom", methodType="GETTER", property="objectId", position="4")
     */
    public function getObjectName()
    {
        if ($this->getObjectClassName() == 'Groupe')
            return !empty($this->data['nom']) ? $this->objectId.' / '.$this->data['nom'] : $this->objectId;
        else
            return !empty($this->data['nom']) && !empty($this->data['prenom']) ? $this->objectId.' / '.$this->data['prenom'].' '.$this->data['nom'] : $this->objectId;
    }
}