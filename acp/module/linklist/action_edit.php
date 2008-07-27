<?php
/**
 * Contains the edit-link-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-link-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_linklist_edit extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();

		$id = $input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';
		
		$data = BS_DAO::get_links()->get_by_id($id);
		if($data === false)
			return 'A link with id="'.$id.'" could not been found';
		
		$url = $input->get_var('url','post',PLIB_Input::STRING);
		$new_category = $input->get_var('new_category','post',PLIB_Input::STRING);
		$category = $input->get_var('category','post',PLIB_Input::STRING);
		$description = $input->get_var('text','post',PLIB_Input::STRING);
		
		if($url != '')
		{
			$text = '';
			$error = BS_PostingUtils::get_instance()->prepare_message_for_db($text,$description,'desc');
			if($error != '')
				return $error;
			
			$sql_cat = ($new_category != '') ? $new_category : $category;
			BS_DAO::get_links()->update($id,array(
				'link_url' => $url,
				'category' => $sql_cat,
				'link_desc' => $text,
				'link_desc_posted' => $description
			));
		}
		
		$this->set_success_msg($locale->lang('link_updated_successfully'));
		$this->set_action_performed(true);

		return '';
	}
}
?>