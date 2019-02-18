<?php

class Process_state_simple {

    use log_simple;

    private static $value_confirm = 'confirm';
    private static $value_target = 'target';
    private static $value_cancel = 'cancel';
    private $initial_state = false;
    private $type_success = true;
    private $name;
    private $index;
    private $value;
    private $log_state;
    private $workflow_name;

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
    private $contition_list = array();
    private $name;
    private $index;
    private $thread_state = false;
    private $input_params = array();
    private $workflow_name;

    public function run(){

        $this->state_start->confirm();
        $this->state_end->target();

        foreach($this->contition_list as $condition_obj) {

            $condition_obj->run();
        }
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
    private $index;
    private $value_ok;
    private $transition_ok;
    private $value_ko;
    private $transition_ko;
    private $transition_fail;
    private $test_name;
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
    private $index;
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

    private static $workflow_state_list = array();
    private static $workflow_transition_list = array();

    private $workflow_name;
}
