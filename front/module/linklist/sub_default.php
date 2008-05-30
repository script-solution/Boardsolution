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
	public function get_actions()
	{
		return array(
			BS_ACTION_VOTE_LINK => 'votelink'
		);
	}
	
	public function run()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		
		$num = BS_DAO::get_links()->get_count(1);
		$end = BS_LINKLIST_LINKS_PER_PAGE;
		$pagination = new BS_Pagination($end,$num);
		
		$this->tpl->add_variables(array(
			'num' => $num
		));
		
		if($num > 0)
		{
			$enable_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys','lnkdesc');
			$enable_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode','lnkdesc');
			
			$linklist = BS_DAO::get_links()->get_all(1,'l.category','ASC',$pagination->get_start(),$end);
			$qry_num = count($linklist);
			if($qry_num)
			{
				$this->_request_formular(false,false);
				$vote_options = array(
					$this->locale->lang('please_choose'),
					'1 - '.$this->locale->lang('verygood'),
					'2 - '.$this->locale->lang('good'),
					'3 - '.$this->locale->lang('middle'),
					'4 - '.$this->locale->lang('acceptable'),
					'5 - '.$this->locale->lang('bad'),
					'6 - 	'.$this->locale->lang('verybad')
				);
				
				$links = array();
				$thiskat = '';
				foreach($linklist as $i => $data)
				{
					$bbcode = new BS_BBCode_Parser($data['link_desc'],'lnkdesc',$enable_bbcode,$enable_smileys);
					$description = $bbcode->get_message_for_output();
	
					if(PLIB_String::strlen($data['link_url']) > 35)
						$url = '<span title="'.$data['link_url'].'">'.PLIB_String::substr($data['link_url'],0,35).'...</span>';
					else
						$url = $data['link_url'];
	
					$redirect_url = $this->url->get_standalone_url(
						'front','linklist_redirect','&amp;'.BS_URL_ID.'='.$data['id']
					);
	
					$links[] = array(
						'id' => $data['id'],
						'can_vote' => $this->user->is_loggedin() &&
							!BS_UserUtils::get_instance()->user_voted_for_link($data['id']),
						'vote_options' => $vote_options,
						'show_category' => $thiskat != $data['category'],
						'category' => $data['category'],
						'index' => $i,
						'description' => $description,
						'link_url' => $url,
						'link_rating' => $this->functions->get_link_rating($data['vote_points'],$data['votes'],0),
						'clicks' => $data['clicks'],
						'user_name' => BS_UserUtils::get_instance()->get_link(
								$data['user_id'],$data['user_name'],$data['user_group']
						),
						'redirect_url' => $redirect_url,
						'link_date' => PLIB_Date::get_date($data['link_date'],false),
						'details_url' => $this->url->get_url(0,'&amp;'.BS_URL_ID.'='.$data['id']).'#details',
						'display' => ($id == $data['id']) ? 'block' : 'none'
					);
					
					$thiskat = $data['category'];
				}

				$this->tpl->add_array('links',$links,false);

				$this->functions->add_pagination(
					$pagination,$this->url->get_url('linklist','&amp;'.BS_URL_SITE.'={d}')
				);
			}
			else
				$this->msgs->add_notice($this->locale->lang('no_links_found'));
		}
		else
			$this->msgs->add_notice($this->locale->lang('no_links_activated'));
		
		$this->tpl->add_variables(array(
			'show_add_link' => $this->cfg['display_denied_options'] || $this->auth->has_global_permission('add_new_link'),
			'add_link_url' => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=add')
		));
	}
	
	public function get_location()
	{
		return array();
	}
}
?>