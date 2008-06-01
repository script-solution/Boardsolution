<?php
/**
 * Contains the functions for the postings
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Some functions for the postings
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_PostingUtils extends PLIB_Singleton
{
	/**
	 * @return BS_PostingUtils the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * Calculates the number of pages for the given number of posts
	 *
	 * @param int $num the total number of entries
	 * @return int the number of pages
	 */
	public function get_post_pages($num)
	{
		$per_page = $this->cfg['posts_per_page'];
		$show = ($num % $per_page);
		$final = ($show == 0) ? ($num / $per_page) : (int)($num / $per_page) + 1;
		return $final;
	}
	
	/**
	 * determines the posts-order for the current user
	 *
	 * @return string the order: ASC, DESC
	 */
	public function get_posts_order()
	{
		if($this->user->is_loggedin())
			return $this->user->get_profile_val('posts_order');
	
		return $this->cfg['default_posts_order'];
	}
	
	/**
	 * creates a quote-tag from given author with the given content
	 *
	 * @param string $input the content of the quote
	 * @param string $username the username
	 * @return string the quote-tag
	 */
	public function quote_text($input,$username)
	{
		if($this->get_message_option('enable_bbcode'))
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
	 * @param string $quote_post_url the url to quote a post (e.g.: index.php?...&amp;BS_URL_ID=)
	 * @return string the html-code
	 */
	public function add_topic_review($topic_data,$show_quote = true,$quote_post_url = '')
	{
		$show_quote = $show_quote && BS_PostingUtils::get_instance()->get_message_option('enable_bbcode');
	
		$this->tpl->set_template('inc_message_review.htm',0);
		
		$review_title = sprintf($this->locale->lang('topic_review_title'),BS_TOPIC_REVIEW_POST_COUNT);
		$this->tpl->add_variables(array(
			'show_quote' => $show_quote,
			'field_id' => 'bbcode_area',
			'request_url' => $this->url->get_standalone_url(
				'front','ajax_quote_message','&id=%d%&type=post','&'
			),
			'topic_title' => $review_title.': "'.$topic_data['name'].'"',
			'limit_height' => false
		));
		
		$posts = array();
		$pagination = new BS_Pagination(BS_TOPIC_REVIEW_POST_COUNT,BS_TOPIC_REVIEW_POST_COUNT);
		$postcon = new BS_Front_Post_Container(
			0,$topic_data['id'],null,$pagination,'p.id DESC'
		);
		foreach($postcon->get_posts() as $post)
		{
			/* @var $post BS_Front_Post_Data */
			$posts[] = array(
				'subject' => '',
				'quote_post_url' => $quote_post_url.$post->get_field('bid'),
				'post_id' => $post->get_field('bid'),
				'user_name' => $post->get_username(),
				'date' => PLIB_Date::get_date($post->get_field('post_time'),true),
				'text' => $post->get_post_text(false,false,false)
			);
		}

		$this->tpl->add_array('messages',$posts);
		$this->tpl->restore_template();
	}
	
	/**
	 * returns the post-preview-text
	 *
	 * @param string $location the location: posts, sig, lnkdesc
	 * @param int $use_smileys -1 = grab from POST, if $location = 'posts', otherwise the value
	 * @param int $use_bbcode -1 = grab from POST, if $location = 'posts', otherwise the value
	 * @return array <code>array('text' => ...,'error' => ...)</code>
	 */
	public function get_post_preview_text($location = 'posts',$use_smileys = -1,$use_bbcode = -1)
	{
		$post_text = $this->input->get_var('text','post',PLIB_Input::STRING);
		$text = '';
		$error = $this->prepare_message_for_db($text,$post_text,$location,$use_smileys,$use_bbcode);
		
		// any error? so break here
		if($error != '')
		{
			if($this->locale->contains_lang('error_'.$error))
				$message = $this->locale->lang('error_'.$error);
			else
				$message = $error;
			
			return array(
				'text' => '',
				'error' => $message
			);
		}
		
		$options = $this->get_message_options($location);
		if($location == 'posts')
		{
			if($use_bbcode === -1)
				$use_bbcode = $this->input->isset_var('use_bbcode','post') ? 1 : 0;
			if($use_smileys === -1)
				$use_smileys = $this->input->isset_var('use_smileys','post') ? 1 : 0;
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
	 * @param string $location the location: posts, sig, lnkdesc
	 * @param int $use_smileys -1 = grab from POST, if $location = 'posts', otherwise the value
	 * @param int $use_bbcode -1 = grab from POST, if $location = 'posts', otherwise the value
	 */
	public function add_post_preview($location = 'posts',$use_smileys = -1,$use_bbcode = -1)
	{
		$res = $this->get_post_preview_text($location,$use_smileys,$use_bbcode);
		
		if($res['error'])
		{
			$this->msgs->add_error($res['error']);
			return;
		}
	
		// show template
		$this->tpl->set_template('inc_post_preview.htm',0);
		$this->tpl->add_variables(array(
			'text' => $res['text']
		));
		$this->tpl->restore_template();
	}
	
	/**
	 * Returns the option with given name for the given location without the prefix
	 * 
	 * @param string $name the option-name
	 * @param string $location your location: posts, lnkdesc, sig
	 * @return mixed the value
	 */
	public function get_message_option($name,$location = 'posts')
	{
		switch($location)
		{
			case 'posts':
			case 'lnkdesc':
			case 'sig':
				$prefix = $location.'_';
				break;
			
			default:
				$prefix = 'posts_';
				break;
		}
		
		return $this->cfg[$prefix.$name];
	}
	
	/**
	 * Collects all message-options for the given location and returns them.
	 * The prefix of the options will not be in the result-array!
	 * 
	 * @param string $location your location: posts, lnkdesc, sig
	 * @return array all options with the corresponding values
	 */
	public function get_message_options($location = 'posts')
	{
		switch($location)
		{
			case 'posts':
			case 'lnkdesc':
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
			$options[$option] = $this->cfg['msgs_'.$option];
		foreach($indiv_options as $option)
			$options[$option] = $this->cfg[$prefix.$option];
		
		return $options;
	}
	
	/**
	 * Prepares the given text for the database for the given location
	 * 
	 * @param string $text will contain the text for the DB
	 * @param string $text_posted the posted text
	 * @param string $location the location: posts, sig, lnkdesc
	 * @param boolean $use_smileys -1 = grab from POST, if $location = 'posts', otherwise the value
	 * @param boolean $use_bbcode -1 = grab from POST, if $location = 'posts', otherwise the value
	 * @return string the error-message, if any, or an empty string
	 */
	public function prepare_message_for_db(&$text,$text_posted,$location = 'posts',$use_smileys = -1,
		$use_bbcode = -1)
	{
		$options = $this->get_message_options($location);
		$this->locale->add_language_file('messages');
		
		// check if the text is empty
		if($location != 'sig')
		{
			if(trim($text_posted) == '')
				return 'posttextleer';
		}
	
		// is the post short enough?
		if(PLIB_String::strlen($text_posted) > $options['max_length'])
			return 'maxpostlen';
	
		if($location == 'posts')
		{
			if($use_bbcode === -1)
				$use_bbcode = $this->input->isset_var('use_bbcode','post') ? 1 : 0;
			if($use_smileys === -1)
				$use_smileys = $this->input->isset_var('use_smileys','post') ? 1 : 0;
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
		$temp = $bbcode->get_message_for_db();
		
		// bbcode-error?
		if($bbcode->get_error_code() !== true)
		{
			list($pos,$err) = $bbcode->get_error_code();
			return sprintf(
				$this->locale->lang('error_bbcode_'.$err),
				PLIB_StringHelper::get_text_part($text_posted,$pos,20),
				$pos
			);
		}
	
		// not too many smileys and pictures?
		if($enable_smileys && $bbcode->get_number_of_smileys() > $options['max_smileys'])
		{
			if($options['max_smileys'] == 0)
				return 'no_smileys_in_msg_allowed';
			
			return sprintf($this->locale->lang('error_maxsmileys'),$options['max_smileys']);
		}
	
		if($enable_bbcode && $bbcode->get_number_of_images() > $options['max_images'])
		{
			if($options['max_images'] == 0)
				return 'no_imgs_in_msg_allowed';
			
			return sprintf($this->locale->lang('error_maxpics'),$options['max_images']);
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
	public function get_post_text(&$post_data,$highlight_keywords = null,
		$show_attachments = false,$show_signature = false,$show_edited_notice = false,
		$attachments = array(),$wordwrap_codes = false)
	{
		$this->tpl->set_template('inc_post_text.htm');
		
		// get post-text
		$enable_bbcode = $this->get_message_option('enable_bbcode') &&
			$post_data['use_bbcode'] == 1;
		$enable_smileys = $this->get_message_option('enable_smileys') &&
			$post_data['use_smileys'] == 1;
		$bbcode = new BS_BBCode_Parser(
			$post_data['text'],'posts',$enable_bbcode,$enable_smileys
		);
		$text = $bbcode->get_message_for_output($wordwrap_codes);
	
		// add the default font of the user
		$this->add_default_font($text,$post_data['default_font']);
	
		// highlight keywords?
	  if($highlight_keywords !== null)
	  {
	  	$kwhl = new PLIB_KeywordHighlighter($highlight_keywords,'<span class="bs_highlight">');
	  	$text = $kwhl->highlight($text);
	  }
		
		$this->tpl->add_variables(array(
			'text' => $text,
			'show_attachments' => $show_attachments && isset($attachments[$post_data['bid']]),
			'show_signature' => $show_signature && $this->cfg['enable_signatures'] == 1 &&
				$post_data['attach_signature'] == 1 && $post_data['bsignatur'] != '',
			'show_edit_notice' => $show_edited_notice && $post_data['edited_times'] > 0
		));
	
		// add attachments
	  if($show_attachments && isset($attachments[$post_data['bid']]))
	  {
	  	$this->tpl->set_template('inc_attachments_display.htm',0);
	  	
	  	$tplatt = array();
	  	for($i = 0;$i < count($attachments[$post_data['bid']]);$i++)
	    {
	      $attachment = $attachments[$post_data['bid']][$i];
	      $ext = PLIB_FileUtils::get_extension($attachment['attachment_path']);
	      $attachment_url = $this->url->get_standalone_url(
	      	'front','download','&amp;'.BS_URL_ID.'='.$attachment['id']
	      );
				$image_url = '';
				$image_title = '';
				$fileicon = '';
	
	      $is_image = $this->cfg['attachments_images_show'] == 1 &&
	      	($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif');
	      
	      if($is_image)
	      {
	      	list($att_width,$att_height) = explode('x',$this->cfg['attachments_images_size']);
	      	$params = '&amp;path='.$attachment['attachment_path'].'&amp;width=';
	      	$params .= $att_width.'&amp;height='.$att_height;
	        $params .= '&amp;method='.$this->cfg['attachments_images_resize_method'];
	        
	      	$image_url = $this->url->get_standalone_url('front','thumbnail',$params);
	        $image_title = sprintf($this->locale->lang('download_image'),
	        	basename($attachment['attachment_path']));
	        $attachment_name = '';
	      }
	      else
	      {
	      	$fileicon = $this->functions->get_attachment_icon($ext);
	      	$attachment_name = basename($attachment['attachment_path']);
	      }
	
				$tplatt[] = array(
					'is_image' => $is_image,
					'fileicon' => $fileicon,
					'image_url' => $image_url,
					'image_title' => $image_title,
					'attachment_url' => $attachment_url,
					'attachment_name' => $attachment_name,
					'attachment_size' => number_format($attachment['attachment_size'],0,',','.'),
					'downloads' => $attachment['downloads']
				);
	    }
	    
	    $this->tpl->add_array('attachments',$tplatt);
	    $this->tpl->restore_template();
	  }
	
		// add signature
	  if($show_signature && $this->cfg['enable_signatures'] == 1 &&
			 $post_data['attach_signature'] == 1 && $post_data['bsignatur'] != '')
	  {
	    $enable_smileys = $this->get_message_option('enable_smileys','sig');
			$enable_bbcode = $this->get_message_option('enable_bbcode','sig');
	    $bbcode = new BS_BBCode_Parser(
	    	$post_data['bsignatur'],'sig',$enable_bbcode,$enable_smileys
	    );
	    $signature = $bbcode->get_message_for_output();
	
	    $this->add_default_font($signature,$post_data['default_font']);
	    
	    $this->tpl->add_variables(array(
	    	'signature' => $signature
	   	));
	  }
	
		// show the edited-information if the post has been edited
	  if($show_edited_notice && $post_data['edited_times'] > 0)
	  {
	  	$user = BS_UserUtils::get_instance()->get_link(
	  		$post_data['edited_user'],$post_data['edited_user_name'],$post_data['edited_user_group'],
	  		false
	  	);
	    
	    $this->tpl->add_variables(array(
	    	'edited' => sprintf(
		    	$this->locale->lang('last_edited_by_user'),$post_data['edited_times'],
					PLIB_Date::get_date($post_data['edited_date']),$user
				)
	   	));
	  }
	
	  return $this->tpl->parse_template();
	}
	
	/**
	 * adds the given default-font to the input-text
	 *
	 * @param string $input a reference to the input-text
	 * @param string $default_font the font you want to use
	 */
	public function add_default_font(&$input,$default_font)
	{
		if($default_font !== 0)
		{
			$fonts = explode(',',$this->cfg['post_font_pool']);
			PLIB_Array_Utils::trim($fonts);
			
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
	public function get_experience_diagram($exppoints,$rank_data,$user_id)
	{
		$user_stats = '';
		if($this->cfg['post_stats_type'] != 'disabled')
		{
			$img_url = $this->url->get_standalone_url(
				'front','user_experience','&amp;'.BS_URL_ID.'='.$user_id
			);
			$faq_url = $this->url->get_url('faq').'#f_0';
			
			$user_stats = '<a href="'.$faq_url.'" style="cursor: help;">';
			$user_stats .= '<img src="'.$img_url.'" alt="" /></a>';
			
			if($this->cfg['post_stats_type'] == 'current_rank')
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
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>