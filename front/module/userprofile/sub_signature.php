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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_EDIT_SIGNATURE,'updatesig');

		$renderer->add_breadcrumb($locale->lang('signature'),$url->get_url(0,'&amp;'.BS_URL_LOC.'=signature'));
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
		$url = FWS_Props::get()->url();

		if($cfg['enable_signatures'] == 0)
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
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