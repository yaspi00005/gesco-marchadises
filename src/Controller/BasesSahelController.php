<?php

namespace App\Controller;

use App\Entity\BasesSahel;
use App\Form\BasesSahelType;
use App\Repository\BasesSahelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/base/gestions/sahel/view')]
final class BasesSahelController extends AbstractController
{
    #[Route(name: 'app_bases_sahel_index', methods: ['GET'])]
    public function index(BasesSahelRepository $basesSahelRepository): Response
    {
        return $this->render('bases_sahel/index.html.twig', [
            'bases_sahels' => $basesSahelRepository->findAll(),
        ]);
    }

   

    #[Route('/{id}', name: 'app_bases_sahel_show', methods: ['GET'])]
    public function show(BasesSahel $basesSahel): Response
    {
        return $this->render('bases_sahel/show.html.twig', [
            'bases_sahel' => $basesSahel,
        ]);
    }


    #[Route('/{id}', name: 'app_bases_sahel_delete', methods: ['POST'])]
    public function delete(Request $request, BasesSahel $basesSahel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$basesSahel->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($basesSahel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_bases_sahel_index', [], Response::HTTP_SEE_OTHER);
    }
}
