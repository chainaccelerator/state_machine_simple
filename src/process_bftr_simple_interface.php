<?php

/**
 * Interface Process_bftr_simple_interface
 */
interface Process_bftr_simple_interface {

    /**
     * @return bool
     */
    public function process_bftr_build_commit_start();

    /**
     * @return bool
     */
    public function process_bftr_build_height_new_ready();

    /**
     * @return bool
     */
    public function process_bftr_build_propose();

    /**
     * @return bool
     */
    public function process_bftr_build_prevote();

    /**
     * @return bool
     */
    public function process_bftr_build_precommit();

    /**
     * @return bool
     */
    public function process_bftr_build_commit_ready();

    /**
     * @return bool
     */
    public function process_bftr_build_commit_end();

    /**
     * @return bool
     */
    public function process_bftr_build_loop();
}

