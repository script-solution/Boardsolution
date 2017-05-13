<?php
/**
 * Contains the signature-userprofile-submodule
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
 * The signature submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_signature extends BS_Front_SubModule
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
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_EDIT_SIGNATURE,'updatesig');

		$renderer->add_breadcrumb($locale->lang('signature'),BS_URL::build_sub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$locale = FWS_Props::get()->locale();
		$user = FWS_Props::get()->user();
		$tpl = FWS_Props::get()->tpl();
		$input = FWS_Props::get()->input();
		
		if($cfg['enable_signatures'] == 0)
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}
		
		$options = BS_PostingUtils::get_message_options('sig');
		if($input->isset_var('preview','post'))
			BS_PostingUtils::add_post_preview('sig',$options['enable_bbcode'],$options['enable_smileys']);
		
		$form = new BS_PostingForm($locale->lang('signature').':',
			$user->get_profile_val('signature_posted'),'sig');
		$form->set_textarea_height(100);
		$form->add_form();
		
		$bbcode = new BS_BBCode_Parser(
			$user->get_profile_val('signatur'),'sig',
			$options['enable_bbcode'],$options['enable_smileys']
		);
		
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_EDIT_SIGNATURE,
			'target_url' => BS_URL::build_sub_url(),
			'signature_preview' => $bbcode->get_message_for_output(),
			'sig_max_height' => $cfg['sig_max_height'],
			'signature_maxheight_notice' => sprintf(
				$locale->lang('signature_maxheight_notice'),$cfg['sig_max_height']
			)
		));
	}
}
?>