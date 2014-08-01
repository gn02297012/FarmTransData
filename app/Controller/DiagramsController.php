<?php

App::uses('AppController', 'Controller');

class DiagramsController extends AppController {

    public $uses = array('Query');

    public function index() {
        $vegetables = array_keys($this->Query->vegetables);
        $fruits = array_keys($this->Query->fruits);
        $markets = array_keys($this->Query->markets);
        $this->set(compact('vegetables', 'fruits', 'markets'));
    }

    public function line() {
        $vegetables = array_keys($this->Query->vegetables);
        $fruits = array_keys($this->Query->fruits);
        $markets = array_keys($this->Query->markets);
        $this->set(compact('vegetables', 'fruits', 'markets'));
    }

    public function dashboard() {
        $vegetables = array_keys($this->Query->vegetables);
        $fruits = array_keys($this->Query->fruits);
        $markets = $this->Query->markets;
        $this->set(compact('vegetables', 'fruits', 'markets'));
    }

}
