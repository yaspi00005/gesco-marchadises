<?php

namespace App\Controller;

use App\Entity\BaseColis;
use App\Entity\BaseColisDetails;
use App\Form\BaseColisDetailsType;
use App\Form\BaseColisType;
use App\Repository\BaseColisRepository;
use App\Repository\TypeMarchandisesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/base/colis')]
final class BaseColisController extends AbstractController
{
    #[Route(name: 'app_base_colis_index', methods: ['GET'])]
    public function index(BaseColisRepository $baseColisRepository): Response
    {
        return $this->render('base_colis/index.html.twig', [
            'base_colis' => $baseColisRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_base_colis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TypeMarchandisesRepository $typeMarchandises, BaseColisRepository $colis, EntityManagerInterface $entityManager, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/')] string $photoDirectory): Response
    {
        $baseColi = new BaseColis();
        $form = $this->createForm(BaseColisType::class, $baseColi);
        $form->handleRequest($request);


        $baseColisDetail = new BaseColisDetails();
        $formColis = $this->createForm(BaseColisDetailsType::class, $baseColisDetail);
        $formColis->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $mois = $baseColi->getDateReceptions()->format('m');
            $annee = $baseColi->getDateReceptions()->format('Y');
            $nbre_mois = $colis->countByColi($mois, $annee)[0][1] + 1;

            $typeMarchandises = $_POST['typeMarchandises'];
            $poidsVolume = $_POST['poidsVolume'];
            $prixPoidsVolume = $_POST['prixPoidsVolume'];
            $photos = $_FILES['photos'];



            $baseColi->setExpediteurs($this->getUser());
            $baseColi->setUnites(count($poidsVolume));
            $numeroSuivi = $mois . $annee . '-' . $nbre_mois . '-' . $colis->countByColi(null, null)[0][1] + 1;
            $baseColi->setNumeroSuivi($numeroSuivi);

            /*  $entityManager->persist($baseColi);
            $entityManager->flush(); */



            if (isset($poidsVolume)) {
                foreach ($poidsVolume as $key => $value) {
                    $baseColisDetail = new BaseColisDetails();

                    $baseColisDetail->setTypeMarchandises($typeMarchandises[$key]);
                    $baseColisDetail->setPoidsVolume($poidsVolume[$key]);
                    $baseColisDetail->setPrixPoidsVolume($prixPoidsVolume[$key]);
                    $baseColisDetail->setMontantTotal($poidsVolume[$key] * $prixPoidsVolume[$key]);
                    $baseColisDetail->setColis($baseColi);
                    $baseColisDetail->setNumeroColis($mois . $annee);

                    if (isset($_FILES['photos'])) {
                        $originalFilename = pathinfo($_FILES['photos']['name'][$key], PATHINFO_FILENAME);
                        $extension = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                        $safeFilename = preg_replace('/[^a-zA-Z0-9-_]/', '', $originalFilename);
                        $newFilename = $safeFilename . '.' . $extension;

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
            $baseColi->setUrl(md5($numeroSuivi));
            $entityManager->persist($baseColi);
            $entityManager->flush();

            return $this->redirectToRoute('conf_enregistrement', ['numeroSuivi' => $numeroSuivi], Response::HTTP_SEE_OTHER);
        }

        return $this->render('base_colis/new.html.twig', [
            'base_coli' => $baseColi,
            'form' => $form,
            'formColis' => $formColis,
            'typeMarchandises' => $typeMarchandises->findAll()
        ]);
    }

    #[Route('/{id}', name: 'app_base_colis_show', methods: ['GET'])]
    public function show(BaseColis $baseColi): Response
    {
        return $this->render('base_colis/show.html.twig', [
            'base_coli' => $baseColi,
        ]);
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

    #[Route('/confirmation/enregistrement/{numeroSuivi}', name: 'conf_enregistrement', methods: ['GET'])]
    public function recu($numeroSuivi): Response
    {

        return $this->render('base_colis/recus.html.twig', [
            "colis" => $numeroSuivi

        ]);
    }

    #[Route('/details/{numeroSuivi}', name: 'colis_details', methods: ['GET'])]
    public function getColisDetails(BaseColisRepository $colisRepository, $numeroSuivi): JsonResponse
    {
        $colis = $colisRepository->findOneBy(['numeroSuivi' => $numeroSuivi]);

        if (!$colis) {
            return new JsonResponse(['error' => 'Colis non trouvé'], 404);
        }

        // dd($colis);

        return new JsonResponse([
            'numeroSuivi' => $colis->getNumeroSuivi(),
            'expediteur' => $colis->getDestinateurs()->getPrenom() . ' ' . $colis->getDestinateurs()->getNom(),
            'telephone' => $colis->getDestinateurs()->getUsename(),
            'dateReception' => $colis->getDateReceptions()->format('d/m/Y'),
            /* 'details' => $colis->getDetailsString(), // Format personnalisé de détails */
            'qrCodePath' => $colis->getQrCodePath(),
        ]);
    }




    #[Route('/option-scanner/camera', name: 'qr_scan_index', methods: ['GET'])]
    public function scaner(): Response
    {

        return $this->render('base_colis/scan.html.twig', []);
    }


    #[Route('/option-scanner/camera/result', name: 'qr_scan', methods: ['POST'])]
    public function handleQrScan(Request $request)
    {
        // Récupérer le contenu JSON envoyé par le client
        $data = json_decode($request->getContent(), true);

        // Vérification si 'qrData' est défini
        if (!isset($data['qrData'])) {
            return new JsonResponse([
                'message' => 'QR Code data missing'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Extraire les données du QR code
        $qrData = $data['qrData'];

        // Vérifier si qrData commence par "colis:" ou "client:"
        if (strpos($qrData, 'colis:') === 0) {
            // Si qrData commence par "colis:"
            // Extraire l'ID du colis après "colis:"
            $colisId = substr($qrData, 6);  // 6 est la longueur de "colis:"
            // Traitement pour le colis avec l'ID extrait
            return new JsonResponse([
                'message' => 'QR Code pour colis reçu',
                'colisId' => $colisId,
                'redirectUrl' => '/path/to/colis/' . $colisId // URL de redirection pour le colis
            ]);
        } elseif (strpos($qrData, 'client:') === 0) {
            // Si qrData commence par "client:"
            // Extraire l'ID du client après "client:"
            $clientId = substr($qrData, 7);  // 7 est la longueur de "client:"
            // Traitement pour le client avec l'ID extrait
            return new JsonResponse([
                'message' => 'QR Code pour client reçu',
                'clientId' => $clientId,
                'redirectUrl' => '/path/to/client/' . $clientId // URL de redirection pour le client
            ]);
        }

        // Si aucune correspondance
        return new JsonResponse([
            'message' => 'QR Code inconnu'
        ], Response::HTTP_BAD_REQUEST);
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
    public function changerStatut($url, Request $request, BaseColisRepository $BaseColisRepository, EntityManagerInterface $em): Response
    {

        $colis = $BaseColisRepository->findOneBy(['url' =>  $url]);




        if (!$colis) {
            throw $this->createNotFoundException('Le colis n\'existe pas.');
        }



        $nouveauStatut = $request->request->get('nouveauStatut');

        if ($nouveauStatut) {


            $colis->setStatut($nouveauStatut);


            $em->flush();



            $this->addFlash('success', 'Le statut du colis a été mis à jour avec succès!');
        }

        return $this->redirectToRoute('colis_details_statut', ['url' => $url]);
    }
}
