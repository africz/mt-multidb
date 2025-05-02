<?php

namespace App\Controller;

use App\Entity\DbConnections;
use App\Form\DbConnectionsForm;
use App\Repository\DbConnectionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/db/connections')]
final class DbConnectionsController extends AbstractController
{
    #[Route(name: 'app_db_connections_index', methods: ['GET'])]
    public function index(DbConnectionsRepository $dbConnectionsRepository): Response
    {
        return $this->render('db_connections/index.html.twig', [
            'db_connections' => $dbConnectionsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_db_connections_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dbConnection = new DbConnections();
        $form = $this->createForm(DbConnectionsForm::class, $dbConnection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dbConnection);
            $entityManager->flush();

            return $this->redirectToRoute('app_db_connections_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('db_connections/new.html.twig', [
            'db_connection' => $dbConnection,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_db_connections_show', methods: ['GET'])]
    public function show(DbConnections $dbConnection): Response
    {
        return $this->render('db_connections/show.html.twig', [
            'db_connection' => $dbConnection,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_db_connections_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DbConnections $dbConnection, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DbConnectionsForm::class, $dbConnection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_db_connections_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('db_connections/edit.html.twig', [
            'db_connection' => $dbConnection,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_db_connections_delete', methods: ['POST'])]
    public function delete(Request $request, DbConnections $dbConnection, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dbConnection->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($dbConnection);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_db_connections_index', [], Response::HTTP_SEE_OTHER);
    }
}
