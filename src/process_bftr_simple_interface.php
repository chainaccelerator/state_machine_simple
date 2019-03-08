<?php

/**
 * Interface Process_bftr_simple_interface
 */
interface Process_bftr_simple_interface {

    /**
     * @var array $input_params
     * @return bool
     */
    public function process_bftr_build_commit_start(array $input_params = array());

    /**
     * @var array $input_params
     * @return bool
     */
    public function process_bftr_build_height_new_ready(array $input_params = array());

    /**
     * @var array $input_params
     * @return bool
     */
    public function process_bftr_build_propose(array $input_params = array());

    /**
     * @var array $input_params
     * @return bool
     */
    public function process_bftr_build_prevote(array $input_params = array());

    /**
     * @var array $input_params
     * @return bool
     */
    public function process_bftr_build_precommit(array $input_params = array());

    /**
     * @var array $input_params
     * @return bool
     */
    public function process_bftr_build_commit_ready(array $input_params = array());

    /**
     * @var array $input_params
     * @return bool
     */
    public function process_bftr_build_commit_end(array $input_params = array());

    /**
     * @var array $input_params
     * @return bool
     */
    public function process_bftr_build_loop(array $input_params = array());
}

