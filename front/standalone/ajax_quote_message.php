<?php
/**
 * Contains the standalone-class for the message-quotation
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Quotes a message. This will be called via AJAX
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_ajax_quote_message extends BS_Standalone
{
	public function run()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		$type = $this->input->correct_var('type','get',PLIB_Input::STRING,array('post','pm'),'post');
		
		if($id != null)
		{
			if($type == 'post')
			{
				$post_data = BS_DAO::get_posts()->get_post_by_id($id);
				// check if the post exist
				if($post_data !== false)
				{
					// check if the post comes from a forum that the user is allowed to view
					if($this->auth->has_access_to_intern_forum($post_data['rubrikid']))
					{
						$username = $post_data['post_user'] != 0 ? $post_data['user'] : $post_data['post_an_user'];
						$text = BS_PostingUtils::get_instance()->quote_text($post_data['text_posted'],$username);
						echo PLIB_StringHelper::htmlspecialchars_back($text);
					}
				}
			}
			// logged in is required
			else if($this->user->is_loggedin())
			{
				$qdata = BS_DAO::get_pms()->get_pm_details($id,$this->user->get_user_id());
				
				// does the pm exist and has the user the permission to view it?
				if($qdata !== false)
				{
					$text = BS_PostingUtils::get_instance()->quote_text(
						$qdata['pm_text_posted'],$qdata['sender_name']
					);
					echo PLIB_StringHelper::htmlspecialchars_back($text);
				}
			}
		}
	}
}
?>