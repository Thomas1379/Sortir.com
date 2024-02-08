<?php

namespace App\Test\Controller;

use App\Entity\Sortie;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SortieControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/sortie/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Sortie::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Sortie index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'sortie[nom]' => 'Testing',
            'sortie[dateHeureDebut]' => 'Testing',
            'sortie[duree]' => 'Testing',
            'sortie[dateLimiteInscription]' => 'Testing',
            'sortie[nbInscriptionsMax]' => 'Testing',
            'sortie[infosSortie]' => 'Testing',
            'sortie[motifAnnulation]' => 'Testing',
            'sortie[Etat]' => 'Testing',
            'sortie[Lieu]' => 'Testing',
            'sortie[Campus]' => 'Testing',
            'sortie[organisateur]' => 'Testing',
            'sortie[Participant]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sortie();
        $fixture->setNom('My Title');
        $fixture->setDateHeureDebut('My Title');
        $fixture->setDuree('My Title');
        $fixture->setDateLimiteInscription('My Title');
        $fixture->setNbInscriptionsMax('My Title');
        $fixture->setInfosSortie('My Title');
        $fixture->setMotifAnnulation('My Title');
        $fixture->setEtat('My Title');
        $fixture->setLieu('My Title');
        $fixture->setCampus('My Title');
        $fixture->setOrganisateur('My Title');
        $fixture->setParticipant('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Sortie');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sortie();
        $fixture->setNom('Value');
        $fixture->setDateHeureDebut('Value');
        $fixture->setDuree('Value');
        $fixture->setDateLimiteInscription('Value');
        $fixture->setNbInscriptionsMax('Value');
        $fixture->setInfosSortie('Value');
        $fixture->setMotifAnnulation('Value');
        $fixture->setEtat('Value');
        $fixture->setLieu('Value');
        $fixture->setCampus('Value');
        $fixture->setOrganisateur('Value');
        $fixture->setParticipant('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'sortie[nom]' => 'Something New',
            'sortie[dateHeureDebut]' => 'Something New',
            'sortie[duree]' => 'Something New',
            'sortie[dateLimiteInscription]' => 'Something New',
            'sortie[nbInscriptionsMax]' => 'Something New',
            'sortie[infosSortie]' => 'Something New',
            'sortie[motifAnnulation]' => 'Something New',
            'sortie[Etat]' => 'Something New',
            'sortie[Lieu]' => 'Something New',
            'sortie[Campus]' => 'Something New',
            'sortie[organisateur]' => 'Something New',
            'sortie[Participant]' => 'Something New',
        ]);

        self::assertResponseRedirects('/sortie/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getDateHeureDebut());
        self::assertSame('Something New', $fixture[0]->getDuree());
        self::assertSame('Something New', $fixture[0]->getDateLimiteInscription());
        self::assertSame('Something New', $fixture[0]->getNbInscriptionsMax());
        self::assertSame('Something New', $fixture[0]->getInfosSortie());
        self::assertSame('Something New', $fixture[0]->getMotifAnnulation());
        self::assertSame('Something New', $fixture[0]->getEtat());
        self::assertSame('Something New', $fixture[0]->getLieu());
        self::assertSame('Something New', $fixture[0]->getCampus());
        self::assertSame('Something New', $fixture[0]->getOrganisateur());
        self::assertSame('Something New', $fixture[0]->getParticipant());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sortie();
        $fixture->setNom('Value');
        $fixture->setDateHeureDebut('Value');
        $fixture->setDuree('Value');
        $fixture->setDateLimiteInscription('Value');
        $fixture->setNbInscriptionsMax('Value');
        $fixture->setInfosSortie('Value');
        $fixture->setMotifAnnulation('Value');
        $fixture->setEtat('Value');
        $fixture->setLieu('Value');
        $fixture->setCampus('Value');
        $fixture->setOrganisateur('Value');
        $fixture->setParticipant('Value');

        $this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/sortie/');
        self::assertSame(0, $this->repository->count([]));
    }
}
