<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participant')]
class ParticipantController extends AbstractController
{
    #[Route('/', name: 'app_participant_index', methods: ['GET'])]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->redirectToRoute('app_admin_user');
    }

    #[Route('/new', name: 'app_participant_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        #[Autowire('%photo_dir%')] string $photoDir
    ): Response
    {

        $user = new Participant();
        $user->setActif(1);
        $participantForm = $this->createForm(ParticipantType::class, $user);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {

            //Pseudo par défaut (Prénom + 1ere lettre du Nom)
            $user->setPseudo(
                $user->getPrenom() . ' ' . substr($user->getNom(), 0, 1) . '.'
            );
            // Haschage du password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $participantForm->get('plainPassword')->getData()
                )
            );

            //Upload de la photo
            if ($photo = $participantForm['photo']->getData()){
                $fileName = uniqid().'.'.$photo->guessExtension();
                $photo->move($photoDir, $fileName);
            }
            $user->setImageFileName($fileName);


            $entityManager->persist($user);
            $entityManager->flush();



            return $this->redirectToRoute('app_participant_index');
        }

        return $this->render('participant/new.html.twig', [
            'participantForm' => $participantForm,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_show', methods: ['GET'])]
    public function show(Participant $participant): Response
    {
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,
                         Participant $participant,
                         EntityManagerInterface $entityManager): Response
    {
        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_participant_index');
        }

        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            'participantForm' => $participantForm,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_delete', methods: ['POST'])]
    public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->request->get('_token'))) {
            $entityManager->remove($participant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }
}
