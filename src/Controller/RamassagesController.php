<?php

namespace App\Controller;

use App\Entity\BaseColis;
use App\Entity\BaseColisDetails;
use App\Entity\Clients;
use App\Entity\Paiements;
use App\Form\BaseColisDetailsType;
use App\Form\BaseColisType;
use App\Form\ClientsType;
use App\Repository\BaseColisRepository;
use App\Repository\PaiementsRepository;
use App\Repository\TypeMarchandisesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

#[Route('/ramassages')]
class RamassagesController extends AbstractController
{
    #[Route(name: 'app_base_colis_index_rama', methods: ['GET'])]
    public function index(BaseColisRepository $baseColisRepository, TypeMarchandisesRepository $typeMarchandises): Response
    {
        return $this->render('ramassages/ramassages.html.twig', [
            'base_colis' => $baseColisRepository->findByColis($this->getUser()),
            'typeMarchandises' => $typeMarchandises->findAll()
        ]);
    }



    #[Route('/client/new', name: 'app_clients_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Clients();
        $form = $this->createForm(ClientsType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setCreatedAt(new \DateTimeImmutable());
            $client->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($client);
            $entityManager->flush();
            $this->addFlash('success', 'Le client a été ajouté avec success');
            return $this->redirectToRoute('app_clients_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clients/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/confirmation/enregistrement/{numeroSuivi}', name: 'conf_enregistrement', methods: ['GET'])]
    public function recu($numeroSuivi,BaseColisRepository $colisRepository): Response
    {

        return $this->render('base_colis/recus.html.twig', [
            "colis" => $numeroSuivi ,
            'paiements' => $colisRepository->findOneBy(['numeroSuivi'=> $numeroSuivi])

        ]);
    }

    #[Route('/colis/new', name: 'app_base_colis_new', methods: ['GET', 'POST'])]
    public function new_colis(TokenGeneratorInterface $tokenGenerator, Request $request, TypeMarchandisesRepository $typeMarchandises, BaseColisRepository $colis, EntityManagerInterface $entityManager, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/')] string $photoDirectory): Response
    {
        $baseColi = new BaseColis();
        $form = $this->createForm(BaseColisType::class, $baseColi);
        $form->handleRequest($request);


        $baseColisDetail = new BaseColisDetails();
        $formColis = $this->createForm(BaseColisDetailsType::class, $baseColisDetail);
        $formColis->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $id = $colis->findOneBy([],['id' => 'DESC' ]);
            $baseColi->setDateReceptions(new \DateTime());


            $mois = $baseColi->getDateReceptions()->format('m');
            $annee = $baseColi->getDateReceptions()->format('Y');
            $nbre_mois = $colis->countByColi($mois, $annee)[0][1] + 1;

            $typeMarchandises = $_POST['typeMarchandises'];
            $poidsVolume = $_POST['poidsVolume'];
            $prixPoidsVolume = $_POST['prixPoidsVolume'];
            $observations = $_POST['observations'];
            $photos = $_FILES['photos'];



            $baseColi->setExpediteurs($this->getUser());
            $codeRetrait = strtoupper(substr(uniqid('COL', true), 0, 10));
            $baseColi->setCode($codeRetrait);
            $baseColi->setUnites(count($poidsVolume));
            $numeroSuivi = $mois . $annee . '-' . $id->getId() + 1 . '-' . $colis->countByColi(null, null)[0][1] + 1;
            $baseColi->setNumeroSuivi($numeroSuivi);

            /*  $entityManager->persist($baseColi);
            $entityManager->flush(); */



            if (isset($prixPoidsVolume)) {
                foreach ($poidsVolume as $key => $value) {
                    $baseColisDetail = new BaseColisDetails();

                    $baseColisDetail->setTypeMarchandises($typeMarchandises[$key]);
                    $baseColisDetail->setPoidsVolume($poidsVolume[$key]);
                    $baseColisDetail->setPrixPoidsVolume($prixPoidsVolume[$key]);
                    $baseColisDetail->setMontantTotal($prixPoidsVolume[$key]);
                    $baseColisDetail->setObservations($observations[$key]);
                    $baseColisDetail->setColis($baseColi);
                    $baseColisDetail->setNumeroColis($mois . $annee);

                    if (isset($_FILES['photos'])) {
                        $originalFilename = pathinfo($_FILES['photos']['name'][$key], PATHINFO_FILENAME);
                        $extension = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                        $safeFilename = preg_replace('/[^a-zA-Z0-9-_]/', '', $originalFilename);
                        $newFilename = $safeFilename .'_'.uniqid(). '.' . $extension;

                        if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $photoDirectory . $newFilename)) {
                            $baseColisDetail->setPhoto($newFilename);
                        } else {
                            echo "Failed to upload: " . $_FILES['photos']['name'][$key] . "<br>";
                        }
                    }

                    $entityManager->persist($baseColisDetail);
                    /*  $entityManager->flush(); */
                }
            }

