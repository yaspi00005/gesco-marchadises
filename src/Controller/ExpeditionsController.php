<?php

namespace App\Controller;

use App\Entity\Expeditions;
use App\Form\ExpeditionsType;
use App\Repository\ExpeditionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/expeditions')]
final class ExpeditionsController extends AbstractController
{
    #[Route(name: 'app_expeditions_index', methods: ['GET'])]
    public function index(ExpeditionsRepository $expeditionsRepository): Response
    {
        return $this->render('expeditions/index.html.twig', [
            'expeditions' => $expeditionsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_expeditions_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $expedition = new Expeditions();
        $form = $this->createForm(ExpeditionsType::class, $expedition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $expedition->setNumeroExpeditions(time());
            $entityManager->persist($expedition);
            $entityManager->flush();

            return $this->redirectToRoute('app_expeditions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('expeditions/new.html.twig', [
            'expedition' => $expedition,
            'form' => $form,
        ]);
    }

    #[Route('/{debut}/{fin}', name: 'app_expeditions_date', methods: ['GET'])]
    public function date($debut, $fin, ExpeditionsRepository $expeditionsRepository): Response
    {



        return $this->render('expeditions/index.html.twig', [
            'expeditions' => $expeditionsRepository->findBydate($debut, $fin),
        ]);
    }

    #[Route('/{id}', name: 'app_expeditions_show', methods: ['GET'])]
    public function show(Expeditions $expedition): Response
    {
        return $this->render('expeditions/show.html.twig', [
            'expedition' => $expedition,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_expeditions_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Expeditions $expedition, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExpeditionsType::class, $expedition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_expeditions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('expeditions/edit.html.twig', [
            'expedition' => $expedition,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_expeditions_delete', methods: ['POST'])]
    public function delete(Request $request, Expeditions $expedition, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $expedition->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($expedition);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_expeditions_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/expedition/{id}/update-status', name: 'app_expedition_update_status', methods: ['POST'])]
    public function updateStatus(
        Expeditions $expedition,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $newStatus = $request->request->get('statut');

        if (!$newStatus) {
            $this->addFlash('error', 'Statut non valide.');
            return $this->redirectToRoute('app_expeditions_index');
        }

        // Mettre à jour le statut de l'expédition
        $expedition->setStatut($newStatus);
        $entityManager->flush();

        $this->addFlash('success', 'Statut mis à jour avec succès.');
        return $this->redirectToRoute('app_expeditions_index');
    }
}
