<?php
/**
 * Contains the smiley-popup-module
 * 
 * @version			$Id$
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