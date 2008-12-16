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
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$theme = $input->get_var('theme','get',FWS_Input::STRING);
		if($theme == null)
			return 'Invalid theme "'.$theme.'"';
		
		$file = FWS_Path::server_app().'themes/'.$theme.'/basic.css';
		$css = new FWS_CSS_StyleSheet(FWS_FileUtils::read($file));
		foreach($input->get_vars_from_method('post') as $key => $value)
		{
			if(FWS_String::strpos($key,'|') !== false)
			{
				$split = explode('|',$key);
				list(,$blockno) = explode('_',$split[0]);
				if(isset($split[2]))
				{
					$val = $input->get_var($split[0].'|'.$split[1].'|val','post');
					$type = $input->get_var($split[0].'|'.$split[1].'|type','post');
					$value = $val.$type;
				}
				$block = $css->get_block($blockno);
				if($block->get_type() == FWS_CSS_Block::RULESET)
					$block->set_property($split[1],stripslashes($value));
			}
		}
		
		if(!FWS_FileUtils::write($file,(string)$css))
			return sprintf($locale->lang('file_not_saved'),$file);
		
		$this->set_action_performed(true);
		$this->set_success_msg($locale->lang('file_saved'));
		
		return '';
	}
}
?>