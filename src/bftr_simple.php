<?php

/**
 * Class Bftr_rule_definition
 */
class Bftr_rule_definition{

    /**
     * @var array
     */
    public $param_list = array();
    /**
     * @var bool
     */
    public $required_ok_state = true;

}

/**
 * Class Bftr_commit
 */
class Bftr_commit {

    use Block_simple;
}

/**
 * Trait Bftr_simple
 */
Trait Bftr_simple
{
    use Process_workflow_simple;
    use Chain_simple;

    /**
     * @var float|int
     */
    private static $bftr_precommit_found_min = 2 / 3;
    /**
     * @var float|int
     */
    private static $bftr_precommit_ttl = 2 / 600000;
    /**
     * @var int
     */
    private static $bftr_precommit_wait = 1000;

    /**
     * @var float|int
     */
    private static $bftr_commit_found_min = 2 / 3;
    /**
     * @var float|int
     */
    private static $bftr_commit_ttl = 2 / 600000;
    /**
     * @var int
     */
    private static $bftr_commit_wait = 1000;
    /**
     * @var string
     */
    private static $bftr_commit_target_state = 'commited';
    /**
     * @var string
     */
    private static $bftr_commit_OK_state = 'init_OK';
    /**
     * @var string
     */
    private static $bftr_commit_KO_state = 'init_KO';
    /**
     * @var string
     */
    private static $bftr_commit_fail_state = 'init_fail';

    /**
     * @var string
     */
    private static $bftr_rule_height_new_prepare_func = 'bftr_height_new_prepare';
    /**
     * @var string
     */
    private static $bftr_rule_broadcast_new_state_to_peers_func = 'bftr_broadcast_new_state_to_peers';
    /**
     * @var string
     */
    private static $bftr_rule_height_new_wait_func = 'bftr_height_new_wait';
    /**
     * @var string
     */
    private static $bftr_rule_precommit_count_func = 'bftr_precommit_count';
    /**
     * @var string
     */
    private static $bftr_rule_precommit_func = 'bftr_precommit';
    /**
     * @var string
     */
    private static $bftr_rule_bftr_commit_wait_func = 'bftr_commit_wait';
    /**
     * @var string
     */
    private static $bftr_rule_bftr_block_get_func = 'bftr_block_get';
    /**
     * @var string
     */
    private static $bftr_rule_bftr_block_stage_func = 'bftr_block_stage';
    /**
     * @var string
     */
    private static $bftr_rule_bftr_block_broadcast_func = 'bftr_block_broadcast';
    /**
     * @var string
     */
    private static $bftr_rule_bftr_commit_time_set_func = 'bftr_commit_time_set';

    /**
     * @var Block_simple
     */
    private $bftr_commit;
    /**
     * @var int
     */
    private $bftr_commit_timestamp;

    /**
     * @param string $version
     * @return bool
     */
    public function bftr_build(string $version)
    {
        $this->workflow_version = $version;

        $this->bftr_build_commit_start();
        $this->bftr_build_height_new_ready();
        $this->bftr_build_propose();
        $this->bftr_build_prevote();
        $this->bftr_build_precommit();
        $this->bftr_build_commit_end();
        $this->bftr_build_loop();

        return true;
    }

    /**
     * @return bool
     */
    private function bftr_build_commit_start(){

        $rule_list_definition = array();
        $rule_list_definition[self::$bftr_rule_height_new_prepare_func] = new Bftr_rule_definition();
        $rule_list_definition[self::$bftr_rule_broadcast_new_state_to_peers_func] = new Bftr_rule_definition();

        $transition_commit_start = $this->bftr_build_transition(__FUNCTION__, $rule_list_definition);

        return $this->process_workflow_init(self::$bftr_commit_target_state, $transition_commit_start);
    }

    /**
     * @return int
     */
    private function bftr_build_height_new_ready(){

        $rule_list_definition = array();
        $rule_list_definition[self::$bftr_rule_height_new_wait_func] = new Bftr_rule_definition();

        $transition_height_new_ready = $this->bftr_build_transition(__FUNCTION__, $rule_list_definition);

        return $this->process_workflow_transition_add($transition_height_new_ready);
    }

    /**
     * @return int
     */
    private function bftr_build_propose(){

        $transition_propose = $this->bftr_build_transition(__FUNCTION__);

        return $this->process_workflow_transition_add($transition_propose);
    }

    /**
     * @return int
     */
    private function bftr_build_prevote(){

        $transition_prevote = $this->bftr_build_transition(__FUNCTION__);

        return $this->process_workflow_transition_add($transition_prevote);
    }

    /**
     * @return int
     */
    private function bftr_build_precommit(){

        $rule_list_definition = array();
        $rule_list_definition[self::$bftr_rule_precommit_count_func] = new Bftr_rule_definition();

        $transition_precommit = $this->bftr_build_transition(__FUNCTION__, $rule_list_definition);

        return $this->process_workflow_transition_add($transition_precommit);
    }

    /**
     * @return int
     */
    private function bftr_build_commit_end() {

        $rule_list_definition = array();
        $rule_list_definition[self::$bftr_rule_bftr_commit_wait_func] = new Bftr_rule_definition();
        $rule_list_definition[self::$bftr_rule_bftr_block_get_func] = new Bftr_rule_definition();
        $rule_list_definition[self::$bftr_rule_bftr_block_stage_func] = new Bftr_rule_definition();
        $rule_list_definition[self::$bftr_rule_bftr_block_broadcast_func] = new Bftr_rule_definition();
        $rule_list_definition[self::$bftr_rule_bftr_commit_time_set_func] = new Bftr_rule_definition();

        $transition_commit_end = $this->bftr_build_transition(__FUNCTION__, $rule_list_definition);

        return $this->process_workflow_transition_add($transition_commit_end);
    }

    /**
     * @return int
     */
    private function bftr_build_loop(){

        $transition_loop = $this->bftr_build_transition(__FUNCTION__);

        return $this->process_workflow_transition_add($transition_loop);
    }

    /**
     * @param string $function_name
     * @param array $param_list
     * @param bool $required_ok_state
     * @return Process_rule_simple
     */
    private function bftr_build_rule(string $function_name, array $param_list = array(), bool $required_ok_state = true){

        $rule = new Process_rule_simple();
        $rule->build($function_name, true, false, $this->workflow_name, $required_ok_state, $param_list);

        return $rule;
    }

    /**
     * @param Process_transition_simple $transition
     * @param array $rule_list_definition
     * @return Process_transition_simple
     */
    private function bftr_build_rules_add(Process_transition_simple $transition, array $rule_list_definition = array()) {

        foreach($rule_list_definition as $rule_name => $rule_definition) {

            $rule = $this->bftr_build_rule($rule_name, $rule_definition->param_list, $rule_definition->required_state_ok);
            $transition->rule_list_add($rule);
        }
        return $transition;
    }

    /**
     * @param string $transition_name
     * @param array $rule_list_definition
     * @return Process_transition_simple
     */
    private function bftr_build_transition(string $transition_name, array $rule_list_definition = array()){

        $transition = new Process_transition_simple();
        $transition->build($transition_name, $this->workflow_name, false);

        $transition = $this->bftr_build_rules_add($transition, $rule_list_definition);

        return $transition;
    }
}