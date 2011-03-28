<?php
/**
 * Contains the action-performer
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The action-performer. We overwrite it to provide a custom get_action_id()
 * method.
 *
 * @package			Boardsolution
 * @subpackage	acp.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_Performer extends FWS_Action_Performer implements FWS_Action_Listener
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->set_mod_folder('acp/module/');
		$this->set_prefix('BS_ACP_Action_');
		$this->set_listener($this);
	}
	
	/**
	 * @see FWS_Action_Performer::get_action_id()
	 *
	 * @return int
	 */
	protected function get_action_id()
	{
		$input = FWS_Props::get()->input();

		$action_type = $input->get_var('action_type','post',FWS_Input::INTEGER);
		if($action_type === null)
			$action_type = $input->get_var('at','get',FWS_Input::INTEGER);

		return $action_type;
	}
	/**
	 * @see FWS_Action_Listener::before_action_performed()
	 *
	 * @param int $id
	 * @param FWS_Action_Base $action
	 */
	public function before_action_performed($id,$action)
	{
		// we have to add the messages-file if an action should be performed
		$locale = FWS_Props::get()->locale();
		$locale->add_language_file('messages');
	}
	
	/**
	 * @see FWS_Action_Listener::after_action_performed()
	 *
	 * @param int $id
	 * @param FWS_Action_Base $action
	 * @param string $message
	 */
	public function after_action_performed($id,$action,&$message)
	{
		// do nothing
	}
}
?>