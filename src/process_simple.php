<?php

class Process_state_simple {

    use log_simple;

    public static $value_initial = 'initial';
    public static $value_confirm = 'confirm';
    public static $value_target = 'target';
    public static $value_fail = 'fail';
    public static $suffix_ok = '_ok';
    public static $suffix_fail = '_fail';
    public static $suffix_cancel = '_cancel';
    private $log_state = true;
    public $name;
    private $value;
    private $workflow_name;
    private $transition_list = array();

    private function build(string $name, string $workflow_name, string $value, $log_state = true)
    {
        $this->log_state = $log_state;
        $this->name = $name;
        $this->value = $value;
        $this->workflow_name = $workflow_name;

        return true;
    }

    public function build_initial(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_ok, $workflow_name, self::$value_initial, true);
    }

    public function build_confirm(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_ok, $workflow_name, self::$value_confirm, true);
    }

    public function build_cancel(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_cancel, $workflow_name, self::$value_cancel, true);
    }

    public function build_target(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_ok, $workflow_name, self::$value_target, true);
    }

    public function build_fail(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_fail, $workflow_name, self::$value_fail, true);
    }

    public function transition_add(Process_transition_simple $transition)
    {

        $this->transition_list[$transition->name] = $transition;
    }

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

    private $state_end_list = array();
    private $rule_list = array();
    public $name;
    private $required_state = true;
    private $async_state = false;
    private $workflow_name;

    public function build(string $name,
                          string $workflow_name,
                          bool $required_state = true,
                          bool $async_state = false
    )
    {

        $this->name = $name;
        $this->required_state = $required_state;
        $this->async_state = $async_state;
        $this->workflow_name = $workflow_name;

        return true;
    }

    public function rule_list_add(Process_rule_simple $rule)
    {

        $this->rule_list[] = $rule;

        return true;
    }

    public function state_end_list_add(Process_state_simple $state_end)
    {

        $this->state_end_list[] = $state_end;

        return true;
    }

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
    public $name;
    private $value_ok;
    private $transition_ok;
    private $value_ko;
    private $transition_ko;
    private $transition_fail;
    private $required_ok_state = true;
    private $workflow_name;

    public function build_transition(string $function)
    {
        $function = 'build_' . $function;
        $state = new Process_state_simple();
        $state->$function($this->name, $this->workflow_name);

        $transition = new Process_transition_simple();
        $transition->build($this->name . '_' . $function, $this->workflow_name, true, false);
        $transition->state_end_list_add($state);

        return $transition;
    }

    public function build_transition_ok()
    {
        return $this->build_transition(Process_state_simple::$value_target);
    }

    public function build_transition_ko()
    {
        return $this->build_transition(Process_state_simple::$value_cancel);
    }

    public function build_transition_fail()
    {
        return $this->build_transition(Process_state_simple::$value_fail);
    }

    public function build_transitions()
    {
        $this->transition_ok = $this->build_transition_ok();
        $this->transition_ko = $this->build_transition_ko();
        $this->transition_fail = $this->build_transition_fail();

        return true;
    }

    public function build(
        string $name,
        bool $value_ok,
        bool $value_ko,
        string $workflow_name,
        bool $required_ok_state = true,
        array $input_params = array())
    {

        $this->input_params = $input_params;
        $this->name = $name;

        $this->build_transitions();

        $this->value_ok = $value_ok;
        $this->value_ko = $value_ko;
        $this->required_ok_state = $required_ok_state;
        $this->workflow_name = $workflow_name;

        return true;
    }

    public function input_params_set(array $input_params = array())
    {

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

trait process_workflow_simple {

    private $workflow_state_initial_list = array();

    private $workflow_version;
    private $workflow_name;
    private $workflow_child = array();

    public function process_workflow_init()
    {

        $this->workflow_name = get_class($this);

        return true;
    }

    public function process_workflow_rule_build(string $name)
    {

        $rule = new Process_rule_simple();
        $rule->build($name, true, false, true, $this->workflow_name, true, array());

        return $rule;
    }

    public function process_workflow_state_target_build(string $name)
    {
        $state = new Process_state_simple();
        $state->build_target($name);

        return $state;
    }

    public function process_workflow_state_initial_build()
    {
        $state = new Process_state_simple();
        $state->build_initial('initial');

        return $state;
    }

    public function process_workflow_transition_build(string $name)
    {

        $transition = new Process_transition_simple();
        $transition->build($name, $this->workflow_name, true, false);

        return $transition;
    }

    public function process_workflow_state_initial_add(Process_state_simple $state_inital)
    {

        $this->workflow_state_initial_list[$state_inital->name] = $state_inital;

        return true;
    }


}
