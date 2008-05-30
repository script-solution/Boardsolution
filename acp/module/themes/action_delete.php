<?php
/**
 * Contains the delete-themes-action
 *
 * @version			$Id: action_delete.php 753 2008-05-24 16:04:46Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-themes-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_themes_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		// try to delete the directories
		$res = true;
		foreach($this->cache->get_cache('themes') as $data)
		{
			if(in_array($data['id'],$ids))
			{
				$folder = PLIB_Path::inner().'themes/'.$data['theme_folder'];
				if(is_dir($folder))
				{
					if(!PLIB_FileUtils::delete_folder($folder))
						$res = false;
				}
			}
		}
		
		BS_DAO::get_themes()->delete_by_ids($ids);
		
		// use the default-theme instead of the themes which have been deleted
		BS_DAO::get_profile()->update_theme_to_default($ids);

		$this->cache->refresh('themes');
		
		if(!$res)
			return 'theme_folder_delete_failed';
		
		$this->set_success_msg($this->locale->lang('theme_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>