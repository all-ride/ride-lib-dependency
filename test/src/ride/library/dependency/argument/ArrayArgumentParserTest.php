<?php

namespace ride\library\dependency\argument;

use ride\library\dependency\DependencyCallArgument;

use PHPUnit\Framework\TestCase;

class ArrayArgumentParserTest extends TestCase {

    public function testGetValue() {
        $parser = new ArrayArgumentParser();

        $data = array('var1' => 'value', 'var2' => 'value');
        $argument = new DependencyCallArgument('name', 'array', $data);

        $result = $parser->getValue($argument);

        $this->assertEquals($data, $result);
    }

}