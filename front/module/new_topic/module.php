<?php
/**
 * Contains the new-topic-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The new-topic-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_new_topic extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$auth = FWS_Props::get()->auth();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($auth->has_current_forum_perm(BS_MODE_START_TOPIC));
		$renderer->add_action(BS_ACTION_START_TOPIC,'default');

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$this->add_loc_forum_path($fid);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$renderer->add_breadcrumb($locale->lang('newthread'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$forums = FWS_Props::get()->forums();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$auth = FWS_Props::get()->auth();
		$cfg = FWS_Props::get()->cfg();

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		if($fid == null)
		{
			$this->report_error();
			return;
		}
		
		// does the forum exist?
		$forum_data = $forums->get_node_data($fid);
		if($forum_data === null || $forum_data->get_forum_type() != 'contains_threads')
		{
			$this->report_error();
			return;
		}
		
		// forum closed?
		if(!$user->is_admin() && $forums->forum_is_closed($fid))
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('forum_is_closed'));
			return;
		}
		
		$form = $this->request_formular(true,true);
	
		if($input->isset_var('preview','post'))
			BS_PostingUtils::add_post_preview();
	
		$loggedin = $user->is_loggedin();
		$subt_def = $loggedin ? $user->get_profile_val('default_email_notification') : 0;
		$symbols = BS_TopicUtils::get_symbols($form);
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$tpl->add_variables(array(
			'important_allowed' => $auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT),
			'target_url' => $url->to_url(),
			'action_type' => BS_ACTION_START_TOPIC,
			'symbols' => $symbols,
			'subscribe_topic_def' => $subt_def,
			'enable_email_notification' => $cfg['enable_email_notification'] && $loggedin,
			'back_url' => BS_URL::build_topics_url($fid)
		));
		
		$pform = new BS_PostingForm($locale->lang('post').':');
		$pform->set_show_attachments(true,0,false,$input->isset_var('action_type','post'));
		$pform->set_show_options(true);
		$pform->add_form();
	}
}
?>