<?php

Trait Bftr_simple
{

    use Process_workflow_simple;

    private static $bftr_precommit_found_min = 2 / 3;
    private static $bftr_precommit_ttl = 2 / 600000;
    private static $bftr_precommit_wait = 1000;
    private static $bftr_commit_found_min = 2 / 3;
    private static $bftr_commit_ttl = 2 / 600000;
    private static $bftr_commit_wait = 1000;

    public static function btfr_ttl_verif_and_wait(int $timestamp, int $ttl, int $wait)
    {

        while (self::btfr_ttl_verif($timestamp, $ttl) === true) {

            sleep($wait);
        }
        return false;
    }

    public static function btfr_ttl_verif(int $timestamp, int $ttl)
    {

        if (time() - $timestamp > $ttl) {

            return false;
        }
        return true;
    }

    public function bftr_build(string $version)
    {
        $this->workflow_version = $version;

        // prepare a new

        return true;
    }

    public function bftr_height_new_prepare()
    {


        $this->bftr_broadcast_new_state_to_peers(time());
    }

    public function bftr_broadcast_new_state_to_peers(int $timestamp_start)
    {

        while (btfr_ttl_verif_and_wait($timestamp_start, self::$bftr_precommit_ttl, self::$bftr_precommit_wait) === false) {

            return $this->bftr_height_new_propose(time(), $timestamp_start);
        }
        return $this->bftr_broadcast_new_state_to_peers($timestamp_start);
    }

    public function bftr_height_new_propose(int $timestamp_start, int $commit_time)
    {

        $this->bftr_height_new_prevote($timestamp_start);
    }

    public function bftr_height_new_prevote(int $timestamp_start)
    {


        $this->bftr_height_new_precommit($timestamp_start, $precommit_found);
    }

    public function bftr_height_new_precommit(int $timestamp_start, $precommit_found)
    {

        if ($precommit_found > $precommit_found_min) {

            return $this->bftr_height_new_propose($timestamp_start, $precommit_found);
        }
        return return bftr_height_new_propose($commit_time);
    }

    public function bftr_commit()
    {

        $commit_found = $this->bftr_commit_get();

        if ($commit_found > self::$bftr_commit_found_min) {

            $this->bftr_commit_set_commit_time();

            return $this->bftr_commit_get_block();
        }
        while (btfr_ttl_verif_and_wait($block->timestamp, self::$bftr_commit_ttl, self::$bftr_commit_wait) === true) {

            $this->bftr_commit();
        }
        return false;
    }

    public function bftr_commit_get()
    {
        // @todo

        return 1;
    }

    public function bftr_commit_set_commit_time()
    {

    }

    public function bftr_commit_get_block_save()
    {

    }

    public function bftr_commit_get_block_stage()
    {

    }

    public function bftr_commit_get_block_broadcast()
    {

    }

}