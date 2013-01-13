<?php
/**
 * Contains the functions for the postings
 * 
 * @package			Boardsolution
 * @subpackage	src
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
 * Some functions for the postings
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_PostingUtils extends FWS_UtilBase
{
	/**
	 * Calculates the number of pages for the given number of posts
	 *
	 * @param int $num the total number of entries
	 * @return int the number of pages
	 */
	public static function get_post_pages($num)
	{
		$cfg = FWS_Props::get()->cfg();

		$per_page = $cfg['posts_per_page'];
		$show = ($num % $per_page);
		$final = ($show == 0) ? ($num / $per_page) : (int)($num / $per_page) + 1;
		return $final;
	}
	
	/**
	 * determines the posts-order for the current user
	 *
	 * @return string the order: ASC, DESC
	 */
	public static function get_posts_order()
	{
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();

		if($user->is_loggedin())
			return $user->get_profile_val('posts_order');
	
		return $cfg['default_posts_order'];
	}
	
	/**
	 * creates a quote-tag from given author with the given content
	 *
	 * @param string $input the content of the quote
	 * @param string $username the username
	 * @return string the quote-tag
	 */
	public static function quote_text($input,$username)
	{
		if(self::get_message_option('enable_bbcode'))
		{
			// we have to replace the brackets to prevent problems in the bbcode-engine
			$username = str_replace('[','(',$username);
			$username = str_replace(']',')',$username);
			return '[QUOTE='.$username.']'.$input.'[/QUOTE]';
		}
	
		$text = '';
		$lines = explode("\n",$input);
		$num = count($lines);
		$i = 0;
		foreach($lines as $line)
		{
			$text .= '> '.$line;
			if($i < $num - 1)
				$text .= "\n";
			$i++;
		}
		return $text;
	}
	
	/**
	 * Builds the posts of the given topic in reverse order for the reply or post-editing
	 * 
	 * @param array $topic_data a reference to the topic-data
	 * @param boolean $show_quote do you want to show the quote-button?
	 * @param BS_URL $quote_url the url to quote a post
	 * @param int $number the number of the bbcode-area (default 1)
	 */
	public static function add_topic_review($topic_data,$show_quote = true,$quote_url = null,$number = 1)
	{
		if($quote_url !== null && !($quote_url instanceof BS_URL))
			FWS_Helper::def_error('instance','quote_url','BS_URL',$quote_url);
		
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		$show_quote = $show_quote && BS_PostingUtils::get_message_option('enable_bbcode');
	
		$tpl->set_template('inc_message_review.htm');
		
		$url = BS_URL::get_standalone_url('ajax_quote');
		$url->set('id','__ID__');
		$url->set('type','post');
		$url->set_separator('&');
		$review_title = sprintf($locale->lang('topic_review_title'),BS_TOPIC_REVIEW_POST_COUNT);
		$tpl->add_variables(array(
			'show_quote' => $show_quote,
			'field_id' => 'bbcode_area'.$number,
			'number' => $number,
			'request_url' => $url->to_url(),
			'topic_title' => $review_title.': "'.$topic_data['name'].'"',
			'limit_height' => false
		));
		
		$posts = array();
		// use FWS_Pagination here to set the page manually
		$pagination = new FWS_Pagination(BS_TOPIC_REVIEW_POST_COUNT,BS_TOPIC_REVIEW_POST_COUNT);
		$postcon = new BS_Front_Post_Container(
			0,$topic_data['id'],null,$pagination,'p.id DESC'
		);
		foreach($postcon->get_posts() as $post)
		{
			/* @var $post BS_Front_Post_Data */
			$pid = $post->get_field('bid');
			$posts[] = array(
				'subject' => '',
				'quote_post_url' => $quote_url === null ? '' : $quote_url->set(BS_URL_PID,$pid),
				'post_id' => $pid,
				'user_name' => $post->get_username(),
				'user_name_plain' => $post->get_username(false),
				'date' => FWS_Date::get_date($post->get_field('post_time'),true),
				'text' => $post->get_post_text(false,false,false)
			);
		}

		$tpl->add_variable_ref('messages',$posts);
		$tpl->restore_template();
	}
	
	/**
	 * returns the post-preview-text
	 *
	 * @param string $location the location: posts, sig, desc
	 * @param bool|int $use_smileys -1 = grab from POST, if $location = 'posts', otherwise the value
	 * @param bool|int $use_bbcode -1 = grab from POST, if $location = 'posts', otherwise the value
	 * @return array <code>array('text' => ...,'error' => ...)</code>
	 */
	public static function get_post_preview_text($location = 'posts',$use_smileys = -1,$use_bbcode = -1)
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$post_text = $input->get_var('text','post',FWS_Input::STRING);
		$text = '';
		$error = self::prepare_message_for_db(
			$text,$post_text,$location,$use_smileys,$use_bbcode
		);
		
		// any error? so break here
		if($error != '')
		{
			if($locale->contains_lang('error_'.$error))
				$message = $locale->lang('error_'.$error);
			else
				$message = $error;
			
			return array(
				'text' => '',
				'error' => $message
			);
		}
		
		$options = self::get_message_options($location);
		if($location == 'posts')
		{
			if($use_bbcode === -1)
				$use_bbcode = $input->isset_var('use_bbcode','post') ? 1 : 0;
			if($use_smileys === -1)
				$use_smileys = $input->isset_var('use_smileys','post') ? 1 : 0;
		}
		else
		{
			$use_bbcode = true;
			$use_smileys = true;
		}
	
		$enable_bbcode = $options['enable_bbcode'] == 1 && $use_bbcode;
		$enable_smileys = $options['enable_smileys'] == 1 && $use_smileys;
	
		$text = stripslashes($text);
		$bbcode = new BS_BBCode_Parser($text,$location,$enable_bbcode,$enable_smileys);
		
		return array(
			'text' => $bbcode->get_message_for_output(),
			'error' => ''
		);
	}
	
	/**
	 * Adds the preview of a post
	 *
	 * @param string $location the location: posts, sig, desc
	 * @param bool|int $use_smileys -1 = grab from POST, if $location = 'posts', otherwise the value
	 * @param bool|int $use_bbcode -1 = grab from POST, if $location = 'posts', otherwise the value
	 */
	public static function add_post_preview($location = 'posts',$use_smileys = -1,$use_bbcode = -1)
	{
		$msgs = FWS_Props::get()->msgs();
		$tpl = FWS_Props::get()->tpl();

		$res = self::get_post_preview_text($location,$use_smileys,$use_bbcode);
		
		if($res['error'])
		{
			$msgs->add_error($res['error']);
			return;
		}
	
		// show template
		$tpl->set_template('inc_post_preview.htm');
		$tpl->add_variables(array(
			'text' => $res['text'],
			'location' => $location
		));
		$tpl->restore_template();
	}
	
	/**
	 * Returns the option with given name for the given location without the prefix
	 * 
	 * @param string $name the option-name
	 * @param string $location your location: posts, desc, sig
	 * @return mixed the value
	 */
	public static function get_message_option($name,$location = 'posts')
	{
		$cfg = FWS_Props::get()->cfg();

		switch($location)
		{
			case 'posts':
			case 'desc':
			case 'sig':
				$prefix = $location.'_';
				break;
			
			default:
				$prefix = 'posts_';
				break;
		}
		
		return $cfg[$prefix.$name];
	}
	
	/**
	 * Collects all message-options for the given location and returns them.
	 * The prefix of the options will not be in the result-array!
	 * 
	 * @param string $location your location: posts, desc, sig
	 * @return array all options with the corresponding values
	 */
	public static function get_message_options($location = 'posts')
	{
		$cfg = FWS_Props::get()->cfg();

		switch($location)
		{
			case 'posts':
			case 'desc':
			case 'sig':
				$prefix = $location.'_';
				break;
			
			default:
				$prefix = 'posts_';
				break;
		}
		
		$def_options = array(
			'default_bbcode_mode','parse_urls','code_highlight','code_line_numbers','max_line_length'
		);
		$indiv_options = array(
			'enable_smileys','enable_bbcode','max_length','max_images','max_smileys','allowed_tags'
		);
		
		$options = array();
		
		foreach($def_options as $option)
			$options[$option] = $cfg['msgs_'.$option];
		foreach($indiv_options as $option)
			$options[$option] = $cfg[$prefix.$option];
		
		return $options;
	}
	
	/**
	 * Prepares the given text for the database for the given location
	 * 
	 * @param string $text will contain the text for the DB
	 * @param string $text_posted the posted text
	 * @param string $location the location: posts, sig, desc
	 * @param bool|int $use_smileys -1 = grab from POST, if $location = 'posts', otherwise the value
	 * @param bool|int $use_bbcode -1 = grab from POST, if $location = 'posts', otherwise the value
	 * @return string the error-message, if any, or an empty string
	 */
	public static function prepare_message_for_db(&$text,$text_posted,$location = 'posts',
		$use_smileys = -1,$use_bbcode = -1)
	{
		$locale = FWS_Props::get()->locale();
		$input = FWS_Props::get()->input();
		$msgs = FWS_Props::get()->msgs();

		$options = self::get_message_options($location);
		$locale->add_language_file('messages');
		
		// check if the text is empty
		if($location != 'sig')
		{
			if(trim($text_posted) == '')
				return 'posttextleer';
		}
	
		// is the post short enough?
		if(FWS_String::strlen($text_posted) > $options['max_length'])
			return 'maxpostlen';
	
		if($location == 'posts')
		{
			if($use_bbcode === -1)
				$use_bbcode = $input->isset_var('use_bbcode','post') ? 1 : 0;
			if($use_smileys === -1)
				$use_smileys = $input->isset_var('use_smileys','post') ? 1 : 0;
		}
		else
		{
			$use_bbcode = true;
			$use_smileys = true;
		}
		
		$enable_bbcode = $options['enable_bbcode'] == 1 && $use_bbcode;
		$enable_smileys = $options['enable_smileys'] == 1 && $use_smileys;
	
		// format message before inserting
		$bbcode = new BS_BBCode_Parser($text_posted,$location,$enable_bbcode,$enable_smileys);
		try
		{
			$temp = $bbcode->get_message_for_db();
		}
		catch(BS_BBCode_Exception $ex)
		{
			return $ex->getMessage();
		}
		
		// add highlight-limit warning
		if($bbcode->reached_highlighting_limit())
			$msgs->add_warning(sprintf($locale->lang('warning_reached_hl_limit'),BS_CODE_HIGHLIGHT_LIMIT));
	
		// not too many smileys and pictures?
		if($enable_smileys && $bbcode->get_number_of_smileys() > $options['max_smileys'])
		{
			if($options['max_smileys'] == 0)
				return 'no_smileys_in_msg_allowed';
			
			return sprintf($locale->lang('error_maxsmileys'),$options['max_smileys']);
		}
	
		if($enable_bbcode && $bbcode->get_number_of_images() > $options['max_images'])
		{
			if($options['max_images'] == 0)
				return 'no_imgs_in_msg_allowed';
			
			return sprintf($locale->lang('error_maxpics'),$options['max_images']);
		}
		
		$text = $temp;
		return '';
	}
	
	/**
	 * builds the post-text
	 *
	 * @param array $post_data the post-data
	 * @param array $highlight_keywords an array with the keywords to highlight or null
	 * @param boolean $show_attachments do you want to show the attachments?
	 * @param boolean $show_signature do you want to show the signature?
	 * @param boolean $show_edited_notice do you want to show the edited-notice?
	 * @param array $attachments the attachments if you want to display them
	 * @param boolean $wordwrap_codes do you want to perform a wordwrap in code-sections?
	 * @return string the text
	 */
	public static function get_post_text(&$post_data,$highlight_keywords = null,
		$show_attachments = false,$show_signature = false,$show_edited_notice = false,
		$attachments = array(),$wordwrap_codes = false)
	{
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();

		$tpl->set_template('inc_post_text.htm');
		
		// get post-text
		$enable_bbcode = self::get_message_option('enable_bbcode') &&
			$post_data['use_bbcode'] == 1;
		$enable_smileys = self::get_message_option('enable_smileys') &&
			$post_data['use_smileys'] == 1;
		$bbcode = new BS_BBCode_Parser(
			$post_data['text'],'posts',$enable_bbcode,$enable_smileys
		);
		$text = $bbcode->get_message_for_output($wordwrap_codes);
	
		// add the default font of the user
		self::add_default_font($text,$post_data['default_font']);
	
		// highlight keywords?
	  if($highlight_keywords !== null)
	  {
	  	$kwhl = new FWS_KeywordHighlighter($highlight_keywords,'<span class="bs_highlight">');
	  	$text = $kwhl->highlight($text);
	  }
		
		$tpl->add_variables(array(
			'text' => $text,
			'show_attachments' => $show_attachments && isset($attachments[$post_data['bid']]),
			'show_signature' => $show_signature && $cfg['enable_signatures'] == 1 &&
				$post_data['attach_signature'] == 1 && $post_data['bsignatur'] != '',
			'show_edit_notice' => $show_edited_notice && $post_data['edited_times'] > 0
		));
	
		// add attachments
	  if($show_attachments && isset($attachments[$post_data['bid']]))
	  {
	  	$tpl->set_template('inc_attachments_display.htm');
	  	
	  	$durl = BS_URL::get_standalone_url('download');
      list($att_width,$att_height) = explode('x',$cfg['attachments_images_size']);
      $turl = BS_URL::get_standalone_url('thumbnail');
	  	$turl->set('width',$att_width);
	  	$turl->set('height',$att_height);
	  	$turl->set('method',$cfg['attachments_images_resize_method']);
	  	
	  	$tplatt = array();
	  	for($i = 0;$i < count($attachments[$post_data['bid']]);$i++)
	    {
	      $attachment = $attachments[$post_data['bid']][$i];
	      $ext = FWS_FileUtils::get_extension($attachment['attachment_path']);
	      $attachment_url = $durl->set(BS_URL_ID,$attachment['id'])->to_url();
				$image_url = '';
				$image_title = '';
	
	      $is_image = $cfg['attachments_images_show'] == 1 &&
	      	($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif');
	      
	      if($is_image)
	      {
	        $turl->set('path',$attachment['attachment_path']);
	      	$image_url = $turl->to_url();
	        $image_title = sprintf($locale->lang('download_image'),
	        	basename($attachment['attachment_path']));
	      }
	
				$tplatt[] = array(
					'is_image' => $is_image,
					'fileicon' => $functions->get_attachment_icon($ext),
					'image_url' => $image_url,
					'image_title' => $image_title,
					'attachment_url' => $attachment_url,
					'attachment_name' => basename($attachment['attachment_path']),
					'attachment_size' => number_format($attachment['attachment_size'],0,',','.'),
					'downloads' => $attachment['downloads']
				);
	    }
	    
	    $tpl->add_variable_ref('attachments',$tplatt);
	    $tpl->restore_template();
	  }
	
		// add signature
	  if($show_signature && $cfg['enable_signatures'] == 1 &&
			 $post_data['attach_signature'] == 1 && $post_data['bsignatur'] != '')
	  {
	    $enable_smileys = self::get_message_option('enable_smileys','sig');
			$enable_bbcode = self::get_message_option('enable_bbcode','sig');
	    $bbcode = new BS_BBCode_Parser(
	    	$post_data['bsignatur'],'sig',$enable_bbcode,$enable_smileys
	    );
	    $signature = $bbcode->get_message_for_output();
	
	    self::add_default_font($signature,$post_data['default_font']);
	    
	    $tpl->add_variables(array(
	    	'signature' => $signature
	   	));
	  }
	
		// show the edited-information if the post has been edited
	  if($show_edited_notice && $post_data['edited_times'] > 0)
	  {
	  	if($post_data['edited_user'] > 0)
	  	{
		  	$user = BS_UserUtils::get_link(
		  		$post_data['edited_user'],$post_data['edited_user_name'],$post_data['edited_user_group'],
		  		false
		  	);
	  	}
	  	else
	  		$user = '<i>'.BS_ANONYMOUS_NAME.'</i>';
	    
	    $tpl->add_variables(array(
	    	'edited' => sprintf(
		    	$locale->lang('last_edited_by_user'),$post_data['edited_times'],
					FWS_Date::get_date($post_data['edited_date']),$user
				)
	   	));
	  }
	
	  return $tpl->parse_template();
	}
	
	/**
	 * adds the given default-font to the input-text
	 *
	 * @param string $input a reference to the input-text
	 * @param string $default_font the font you want to use
	 */
	public static function add_default_font(&$input,$default_font)
	{
		$cfg = FWS_Props::get()->cfg();

		if($default_font !== 0)
		{
			$fonts = explode(',',$cfg['post_font_pool']);
			FWS_Array_Utils::trim($fonts);
			
			if(in_array($default_font,$fonts))
				$input = '<div style="font-family: \''.$default_font.'\';">'.$input.'</div>';
		}
	}
	
	/**
	 * generates and returns the experience diagram for a user
	 *
	 * @param int $exppoints the number of points
	 * @param array $rank_data the rank-data-array
	 * @param int $user_id the id of the user
	 * @return string the experience-diagram
	 */
	public static function get_experience_diagram($exppoints,$rank_data,$user_id)
	{
		$cfg = FWS_Props::get()->cfg();
		$user_stats = '';
		if($cfg['post_stats_type'] != 'disabled')
		{
			$img_url = BS_URL::get_standalone_url('user_experience');
			$img_url->set(BS_URL_ID,$user_id);
			$faq_url = BS_URL::get_mod_url('faq');
			$faq_url->set_anchor('f_0');
			
			$user_stats = '<a href="'.$faq_url->to_url().'" style="cursor: help;">';
			$user_stats .= '<img src="'.$img_url->to_url().'" alt="" /></a>';
			
			if($cfg['post_stats_type'] == 'current_rank')
			{
				$r_start = $rank_data['post_from'];
				$r_end = $rank_data['post_to'];
				if($r_end - $r_start == 0)
					$percent = 0;
				else
					$percent = min(100,100 * (($exppoints - $r_start) / ($r_end - $r_start)));
				$percent = round($percent,1);
				$user_stats .= ' '.$percent.'% ('.$r_start.','.$exppoints.','.$r_end.')';
			}
		}
	
		return $user_stats;
	}
}
?>