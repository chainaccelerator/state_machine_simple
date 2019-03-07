<?php

/**
 * Trait Bftr_simple
 */
Trait Process_bftr_simple
{
    use Process_workflow_simple;
    use Chain_simple;
    use Socket_client_push_simple;

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
     * @var
     */
    public $process_bftr_height_new_push_address;
    /**
     * @var int
     */
    public $process_bftr_height_new_wait_total = 0;
    /**
     * @var int
     */
    public $process_bftr_precommit_count = 0;

    /**
     * @var string
     */
    private $process_bftr_previous = '';
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
     * @param string $transition_name
     * @return bool
     */
    private function process_bftr_follow(string $transition_name){

        $this->workflow_transition_list[$this->process_bftr_previous]->transition_ok = $this->workflow_transition_list[$transition_name];

        $this->process_bftr_previous = $transition_name;

        return true;
    }

    /**
     * @return bool
     */
    private function process_bftr_build_commit_start(){

        $rule_list_definition = array();
        $rule_list_definition[self::$process_bftr_rule_height_new_prepare_func] = new Process_rule_definition_simple();
        $rule_list_definition[self::$process_bftr_rule_broadcast_new_state_to_peers_func] = new Process_rule_definition_simple();

        $transition_commit_start = $this->process_workflow_build_transition(__FUNCTION__, $rule_list_definition);

        $this->process_workflow_init(__FUNCTION__, $transition_commit_start);

        return $this->process_bftr_follow(__FUNCTION__);
    }

    /**
     * @return int
     */
    private function process_bftr_build_height_new_ready(){

        $rule_list_definition = array();
        $rule_list_definition[self::$process_bftr_rule_height_new_wait_func] = new Process_rule_definition_simple();

        $transition_height_new_ready = $this->process_workflow_build_transition(__FUNCTION__, $rule_list_definition);

        $this->process_workflow_transition_add($transition_height_new_ready);

        return $this->process_bftr_follow(__FUNCTION__);
    }

    /**
     * @return int
     */
    private function process_bftr_build_propose(){

        $transition_propose = $this->process_workflow_build_transition(__FUNCTION__);

        $this->process_workflow_transition_add($transition_propose);

        return $this->process_bftr_follow(__FUNCTION__);
    }

    /**
     * @return int
     */
    private function process_bftr_build_prevote(){

        $transition_prevote = $this->process_workflow_build_transition(__FUNCTION__);

        $this->process_workflow_transition_add($transition_prevote);

        return $this->process_bftr_follow(__FUNCTION__);
    }

    /**
     * @return int
     */
    private function process_bftr_build_precommit(){

        $rule_list_definition = array();
        $rule_list_definition[self::$process_bftr_rule_precommit_count_func] = new Process_rule_definition_simple();

        $transition_precommit = $this->process_workflow_build_transition(__FUNCTION__, $rule_list_definition);
        $rule_precommit_count = $transition_precommit->rule_get(self::$process_bftr_rule_precommit_count_func);

        $rule_precommit_count->transition_ko = $this->workflow_transition_list['process_bftr_build_propose'];

        $transition_precommit->rule_set($rule_precommit_count);

        $this->process_workflow_transition_add($transition_precommit);

        return $this->process_bftr_follow(__FUNCTION__);
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

        $this->process_workflow_transition_add($transition_commit_end);

        return $this->process_bftr_follow(__FUNCTION__);
    }

    /**
     * @return int
     */
    private function process_bftr_build_loop(){

        $transition_loop = $this->process_workflow_build_transition(__FUNCTION__);

        $this->process_workflow_transition_add($transition_loop);

        return $this->process_bftr_follow(__FUNCTION__);
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_height_new_prepare(array $input_params = array()){

        if(empty(self::$chain_sign) === true) {

            $this->chain_init();
        }
        $height = $this->chain_block_next();

        return $height;
    }

    /**
     * @return float|int
     */
    public function process_bftr_commit_ttl_get() {

        return self::$process_bftr_commit_ttl;
    }

    /**
     * @return int
     */
    public function process_bftr_commit_wait_get() {

        return self::$process_bftr_commit_wait;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_height_new_wait(array $input_params = array()){

        $msg = json_encode($this->block_data);

        $this->process_bftr_height_new_push_address = $this->socket_client_push_broadcast($msg, $this->workflow_transition_list);

        $ttl = $this->process_bftr_commit_ttl_get();
        $wait = $this->process_bftr_commit_wait_get();
        $this->process_bftr_height_new_wait_total = $this->process_workflow_ttl_verif_and_wait_before_ok($ttl, $wait, true);

        return true;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_precommit_count(array $input_params = array()){

        $response = $this->socket_client_push_broadcast_request_count($this->process_bftr_height_new_push_address);

        $this->process_bftr_precommit_count = $response->data->bftr_precommit_count;

        return true;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_precommit(array $input_params = array()){

        return true;
    }

    /**
     * @return float|int
     */
    public function process_bftr_precommit_found_min_get(){

        return self::$process_bftr_precommit_found_min;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_commit_wait(array $input_params = array()){

        $process_bftr_precommit_found_min = $this->process_bftr_precommit_found_min_get();

        if($this->process_bftr_precommit_count < $process_bftr_precommit_found_min) {

            return false;
        }
        return true;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_block_get(array $input_params = array()){

        

        return true;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_block_stage(array $input_params = array()){

        return true;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_block_broadcast(array $input_params = array()){

        return true;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_commit_time_set(array $input_params = array()){

        return true;
    }
}

