<?php

class Process_state_simple {

    use log_simple;

    private static $value_initial = 'initial';
    private static $value_confirm = 'confirm';
    private static $value_target = 'target';
    private static $value_route = 'route';
    private static $value_cancel = 'cancel';
    private $log_state = true;
    private $name;
    private $value;
    private $workflow_name;
    private $transition_list = array();

    public function confirm(string $data_ref = ''){

        $this->value = self::$value_confirm;

        if($this->log_state === true) {

            $this->log_init();

            return $this->log_build($this, $this->workflow_name, $this->name, $data_ref);
        }
        return true;
    }

    public function target(){

        $this->value = self::$value_target;

        return true;
    }

    public function cancel(){

        $this->value = self::$value_cancel;

        return true;
    }
}

class Process_transition_simple {

    private $state_start;
    private $state_end;
    private $condition;
    private $name;
    private $required_state = true;
    private $acync_state = false;
    private $input_params = array();
    private $workflow_name;

    public function run(){

        $this->state_start->confirm();
        $this->state_end->target();
        $condition_obj->run();

        return true;
    }

    public function set(string $workflow_name, array $input_params) {

        $this->workflow_name = $workflow_name;
        $this->input_params = $input_params;

        return true;
    }
}

class Process_rule_simple {

    private $input_params = array();
    private $name;
    private $value_ok;
    private $transition_ok;
    private $value_ko;
    private $transition_ko;
    private $transition_fail;
    private $required_ok_state = true;
    private $workflow_name;

    public function set(string $workflow_name, array $input_params) {

        $this->workflow_name = $workflow_name;
        $this->input_params = $input_params;

        return true;
    }

    public function test_call(){

        $func = $this->test_name;
        return  $this->$func($this->input_params);
    }

    public function test(){

        $value = $this->test_call();

        if($value === $this->value_ok) return $this->transition_ok->run();

        if($value === $this->value_ko) return $this->transition_ko->run();

        if($this->required_ok_state === true) return $this->transition_fail->run();

        return true;
    }
}

class Process_condition_simple {

    private $input_params = array();
    private $name;
    private $rule_list = array();
    private $workflow_name;

    public function set(string $workflow_name, array $input_params) {

        $this->workflow_name = $workflow_name;
        $this->input_params = $input_params;

        return true;
    }

    public function run()
    {
        foreach($this->rules_list as $rule) {

            $rule->test();
        }
        return true;
    }
}

trait process_workflow_simple {

    private static $workflow_state_initial_list = array();

    private $workflow_name;
}
