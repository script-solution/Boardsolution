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
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();

		$theme = $input->get_var('theme','get',PLIB_Input::STRING);
		if($theme == null)
			return 'Invalid theme "'.$theme.'"';
		
		$file = PLIB_Path::server_app().'themes/'.$theme.'/style.css';
		
		$content = $input->get_var('file_content','post',PLIB_Input::STRING);
		$content = PLIB_StringHelper::htmlspecialchars_back(stripslashes(trim($content)),ENT_QUOTES);
		
		if(!PLIB_FileUtils::write($file,$content))
			return sprintf($locale->lang('file_not_saved'),$file);
		
		$this->set_action_performed(true);
		$this->set_success_msg($locale->lang('file_saved'));

		return '';
	}
}
?>