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

        $rule_height_new_prepare = new Process_rule_simple();
        $rule_height_new_prepare->build('bftr_height_new_prepare', true, false, $this->workflow_name, true, array());

        $rule_broadcast_new_state_to_peers = new Process_rule_simple();
        $rule_broadcast_new_state_to_peers->build('bftr_broadcast_new_state_to_peers', true, false, $this->workflow_name, true, array());

        $transition_commit_start = new Process_transition_simple();
        $transition_commit_start->build('commit_start', true, false);
        $transition_commit_start->rule_list_add($rule_height_new_prepare);
        $transition_commit_start->rule_list_add($rule_broadcast_new_state_to_peers);

        $this->process_workflow_init(self::$bftr_commit_target_state, $transition_commit_start);

        $rule_height_new_wait = new Process_rule_simple();
        $rule_height_new_wait->build('bftr_height_new_wait', true, false, $this->workflow_name, true, array());

        $transition_height_new_ready = new Process_transition_simple();
        $transition_height_new_ready->build('bftr_height_new_ready', true, false);
        $transition_height_new_ready->rule_list_add($transition_height_new_ready);

        $this->process_workflow_transition_add($transition_height_new_ready);

        $transition_propose = new Process_transition_simple();
        $transition_propose->build('bftr_propose', true, false);

        $this->process_workflow_transition_add($transition_propose);

        $transition_prevote = new Process_transition_simple();
        $transition_prevote->build('bftr_prevote', true, false);

        $this->process_workflow_transition_add($transition_prevote);

        $rule_precommit_count = new Process_rule_simple();
        $rule_precommit_count->build('bftr_precommit_count', true, false, $this->workflow_name, true, array());

        $transition_precommit = new Process_transition_simple();
        $transition_precommit->build('bftr_precommit', true, false);
        $transition_precommit->rule_list_add($rule_precommit_count);

        $this->process_workflow_transition_add($transition_precommit);

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

        $this->process_workflow_transition_add($transition_commit_end);

        $transition_loop = new Process_transition_simple();
        $transition_loop->build('bftr_loop', true, false);

        $this->process_workflow_transition_add($transition_loop);

        return true;
    }

    /**
     *
     */
    public function bftr_prepare_new_height()
    {
        $this->bftr_commit = $this->block_data;

        return true;
    }

    /**
     *
     */
    public function bftr_broadcast_new_state_to_peers()
    {
        $this->bftr_commit_timestamp = time();

        while ($this->process_workflow_ttl_verif_and_wait(
                $this->bftr_commit_timestamp,
                self::$bftr_precommit_ttl,
                self::$bftr_precommit_wait) === false) {

            return $this->bftr_broadcast_new_state_to_peers();
        }
        return false;
    }

    /**
     * @param int $timestamp_start
     * @param int $commit_time
     */
    public function bftr_height_new_propose()
    {
    }

    /**
     * @param int $timestamp_start
     */
    public function bftr_height_new_prevote(int $timestamp_start)
    {


        $this->bftr_height_new_precommit($timestamp_start, $precommit_found);
    }

    /**
     * @param int $timestamp_start
     * @param $precommit_found
     */
    public function bftr_height_new_precommit(int $timestamp_start, $precommit_found)
    {

        if ($precommit_found > $precommit_found_min) {

            return $this->bftr_height_new_propose($timestamp_start, $precommit_found);
        }
        return return bftr_height_new_propose($commit_time);
    }

    /**
     * @return bool
     */
    public function bftr_commit()
    {

        $commit_found_list = $this->bftr_commit_list_get();

        foreach($commit_found_list as $bftr_commit) {

            if (count($commit_found_list) > self::$bftr_commit_found_min) {

                $this->bftr_commit_set_commit_time();

                return $this->bftr_commit_get_block();
            }
            while ($this->btfr_ttl_verif_and_wait($bftr_commit->timestamp, self::$bftr_commit_ttl, self::$bftr_commit_wait) === true) {

                $this->bftr_commit();
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function bftr_commit_list_get()
    {
        // @todo

        return array();
    }

    /**
     *
     */
    public function bftr_commit_set_commit_time()
    {

    }

    /**
     *
     */
    public function bftr_commit_get_block_save()
    {

    }

    /**
     *
     */
    public function bftr_commit_get_block_stage()
    {

    }

    /**
     *
     */
    public function bftr_commit_get_block_broadcast()
    {

    }

}