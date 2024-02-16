<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('admin/register', name: 'app_admin_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        #[Autowire('%photo_dir%')] string $photoDir
    ): Response
    {

        $user = new Participant();
        $user->setActif(1);
        $user->setRoles(["ROLE_PARTICIPANT"]);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Pseudo par défaut (Prénom + 1ere lettre du Nom)
            $user->setPseudo(
                $user->getPrenom() . ' ' . substr($user->getNom(), 0, 1) . '.'
            );
            // Haschage du password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            //Upload de la photo
            if ($photo = $form['photo']->getData()){
                $fileName = uniqid().'.'.$photo->guessExtension();
                $photo->move($photoDir, $fileName);
            }
            $user->setImageFileName($fileName);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
