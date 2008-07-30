<?php
/**
 * Contains the update-bans-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The update-bans-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_banlist_update extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();

		$types = $input->get_var('types','post');
		$values = $input->get_var('values','post');
		if(!is_array($types) || !is_array($values) || count($types) != count($values))
			return 'Invalid POST-variables "types", "values". No arrays? Size not equal?';
		
		$valid_types = array('mail','user','ip');
		foreach($values as $id => $value)
		{
			if(FWS_Helper::is_integer($id))
			{
				$type = in_array($types[$id],$valid_types) ? $types[$id] : 'ip';
				BS_DAO::get_bans()->update_by_id($id,$value,$type);
			}
		}

		$cache->refresh('banlist');
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>