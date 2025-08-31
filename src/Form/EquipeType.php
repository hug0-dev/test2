<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_equipe', null, [
                'label' => 'Nom de l\'équipe',
                'attr' => ['class' => 'form-control']
            ])
            ->add('chef_equipe', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',  
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_ADMIN%');
                },
                'label' => 'Chef d\'équipe',
                'placeholder' => 'Sélectionnez un chef d\'équipe',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            // Plus besoin de competance_equipe et nombre 
            // car ils sont calculés automatiquement
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
        ]);
    }
}