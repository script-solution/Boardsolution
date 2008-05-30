<?php
/**
 * Contains the default-submodule for smileys
 * 
 * @version			$Id: sub_default.php 795 2008-05-29 18:22:45Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the smileys-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_smileys_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_SWITCH_SMILEYS => 'switch',
			BS_ACP_ACTION_DELETE_SMILEYS => 'delete',
			BS_ACP_ACTION_IMPORT_SMILEYS => 'import'
		);
	}
	
	public function run()
	{
		if($this->input->isset_var('delete','post'))
		{
			$site = $this->input->get_var('site','get',PLIB_Input::ID);
			$ids = $this->input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_smileys()->get_by_ids($ids) as $smiley)
				$names[] = $smiley['smiley_path'];
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(0,
					'&amp;at='.BS_ACP_ACTION_DELETE_SMILEYS.'&amp;ids='.implode(',',$ids).'&amp;site='.$site
				),
				$this->url->get_acpmod_url(0,'&amp;site='.$site)
			);
		}
		
		$num = BS_DAO::get_smileys()->get_count();
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		$page = $pagination->get_page();
		
		$this->tpl->add_variables(array(
			'page' => $page,
			'import_url' => $this->url->get_acpmod_url(0,'&amp;at='.BS_ACP_ACTION_IMPORT_SMILEYS)
		));
		
		$smileys = array();
		if($num)
		{
			// collect rows
			$rows = BS_DAO::get_smileys()->get_all();
			
			$start = $page > 1 ? (($page - 1) * $end) : 0;
			$i = $start;
			$loop_end = min($start + $end,count($rows));
			for(;$i < $loop_end;$i++)
			{
				$data = &$rows[$i];
				
				$switch_up_url = '';
				if($i > 0)
				{
					$prev = &$rows[$i - 1];
					$switch_up_url = $this->url->get_acpmod_url(
						0,'&amp;at='.BS_ACP_ACTION_SWITCH_SMILEYS.'&amp;ids='.$data['id'].','.$prev['id']
							.'&amp;site='.$page
					);
				}
	
				$switch_down_url = '';
				if($i < $num - 1)
				{
					$next = &$rows[$i + 1];
					$switch_down_url = $this->url->get_acpmod_url(
						0,'&amp;at='.BS_ACP_ACTION_SWITCH_SMILEYS.'&amp;ids='.$next['id'].','.$data['id']
							.'&amp;site='.$page
					);
				}
				
				$smileys[] = array(
					'id' => $data['id'],
					'primary_code' => $data['primary_code'],
					'secondary_code' => $data['secondary_code'],
					'smiley_path' => $data['smiley_path'],
					'sort_key' => $data['sort_key'],
					'is_base' => BS_ACP_Utils::get_instance()->get_yesno($data['is_base']),
					'switch_up_url' => $switch_up_url,
					'switch_down_url' => $switch_down_url,
					'show_up' => $i > 0,
					'show_down' => $i < $num - 1
				);
			}
		}

		$this->tpl->add_array('smileys',$smileys);
		
		$this->functions->add_pagination($pagination,$this->url->get_acpmod_url(0,'&amp;site={d}'));
	}
	
	public function get_location()
	{
		return array();
	}
}
?>