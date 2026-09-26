<?php

namespace App\Controller;

use App\Controller\config\Configsms;
use App\Entity\BaseColis;
use App\Entity\BaseColisDetails;
use App\Entity\Paiements;
use App\Form\BaseColisDetailsType;
use App\Form\BaseColisType;
use App\Repository\BaseColisRepository;
use App\Repository\ExpeditionsRepository;
use App\Repository\PaiementsRepository;
use App\Repository\TypeMarchandisesRepository;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/pays/base/colis')]
final class BaseColisController extends AbstractController
{
    #[Route(name: 'app_base_colis_index', methods: ['GET'])]
    public function index(
        ExpeditionsRepository $expeditionsRepository,
    ): Response {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur est un admin (il voit toutes les expéditions)
        if ($this->isGranted('ROLE_ADMIN')) {
            $expeditions = $expeditionsRepository->findAll();
        }
        // Si l'utilisateur est un représentant de pays, il ne voit que les expéditions qui lui sont destinées
        elseif ($this->isGranted('ROLE_REPRESENTANT')) {
            if ($this->isGranted('ROLE_MALI')) {
                $pays = 'Mali';
            } else {
                $pays = 'Sénégal';
            }
            $expeditions = $expeditionsRepository->findBy(['destinations' => $pays]);
        }
        // Autres utilisateurs (interdit)
        else {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette page.");
        }

        return $this->render('base_colis/index.html.twig', [
            'Expeditions' => $expeditions,
        ]);
    }



    #[Route('/base_colis/liste/{expedition}', name: 'app_base_colis_liste', methods: ['GET'])]
    public function liste(
        string $expedition,
        BaseColisRepository $baseColisRepository,
        ExpeditionsRepository $expeditionsRepository,
    ): Response {
        $user = $this->getUser();

        // Récupérer l'expédition par son numéro
        $expeditionEntity = $expeditionsRepository->findOneBy(['numeroExpeditions' => $expedition]);

        if (!$expeditionEntity) {
            throw $this->createNotFoundException("Expédition introuvable !");
        }

        // Vérifier les permissions selon le rôle
        if ($this->isGranted('ROLE_ADMIN')) {
            // L'admin voit tous les colis
            $colis = $baseColisRepository->findBy(['expeditions' => $expeditionEntity]);
        } elseif ($this->isGranted('ROLE_REPRESENTANT')) { 
            // Déterminer le pays de l'utilisateur
            if ($this->isGranted('ROLE_MALI')) {
                $pays = 'Mali';
            } elseif ($this->isGranted('ROLE_SENEGAL')) {
                $pays = 'Sénégal';
            } else {
                throw $this->createAccessDeniedException("Votre rôle ne vous permet pas d'accéder aux expéditions.");
            }

            // Vérifier si l'expédition correspond au pays de l'utilisateur
            if ($expeditionEntity->getDestinations() !== $pays) {
                throw $this->createAccessDeniedException("Vous ne pouvez voir que les expéditions destinées à votre pays.");
            }

            // Filtrer les colis de cette expédition
            $colis = $baseColisRepository->findBy(['expeditions' => $expeditionEntity]);
        } else {
            throw $this->createAccessDeniedException("Accès refusé !");
        }

        return $this->render('base_colis/liste.html.twig', [
            'base_colis' => $colis,
            'expedition' => $expeditionEntity,
        ]);
    }


