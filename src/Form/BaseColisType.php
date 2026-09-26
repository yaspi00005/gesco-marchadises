<?php

namespace App\Form;

use App\Entity\BaseColis;
use App\Entity\Clients;
use App\Entity\Expeditions;
use App\Entity\User;
use App\Repository\ClientsRepository;
use App\Repository\ExpeditionsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseColisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeExpeditions', HiddenType::class)
            ->add('destinateursNom',TextType::class )
            ->add('unites',NumberType::class )
            ->add('destinateursTelephones',TextType::class )
            ->add('expeditions', EntityType::class, [ 
                'attr' => ['class' => 'searchInput'],
                'class' => Expeditions::class,
                'query_builder' => function (ExpeditionsRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('p')
                        ->andWhere('p.statut = :val')
                        ->setParameter('val', 'En préparation');
                },
                'choice_label' => function ($expedition) {
                    return $expedition->getDestinations() . ' - ' . $expedition->getDateExpeditions()->format('d/m/Y');
                },
                'placeholder' => 'Choisir une expédition',
            ])
            

            ->add('clients', EntityType::class, [
                'attr' => ['class' => 'searchInput select2'],
                'class' => Clients::class,
                'query_builder' => function (ClientsRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('p')
                        /* ->andWhere('p.receptions = :val')
                        ->setParameter('val', 0) */;
                },
                'choice_label' => function (Clients $Clients) {
                    return $Clients->getPrenom() . ' ' . $Clients->getNom() . '(' . $Clients->getTelephone() . ')';

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
