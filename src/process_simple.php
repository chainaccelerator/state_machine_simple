<?php

/**
 * Class Process_state_simple
 */
class Process_state_simple {

    use log_simple;

    /**
     * @var string
     */
    public static $value_initial = 'initial';
    /**
     * @var string
     */
    public static $value_confirm = 'confirm';
    /**
     * @var string
     */
    public static $value_target = 'target';
    /**
     * @var string
     */
    public static $value_fail = 'fail';
    /**
     * @var string
     */
    public static $suffix_ok = '_ok';
    /**
     * @var string
     */
    public static $suffix_fail = '_fail';
    /**
     * @var string
     */
    public static $suffix_cancel = '_cancel';
    /**
     * @var bool
     */
    private $log_state = true;
    /**
     * @var
     */
    public $name;
    /**
     * @var
     */
    private $value;
    /**
     * @var
     */
    private $workflow_name;
    /**
     * @var array
     */
    private $transition_list = array();

    /**
     * @param string $name
     * @param string $workflow_name
     * @param string $value
     * @param bool $log_state
     * @return bool
     */
    private function build(string $name, string $workflow_name, string $value, $log_state = true)
    {
        $this->log_state = $log_state;
        $this->name = $name;
        $this->value = $value;
        $this->workflow_name = $workflow_name;

        return true;
    }

    /**
     * @param string $name
     * @param string $workflow_name
     * @return bool
     */
    public function build_initial(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_ok, $workflow_name, self::$value_initial, true);
    }

    /**
     * @param string $name
     * @param string $workflow_name
     * @return bool
     */
    public function build_confirm(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_ok, $workflow_name, self::$value_confirm, true);
    }

    /**
     * @param string $name
     * @param string $workflow_name
     * @return bool
     */
    public function build_cancel(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_cancel, $workflow_name, self::$value_cancel, true);
    }

    /**
     * @param string $name
     * @param string $workflow_name
     * @return bool
     */
    public function build_target(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_ok, $workflow_name, self::$value_target, true);
    }

    /**
     * @param string $name
     * @param string $workflow_name
     * @return bool
     */
    public function build_fail(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_fail, $workflow_name, self::$value_fail, true);
    }

    /**
     * @param Process_transition_simple $transition
     */
    public function transition_add(Process_transition_simple $transition)
    {

        $this->transition_list[$transition->name] = $transition;
    }

    /**
     * @param string $data_ref
     * @return bool|mixed
     */
    public function confirm(string $data_ref = ''){

        $this->value = self::$value_confirm;

        if($this->log_state === true) {

            $this->log_init();

            return $this->log_build($this, $this->workflow_name, $this->name, $data_ref);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function target(){

        $this->value = self::$value_target;

        return true;
    }

    /**
     * @return bool
     */
    public function cancel(){

        $this->value = self::$value_cancel;

        return true;
    }
}

/**
 * Class Process_transition_simple
 */
class Process_transition_simple {

    /**
     * @var
     */
    public $name;
    /**
     * @var integer
     */
    public $index;
    /**
     * @var array
     */
    public $state_end_list = array();
    /**
     * @var array
     */
    private $rule_list = array();
    /**
     * @var bool
     */
    private $required_state = true;
    /**
     * @var bool
     */
    private $async_state = false;
    /**
     * @var
     */
    private $workflow_name;

    /**
     * @param string $name
     * @param string $workflow_name
     * @param bool $required_state
     * @param bool $async_state
     * @return bool
     */
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

    /**
     * @param Process_rule_simple $rule
     * @return bool
     */
    public function rule_list_add(Process_rule_simple $rule)
    {

        $this->rule_list[] = $rule;

        return true;
    }

    /**
     * @param Process_state_simple $state_end
     * @return bool
     */
    public function state_end_list_add(Process_state_simple $state_end)
    {
        $this->state_end_list[] = $state_end;

        return true;
    }

    /**
     * @return bool
     */
    public function run(){

        $this->state_start->confirm();
        $this->state_end->target();
        $condition_obj->run();

        return true;
    }

    /**
     * @param string $workflow_name
     * @param array $input_params
     * @return bool
     */
    public function set(string $workflow_name, array $input_params) {

        $this->workflow_name = $workflow_name;
        $this->input_params = $input_params;

        return true;
    }
}

/**
 * Class Process_rule_simple
 */
class Process_rule_simple {

    /**
     * @var array
     */
    private $input_params = array();
    /**
     * @var
     */
    public $name;
    /**
     * @var
     */
    private $value_ok;
    /**
     * @var
     */
    private $transition_ok;
    /**
     * @var
     */
    private $value_ko;
    /**
     * @var
     */
    private $transition_ko;
    /**
     * @var
     */
    private $transition_fail;
    /**
     * @var bool
     */
    private $required_ok_state = true;
    /**
     * @var
     */
    private $workflow_name;

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

/**
 * Trait Process_workflow_simple
 */
trait Process_workflow_simple
{

    /**
     * @var array
     */
    public $workflow_state_initial_list = array();

    /**
     * @var string
     */
    private $workflow_version;
    /**
     * @var string
     */
    private $workflow_name;
    /**
     * @var array
     */
    private $workflow_child = array();
    /**
     * @var array
     */
    private $workflow_transition_list = array();

    /**
     * @param string $state_initial_name
     * @param Process_transition_simple $transition
     * @return bool
     */
    public function process_workflow_init(string $state_initial_name,
                                          Process_transition_simple $transition)
    {
        $this->workflow_name = get_class($this);
        $this->process_workflow_state_initial_build_and_add($state_initial_name);
        $this->process_workflow_transition_add($transition);

        return true;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function process_workflow_state_initial_build_and_add(string $name = 'initial') {

        $state = $this->process_workflow_state_initial_build($name);

        return $this->process_workflow_state_initial_add($state);
    }

    /**
     * @param string $name
     * @return Process_state_simple
     */
    public function process_workflow_state_initial_build(string $name = 'initial')
    {
        $state = new Process_state_simple();

        $state->build_initial($name);

        return $state;
    }

    /**
     * @param Process_state_simple $state_initial
     * @return bool
     */
    public function process_workflow_state_initial_add(Process_state_simple $state_initial)
    {
        $this->workflow_state_initial_list[$state_initial->name] = $state_initial;

        return true;
    }

    public function process_workflow_transition_add(Process_transition_simple $transition) {

        $index = count($this->workflow_transition_list);
        $this->workflow_transition_list[] = $transition;
        $transition->index = $index;

        return $index;
    }

    /**
     * @param int $timestamp
     * @param int $ttl
     * @param int $wait
     * @return bool
     */
    public static function process_workflow_ttl_verif_and_wait(int $timestamp, int $ttl, int $wait)
    {

        while (self::process_workflow_ttl_verif($timestamp, $ttl) === true) {

            return false;
        }
        sleep($wait);

        return true;
    }

    /**
     * @param int $timestamp
     * @param int $ttl
     * @return bool
     */
    public static function process_workflow_ttl_verif(int $timestamp, int $ttl)
    {
        if (time() - $timestamp > $ttl) {

            return false;
        }
        return true;
    }
}
