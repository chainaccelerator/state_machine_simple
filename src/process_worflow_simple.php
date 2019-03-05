<?php

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
    protected $workflow_version;
    /**
     * @var string
     */
    protected $workflow_name;
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
}