    #[Route('/encaisser/{id}', name: 'encaisser_colis', methods: ['POST'])]
    public function encaisser(
        int $id,
        Request $request,
        BaseColisRepository $colisRepository,
        PaiementsRepository $paiementsRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();
        $colis = $colisRepository->find($id);

        if (!$colis) {
            return new JsonResponse(['message' => 'Colis introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Vérification du droit d'encaisser selon le rôle
        if ($this->isGranted('ROLE_ADMIN')) {
            // L'admin peut encaisser n'importe quel colis
        } elseif ($this->isGranted('ROLE_REPRESENTANT')) {
            $paysAutorisé = $this->isGranted('ROLE_MALI') ? 'Mali' : 'Sénégal';

            if ($colis->getExpeditions()->getDestinations() !== $paysAutorisé) {
                return new JsonResponse(['message' => 'Accès refusé : Vous ne pouvez encaisser que les colis de votre pays'], Response::HTTP_FORBIDDEN);
            }
        } else {
            return new JsonResponse(['message' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $montantPayé = $data['montantPayé'];

        // Vérifier que le montant payé ne dépasse pas le montant restant
        $montantRestant = $colis->getFraisExpeditions() - $colis->getMontantPaye();
        if ($montantPayé > $montantRestant) {
            return new JsonResponse(['message' => 'Erreur : Montant payé supérieur au montant restant'], Response::HTTP_BAD_REQUEST);
        }

        // Enregistrer le paiement avec remise
        $paiement = new Paiements();
        $paiement->setColis($colis);
        $paiement->setMontants($montantPayé);
        $paiement->setDatePaiements(new \DateTime());
        $paiement->setModePaiement('Espèces');
        $paiement->setCaissier($user);
        $paiement->setRemises(0);

        $entityManager->persist($paiement);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Paiement enregistré avec succès'], Response::HTTP_CREATED);
    }


    #[Route('/recherche/{codeRetrait}', name: 'recherche_colis', methods: ['GET'])]
    public function rechercheColis(string $codeRetrait, BaseColisRepository $colisRepository): JsonResponse
    {
        $colis = $colisRepository->findOneBy(['code' => $codeRetrait]);

        if (!$colis) {
            return new JsonResponse(['error' => 'Aucun colis trouvé avec ce code de retrait.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'numeroSuivi' => $colis->getNumeroSuivi(),
            'codeRetrait' => $colis->getCode(),
            'typeExpeditions' => $colis->getTypeExpeditions(),
            'unites' => $colis->getUnites(),
            'fraisExpeditions' => number_format($colis->getFraisExpeditions(), 0, '.', ','),
            'statut' => $colis->getStatut(),
            'statutPaiements' => $colis->getStatutPaiements(),
            'montantPaye' => number_format($colis->getMontantPaye(), 0, '.', ','),
            'remises' => number_format($colis->getRemises(), 0, '.', ','),
            'montantRestant' => number_format($colis->getFraisExpeditions() - $colis->getMontantPaye() - $colis->getRemises(), 0, '.', ','),
            'dateReceptions' => $colis->getDateReceptions()?->format('d-m-Y'),
            'clients' => [
                'prenom' => $colis->getClients()->getPrenom(),
                'nom' => $colis->getClients()->getNom(),
            ],
            'expeditions' => [
                'destinations' => $colis->getExpeditions()->getDestinations(),
                'dateExpeditions' => $colis->getExpeditions()->getDateExpeditions()?->format('Y-m-d'),
            ]
        ]);
    }


    #[Route('/marquer-recupere/{id}', name: 'colis_marquer_recupere', methods: ['POST'])]
    public function marquerRecupere(
         $id,
        BaseColisRepository $baseColisRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $colis = $baseColisRepository->findOneBy(['url' => $id ]);

        if (!$colis) {
            return new JsonResponse(['success' => false, 'message' => 'Colis non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($colis->getStatut() === 'Récupéré') {
            return new JsonResponse(['success' => false, 'message' => 'Ce colis est déjà récupéré'], Response::HTTP_CONFLICT);
        }

        $colis->setStatut('Récupéré');
        $entityManager->persist($colis);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Colis marqué comme récupéré'], Response::HTTP_OK);
    }



    #[Route('/{id}/edit', name: 'app_base_colis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BaseColis $baseColi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BaseColisType::class, $baseColi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_base_colis_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('base_colis/edit.html.twig', [
            'base_coli' => $baseColi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_base_colis_delete', methods: ['POST'])]
    public function delete(Request $request, BaseColis $baseColi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $baseColi->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($baseColi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_base_colis_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/colis/{id}/changer-statut/{nouveauStatut}', name: 'changer_statut_via_qr', methods: ['GET'])]
    public function changerStatutViaQr(int $id, string $nouveauStatut, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le colis par ID
        $colis = $entityManager->getRepository(BaseColis::class)->find($id);

        if (!$colis) {
            return $this->json(['error' => 'Colis introuvable.'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour le statut du colis
        $colis->setStatut($nouveauStatut);

        // Enregistrer les modifications
        $entityManager->persist($colis);
        $entityManager->flush();

        // Retourner une réponse JSON
        return $this->json([
            'message' => 'Le statut du colis a été mis à jour avec succès.',
            'id' => $colis->getId(),
            'nouveauStatut' => $colis->getStatut()
        ], Response::HTTP_OK);
    }




    #[Route('/details/{url}/reception', name: 'colis_details_statut', methods: ['GET'])]
    public function details($url, BaseColisRepository $colis, EntityManagerInterface $em): Response
    {
        $coli = $colis->findOneBy(['url' =>  $url]);


        if (!$colis) {
        }



        return $this->render('base_colis/details.html.twig', [
            'colis' => $coli,
        ]);
    }



    #[Route('/details/{url}/chargement-statut', name: 'changer_statut_via_qr', methods: ['POST'])]
    public function changerStatut($url, MailerInterface $mailer, Request $request, BaseColisRepository $BaseColisRepository, EntityManagerInterface $em): Response
    {

        $colis = $BaseColisRepository->findOneBy(['url' =>  $url]);




        if (!$colis) {
            throw $this->createNotFoundException('Le colis n\'existe pas.');
        }



        $nouveauStatut = $request->request->get('nouveauStatut');

        if ($nouveauStatut) {


            $colis->setStatut($nouveauStatut);


            $em->flush();


            if ($nouveauStatut == 'Arrivé') {
                $senderAdresse = "tel:+22378478742";
                $receiverAdresse = "tel:+223" . $colis->getDestinateurs()->getUsename();
                $message = '';

                $newToken = new Configsms();

                $token = $newToken->getTokenFromConsumerKey();
                $config = array(
                    'token' =>
                    $token['access_token']
                );

                $message = "$message = 'Bonjour, nous vous informons que votre colis est arrivé à Bamako. Poids : ' . $colis->getPoidsVolumeTotal . ' kg. Prix : ' . number_format($colis->getPrix(), 2, ',', ' ') . ' FCFA. Merci pour votre confiance.';
";
                $osms = new Configsms($config);
                $osms->setVerifyPeerSSL(false);
                $response = $osms->sendSms($senderAdresse, $receiverAdresse, $message, '');
            } else {
                // generate a signed url and email it to the user
                $email = (new TemplatedEmail())
                    ->from('info@Sotrama.gescoflex.com')
                    ->to(new Address($colis->getDestinateurs()->getEmail()))
                    ->subject('Restauration - CIRA')

                    // path of the Twig template to render
                    ->htmlTemplate('message.html.twig')

                    // pass variables (name => value) to the template
                    ->context([

                        'user' => $colis->getDestinateurs(),
                    ]);

                $mailer->send($email);
            }

            $this->addFlash('success', 'Le statut du colis a été mis à jour avec succès!');
        }

        return $this->redirectToRoute('colis_details_statut', ['url' => $url]);
    }


    /*  #[Route('/encaisser/{id}', name: 'encaisser', methods: ['POST'])]
    public function encaisser(int $id, BaseColisRepository $colisRepository, PaiementsRepository $paiementsRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $colis = $colisRepository->find($id);

        if (!$colis) {
            return new JsonResponse(['message' => 'Colis introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Vérifiez si un paiement existe déjà pour ce colis
        $paiementExistant = $paiementsRepository->findOneBy(['colis' => $colis]);

        if ($paiementExistant) {
            return new JsonResponse(['message' => 'Un paiement existe déjà pour ce colis'], Response::HTTP_CONFLICT);
        }

        // Créez un nouvel enregistrement de paiement
        $paiement = new Paiements();
        $paiement->setColis($colis);
        $paiement->setMontants($colis->getFraisExpeditions()); // Exemple d'utilisation des frais d'expédition comme montant
        $paiement->setDatePaiements(new \DateTime());
        $colis->setStatutPaiements('Payé');
        $paiement->setModePaiement('Espèces');
        $paiement->setCaissier($this->getUser());
        // Sauvegardez le paiement dans la base de données
        $entityManager->persist($paiement);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Paiement enregistré avec succès'], Response::HTTP_CREATED);
    } */

    #[Route('/rechercher', name: 'rechercher', methods: ['GET'])]
    public function rechercher(Request $request, BaseColisRepository $baseColisRepository): JsonResponse
    {
        $numSuivi = $request->query->get('numSuivi');
        $date = $request->query->get('date');
        $statut = $request->query->get('statut');

        $queryBuilder = $baseColisRepository->createQueryBuilder('c')

            ->select('c.id, c.numeroSuivi, c.typeExpeditions, c.poidsVolumeTotal, c.unites, c.fraisExpeditions, c.statut, c.statutPaiements, c.dateReceptions, c.dateRecuperations,CONCAT(d.prenom,d.nom) AS destinateurNom, e.numeroExpeditions AS expeditionNom, e.modeTransport AS modeTransport ')
            ->leftJoin('c.destinateurs', 'd') // Relation avec "destinateurs"
            ->leftJoin('c.expeditions', 'e'); // Relation avec "expeditions"
        if (!empty($numSuivi)) {
            $queryBuilder->andWhere('c.numeroSuivi LIKE :numSuivi')
                ->setParameter('numSuivi', "%$numSuivi%");
        }

        if (!empty($date)) {
            try {
                $dateTime = new \DateTime($date);
                $queryBuilder->andWhere('c.dateReceptions = :date')
                    ->setParameter('date', $dateTime);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Date invalide'], 400);
            }
        }

        if (!empty($statut) && $statut !== 'Tous') {
            $queryBuilder->andWhere('c.statut = :statut')
                ->setParameter('statut', $statut);
        }

        $result = $queryBuilder->getQuery()->getArrayResult(); // Utilisation de `getArrayResult` pour retourner des données propres

        return new JsonResponse($result);
    }
}
