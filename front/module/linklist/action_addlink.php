<?php
/**
 * Contains the add-link-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The add-link-action
 *
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_linklist_addlink extends BS_Front_Action_Base
{
	function perform_action()
	{
		// nothing to do?
		if(!$this->input->isset_var('submit','post'))
			return '';

		// check if the user has permission to add a link
		if($this->cfg['enable_linklist'] == 0 || !$this->auth->has_global_permission('add_new_link'))
			return 'The linklist is disabled or you have no permission to add links';

		// check for spam
		$time = time();
		$spam_linkadd_on = $this->auth->is_ipblock_enabled('spam_linkadd');
		if($spam_linkadd_on)
		{
			if($this->ips->entry_exists('linkadd'))
				return 'linkipsperre';
		}

		// grab input-params
		$link = $this->input->get_var('link_url','post',PLIB_Input::STRING);
		$new_category = $this->input->get_var('new_category','post',PLIB_Input::STRING);
		$category = $this->input->get_var('link_category','post',PLIB_Input::STRING);

		// check if the link exists
		$link = PLIB_StringHelper::correct_homepage($link);
		if(BS_DAO::get_links()->url_exists($link))
			return 'linkschoneingefuegt';

		if(trim($link) == '')
			return 'fillallfields';

		$post_text = $this->input->get_var('text','post',PLIB_Input::STRING);
		$text = '';
		$error = BS_PostingUtils::get_instance()->prepare_message_for_db($text,$post_text,'lnkdesc');
		if($error != '')
			return $error;

		// insert the link into the database
		$sql_category = ($new_category != '') ? $new_category : $category;
		$active = ($this->cfg['linklist_activate_links'] == 1 && !$this->user->is_admin()) ? 0 : 1;

		$fields = array(
			'link_url' => $link,
			'category' => $sql_category,
			'link_desc' => $text,
			'link_desc_posted' => $post_text,
			'link_date' => $time,
			'user_id' => $this->user->get_user_id(),
			'active' => $active
		);
		BS_DAO::get_links()->create($fields);

		// make the ip-entry, if necessary
		$this->ips->add_entry('linkadd');

		// write PM's to the admins if enabled
		if($this->cfg['get_email_new_link'] == 1 && $active == 0)
		{
			$this->locale->add_language_file('email');

			$post_title = addslashes(sprintf(
				$this->locale->lang('link_email_title'),$this->cfg['forum_title']
			));
			$post_text = addslashes(sprintf(
				$this->locale->lang('link_email_text'),$this->cfg['forum_title'],$this->cfg['board_url']
			));
			$email = $this->functions->get_mailer('',$post_title,$post_text);

			foreach(BS_DAO::get_user()->get_users_by_groups(array(BS_STATUS_ADMIN)) as $adata)
			{
				$email->set_recipient($adata['user_email']);
				$email->send_mail();
			}
		}

		$this->set_action_performed(true);
		$this->add_link($this->locale->lang('back'),$this->url->get_url('linklist'));
		if($active == 1)
			$this->set_success_msg($this->locale->lang('success_'.BS_ACTION_ADD_LINK.'_activated'));
		else
			$this->set_success_msg($this->locale->lang('success_'.BS_ACTION_ADD_LINK.'_not_activated'));

		return '';
	}
}
?>