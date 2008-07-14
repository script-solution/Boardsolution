<?php
/**
 * Contains the default-submodule for the linklist
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default submodule for the linklist
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_linklist_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_DELETE_LINKS => 'delete',
			BS_ACP_ACTION_ACTIVATE_LINKS => array('activate',1),
			BS_ACP_ACTION_DEACTIVATE_LINKS => array('activate',0),
		);
	}
	
	public function run()
	{
		$site = $this->input->get_var('site','get',PLIB_Input::INTEGER);
		
		// display delete info?
		$delete = $this->input->get_var('delete','post');
		$action_type = $this->input->get_var('action_type','post',PLIB_Input::INTEGER);
		if(is_array($delete) && $action_type == -1)
		{
			$names = array();
			foreach(BS_DAO::get_links()->get_by_ids($delete) as $data)
				$names[] = $data['link_url'];
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_LINKS.'&amp;ids='.implode(',',$delete).'&amp;site='.$site
				),
				$this->url->get_acpmod_url(0,'&amp;site='.$site)
			);
		}
		
		$search = $this->input->get_var('search','get',PLIB_Input::STRING);
		if($search != '')
			$num = BS_DAO::get_links()->get_count_by_keyword($search);
		else
			$num = BS_DAO::get_links()->get_count();
		$end = 15;

		$order = $this->input->correct_var(BS_URL_ORDER,'get',PLIB_Input::STRING,
			array('url','category','clicks','date','act'),'date');
		$ad = $this->input->correct_var(BS_URL_AD,'get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');
		$url = $this->url->get_acpmod_url(0,'&amp;search='.$search.'&amp;');
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$this->tpl->add_variables(array(
			'col_url' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('url'),'url','ASC',$order,$url
			),
			'col_category' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('category'),'category','ASC',$order,$url
			),
			'col_klicks' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('clicks'),'clicks','DESC',$order,$url
			),
			'col_added' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('added'),'date','DESC',$order,$url
			),
			'col_activated' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('enabled'),'act','ASC',$order,$url
			),
		));
		
		switch($order)
		{
			case 'url':
				$sql_order = 'l.link_url';
				break;
			case 'category':
				$sql_order = 'l.category';
				break;
			case 'date':
				$sql_order = 'l.id';
				break;
			case 'clicks':
				$sql_order = 'l.clicks';
				break;
			case 'act':
				$sql_order = 'l.active';
				break;
		}
		
		$enable_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode','lnkdesc');
		$enable_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys','lnkdesc');

		if($search != '')
		{
			$list = BS_DAO::get_links()->get_list_by_keyword(
				$search,-1,$sql_order,$ad,$pagination->get_start(),$end
			);
		}
		else
			$list = BS_DAO::get_links()->get_list(-1,$sql_order,$ad,$pagination->get_start(),$end);
		
		$links = array();
		foreach($list as $data)
		{
			$link_date = PLIB_Date::get_date($data['link_date'],false);
			$link_rating = $this->functions->get_link_rating($data['vote_points'],$data['votes'],0,1,'');
		
			$bbcode = new BS_BBCode_Parser($data['link_desc'],'lnkdesc',$enable_bbcode,$enable_smileys);
			
			$link_url = PLIB_StringHelper::get_limited_string($data['link_url'],25);
			$user = BS_ACP_Utils::get_instance()->get_userlink($data['user_id'],$data['user_name']);
			
			$links[] = array(
				'id' => $data['id'],
				'url' => $link_url['displayed'],
				'url_complete' => $link_url['complete'],
				'category' => $data['category'],
				'clicks' => $data['clicks'],
				'details_image' => PLIB_Path::inner().'acp/images/crossclosed.gif',
				'rating' => $link_rating,
				'date' => $link_date.', '.$user,
				'activated' => BS_ACP_Utils::get_instance()->get_yesno($data['active'],true),
				'description' => $bbcode->get_message_for_output()
			);
		}

		$hidden = $this->input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$this->tpl->add_array('links',$links);
		$this->tpl->add_variables(array(
			'at_activate' => BS_ACP_ACTION_ACTIVATE_LINKS,
			'at_deactivate' => BS_ACP_ACTION_DEACTIVATE_LINKS,
			'search_url' => $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));
		
		$purl = $url.'order='.$order.'&amp;ad='.$ad.'&amp;site={d}';
		$this->functions->add_pagination($pagination,$purl);
	}
	
	public function get_location()
	{
		return array();
	}
}
?>