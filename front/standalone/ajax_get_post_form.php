<?php
/**
 * Contains the standalone-class for the post-form-change
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Returns a post-form. This will be called via AJAX
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_ajax_get_post_form extends BS_Standalone
{
	public function run()
	{
		$type = $this->input->correct_var(
			'type','get',PLIB_Input::STRING,array('post','sig','linkdesc','pm'),'post'
		);
		$mode = $this->input->correct_var(
			'mode','get',PLIB_Input::STRING,array('simple','advanced','applet'),'simple'
		);
		$height = $this->input->get_var('height','get',PLIB_Input::STRING);
		
		$form = new BS_PostingForm('','',$type);
		$form->set_textarea_height($height);
		echo $form->get_textarea('',$mode == 'applet');
	}
}
?>