<?php

namespace App\Controller;

use App\Controller\config\Configom;
use App\Entity\BaseColis;
use App\Entity\Om;
use App\Repository\BaseColisRepository;
use App\Repository\OmRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/suivi-colis')]
class ClientsController extends AbstractController
{
    #[Route('/index', name: 'app_clients')]
    public function index_(BaseColisRepository $colis): Response
    {

        return $this->render('clients/index.html.twig', [
            'colis' => $colis->findByFiltre(2),
        ]);
    }

    // Liste des colis
    #[Route('/view', name: 'colis_index')]
    public function index(): Response
    {
        $colis = [
            [
                'id' => 1,
                'numero' => '78478742',
                'description' => 'Colis fragile destiné à Bamako.',
                'statut' => 'En Arrivage',
                'date' => '2024-12-28',
            ],
            [
                'id' => 2,
                'numero' => '45454545',
                'description' => 'Colis non fragile.',
                'statut' => 'Payé non livré',
                'date' => '2024-12-27',
            ],
        ];

        return $this->json($colis);
    }

    // Afficher les détails d’un colis
    #[Route('/details/{url}', name: 'colis_details', methods: ['GET'])]
    public function details($url, BaseColisRepository $baseColisRepository): Response
    {
        return $this->render('clients/details.html.twig', [
            'colis' => $baseColisRepository->findOneBy(['url' => $url]),
        ]);
    }

    // Changer le statut d’un colis
    #[Route('/colis/{id}/changer-statut', name: 'colis_changer_statut', methods: ['POST'])]
    public function changerStatut(int $id, Request $request): JsonResponse
    {
        $nouveauStatut = $request->request->get('statut');
        // Logique pour mettre à jour le statut dans la base de données
        return $this->json([
            'message' => 'Statut mis à jour avec succès',
            'colisId' => $id,
            'nouveauStatut' => $nouveauStatut,
        ]);
    }


    #[Route('/client/fiche/', name: 'client_fiche', methods: ['GET'])]
    public function ficheClient(
        UserRepository $clientRepository,

    ): Response {
        $client = $clientRepository->find($this->getUser());

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable');
        }
        $transitaire = [
            'nom' => 'SB AIR CARGO',
            'adresse_chine' => 'RoomB1-2 Tianxiu Building No.300 Huanshi Middle Road Yuexiu District Guangzhou',
            'adresse_mali' => 'Mali:79500909',
            'telephone_chine' => '0086 137 98 19 16 52, 0086 186 17 34 54 58',
            'telephone_mali' => '(00223)79 43 38 30',
            'infos' => 'AIR CARGO CHINE- MALI BKO-MLI AD',
            'warning' => '外包装必须备注客户的姓名和电话 ()' // Ceci est le texte en chinois, qui signifie que l\'emballage doit mentionner le nom et le téléphone du client.
        ];
        return $this->render('clients/fiches.html.twig', [
            'client' => $client,
            'transitaire' => $transitaire,
        ]);
    }



    #[Route('/client/fiche/export', name: 'client_fiche_export', methods: ['GET'])]
    public function exportFicheClient(UserRepository $clientRepository): Response
    {
        $client = $clientRepository->find($this->getUser());


        if (!$client) {
            throw $this->createNotFoundException('Client introuvable');
        }
        $transitaire = [
            'nom' => 'SB AIR CARGO',
            'adresse_chine' => 'RoomB1-2 Tianxiu Building No.300 Huanshi Middle Road Yuexiu District Guangzhou',
            'adresse_mali' => 'Mali:79500909',
            'telephone_chine' => '0086 137 98 19 16 52, 0086 186 17 34 54 58',
            'telephone_mali' => '(00223)79 43 38 30',
            'infos' => 'AIR CARGO CHINE- MALI BKO-MLI AD',
            'warning' => '外包装必须备注客户的姓名和电话'
        ];
        $html = $this->renderView('clients/fiches.Export.html.twig', [
            'client' => $client,
            'transitaire' => $transitaire,
        ]);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait');
        $dompdf->render();
        $dompdf->stream("document.pdf", ["Attachment" => false]);

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="fiche-client.pdf"',
        ]);
    }



 #[Route('/paiement/om/web', name: 'payementOmWeb', methods: ['GET'])]
public function payementOm(OmRepository $omRepo, BaseColisRepository $basesRepo, Request $request): Response
{
    $id = $request->query->get('id'); // Changez en fonction de la méthode d'envoi
    if (!$id) {
        return $this->json(['error' => 'ID manquant'], Response::HTTP_BAD_REQUEST);
    }

    $base = $basesRepo->find($id);
    if (!$base) {
        return $this->json(['error' => 'Colis introuvable'], Response::HTTP_NOT_FOUND);
    }

    $montant = $base->getFraisExpeditions() * 1.01;
    $orderId = $base->getNumeroSuivi();

    $returnUrl = "https://portail.drepaussd.com/Sos/Confirmation/paiement/om/{$orderId}";
    $cancelUrl = $returnUrl;
    $notifUrl = $returnUrl;

    $osms = new Configom();
    $osms->setVerifyPeerSSL(true); // Activez SSL pour la sécurité
    $response = $osms->payement($orderId, $montant, $returnUrl, $cancelUrl, $notifUrl);

    if (!empty($response['error'])) {
        return $this->json(['error' => $response['error']], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Enregistrer le paiement dans la base de données
    $paiement = new Om();
    $paiement->setOrderNum($orderId);
    $paiement->setOrderNum($orderId);
    $paiement->setMontant($montant);
    $paiement->setDateOperations(new \DateTime());
    $paiement->setPayToken($response['pay_token']);
    $omRepo->add($paiement, true);

    // Rediriger vers l'URL de paiement
    return new RedirectResponse($response['payment_url']);
}

}
