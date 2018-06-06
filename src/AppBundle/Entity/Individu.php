<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SosthenG\EntityPortationBundle\Annotation\EntityPortation;
use SosthenG\EntityPortationBundle\Annotation\PortableMethod;
use SosthenG\EntityPortationBundle\Annotation\PortableProperty;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Individu
 *
 * @ORM\Table(name="GRP_individu", uniqueConstraints={@UniqueConstraint(name="util_unique", columns={"identifiant", "statut_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IndividuRepository")
 * @Gedmo\Loggable(logEntryClass="AppBundle\Entity\Logs")
 * @UniqueEntity(fields={"identifiant", "statut"}, message="Un utilisateur existe déjà avec cet identifiant.")
 * @EntityPortation(csvDelimiter=";", fallBackValue="N/A", sheetTitle="Export des individus")
 */
class Individu
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
     * @ORM\Column(name="identifiant", type="string", length=50)
     * @Assert\NotBlank()
     *
     * @Gedmo\Versioned()
     *
     * @PortableProperty(label="Identifiant", position="0")
     */
    private $identifiant;

    /**
     * @var Statut
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Statut")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     *
     * @Gedmo\Versioned()
     */
    private $statut;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     * @Assert\NotBlank()
     *
     * @Gedmo\Versioned()
     *
     * @PortableProperty(label="Nom", position="3")
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255)
     * @Assert\NotBlank()
     *
     * @Gedmo\Versioned()
     *
     * @PortableProperty(label="Prénom", position="2")
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     *
     * @Gedmo\Versioned()
     *
     * @PortableProperty(label="Email", position="4")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="telephone", type="string", length=255)
     * @Assert\NotBlank()
     *
     * @Gedmo\Versioned()
     *
     * @PortableProperty(label="Téléphone", position="5")
     */
    private $telephone;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_naissance", type="date", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Date()
     *
     * @Gedmo\Versioned()
     *
     * @PortableProperty(label="Date de naissance", position="6", getter="getDateNaissanceFormatted")
     */
    private $dateNaissance;

    /**
     * @var Groupe[]
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Groupe", mappedBy="individus", cascade={"persist"})
     */
    private $groupes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groupes = new ArrayCollection();
    }

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
     * Set identifiant
     *
     * @param string $identifiant
     *
     * @return Individu
     */
    public function setIdentifiant($identifiant)
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    /**
     * Get identifiant
     *
     * @return string
     */
    public function getIdentifiant()
    {
        return $this->identifiant;
    }

    /**
     * Set statut
     *
     * @param Statut $statut
     *
     * @return Individu
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut
     *
     * @return Statut
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Get statut libelle
     *
     * @return string
     *
     * @PortableMethod(label="Statut", position="1")
     */
    public function getStatutLibelle()
    {
        return (!empty($this->statut)) ? $this->statut->getLibelle() : 'N/A';
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Individu
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return Individu
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Get prenomNom
     *
     * @return string
     */
    public function getPrenomNom()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Individu
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     *
     * @return Individu
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set dateNaissance
     *
     * @param \DateTime $dateNaissance
     *
     * @return Individu
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * Get dateNaissance
     *
     * @return \DateTime
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * Get dateNaissanceFormatted
     *
     * @return string
     */
    public function getDateNaissanceFormatted($format = 'Y-m-d')
    {
        return $this->dateNaissance->format($format);
    }

    /**
     * Add groupe
     *
     * @param Groupe $groupe
     *
     * @return Individu
     */
    public function addGroupe(Groupe $groupe)
    {
        $this->groupes[] = $groupe;

        $groupe->addIndividus($this);

        return $this;
    }

    /**
     * Remove groupe
     *
     * @param Groupe $groupe
     *
     * @return Individu
     */
    public function removeGroupe(Groupe $groupe)
    {
        $this->groupes->removeElement($groupe);

        $groupe->removeIndividus($this);

        return $this;
    }

    /**
     * Get groupes
     *
     * @return ArrayCollection|Groupe[]
     */
    public function getGroupes()
    {
        return $this->groupes;
    }

    /**
     * Récupère tous les groupes dans lesquels est l'utilisateur, y compris indirectement
     *
     * @return ArrayCollection|Groupe[]
     */
    public function getAllGroupes() {
        $groupes = $this->groupes;

        foreach ($this->groupes as $groupe) {
            foreach ($groupe->getAssociationsHeritees() as $association) {
                if (!$groupes->contains($association->getGroupe())) {
                    if ($association->getType() == 'UNION') {
                        $groupes->add($association->getGroupe());
                    }
                    elseif ($association->getType() == 'INTERSECTION') {
                        if ($association->getIndividusGroupesAssocies()->contains($this)) {
                            $groupes->add($association->getGroupe());
                        }
                    }
                }
            }
        }

        return $groupes;
    }
}
