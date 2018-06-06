<?php

namespace UserBundle\Entity;

use AppBundle\Entity\Groupe;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use SosthenG\EntityPortationBundle\Annotation\EntityPortation;
use SosthenG\EntityPortationBundle\Annotation\PortableMethod;

/**
 * @ORM\Table(name="GRP_user")
 * @ORM\Entity
 * @UniqueEntity(fields="username", message="Un utilisateur existe déjà avec cet identifiant.")
 * @UniqueEntity(fields="email", message="Un utilisateur existe déjà avec cette adresse email.")
 * @EntityPortation(csvDelimiter=";", sheetTitle="Export des utilisateurs", fallBackValue="N/A")
 */
class User extends BaseUser
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="fos_user.username.blank")
     * @Assert\Length(min=2, max=50, minMessage="fos_user.username.short", maxMessage="fos_user.username.long")
     */
    protected $username;

    /**
     * @Assert\NotBlank(message="fos_user.email.blank")
     * @Assert\Email(message="fos_user.email.invalid")
     * @Assert\Length(min=2, max=180, minMessage="fos_user.email.short", maxMessage="fos_user.email.long")
     */
    protected $email;

    /**
     * @Assert\NotBlank(message="fos_user.password.blank", groups={"Creation"})
     * @Assert\Length(min=2, max=4096, minMessage="fos_user.password.short")
     */
    protected $plainPassword;

    /**
     * @var ArrayCollection|Groupe[]
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Groupe", inversedBy="usersAutorises", cascade={"persist"})
     * @ORM\JoinTable(name="GRP_groupe_acces")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $groupesAcces;

    /**
     * Retourne le rôle maximum dans un format lisible
     *
     * @return string
     * @PortableMethod(label="Rôle", position="2")
     */
    public function getHigherRoleText() {
        if ($this->hasRole('ROLE_SUPER_ADMIN')) {
            return 'Super Administrateur';
        }
        elseif ($this->hasRole('ROLE_ADMIN')) {
            return 'Administrateur';
        }
        elseif ($this->hasRole('ROLE_USER')) {
            return 'Utilisateur';
        }

        return '';
    }

    /**
     * @inheritdoc
     * @PortableMethod(label="Identifiant", position="0")
     */
    public function getUsername()
    {
        return parent::getUsername();
    }

    /**
     * @inheritdoc
     * @PortableMethod(label="Adresse email", position="1")
     */
    public function getEmail()
    {
        return parent::getEmail();
    }

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        $roles = parent::getRoles();
        if (in_array('ROLE_SUPER_ADMIN', $roles)) {
            return array('ROLE_SUPER_ADMIN');
        }
        elseif (in_array('ROLE_ADMIN', $roles)) {
            return array('ROLE_ADMIN');
        }
        elseif (in_array('ROLE_USER', $roles)) {
            return array('ROLE_USER');
        }

        return $roles;
    }

    /**
     * Get groupes accessibles
     *
     * @return ArrayCollection|Groupe[]
     */
    public function getGroupesAcces()
    {
        return $this->groupesAcces;
    }

    /**
     * Add groupesAcce
     *
     * @param \AppBundle\Entity\Groupe $groupesAcce
     *
     * @return User
     */
    public function addGroupesAcce(\AppBundle\Entity\Groupe $groupesAcce)
    {
        $this->groupesAcces[] = $groupesAcce;

        return $this;
    }

    /**
     * Remove groupesAcce
     *
     * @param \AppBundle\Entity\Groupe $groupesAcce
     */
    public function removeGroupesAcce(\AppBundle\Entity\Groupe $groupesAcce)
    {
        $this->groupesAcces->removeElement($groupesAcce);
    }

    /**
     * @param Groupe $groupe
     *
     * @return bool
     */
    public function hasAccess(Groupe $groupe)
    {
        return $this->hasRole('ROLE_SUPER_ADMIN') || $this->groupesAcces->contains($groupe);
    }
}
