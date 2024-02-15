<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isEmpty;
use Symfony\Component\Validator\Constraints\DateTime;
use function Symfony\Component\Clock\now;
use Symfony\Component\HttpFoundation\Cookie;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'app_sortie_index', methods: ['GET', 'POST'])]
    public function index(SortieRepository $sortieRepository, Request $request, CampusRepository $campusRepository, PaginatorInterface $paginator): Response
    {
        $search = $request->query->all();

        // Vérifier s'il y a des données de recherche dans la requête
        if (empty($search)) {
            // Si non, essayer de récupérer les données du cookie
            $searchCookie = $request->cookies->get('search');

            // Vérifier si le cookie existe et n'est pas vide
            if ($searchCookie !== null && !empty($searchCookie)) {
                // Décoder les données JSON du cookie
                $search = json_decode($searchCookie, true);
            }
        }
        $user = $this->getUser();

        if (empty($search['campus']) || $search['campus'] === 'Choisissez un campus') {
            $search['campus'] = $user->getCampus()->getId();
        }
        if (empty($search['search'])) {
            $search['search'] = '';
        }
        if (empty($search['date1'])) {
            $search['date1'] = date('Y').'-01-01';
        }
        if (empty($search['date2'])) {
            $search['date2'] = date('Y').'-12-31';
        }

        // Créer un cookie avec la valeur de $search
        $cookie = new Cookie('search', json_encode($search), time() + (3600 * 24 * 30)); // Cookie valide pendant 30 jours

        // Ajouter le cookie à la réponse
        $response = new Response();
        $response->headers->setCookie($cookie);
        $response->send();

        $sorties = $sortieRepository->searchByName($search);
        $campus = $campusRepository->findAll();

        // Pagination
        $pagination = $paginator->paginate(
            $sorties,
            $request->query->get('page', 1),
            5
        );

        dump($pagination);
        dump($search);
        dump($user);

        return $this->render('sortie/index.html.twig', [
            'campuses' => $campus,
            'search' => $search,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        EntityManagerInterface $entityManager,
                        EtatRepository $etatRepository,
                        LieuRepository $lieuRepository,
                        VilleRepository $villeRepository,
                        SessionInterface $session): Response
    {
        $search = $session->get('search', []);

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
    public function show(Sortie $sortie, SessionInterface $session): Response
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

            $this->addFlash('Success', '"' . $sortie->getNom() . '" a bien été modifiée');
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
        $now = new \DateTime();

        // Vérifier si la sortie a l'état "ouvert"
        if ($sortie->getEtat()->getId() !== 1) {
            $this->addFlash('Fail', "Impossible de s'inscrire à cette sortie car elle n'est pas ouverte.");
            return $this->redirectToRoute('app_sortie_index');
        }

        if ($sortie->getNbInscriptionsMax() <= $sortie->getParticipant()->count()) {
            $this->addFlash('Fail', "La sortie est déjà pleine");
        } else {
            if ($sortie->getDateLimiteInscription() < $now) {
                $this->addFlash('Fail', "La date d'inscription est dépassée");
            } else {
                $participant = $this->getUser();
                $sortie->addParticipant($participant);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('Success', 'Votre inscription a bien été prise en compte');
            }
        }

        return $this->redirectToRoute('app_sortie_index');
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
        return $this->redirectToRoute('app_sortie_index');
        }

    #[Route('/publier/{id}', name: 'app_sortie_publier', methods: ['GET'])]
    public function publier(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $participant = $this->getUser();
        $sortie->setEtat($etatRepository->find(1));
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('Success', 'Votre sortie "' . $sortie->getNom() . '" a bien été publiée');
        return $this->redirectToRoute('app_sortie_index');
    }

    #[Route('/annuler/{id}', name: 'app_sortie_annuler', methods: ['GET'])]
    public function annuler(Sortie $sortie, Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        // Récupérer la raison fournie par l'utilisateur depuis la requête
        $reason = $request->query->get('reason');

        $sortie->setEtat($etatRepository->find(2));
        $sortie->setMotifAnnulation($reason);
        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->redirectToRoute('app_sortie_index', [
            'sorties' => $sortie
        ]);
    }

}
