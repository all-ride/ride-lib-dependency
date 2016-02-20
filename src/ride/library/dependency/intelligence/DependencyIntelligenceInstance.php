<?php

namespace ride\library\dependency\intelligence;

class DependencyIntelligenceInstance {

    public function __construct($fingerprint) {
        $this->fingerprint = $fingerprint;

        $this->className = null;
        $this->factoryInterface = null;
        $this->factoryId = null;
        $this->methods = array();
        $this->method = null;
        $this->arguments = array();
        $this->interfaces = array();
        $this->id = null;
        $this->isAnonymous = false;
    }

    public function getFingerprint() {
        return $this->fingerprint;
    }

    public function setInterfaces(array $interfaces) {
        $this->interfaces = $interfaces;
    }

    public function getInterfaces() {
        return $this->interfaces;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setIsAnonymous($isAnonymous) {
        $this->isAnonymous = $isAnonymous;
    }

    public function isAnonymous() {
        return $this->isAnonymous;
    }

    public function setClassName($className) {
        $this->className = $className;
    }

    public function getClassName() {
        return $this->className;
    }

    public function getMethods() {
        $this->finishMethod();

        return $this->methods;
    }

    public function setFactory($interface, $id) {
        $this->factoryInterface = $interface;
        $this->factoryId = $id;
    }

    public function getFactoryInterface() {
        return $this->factoryInterface;
    }

    public function getFactoryId() {
        return $this->factoryId;
    }

    public function getFactoryMethod() {
        $this->finishMethod();

        return reset($this->methods);
    }

    public function addMethod($method) {
        $this->finishMethod();

        $this->method = $method;
    }

    public function addArgument($name, $value) {
        $this->arguments[$name] = $value;
    }

    private function finishMethod() {
        if ($this->method === null) {
            return;
        }

        $this->methods[] = array(
            'method' => $this->method,
            'arguments' => $this->arguments,
        );

        $this->method = null;
        $this->arguments = array();
    }

}
