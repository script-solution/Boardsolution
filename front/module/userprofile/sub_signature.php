<?php
/**
 * Contains the signature-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The signature submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_signature extends BS_Front_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACTION_EDIT_SIGNATURE,'updatesig');

		$doc->add_breadcrumb($locale->lang('signature'),$url->get_url(0,'&amp;'.BS_URL_LOC.'=signature'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$cfg = PLIB_Props::get()->cfg();
		$locale = PLIB_Props::get()->locale();
		$user = PLIB_Props::get()->user();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		if($cfg['enable_signatures'] == 0)
		{
			$this->report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}
		
		$form = new BS_PostingForm($locale->lang('signature').':',
			$user->get_profile_val('signature_posted'),'sig');
		$form->set_textarea_height('100px');
		$form->add_form();
		
		$options = BS_PostingUtils::get_instance()->get_message_options('sig');
		$bbcode = new BS_BBCode_Parser(
			$user->get_profile_val('signatur'),'sig',
			$options['enable_bbcode'],$options['enable_smileys']
		);
		
		$tpl->add_variables(array(
			'action_type' => BS_ACTION_EDIT_SIGNATURE,
			'target_url' => $url->get_url(0,'&amp;'.BS_URL_LOC.'=signature'),
			'signature_preview' => $bbcode->get_message_for_output()
		));
	}
}
?>