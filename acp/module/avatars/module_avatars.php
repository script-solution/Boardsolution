<?php
/**
 * Contains the avatar module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The avatar-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_avatars extends BS_ACP_Module
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_DELETE_AVATARS => 'delete',
			BS_ACP_ACTION_IMPORT_AVATARS => 'import'
		);
	}
	
	public function run()
	{
		$delete = $this->input->get_var('delete','post');
		if($delete != null)
		{
			$ids = $this->input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_avatars()->get_by_ids($ids) as $data)
				$names[] = $data['av_pfad'];
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(0,
					'&amp;at='.BS_ACP_ACTION_DELETE_AVATARS.'&amp;ids='.implode(',',$ids)
				),
				$this->url->get_acpmod_url()
			);
		}
		
		$search = $this->input->get_var('search','get',PLIB_Input::STRING);
		if($search != '')
			$num = BS_DAO::get_avatars()->get_count_for_keyword($search);
		else
			$num = BS_DAO::get_avatars()->get_count();
		
		$hidden = $this->input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$this->tpl->add_variables(array(
			'num' => $num,
			'action_type_import' => BS_ACP_ACTION_IMPORT_AVATARS,
			'search_url' => $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));

		$end = 10;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$avatars = array();
		if($search != '')
			$list = BS_DAO::get_avatars()->get_list_for_keyword($search,$pagination->get_start(),$end);
		else
			$list = BS_DAO::get_avatars()->get_list($pagination->get_start(),$end);
		
		foreach($list as $data)
		{
			if($data['user'] == 0)
				$data['owner'] = $this->locale->lang('administrator');
			else
				$data['owner'] = BS_ACP_Utils::get_instance()->get_userlink($data['user'],$data['user_name']);
			$avatars[] = $data;
		}

		$this->tpl->add_array('avatars',$avatars);
		$url = $this->url->get_acpmod_url(0,'&amp;search='.$search.'&amp;site={d}');
		$this->functions->add_pagination($pagination,$url);
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('acpmod_avatars') => $this->url->get_acpmod_url()
		);
	}
}
?>