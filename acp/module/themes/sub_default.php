<?php
/**
 * Contains the default-submodule for themes
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the themes-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_themes_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_UPDATE_THEMES => 'update',
			BS_ACP_ACTION_DELETE_THEMES => 'delete'
		);
	}
	
	public function run()
	{
		$delete = $this->input->get_var('delete','post');
		if($delete != null)
		{
			$names = $this->cache->get_cache('themes')->get_field_vals_of_keys($delete,'theme_name');
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_THEMES.'&amp;ids='.implode(',',$delete)
				),
				$this->url->get_acpmod_url()
			);
		}
		
		$this->_request_formular();
		
		$this->tpl->add_variables(array(
			'at_update' => BS_ACP_ACTION_UPDATE_THEMES
		));
		
		$themes = array();
		foreach($this->cache->get_cache('themes') as $data)
		{
			$themes[] = array(
				'id' => $data['id'],
				'theme_name' => $data['theme_name'],
				'theme_folder' => $data['theme_folder'],
				'edit_url' => $this->url->get_acpmod_url(0,'&amp;action=editor&amp;theme='.$data['theme_folder'])
			);
		}
		$this->tpl->add_array('themes',$themes);
	}
	
	public function get_location()
	{
		return array();
	}
}
?>