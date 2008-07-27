<?php
/**
 * Contains the simpleadd-themes-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The simpleadd-themes-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_themes_simpleadd extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();

		$theme = $input->get_var('theme','get',PLIB_Input::STRING);
		if($theme == null)
			return 'Invalid theme "'.$theme.'"';
		
		$file = PLIB_Path::server_app().'themes/'.$theme.'/style.css';
		$css = new PLIB_CSS_SimpleParser($file,$file);
		
		$attr = $input->get_var('attribute','post');
		$keys = array_keys($input->get_var('add','post'));
		$attribute = $attr[$keys[0]];

		if(!$css->class_attribute_exists($keys[0],$attribute))
		{
			$css->set_class_attribute($keys[0],$attribute,$css->get_default_value($attribute));
			$css->write();
		}
		
		if(!$css->write())
			return sprintf($locale->lang('file_not_saved'),$file);
		
		$this->set_success_msg($locale->lang('file_saved'));
		$this->set_action_performed(true);
		
		return '';
	}
}
?>