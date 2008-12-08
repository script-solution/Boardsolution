<?php
/**
 * Contains the print-popup-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Displays the print-popup
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_print extends BS_Front_Module
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
		$input = FWS_Props::get()->input();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_template('popup_print.htm');
		$renderer->set_show_headline(false);
		$renderer->set_show_bottom(false);
		
		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		
		$this->add_loc_forum_path($fid);
		$this->add_loc_topic();
		
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_FID,$fid);
		$url->set(BS_URL_TID,$tid);
		$renderer->add_breadcrumb($locale->lang('print_topic'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$auth = FWS_Props::get()->auth();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();

		$fid = $input->get_var(BS_URL_FID,'get',FWS_Input::ID);
		$tid = $input->get_var(BS_URL_TID,'get',FWS_Input::ID);
		
		// are the parameters valid?
		if($fid == null || $tid == null)
		{
			$this->report_error();
			return;
		}
		
		// check if the topic exists
		$topic_data = BS_Front_TopicFactory::get_current_topic();
		if($topic_data === null)
		{
			$this->report_error(FWS_Document_Messages::ERROR,$locale->lang('thread_not_found'));
			return;
		}
		
		// check if the user is allowed to view this topic
		if(!$auth->has_access_to_intern_forum($fid))
		{
			$this->report_error();
			return;
		}
		
		// generate the location
		$topic_name = $topic_data['name'];
		$forum_path = BS_ForumUtils::get_forum_path(0,false);
		$add_location = strip_tags($forum_path).' &raquo; '.$topic_name.' &raquo; ';
		$add_location .= $locale->lang('printview_title');
		
		// change some values in the header-template
		$theme = $user->get_theme();
		$tpl->set_template('inc_popup_header.htm');
		$tpl->add_variables(array(
			'css_file' => FWS_Path::client_app().'themes/'.$theme.'/templates/popup_print_style.css',
			'page_title' => $cfg['forum_title'].' &raquo; '.strip_tags($add_location),
		));
		$tpl->restore_template();
		
		
		$tpl->add_variables(array(
			'board_title' => $cfg['forum_title'],
			'location' => $add_location,
			'show_poll' => $topic_data['type'] > 0,
			'show_event' => $topic_data['type'] == -1,
		));
		
		// display the poll-result
		if($topic_data['type'] > 0)
		{
			$tpl->add_variables(array(
				'question' => $topic_data['name'],
			));
			
			$total_votes = 0;
			$poll_options = array();
			$optionlist = BS_DAO::get_polls()->get_options_by_id($topic_data["type"],'option_value','DESC');
			foreach($optionlist as $pdata)
			{
				$poll_options[] = $pdata;
				$total_votes += $pdata['option_value'];
			}
		
			$tploptions = array();
			foreach($poll_options as $pdata)
			{
				if($pdata['option_value'] == 0)
					$percent = 0;
				else
					$percent = @round(100 / ($total_votes / $pdata['option_value']),2);
		
				$img_percent = round($percent,0);
		
				$tploptions[] = array(
					'multichoice' => $pdata['multichoice'],
					'show_results' => true,
					'option_name' => $pdata['option_name'],
					'option_value' => $pdata['option_value'],
					'percent' => $percent,
					'img_width' => ($img_percent > 0 ? '100%' : '0px'),
					'img_percent' => $img_percent,
					'img_remaining_percent' => 100 - $img_percent,
				);
			}
			
			$tpl->add_array('poll_options',$tploptions);
		}
		// display the event-data
		else if($topic_data['type'] == -1)
		{
			$edata = BS_DAO::get_events()->get_by_topic_id($tid);
		
			if($edata['event_end'] == 0)
				$event_end = 'open';
			else
				$event_end = FWS_Date::get_date($edata['event_end'],true,false);
			
			$tpl->add_variables(array(
				'event_title' => $topic_data['name']
			));
			
			$tpl->set_template('inc_event.htm');
			$tpl->add_variables(array(
				'location' => $edata['event_location'],
				'event_begin' => FWS_Date::get_date($edata['event_begin'],true,false),
				'event_end' => $event_end,
				'description' => nl2br($edata['description']),
				'show_announcements' => $edata['max_announcements'] >= 0
			));
		
			if($edata['max_announcements'] >= 0)
			{
				$timeout_date = ($edata['timeout'] == 0) ? $edata['event_begin'] : $edata['timeout'];
				
				$event = new BS_Event($edata);
				$tpl->add_variables(array(
					'timeout' => FWS_Date::get_date($timeout_date,true,false),
					'can_leave' => false,
					'can_announce' => false,
					'announcement_list' => $event->get_announcement_list(false),
					'max_announcements' => $edata['max_announcements'],
					'total_announcements' => $event->get_count()
				));
			}
			$tpl->restore_template();
		}
		
		// display the topic-type
		if($topic_data['type'] == - 1)
			$thread_type = $locale->lang('event');
		else if($topic_data['type'] == 0)
			$thread_type = $locale->lang('thread');
		else
			$thread_type = $locale->lang('poll');
		
		$tpl->add_variables(array(
			'thread_type' => $thread_type
		));
		
		$posts = array();
		$postcon = new BS_Front_Post_Container(
			$fid,$tid,null,null,'p.id '.BS_PostingUtils::get_posts_order()
		);
		foreach($postcon->get_posts() as $post)
		{
			/* @var $post BS_Front_Post_Data */
			$posts[] = array(
				'user_name' => $post->get_username(false),
				'date' => FWS_Date::get_date($post->get_field('post_time'),true),
				'text' => $post->get_post_text(false,false,false,true)
			);
		}
		
		$tpl->add_array('posts',$posts);
	}
}
?>