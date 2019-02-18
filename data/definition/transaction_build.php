<?php

class Transaction
{

    use process_workflow_simple;

    public function build()
    {

        $rule_sign_from_verif = $this->process_workflow_rule_build('sign_from_verif');
        $rule_sign_from_verif->input_params_set(array("amount", "address_form", "msg_signed", "public_key"));
        $rule_sign_to_get = $this->process_workflow_rule_build('sign_to_get');
        $rule_sign_to_get->input_params_set(array("amount", "address_to", "msg_to_sign", "public_key"));
        $rule_balance_check = $this->process_workflow_rule_build('balance_check');
        $rule_balance_check->input_params_set(array("amount", "address_from"));
        $rule_double_spend_check = $this->process_workflow_rule_build('double_spend_check');
        $rule_double_spend_check->input_params_set(array("address_from"));
        $rule_reward_list_get = $this->process_workflow_rule_build('reward_list_get');
        $rule_reward_list_get->input_params_set(array("contribution_list"));

        $state_end = $this->process_workflow_state_target_build(__CLASS__);
        $state_end_reward_list = $this->process_workflow_state_target_build('reward_list');

        $transition = $this->process_workflow_transition_build(__CLASS__);
        $transition->state_end_list_add($state_end);
        $transition->state_end_list_add($state_end_reward_list);
        $transition->rule_list_add($rule_sign_from_verif);
        $transition->rule_list_add($rule_sign_to_get);
        $transition->rule_list_add($rule_balance_check);
        $transition->rule_list_add($rule_double_spend_check);
        $transition->rule_list_add($rule_reward_list_get);

        $state_inital = $this->process_workflow_state_initial_build();
        $state_inital->transition_add($transition);

        $this->process_workflow_init();
        $this->process_workflow_state_initial_add($state_inital);

        return true;
    }


}
