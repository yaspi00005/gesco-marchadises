<?php

namespace App\Controller;

use App\Entity\BaseColisDetails;
use App\Form\BaseColisDetailsType;
use App\Repository\BaseColisDetailsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/base/colis/details')]
final class BaseColisDetailsController extends AbstractController
{
    #[Route(name: 'app_base_colis_details_index', methods: ['GET'])]
    public function index(BaseColisDetailsRepository $baseColisDetailsRepository): Response
    {
        return $this->render('base_colis_details/index.html.twig', [
            'base_colis_details' => $baseColisDetailsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_base_colis_details_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $baseColisDetail = new BaseColisDetails();
        $form = $this->createForm(BaseColisDetailsType::class, $baseColisDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($baseColisDetail);
            $entityManager->flush();

            return $this->redirectToRoute('app_base_colis_details_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('base_colis_details/new.html.twig', [
            'base_colis_detail' => $baseColisDetail,
            'form' => $form,
        ]);
    }

  /*   #[Route('/{id}', name: 'app_base_colis_details_show', methods: ['GET'])]
    public function show(BaseColisDetails $baseColisDetail): Response
    {
        return $this->render('base_colis_details/show.html.twig', [
            'base_colis_detail' => $baseColisDetail,
        ]);
    } */

    #[Route('/{id}/edit', name: 'app_base_colis_details_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BaseColisDetails $baseColisDetail, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BaseColisDetailsType::class, $baseColisDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_base_colis_details_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('base_colis_details/edit.html.twig', [
            'base_colis_detail' => $baseColisDetail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_base_colis_details_delete', methods: ['POST'])]
    public function delete(Request $request, BaseColisDetails $baseColisDetail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$baseColisDetail->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($baseColisDetail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_base_colis_details_index', [], Response::HTTP_SEE_OTHER);
    }
}
