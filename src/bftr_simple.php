<?php

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

        $this->build_commit_start();
        $this->build_height_new_ready();
        $this->build_propose();
        $this->build_prevote();
        $this->build_precommit();
        $this->build_commit_end();
        $this->build_loop();

        return true;
    }

    private function build_commit_start(){

        $rule_height_new_prepare = new Process_rule_simple();
        $rule_height_new_prepare->build('bftr_height_new_prepare', true, false, $this->workflow_name, true, array());

        $rule_broadcast_new_state_to_peers = new Process_rule_simple();
        $rule_broadcast_new_state_to_peers->build('bftr_broadcast_new_state_to_peers', true, false, $this->workflow_name, true, array());

        $transition_commit_start = new Process_transition_simple();
        $transition_commit_start->build('commit_start', true, false);
        $transition_commit_start->rule_list_add($rule_height_new_prepare);
        $transition_commit_start->rule_list_add($rule_broadcast_new_state_to_peers);

        return $this->process_workflow_init(self::$bftr_commit_target_state, $transition_commit_start);
    }

    private function build_height_new_ready(){

        $rule_height_new_wait = new Process_rule_simple();
        $rule_height_new_wait->build('bftr_height_new_wait', true, false, $this->workflow_name, true, array());

        $transition_height_new_ready = new Process_transition_simple();
        $transition_height_new_ready->build('bftr_height_new_ready', true, false);
        $transition_height_new_ready->rule_list_add($transition_height_new_ready);

        return $this->process_workflow_transition_add($transition_height_new_ready);
    }

    private function build_propose(){

        $transition_propose = new Process_transition_simple();
        $transition_propose->build('bftr_propose', true, false);

        return $this->process_workflow_transition_add($transition_propose);
    }

    private function build_prevote(){

        $transition_prevote = new Process_transition_simple();
        $transition_prevote->build('bftr_prevote', true, false);

        return $this->process_workflow_transition_add($transition_prevote);
    }

    private function build_precommit(){

        $rule_precommit_count = new Process_rule_simple();
        $rule_precommit_count->build('bftr_precommit_count', true, false, $this->workflow_name, true, array());

        $transition_precommit = new Process_transition_simple();
        $transition_precommit->build('bftr_precommit', true, false);
        $transition_precommit->rule_list_add($rule_precommit_count);

        return $this->process_workflow_transition_add($transition_precommit);
    }

    private function build_commit_end() {

        $rule_commit_wait = new Process_rule_simple();
        $rule_commit_wait->build('bftr_commit_wait', true, false, $this->workflow_name, true, array());

        $rule_block_get = new Process_rule_simple();
        $rule_block_get->build('bftr_block_get', true, false, $this->workflow_name, true, array());

        $rule_block_stage = new Process_rule_simple();
        $rule_block_stage->build('bftr_block_stage', true, false, $this->workflow_name, true, array());

        $rule_block_broadcast = new Process_rule_simple();
        $rule_block_broadcast->build('bftr_block_broadcast', true, false, $this->workflow_name, true, array());

        $rule_commit_time_set = new Process_rule_simple();
        $rule_commit_time_set->build('bftr_commit_time_set', true, false, $this->workflow_name, true, array());

        $transition_commit_end = new Process_transition_simple();
        $transition_commit_end->build('bftr_commit_end', true, false);
        $transition_commit_end->rule_list_add($rule_commit_wait);
        $transition_commit_end->rule_list_add($rule_block_get);
        $transition_commit_end->rule_list_add($rule_block_stage);
        $transition_commit_end->rule_list_add($rule_block_broadcast);
        $transition_commit_end->rule_list_add($rule_commit_time_set);

        return $this->process_workflow_transition_add($transition_commit_end);
    }

    private function build_loop(){

        $transition_loop = new Process_transition_simple();
        $transition_loop->build('bftr_loop', true, false);

        return $this->process_workflow_transition_add($transition_loop);
    }

}