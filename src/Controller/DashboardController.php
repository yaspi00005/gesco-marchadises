<?php

namespace App\Controller;

use App\Repository\BaseColisRepository;
use App\Repository\ClientsRepository;
use App\Repository\PaiementsRepository;
use App\Service\SmsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_dashboard')]
    public function index(
        ClientsRepository $clientsRepository,
        BaseColisRepository $colisRepository,
        PaiementsRepository $paiementsRepository
    ): Response {
        // Récupérer les statistiques
        $totalClients = $clientsRepository->count([]);
        $totalColis = $colisRepository->count([]);
        $colisNonRecuperes = $colisRepository->count(['statut' => 'Non récupéré']);
        
        // Calcul des montants encaissés et restants
        $montantEncaisse = $paiementsRepository->createQueryBuilder('p')
            ->select('SUM(p.montants)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    
        $montantTotalColis = $colisRepository->createQueryBuilder('c')
            ->select('SUM(c.fraisExpeditions)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    
        $montantRestant = $montantTotalColis - $montantEncaisse;
    
        return $this->render('dashboard/index.html.twig', [
            'totalClients' => $totalClients,
            'totalColis' => $totalColis,
            'colisNonRecuperes' => $colisNonRecuperes,
            'montantEncaisse' => $montantEncaisse,
            'montantRestant' => $montantRestant,
        ]);
    }
    
}