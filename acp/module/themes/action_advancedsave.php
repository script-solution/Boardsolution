<?php
/**
 * Contains the advancedsave-themes-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The advancedsave-themes-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_themes_advancedsave extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$file = $input->get_var('file','get',FWS_Input::STRING);
		if(!preg_match('/^[a-zA-Z0-9_]+\.css$/',$file))
			return 'Invalid filename!';
		$theme = $input->get_var('theme','get',FWS_Input::STRING);
		if($theme == null)
			return 'Invalid theme "'.$theme.'"';
		
		$path = FWS_Path::server_app().'themes/'.$theme.'/'.$file;
		
		$content = $input->get_var('file_content','post',FWS_Input::STRING);
		$content = FWS_StringHelper::htmlspecialchars_back(stripslashes(trim($content)));
		
		if(!FWS_FileUtils::write($path,$content))
			return sprintf($locale->lang('file_not_saved'),$path);
		
		$this->set_action_performed(true);
		$this->set_success_msg($locale->lang('file_saved'));

		return '';
	}
}
?>