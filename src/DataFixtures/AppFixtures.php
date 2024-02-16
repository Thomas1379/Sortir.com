<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->faker = Factory::create('fr_FR');
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        //Etat
        $etats = ['Ouvert', 'Fermée', 'En Cours', 'En Création'];
        $i = 1;
        foreach ($etats as $e) {
            $etat = new Etat();
            $etat->setLibelle($e);
            $etat->setId($i);
            $manager->persist($etat);
            $i++;
        }

        $manager->flush();

        //Campus
        for ($i = 0; $i < 5; $i++) {
            $campus = new Campus();
            $campus->setNom('Campus_' . $this->faker->region());
            $manager->persist($campus);
        }
        $campus->setNom('Campus En Ligne');
        $manager->persist($campus);

        //Ville
        for ($i = 0; $i < 5; $i++) {
            $ville = new Ville();
            $ville->setNom($this->faker->city());
            $ville->setCodePostal($this->faker->randomNumber(5, true));
            $manager->persist($ville);
        }

        $manager->flush();

        //Lieu
        $villeRepository = $manager->getRepository(Ville::class);
        $villes = $villeRepository->findAll();
        for ($i = 0; $i < 5; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($this->faker->word());
            $lieu->setRue($this->faker->streetAddress());
            $lieu->setLatitude($this->faker->latitude());
            $lieu->setLongitude($this->faker->longitude());
            $lieu->setVille($villes[rand(0,4)]);
            $manager->persist($lieu);
        }

        //Participant
        $campusRepository = $manager->getRepository(Campus::class);
        $campuses = $campusRepository->findAll();
        $admin = new Participant();
        $admin->setCampus($campuses[rand(0,4)]);
        $admin->setActif(true);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPrenom($this->faker->firstName());
        $admin->setNom($this->faker->lastName());
        $admin->setPseudo($admin->getPrenom() . ' ' . substr($admin->getNom(), 0, 1) . '.');
        $admin->setEmail('admin@admin.fr');
        $hash = $this->hasher->hashPassword($admin, 'admin');
        $admin->setPassword($hash);
        $admin->setTelephone($this->faker->phoneNumber());
        $manager->persist($admin);
        for ($j=0; $j<9; $j++) {
            $user = new Participant();
            $user->setCampus($campuses[rand(0,4)]);
            $user->setActif(true);
            $user->setRoles(['ROLE_PARTICIPANT']);
            $user->setPrenom($this->faker->firstName());
            $user->setNom($this->faker->lastName());
            $user->setPseudo($user->getPrenom() . ' ' . substr($user->getNom(), 0, 1) . '.');
            $user->setEmail('user' . $j + 1 . '@user.fr');
            $hash = $this->hasher->hashPassword($user, 'user');
            $user->setPassword($hash);
            $user->setTelephone($this->faker->phoneNumber());
            $manager->persist($user);
        }

        $manager->flush();

        // Sortie
        $etatsRepository = $manager->getRepository(Etat::class);
        $etats = $etatsRepository->findAll();
        $participantRepository = $manager->getRepository(Participant::class);
        $participants = $participantRepository->findAll();
        $lieusRepository = $manager->getRepository(Lieu::class);
        $lieus = $lieusRepository->findAll();
        for ($j=0; $j<50; $j++) {
            $sortie = new Sortie();
            $sortie->setInfosSortie($this->faker->paragraphs(3, true));
            $sortie->setNbInscriptionsMax($this->faker->numberBetween(5,99));
            $sortie->setDuree($this->faker->numberBetween(15,300));
            $sortie->setEtat($etats[rand(0,3)]);
            $sortie->setCampus($campuses[rand(0,4)]);
            $sortie->setOrganisateur($participants[rand(0,9)]);
            $sortie->setLieu($lieus[rand(0,4)]);
            $sortie->setDateHeureDebut($this->faker->dateTimeBetween('now', '+10 days'));
            $sortie->setDateLimiteInscription($this->faker->dateTimeBetween('-5 days', 'now'));
            $sortie->setNom($this->faker->words(2, true));

            $manager->persist($sortie);
        }

        $manager->flush();

        //Sortie_Participants
        $sortieRepository = $manager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findAll();
        for ($j=0; $j<200; $j++) {
            $sortie_part = $sorties[rand(0,49)];

            $sortie_part->addParticipant($participants[rand(0,9)]);

            $manager->persist($sortie_part);
        }

        $manager->flush();
    }
}
