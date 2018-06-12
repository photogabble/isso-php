<?php

namespace App\Tests\Unit;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    /**
     * @var EntityManager
     */
    protected static $em;

    /**
     * This method is called before the first test of this test class is run.
     *
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        $configuration = include __DIR__ . '/../../config.php';
        $em = EntityManager::create(
            $configuration['database'],
            Setup::createAnnotationMetadataConfiguration(
                [
                    realpath(__DIR__ . '/../../src/Entities')
                ],
                true
            )
        );
        $tool = new SchemaTool($em);
        $tool->dropDatabase();
        $tool->createSchema($em->getMetadataFactory()->getAllMetadata());
        self::$em = $em;
    }

    public function testEntity()
    {
        // ...
    }
}