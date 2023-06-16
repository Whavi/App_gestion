<?php

namespace App\Controller;

use App\Repository\CollaborateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{
    #[Route('/pdf/email/{id<\d+>}', name:'user_gestion_attribution_email')]
    public function sendEmail(int $id, MailerInterface $mailer, CollaborateurRepository $collaborateurRepository): Response
    {
        $collaborateurEmail = $collaborateurRepository->findAllOrderedByCollaborateurEmail($id);
        $email = (new Email())
            ->from('IT-SIF@secours-islamique.org')
            ->to('test@test.fr')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Bon de commande SIF')
            // ->attachFromPath('user_gestion_attribution_pdf')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
        return $this->redirectToRoute('user_gestion_attribution');
    }
}