<?php

namespace App\Form;

use App\Entity\Expeditions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpeditionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('modeTransport', ChoiceType::class, ['choices' => ['Aérien' => 'Aérien', 'Maritime ' => 'Maritime '], 'placeholder' => 'Choisir'])
            ->add('destinations', ChoiceType::class, ['choices' => ['Mali' => 'Mali', 'Sénégal ' => 'Sénégal '], 'placeholder' => 'Choisir']) 
              ->add('dateExpeditions', null, [
                'widget' => 'single_text',
            ])
           /*  ->add('dateArrive', null, [
                'widget' => 'single_text',
            ])  */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expeditions::class,
        ]);
    }
}
