<?php
/**
 * Contains the advancedsave-themes-action
 *
 * @version			$Id: action_advancedsave.php 676 2008-05-08 09:02:28Z nasmussen $
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
		$theme = $this->input->get_var('theme','get',PLIB_Input::STRING);
		if($theme == null)
			return 'Invalid theme "'.$theme.'"';
		
		$file = PLIB_Path::inner().'themes/'.$theme.'/style.css';
		
		$content = $this->input->get_var('file_content','post',PLIB_Input::STRING);
		$content = PLIB_StringHelper::htmlspecialchars_back(stripslashes(trim($content)),ENT_QUOTES);
		
		if(!PLIB_FileUtils::write($file,$content))
			return sprintf($this->locale->lang('file_not_saved'),$file);
		
		$this->set_action_performed(true);
		$this->set_success_msg($this->locale->lang('file_saved'));

		return '';
	}
}
?>