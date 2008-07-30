<?php
/**
 * Contains the action-performer for the ACP
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The action-performer for the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_Performer extends FWS_Actions_Performer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->set_mod_folder('acp/module/');
	}
	
	protected function before_action_performed($id,$action)
	{
		$locale = FWS_Props::get()->locale();

		parent::before_action_performed($id,$action);
		
		// we have to add the messages-file if an action should be performed
		$locale->add_language_file('messages');
	}
}
?>