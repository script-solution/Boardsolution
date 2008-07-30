<?php
/**
 * Contains the delete-themes-action
 *
 * @version			$Id$
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
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$id_str = $input->get_var('ids','get',FWS_Input::STRING);
		if(!($ids = FWS_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		// try to delete the directories
		$res = true;
		foreach($cache->get_cache('themes') as $data)
		{
			if(in_array($data['id'],$ids))
			{
				$folder = FWS_Path::server_app().'themes/'.$data['theme_folder'];
				if(is_dir($folder))
				{
					if(!FWS_FileUtils::delete_folder($folder))
						$res = false;
				}
			}
		}
		
		BS_DAO::get_themes()->delete_by_ids($ids);
		
		// use the default-theme instead of the themes which have been deleted
		BS_DAO::get_profile()->update_theme_to_default($ids);

		$cache->refresh('themes');
		
		if(!$res)
			return 'theme_folder_delete_failed';
		
		$this->set_success_msg($locale->lang('theme_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>