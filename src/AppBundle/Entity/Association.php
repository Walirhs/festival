<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Association
 *
 * @ORM\Table(name="GRP_association")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AssociationRepository")
 */
class Association
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
     * @ORM\Column(name="type", type="string", length=12)
     *
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @var Groupe
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Groupe", inversedBy="associations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank()
     */
    private $groupe;

    /**
     * @var ArrayCollection|Groupe[]
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Groupe", inversedBy="associationsHeritees", cascade={"persist"})
     * @ORM\JoinTable(name="GRP_association_groupe", joinColumns={ @ORM\JoinColumn(onDelete="CASCADE") })
     *
     * @Assert\NotBlank()
     * @Assert\Count(min="1", minMessage="Votre association doit contenir au moins un groupe.")
     */
    private $groupesAssocies;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groupesAssocies = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Association
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set type
     *
     * @param string $type
     *
     * @return Association
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set groupe
     *
     * @param Groupe $groupe
     *
     * @return Association
     */
    public function setGroupe(Groupe $groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return Groupe
     */
    public function getGroupe()
    {
        return $this->groupe;
    }
    /**
     * Add groupesAssocy
     *
     * @param Groupe $groupesAssocy
     *
     * @return Association
     */
    public function addGroupesAssocy(Groupe $groupesAssocy)
    {
        $this->groupesAssocies[] = $groupesAssocy;

        return $this;
    }

    /**
     * Remove groupesAssocy
     *
     * @param Groupe $groupesAssocy
     */
    public function removeGroupesAssocy(Groupe $groupesAssocy)
    {
        $this->groupesAssocies->removeElement($groupesAssocy);
    }

    /**
     * Get groupesAssocies
     *
     * @return ArrayCollection|Groupe[]
     */
    public function getGroupesAssocies()
    {
        return $this->groupesAssocies;
    }

    /**
     * Retourne la liste des individus hérités par l'association
     *
     * @return ArrayCollection|Individu[]
     */
    public function getIndividusGroupesAssocies() {
        $individus = array();
        $init = false;

        switch ($this->type) {
            case 'UNION':
                foreach ($this->groupesAssocies as $groupe) {
                    $individus = array_merge($individus, $groupe->getAllIndividus()->toArray());
                }
                break;
            case 'INTERSECTION':
                foreach ($this->groupesAssocies as $groupe) {
                    if (!$init) {
                        $individus = $groupe->getAllIndividus()->toArray();
                        $init = true;
                    }
                    else {
                        $newIndividus = array();
                        foreach ($groupe->getAllIndividus() as $individu) {
                            if (in_array($individu, $individus)) {
                                $newIndividus[] = $individu;
                            }
                        }
                        $individus = $newIndividus;
                    }
                }
                break;
        }

        return new ArrayCollection($individus);
    }
}
