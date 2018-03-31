<?php

namespace ride\library\dependency\argument;

use ride\library\dependency\DependencyCallArgument;

use PHPUnit\Framework\TestCase;

class DependenciesArgumentParserTest extends TestCase {

    /**
     * @expectedException ride\library\dependency\exception\DependencyException
     */
    public function testGetValueShouldThrowDependencyException() {
        $parser = new DependenciesArgumentParser();

        $data = array('var1' => 'value', 'var2' => 'value');
        $argument = new DependencyCallArgument('name', 'array', $data);

        $result = $parser->getValue($argument);

        $this->assertEquals($data, $result);
    }

}