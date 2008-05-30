<?php
/**
 * Contains the simpledelete-themes-action
 *
 * @version			$Id: action_simpledelete.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The simpledelete-themes-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_themes_simpledelete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$theme = $this->input->get_var('theme','get',PLIB_Input::STRING);
		if($theme == null)
			return 'Invalid theme "'.$theme.'"';
		
		$file = PLIB_Path::inner().'themes/'.$theme.'/style.css';
		$css = new PLIB_CSS_SimpleParser($file,$file);
		
		$split = explode(',',$this->input->get_var('ids','get',PLIB_Input::STRING));
		for($i = 0;$i < count($split);$i++)
		{
			if($split[$i] != '')
			{
				$explode = explode('|',$split[$i]);
				$css->remove_class_attribute($explode[0],$explode[1]);
			}
		}

		if(!$css->write())
			return sprintf($this->locale->lang('file_not_saved'),$file);
		
		$this->set_success_msg($this->locale->lang('file_saved'));
		$this->set_action_performed(true);

		return '';
	}
}
?>