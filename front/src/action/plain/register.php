<?php
/**
 * Contains the register-plain-action-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The plain-action to register a user
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Plain_Register extends BS_Front_Action_Plain
{
	/**
	 * Returns the default instance (variables read from POST) of this class.
	 *
	 * @return BS_Front_Action_Plain_Register the register-object or the error-message if anything
	 * 	went wrong
	 */
	public static function get_default()
	{
		$input = PLIB_Props::get()->input();
		$cfg = PLIB_Props::get()->cfg();
		
		// name and email
		$user_name = $input->get_var('user_name','post',PLIB_Input::STRING);
		$user_email = $input->get_var('user_email','post',PLIB_Input::STRING);
		$user_email_conf = $input->get_var('user_email_conf','post',PLIB_Input::STRING);
		if($user_email != $user_email_conf)
			return 'emails_not_equal';

		// password
		$user_pw = $input->get_var('user_pw','post',PLIB_Input::STRING);
		$user_pw_conf = $input->get_var('user_pw_conf','post',PLIB_Input::STRING);
		if($user_pw != $user_pw_conf)
			return 'registerpwsnichtidentisch';

		// check and update additional-fields
		$addfields = array();
		$cfields = BS_AddField_Manager::get_instance();
		foreach($cfields->get_fields_at(BS_UF_LOC_REGISTRATION) as $field)
			$addfields[$field->get_data()->get_name()] = $field->get_value_from_formular();

		// some other stuff
		$email_display_mode = $input->correct_var(
			'email_display_mode','post',PLIB_Input::STRING,array('hide','jumble','default'),'default'
		);
		$allow_pms = $input->get_var('enable_pms','post',PLIB_Input::INT_BOOL);
		$active = ($cfg['account_activation'] == 'none') ? true : false;
		$allow_board_emails = $input->get_var('allow_board_emails','post',PLIB_Input::INT_BOOL);
		
		// build plain-action
		return new BS_Front_Action_Plain_Register(
			$user_name,$user_pw,$user_email,array(BS_STATUS_USER),$addfields,$active,$email_display_mode,
			$allow_pms,$allow_board_emails
		);
	}
	
	/**
	 * The id of the created user
	 *
	 * @var int
	 */
	private $_user_id = null;
	
	/**
	 * The name of the user
	 *
	 * @var string
	 */
	private $_user_name;
	
	/**
	 * The password of the user
	 *
	 * @var string
	 */
	private $_user_pw;
	
	/**
	 * The email-address
	 *
	 * @var string
	 */
	private $_user_email;
	
	/**
	 * An array with all user-groups. The first group will be the main-group!
	 *
	 * @var array
	 */
	private $_user_groups;
	
	/**
	 * The email-display-mode to use: default, jumble, hide
	 *
	 * @var string
	 */
	private $_email_display_mode;
	
	/**
	 * Wether the user should be activated
	 *
	 * @var boolean
	 */
	private $_active;
	
	/**
	 * Wether PMs should be enabled
	 *
	 * @var boolean
	 */
	private $_allow_pms;
	
	/**
	 * Wether board-emails should be enabled
	 *
	 * @var boolean
	 */
	private $_allow_board_emails;
	
	/**
	 * An array with all additional-fields to set. In the following form:
	 * <code>
	 * 	array(
	 * 		<fieldName> => <fieldValue>,
	 * 		...
	 * 	)
	 * </code>
	 *
	 * @var array
	 */
	private $_additional_fields;
	
	/**
	 * Constructor
	 *
	 * @param string $user_name the user-name
	 * @param string $user_pw the password (clear text)
	 * @param string $user_email the email
	 * @param array $user_groups An array with all user-groups. The first group will be the main-group!
	 * @param array $additional_fields An array with all additional-fields to set. In the following form:
	 * 	<code>
	 * 		array(
	 * 			<fieldName> => <fieldValue>,
	 * 			...
	 * 		)
	 * 	</code>
	 * @param boolean $active wether the user should be activated
	 * @param string $email_display_mode the email-display-mode to use: default, jumble, hide
	 * @param boolean $allow_pms wether PMs should be enabled
	 * @param boolean $allow_board_emails wether board-emails should be enabled
	 */
	public function __construct($user_name,$user_pw,$user_email,$user_groups,
		$additional_fields = array(),$active = true,$email_display_mode = 'default',$allow_pms = true,
		$allow_board_emails = true)
	{
		if(!PLIB_Array_Utils::is_integer($user_groups) || count($user_groups) == 0)
			PLIB_Helper::def_error('intarray>0','user_groups',$user_groups);
		if(!is_array($additional_fields))
			PLIB_Helper::def_error('array','additional_fields',$additional_fields);
		
		$this->_user_name = (string)$user_name;
		$this->_user_pw = (string)$user_pw;
		$this->_user_email = (string)$user_email;
		$this->_user_groups = $user_groups;
		$this->_additional_fields = $additional_fields;
		$this->_active = (bool)$active;
		$this->_email_display_mode = (string)$email_display_mode;
		$this->_allow_pms = (bool)$allow_pms;
		$this->_allow_board_emails = (bool)$allow_board_emails;
	}
	
	/**
	 * @return int the id of the created user (available after perform_action())
	 */
	public function get_user_id()
	{
		return $this->_user_id;
	}
	
	public function check_data()
	{
		$functions = PLIB_Props::get()->functions();
		$cfg = PLIB_Props::get()->cfg();
		$locale = PLIB_Props::get()->locale();

		// this is only possible if the community has not been exported
		if(BS_ENABLE_EXPORT)
			return 'The community is exported';

		// is the username valid?
		if(BS_DAO::get_user()->name_exists($this->_user_name))
			return 'registeruservorhanden';

		if(trim($this->_user_name) == '')
			return 'registeruserleer';

		if(!BS_UserUtils::get_instance()->check_username($this->_user_name))
			return 'usernamenotallowed';

		if($functions->is_banned('user',$this->_user_name))
			return 'usernamenotallowed';

		$len = PLIB_String::strlen($this->_user_name);
		if($len < $cfg['profile_min_user_len'] && $len > $cfg['profile_max_user_len'])
			return sprintf($locale->lang('error_wronguserlen'),
										 $cfg['profile_min_user_len'],
										 $cfg['profile_max_user_len']);

		// check the email-address
		if($functions->is_banned('mail',$this->_user_email))
			return 'mailnotallowed';

		if($this->_user_email == '')
			return 'email_empty';

		$this->_user_email = trim($this->_user_email);
		if(!PLIB_StringHelper::is_valid_email($this->_user_email))
			return 'invalid_email';

		// does the email already exist?
		if(BS_DAO::get_user()->email_exists($this->_user_email))
			return 'email_exists';

		// check the password
		if($this->_user_pw == '')
			return 'password_empty';
		
		if(!in_array($this->_email_display_mode,array('default','jumble','hide')))
			return 'Invalid email-display-mode "'.$this->_email_display_mode.'"!';

		// check and update additional-fields
		$cfields = BS_AddField_Manager::get_instance();
		foreach($this->_additional_fields as $name => $value)
		{
			$field = $cfields->get_field_by_name($name);
			if($field === null)
				return 'The field with name "'.$name.'" doesn\'t exist!';
			
			if(($error = $field->is_valid_value($value)) !== '')
				return sprintf($locale->lang('error_add_field_'.$error),$field->get_data()->get_title());
		}
		
		// check if all required fields are specified
		$required_fields = $cfields->get_required_fields();
		foreach($required_fields as $f)
		{
			if(!isset($this->_additional_fields[$f->get_data()->get_name()]))
				return 'The required field "'.$f->get_data()->get_name().'" is missing!';
		}
		
		return parent::check_data();
	}
	
	public function perform_action()
	{
		$db = PLIB_Props::get()->db();
		$cfg = PLIB_Props::get()->cfg();
		$locale = PLIB_Props::get()->locale();
		$msgs = PLIB_Props::get()->msgs();
		$ips = PLIB_Props::get()->ips();
		$cookies = PLIB_Props::get()->cookies();
		$input = PLIB_Props::get()->input();

		parent::perform_action();
		
		$db->start_transaction();
		
		// insert the user into the database
		$this->_user_id = BS_DAO::get_user()->create(
			$this->_user_name,$this->_user_email,$this->_user_pw
		);
		
		$time = time();
		
		$fields = array(
			'id' => $this->_user_id,
			'user_group' => implode(',',$this->_user_groups).',',
			'email_display_mode' => $this->_email_display_mode,
			'registerdate' => $time,
			'lastlogin' => 0,
			'active' => $this->_active ? 1 : 0,
			'allow_pms' => $this->_allow_pms ? 1 : 0,
			'allow_board_emails' => $this->_allow_board_emails ? 1 : 0,
			'timezone' => $cfg['default_timezone'],
			'last_unread_update' => $time,
			'bbcode_mode' => $cfg['msgs_default_bbcode_mode'],
			'posts_order' => $cfg['default_posts_order']
		);
		
		// add additional fields
		$cfields = BS_AddField_Manager::get_instance();
		foreach($this->_additional_fields as $name => $value)
		{
			$field = $cfields->get_field_by_name($name);
			$fields['add_'.$name] = $field->get_value_to_store($value);
		}
		
		BS_DAO::get_profile()->create_custom($fields);

		// send the administrators a PM
		if($cfg['get_email_new_account'] == 1)
		{
			$mail = BS_EmailFactory::get_instance()->get_new_account_mail($this->_user_name);
			$mail_errors = array();
			foreach(BS_DAO::get_user()->get_users_by_groups(array(BS_STATUS_ADMIN)) as $adata)
			{
				$mail->set_recipient($adata['user_email']);
				if(!$mail->send_mail())
					$mail_errors[] = $mail->get_error_message();
			}
			
			// mail errors?
			if(count($mail_errors) > 0)
			{
				$msg = sprintf($locale->lang('error_mail_error'),implode('<br />',$mail_errors));
				$msgs->add_error($msg);
			}
		}

		$ips->add_entry('reg');

		// no account-activation...so login the user
		if($cfg['account_activation'] == 'none')
		{
			$cookies->set_cookie('user',$this->_user_name);
			$cookies->set_cookie('pw',md5($this->_user_pw));
			
			// fire community-event
			$status = BS_Community_User::get_status_from_groups($this->_user_groups);
			$user = new BS_Community_User(
				$this->_user_id,
				$input->unescape_value($this->_user_name,'post'),
				$input->unescape_value($this->_user_email,'post'),$status,md5($this->_user_pw),
				$input->unescape_value($this->_user_pw,'post')
			);
			BS_Community_Manager::get_instance()->fire_user_registered($user);
		}

		$user_key = '';
		if($cfg['account_activation'] == 'email')
		{
			$user_key = PLIB_StringHelper::generate_random_key();
			BS_DAO::get_activation()->create($this->_user_id,$user_key);
		}
		
		// send an email to the user
		$mail = BS_EmailFactory::get_instance()->get_register_mail(
			$this->_user_id,$this->_user_name,$this->_user_email,$this->_user_pw,$user_key
		);
		if(!$mail->send_mail())
		{
			$msg = sprintf($locale->lang('error_mail_error'),$mail->get_error_message());
			$msgs->add_error($msg);
		}
		
		$db->commit_transaction();
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>