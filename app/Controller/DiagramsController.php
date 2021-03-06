<?php

App::uses('AppController', 'Controller');

class DiagramsController extends AppController {

    public $uses = array('Query');
    
    public function beforeFilter() {
        parent::beforeFilter();
        if ($this->request->query('ajax') != null) {
            $this->layout = 'ajax';
        }
    }
    
    public function index() {
        
    }    
    
    public function search() {
        $vegetables = array_keys($this->Query->vegetables);
        $fruits = array_keys($this->Query->fruits);
        $markets = array_keys($this->Query->markets);
        $this->set(compact('vegetables', 'fruits', 'markets'));
    }
    
    public function partition() {
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
        $markets = array_keys($this->Query->markets);
        $markets_raw = $this->Query->markets;
        $this->set(compact('vegetables', 'fruits', 'markets', 'markets_raw'));
    }

    public function bubble() {
        $vegetables = array_keys($this->Query->vegetables);
        $fruits = array_keys($this->Query->fruits);
        $markets = array_keys($this->Query->markets);
        $this->set(compact('vegetables', 'fruits', 'markets'));
    }
    
    public function rank() {
        $vegetables = array_keys($this->Query->vegetables);
        $fruits = array_keys($this->Query->fruits);
        $markets = array_keys($this->Query->markets);
        $this->set(compact('vegetables', 'fruits', 'markets'));
    }
}
