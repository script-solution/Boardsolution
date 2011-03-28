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
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$theme = $input->get_var('theme','get',FWS_Input::STRING);
		if($theme == null)
			return 'Invalid theme "'.$theme.'"';
		
		$file = FWS_Path::server_app().'themes/'.$theme.'/basic.css';
		$css = new FWS_CSS_StyleSheet(FWS_FileUtils::read($file));
		
		$attr = $input->get_var('attribute','post');
		$keys = array_keys($input->get_var('add','post'));
		if(!isset($keys[0]) || !isset($attr[$keys[0]]))
			return 'Invalid parameter';
		
		$attribute = $attr[$keys[0]];

		$block = $css->get_block($keys[0]);
		if($block !== null && $block->get_type() == FWS_CSS_Block::RULESET)
		{
			if(!$block->contains_property($attribute))
				$block->set_property($attribute,FWS_CSS_Block_Ruleset::get_def_prop_value($attribute));
		}
		
		if(!FWS_FileUtils::write($file,(string)$css))
			return sprintf($locale->lang('file_not_saved'),$file);
		
		$this->set_success_msg($locale->lang('file_saved'));
		$this->set_action_performed(true);
		
		return '';
	}
}
?>