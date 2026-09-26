<?php

namespace App\Controller;

use App\Entity\Paiements;
use App\Form\PaiementsType;
use App\Repository\BaseColisRepository;
use App\Repository\BasesSahelRepository;
use App\Repository\ExpeditionsRepository;
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
    public function index(ExpeditionsRepository $ExpeditionsRepository): Response
    {
        return $this->render('paiements/index.html.twig', [
            'expeditions' => $ExpeditionsRepository->findAll(),
        ]);
    }


    #[Route('/liste_paiement/{numeroExpeditions}', name: 'liste_paiement', methods: ['GET'])]
    public function listePaiement(
        BaseColisRepository $baseColisRepository,
        PaiementsRepository $paiementsRepository,
        ExpeditionsRepository $expeditionsRepository,
        EntityManagerInterface $entityManager,
        $numeroExpeditions
    ): Response {
        $expedition = $expeditionsRepository->findOneBy(['numeroExpeditions' => $numeroExpeditions]);

        if (!$expedition) {
            throw $this->createNotFoundException("Expédition non trouvée !");
        }

        $baseColis = $baseColisRepository->findBy(['expeditions' => $expedition]);

        // Récupérer les paiements associés aux colis de cette expédition
        $paiements = [];
        $montantsParColis = [];

        foreach ($baseColis as $colis) {
            $paiementsColis = $paiementsRepository->findBy(['colis' => $colis]);
            $paiements[$colis->getId()] = $paiementsColis;

            // Calcul du montant total payé pour chaque colis
            $montantPaye = array_sum(array_map(fn($paiement) => $paiement->getMontants(), $paiementsColis));
            $montantsParColis[$colis->getId()] = [
                'total' => $colis->getFraisExpeditions(),
                'paye' => $montantPaye,
                'remises' => $colis->getRemises(),
                'restant' => max(0, $colis->getFraisExpeditions() - $montantPaye - $colis->getRemises()),
                'id' => $colis->getId(),
            ];
        }

        // Compter le nombre de clients uniques
        $clientsCount = count(array_unique(array_map(fn($coli) => $coli->getClients()->getId(), $baseColis)));

        // Calcul du montant total encaissé et restant pour toute l'expédition
        $montantTotal = array_sum(array_map(fn($coli) => $coli->getFraisExpeditions(), $baseColis));
        $montantEncaisse = array_sum(array_column($montantsParColis, 'paye'));
        $montantRestant = $montantTotal - $montantEncaisse;

        $encaissementsParCaissier = $entityManager->createQuery("
        SELECT u.nom, u.prenom, SUM(p.montants) as total
        FROM App\Entity\Paiements p
        JOIN p.caissier u
        WHERE p.colis IN (:colis)
        GROUP BY u.nom, u.prenom
    ")->setParameter('colis', $baseColis)->getResult();


        return $this->render('paiements/paiement.html.twig', [
            'base_colis' => $baseColis,
            'paiements' => $paiements,
            'montantsParColis' => $montantsParColis,
            'clients_count' => $clientsCount,
            'montant_encaisse' => $montantEncaisse,
            'montant_restant' => $montantRestant,
            'encaissements_par_caissier' => $encaissementsParCaissier,
            'numeroExpeditions' => $numeroExpeditions
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

    #[Route('/paiement/update', name: 'paiement_update', methods: ['POST'])]
    public function updatePaiement(Request $request, EntityManagerInterface $em, BaseColisRepository $colisRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $colis = $colisRepository->find($data['id']);

        if (!$colis) {
            return new JsonResponse(['success' => false, 'message' => 'Colis introuvable'], 404);
        }

        $colis->setFraisExpeditions((int) $data['montantTotal']);
        /*  $colis->setRemises((float) $data['remise']);
        $colis->setMontantPaye((float) $data['montantDejaPaye']); */





        // Supprimer les anciens paiements liés à ce colis
        foreach ($colis->getPaiements() as $ancienPaiement) {
            $em->remove($ancienPaiement);
            $em->flush();
        }

        // Créer un nouveau paiement
        $paiement = new Paiements();
        $paiement->setColis($colis);
        $paiement->setRemises($data['remise']);
        $paiement->setMontants($data['montantDejaPaye']);
        $paiement->setModePaiement('Espèces');
        $paiement->setDatePaiements(new \DateTime());
        $paiement->setCaissier($this->getUser());
        $colis->setRemises($data['remise']);
        $colis->setMontantPaye($data['montantDejaPaye']);
        $em->persist($paiement);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Paiement mis à jour avec succès']);
    }

    #[Route('/bonus/info/{colisId}', name: 'bonus_info', methods: ['GET'])]
    public function bonusInfo(
        int $colisId,
        BaseColisRepository $colisRepo
    ): JsonResponse {
        $colis = $colisRepo->find($colisId);
        if (!$colis) {
            return new JsonResponse(['error' => 'Colis introuvable'], 404);
        }

        // Bonus disponible désormais porté par clients.sahel
        $bonusDisponible = (float) $colis->getClients()->getSahel();

        $frais  = (float) $colis->getFraisExpeditions();
        $deja   = (float) $colis->getMontantPaye();
        $remise = (float) $colis->getRemises();
        $restant = max(0.0, $frais - $deja - $remise);

        return new JsonResponse([
            'client'          => trim($colis->getClients()->getPrenom() . ' ' . $colis->getClients()->getNom()),
            'numero'          => (string) $colis->getNumeroSuivi(),
            'frais'           => $frais,
            'dejaPaye'        => $deja,
            'restant'         => $restant,
            'bonusDisponible' => max(0.0, $bonusDisponible),
        ]);
    }

    #[Route('/bonus/appliquer', name: 'bonus_apply', methods: ['POST'])]
    public function bonusApply(
        Request $request,
        EntityManagerInterface $em,
        BaseColisRepository $colisRepo,
        BasesSahelRepository $sahelRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $colisId = (int)($data['colisId'] ?? 0);

        $colis = $colisRepo->find($colisId);
        if (!$colis) {
            return new JsonResponse(['success' => false, 'message' => 'Colis introuvable'], 404);
        }

        $client = $colis->getClients();
        if (!$client) {
            return new JsonResponse(['success' => false, 'message' => 'Client introuvable'], 404);
        }

        // Montants actuels
        $frais   = (float) $colis->getFraisExpeditions();
        $deja    = (float) $colis->getMontantPaye();
        $remise  = (float) $colis->getRemises();
        $restant = max(0.0, $frais - $deja - $remise);

        // Solde de bonus sur la fiche client (clients.sahel)
        $bonusDisponible = (float) $client->getSahel();

        if ($restant <= 0.0 || $bonusDisponible <= 0.0) {
            return new JsonResponse(['success' => false, 'message' => 'Aucun bonus applicable'], 200);
        }

        // Montant appliqué = min(restant, bonus dispo)
        $aAppliquer = (float) min($restant, $bonusDisponible);
        if ($aAppliquer <= 0.0) {
            return new JsonResponse(['success' => false, 'message' => 'Montant non applicable'], 200);
        }

        // 1) Appliquer en remise sur le colis
        $colis->setRemises($remise + $aAppliquer);
        $em->persist($colis);

        // 2) Consommer les lignes de bases_sahel (statut = 0) en FIFO jusqu'à couvrir $aAppliquer
        $conn = $em->getConnection();

        // Récupère les lignes non consommées pour ce client
        $rows = $conn->fetchAllAssociative(
            'SELECT id, sahel 
           FROM bases_sahel 
          WHERE clients_id = :cid AND statut = 0
          ORDER BY date_operations ASC, id ASC',
            ['cid' => (int)$client->getId()]
        );

        $resteAConsommer = (int) $aAppliquer;

        foreach ($rows as $row) {
            if ($resteAConsommer <= 0) break;

            $ligneId = (int)$row['id'];
            $montant = (int)$row['sahel'];

            if ($montant <= $resteAConsommer) {
                // Consommation totale de la ligne : statut = 1
                $conn->update('bases_sahel', ['statut' => 1], ['id' => $ligneId]);
                $resteAConsommer -= $montant;
            } else {
                // Consommation partielle : on réduit la ligne (reste non consommée)
                $conn->update('bases_sahel', ['sahel' => $montant - $resteAConsommer], ['id' => $ligneId]);
                $resteAConsommer = 0;
            }
        }

        // 3) Décrémenter le solde du client
        $client->setSahel($bonusDisponible - $aAppliquer);
        // Si tu veux forcer à 0 quoi qu'il arrive (même si aAppliquer < bonusDisponible), remplace par :
        // $client->setSahel(0);
        $em->persist($client);

        // 4) Enregistrer un "paiement" de type remise via bonus
        $paiement = new Paiements();
        $paiement->setColis($colis);
        $paiement->setMontants(0);                              // pas d'encaissement en numéraire
        $paiement->setRemises((int) $aAppliquer);               // remise = bonus appliqué
        $paiement->setModePaiement($data['typePaiement'] ?? 'BONUS'); // ou impose 'BONUS'
        $paiement->setDatePaiements(new \DateTime());
        $paiement->setCaissier($this->getUser());

        $em->persist($paiement);

        // Sauvegarde
        $em->flush();

        return new JsonResponse([
            'success'      => true,
            'applique'     => $aAppliquer,
            'restant'      => max(0.0, $restant - $aAppliquer),
            'bonusClient'  => max(0.0, $bonusDisponible - $aAppliquer), // ou 0 si tu forces
        ]);
    }
}
