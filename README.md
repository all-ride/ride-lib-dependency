# Ride: Dependency Injection Library

Dependency injection library of the PHP Ride framework.

This module can create objects and invoke callbacks with dynamic argument injection.
Read more about the dependency injection pattern on [Wikipedia](https://en.wikipedia.org/wiki/Dependency_injection).

## Dependency

The _Dependency_ class is used to define your class instances. 
You can tell what interfaces a class implements so the DependencyInjector knows when to use this instance.
Define method calls to build up your instance as you need it, ready to work.

When you have multiple instances of a class, you can set an id to the dependency to specify each instance.

Dependencies can be tagged to retrieve a dependency subset for a specified interface.

Instead of literally constructing the object, a dependency can also be defined as being constructed by a factory.

## DependencyInjector

The _DependencyInjector_ is the facade of this library.
It has different getters to retrieve a single or multiple instances.
Dependencies are requested by interface, optionally an id, or for multiple instances, tag(s).

When a requested interface, or instance dependency, is not defined in the container, an attempt is made to automatically construct the instance.

## DependencyContainer

The _DependencyContainer_ is like it sais, a container of dependencies.
All you definitions are kept here for the dependency injector to use as it's source.

## DependencyArgumentParser

When defining method calls for your dependencies, you can pass arguments to those calls.
You have different type of arguments.
This library defines the following types by default: _null_, _scalar_, _array_, _dependency_ and _call_.

By implementing the _DependencyArgumentParser_ interface, you can create your own argument types.
Ride will add the _parameter_ and _route_ type with the _ride-app_ and _ride-web_ modules.

## Code Sample

Check this code sample to see the possibilities of this library:

```php
<?php

use ride\library\dependency\Dependency;
use ride\library\dependency\DependencyCall;
use ride\library\dependency\DependencyCallArgument;
use ride\library\dependency\DependencyContainer;
use ride\library\dependency\DependencyInjector;

// Your dependencies are stored in a dependency container. For the sake of
// explaining this library, let's initialize it manually. This should be done
// through configuration to get real benefit of this library
$dependencyContainer = new DependencyContainer();

// most generic definition of a dependency is a class, however this definition 
// is obsulete since the dependency injector attempts to create undefined 
// dependencies as well
$dependency = new Dependency('some\Class');

// give your dependency an id to retrieve a specific instance of an interface
$dependency = new Dependency('some\Class', 'id1');

// some\Class implements some interfaces
$dependency->addInterface('some\Interface');
$dependency->addInterface('some\OtherInterface');

// now add it to the container
$dependencyContainer->addDependency($dependency);

// lets create another another, this time with a constructor and some action

// define the constructor call
$argument = new DependencyCallArgument('name', 'dependency', array(
    'interface' => 'some\Interface', 
    'id' => 'id1',
));
$call = new DependencyCall('__construct');
$call->addArgument($argument);

// define the dependency and add some calls
$dependency = new Dependency('another\Class', 'id2');
$dependency->addCall($call);
$dependency->addCall(new DependencyCall('doSomething'));
$dependency->addInterface('some\Interface');

// add it to the container
$dependencyContainer->addDependency($dependency);

// define a factory for a dependency
$constructCall = new DependencyConstructCall('some\Factory', 'methodOnFactory');

$dependency = new Dependency($constructCall, 'id');
$dependency->addCall(new DependencyCall('doSomething'));
$dependency->addInterface('some\Interface');

$dependencyContainer->addDependency($dependency);

// Your dependency container gets filled up with this kind of definitions.
// Once setup, you are ready to get your instances.

// First we need to create the dependency injector itself.
$dependencyInjector = new DependencyInjector($dependencyContainer);

// Let's get an instance, the thing you are most likely to do...
$instance = $dependencyInjector->get('some\Interface'); // another\Class since it's last defined
$instance = $dependencyInjector->get('some\Interface', 'id1'); // some\Class
try {
    $instance = $dependencyInjector->get('third\Class');
    // your instance if the third\Class can be created with the available dependencies
} catch (Exception $e) {
    // when the dependency is an interface,
    // or when it's a class and it's not constructable or when some required arguments could
    // not be injected.
}

// You can also invoke callbacks where not provided arguments are injected if possible
$callback = 'function';
$callback = array('some\Static', 'call');
$callback = array(new some\Class(), 'call');
$arguments = array('name' => $value); // arguments you know/want, the rest will be injected
$returnValue = $dependencyInjector->invoke($callback, $arguments);
```
