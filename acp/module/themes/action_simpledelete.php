<?php
/**
 * Contains the simpledelete-themes-action
 *
 * @version			$Id$
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
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$theme = $input->get_var('theme','get',FWS_Input::STRING);
		if($theme == null)
			return 'Invalid theme "'.$theme.'"';
		
		$file = FWS_Path::server_app().'themes/'.$theme.'/style.css';
		$css = new FWS_CSS_SimpleParser($file,$file);
		
		$split = explode(',',$input->get_var('ids','get',FWS_Input::STRING));
		for($i = 0;$i < count($split);$i++)
		{
			if($split[$i] != '')
			{
				$explode = explode('|',$split[$i]);
				$css->remove_class_attribute($explode[0],$explode[1]);
			}
		}

		if(!$css->write())
			return sprintf($locale->lang('file_not_saved'),$file);
		
		$this->set_success_msg($locale->lang('file_saved'));
		$this->set_action_performed(true);

		return '';
	}
}
?>