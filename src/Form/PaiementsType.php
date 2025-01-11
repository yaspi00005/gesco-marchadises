<?php

namespace App\Form;

use App\Entity\BaseColis;
use App\Entity\Paiements;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaiementsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montants')
            ->add('dateReception', null, [
                'widget' => 'single_text',
            ])
            ->add('modePaiement')
            ->add('colis', EntityType::class, [
                'class' => BaseColis::class,
                'choice_label' => 'id',
            ])
            ->add('caissier', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Paiements::class,
        ]);
    }
}
