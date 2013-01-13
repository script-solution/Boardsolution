<?php
/**
 * Contains the smiley-popup-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * Displays the smiley-popup
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_smileys extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$input = FWS_Props::get()->input();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_template('popup_smileys.htm');
		$renderer->set_show_headline(false);
		$renderer->set_show_bottom(false);
		
		$number = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		$url = BS_URL::get_mod_url();
		$url->set(BS_URL_ID,$number);
		$renderer->add_breadcrumb($locale->lang('smileys'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();

		$number = $input->get_var(BS_URL_ID,'get',FWS_Input::ID);
		
		$smileys = array();
		foreach(BS_DAO::get_smileys()->get_list() as $data)
		{
			$primcode = $data['primary_code'];
			$smileys[] = array(
				'smiley_code' => BS_SPACES_AROUND_SMILEYS ? '%20'.$primcode.'%20' : $primcode,
				'display_code' => $primcode,
				'smiley_path' => FWS_Path::client_app().'images/smileys/'.$data['smiley_path']
			);
		}
		
		$tpl->add_variable_ref('smileys',$smileys);
		$tpl->add_variables(array(
			'number' => $number
		));
	}
}
?>