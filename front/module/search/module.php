<?php
/**
 * Contains the search-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The search-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_search extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($cfg['enable_search'] == 1 && $auth->has_global_permission('view_search'));
		
		$renderer->add_breadcrumb($locale->lang('search'),BS_URL::build_mod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		// display the search-form
		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$modes = array('default','user_posts','user_topics','topic','similar_topics');
		$mode = $input->correct_var(BS_URL_MODE,'get',FWS_Input::STRING,$modes,'default');
		$keywords = $input->get_var(BS_URL_KW,'get',FWS_Input::STRING);
		$usernames = $input->get_var(BS_URL_UN,'get',FWS_Input::STRING);
		
		$submitted = $input->isset_var('submit','post');
		if($mode != 'default' || $submitted || $id != null || $keywords != null || $usernames != null)
		{
			switch($mode)
			{
				case 'user_posts':
					$request = new BS_Front_Search_Request_UserPosts();
					break;
				case 'user_topics':
					$request = new BS_Front_Search_Request_UserTopics();
					break;
				case 'similar_topics':
					$request = new BS_Front_Search_Request_SimilarTopics();
					break;
				case 'topic':
					$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
					$request = new BS_Front_Search_Request_Topic($tid);
					break;
				default:
					$fids = $input->get_var(BS_URL_FID,'get',FWS_Input::STRING);
					$request = new BS_Front_Search_Request_Default($fids ? explode(',',$fids) : null);
					break;
			}
			
			$manager = new BS_Front_Search_Manager($id,$request);
			$result_num = count($manager->get_result_ids());
			$result = $request->get_result();
		
			$tpl->add_variables(array(
				'result_tpl' => $result !== null ? $result->get_template() : '',
				'result_num' => $result_num
			));
		}
		else
			$result_num = 0;
		
		if($result_num > 0)
			$manager->add_result();
		else
		{
			$order_vals = array('date','topic_name','topic_type','replies','views','relevance');
			$order = $input->correct_var('order','post',FWS_Input::STRING,$order_vals,'relevance');
			$ad = $input->correct_var('ad','post',FWS_Input::STRING,array('ASC','DESC'),'DESC');
			$limit_vals = array(10,25,50,100,250,500);
			$limit = $input->correct_var('limit','post',FWS_Input::INTEGER,$limit_vals,250);

			// the condition is true if we should have been displayed the result but
			// there has occurred an error
			$form = $this->request_formular(false,false);
			$form->set_condition($mode != 'default' || $submitted || $id != null);
			
			$keyword_mode_options = array(
				'and' => $locale->lang('keyword_mode_and'),
				'or' => $locale->lang('keyword_mode_or')
			);

			$order_options = array(
				'relevance' => $locale->lang('relevance'),
				'date' => $locale->lang('date'),
				'topic_name' => $locale->lang('name'),
				'topic_type' => $locale->lang('threadtype'),
				'replies' => $locale->lang('posts'),
				'views' => $locale->lang('hits')
			);

			$ad_options = array(
				'DESC' => $locale->lang('descending'),
				'ASC' => $locale->lang('ascending')
			);

			$limit_options = array(
				10 => 10,
				25 => 25,
				50 => 50,
				100 => 100,
				250 => 250,
				500 => 500
			);

			$result_type_options = array(
				'posts' => $locale->lang('posts'),
				'topics' => $locale->lang('threads')
			);

			$keyword = stripslashes($input->get_var('keyword','post',FWS_Input::STRING));
			$username = stripslashes($input->get_var('un','post',FWS_Input::STRING));
			
			$selection = $input->get_var('fid','post');
			$forum_combo = BS_ForumUtils::get_recursive_forum_combo(
				'fid[]',$selection === null ? 0 : $selection,-1,true,true
			);
			
			$faq = BS_URL::get_mod_url('faq');
			$faq->set_anchor('f_9');
			
			$tpl->add_variables(array(
				'action_param' => BS_URL_ACTION,
				'search_explain_keyword' => sprintf(
					$locale->lang('search_explain_keyword'),$faq->to_url(),BS_SEARCH_MIN_KEYWORD_LEN
				),
				'search_explain_user' => sprintf($locale->lang('search_explain_user'),$faq->to_url()),
				'target_url' => BS_URL::build_mod_url(),
				'keyword' => $keyword,
				'keyword_mode_options' => $keyword_mode_options,
				'keyword_mode' => 'and',
				'order_options' => $order_options,
				'order' => $order,
				'ad_options' => $ad_options,
				'ad' => $ad,
				'limit_options' => $limit_options,
				'limit' => $limit,
				'result_type_options' => $result_type_options,
				'user_name' => $username,
				'forum_combo' => $forum_combo
			));
		}
	}
}
?>