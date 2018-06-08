<?php

namespace ShuttleTest\Migrator;

use PHPUnit\Framework\TestCase;
use Shuttle\Migrator\MigratorCollection;
use Shuttle\Migrator\MigratorInterface;
use ShuttleTest\Fixture\TestMigrator;

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
        $a = new TestMigrator('a', ['b', 'c']);
        $b = new TestMigrator('b', ['c', 'd']);
        $c = new TestMigrator('c', ['d']);
        $d = new TestMigrator('d');

        $coll = new MigratorCollection([$a, $b, $c, $d]);
        /** @var MigratorInterface[] $arr */
        $arr = iterator_to_array($coll->getIterator());

        $this->assertEquals(4, count($arr));
        $this->assertEquals('d', $arr[0]->getName());
        $this->assertEquals('c', $arr[1]->getName());
        $this->assertEquals('b', $arr[2]->getName());
        $this->assertEquals('a', $arr[3]->getName());
    }

    /**
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     * @expectedException \MJS\TopSort\CircularDependencyException
     */
    public function testGetIteratorThrowsExceptionOnCircularDependency()
    {
        $a = new TestMigrator('a', ['b']);
        $b = new TestMigrator('b', ['a']);

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
        $coll->add(new TestMigrator('a', ['b']));
        $coll->getIterator();
    }

    /**
     *
     */
    public function testCountReturnsExpectedValue()
    {
        $a = new TestMigrator('a', ['b']);
        $b = new TestMigrator('b', []);

        $coll = new MigratorCollection([$a, $b]);
        $this->assertEquals(2, $coll->count());
    }

    /**
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function testResolveDependenciesReturnsDependenciesInExpectedOrder()
    {
        $a = new TestMigrator('a', ['b', 'e']);
        $b = new TestMigrator('b', ['c', 'd']);
        $c = new TestMigrator('c');
        $d = new TestMigrator('d');
        $e = new TestMigrator('e');

        $coll = new MigratorCollection([$a, $b, $c, $d, $e]);
        $arr = array_map(function (MigratorInterface $migrator) {
            return $migrator->getName();
        }, $coll->resolveDependencies($b));

        // Resulting dependency graph should include 'b' and its dependencies, but not 'a' and its dependencies ('e')
        $this->assertEquals(3, count($arr));
        $this->assertContains('b', $arr);
        $this->assertContains('c', $arr);
        $this->assertContains('d', $arr);

        // Also, 'b' should be last, since the dependencies should come first
        $this->assertEquals('b', end($arr));
    }
}
