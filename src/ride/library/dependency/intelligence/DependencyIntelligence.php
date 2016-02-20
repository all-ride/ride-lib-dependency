<?php

namespace ride\library\dependency\intelligence;

use ride\library\dependency\DependencyInjector;

class DependencyIntelligence {

    private $file;

    private $factory;

    private $instances;

    public function __construct($file) {
        $this->file = $file;
        $this->factory = $this->readFactory();
    }

    public function __destruct() {
        if ($this->instances) {
            $this->writeFactory();
        }
    }

    private function readFactory() {
        if (file_exists($this->file)) {
            // the generated script exists, include it
            include $this->file;
        }

        if (!isset($factory)) {
            return null;
        }

        // the script defined a factory, return it
        return $factory;
    }

    private function writeFactory() {
        file_put_contents($this->file, $this->generateFactory($this->factory));
    }

    private function generateFactory($factory = null) {
        $codeGenerator = new DependencyIntelligenceCodeGenerator();

        $className = 'DependencyIntelligenceFactory';
        $fingerprints = array();
        $methods = array();

        if ($factory) {
            $codeGenerator->setMeta($factory->getMeta());
        }

        foreach ($this->instances as $instance) {
            $codeGenerator->addInstance($instance);
        }

        return $codeGenerator->generateFactory($this);
    }

    public function getFingerprint($interface, $id = null, array $exclude = null) {
        $fingerprint = $interface . '__' . $id;

        // if ($exclude) {
            // $fingerprint .= '_' . implode('__', array_keys($exclude));
        // }

        return str_replace(array('\\', '.'), '_', $fingerprint);
    }

    public function getIntelligenceInstance($fingerprint) {
        if (isset($this->instances[$fingerprint])) {
            return $this->instances[$fingerprint];
        }

        return $this->instances[$fingerprint] = new DependencyIntelligenceInstance($fingerprint);
    }

    public function removeIntelligenceInstance($fingerprint) {
        if (isset($this->instances[$fingerprint])) {
            unset($this->instances[$fingerprint]);
        }
    }

    public function getInstanceId($fingerprint) {
        if (!$this->factory) {
            return false;
        }

        return $this->factory->getInstanceId($fingerprint);
    }

    public function getInstanceMeta($instanceId) {
        return $this->factory->getMeta($instanceId);
    }

    public function createInstance($instanceId, DependencyInjector $dependencyInjector) {
        return $this->factory->$instanceId($dependencyInjector);
    }

}
