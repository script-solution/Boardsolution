<?php
/**
 * Contains the acp-action-listener
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The action-listener for the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_Listener extends FWS_Object implements FWS_Action_Listener
{
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
	
	/**
	 * @see FWS_Object::get_dump_vars()
	 *
	 * @return array
	 */
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>