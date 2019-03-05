<?php

/**
* Class Process_rule_definition_simple
 */
class Process_rule_definition_simple {

    /**
     * @var array
     */
    public $param_list = array();
    /**
     * @var bool
     */
    public $required_ok_state = true;

    public function __construct(array $param_list = array(), bool $required_ok_state = true){

        $this->param_list = $param_list;
        $this->required_ok_state = $required_ok_state;
    }

}
