<?php
namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

class AzureController extends AbstractController
{

    #[Route('/connect/azure', name: 'connect_azure_start')]    
    public function connectAction(ClientRegistry $clientRegistry)
    {
        // will redirect to Azure!
        return $clientRegistry
            ->getClient('azure')
            ->redirect(["openid", "profile", "email"], []);
    }

    #[Route('/connect/azure/check', name: 'connect_azure_check')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\AzureClient $client */
        $client = $clientRegistry->getClient('azure');

        try {
            // the exact class depends on which provider you're using
            /** @var \League\OAuth2\Client\Provider\GenericResourceOwner $user */
            $user = $client->fetchUser();

            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            var_dump($user); die;
            // ...
        } catch (IdentityProviderException $e) {
            var_dump($e->getMessage()); die;
        }
    }
}
