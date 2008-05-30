<?php
/**
 * Contains the standalone-class for the print-popup
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Displays the print-popup
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_print extends BS_Standalone
{
	public function get_template()
	{
		return 'popup_print.htm';
	}
	
	public function run()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		
		// are the parameters valid?
		if($fid == null || $tid == null)
		{
			$this->_report_error();
			return;
		}
		
		$topic_data = $this->cache->get_cache('topic')->current();
		
		// check if the topic exists
		if($topic_data['id'] == '')
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_ERROR,$this->locale->lang('thread_not_found'));
			return;
		}
		
		// check if the user is allowed to view this topic
		if(!$this->auth->has_access_to_intern_forum($fid))
		{
			$this->_report_error();
			return;
		}
		
		// generate the location
		$topic_name = $topic_data['name'];
		$forum_path = BS_ForumUtils::get_instance()->get_forum_path(0,false);
		$add_location = strip_tags($forum_path).' &raquo; '.$topic_name.' &raquo; ';
		$add_location .= $this->locale->lang('printview_title');
		
		// change some values in the header-template
		$theme = $this->user->get_theme();
		$this->tpl->set_template('inc_popup_header.htm');
		$this->tpl->add_variables(array(
			'css_file' => PLIB_Path::inner().'themes/'.$theme.'/templates/popup_print_style.css',
			'page_title' => $this->cfg['forum_title'].' &raquo; '.strip_tags($add_location),
		));
		$this->tpl->restore_template();
		
		
		$this->tpl->add_variables(array(
			'board_title' => $this->cfg['forum_title'],
			'location' => $add_location,
			'show_poll' => $topic_data['type'] > 0,
			'show_event' => $topic_data['type'] == -1,
		));
		
		// display the poll-result
		if($topic_data['type'] > 0)
		{
			$this->tpl->add_variables(array(
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
			
			$this->tpl->add_array('poll_options',$tploptions);
		}
		// display the event-data
		else if($topic_data['type'] == -1)
		{
			$edata = BS_DAO::get_events()->get_by_topic_id($tid);
		
			if($edata['event_end'] == 0)
				$event_end = 'open';
			else
				$event_end = PLIB_Date::get_date($edata['event_end'],true,false);
			
			$this->tpl->add_variables(array(
				'event_title' => $topic_data['name']
			));
			
			$this->tpl->set_template('inc_event.htm');
			$this->tpl->add_variables(array(
				'location' => $edata['event_location'],
				'event_begin' => PLIB_Date::get_date($edata['event_begin'],true,false),
				'event_end' => $event_end,
				'description' => nl2br($edata['description']),
				'show_announcements' => $edata['max_announcements'] >= 0
			));
		
			if($edata['max_announcements'] >= 0)
			{
				$timeout_date = ($edata['timeout'] == 0) ? $edata['event_begin'] : $edata['timeout'];
				
				$event = new BS_Event($edata);
				$this->tpl->add_variables(array(
					'timeout' => PLIB_Date::get_date($timeout_date,true,false),
					'can_leave' => false,
					'can_announce' => false,
					'announcement_list' => $event->get_announcement_list(false),
					'max_announcements' => $edata['max_announcements'],
					'total_announcements' => $event->get_count()
				));
			}
			$this->tpl->restore_template();
		}
		
		// display the topic-type
		if($topic_data['type'] == - 1)
			$thread_type = $this->locale->lang('event');
		else if($topic_data['type'] == 0)
			$thread_type = $this->locale->lang('thread');
		else
			$thread_type = $this->locale->lang('poll');
		
		$this->tpl->add_variables(array(
			'thread_type' => $thread_type
		));
		
		$posts = array();
		$postcon = new BS_Front_Post_Container(
			$fid,$tid,null,null,'p.id '.BS_PostingUtils::get_instance()->get_posts_order()
		);
		foreach($postcon->get_posts() as $post)
		{
			/* @var $post BS_Front_Post_Data */
			$posts[] = array(
				'user_name' => $post->get_username(false),
				'date' => PLIB_Date::get_date($post->get_field('post_time'),true),
				'text' => $post->get_post_text(false,false,false,true)
			);
		}
		
		$this->tpl->add_array('posts',$posts);
	}
}
?>