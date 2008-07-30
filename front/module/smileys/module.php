<?php
/**
 * Contains the smiley-popup-module
 * 
 * @version			$Id: module_smileys.php 43 2008-07-30 10:47:55Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$input = PLIB_Props::get()->input();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_template('popup_smileys.htm');
		$renderer->set_show_headline(false);
		$renderer->set_show_bottom(false);
		
		$number = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$renderer->add_breadcrumb(
			$locale->lang('smileys'),
			$url->get_url('smileys','&amp;'.BS_URL_ID.'='.$number)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();

		$number = $input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		
		$smileys = array();
		foreach(BS_DAO::get_smileys()->get_list() as $data)
		{
			$primcode = $data['primary_code'];
			$smileys[] = array(
				'smiley_code' => BS_SPACES_AROUND_SMILEYS ? '%20'.$primcode.'%20' : $primcode,
				'display_code' => $primcode,
				'smiley_path' => PLIB_Path::client_app().'images/smileys/'.$data['smiley_path']
			);
		}
		
		$tpl->add_array('smileys',$smileys);
		$tpl->add_variables(array(
			'number' => $number
		));
	}
}
?>