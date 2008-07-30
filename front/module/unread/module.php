<?php
/**
 * Contains the unread-module
 * 
 * @version			$Id$
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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_breadcrumb($locale->lang('unread_threads'),$url->get_url('unread'));
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$unread = FWS_Props::get()->unread();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$url = FWS_Props::get()->url();
		$tpl = FWS_Props::get()->tpl();

		$end = $cfg['threads_per_page'];
		$num = $unread->get_length();

		// collect topic-ids
		$tids = '';
		$unread_topics = $unread->get_unread_topics();
		if(is_array($unread_topics))
			$tids = implode(',',array_keys($unread_topics));

		// display the topics
		$topics = new BS_Front_Topics(
			$locale->lang('unread_threads'),
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
		$functions->add_pagination(
			$pagination,$url->get_url('unread','&amp;'.BS_URL_SITE.'={d}')
		);
		$action_type = '&amp;'.BS_URL_AT.'='.BS_ACTION_CHANGE_READ_STATUS;
		
		$tpl->add_variables(array(
			'target_url' => $url->get_url('redirect','&amp;'.BS_URL_LOC.'=topic_action'),
			'js_url' => $url->get_url(
				0,$action_type.'&amp;'.BS_URL_LOC.'=read&amp;'.BS_URL_MODE.'=topics&amp;'.BS_URL_ID.'=',
				'&amp;',true
			),
		));
	}
}
?>