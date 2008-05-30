<?php
/**
 * Contains the search-module
 * 
 * @version			$Id: module_search.php 705 2008-05-15 10:14:58Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The search-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_search extends BS_Front_Module
{
	public function run()
	{
		// display the search-form
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		$modes = array('default','user_posts','user_topics','topic','similar_topics');
		$mode = $this->input->correct_var(BS_URL_MODE,'get',PLIB_Input::STRING,$modes,'default');
		
		$submitted = $this->input->isset_var('submit','post');
		if($mode != 'default' || $submitted || $id != null)
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
					$request = new BS_Front_Search_Request_Topic();
					break;
				default:
					$request = new BS_Front_Search_Request_Default();
					break;
			}
			
			$manager = new BS_Front_Search_Manager($id,$request);
			$result_num = count($manager->get_result_ids());
			$result = $request->get_result();
		
			$this->tpl->add_variables(array(
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
			$order_vals = array('lastpost','topic_name','topic_type','replies','views');
			$order = $this->input->correct_var('order','post',PLIB_Input::STRING,$order_vals,'lastpost');
			$ad = $this->input->correct_var('ad','post',PLIB_Input::STRING,array('ASC','DESC'),'DESC');
			$limit_vals = array(10,25,50,100,250,500);
			$limit = $this->input->correct_var('limit','post',PLIB_Input::INTEGER,$limit_vals,250);

			// the condition is true if we should have been displayed the result but
			// there has occurred an error
			$form = $this->_request_formular(false,false);
			$form->set_condition($mode != 'default' || $submitted || $id != null);
			
			$keyword_mode_options = array(
				'and' => $this->locale->lang('keyword_mode_and'),
				'or' => $this->locale->lang('keyword_mode_or')
			);

			$order_options = array(
				'lastpost' => $this->locale->lang('date'),
				'topic_name' => $this->locale->lang('name'),
				'topic_type' => $this->locale->lang('threadtype'),
				'replies' => $this->locale->lang('posts'),
				'views' => $this->locale->lang('hits')
			);

			$ad_options = array(
				'DESC' => $this->locale->lang('descending'),
				'ASC' => $this->locale->lang('ascending')
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
				'topics' => $this->locale->lang('threads'),
				'posts' => $this->locale->lang('posts')
			);

			$keyword = stripslashes($this->input->get_var('keyword','post',PLIB_Input::STRING));
			$username = stripslashes($this->input->get_var('un','post',PLIB_Input::STRING));
			
			$selection = $this->input->get_var('fid','post');
			$forum_combo = BS_ForumUtils::get_instance()->get_recursive_forum_combo(
				'fid[]',$selection,-1,true,true
			);
			
			$this->tpl->add_variables(array(
				'action_param' => BS_URL_ACTION,
				'search_explain_keyword' => sprintf(
					$this->locale->lang('search_explain_keyword'),
					$this->url->get_url('faq').'#f_9',
					BS_SEARCH_MIN_KEYWORD_LEN
				),
				'search_explain_user' => sprintf(
					$this->locale->lang('search_explain_user'),$this->url->get_url('faq').'#f_9'
				),
				'target_url' => $this->url->get_url(0),
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

	public function get_location()
	{
		return array($this->locale->lang('search') => $this->url->get_url('search'));
	}

	public function has_access()
	{
		return $this->cfg['enable_search'] == 1 && $this->auth->has_global_permission('view_search');
	}
}
?>