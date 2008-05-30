<?php
/**
 * Contains the languages module for the ACP
 * 
 * @version			$Id: module_languages.php 765 2008-05-24 21:14:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The languages-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_languages extends BS_ACP_Module
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_ADD_LANGUAGE => 'add',
			BS_ACP_ACTION_DELETE_LANGUAGES => 'delete',
			BS_ACP_ACTION_UPDATE_LANGUAGES => 'update'
		);
	}
	
	public function run()
	{
		if($this->input->isset_var('delete','post'))
		{
			$ids = $this->input->get_var('delete','post');
			$names = $this->cache->get_cache('languages')->get_field_vals_of_keys($ids,'lang_name');
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(0,
					'&amp;at='.BS_ACP_ACTION_DELETE_LANGUAGES.'&amp;ids='.implode(',',$ids)
				),
				$this->url->get_acpmod_url()
			);
		}
		
		$this->tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_UPDATE_LANGUAGES,
			'action_type_add' => BS_ACP_ACTION_ADD_LANGUAGE
		));

		$languages = $this->cache->get_cache('languages')->get_elements();
		$this->tpl->add_array('languages',$languages);
	}

	public function get_location()
	{
		return array(
			$this->locale->lang('acpmod_languages') => $this->url->get_acpmod_url()
		);
	}
}
?>