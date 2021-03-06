<?php
/**
 * Contains the default-linklist-submodule
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The default submodule for the linklist
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_linklist_default extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_ACTION_VOTE_LINK,'votelink');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$functions = FWS_Props::get()->functions();
		$msgs = FWS_Props::get()->msgs();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		
		$num = BS_DAO::get_links()->get_count(1);
		$end = BS_LINKLIST_LINKS_PER_PAGE;
		$pagination = new BS_Pagination($end,$num);
		
		$tpl->add_variables(array(
			'num' => $num
		));
		
		if($num > 0)
		{
			$enable_smileys = BS_PostingUtils::get_message_option('enable_smileys','desc');
			$enable_bbcode = BS_PostingUtils::get_message_option('enable_bbcode','desc');
			
			$linklist = BS_DAO::get_links()->get_list(1,'l.category','ASC',$pagination->get_start(),$end);
			$qry_num = count($linklist);
			if($qry_num)
			{
				$this->request_formular(false,false);
				$vote_options = array(
					$locale->lang('please_choose'),
					'1 &ndash; '.$locale->lang('verygood'),
					'2 &ndash; '.$locale->lang('good'),
					'3 &ndash; '.$locale->lang('middle'),
					'4 &ndash; '.$locale->lang('acceptable'),
					'5 &ndash; '.$locale->lang('bad'),
					'6 &ndash; '.$locale->lang('verybad')
				);
				
				$links = array();
				$thiskat = '';
				$durl = BS_URL::get_mod_url();
				$rurl = BS_URL::get_standalone_url('linklist_redirect');
				
				foreach($linklist as $i => $data)
				{
					$bbcode = new BS_BBCode_Parser($data['link_desc'],'desc',$enable_bbcode,$enable_smileys);
					$description = $bbcode->get_message_for_output();
	
					if(FWS_String::strlen($data['link_url']) > 35)
						$murl = '<span title="'.$data['link_url'].'">'.FWS_String::substr($data['link_url'],0,35).'&hellip;</span>';
					else
						$murl = $data['link_url'];
	
					$redirect_url = $rurl->set(BS_URL_ID,$data['id'])->to_url();
	
					$links[] = array(
						'id' => $data['id'],
						'can_vote' => $user->is_loggedin() &&
							!BS_UserUtils::user_voted_for_link($data['id']),
						'vote_options' => $vote_options,
						'show_category' => $thiskat != $data['category'],
						'category' => $data['category'],
						'index' => $i,
						'description' => $description,
						'link_url' => $murl,
						'link_rating' => $functions->get_link_rating($data['vote_points'],$data['votes'],false),
						'clicks' => $data['clicks'],
						'user_name' => BS_UserUtils::get_link(
								$data['user_id'],$data['user_name'],$data['user_group']
						),
						'redirect_url' => $redirect_url,
						'link_date' => FWS_Date::get_date($data['link_date'],false),
						'details_url' => $durl->set(BS_URL_ID,$data['id'])->to_url(),
						'display' => ($id == $data['id']) ? 'block' : 'none'
					);
					
					$thiskat = $data['category'];
				}

				$tpl->add_variable_ref('links',$links);

				$pagination->populate_tpl(BS_URL::get_mod_url('linklist'));
			}
			else
				$msgs->add_notice($locale->lang('no_links_found'));
		}
		else
			$msgs->add_notice($locale->lang('no_links_activated'));
		
		$tpl->add_variables(array(
			'show_add_link' => $cfg['display_denied_options'] || $auth->has_global_permission('add_new_link'),
			'add_link_url' => BS_URL::build_sub_url(0,'add')
		));
	}
}
?>