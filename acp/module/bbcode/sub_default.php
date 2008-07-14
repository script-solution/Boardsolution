<?php
/**
 * Contains the default-submodule for bbcode
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the bbcode-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_bbcode_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_DELETE_BBCODES => 'delete'
		);
	}
	
	public function run()
	{
		$site = $this->input->get_var('site','get',PLIB_Input::ID);
		if($site == null)
			$site = 1;
	
		// display delete-message?
		if($this->input->isset_var('delete','post'))
		{
			$ids = $this->input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_bbcodes()->get_by_ids($ids) as $row)
				$names[] = $row['name'];
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_BBCODES.'&amp;ids='.implode(',',$ids).'&amp;site='.$site
				),
				$this->url->get_acpmod_url(0,'&amp;site='.$site)
			);
		}
		
		$search = $this->input->get_var('search','get',PLIB_Input::STRING);
		if($search != '')
			$num = BS_DAO::get_bbcodes()->get_count_by_keyword($search);
		else
			$num = BS_DAO::get_bbcodes()->get_count();
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		if($search != '')
			$bbclist = BS_DAO::get_bbcodes()->get_list_by_keyword($search,$pagination->get_start(),$end);
		else
			$bbclist = BS_DAO::get_bbcodes()->get_list($pagination->get_start(),$end);
		
		$tags = array();
		foreach($bbclist as $data)
		{
			if($this->locale->contains_lang('tag_type_'.$data['type']))
				$type = $this->locale->lang('tag_type_'.$data['type']);
			else
				$type = $data['type'];
			
			$tags[] = array(
				'id' => $data['id'],
				'name' => $data['name'],
				'type' => $type,
				'content' => $data['content'],
				'param' => $this->locale->lang('tag_param_'.$data['param'])
			);
		}
		
		$url = $this->url->get_acpmod_url(0,'&amp;search='.$search.'&amp;site={d}');
		$this->functions->add_pagination($pagination,$url);
		
		$hidden = $this->input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$this->tpl->add_array('tags',$tags);
		$this->tpl->add_variables(array(
			'site' => $site,
			'search_url' => $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));
	}
	
	public function get_location()
	{
		return array();
	}
}
?>