<?php

namespace App\Controller;

use App\Repository\BaseColisRepository;
use App\Repository\ClientsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/liste/clients', name: 'app_admin')]
    public function index(ClientsRepository $clientsRepository,): Response
    {
        return $this->render('admin/client.html.twig', [
            'clients' => $clientsRepository->findAll(),
        ]);
    }

    #[Route('/clients/update/{id}', name: 'clients_update', methods: ['POST'])]
    public function updateClient(int $id, Request $request, ClientsRepository $clientsRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $client = $clientsRepository->find($id);

        if (!$client) {
            return new JsonResponse(['success' => false, 'message' => 'Client introuvable'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $client->setNom(mb_strtoupper($data['nom'], "UTF-8"));
        $client->setPrenom(mb_convert_case($data['prenom'], MB_CASE_TITLE, "UTF-8"));
        $client->setTelephone($data['telephone']);
        $client->setAdresse($data['adresse']);
        $client->setVille($data['ville']);
        $client->setCodePostal($data['codePostal']);
        $client->setPays($data['pays']);
        $client->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($client);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Client mis à jour avec succès']);
    }

    #[Route('/clients/expeditions/{id}', name: 'app_client_expedition', methods: ['GET'])]
    public function show($id,BaseColisRepository $baseColisRepository): Response
    {
        return $this->render('clients/show.html.twig', [
            'base_colis' => $baseColisRepository->findBy(['clients' => $id ]),
        ]);
    }
}
