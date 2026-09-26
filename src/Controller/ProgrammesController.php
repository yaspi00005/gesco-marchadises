<?php

namespace App\Controller;

use App\Entity\Clients;
use App\Entity\Programmes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ramassages')]
class ProgrammesController extends AbstractController
{
    #[Route('/programmes', name: 'app_programmes')]
    public function new(EntityManagerInterface $entityManager): Response
    {
        $clients = $entityManager->getRepository(Clients::class)->findAll();

        return $this->render('programmes/index.html.twig', [
            'clients' => $clients
        ]);
    }

    #[Route('/programmes/add', name: 'programmes_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = $entityManager->getRepository(Clients::class)->find($data['client_id']);
        if (!$client) {
            return new JsonResponse(["error" => "Client non trouvé"], 400);
        }

        $programme = new Programmes();
        $programme->setClients($client);
        $programme->setCommentaires($data['commentaires']);
        $programme->setDateRamassages(new \DateTime($data['dateRamassages']));
        $programme->setStatut('En attente');

        $entityManager->persist($programme);
        $entityManager->flush();

        return new JsonResponse(["success" => true]);
    }
    #[Route('/programmes/events', name: 'programmes_events', methods: ['GET'])]
    public function events(EntityManagerInterface $entityManager): JsonResponse
    {
        $programmes = $entityManager->getRepository(Programmes::class)->findAll();
        $events = [];

        foreach ($programmes as $programme) {
            if ($programme->getDateRamassages()) {
                $events[] = [
                    'id' => $programme->getId(),
                    'title' => $programme->getClients()->getNom(),
                    'start' => $programme->getDateRamassages()->format('Y-m-d\TH:i:s'),
                    'end' => $programme->getDateRamassages()->modify('+1 hour')->format('Y-m-d\TH:i:s'), // Durée de 1h
                    'className' => match ($programme->getStatut()) {
                        "Récupéré" => 'bg-success',
                        "Annulé" => 'bg-danger',
                        default => 'bg-warning'
                    }
                ];
            }
        }

        return new JsonResponse($events);
    }

    #[Route('/programmes/update/{id}', name: 'programmes_update', methods: ['POST'])]
    public function updateEvent($id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $programme = $entityManager->getRepository(Programmes::class)->find($id);

        if (!$programme) {
            return new JsonResponse(["error" => "Événement non trouvé"], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $programme->setCommentaires($data['title']);
        }

        if (isset($data['status'])) {
            $programme->setStatut($data['status']);
        }

        $entityManager->persist($programme);
        $entityManager->flush();

        return new JsonResponse(["success" => true]);
    }
}
