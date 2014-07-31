<?php

App::uses('AppController', 'Controller');

class DiagramsController extends AppController {

    public $uses = array('Query');

    public function index() {
        
    }

    public function line() {
        $vegetables = $this->Query->vegetables;
        $markets = $this->Query->markets;
        $this->set(compact('vegetables', 'markets'));
    }

    public function dashboard() {
        $vegetables = array_keys($this->Query->vegetables);
        $fruits = array_keys($this->Query->fruits);
        $markets = $this->Query->markets;
        $this->set(compact('vegetables', 'fruits', 'markets'));
    }

}
