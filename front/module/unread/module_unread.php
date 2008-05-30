<?php
/**
 * Contains the unread-module
 * 
 * @version			$Id: module_unread.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The unread-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_unread extends BS_Front_Module
{
	public function run()
	{
		$end = $this->cfg['threads_per_page'];
		$num = $this->unread->get_length();

		// collect topic-ids
		$tids = '';
		$unread_topics = $this->unread->get_unread_topics();
		if(is_array($unread_topics))
			$tids = implode(',',array_keys($unread_topics));

		// display the topics
		$topics = new BS_Front_Topics(
			$this->locale->lang('unread_threads'),
			' t.id IN ('.$tids.') AND moved_tid = 0',
			'lastpost',
			'DESC',
			$end
		);
		$topics->set_total_topic_num($num);
		$topics->set_show_forum(true);
		$topics->set_middle_width(80);
		$topics->add_topics();
		
		$pagination = new BS_Pagination($end,$num);
		$this->functions->add_pagination(
			$pagination,$this->url->get_url('unread','&amp;'.BS_URL_SITE.'={d}')
		);
		$action_type = '&amp;'.BS_URL_AT.'='.BS_ACTION_CHANGE_READ_STATUS;
		
		$this->tpl->add_variables(array(
			'target_url' => $this->url->get_url('redirect','&amp;'.BS_URL_LOC.'=topic_action'),
			'js_url' => $this->url->get_url(
				0,$action_type.'&amp;'.BS_URL_LOC.'=read&amp;'.BS_URL_MODE.'=topics&amp;'.BS_URL_ID.'=',
				'&amp;',true
			),
		));
	}

	public function get_location()
	{
		return array($this->locale->lang('unread_threads') => $this->url->get_url('unread'));
	}

	public function has_access()
	{
		return $this->user->is_loggedin();
	}
}
?>