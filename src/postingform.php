<?php
/**
 * Contains the posting-form-class
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
 * The posting-form. That means the textfield, BBCode, smileys and attachment-upload.
 * <p>
 * You may use multiple instances on one page. If you do that call set_name_suffix() to
 * make sure that the formular-fields are named differently.
 * Note that the guest-fields are shown just once and an attachment-form can be used just
 * once, too!
 * You can use them simply by:
 * <code>{include "post_form.htm" #&lt;number&gt;}</code>
 * &lt;number&gt; starts with 1.
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_PostingForm extends FWS_Object
{
	/**
	 * The template-number to use
	 *
	 * @var int
	 */
	private static $number = 1;
	
	/**
	 * The attachment-settings
	 *
	 * @var array
	 */
	private $_attachments = array(
		'show' => false,
		'post_id' => 0,
		'db_attachments' => false,
		'post_attachments' => true
	);
	
	/**
	 * The title of the formular
	 *
	 * @var string
	 */
	private $_title;
	
	/**
	 * The message-type: posts, desc, sig, pm
	 *
	 * @var string
	 */
	private $_type;
	
	/**
	 * The text at the beginning
	 *
	 * @var string
	 */
	private $_text = '';
	
	/**
	 * In posts the user may disable smileys.
	 * True by default
	 *
	 * @var boolean
	 */
	private $_use_smileys = true;
	
	/**
	 * In posts the user may disable bbcode
	 * True by default
	 *
	 * @var boolean
	 */
	private $_use_bbcode = true;
	
	/**
	 * The height of the textarea
	 *
	 * @var integer
	 */
	private $_textarea_height = 250;
	
	/**
	 * Show the hide-bbcode/-smiley-checkboxes?
	 *
	 * @var boolean
	 */
	private $_show_options = false;
	
	/**
	 * The suffix for all formular-element-names
	 *
	 * @var string
	 */
	private $_name_suffix = '';
	
	/**
	 * The formular instance
	 *
	 * @var BS_HTML_Formular
	 */
	private $_form = null;
	
	/**
	 * constructor
	 * 
	 * @param string $title the title of the form (e.g. "Text")
	 * @param string $text the text which the textarea will contain at beginning
	 * @param string $type the type: 'posts','sig','desc','pm'
	 */
	public function __construct($title,$text = '',$type = 'posts')
	{
		parent::__construct();
		
		$this->_title = $title;
		$this->_text = $text;
		$this->_type = $type;
	}
	
	/**
	 * Sets wether to show attachments. Note that this enables attachments for all used post-forms
	 * in this request!
	 * 
	 * @param boolean $show show the form?
	 * @param int $post_id the id of the post (required if $db_attachments is enabled)
	 * @param boolean $db_attachments grab attachments from the given post-id from the database?
	 * @param boolean $post_attachments grab attachments from the $_POST-array?
	 */
	public function set_show_attachments($show,$post_id = 0,$db_attachments = false,
		$post_attachments = true)
	{
		$this->_attachments = array(
			'show' => $show,
			'post_id' => $post_id,
			'db_attachments' => $db_attachments,
			'post_attachments' => $post_attachments
		);
	}
	
	/**
	 * Sets the suffix for all formular-element-names. That means you will get:
	 * <ul>
	 * 	<li>"text&lt;suffix&gt;"</li>
	 * 	<li>"use_smileys&lt;suffix&gt;"</li>
	 * 	<li>"use_bbcode&lt;suffix&gt;"</li>
	 * </ul>
	 * Note that this does not affect the guest-fields. These will be shown just once!
	 *
	 * @param string $suffix the name suffix
	 */
	public function set_name_suffix($suffix)
	{
		$this->_name_suffix = $suffix;
	}
	
	/**
	 * Sets wether smileys should be used if enabled
	 * 
	 * @param boolean $smileys use smileys?
	 */
	public function set_use_smileys($smileys)
	{
		$this->_use_smileys = $smileys;
	}
	
	/**
	 * Sets wether bbcode should be used if enabled
	 * 
	 * @param boolean $bbcode use bbcode?
	 */
	public function set_use_bbcode($bbcode)
	{
		$this->_use_bbcode = $bbcode;
	}
	
	/**
	 * Sets the height of the textarea
	 * 
	 * @param string $height the new value in pixels
	 */
	public function set_textarea_height($height)
	{
		$this->_textarea_height = $height;
	}
	
	/**
	 * Sets wether the hide-bbcode/-smiley-checkboxes should be displayed
	 * 
	 * @param boolean $show show the checkboxes?
	 */
	public function set_show_options($show)
	{
		$this->_show_options = $show;
	}
	
	/**
	 * Sets the FWS_HTML_Formular-instance for this posting-form
	 * 
	 * @param FWS_HTML_Formular $form the formular
	 */
	public function set_formular($form)
	{
		$this->_form = $form;
	}
	
	/**
	 * Adds all variables to the template inc_post_form.htm so that you can include it
	 */
	public function add_form()
	{
		$this->_add_post_form();
		if($this->_attachments['show'])
			$this->_add_attachment_form();
		
		// increase for the next post-form
		self::$number++;
	}
	
	/**
	 * Builds the textarea with given text and all options
	 * 
	 * @param string $text the text
	 * @param boolean $use_applet do you want to use the applet?
	 * @param string $path the path to Boardsolution (by default FWS_Path::client_app())
	 * @return string the textarea
	 */
	public function get_textarea($text,$use_applet,$path = null)
	{
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();

		$use_applet = $use_applet && $cfg['msgs_allow_java_applet'];
		$options = BS_PostingUtils::get_message_options($this->_type);
		$path = $path === null ? FWS_Path::client_app() : $path;
		
		$sallowed = '';
		if($options['enable_bbcode'])
			$sallowed = strtolower(BS_PostingUtils::get_message_option('allowed_tags',$this->_type));
		$bbcode_buttons = '';
		if(!$use_applet && $options['enable_bbcode'])
			$bbcode_buttons = $this->_get_bbcode_for_post($sallowed,$path);
		
		$tpl->set_template('inc_textarea.htm');
		$tpl->add_variables(array(
			'applet' => $use_applet,
			'number' => self::$number,
			'bspath' => $path,
			'name_suffix' => $this->_name_suffix,
			'enable_smileys' => $options['enable_smileys'],
			'bbcode_buttons' => $bbcode_buttons,
			'textarea_height' => $this->_textarea_height,
			'max_line_length' => $cfg['msgs_max_line_length'],
			'code_line_numbers' => $cfg['msgs_code_line_numbers'],
			'allowed_tags' => $sallowed,
			'font_families' => $cfg['post_font_pool'],
			'text' => $text,
			'applet_text' => str_replace("\t","%t%",str_replace("\n","%n%",$text))
		));
		$tpl->add_variables($this->_get_smileys_for_post());
		return $tpl->parse_template();
	}
	
	/**
	 * Adds the post-formular to the template
	 *
	 */
	private function _add_post_form()
	{
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();
		$locale = FWS_Props::get()->locale();
		$input = FWS_Props::get()->input();
		$cfg = FWS_Props::get()->cfg();
		$options = BS_PostingUtils::get_message_options($this->_type);
	
		// instantiate form-var-helper?
		if($this->_form === null)
			$this->_form = new BS_HTML_Formular($this->_attachments['show'],true);
		
		$tpl->set_template('inc_post_form.htm',self::$number);
		$tpl->add_variables(array('form',$this->_form));
		$tpl->add_allowed_method('form','*');
	
		$toggle_smbb = '';
		if($user->use_bbcode_applet())
		{
			if($this->_show_options)
			{
				$toggle_smbb .= $this->_form->get_checkbox(
					'use_bbcode'.$this->_name_suffix,$this->_use_bbcode,null,$locale->lang('use_bbcode')
				);
				$toggle_smbb .= '<br />';
				$toggle_smbb .= $this->_form->get_checkbox(
					'use_smileys'.$this->_name_suffix,$this->_use_smileys,null,$locale->lang('use_smileys')
				);
			}
		}
		else
		{
			if($options['enable_bbcode'])
			{
				if($this->_show_options)
				{
					$toggle_smbb .= $this->_form->get_checkbox(
						'use_bbcode'.$this->_name_suffix,$this->_use_bbcode,null,
						$locale->lang('use_bbcode')
					);
					$toggle_smbb .= '<br />';
				}
			}
			
			if($options['enable_smileys'])
			{
				if($this->_show_options)
				{
					$toggle_smbb .= $this->_form->get_checkbox(
						'use_smileys'.$this->_name_suffix,$this->_use_smileys,null,
						$locale->lang('use_smileys')
					);
				}
			}
			if($toggle_smbb != '')
				$toggle_smbb = '<br />'.$toggle_smbb;
		}
	
		$text = $this->_form->get_input_value('text',$this->_text);
		
		$use_applet = $user->use_bbcode_applet();
		if($input->isset_var('bbcode_mode_'.self::$number,'post'))
		{
			$mode = $input->get_var('bbcode_mode_'.self::$number,'post',FWS_Input::STRING);
			$use_applet = $mode == 'applet';
		}
		
		$textarea = $this->get_textarea($text,$use_applet);
		
		if(!$user->is_loggedin())
		{
			$sec_code_field = FWS_StringHelper::generate_random_key(15);
			$user->set_session_data('sec_code_field',$sec_code_field);
			
			$tpl->add_variables(array(
				'user_name_value' => $this->_form->get_input_value('user_name'),
				'user_maxlength' => max(10,min(30,$cfg['profile_max_user_len'])),
				'email_value' => $this->_form->get_input_value('email_adr'),
				'security_code_img' => BS_URL::build_standalone_url('security_code'),
				'enable_security_code' => $cfg['use_captcha_for_guests'],
				'sec_code_field' => $sec_code_field
			));
		}
		
		$murl = BS_URL::get_mod_url('faq');
		$murl->set_anchor('f_11');
		if(defined('BS_ACP'))
			$murl->set_absolute(true);
		
		if($options['enable_bbcode'])
		{
			if($cfg['enable_faq'])
				$bbc_act = sprintf($locale->lang('bbcode_activated'),$murl->to_url());
			else
				$bbc_act = sprintf($locale->lang('bbcode_activated_no_link'),$murl->to_url());
		}
		else
		{
			if($cfg['enable_faq'])
				$bbc_act = sprintf($locale->lang('bbcode_not_activated'),$murl->to_url());
			else
				$bbc_act = sprintf($locale->lang('bbcode_not_activated_no_link'),$murl->to_url());
		}
		
		if($options['enable_smileys'])
			$smileys_act = $locale->lang('smileys_activated');
		else
			$smileys_act = $locale->lang('smileys_not_activated');

		if(!$user->use_bbcode_applet())
		{
			$tpl->add_variables(array(
				'text' => $text
			));
		}
	
		$bbcode_mode = $input->get_var('bbcode_mode_'.self::$number,'post',FWS_Input::STRING);
		if($bbcode_mode == null)
		{
			if($user->is_loggedin())
				$bbcode_mode = $user->get_profile_val('bbcode_mode');
			else
				$bbcode_mode = $cfg['msgs_default_bbcode_mode'];
		}
		
		$url = BS_URL::get_standalone_url('ajax_get_postform');
		$url->set('type',$this->_type);
		$url->set('mode','__MODE__');
		$url->set('height',$this->_textarea_height);
		$url->set_separator('&');
		
		$tpl->add_variables(array(
			'get_post_form_url' => $url->to_url(),
			'number' => self::$number,
			'applet_enabled' => $cfg['msgs_allow_java_applet'],
			'bbcode_enabled' => $options['enable_bbcode'],
			'bbcode_activated' => $bbc_act,
			'smileys_activated' => $smileys_act,
			'post_title' => $this->_title,
			'toggle_smbb' => $toggle_smbb,
			'textarea' => $textarea,
			'bbcode_mode' => $bbcode_mode,
			'bb_si_checked' => $bbcode_mode == 'simple' ? ' checked="checked"' : '',
			'bb_ad_checked' => $bbcode_mode == 'advanced' ? ' checked="checked"' : '',
			'bb_app_checked' => $bbcode_mode == 'applet' ? ' checked="checked"' : ''
		));
		
		$tpl->restore_template();
	}
	
	/**
	 * Adds the attachment-formular to the template
	 */
	private function _add_attachment_form()
	{
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$tpl = FWS_Props::get()->tpl();
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		if($cfg['attachments_enable'] == 1 &&
			 $auth->has_global_permission('attachments_add'))
		{
			$tpl->set_template('inc_attachments.htm');
			
			$file_paths = $input->get_var('attached_file_paths','post');
			
			// determine hint
			if($input->get_var(BS_URL_ACTION,'get',FWS_Input::STRING) == 'edit_post' &&
				 $input->isset_var('attached_file_paths','post') &&
				 ($input->isset_var('add_attachment','post') ||
				 	$input->isset_var('remove_attachment','post')) && count($file_paths) > 0)
				$hint = $locale->lang('press_upload_to_finish');
			else
				$hint = '';
			
			$attachments = array();
			
			if($this->_attachments['db_attachments'])
			{
				foreach(BS_DAO::get_attachments()->get_by_postid($this->_attachments['post_id']) as $data)
				{
					$attachments[] = array(
						'attached_file' => $data['attachment_path'],
						'input_stuff' => '<input type="submit" name="remove_attachment[db|'.$data['id'].']"'
														.' value="'.$locale->lang('delete').'" />'
					);
				}
			}
	
			if($this->_attachments['post_attachments'] && is_array($file_paths))
			{
				foreach($file_paths as $index => $attached_file)
				{
					$input_stuff = '<input type="hidden" name="attached_file_paths['.$index.']"';
					$input_stuff .= ' value="'.$attached_file.'" />'."\n";
					$input_stuff .= ' <input type="submit" name="remove_attachment[file|'.$index.']"';
					$input_stuff .= ' value="'.$locale->lang('delete').'" />';
	
					$attachments[] = array(
						'attached_file' => $attached_file,
						'input_stuff' => $input_stuff
					);
				}
			}
			
			$attachment_limits = sprintf(
				$locale->lang('attachment_limits'),
				$cfg['attachments_max_per_post'],
				$cfg['attachments_max_filesize'],
				str_replace('|',',',$cfg['attachments_filetypes'])
			);
	
			$tpl->add_variables(array(
				'attachment_limits' => $attachment_limits,
				'show_attachments' => count($attachments) > 0,
				'hint' => $hint
			));
			$tpl->add_variable_ref('attachments',$attachments);
			
			$tpl->restore_template();
		}
	}
	
	/**
	 * Collects the smileys
	 *
	 * @return array all variables to add to the template
	 */
	private function _get_smileys_for_post()
	{
		$res = array(
			'smileys' => array()
		);
		
		// collect base-smileys
		$base_num = 0;
		$smileys = BS_DAO::get_smileys()->get_list();
		foreach($smileys as $data)
		{
			if($data['is_base'] == 1)
				$base_num++;
			
			$text = BS_SPACES_AROUND_SMILEYS ? "%20".$data['primary_code']."%20" : $data['primary_code'];
			$res['smileys'][] = array(
				'is_base' => $data['is_base'],
				'smiley_path' => $data['smiley_path'],
				'primary_code' => $data['primary_code'],
				'secondary_code' => $data['secondary_code'],
				'prim_code_insert' => $text
			);
		}
		
		$total = count($smileys);
		$res['more_smileys'] = $total > $base_num;
		$url = BS_URL::get_standalone_url('smileys');
		$url->set(BS_URL_ID,self::$number);
		$res['smiley_popup_url'] = $url->to_url();
		$res['smiley_popup_height'] = $total * 28 + 120;
	
		return $res;
	}
	
	/**
	 * generates the bbcode-area for posts etc.
	 *
	 * @param string $sallowed the string with the allowed tags
	 * @param string $bspath the path to bs
	 * @return string the html-code
	 */
	private function _get_bbcode_for_post($sallowed,$bspath)
	{
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();

		$allowed = FWS_Array_Utils::advanced_explode(',',$sallowed);
		
		// once is enough :)
		$bbcode_data = '';
		if(self::$number == 1)
		{
			$bbcode_data = 'var BBCODE = new Array();'."\n";
			$i = 0;
			foreach(BS_BBCode_Helper::get_instance()->get_tags() as $row)
			{
				if(in_array(strtolower($row['name']),$allowed))
				{
					$bbcode_data .= 'BBCODE['.$i.'] = new Array();'."\n";
					$bbcode_data .= 'BBCODE['.$i.']["tag"] = "'.$row['name'].'";'."\n";
					$bbcode_data .= 'BBCODE['.$i.']["param"] = "'.$row['param'].'";'."\n";
					$bbcode_data .= 'BBCODE['.$i.']["prompt_text"] = "';
					if($locale->contains_lang('bbcode_prompt_'.$row['name']))
						$bbcode_data .= $locale->lang('bbcode_prompt_'.$row['name']);
					$bbcode_data .= '";'."\n";
					$bbcode_data .= 'BBCODE['.$i.']["prompt_param_text"] = "';
					if($locale->contains_lang('bbcode_prompt_param_'.$row['name']))
						$bbcode_data .= $locale->lang('bbcode_prompt_param_'.$row['name']);
					$bbcode_data .= '";'."\n\n";
					$i++;
				}
			}
		}
		
		$tpl->set_template('inc_bbcode.htm');
		
		$hldir = FWS_Path::server_app().'bbceditor/highlighter/';
		FWS_Highlighting_Languages::ensure_inited($hldir.'languages.xml');
		
		$fonts = FWS_Array_Utils::advanced_explode(',',$cfg['post_font_pool']);
		sort($fonts);
		$tpl->add_variables(array(
			'textarea_id' => 'bbcode_area'.self::$number,
			'number' => self::$number,
			'bspath' => $bspath,
			'bbcode' => $this,
			'hllangs' => FWS_Highlighting_Languages::get_languages(),
			'bbcode_data' => $bbcode_data,
			'post_fonts' => $fonts
		));
		$tpl->add_allowed_method('bbcode','is_allowed');
	
		return $tpl->parse_template();
	}
	
	/**
	 * Checks wether the given tag is allowed. You may also specify multiple tags by passing
	 * multiple arguments to the method.
	 *
	 * @param string $tag the tag
	 * @return boolean true if the tag or at least one tags are allowed
	 */
	public function is_allowed($tag)
	{
		static $allowed = null;
		if($allowed === null)
		{
			$sallowed = BS_PostingUtils::get_message_option('allowed_tags',$this->_type);
			$allowed = FWS_Array_Utils::advanced_explode(',',strtolower($sallowed));
		}
		
		if(func_num_args() > 1)
		{
			foreach(func_get_args() as $arg)
			{
				if(in_array(strtolower($arg),$allowed))
					return true;
			}
			return false;
		}
		
		return in_array(strtolower($tag),$allowed);
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>