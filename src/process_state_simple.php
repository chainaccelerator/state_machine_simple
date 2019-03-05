<?php

/**
 * Class Process_state_simple
 */
class Process_state_simple {

    use log_simple;

    /**
     * @var string
     */
    public static $value_initial = 'initial';
    /**
     * @var string
     */
    public static $value_confirm = 'confirm';
    /**
     * @var string
     */
    public static $value_target = 'target';
    /**
     * @var string
     */
    public static $value_fail = 'fail';
    /**
     * @var string
     */
    public static $suffix_ok = '_ok';
    /**
     * @var string
     */
    public static $suffix_fail = '_fail';
    /**
     * @var string
     */
    public static $suffix_cancel = '_cancel';
    /**
     * @var bool
     */
    private $log_state = true;
    /**
     * @var
     */
    public $name;
    /**
     * @var
     */
    private $value;
    /**
     * @var
     */
    private $workflow_name;
    /**
     * @var array
     */
    private $transition_list = array();

    /**
     * @param string $name
     * @param string $workflow_name
     * @param string $value
     * @param bool $log_state
     * @return bool
     */
    private function build(string $name, string $workflow_name, string $value, $log_state = true)
    {
        $this->log_state = $log_state;
        $this->name = $name;
        $this->value = $value;
        $this->workflow_name = $workflow_name;

        return true;
    }

    /**
     * @param string $name
     * @param string $workflow_name
     * @return bool
     */
    public function build_initial(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_ok, $workflow_name, self::$value_initial, true);
    }

    /**
     * @param string $name
     * @param string $workflow_name
     * @return bool
     */
    public function build_confirm(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_ok, $workflow_name, self::$value_confirm, true);
    }

    /**
     * @param string $name
     * @param string $workflow_name
     * @return bool
     */
    public function build_cancel(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_cancel, $workflow_name, self::$value_cancel, true);
    }

    /**
     * @param string $name
     * @param string $workflow_name
     * @return bool
     */
    public function build_target(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_ok, $workflow_name, self::$value_target, true);
    }

    /**
     * @param string $name
     * @param string $workflow_name
     * @return bool
     */
    public function build_fail(string $name, string $workflow_name)
    {
        return $this->build($name . self::$suffix_fail, $workflow_name, self::$value_fail, true);
    }

    /**
     * @param Process_transition_simple $transition
     */
    public function transition_add(Process_transition_simple $transition)
    {

        $this->transition_list[$transition->name] = $transition;
    }

    /**
     * @param string $data_ref
     * @return bool|mixed
     */
    public function confirm(string $data_ref = ''){

        $this->value = self::$value_confirm;

        if($this->log_state === true) {

            $this->log_init();

            return $this->log_build($this, $this->workflow_name, $this->name, $data_ref);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function target(){

        $this->value = self::$value_target;

        return true;
    }

    /**
     * @return bool
     */
    public function cancel(){

        $this->value = self::$value_cancel;

        return true;
    }
}
