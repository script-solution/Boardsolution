<?php
/**
 * Contains utility-methods for the ACP
 *
 * @version			$Id: utils.php 713 2008-05-20 21:59:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Utitity methods for the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Utils extends PLIB_Singleton
{
	/**
	 * @return BS_ACP_Utils the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}

	/**
	 * Determines the image-filename for the given file by the extension
	 *
	 * @param string $file the filename
	 * @return string the name of the image-file
	 */
	public function get_file_image($file)
	{
		switch(PLIB_FileUtils::get_extension($file))
		{
			case 'html':
			case 'htm':
				$image = 'html.gif';
				break;

			case 'css':
				$image = 'css.gif';
				break;

			case 'js':
				$image = 'js.gif';
				break;

			case 'gif':
			case 'jpg':
			case 'jpeg':
			case 'png':
				$image = 'image.gif';
				break;
			
			case 'php':
				$image = 'php.gif';
				break;

			default:
				$image = 'file.gif';
				break;
		}

		return $image;
	}
	
	/**
	 * Builds a link for the given user which shows the userdetails-popup
	 *
	 * @param int $id the user-id
	 * @param string $name the username
	 */
	public function get_userlink($id,$name)
	{
		$url = $this->url->get_standalone_url('acp','user_details','&amp;id='.$id);
		$user = '<a href="javascript:PLIB_openDefaultPopup(\''.$url.'\',';
		$user .= '\'UserDetails\',800,500);">'.$name.'</a>';
		return $user;
	}
	
	/**
	 * Builds the text for an "order-column"
	 *
	 * @param string $title the title of the column
	 * @param string $order_value the value of the order-parameter
	 * @param string $def_ascdesc the default value for 'ad' (ASC or DESC)
	 * @param string $order the current value of 'order'
	 * @param string $url the current URL
	 * @return string the column-content
	 */
	public function get_order_column($title,$order_value,$def_ascdesc,$order,$url)
	{
		if($order == $order_value)
		{
			$result = $title.' <a href="'.$url.'order='.$order_value.'&amp;ad=ASC">';
			$result .= '<img src="'.PLIB_Path::inner().'acp/images/asc.gif" alt="ASC" />';
			$result .= '</a> ';
			$result .= '<a href="'.$url.'order='.$order_value.'&amp;ad=DESC">';
			$result .= '<img src="'.PLIB_Path::inner().'acp/images/desc.gif" alt="DESC" />';
			$result .= '</a>';
		}
		else
		{
			$result = '<a href="'.$url.'order='.$order_value.'&amp;ad=';
			$result .= $def_ascdesc.'">'.$title.'</a>';
		}
	
		return $result;
	}
	
	/**
	 * Returns the language-entry for 'yes' or 'no' corresponding to the given boolean
	 *
	 * @param boolean $bool the boolean
	 * @param boolean $colored wether green (#008000) / red (#ff0000) should be used
	 * @param boolean $yesisgreen if colored: should green be used for 'yes'?
	 * @return string yes or no
	 */
	public function get_yesno($bool,$colored = false,$yesisgreen = true)
	{
		if($colored)
		{
			$green = $yesisgreen ? '#008000' : '#ff0000';
			$red = $yesisgreen ? '#ff0000' : '#008000';
			if($bool)
				return '<span style="color: '.$green.';">'.$this->locale->lang('yes').'</span>';
			return '<span style="color: '.$red.';">'.$this->locale->lang('no').'</span>';
		}
		
		return $bool ? $this->locale->lang('yes') : $this->locale->lang('no');
	}
	
	/**
	 * Sends an email with the given subject and message to all given users.
	 *
	 * @param array $user_ids all user-ids
	 * @param string $subject the subject of the email
	 * @param string $text the text of the email
	 */
	public function send_email_to_user($user_ids,$subject,$text)
	{
		$error_msgs = array();
		$email = $this->functions->get_mailer('',$subject,$text);
		foreach(BS_DAO::get_user()->get_users_by_ids($user_ids) as $data)
		{
			$email->set_recipient($data['user_email']);
			if(!$email->send_mail())
			{
				$error = $email->get_error_message();
				if(!isset($error_msgs[$error]))
					$error_msgs[$error] = true;
			}
		}
		
		foreach(array_keys($error_msgs) as $error)
			$this->msgs->add_error($error);
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>