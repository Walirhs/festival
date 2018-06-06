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

use FOS\UserBundle\Form\Type\RegistrationFormType;
use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // On rend le champ mot de passe facultatif
        $passwordField = $builder->get('plainPassword');
        $optionsPassword = $passwordField->getOptions();
        $typePassword = get_class($passwordField->getType()->getInnerType());
        $optionsPassword['required'] = false;
        $builder->add('plainPassword', $typePassword, $optionsPassword);
    }

    public function getParent()
    {
        return UserFormType::class;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                                   'validation_groups' => array('Default'),
                               ));
    }
}
