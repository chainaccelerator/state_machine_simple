<?php

/**
 * Class Process_rule_simple
 */
class Process_rule_simple {

    /**
     * @var array
     */
    public $input_params = array();
    /**
     * @var string
     */
    public $name;
    /**
     * @var mixed
     */
    public $value_ok;
    /**
     * @var Process_transition_simple
     */
    public $transition_ok;
    /**
     * @var mixed
     */
    public $value_ko;
    /**
     * @var Process_transition_simple
     */
    public $transition_ko;
    /**
     * @var Process_transition_simple
     */
    public $transition_fail;
    /**
     * @var bool
     */
    public $required_ok_state = true;
    /**
     * @var srting
     */
    public $workflow_name;

    /**
     * @param string $function
     * @return Process_transition_simple
     */
    public function build_transition(string $function)
    {
        $function = $this->name . $function;
        $state = new Process_state_simple();
        $state->$function($this->name, $this->workflow_name);

        $transition = new Process_transition_simple();
        $transition->build($this->name . '_' . $function, $this->workflow_name, true, false);
        $transition->state_end_list_add($state);

        return $transition;
    }

    /**
     * @return Process_transition_simple
     */
    public function build_transition_ok()
    {
        return $this->build_transition(Process_state_simple::$value_target);
    }

    /**
     * @return Process_transition_simple
     */
    public function build_transition_ko()
    {
        return $this->build_transition(Process_state_simple::$value_cancel);
    }

    /**
     * @return Process_transition_simple
     */
    public function build_transition_fail()
    {
        return $this->build_transition(Process_state_simple::$value_fail);
    }

    /**
     * @return bool
     */
    public function build_transitions()
    {
        $this->transition_ok = $this->build_transition_ok();
        $this->transition_ko = $this->build_transition_ko();
        $this->transition_fail = $this->build_transition_fail();

        return true;
    }

    /**
     * @param string $name
     * @param bool $value_ok
     * @param bool $value_ko
     * @param string $workflow_name
     * @param bool $required_ok_state
     * @param array $input_params
     * @return bool
     */
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

    /**
     * @param array $input_params
     * @return bool
     */
    public function input_params_set(array $input_params = array())
    {

        $this->input_params = $input_params;

        return true;
    }

    /**
     * @return mixed
     */
    public function test_call(){

        $func = $this->test_name;
        return  $this->$func($this->input_params);
    }

    /**
     * @return bool
     */
    public function test(){

        $value = $this->test_call();

        if($value === $this->value_ok) return $this->transition_ok->run();

        if($value === $this->value_ko) return $this->transition_ko->run();

        if($this->required_ok_state === true) return $this->transition_fail->run();

        return true;
    }
}

