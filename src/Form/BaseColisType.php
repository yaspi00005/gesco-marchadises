<?php

namespace App\Form;

use App\Entity\BaseColis;
use App\Entity\Expeditions;
use App\Entity\User;
use App\Repository\ExpeditionsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseColisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateReceptions', null, [
                'widget' => 'single_text',
            ])

            ->add('typeExpeditions', ChoiceType::class, ['choices' => ['Standard' => 'Standard', 'Express ' => 'Express '], 'placeholder' => 'Choisir'])
            ->add('destinateurs')
            ->add('expeditions', EntityType::class, [
                'attr' => ['class' => 'searchInput'],
                'class' => Expeditions::class,
                'query_builder' => function (ExpeditionsRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('p')
                        ->andWhere('p.receptions = :val')
                        ->setParameter('val', 0);
                },
                'choice_label' => 'numeroExpeditions',
                'placeholder' => 'Choisir une expédition',
            ])

            ->add('destinateurs', EntityType::class, [
                'attr' => ['class' => 'searchInput'],
                'class' => User::class,
                'query_builder' => function (UserRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('p')
                        /* ->andWhere('p.receptions = :val')
                        ->setParameter('val', 0) */;
                },
                'choice_label' => function (User $user) {
                    return $user->getPrenom() . ' ' . $user->getNom() . '(' . $user->getUsename() . ')';

                    // or better, move this logic to Customer, and return:
                    // return $customer->getFullname();
                },
                'placeholder' => 'Choisir un client',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BaseColis::class,
            'csrf_protection' => false,
        ]);
    }
}
