<?php

App::uses('AppController', 'Controller');

class DiagramsController extends AppController {

    public $uses = array('Query');

    public function index() {
        
    }

    public function line() {
        $vegetables = $this->Query->vegetables;
        $this->set(compact('vegetables'));
    }

}
