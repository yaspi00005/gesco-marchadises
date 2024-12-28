<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class WhatsappController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

   
    #[Route(path: '/send-whatsapp', name: 'send_whatsapp')]
    public function sendWhatsAppMessage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $phoneNumber ='22378478742'; // Numéro de téléphone du destinataire
        $message = "Yaya" ;         // Message à envoyer

        if (!$phoneNumber || !$message) {
            return new JsonResponse(['error' => 'Numéro ou message manquant.'], 400);
        }

        $accessToken = 'EAANP4kMCJB4BO9NnpZBDjY2d69rHIjPiVrKpPA2SRrTpWsM8pX5AkTcXf9f9XgtJA0b2aKfNqbRHMVyXp6XFV11xTK7RJgy8HG0DuX9kwabREXp9lLxsGZBY99fzQwD9V0LkUAqijJ5BV6z4I3wxUAi9JFqZA7pVR2PQsw0FnIITXCGDKHdwrTZBmQwLqo7LcmEkGZAZA7zKi4jw6PZAq2mIYT31qTMDvLRTZANt5Ni8'; // Remplacez par votre token API WhatsApp Business
        $whatsappApiUrl = 'https://graph.facebook.com/v15.0/{PhoneNumberId}/messages';

        try {
            $response = $this->httpClient->request('POST', $whatsappApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to' => $phoneNumber,
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray();

            if ($statusCode === 200) {
                return new JsonResponse(['success' => 'Message envoyé avec succès.', 'data' => $content]);
            }

            return new JsonResponse(['error' => 'Erreur lors de l\'envoi du message.', 'data' => $content], $statusCode);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
}
