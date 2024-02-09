<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'app_sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository, Request $request): Response
    {



        $sortie = $sortieRepository->AllTables();
        //dd($sortie);
        return $this->render('sortie/index.html.twig', ['sorties' => $sortie,]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();
        $sortie->setCampus($this->getUser()->getCampus());
        $sortie->setOrganisateur($this->getUser());
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $submited = $request->request->get('submit');

            switch($submited) {
                case"enregistrer":
                    {
                        $etat = $etatRepository->find(4);
                        $sortie->setEtat($etat);
                        $entityManager->persist($sortie);
                        $entityManager->flush();
                        $this->addFlash('warning',"Merci, votre sortie est créée, elle n'est pas encore publiée");
                        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
                    }

                case"publier":
                    {
                        $etat = $etatRepository->find(1);
                        $sortie->setEtat($etat);
                        $entityManager->persist($sortie);
                        $entityManager->flush();
                        $this->addFlash('success',"Merci, votre sortie est créée et publiée");
                        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
                    }

                case"annuler":
                    {
                        $this->addFlash('success',"Votre sortie n'a pas été enregistrée");
                        return $this->redirectToRoute('app_sortie_new');
                    }
            }
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('Success', '"' . $sortie->getNom() . '" à bien été modifier');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        $this->addFlash('Success', 'Vous venez de supprimer la sortie "' . $sortie->getNom() . '"');
        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/inscription/{id}', name: 'app_sortie_inscription', methods: ['GET'])]
    public function inscription(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $participant = $this->getUser();
        $sortie->addParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('Success', 'Votre inscription a bien était prise en compte');
        return $this->redirectToRoute('app_sortie_index', [
            'sorties' => $sortie
        ]);
    }
    #[Route('/seDesister/{id}', name: 'app_sortie_seDesister', methods: ['GET'])]
    public function seDesister(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $participant = $this->getUser();
        $sortie->removeParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('Success', 'Vous venez de vous désinscrire de la sortie : ' . $sortie->getNom());
        return $this->redirectToRoute('app_sortie_index', [
            'sorties' => $sortie
        ]);
    }
    #[Route('/publier/{id}', name: 'app_sortie_publier', methods: ['GET'])]
    public function publier(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $participant = $this->getUser();
        $sortie->setEtat($etatRepository->find(1));
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('Success', 'Votre sortie "' . $sortie->getNom() . '" à bien était publier');
        return $this->redirectToRoute('app_sortie_index', [
            'sorties' => $sortie
        ]);
    }
}
