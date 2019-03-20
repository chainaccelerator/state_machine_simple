<?php

/**
 * Class Process_transition_simple
 */
class Process_transition_simple {

    use Socket_client_push_simple;

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
    public $rule_list = array();
    /**
     * @var bool
     */
    public $required_state = true;
    /**
     * @var bool
     */
    public $async_state = false;
    /**
     * @var
     */
    public $workflow_name;
    /**
     * @var Process_state_simple
     */
    public $state_start;
    /**
     * @var Process_state_simple
     */
    public $state_end;

    /**
     * @var
     */
    public $input_params = array();

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
     * @param string $rule_name
     * @return Process_rule_simple
     */
    public function rule_get(string $rule_name) {

        return $this->rule_list[$rule_name];
    }

    /**
     * @param Process_rule_simple $rule
     * @return Process_rule_simple
     */
    public function rule_set(Process_rule_simple $rule) {

        return $this->rule_list[$rule->name] = $rule;
    }

    /**
     * @param Process_state_simple $state_end
     * @return Process_state_simple
     */
    public function state_end_list_add(Process_state_simple $state_end)
    {
        $this->state_end_list[] = $state_end;

        return $state_end;
    }

    /**
     * @var Process_transition_simple $condition_obj
     * @return bool
     */
    public function run(Process_transition_simple $condition_obj){

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
