<?php

namespace ShuttleTest\Migrator;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Shuttle\Migrator\MigratorInterface;
use Shuttle\MigratorCollection;

/**
 * Class MigratorCollectionTest
 * @package ShuttleTest\Migrator
 */
class MigratorCollectionTest extends TestCase
{
    public function testInstantiationSucceeds()
    {
        $obj = new MigratorCollection();
        $this->assertInstanceOf(MigratorCollection::class, $obj);
    }

    /**
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function testGetIteratorIteratesBasedOnDependencies()
    {
        $a = $this->mockMigrator('a', ['b', 'c']);
        $b = $this->mockMigrator('b', ['c', 'd']);
        $c = $this->mockMigrator('c', ['d']);
        $d = $this->mockMigrator('d');

        $coll = new MigratorCollection([$a, $b, $c, $d]);
        /** @var MigratorInterface[] $arr */
        $arr = iterator_to_array($coll->getIterator());

        $this->assertEquals(4, count($arr));
        $this->assertEquals('d', (string) $arr[0]);
        $this->assertEquals('c', (string) $arr[1]);
        $this->assertEquals('b', (string) $arr[2]);
        $this->assertEquals('a', (string) $arr[3]);
    }

    /**
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     * @expectedException \MJS\TopSort\CircularDependencyException
     */
    public function testGetIteratorThrowsExceptionOnCircularDependency()
    {
        $a = $this->mockMigrator('a', ['b']);
        $b = $this->mockMigrator('b', ['a']);

        $coll = new MigratorCollection([$a, $b]);
        $coll->getIterator();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNonExistentMigratorThrowsExeption()
    {
        $coll = new MigratorCollection();
        $coll->get('nothing');
    }

    /**
     * @expectedException \MJS\TopSort\ElementNotFoundException
     * @throws \MJS\TopSort\CircularDependencyException
     */
    public function testReferToNonExistentDependencyThrowsException()
    {
        $coll = new MigratorCollection();
        $coll->add($this->mockMigrator('a', ['b']));
        $coll->getIterator();
    }

    /**
     *
     */
    public function testCountReturnsExpectedValue()
    {
        $a = $this->mockMigrator('a', ['b']);
        $b = $this->mockMigrator('b', []);

        $coll = new MigratorCollection([$a, $b]);
        $this->assertEquals(2, $coll->count());
    }

    /**
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function testResolveDependenciesReturnsDependenciesInExpectedOrder()
    {
        $a = $this->mockMigrator('a', ['b', 'e']);
        $b = $this->mockMigrator('b', ['c', 'd']);
        $c = $this->mockMigrator('c');
        $d = $this->mockMigrator('d');
        $e = $this->mockMigrator('e');

        $coll = new MigratorCollection([$a, $b, $c, $d, $e]);
        $arr = array_map(function (MigratorInterface $migrator) {
            return (string) $migrator;
        }, iterator_to_array($coll->resolveDependencies([$b])));

        // Resulting dependency graph should include 'b' and its dependencies, but not 'a' and its dependencies ('e')
        $this->assertEquals(3, count($arr));
        $this->assertContains('b', $arr);
        $this->assertContains('c', $arr);
        $this->assertContains('d', $arr);

        // Also, 'b' should be last, since the dependencies should come first
        $this->assertEquals('b', end($arr));
    }

    /**
     * @param string $name
     * @param array $dependsOn
     * @return MigratorInterface
     */
    private function mockMigrator(string $name, array $dependsOn = []): MigratorInterface
    {
        /** @var MigratorInterface|MockInterface $mock */
        $mock = \Mockery::mock(MigratorInterface::class);
        $mock->shouldReceive('__toString')->andReturn($name);
        $mock->shouldReceive('getDependsOn')->andReturn($dependsOn);
        return $mock;
    }
}
