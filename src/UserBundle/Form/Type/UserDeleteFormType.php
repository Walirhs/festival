<?php

namespace UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserDeleteFormType
 *
 * Crée un formulaire tout simple qui permet la suppression d'un élément (quel qu'il soit)
 * Une vue générique existe aussi : AppBundle\Resources\views\elements\delete_icon.html.twig
 *
 * @package UserBundle\Form\Type
 */
class UserDeleteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($options['action_url'])
                ->setMethod('DELETE')
                ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('action_url')->setRequired('action_url')->setDefault('action_url', null);
    }

    public function getBlockPrefix()
    {
        return 'user_bundle_user_delete_form_type';
    }
}
