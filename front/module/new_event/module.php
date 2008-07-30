<?php
/**
 * Contains the new-event-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The new-event-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_new_event extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$auth = PLIB_Props::get()->auth();
		$cfg = PLIB_Props::get()->cfg();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($auth->has_current_forum_perm(BS_MODE_START_EVENT) &&
			$cfg['enable_events'] == 1);
		
		$renderer->add_action(BS_ACTION_START_EVENT,'default');

		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		
		$this->add_loc_forum_path($fid);
		$renderer->add_breadcrumb(
			$locale->lang('newevent'),
			$url->get_url('new_event','&amp;'.BS_URL_FID.'='.$fid)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$forums = PLIB_Props::get()->forums();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();

		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
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
			$this->report_error(PLIB_Document_Messages::ERROR,$locale->lang('forum_is_closed'));
			return;
		}
	
		$form = $this->request_formular(true,true);
	
		if($input->isset_var('preview','post'))
			BS_PostingUtils::get_instance()->add_post_preview();
		
		$loggedin = $user->is_loggedin();
		$subt_def = $loggedin ? $user->get_profile_val('default_email_notification') : 0;
		
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_START_EVENT,
			'open_end' => $form->get_checkbox_value('open_end',false),
			'timeout_type_begin' => $form->get_radio_value('timeout_type','begin',true),
			'timeout_type_self' => $form->get_radio_value('timeout_type','self',false),
			'target_url' => $url->get_url(0,'&amp;'.BS_URL_FID.'='.$fid),
			'subscribe_topic_def' => $subt_def,
			'enable_email_notification' => $cfg['enable_email_notification'] && $loggedin,
			'important_allowed' => $auth->has_current_forum_perm(BS_MODE_MARK_TOPICS_IMPORTANT),
			'back_url' => $url->get_topics_url($fid)
		));
		
		$pform = new BS_PostingForm($locale->lang('post').':');
		$pform->set_show_attachments(true,0,false,$input->isset_var('action_type','post'));
		$pform->set_show_options(true);
		$pform->add_form();
	}
}
?>