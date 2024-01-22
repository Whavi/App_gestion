<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class YousignService{
    public function __construct(private HttpClientInterface $yousignClient){}

    public function signatureRequest() : array {
        $response = $this->yousignClient->request(
            'POST',
            'signature_requests',
            [
                'body' =>  <<<JSON
                {
                  "name": "Bon de commande",
                  "delivery_mode": "email",
                  "timezone": "Europe/Paris"
                }
                JSON,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
            );
        return $response->toArray();
    }

    public function uploadDocument(String $signatureRequestId, String $filename): array {
        $formFields = [
            'nature' => 'signable_document',
            'file' => DataPart::fromPath(self::PATHFILE.$filename, $filename, 'application/pdf')
        ];
        $formData = new FormDataPart($formFields);
        $headers = $formData->getPreparedHeaders()->toArray();

        $response = $this->yousignClient->request();
    }
}