<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LogControllerTest extends WebTestCase
{
    private const LOGS_ENDPOINT = '/api/v1/logs';

    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected SchemaTool $schemaTool;
    protected array $metaData;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        if ('test' !== self::$kernel->getEnvironment()) {
            throw new \LogicException('Tests cases with fresh database must be executed in the test environment');
        }

        $this->em = self::$kernel->getContainer()->get('doctrine')->getManager();

        $this->metaData = $this->em->getMetadataFactory()->getAllMetadata();
        $this->schemaTool = new SchemaTool($this->em);
        $this->schemaTool->updateSchema($this->metaData);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $purger = new ORMPurger($this->em);

        $purger->setPurgeMode(2);
        $purger->purge();
    }

    public function testGetLogs(): void
    {
        $this->client->request(Request::METHOD_GET, self::LOGS_ENDPOINT);

        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testPostLogs(): void
    {
        $this->client->request(Request::METHOD_POST, self::LOGS_ENDPOINT);

        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    public function testPutLogs(): void
    {
        $this->client->request(Request::METHOD_PUT, self::LOGS_ENDPOINT);

        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    public function testDeleteLogs(): void
    {
        $this->client->request(Request::METHOD_DELETE, self::LOGS_ENDPOINT);

        $response = $this->client->getResponse();

        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }
}
