<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieCreateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name: 'app_sortie_')]
class SortieController extends AbstractController
{
    #[Route('/create', name: 'create')]
    public function create(Request $request,
    EntityManagerInterface $entityManager
): Response
    {
        //$user = $this->getUser();
        //dd($user);
        $sortie = new Sortie();
        $form = $this->createForm(SortieCreateType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager->persist($sortie);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_sortie_create');
        }

        return $this->render('sortie/create.html.twig', [
            'SortieCreateType' => $form->createView()
        ]);
    }
}
