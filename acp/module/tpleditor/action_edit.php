<?php
/**
 * Contains the edit-tpleditor-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-tpleditor-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_tpleditor_edit extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$helper = BS_ACP_Module_TplEditor_Helper::get_instance();
		$path = $helper->get_path();
		
		$file = $input->get_var('file','get',FWS_Input::STRING);
		$content = $input->get_var('file_content','post',FWS_Input::STRING);
		
		if($fp = @fopen($path.'/'.$file,'w'))
		{
			flock($fp,LOCK_EX);
			$content = FWS_StringHelper::htmlspecialchars_back(stripslashes(trim($content)),ENT_QUOTES);
			fwrite($fp,$content);
			flock($fp,LOCK_UN);
			fclose($fp);
		}
		else
			return 'template_edit_failed';
		
		$this->set_success_msg($locale->lang('template_edit_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>