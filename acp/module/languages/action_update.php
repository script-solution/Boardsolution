<?php
/**
 * Contains the update-languages-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The update-languages-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_languages_update extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$names = $input->get_var('names','post');
		$folders = $input->get_var('folders','post');
		if(count($names) == 0 || count($folders) == 0 || count($names) != count($folders))
			return 'Invalid POST-variables "names" and "folders". No array? Empty? Size not equal?';
		
		$count = 0;
		foreach($names as $id => $value)
		{
			$data = $cache->get_cache('languages')->get_element($id);
			if(FWS_Helper::is_integer($id) && isset($folders[$id]) &&
				($data['lang_name'] != $value || $data['lang_folder'] != $folders[$id]))
			{
				BS_DAO::get_langs()->update_by_id($id,$value,$folders[$id]);
				$count++;
			}
		}

		if($count > 0)
			$cache->refresh('languages');

		$this->set_success_msg($locale->lang('langs_updated_notice'));
		$this->set_action_performed(true);

		return '';
	}
}
?>