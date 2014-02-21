<?php

namespace ride\library\dependency\argument;

use ride\library\dependency\DependencyInjector;

/**
 * Parser for defined dependency values.
 */
interface InjectableArgumentParser extends ArgumentParser {

    /**
     * Sets the dependency injector to this parser
     * @param ride\library\dependency\DependencyInjector $dependencyInjector
     * @return null
     */
    public function setDependencyInjector(DependencyInjector $dependencyInjector);

    /**
     * Sets the exclude array of the dependency injector
     * @param array $exclude
     * @return null
     */
    public function setExclude(array $exclude = null);

}