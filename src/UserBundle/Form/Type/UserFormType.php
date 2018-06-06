<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Form\Type;

use AppBundle\Entity\Groupe;
use FOS\UserBundle\Form\Type\RegistrationFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('roles', CollectionType::class, array(
                             'entry_type' => ChoiceType::class,
                             'entry_options' => array(
                                 'label' => 'Rôle',
                                 'placeholder' => 'Choisissez un rôle',
                                 'choices' => array('Super-Administrateur' => 'ROLE_SUPER_ADMIN', 'Administrateur' => 'ROLE_ADMIN', 'Utilisateur' => 'ROLE_USER')
                             ),
                             'label' => false
                         )
            )
            ->add('groupesAcces', EntityType::class, array(
                'class'        => Groupe::class,
                'choice_label' => 'nom',
                'label'  => "Groupes accessibles à l'utilisateur",
                'multiple'     => true,
                'required'     => false,
            ));
    }

    public function getParent()
    {
        return RegistrationFormType::class;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                                   'validation_groups' => array('Default', 'Creation'),
                               ));
    }
}
