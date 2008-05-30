<?php
/**
 * Contains the update-bans-action
 *
 * @version			$Id: action_update.php 737 2008-05-23 18:26:46Z nasmussen $
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
		$types = $this->input->get_var('types','post');
		$values = $this->input->get_var('values','post');
		if(!is_array($types) || !is_array($values) || count($types) != count($values))
			return 'Invalid POST-variables "types", "values". No arrays? Size not equal?';
		
		$valid_types = array('mail','user','ip');
		foreach($values as $id => $value)
		{
			if(PLIB_Helper::is_integer($id))
			{
				$type = in_array($types[$id],$valid_types) ? $types[$id] : 'ip';
				BS_DAO::get_bans()->update_by_id($id,$value,$type);
			}
		}

		$this->cache->refresh('banlist');
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>