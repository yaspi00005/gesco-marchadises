<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConditionsController extends AbstractController
{
    #[Route('/conditions', name: 'app_conditions')]
    public function index(): Response
    {
        return $this->render('conditions/index.html.twig', [
            'controller_name' => 'ConditionsController',
        ]);
    }
}
