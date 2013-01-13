<?php
/**
 * Contains the edit-link-action
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
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
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';
		
		$data = BS_DAO::get_links()->get_by_id($id);
		if($data === false)
			return 'A link with id="'.$id.'" could not been found';
		
		$url = $input->get_var('url','post',FWS_Input::STRING);
		$new_category = $input->get_var('new_category','post',FWS_Input::STRING);
		$category = $input->get_var('category','post',FWS_Input::STRING);
		$description = $input->get_var('text','post',FWS_Input::STRING);
		
		if($url != '')
		{
			$text = '';
			$error = BS_PostingUtils::prepare_message_for_db($text,$description,'desc');
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