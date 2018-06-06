<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Individu;
use AppBundle\Form\Type\AssociationType;
use AppBundle\Repository\IndividuRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $groupe = $builder->getData();
        $builder->add('nom')
                ->add('description')
                ->add('individus', EntityType::class, array(
                    'class'        => Individu::class,
                    'choice_label' => 'prenomNom',
                    'placeholder'  => '',
                    'multiple'     => true,
                    'required'     => false,
                    'query_builder' => function(IndividuRepository $er) {
                        $qb = $er->createQueryBuilder('i')->orderBy('i.prenom', 'ASC')->addOrderBy('i.nom', 'ASC');
                        return $qb;
                    }
                ))
                ->add('associations', CollectionType::class, array(
                    'entry_type'    => AssociationType::class,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,
                    'label'         => false,
                    'entry_options' => array(
                        'label' => false
                    ),
                    'required'      => false
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                                   'data_class' => 'AppBundle\Entity\Groupe'
                               ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_groupe';
    }
}
