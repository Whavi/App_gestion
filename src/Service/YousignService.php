<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class YousignService{

    private const PATHFILE = __DIR__ . '/../../public/';
    public function __construct(private HttpClientInterface $yousignClient){}

    public function signatureRequest() : string {
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
        return $response->getContent();
    }

    public function uploadDocument(String $signatureRequestId, String $filename): string {
        $formFields = [
            'nature' => 'signable_document',
            'file' => DataPart::fromPath(self::PATHFILE.$filename, $filename, 'application/pdf')
        ];
        $formData = new FormDataPart($formFields);
        $headers = $formData->getPreparedHeaders()->toArray();

        $response = $this->yousignClient->request(
            'POST', 
             sprintf('signature_requests/%s/documents', $signatureRequestId), 
             [
               'headers' => $headers,
               'body' => $formData->bodyToIterable(),
             ]
        );
        return $response->getContent();
    }

    public function addSigner(string $signatureRequestId, string $documentId, string $email, string $prenom, string $nom): string
        {
            $response = $this->yousignClient->request(
                'POST',
                sprintf('signature_requests/%s/signers', $signatureRequestId),
                [
                    'body' => <<<JSON
                        {
                            "info": {
                                "first_name": "$prenom",
                                "last_name": "$nom",
                                "email": "$email",
                                "locale":"fr"
                            },
                            "fields":[
                                {
                                    "type":"signature",
                                    "document_id":"$documentId",
                                    "page":1,
                                    "x":77,
                                    "y":581
                                }
                            ],
                            "signature_level":"electronic_signature",
                            "signature_authentication_mode":"no_otp"
                        }
                        JSON,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ]);
                    return $response->getContent();
        }

    public function activateSignatureRequest(String $signatureRequestId): string {
        $response = $this->yousignClient->request(
            'POST',
            sprintf('signature_requests/%s/activate', $signatureRequestId)
        );

        return $response->getContent();
    }
}