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
        // Recherche d'une ville (par nom ou code postal)
       $searchTerm = $request->query->get('search', '');

        if ($searchTerm != '') {
            $villes = $villeRepository->searchByNomOrCodePostal($searchTerm);;
        } else {
            $villes = $villeRepository->findAll();
        }

        // Création d'une nouvelle ville
        $ville = new Ville();

        $villeForm = $this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);

            // Enregistrement dans la bdd si rempli et validé
                if ($villeForm->isSubmitted() && $villeForm->isValid()) {

                    $allVilles = $villeRepository->findAll();
                    foreach ($allVilles as $allVille) {
                        if ($allVille->getNom() == $ville->getNom() && $allVille->getCodePostal() == $ville->getCodePostal()) {
                            $this->addFlash('fail', "votre ville existe deja ");
                            return $this->redirectToRoute('app_ville_index');
                        }
                    }
                    $entityManager->persist($ville);
                    $entityManager->flush();

                    // Affichage du message "succes" en cas de validation
                    $this->addFlash('success', $ville->getNom() . " a bien été créée !");
                    return $this->redirectToRoute('app_ville_index');
                }

            // Affichage de la liste des villes créées et du formulaire de création
            return $this->render('ville/index.html.twig', [
                'searchTerm' => $searchTerm,
                'villes' => $villes,
                'villeForm' => $villeForm,
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

    #[Route('/{id}', name: 'app_ville_delete', methods: ['GET', 'POST'])]
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

            return $this->redirectToRoute('app_ville_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ville/_delete_form.html.twig', [
            'ville' => $ville,
        ]);
    }

}
