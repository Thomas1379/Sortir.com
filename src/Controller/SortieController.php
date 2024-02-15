<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SearchType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isEmpty;
use Symfony\Component\Validator\Constraints\DateTime;

#[Route('/sortie')]
class SortieController extends AbstractController
{

    #[Route('/', name: 'app_sortie_index', methods: ['GET', 'POST'])]
    public function index(SortieRepository $sortieRepository, Request $request): Response
    {
        $search = $request->query->all();
        dump($request->query->all());
        dump(count($search));
        dump(empty($search));

        if(count($search) > 0) {
            $sorties = $sortieRepository->searchByName($search);
        } else {
            dump('else');
            $sorties = $sortieRepository->allTables();
        }

        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        EntityManagerInterface $entityManager,
                        EtatRepository $etatRepository,
                        LieuRepository $lieuRepository,
                        VilleRepository $villeRepository): Response
    {
        $sortie = new Sortie();
        $sortie->setCampus($this->getUser()->getCampus());
        $sortie->setOrganisateur($this->getUser());

        $villes = $villeRepository->getAllVille();

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submited = $request->request->get('submit');
            $lieu = $lieuRepository->findOneBy(['id' => $request->request->all('sortie')['lieu']]);
            $sortie->setLieu($lieu);

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
                        return $this->redirectToRoute('app_sortie_index');
                    }
            }
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'villes' => $villes,
            'form' => $form,
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
    public function edit(Request $request,
                         Sortie $sortie,
                         EntityManagerInterface $entityManager,
                         EtatRepository $etatRepository,
                         LieuRepository $lieuRepository,
                         VilleRepository $villeRepository): Response
    {
        $villes = $villeRepository->getAllVille();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie = $form->getData();
            $submited = $request->request->get('submit');
            $lieu = $lieuRepository->findOneBy(['id' => $request->request->all('sortie')['lieu']]);
            $sortie->setLieu($lieu);

            switch($submited) {
                case"enregistrer":
                {
                    $etat = $etatRepository->find(4);
                    $sortie->setEtat($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $this->addFlash('warning',"Merci, votre sortie a été modifiée, elle n'est pas encore publiée");
                    return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
                }

                case"publier":
                {
                    $etat = $etatRepository->find(1);
                    $sortie->setEtat($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $this->addFlash('success',"Merci, votre sortie est publiée");
                    return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
                }

                case"supprimer":
                {
                    $entityManager->remove($sortie);
                    $entityManager->flush();
//cette partie du code ne fonctionne pas (VQ)
//                    if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
//                          $entityManager->remove($sortie);
//                          $entityManager->flush();
//                    }
                    $this->addFlash('Success', 'Vous venez de supprimer la sortie "' . $sortie->getNom() . '"');
                    return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
                }

                case"annuler":
                {
                    $this->addFlash('warning',"Votre sortie n'a pas été enregistrée");
                    return $this->redirectToRoute('app_sortie_index');
                }
            }
        }

        return $this->render('sortie/edit.html.twig', [
                'sortie' => $sortie,
                'villes' => $villes,
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
        $now = new \DateTime();
        if( $sortie->getDateLimiteInscription()>$now) {
        $participant = $this->getUser();
        $sortie->addParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();
        $this->addFlash('Success', 'Votre inscription a bien été prise en compte');

        }else
        {
            $this->addFlash('Fail', "La date d'inscription est dépassée");
        }
        return $this->redirectToRoute('app_sortie_index', [
            'sorties' => $sortie
        ]);
    }
    #[Route('/seDesister/{id}', name: 'app_sortie_seDesister', methods: ['GET'])]
    public function seDesister(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $now = new \DateTime();
        if( $sortie->getDateHeureDebut()>$now) {
        $participant = $this->getUser();
        $sortie->removeParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('Success', 'Vous venez de vous désinscrire de la sortie : ' . $sortie->getNom());

        }
        else{
            $this->addFlash('Fail', "trop tard ! La date de la sortie est dépassée");
                }
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

        $this->addFlash('Success', 'Votre sortie "' . $sortie->getNom() . '" a bien été publiée');
        return $this->redirectToRoute('app_sortie_index', [
            'sorties' => $sortie
        ]);
    }

}
