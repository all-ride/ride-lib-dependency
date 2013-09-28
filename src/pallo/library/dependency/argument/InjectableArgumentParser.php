<?php

namespace pallo\library\dependency\argument;

use pallo\library\dependency\DependencyInjector;

/**
 * Parser for defined dependency values.
 */
interface InjectableArgumentParser extends ArgumentParser {

    /**
     * Sets the dependency injector to this parser
     * @param pallo\library\dependency\DependencyInjector $dependencyInjector
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