<?php
/**
 * Contains the post-form-change-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Returns a post-form. This will be called via AJAX
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_ajax_get_postform extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_raw_renderer();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$doc = FWS_Props::get()->doc();

		$type = $input->correct_var(
			'type','get',FWS_Input::STRING,array('post','sig','linkdesc','pm'),'post'
		);
		$mode = $input->correct_var(
			'mode','get',FWS_Input::STRING,array('simple','advanced','applet'),'simple'
		);
		$height = $input->get_var('height','get',FWS_Input::STRING);
		$path = $input->get_var('bspath','post',FWS_Input::STRING);
		
		$form = new BS_PostingForm('','',$type);
		$form->set_textarea_height($height);
		
		$renderer = $doc->use_raw_renderer();
		$renderer->set_content($form->get_textarea('',$mode == 'applet',$path));
	}
}
?>