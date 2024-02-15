<?php

namespace App\Controller;

use App\Form\LogFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\LogEntryRepository;
use Knp\Component\Pager\PaginatorInterface;

class LogEntryController extends AbstractController
{
    #[Route('/log/entry', name: 'admin_app_log_entry')]
    public function index(LogEntryRepository $logEntryRepository, PaginatorInterface $paginatorInterface, Request $request): Response
    { 
        $data = $logEntryRepository->findAllOrderedByLogNumber();
        $posts = $paginatorInterface->paginate($data, $request->query->getInt('page', 1), 30);

        $form = $this->createForm(LogFilterType::class); 
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $formData = $form->getData();

            // Filtrer les résultats en fonction des niveaux et catégories sélectionnés
            $filteredData = $logEntryRepository->filterByLevelsAndCategories($formData->getLevel(),$formData->getChannel());
            $posts = $paginatorInterface->paginate($filteredData, $request->query->getInt('page', 1), 30);

            return $this->render('pages/user/log_entry/index.html.twig', [
                'log_entries' => $posts,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('pages/user/log_entry/index.html.twig', [
            'log_entries' => $posts,
            'form' => $form->createView(),
        ]);
    }
}