            // Générer le QR code
            $qrCode = new QrCode("colis:" . md5($numeroSuivi));
            $writer = new PngWriter();

            $writer = new PngWriter();
            $qrCodeFilePath = $photoDirectory . 'qrcode_' . $numeroSuivi . '.png';
            $result = $writer->write($qrCode);
            $result->saveToFile($qrCodeFilePath);

            // Sauvegarder le chemin du QR code dans la base
            $baseColi->setQrCodePath('qrcode_' . $numeroSuivi . '.png');
            $baseColi->setUrl($tokenGenerator->generateToken());
            $baseColi->setDateUpdate(new \DateTime());
            $baseColi->setDateReceptions(new \DateTime());
            $entityManager->persist($baseColi);
            $entityManager->flush();

            /*  $messageSms = "Bonjour {$client->getNom()}, votre colis est enregistré avec le numéro de suivi : {$numeroSuivi}. Merci !";
            $smsService->sendSms($telephone, $messageSms); */


            return $this->redirectToRoute('conf_enregistrement', ['numeroSuivi' => $numeroSuivi], Response::HTTP_SEE_OTHER);
        }

        return $this->render('base_colis/new.html.twig', [
            'base_coli' => $baseColi,
            'form' => $form,
            'formColis' => $formColis,
            'typeMarchandises' => $typeMarchandises->findAll()
        ]);
    }



    #[Route('/colis/addArticle', name: 'colis_add_article', methods: ['POST'])]
    public function addArticle(
        Request $request,
        BaseColisRepository $baseColisRepository,
        EntityManagerInterface $entityManager,
        #[Autowire('%photo_directory%')] string $photoDirectory
    ): JsonResponse {
        $colisId = $request->request->get('colisUrl');
        $colis = $baseColisRepository->findOneBy(['url' => $colisId]);

        if (!$colis) {
            return new JsonResponse(['message' => 'Colis introuvable'], Response::HTTP_NOT_FOUND);
        }

        $baseColisDetail = new BaseColisDetails();
        $baseColisDetail->setTypeMarchandises($request->request->get('typeMarchandises'));
        $baseColisDetail->setPoidsVolume(floatval($request->request->get('poidsVolume')));
        $baseColisDetail->setPrixPoidsVolume(floatval($request->request->get('prixPoidsVolume')));
        $baseColisDetail->setMontantTotal(floatval($request->request->get('prixPoidsVolume')));
        $baseColisDetail->setObservations($request->request->get('observations'));
        $baseColisDetail->setColis($colis);
        $baseColisDetail->setNumeroColis(date('mY'));

        // Gestion de l'upload de photo
        if ($request->files->get('photo')) {
            $photo = $request->files->get('photo');
            $fileName = uniqid() . '.' . $photo->guessExtension();
            $photo->move($photoDirectory, $fileName);
            $baseColisDetail->setPhoto($fileName);
        }

        $entityManager->persist($baseColisDetail);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Article ajouté avec succès'], Response::HTTP_CREATED);
    }




    #[Route('/colis/details/{numeroSuivi}/ajax',   name: 'colis_detailss', methods: ['GET'])]
    public function getColisDetails(BaseColisRepository $colisRepository, $numeroSuivi): JsonResponse
    {
        $colis = $colisRepository->findOneBy(['numeroSuivi' => $numeroSuivi]);

        if (!$colis) {
            return new JsonResponse(['error' => 'Colis non trouvé'], 404);
        }

        // dd($colis);
        return $this->json([
            'numeroSuivi' => $colis->getNumeroSuivi(),
            'codeRetrait' => $colis->getCode(),
            'expediteur' => $colis->getClients()->getPrenom() . ' ' . $colis->getClients()->getNom(),
            'telephone' => $colis->getClients()->getTelephone(),
            'prix' => $colis->getFraisExpeditions(),
            'TypeExpedition' => $colis->getTypeExpeditions(),
            'dateReception' => $colis->getDateReceptions()->format('d/m/Y'),
            'qrCodePath' => $colis->getQrCodePath(),
        ]);
    }

    #[Route('/colis/encaisser/{id}', name: 'encaisser2', methods: ['POST'])]
    public function encaisser(
        int $id,
        Request $request,
        BaseColisRepository $colisRepository,
        PaiementsRepository $paiementsRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Récupérer les données envoyées via AJAX
        $data = json_decode($request->getContent(), true);

        if (!isset($data['montantPaye']) || !isset($data['typePaiement']) || !isset($data['remise'])) {
            return new JsonResponse(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer le colis concerné
        $colis = $colisRepository->find($id);
        if (!$colis) {
            return new JsonResponse(['error' => 'Colis introuvable'], Response::HTTP_NOT_FOUND);
        }

        $montantTotal = $colis->getFraisExpeditions(); // Montant total initial
        $montantDejaPaye = $paiementsRepository->getMontantTotalPaye($colis) ?? 0; // Montant déjà payé
        $remise = (float) $data['remise']; // Remise appliquée
        $montantRestant = $montantTotal - $montantDejaPaye - $remise; // Nouveau montant restant après remise
        $montantPaye = (float) $data['montantPaye']; // Montant payé

        // Vérification si le montant payé ne dépasse pas le montant restant
        if ($montantPaye > $montantRestant) {
            return new JsonResponse(['error' => 'Le montant payé dépasse le montant restant après remise.'], Response::HTTP_BAD_REQUEST);
        }

        // Enregistrer le paiement
        $paiement = new Paiements();
        $paiement->setColis($colis);
        $paiement->setMontants($montantPaye);
        $paiement->setRemises($remise);
        $paiement->setModePaiement($data['typePaiement']);
        $paiement->setDatePaiements(new \DateTime());
        $paiement->setCaissier($this->getUser());

        $entityManager->persist($paiement);

        // Mettre à jour le statut du paiement
        if ($montantPaye == $montantRestant) {
            $colis->setStatutPaiements('Payé');
        } else {
            $colis->setStatutPaiements('Partiellement payé');
        }

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Paiement enregistré avec succès',
            'montantRestant' => $montantRestant - $montantPaye
        ], Response::HTTP_CREATED);
    }


    #[Route('/recu/pdf/{colisId}', name: 'generate_pdf')]
    public function generatePdf($colisId, BaseColisRepository $colisRepository): Response
    {

        //  Récupérer le colis par son numéro de suivi
        $colis = $colisRepository->findOneBy(['numeroSuivi' => $colisId]);

        //  Vérifier si le colis existe
        if (!$colis) {
            throw $this->createNotFoundException("Colis non trouvé !");
        }

        //  Vérifier si le logo existe
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/assets/img/logo.png';
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        //  Récupérer toutes les photos associées au colis
        $photosData = [];
        foreach ($colis->getBaseColisDetails() as $detail) {
            $photoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $detail->getPhoto();
            if (file_exists($photoPath) && !empty($detail->getPhoto())) {
                $photosData[] = [
                    'photoBase64' => base64_encode(file_get_contents($photoPath)),
                    'observation' => $detail->getObservations() // Ajout de l'observation
                ];
            }
        }

        // Options DOMPDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        // 🔥 Générer la vue du PDF avec les images encodées en Base64
        $html = $this->renderView('ramassages/index.html.twig', [
            'colis' => $colis,
            'logoBase64' => $logoBase64,
            'photosData' => $photosData
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->stream("Reçu_Colis_{$colisId}.pdf", ["Attachment" => true]), 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }


    #[Route('/colis/supprimer/{id}', name: 'colis_supprimer', methods: ['DELETE'])]
    public function supprimer(BaseColisRepository $colisRepository, EntityManagerInterface $entityManager,  $id): JsonResponse
    {
        $colis = $colisRepository->findOneBy(['url' => $id]);


        if (!$colis) {
            return new JsonResponse(['success' => false, 'message' => 'Colis non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($colis->getStatut() !== 'En préparation') {
            return new JsonResponse(['success' => false, 'message' => 'Seuls les colis en préparation peuvent être supprimés'], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($colis);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Colis supprimé avec succès']);
    }

    #[Route('/paiements/delete/{url}', name: 'delete_paiement', methods: ['POST'])]
    public function deletePaiement($url, BaseColisRepository $baseColis, PaiementsRepository $paiementRepository, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        // Vérifier si la requête est en AJAX
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Requête invalide'], 400);
        }

        $colis = $baseColis->findOneBy(['url' => $url]);
        // Récupérer le paiement
        $paiement = $paiementRepository->findOneBy(['colis' => $colis]);

        if (!$paiement) {
            return new JsonResponse(['error' => 'Paiement non trouvé'], 404);
        }

        // Supprimer le paiement
        $entityManager->remove($paiement);
        $entityManager->flush();

        return new JsonResponse(['success' => 'Paiement supprimé avec succès', 'id' => $url]);
    }
}
