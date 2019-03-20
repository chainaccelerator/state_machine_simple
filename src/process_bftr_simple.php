<?php

/**
 * Trait Bftr_simple
 */
Trait Process_bftr_simple
{
    use Process_workflow_simple;
    use Chain_simple;
    use Socket_client_push_broadcast_simple;

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
     * @var int
     */
    public static $process_bftr_block_wait = 1000;

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
    public static $process_bftr_rule_commit_count_func = 'process_bftr_commit_count';
    /**
     * @var string
     */
    public static $process_bftr_rule_commit_wait_func = 'process_bftr_commit_wait';
    /**
     * @var string
     */
    public static $process_bftr_rule_block_get_func = 'process_bftr_block_get';
    /**
     * @var string
     */
    public static $process_bftr_rule_block_stage_func = 'process_bftr_block_stage';
    /**
     * @var string
     */
    public static $process_bftr_rule_block_broadcast_func = 'process_bftr_block_broadcast';
    /**
     * @var string
     */
    public static $process_bftr_rule_commit_time_set_func = 'process_bftr_commit_time_set';

    /**
     * @var string
     */
    public static $process_bftr_rule_block_wait_func = 'process_bftr_block_wait';

    /**
     * @var string
     */
    public static $process_bftr_build_commit_start = 'process_bftr_build_commit_start';
    /**
     * @var string
     */
    public static $process_bftr_build_height_new_ready = 'process_bftr_build_height_new_ready';
    /**
     * @var string
     */
    public static $process_bftr_build_propose = 'process_bftr_build_propose';
    /**
     * @var string
     */
    public static $process_bftr_build_prevote = 'process_bftr_build_prevote';
    /**
     * @var string
     */
    public static $process_bftr_build_precommit = 'process_bftr_build_precommit';
    /**
     * @var string
     */
    public static $process_bftr_build_commit_ready = 'process_bftr_build_commit_ready';
    /**
     * @var string
     */
    public static $process_bftr_build_commit_end = 'process_bftr_build_commit_end';
    /**
     * @var string
     */
    public static $process_bftr_build_loop = 'process_bftr_build_loop';


    /**
     * @var string
     */
    private $process_bftr_previous = '';

    /**
     * @var Block_simple_data
     */
    private $process_bar_block_data_received;
    /**
     * @var
     */
    public $process_bftr_height_new_push_address;

    /**
     * @var
     */
    public $process_bftr_build_commit_ready_loop_count = 0;
    /**
     * @var int
     */
    public $process_bftr_height_new_wait_loop_count = 0;
    /**
     * @var int
     */
    public $process_bftr_precommit_count = 0;
    /**
     * @var int
     */
    public $process_bftr_commit_count = 0;
    /**
     * @var int
     */
    public $process_bftr_loop_wait = 0;

    /**
     * @var int
     */
    public $process_bftr_commit_time = 0;
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
        $this->process_bftr_build_commit_ready();
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
    private function process_bftr_build_commit_ready() {

        $this->process_bftr_build_commit_ready_loop_count++;

        $rule_list_definition = array();
        $rule_list_definition[self::$process_bftr_rule_commit_wait_func] = new Process_rule_definition_simple();
        $rule_list_definition[self::$process_bftr_rule_commit_count_func] = new Process_rule_definition_simple();

        $transition_commit_ready = $this->process_workflow_build_transition(__FUNCTION__, $rule_list_definition);

        $this->process_workflow_transition_add($transition_commit_ready);

        $rule_commit_count = $transition_commit_ready->rule_get(self::$process_bftr_rule_commit_count_func);
        $rule_commit_count->transition_ko = $this->workflow_transition_list['process_bftr_build_commit_ready'];

        $transition_commit_ready->rule_set($rule_commit_count);

        return $this->process_bftr_follow(__FUNCTION__);
    }

    /**
     * @return int
     */
    private function process_bftr_build_commit_end() {

        $rule_list_definition = array();
        $rule_list_definition[self::$process_bftr_rule_block_get_func] = new Process_rule_definition_simple();
        $rule_list_definition[self::$process_bftr_rule_block_stage_func] = new Process_rule_definition_simple();
        $rule_list_definition[self::$process_bftr_rule_block_broadcast_func] = new Process_rule_definition_simple();
        $rule_list_definition[self::$process_bftr_rule_commit_time_set_func] = new Process_rule_definition_simple();

        $transition_commit_end = $this->process_workflow_build_transition(__FUNCTION__, $rule_list_definition);

        $this->process_workflow_transition_add($transition_commit_end);

        return $this->process_bftr_follow(__FUNCTION__);
    }

    /**
     * @return int
     */
    private function process_bftr_build_loop(){

        $rule_list_definition = array();
        $rule_list_definition[self::$process_bftr_rule_block_wait_func] = new Process_rule_definition_simple();

        $transition_loop = $this->process_workflow_build_transition(__FUNCTION__, $rule_list_definition);

        $this->process_workflow_transition_add($transition_loop);

        $rule_block_wait = $transition_loop->rule_get(self::$process_bftr_rule_block_wait_func);
        $rule_block_wait->transition_ok = $this->workflow_transition_list['process_bftr_build_commit_start'];

        $transition_loop->rule_set($rule_block_wait);

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
    private static function process_bftr_commit_ttl_get() {

        return self::$process_bftr_commit_ttl;
    }

    /**
     * @return int
     */
    private static function process_bftr_commit_wait_get() {

        return self::$process_bftr_commit_wait;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_height_new_wait(array $input_params = array()){

        $this->process_bftr_height_new_push_address = $this->socket_client_push_broadcast('/propose/block', $this->block_data);

        $ttl = self::process_bftr_commit_ttl_get();
        $wait = self::process_bftr_commit_wait_get();
        $this->process_bftr_height_new_wait_loop_count = $this->process_workflow_ttl_verif_and_wait_before_ok($ttl, $wait, $this->process_bftr_commit_time);

        return true;
    }

    /**
     * @return float|int
     */
    private static function process_bftr_precommit_found_min_get(){

        return self::$process_bftr_precommit_found_min;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_precommit_count(array $input_params = array()){

        $process_bftr_precommit_found_min = self::process_bftr_precommit_found_min_get();

        return $this->process_bftr_count_get('precommit', $this->process_bftr_height_new_push_address, $process_bftr_precommit_found_min);
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
    private static function process_bftr_commit_found_min_get(){

        return self::$process_bftr_commit_found_min;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_commit_count(array $input_params = array()){

        $process_bftr_commit_found_min = self::process_bftr_commit_found_min_get();

        return $this->process_bftr_count_get('commit', $this->process_bftr_height_new_push_address, $process_bftr_commit_found_min);
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_commit_wait(array $input_params = array()){

        $wait = $this->process_bftr_commit_wait_get();

        sleep($wait);

        return true;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_block_get(array $input_params = array()){

        $this->process_bar_block_data_received = $this->process_bftr_block_get_from_network('commit', $this->process_bftr_height_new_push_address);

        return true;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_block_stage(array $input_params = array()){

        return $this->chain_block_stage($this->process_bar_block_data_received);
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_block_broadcast(array $input_params = array()){

        $this->process_bftr_push('commit', $this->process_bar_block_data_received->cost);

        return true;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_commit_time_set(array $input_params = array()){

        $this->process_bftr_commit_time = microtime();

        return true;
    }

    /**
     * @return int
     */
    private static function process_bftr_block_wait_get(){

        return self::$process_bftr_block_wait;
    }

    /**
     * @param array $input_params
     * @return bool
     */
    public function process_bftr_block_wait(array $input_params = array()){

        $wait = self::process_bftr_block_wait_get();

        sleep($wait);

        return true;
    }

    /**
     * @param string $step_name
     * @param string $height_new_push_address
     * @param int $result_count_min
     * @return bool
     */
    private function process_bftr_count_get(string $step_name, string $height_new_push_address, int $result_count_min){

        $response = $this->socket_client_push_broadcast_request_count_get('/'.$step_name.'/block/push/'.$height_new_push_address.'/count');

        $count = $response->data->count;

        if($count < $result_count_min) {

            return false;
        }
        return true;
    }

    /**
     * @param string $step_name
     * @param string $height_new_push_address
     * @param int $result_count_min
     * @return bool
     */
    private function process_bftr_push(string $step_name, string $height_new_push_address){

        return $this->socket_client_push_broadcast_send('/'.$step_name.'/block/'.$height_new_push_address.'/send', $this->workflow_transition_list);
    }

    /**
     * @var string $step_name
     * @var string $height_new_push_address
     * @return Block_simple_data
     */
    public function process_bftr_block_get_from_network(string $step_name, string $height_new_push_address) {

        $response = $this->socket_client_push_broadcast_request_count_get('/'.$step_name.'/block/'.$height_new_push_address);

        $block_data = $response->data->block_data;

        return $block_data;
    }
}

