<?php

namespace App\Controller;

use App\Entity\BaseColis;
use App\Entity\BaseColisDetails;
use App\Form\BaseColisDetailsType;
use App\Form\BaseColisType;
use App\Repository\BaseColisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function new(Request $request, BaseColisRepository $colis, EntityManagerInterface $entityManager, SluggerInterface $slugger,#[Autowire('%kernel.project_dir%/public/uploads/')] string $photoDirectory): Response
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

            $baseColi->setExpediteurs($this->getUser());
            $baseColi->setNumeroSuivi($mois . $annee . '-' . $nbre_mois . '-'. $colis->countByColi(null, null)[0][1] + 1);
            $entityManager->persist($baseColi);
            $entityManager->flush();


            $typeMarchandises = $_POST['typeMarchandises'];
            $poidsVolume = $_POST['poidsVolume'];
            $prixPoidsVolume = $_POST['prixPoidsVolume'];
            $photos = $_FILES['photos'];
            if (isset($poidsVolume)) {
                foreach ($poidsVolume as $key => $value) {
                    $baseColisDetail = new BaseColisDetails();

                    $baseColisDetail->setTypeMarchandises($typeMarchandises[$key]);
                    $baseColisDetail->setPoidsVolume($poidsVolume[$key]);
                    $baseColisDetail->setPrixPoidsVolume($prixPoidsVolume[$key]);
                    $baseColisDetail->setMontantTotal($poidsVolume[$key] * $prixPoidsVolume[$key]);
                    $baseColisDetail->setMontantTotal($poidsVolume[$key] * $prixPoidsVolume[$key]);
                    $baseColisDetail->setColis($baseColi);
                    $baseColisDetail->setNumeroColis($mois . $annee);
                    if (isset($_FILES['photos'])) {
                       /*  foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) { */
                            $originalFilename = pathinfo($_FILES['photos']['name'][$key], PATHINFO_FILENAME);
                            $extension = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                            $safeFilename = preg_replace('/[^a-zA-Z0-9-_]/', '', $originalFilename);
                            $newFilename = $safeFilename .  '.' . $extension;
                    
                            if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $photoDirectory . $newFilename)) {
                               $baseColisDetail->setPhoto($newFilename);
                            } else {
                                echo "Failed to upload: " . $_FILES['photos']['name'][$key] . "<br>";
                            }
                        /* } */
                    }
                    $entityManager->persist($baseColisDetail);
                    $entityManager->flush();
                }
               
            }

        

            return $this->redirectToRoute('app_base_colis_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('base_colis/new.html.twig', [
            'base_coli' => $baseColi,
            'form' => $form,
            'formColis' => $formColis
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
}
