<?php
/**
 * Contains utility-methods for the ACP
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
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
 * Utitity methods for the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Utils extends FWS_UtilBase
{
	/**
	 * Determines the image-filename for the given file by the extension
	 *
	 * @param string $file the filename
	 * @return string the name of the image-file
	 */
	public static function get_file_image($file)
	{
		switch(FWS_FileUtils::get_extension($file))
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
	 * @return string the HTML-code
	 */
	public static function get_userlink($id,$name)
	{
		$furl = BS_URL::get_acpmod_url('userdetails');
		$furl->set('id',$id);
		$user = '<a href="javascript:FWS_openDefaultPopup(\''.$furl->to_url().'\',';
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
	 * @param BS_URL $url the current URL
	 * @return string the column-content
	 */
	public static function get_order_column($title,$order_value,$def_ascdesc,$order,$url)
	{
		if(!($url instanceof BS_URL))
			FWS_Helper::def_error('instance','url','BS_URL',$url);
		
		$url->set('order',$order_value);
		if($order == $order_value)
		{
			$result = $title.' <a href="'.$url->set('ad','ASC')->to_url().'">';
			$result .= '<img src="'.FWS_Path::client_app().'acp/images/asc.gif" alt="ASC" />';
			$result .= '</a> ';
			$result .= '<a href="'.$url->set('ad','DESC')->to_url().'">';
			$result .= '<img src="'.FWS_Path::client_app().'acp/images/desc.gif" alt="DESC" />';
			$result .= '</a>';
		}
		else
			$result = '<a href="'.$url->set('ad',$def_ascdesc)->to_url().'">'.$title.'</a>';
	
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
	public static function get_yesno($bool,$colored = false,$yesisgreen = true)
	{
		$locale = FWS_Props::get()->locale();

		if($colored)
		{
			$green = $yesisgreen ? '#008000' : '#ff0000';
			$red = $yesisgreen ? '#ff0000' : '#008000';
			if($bool)
				return '<span style="color: '.$green.';">'.$locale->lang('yes').'</span>';
			return '<span style="color: '.$red.';">'.$locale->lang('no').'</span>';
		}
		
		return $bool ? $locale->lang('yes') : $locale->lang('no');
	}
	
	/**
	 * Sends an email with the mail-instance to all given users.
	 *
	 * @param FWS_Email_Base $mail the email-instance
	 * @param array $user_ids all user-ids
	 */
	public static function send_email_to_user($mail,$user_ids)
	{
		$msgs = FWS_Props::get()->msgs();

		$error_msgs = array();
		foreach(BS_DAO::get_user()->get_users_by_ids($user_ids,-1,-1) as $data)
		{
			$mail->set_recipient($data['user_email']);
			if(!$mail->send_mail())
				$error_msgs[$mail->get_error_message()] = true;
		}
		
		foreach(array_keys($error_msgs) as $error)
		{
			if($error)
				$msgs->add_error($error);
		}
	}
}
?>