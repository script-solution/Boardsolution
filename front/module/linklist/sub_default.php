<?php
/**
 * Contains the default-linklist-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default submodule for the linklist
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_linklist_default extends BS_Front_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->add_action(BS_ACTION_VOTE_LINK,'votelink');
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$locale = PLIB_Props::get()->locale();
		$user = PLIB_Props::get()->user();
		$functions = PLIB_Props::get()->functions();
		$msgs = PLIB_Props::get()->msgs();
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		$url = PLIB_Props::get()->url();

		$id = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		
		$num = BS_DAO::get_links()->get_count(1);
		$end = BS_LINKLIST_LINKS_PER_PAGE;
		$pagination = new BS_Pagination($end,$num);
		
		$tpl->add_variables(array(
			'num' => $num
		));
		
		if($num > 0)
		{
			$enable_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys','desc');
			$enable_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode','desc');
			
			$linklist = BS_DAO::get_links()->get_list(1,'l.category','ASC',$pagination->get_start(),$end);
			$qry_num = count($linklist);
			if($qry_num)
			{
				$this->request_formular(false,false);
				$vote_options = array(
					$locale->lang('please_choose'),
					'1 - '.$locale->lang('verygood'),
					'2 - '.$locale->lang('good'),
					'3 - '.$locale->lang('middle'),
					'4 - '.$locale->lang('acceptable'),
					'5 - '.$locale->lang('bad'),
					'6 - 	'.$locale->lang('verybad')
				);
				
				$links = array();
				$thiskat = '';
				foreach($linklist as $i => $data)
				{
					$bbcode = new BS_BBCode_Parser($data['link_desc'],'desc',$enable_bbcode,$enable_smileys);
					$description = $bbcode->get_message_for_output();
	
					if(PLIB_String::strlen($data['link_url']) > 35)
						$murl = '<span title="'.$data['link_url'].'">'.PLIB_String::substr($data['link_url'],0,35).'...</span>';
					else
						$murl = $data['link_url'];
	
					$redirect_url = $url->get_url('linklist_redirect','&amp;'.BS_URL_ID.'='.$data['id']);
	
					$links[] = array(
						'id' => $data['id'],
						'can_vote' => $user->is_loggedin() &&
							!BS_UserUtils::get_instance()->user_voted_for_link($data['id']),
						'vote_options' => $vote_options,
						'show_category' => $thiskat != $data['category'],
						'category' => $data['category'],
						'index' => $i,
						'description' => $description,
						'link_url' => $murl,
						'link_rating' => $functions->get_link_rating($data['vote_points'],$data['votes'],0),
						'clicks' => $data['clicks'],
						'user_name' => BS_UserUtils::get_instance()->get_link(
								$data['user_id'],$data['user_name'],$data['user_group']
						),
						'redirect_url' => $redirect_url,
						'link_date' => PLIB_Date::get_date($data['link_date'],false),
						'details_url' => $url->get_url(0,'&amp;'.BS_URL_ID.'='.$data['id']).'#details',
						'display' => ($id == $data['id']) ? 'block' : 'none'
					);
					
					$thiskat = $data['category'];
				}

				$tpl->add_array('links',$links,false);

				$functions->add_pagination(
					$pagination,$url->get_url('linklist','&amp;'.BS_URL_SITE.'={d}')
				);
			}
			else
				$msgs->add_notice($locale->lang('no_links_found'));
		}
		else
			$msgs->add_notice($locale->lang('no_links_activated'));
		
		$tpl->add_variables(array(
			'show_add_link' => $cfg['display_denied_options'] || $auth->has_global_permission('add_new_link'),
			'add_link_url' => $url->get_url(0,'&amp;'.BS_URL_LOC.'=add')
		));
	}
}
?>