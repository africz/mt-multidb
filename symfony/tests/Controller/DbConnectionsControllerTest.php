<?php

namespace App\Tests\Controller;

use App\Entity\DbConnections;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DbConnectionsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $dbConnectionRepository;
    private string $path = '/db/connections/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->dbConnectionRepository = $this->manager->getRepository(DbConnections::class);

        foreach ($this->dbConnectionRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('DbConnection index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'db_connection[name]' => 'Testing',
            'db_connection[host]' => 'Testing',
            'db_connection[port]' => 'Testing',
            'db_connection[username]' => 'Testing',
            'db_connection[password]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->dbConnectionRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new DbConnections();
        $fixture->setName('My Title');
        $fixture->setHost('My Title');
        $fixture->setPort('My Title');
        $fixture->setUsername('My Title');
        $fixture->setPassword('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('DbConnection');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new DbConnections();
        $fixture->setName('Value');
        $fixture->setHost('Value');
        $fixture->setPort('Value');
        $fixture->setUsername('Value');
        $fixture->setPassword('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'db_connection[name]' => 'Something New',
            'db_connection[host]' => 'Something New',
            'db_connection[port]' => 'Something New',
            'db_connection[username]' => 'Something New',
            'db_connection[password]' => 'Something New',
        ]);

        self::assertResponseRedirects('/db/connections/');

        $fixture = $this->dbConnectionRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getHost());
        self::assertSame('Something New', $fixture[0]->getPort());
        self::assertSame('Something New', $fixture[0]->getUsername());
        self::assertSame('Something New', $fixture[0]->getPassword());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new DbConnections();
        $fixture->setName('Value');
        $fixture->setHost('Value');
        $fixture->setPort('Value');
        $fixture->setUsername('Value');
        $fixture->setPassword('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/db/connections/');
        self::assertSame(0, $this->dbConnectionRepository->count([]));
    }
}
