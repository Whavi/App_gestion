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
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/azure", name="connect_azure_start")
     */
    #[Route('/connect/azure', name: 'connect_azure_start')]    
    #[IsGranted('ROLE_USER')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        // will redirect to Azure!
        return $clientRegistry
            ->getClient('azure') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect();
    }

    /**
     * After going to Azure, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/azure/check", name="connect_azure_check")
     */
    #[Route('/connect/azure/check', name: 'connect_azure_check')]
    #[IsGranted('ROLE_USER')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

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
            // something went wrong!
            // probably you should return the reason to the user
            var_dump($e->getMessage()); die;
        }
    }
}
