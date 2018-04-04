<?php

namespace ride\library\dependency;

use PHPUnit\Framework\TestCase;

class DependencyContainerTest extends TestCase {

    public function testConstruct() {
        $container = new DependencyContainer();

        $this->assertNotNull($container);
    }

    public function testGetDependencies() {
        $container = new DependencyContainer();

        $this->assertEquals(array(), $container->getDependencies());
        $this->assertEquals(array(), $container->getDependencies('interface'));
    }

    public function testRemoveDependency() {
        $container = new DependencyContainer();
        $className = 'className';
        $for = 'foo';
        $id = 'd0';
        $dependency = new Dependency($className);
        $dependency->addInterface($for);

        $container->addDependency($dependency);

        $this->assertTrue($container->removeDependency($for, $id));
        $this->assertFalse($container->removeDependency('bar', 'd1'));
    }

    public function testGetDependenciesByTag() {
        $container = new DependencyContainer();

        $tagInclude = 'include';
        $tagExclude = 'exclude';

        $for = 'foo';
        $className = 'className';

        $dependency1 = new Dependency($className, 'd1');
        $dependency1->addInterface($for);
        $dependency1->addTag($tagInclude);

        $dependency2 = new Dependency($className, 'd2');
        $dependency2->addInterface($for);
        $dependency2->addTag($tagExclude);

        $dependency3 = new Dependency($className, 'd3');
        $dependency3->addInterface($for);
        $dependency3->addTag($tagInclude);
        $dependency3->addTag($tagExclude);

        $container->addDependency($dependency1);
        $container->addDependency($dependency2);
        $container->addDependency($dependency3);

        $this->assertSame(array(), $container->getDependenciesByTag($for, 'tag'));
        $this->assertSame(array($dependency1, $dependency3), $container->getDependenciesByTag($for, $tagInclude));
        $this->assertSame(array($dependency1), $container->getDependenciesByTag($for, null, $tagExclude));
        $this->assertSame(array($dependency1), $container->getDependenciesByTag($for, $tagInclude, $tagExclude));
    }

    public function testAddDependency() {
        $container = new DependencyContainer();

        $for = 'foo';
        $className = 'className';
        $id = 'd0';
        $dependency = new Dependency($className);
        $dependency->addInterface($for);

        $container->addDependency($dependency);
        $expected = array($for => array($id => $dependency));

        $this->assertEquals($expected, $container->getDependencies());
        $this->assertEquals($id, $dependency->getId());

        $id = 'id';
        $dependency = clone $dependency;
        $dependency->setId($id);
        $container->addDependency($dependency);
        $expected[$for][$id] = $dependency;

        $this->assertEquals($expected, $container->getDependencies());

        $dependency = clone $dependency;
        $dependency->removeInterface($for);
        $for = "bar";
        $dependency->addInterface($for);
        $container->addDependency($dependency);
        $expected[$for][$id] = $dependency;

        $this->assertEquals($expected, $container->getDependencies());

        $dependency = clone $dependency;
        $dependency->removeInterface($for);
        $for = "foo";
        $dependency->addInterface($for);
        $dependency->setId();
        $container->addDependency($dependency);
        $expected[$for]['d2'] = $dependency;

        $this->assertEquals($expected, $container->getDependencies());
        $this->assertEquals('d2', $dependency->getId());
    }

    /**
     * @expectedException ride\library\dependency\exception\DependencyException
     */
    public function testAddDependencyWithoutInterfacesThrowsException() {
        $dependency = new Dependency('className');

        $container = new DependencyContainer();
        $container->addDependency($dependency);
    }

    /**
     * @expectedException ride\library\dependency\exception\DependencyException
     */
    public function testGetDependenciesWithInvalidInterfaceThrowsException() {
        $container = new DependencyContainer();
        $container->getDependencies($this);
    }

}
