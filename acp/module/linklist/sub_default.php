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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACP_ACTION_DELETE_LINKS,'delete');
		$renderer->add_action(BS_ACP_ACTION_ACTIVATE_LINKS,array('activate',1));
		$renderer->add_action(BS_ACP_ACTION_DEACTIVATE_LINKS,array('activate',0));
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
		$site = $input->get_var('site','get',FWS_Input::INTEGER);
		
		// display delete info?
		$delete = $input->get_var('delete','post');
		$action_type = $input->get_var('action_type','post',FWS_Input::INTEGER);
		if(is_array($delete) && $action_type == -1)
		{
			$names = array();
			foreach(BS_DAO::get_links()->get_by_ids($delete) as $data)
				$names[] = $data['link_url'];
			$namelist = FWS_StringHelper::get_enum($names,$locale->lang('and'));
			
			$yurl = BS_URL::get_acpsub_url();
			$yurl->set('at',BS_ACP_ACTION_DELETE_LINKS);
			$yurl->set('ids',implode(',',$delete));
			$yurl->set('site',$site);
			
			$nurl = BS_URL::get_acpsub_url();
			$nurl->set('site',$site);
			
			$functions->add_delete_message(
				sprintf($locale->lang('delete_message'),$namelist),
				$yurl->to_url(),
				$nurl->to_url()
			);
		}
		
		$search = $input->get_var('search','get',FWS_Input::STRING);
		if($search != '')
			$num = BS_DAO::get_links()->get_count_by_keyword($search);
		else
			$num = BS_DAO::get_links()->get_count();
		$end = 15;

		$order = $input->correct_var(BS_URL_ORDER,'get',FWS_Input::STRING,
			array('url','category','clicks','date','act'),'date');
		$ad = $input->correct_var(BS_URL_AD,'get',FWS_Input::STRING,array('ASC','DESC'),'DESC');
		
		$baseurl = BS_URL::get_acpmod_url();
		$baseurl->set('search',$search);
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$orderurl = clone $baseurl;
		$tpl->add_variables(array(
			'col_url' => BS_ACP_Utils::get_order_column(
				$locale->lang('url'),'url','ASC',$order,$orderurl
			),
			'col_category' => BS_ACP_Utils::get_order_column(
				$locale->lang('category'),'category','ASC',$order,$orderurl
			),
			'col_klicks' => BS_ACP_Utils::get_order_column(
				$locale->lang('clicks'),'clicks','DESC',$order,$orderurl
			),
			'col_added' => BS_ACP_Utils::get_order_column(
				$locale->lang('added'),'date','DESC',$order,$orderurl
			),
			'col_activated' => BS_ACP_Utils::get_order_column(
				$locale->lang('enabled'),'act','ASC',$order,$orderurl
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
		
		$enable_bbcode = BS_PostingUtils::get_message_option('enable_bbcode','desc');
		$enable_smileys = BS_PostingUtils::get_message_option('enable_smileys','desc');

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
			$link_date = FWS_Date::get_date($data['link_date'],false);
			$link_rating = $functions->get_link_rating($data['vote_points'],$data['votes'],0,1);
		
			$bbcode = new BS_BBCode_Parser($data['link_desc'],'desc',$enable_bbcode,$enable_smileys);
			
			$link_url = FWS_StringHelper::get_limited_string($data['link_url'],25);
			$user = BS_ACP_Utils::get_userlink($data['user_id'],$data['user_name']);
			
			$links[] = array(
				'id' => $data['id'],
				'url' => $link_url['displayed'],
				'url_complete' => $link_url['complete'],
				'category' => $data['category'],
				'clicks' => $data['clicks'],
				'details_image' => FWS_Path::client_app().'acp/images/crossclosed.gif',
				'rating' => $link_rating,
				'date' => $link_date.', '.$user,
				'activated' => BS_ACP_Utils::get_yesno($data['active'],true),
				'description' => $bbcode->get_message_for_output()
			);
		}

		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_array('links',$links);
		$tpl->add_variables(array(
			'at_activate' => BS_ACP_ACTION_ACTIVATE_LINKS,
			'at_deactivate' => BS_ACP_ACTION_DEACTIVATE_LINKS,
			'search_url' => $input->get_var('PHP_SELF','server',FWS_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));
		
		$baseurl->set('order',$order);
		$baseurl->set('ad',$ad);
		$pagination->populate_tpl($baseurl);
	}
}
?>