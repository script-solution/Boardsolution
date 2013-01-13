<?php
/**
 * Contains the default-submodule for smileys
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_SWITCH_SMILEYS,'switch');
		$renderer->add_action(BS_ACP_ACTION_DELETE_SMILEYS,'delete');
		$renderer->add_action(BS_ACP_ACTION_IMPORT_SMILEYS,'import');
		$renderer->add_action(BS_ACP_ACTION_RESORT_SMILEYS,'resort');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();
		
		$site = $input->get_var('site','get',FWS_Input::ID);
		
		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_smileys()->get_by_ids($ids) as $smiley)
				$names[] = $smiley['smiley_path'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$yurl = BS_URL::get_acpsub_url();
			$yurl->set('at',BS_ACP_ACTION_DELETE_SMILEYS);
			$yurl->set('ids',implode(',',$ids));
			$yurl->set('site',$site);
			
			$nurl = BS_URL::get_acpsub_url();
			$nurl->set('site',$site);
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),$yurl->to_url(),$nurl->to_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		$num = BS_DAO::get_smileys()->get_count();
		$matches = array();
		if($num)
		{
			// collect rows
			$rows = BS_DAO::get_smileys()->get_list();
			for($i = 0,$len = count($rows);$i < $len;$i++)
			{
				// skip this smiley?
				if($search && !$this->_matches($rows[$i],$search))
					$num--;
				else
					$matches[$i] = true;
			}
		}
		
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		$page = $pagination->get_page();
		
		$url = BS_URL::get_acpsub_url();
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'page' => $page,
			'import_url' => $url->set('at',BS_ACP_ACTION_IMPORT_SMILEYS)->to_url(),
			'correct_sort_url' => $url->set('at',BS_ACP_ACTION_RESORT_SMILEYS)->to_url(),
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => $search
		));
		
		$switchurl = BS_URL::get_acpsub_url();
		$switchurl->set('at',BS_ACP_ACTION_SWITCH_SMILEYS);
		$switchurl->set('site',$site);
		
		$smileys = array();
		$c = 0;
		if($num)
		{
			$start = $page > 1 ? (($page - 1) * $end) : 0;
			$loop_end = count($rows);
			for($i = 0;$i < $loop_end;$i++)
			{
				$data = &$rows[$i];
				
				// skip this smiley?
				if(!isset($matches[$i]))
					continue;
				
				$c++;
				if($c >= $start && $c < $start + $end)
				{
					$switch_up_url = '';
					if($i > 0)
					{
						$prev = &$rows[$i - 1];
						$switch_up_url = $switchurl->set('ids',$data['id'].','.$prev['id'])->to_url();
					}
		
					$switch_down_url = '';
					if($i < $num - 1)
					{
						$next = &$rows[$i + 1];
						$switch_down_url = $switchurl->set('ids',$next['id'].','.$data['id'])->to_url();
					}
					
					$smileys[] = array(
						'id' => $data['id'],
						'primary_code' => $data['primary_code'],
						'secondary_code' => $data['secondary_code'],
						'smiley_path' => $data['smiley_path'],
						'sort_key' => $data['sort_key'],
						'is_base' => BS_ACP_Utils::get_yesno($data['is_base']),
						'switch_up_url' => $switch_up_url,
						'switch_down_url' => $switch_down_url,
						'show_up' => $i > 0,
						'show_down' => $i < $num - 1
					);
				}
			}
		}

		$tpl->add_variable_ref('smileys',$smileys);
		$tpl->add_variables(array('total' => $c));
		
		$murl = BS_URL::get_acpmod_url();
		$murl->set('search',$search);
		$pagination->populate_tpl($murl);
	}
	
	/**
	 * Checks wether the given smiley-data matches the given keyword
	 *
	 * @param array $data the smiley-data
	 * @param string $search the search-keyword
	 * @return bool true if it matches
	 */
	private function _matches($data,$search)
	{
		if(stripos($data['primary_code'],$search) !== false)
			return true;
		if(stripos($data['secondary_code'],$search) !== false)
			return true;
		if(stripos($data['smiley_path'],$search) !== false)
			return true;
		return false;
	}
}
?>