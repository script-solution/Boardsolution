<?php
/**
 * Contains the signature-userprofile-submodule
 * 
 * @version			$Id: sub_signature.php 705 2008-05-15 10:14:58Z nasmussen $
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
	public function get_actions()
	{
		return array(
			BS_ACTION_EDIT_SIGNATURE => 'updatesig'
		);
	}
	
	public function run()
	{
		if($this->cfg['enable_signatures'] == 0)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}
		
		$form = new BS_PostingForm($this->locale->lang('signature').':',
			$this->user->get_profile_val('signature_posted'),'sig');
		$form->set_textarea_height('100px');
		$form->add_form();
		
		$options = BS_PostingUtils::get_instance()->get_message_options('sig');
		$bbcode = new BS_BBCode_Parser(
			$this->user->get_profile_val('signatur'),'sig',
			$options['enable_bbcode'],$options['enable_smileys']
		);
		
		$this->tpl->add_variables(array(
			'action_type' => BS_ACTION_EDIT_SIGNATURE,
			'target_url' => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=signature'),
			'signature_preview' => $bbcode->get_message_for_output()
		));
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('signature') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=signature')
		);
	}
}
?>