<?php

App::uses('AppModel', 'Model');

class Code extends AppModel {

    public $name = 'Code';
    public $useTable = 'code';
    public $primaryKey = 'code';
    public $hasMany = array(
        'Query' => array(
            'className' => 'Query',
        )
    );

}
