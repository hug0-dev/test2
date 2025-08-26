<?php

namespace App\Form;

use App\Entity\Affectation;
use App\Entity\Chantier;
use App\Entity\Equipe;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\EntityRepository;

class AffectationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom affectation',
                'attr' => ['class' => 'form-control']
            ])
            ->add('date_debut', DateType::class, [
                'label' => 'Date de Début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('date_fin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('chantier', EntityType::class, [
                'class' => Chantier::class,
                'choice_label' => function (Chantier $chantier) {
                    return $chantier->getNom() . ' - Effectif requis : ' . $chantier->getEffectifRequis();
                },
                'attr' => ['class' => 'form-control'],
                'disabled' => true,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getNom() . ' - Compétences : ' . implode(', ', $user->getCompetences());
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_USER%');
                },
                'label' => 'Utilisateur',
                'placeholder' => 'Sélectionnez un utilisateur',
                'attr' => ['class' => 'form-control']
            ])
            ->add('equipe', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => function(Equipe $equipe) {
                    return $equipe->getNomEquipe() . ' - Compétences : ' . implode(', ', $equipe->getCompetanceEquipe()) . ' - Nombre : ' . $equipe->getNombre();
                },
                'label' => 'Équipe',
                'placeholder' => 'Sélectionnez une équipe',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Callback([$this, 'validateCompatibility'])
                ]
            ]);
    }

    public function validateCompatibility($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $chantier = $form->get('chantier')->getData();
        $user = $form->get('user')->getData();
        
        if ($chantier && $user) {
            // Vérifier que l'utilisateur a au moins une compétence requise pour le chantier
            $competencesRequises = $chantier->getChantierPrerequis();
            $competencesUser = $user->getCompetences();

            if (empty($competencesUser) || empty(array_intersect($competencesRequises, $competencesUser))) {
                $context->buildViolation('L\'utilisateur doit posséder au moins une des compétences requises pour ce chantier.')
                    ->atPath('user')
                    ->addViolation();
            }
        }

        if ($chantier && $value) {
            // Vérifier que l'équipe a les compétences requises
            $competencesRequises = $chantier->getChantierPrerequis(); 
            $competencesEquipe = $value->getCompetanceEquipe(); 

            if (empty($competencesEquipe) || empty(array_intersect($competencesRequises, $competencesEquipe))) {
                $context->buildViolation('L\'équipe doit posséder au moins une des compétences requises du chantier.')
                    ->atPath('equipe')
                    ->addViolation();
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Affectation::class,
        ]);
    }
}