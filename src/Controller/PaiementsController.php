<?php

namespace App\Controller;

use App\Entity\Paiements;
use App\Form\PaiementsType;
use App\Repository\PaiementsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/paiements')]
final class PaiementsController extends AbstractController
{
    #[Route(name: 'app_paiements_index', methods: ['GET'])]
    public function index(PaiementsRepository $paiementsRepository): Response
    {
        return $this->render('paiements/index.html.twig', [
            'paiements' => $paiementsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_paiements_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $paiement = new Paiements();
        $form = $this->createForm(PaiementsType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($paiement);
            $entityManager->flush();

            return $this->redirectToRoute('app_paiements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paiements/new.html.twig', [
            'paiement' => $paiement,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_paiements_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Paiements $paiement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PaiementsType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_paiements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paiements/edit.html.twig', [
            'paiement' => $paiement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_paiements_delete', methods: ['POST'])]
    public function delete(Request $request, Paiements $paiement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $paiement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($paiement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_paiements_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/rechercher-paiements', name: 'rechercher_paiements', methods: ['GET'])]
    public function rechercherPaiements(Request $request, PaiementsRepository $paiementsRepository): JsonResponse
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        if (!$startDate || !$endDate) {
            return new JsonResponse(['error' => 'Les dates sont obligatoires.'], 400);
        }

        $startDateTime = new \DateTime($startDate . ' 00:00:00');
        $endDateTime = new \DateTime($endDate . ' 23:59:59');

        $paiements = $paiementsRepository->findByDateRange($startDateTime, $endDateTime);
        /*  $paiements = $paiementsRepository->createQueryBuilder('p')
        ->where('p.dateReception BETWEEN :startDate AND :endDate')
        ->setParameter('startDate', $startDateTime)
        ->setParameter('endDate', $endDateTime)
        ->getQuery()
        ->getResult(); */

        $result = array_map(function (Paiements $paiement) {
            return [
                'montants' => $paiement->getMontants(),
                'dateReception' => $paiement->getDatePaiements()->format('d-m-Y H:i'),
                'modePaiement' => $paiement->getModePaiement(),
                'clients' => $paiement->getColis()->getDestinateurs()->getPrenom() . ' ' . $paiement->getColis()->getDestinateurs()->getNom(),
                'colis' => $paiement->getColis()->getNumeroSuivi(),
                'caissier' => $paiement->getCaissier()->getPrenom() . ' ' . $paiement->getCaissier()->getNom(),
            ];
        }, $paiements);

        return new JsonResponse(['paiements' => $result]);
    }
}
