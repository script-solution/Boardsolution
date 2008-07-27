<?php
/**
 * Contains the simplesave-themes-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The simplesave-themes-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_themes_simplesave extends BS_ACP_Action_Base
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
		foreach($input->get_vars_from_method('post') as $key => $value)
		{
			if(PLIB_String::strpos($key,'|') !== false)
			{
				$split = explode('|',$key);
				if(isset($split[2]))
				{
					$val = $input->get_var($split[0].'|'.$split[1].'|val','post');
					$type = $input->get_var($split[0].'|'.$split[1].'|type','post');
					$value = $val.$type;
				}
				
				$css->set_class_attribute(str_replace('%','.',$split[0]),$split[1],stripslashes($value));
			}
		}
		
		// nothing to do?
		if(!$css->has_changed())
			return '';

		if(!$css->write())
			return sprintf($locale->lang('file_not_saved'),$file);
		
		$this->set_action_performed(true);
		$this->set_success_msg($locale->lang('file_saved'));
		
		return '';
	}
}
?>