<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/ville')]
class VilleController extends AbstractController
{
    #[Route('/', name: 'app_ville_index', methods: ['GET','POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        VilleRepository $villeRepository
    ): Response
    {
        // Création d'une nouvelle ville
        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        // Enregistrement dans la bdd si rempli et validé
        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            // Affichage du message "succes" en cas de validation
            $this->addFlash('success', $ville->getNom() . " a bien été créée !");
            return $this->redirectToRoute('app_ville_index');
        }

        // Affichage de la liste des villes créées et du formulaire de création
        return $this->render('ville/index.html.twig', [
            'villes' => $villeRepository->findAll(),
            'villeForm' => $villeForm
        ]);
    }

    #[Route('/{id}', name: 'app_ville_show', methods: ['GET'])]
    public function show(Ville $ville): Response
    {
        // Affichage le détails sur la ville
        return $this->render('ville/show.html.twig', [
            'ville' => $ville,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ville_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Ville $ville,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Modification d'une ville (nom ou code postal)
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Retour à la liste des villes après modification
            return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
        }

        // Affichage de la liste des villes, avec la ville modifié (et toujours du formulaire de création)
        return $this->render('ville/edit.html.twig', [
            'ville' => $ville,
            'villeForm' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ville_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Ville $ville,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Suppression d'une ville
        if ($this->isCsrfTokenValid('delete'.$ville->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ville);
            $entityManager->flush();
        }
        // Retour à la liste des villes après suppression
        return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
    }


}
