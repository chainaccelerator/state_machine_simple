<?php

/**
 * Trait Process_workflow_simple
 */
trait Process_workflow_simple
{

    use Crypto_simple;

    /**
     * @var array
     */
    public $workflow_state_initial_list = array();

    /**
     * @var string
     */
    public $workflow_version;
    /**
     * @var string
     */
    public $workflow_name;
    /**
     * @var array
     */
    public $workflow_child = array();
    /**
     * @var array
     */
    public $workflow_transition_list = array();

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

        $state->build_initial($name, $this->workflow_name);

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
        $this->workflow_transition_list[$transition->name] = $transition;
        $transition->index = $index;

        return $index;
    }

    /**
     * @param int $ttl
     * @param int $wait
     * @param int|bool $timestamp
     * @return bool
     */
    public static function process_workflow_ttl_verif_and_wait_before_ok(int $ttl, int $wait, $timestamp = true)
    {
        $loop_count = 0;

        while (self::process_workflow_ttl_verif_before_ok($ttl, $timestamp) === false) {

            sleep($wait);
            $loop_count++;
        }
        return ($loop_count * $wait);
    }

    /**
     * @param int $ttl
     * @param int|bool $timestamp
     * @return bool
     */
    public static function process_workflow_ttl_verif_before_ok(int $ttl, $timestamp = true)
    {
        if($timestamp === true) {

            $timestamp = microtime();
        }
        if (time() - $timestamp > $ttl) {

            return true;
        }
        return false;
    }


    /**
     * @param string $function_name
     * @param array $param_list
     * @param bool $required_ok_state
     * @return Process_rule_simple
     */
    protected function process_workflow_build_rule(string $function_name, array $param_list = array(), bool $required_ok_state = true){

        $rule = new Process_rule_simple();
        $rule->build($function_name, true, false, $this->workflow_name, $required_ok_state, $param_list);

        return $rule;
    }

    /**
     * @param Process_transition_simple $transition
     * @param array $rule_list_definition
     * @return Process_transition_simple
     */
    protected function process_workflow_build_rules_add(Process_transition_simple $transition, array $rule_list_definition = array()) {

        foreach($rule_list_definition as $rule_name => $rule_definition) {

            $rule = $this->process_workflow_build_rule($rule_name, $rule_definition->param_list, $rule_definition->required_state_ok);
            $transition->rule_list_add($rule);
        }
        return $transition;
    }

    /**
     * @param string $transition_name
     * @param array $rule_list_definition
     * @return Process_transition_simple
     */
    protected function process_workflow_build_transition(string $transition_name, array $rule_list_definition = array()){

        $transition = new Process_transition_simple();
        $transition->build($transition_name, $this->workflow_name, false);

        $transition = $this->process_workflow_build_rules_add($transition, $rule_list_definition);

        return $transition;
    }

    /**
     * @param Log_simple_code $log_code
     * @param array $access_list
     * @return bool
     * @throws ReflectionException
     */
    public function process_workflow_store(Log_simple_code $log_code, array $access_list = array()){

        $this->sign_init();

        $class_name = get_class($this);
        $class = new ReflectionClass($class_name);
        $methods = $class->getMethods();

        $signature = $this->sign(implode('',$this->workflow_transition_list));
        $public_key = $this->sign_public_key_get();
        $log_code_interface = new Log_simple_code_Interface($this->process_bftr_height_new_push_address, $signature, $public_key);

        foreach($methods as $method_name) {

            $log_argument = new Log_simple_code_argument('input_params', 'array', array(), 1000000, false);
            $log_return = new Log_simple_code_return('result', 'bool', array(), 1000000000, false,
                                false, array(), array(), true);

            $log_function = new Log_simple_code_function($method_name, $class_name);
            $log_function->argument_add($log_argument);
            $log_function->return_add($log_return);

            $log_code_interface->function_add($log_function);
        }
        $log_code->interface_set($log_code_interface);
        $log_code->put($this->process_bftr_height_new_push_address, $access_list);

        return true;
    }

    /**
     * @param Log_simple_code_Interface_call $log_code_Interface_call
     * @param Log_simple_code $log_code
     * @param array $access_list
     * @return bool
     * @throws ReflectionException
     */
    public function process_workflow_call(Log_simple_code_Interface_call $log_code_Interface_call, Log_simple_code $log_code, string $public_key, array $access_list = array()){

        $log_code->log_storage_extract();

        $log_code->run($log_code_Interface_call);

        return true;
    }
}

