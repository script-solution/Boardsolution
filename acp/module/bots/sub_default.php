<?php
/**
 * Contains the default-submodule for bots
 * 
 * @version			$Id: sub_default.php 765 2008-05-24 21:14:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default submodule for bots
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_bots_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_DELETE_BOTS => 'delete'
		);
	}
	
	public function run()
	{
		// display delete-message?
		if($this->input->isset_var('delete','post'))
		{
			$ids = $this->input->get_var('delete','post');
			$site = $this->input->get_var('site','get',PLIB_Input::ID);
			$names = $this->cache->get_cache('bots')->get_field_vals_of_keys($ids,'bot_name');
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_BOTS.'&amp;ids='.implode(',',$ids).'&amp;site='.$site
				),
				$this->url->get_acpmod_url(0,'&amp;site='.$site)
			);
		}
		
		$num = $this->cache->get_cache('bots')->get_element_count();
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		$site = $pagination->get_page();

		$row = 0;
		$bots = array();
		$start = $site > 1 ? (($site - 1) * $end) : 0;
		foreach($this->cache->get_cache('bots') as $data)
		{
			if($row >= $start + $end)
				break;

			if($row >= $start)
			{
				if($data['bot_ip_start'] == '')
					$ip_range = $this->locale->lang('notavailable');
				else if($data['bot_ip_start'] == $data['bot_ip_end'])
					$ip_range = $data['bot_ip_start'];
				else
					$ip_range = $data['bot_ip_start'].' ... '.$data['bot_ip_end'];
				
				$bots[] = array(
					'id' => $data['id'],
					'name' => $data['bot_name'],
					'match' => $data['bot_match'],
					'ip_range' => $ip_range,
					'access' => BS_ACP_Utils::get_instance()->get_yesno($data['bot_access'])
				);
			}

			$row++;
		}
		
		$this->tpl->add_array('bots',$bots);
		$this->tpl->add_variables(array(
			'site' => $site
		));
		$this->functions->add_pagination($pagination,$this->url->get_acpmod_url(0,'&amp;site={d}'));
	}
	
	public function get_location()
	{
		return array();
	}
}
?>