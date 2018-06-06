<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Entity\User;

/**
 * Groupe
 *
 * @ORM\Table(name="GRP_groupe")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupeRepository")
 * @Gedmo\Loggable(logEntryClass="AppBundle\Entity\Logs")
 */
class Groupe
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
     * @ORM\Column(name="nom", type="string", length=255)
     *
     * @Gedmo\Versioned()
     *
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     *
     * @Gedmo\Versioned()
     *
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var ArrayCollection|Individu[]
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Individu", cascade={"persist"}, inversedBy="groupes")
     * @ORM\JoinTable(name="GRP_groupe_individu")
     */
    private $individus;

    /**
     * @var ArrayCollection|Association[]
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Association", mappedBy="groupe", orphanRemoval=true, cascade={"persist"})
     * @Assert\Valid(traverse=true)
     */
    private $associations;

    /**
     * @var ArrayCollection|Association[]
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Association", mappedBy="groupesAssocies")
     */
    private $associationsHeritees;

    /**
     * @var ArrayCollection|User[]
     *
     * @ORM\ManyToMany(targetEntity="UserBundle\Entity\User", mappedBy="groupesAcces", cascade={"persist"})
     */
    private $usersAutorises;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->individus = new ArrayCollection();
        $this->associations = new ArrayCollection();
        $this->associationsHeritees = new ArrayCollection();
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
    * Set id
    *
    * @param  id
    *
    * @return int
    */
    public function setId($id)
    {
        $this->id = $id;

        return $this->id;
    }
    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Groupe
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
     * Set description
     *
     * @param string $description
     *
     * @return Groupe
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add individus
     *
     * @param Individu $individus
     *
     * @return Groupe
     */
    public function addIndividus(Individu $individus)
    {
        $this->individus[] = $individus;

        return $this;
    }

    /**
     * Remove individus
     *
     * @param Individu $individus
     */
    public function removeIndividus(Individu $individus)
    {
        $this->individus->removeElement($individus);
    }

    /**
     * Get individus
     *
     * @return ArrayCollection|Individu[]
     */
    public function getIndividus()
    {
        return $this->individus;
    }

    /**
     * Get AllIndividus
     *
     * Retourne les individus en "dur" du groupe et ceux qui sont issus d'une association avec d'autres groupes
     *
     * @return ArrayCollection|Individu[]
     */
    public function getAllIndividus()
    {
        // Récupération des individus "en dur" du groupe
        $individus = $this->individus->toArray();

        // Pour chaque groupe hérité, on récupère leurs individus
        foreach ($this->associations as $association) {
            $individus = array_unique(array_merge($individus, $association->getIndividusGroupesAssocies()->toArray()), SORT_REGULAR);
        }

        return new ArrayCollection($individus);
    }

    /**
     * Add association
     *
     * @param Association $association
     *
     * @return Groupe
     */
    public function addAssociation(Association $association)
    {
        $association->setGroupe($this);

        $this->associations[] = $association;

        return $this;
    }

    /**
     * Remove association
     *
     * @param Association $association
     *
     * @return Groupe
     */
    public function removeAssociation(Association $association)
    {
        $this->associations->removeElement($association);

        $association->setGroupe(null);

        return $this;
    }

    /**
     * Get associations
     *
     * @return ArrayCollection|Association[]
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Get associationsHeritees
     *
     * @return ArrayCollection|Association[]
     */
    public function getAssociationsHeritees()
    {
        return $this->associationsHeritees;
    }

    /**
     * Get usersAutorises
     *
     * @return ArrayCollection|User[]
     */
    public function getUserAutorise()
    {
        return $this->usersAutorises;
    }

    /**
     * Add userAutorise
     *
     * @param User $user
     *
     * @return Groupe
     */
    public function addUserAutorise(User $user)
    {
        $this->usersAutorises[] = $user;

        $user->addGroupesAcce($this);

        return $this;
    }

    /**
     * Remove userAutorise
     *
     * @param User $user
     */
    public function removeUserAutorise(User $user)
    {
        $this->usersAutorises->removeElement($user);

        $user->removeGroupesAcce($this);
    }
}
