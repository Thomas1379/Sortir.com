<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/user', name: 'user')]
    public function user(ParticipantRepository $participantRepository): Response
    {
        $users = $participantRepository->findAll();

        return $this->render('participant/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/desactivate/{id}', name: 'desactivate')]
    public function desactivate(Participant $user, EntityManagerInterface $entityManager): Response
    {

        if (!empty($user)) {
            $user->setActif(0);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "l'utilisateur " . $user->getEmail() . " a bien était désactivé");
        } else {
            $this->addFlash('fail', "l'utilisateur n'a pas était trouvé");
        }

        return $this->redirectToRoute('app_admin_user');
    }
    #[Route('/activate/{id}', name: 'activate')]
    public function activate(Participant $user, EntityManagerInterface $entityManager): Response
    {

        if (!empty($user)) {
            $user->setActif(1);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "l'utilisateur " . $user->getEmail() . " a bien était activé");
        } else {
            $this->addFlash('fail', "l'utilisateur n'a pas était trouvé");
        }

        return $this->redirectToRoute('app_admin_user');
    }

}
