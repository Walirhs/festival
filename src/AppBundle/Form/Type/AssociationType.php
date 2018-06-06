<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Association;
use AppBundle\Entity\Groupe;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, array(
                            'label'   => "Type d'association",
                            'choices' => array(
                                'Union'        => 'UNION',
                                'Intersection' => 'INTERSECTION',
                            )
                        )
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $assoc = $event->getData();
            $event->getForm()->add('groupesAssocies', EntityType::class, array(
                                                        'class'         => Groupe::class,
                                                        'choice_label'  => 'nom',
                                                        'multiple'      => true,
                                                        'query_builder' => function(EntityRepository $er) use ($assoc) {
                                                            $qb = $er->createQueryBuilder('g');
                                                            if (!empty($assoc)) {
                                                                $qb->where('g != :groupe')->setParameter('groupe', $assoc->getGroupe());
                                                            }
                                                            return $qb;
                                                        },
                                                    )
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                                   'data_class' => 'AppBundle\Entity\Association'
                               ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_association';
    }
}
