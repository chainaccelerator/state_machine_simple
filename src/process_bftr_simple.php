<?php

/**
 * Trait Bftr_simple
 */
Trait Process_bftr_simple
{
    use Process_workflow_simple;
    use Chain_simple;

    /**
     * @var float|int
     */
    private static $process_bftr_precommit_found_min = 2 / 3;
    /**
     * @var float|int
     */
    private static $process_bftr_precommit_ttl = 2 / 600000;
    /**
     * @var int
     */
    private static $process_bftr_precommit_wait = 1000;

    /**
     * @var float|int
     */
    private static $process_bftr_commit_found_min = 2 / 3;
    /**
     * @var float|int
     */
    private static $process_bftr_commit_ttl = 2 / 600000;
    /**
     * @var int
     */
    private static $process_bftr_commit_wait = 1000;

    /**
     * @var string
     */
    public static $process_bftr_rule_height_new_prepare_func = 'process_bftr_height_new_prepare';
    /**
     * @var string
     */
    public static $process_bftr_rule_broadcast_new_state_to_peers_func = 'process_bftr_broadcast_new_state_to_peers';
    /**
     * @var string
     */
    public static $process_bftr_rule_height_new_wait_func = 'process_bftr_height_new_wait';
    /**
     * @var string
     */
    public static $process_bftr_rule_precommit_count_func = 'process_bftr_precommit_count';
    /**
     * @var string
     */
    public static $process_bftr_rule_precommit_func = 'process_bftr_precommit';
    /**
     * @var string
     */
    public static $process_bftr_rule_bftr_commit_wait_func = 'process_bftr_commit_wait';
    /**
     * @var string
     */
    public static $process_bftr_rule_bftr_block_get_func = 'process_bftr_block_get';
    /**
     * @var string
     */
    public static $process_bftr_rule_bftr_block_stage_func = 'process_bftr_block_stage';
    /**
     * @var string
     */
    public static $process_bftr_rule_bftr_block_broadcast_func = 'process_bftr_block_broadcast';
    /**
     * @var string
     */
    public static $process_bftr_rule_bftr_commit_time_set_func = 'process_bftr_commit_time_set';

    /**
     * @var Block_simple
     */
    private $process_bftr_commit;
    /**
     * @var int
     */
    private $process_bftr_commit_timestamp;

    /**
     * @param string $version
     * @return bool
     */
    public function process_bftr_build(string $version)
    {
        $this->workflow_version = $version;

        $this->process_bftr_build_commit_start();
        $this->process_bftr_build_height_new_ready();
        $this->process_bftr_build_propose();
        $this->process_bftr_build_prevote();
        $this->process_bftr_build_precommit();
        $this->process_bftr_build_commit_end();
        $this->process_bftr_build_loop();

        return json_encode($this);
    }

    /**
     * @return bool
     */
    private function process_bftr_build_commit_start(){

        $rule_list_definition = array();
        $rule_list_definition[self::$process_bftr_rule_height_new_prepare_func] = new Process_rule_definition_simple();
        $rule_list_definition[self::$process_bftr_rule_broadcast_new_state_to_peers_func] = new Process_rule_definition_simple();

        $transition_commit_start = $this->process_workflow_build_transition(__FUNCTION__, $rule_list_definition);

        return $this->process_workflow_init(__FUNCTION__, $transition_commit_start);
    }

    /**
     * @return int
     */
    private function process_bftr_build_height_new_ready(){

        $rule_list_definition = array();
        $rule_list_definition[self::$process_bftr_rule_height_new_wait_func] = new Process_rule_definition_simple();

        $transition_height_new_ready = $this->process_workflow_build_transition(__FUNCTION__, $rule_list_definition);

        return $this->process_workflow_transition_add($transition_height_new_ready);
    }

    /**
     * @return int
     */
    private function process_bftr_build_propose(){

        $transition_propose = $this->process_workflow_build_transition(__FUNCTION__);

        return $this->process_workflow_transition_add($transition_propose);
    }

    /**
     * @return int
     */
    private function process_bftr_build_prevote(){

        $transition_prevote = $this->process_workflow_build_transition(__FUNCTION__);

        return $this->process_workflow_transition_add($transition_prevote);
    }

    /**
     * @return int
     */
    private function process_bftr_build_precommit(){

        $rule_list_definition = array();
        $rule_list_definition[self::$process_bftr_rule_precommit_count_func] = new Process_rule_definition_simple();

        $transition_precommit = $this->process_workflow_build_transition(__FUNCTION__, $rule_list_definition);

        return $this->process_workflow_transition_add($transition_precommit);
    }

    /**
     * @return int
     */
    private function process_bftr_build_commit_end() {

        $rule_list_definition = array();
        $rule_list_definition[self::$process_bftr_rule_bftr_commit_wait_func] = new Process_rule_definition_simple();
        $rule_list_definition[self::$process_bftr_rule_bftr_block_get_func] = new Process_rule_definition_simple();
        $rule_list_definition[self::$process_bftr_rule_bftr_block_stage_func] = new Process_rule_definition_simple();
        $rule_list_definition[self::$process_bftr_rule_bftr_block_broadcast_func] = new Process_rule_definition_simple();
        $rule_list_definition[self::$process_bftr_rule_bftr_commit_time_set_func] = new Process_rule_definition_simple();

        $transition_commit_end = $this->process_workflow_build_transition(__FUNCTION__, $rule_list_definition);

        return $this->process_workflow_transition_add($transition_commit_end);
    }

    /**
     * @return int
     */
    private function process_bftr_build_loop(){

        $transition_loop = $this->process_workflow_build_transition(__FUNCTION__);

        return $this->process_workflow_transition_add($transition_loop);
    }

    public function process_bftr_height_new_prepare(array $input_params = array()){

        return true;
    }
    public function process_bftr_height_new_wait(array $input_params = array()){

        return true;
    }
    public function process_bftr_precommit_count(array $input_params = array()){

        return true;
    }
    public function process_bftr_precommit(array $input_params = array()){

        return true;
    }
    public function process_bftr_commit_wait(array $input_params = array()){

        return true;
    }
    public function process_bftr_block_get(array $input_params = array()){

        return true;
    }
    public function process_bftr_block_stage(array $input_params = array()){

        return true;
    }
    public function process_bftr_block_broadcast(array $input_params = array()){

        return true;
    }
    public function process_bftr_commit_time_set(array $input_params = array()){

        return true;
    }
}

