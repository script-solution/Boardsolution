<?php
/**
 * Contains the message-quotation-module
 * 
 * @version			$Id: module_ajax_quote.php 43 2008-07-30 10:47:55Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Quotes a message. This will be called via AJAX
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_ajax_quote extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_raw_renderer();
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$auth = PLIB_Props::get()->auth();
		$user = PLIB_Props::get()->user();
		$doc = PLIB_Props::get()->doc();

		$id = $input->get_var('id','get',PLIB_Input::ID);
		$type = $input->correct_var('type','get',PLIB_Input::STRING,array('post','pm'),'post');
		$res = '';
		
		if($id == null)
		{
			$this->report_error();
			return;
		}
		
		if($type == 'post')
		{
			$post_data = BS_DAO::get_posts()->get_post_by_id($id);
			// check if the post exist
			if($post_data === false)
			{
				$this->report_error();
				return;
			}
			
			// check if the post comes from a forum that the user is allowed to view
			if(!$auth->has_access_to_intern_forum($post_data['rubrikid']))
			{
				$this->report_error(PLIB_Document_Messages::NO_ACCESS);
				return;
			}
			
			$username = $post_data['post_user'] != 0 ? $post_data['user_name'] : $post_data['post_an_user'];
			$text = BS_PostingUtils::get_instance()->quote_text($post_data['text_posted'],$username);
			$res = PLIB_StringHelper::htmlspecialchars_back($text);
		}
		// logged in is required
		else if($user->is_loggedin())
		{
			$qdata = BS_DAO::get_pms()->get_pm_details($id,$user->get_user_id());
			
			// does the pm exist and has the user the permission to view it?
			if($qdata === false)
			{
				$this->report_error();
				return;
			}
			
			$text = BS_PostingUtils::get_instance()->quote_text(
				$qdata['pm_text_posted'],$qdata['sender_name']
			);
			$res = PLIB_StringHelper::htmlspecialchars_back($text);
		}
		
		$renderer = $doc->use_raw_renderer();
		$renderer->set_content($res);
	}
}
?>