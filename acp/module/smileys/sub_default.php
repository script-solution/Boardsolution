<?php
/**
 * Contains the default-submodule for smileys
 * 
 * @version			$Id$
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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->add_action(BS_ACP_ACTION_SWITCH_SMILEYS,'switch');
		$doc->add_action(BS_ACP_ACTION_DELETE_SMILEYS,'delete');
		$doc->add_action(BS_ACP_ACTION_IMPORT_SMILEYS,'import');
		$doc->add_action(BS_ACP_ACTION_RESORT_SMILEYS,'resort');
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		if($input->isset_var('delete','post'))
		{
			$site = $input->get_var('site','get',PLIB_Input::ID);
			$ids = $input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_smileys()->get_by_ids($ids) as $smiley)
				$names[] = $smiley['smiley_path'];
			$namelist = PLIB_StringHelper::get_enum($names,$locale->lang('and'));
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$url->get_acpmod_url(0,
					'&amp;at='.BS_ACP_ACTION_DELETE_SMILEYS.'&amp;ids='.implode(',',$ids).'&amp;site='.$site
				),
				$url->get_acpmod_url(0,'&amp;site='.$site)
			);
		}
		
		$search = $input->get_var('search','get',PLIB_Input::STRING);
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
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'page' => $page,
			'import_url' => $url->get_acpmod_url(0,'&amp;at='.BS_ACP_ACTION_IMPORT_SMILEYS),
			'correct_sort_url' => $url->get_acpmod_url(0,'&amp;at='.BS_ACP_ACTION_RESORT_SMILEYS),
			'search_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));
		
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
						$switch_up_url = $url->get_acpmod_url(
							0,'&amp;at='.BS_ACP_ACTION_SWITCH_SMILEYS.'&amp;ids='.$data['id'].','.$prev['id']
								.'&amp;site='.$page
						);
					}
		
					$switch_down_url = '';
					if($i < $num - 1)
					{
						$next = &$rows[$i + 1];
						$switch_down_url = $url->get_acpmod_url(
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
		}

		$tpl->add_array('smileys',$smileys);
		$tpl->add_variables(array('total' => $c));
		
		$murl = $url->get_acpmod_url(0,'&amp;search='.$search.'&amp;site={d}');
		$functions->add_pagination($pagination,$murl);
	}
	
	/**
	 * Checks wether the given smiley-data matches the given keyword
	 *
	 * @param array $data the smiley-data
	 * @param string $search the search-keyword
	 * @return true if it matches
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