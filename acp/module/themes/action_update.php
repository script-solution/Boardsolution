<?php
/**
 * Contains the update-themes-action
 *
 * @version			$Id: action_update.php 753 2008-05-24 16:04:46Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The update-themes-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_themes_update extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$names = $this->input->get_var('names','post');
		$folders = $this->input->get_var('folders','post');
		if(!is_array($names) || !is_array($folders))
			return 'Invalid POST-variables "names" or "folders"';
		
		$c = 0;
		foreach($names as $id => $value)
		{
			if(PLIB_Helper::is_integer($id))
			{
				BS_DAO::get_themes()->update_by_id($id,$value,isset($folders[$id]) ? $folders[$id] : '');
				$c++;
			}
		}

		if($c > 0)
			$this->cache->refresh('themes');
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>