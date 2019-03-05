<?php

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
