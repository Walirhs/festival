<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Statut
 *
 * @ORM\Table(name="GRP_statut")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StatutRepository")
 */
class Statut
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255, unique=true)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="type_identifiant", type="string", length=255)
     */
    private $typeIdentifiant;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Statut
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set typeIdentifiant
     *
     * @param string $typeIdentifiant
     *
     * @return Statut
     */
    public function setTypeIdentifiant($typeIdentifiant)
    {
        $this->typeIdentifiant = $typeIdentifiant;

        return $this;
    }

    /**
     * Get typeIdentifiant
     *
     * @return string
     */
    public function getTypeIdentifiant()
    {
        return $this->typeIdentifiant;
    }
}
