<?php

namespace App\Service;

use Twilio\Rest\Client;

class SmsService
{
    private string $twilioSid;
    private string $twilioAuthToken;
    private string $twilioPhoneNumber;

    public function __construct(string $twilioSid, string $twilioAuthToken, string $twilioPhoneNumber)
    {
        $this->twilioSid = $twilioSid;
        $this->twilioAuthToken = $twilioAuthToken;
        $this->twilioPhoneNumber = $twilioPhoneNumber;
    }

    public function sendSms(string $to, string $messageBody): void
    {
        // Vérification des valeurs chargées
        if (empty($this->twilioSid) || empty($this->twilioAuthToken) || empty($this->twilioPhoneNumber)) {
            throw new \Exception("Les informations Twilio ne sont pas chargées correctement.");
        }

        // Initialisation du client Twilio avec les paramètres injectés
        $client = new Client($this->twilioSid, $this->twilioAuthToken);

        // Envoi du message
        $message = $client->messages->create(
            $to, 
            [
                "from" => $this->twilioPhoneNumber,
                "body" => $messageBody
            ]
        );

        // Afficher le SID du message (utile pour le debug)
        print($message->sid);
    }
}
