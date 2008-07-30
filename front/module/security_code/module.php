<?php
/**
 * Contains the security-code-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The security-code
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_security_code extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param PLIB_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->use_gdimage_renderer();
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$user = PLIB_Props::get()->user();
		$doc = PLIB_Props::get()->doc();
		
		$imagedir = PLIB_Path::server_app().'images/gd/';
		$captcha = new PLIB_GD_Captcha();
		$captcha->add_ttf_font(new PLIB_GD_Font_TTF($imagedir.'scratch.ttf'),35);
		$captcha->add_ttf_font(new PLIB_GD_Font_TTF($imagedir.'veramono.ttf'),35);
		$captcha->add_ttf_font(new PLIB_GD_Font_TTF($imagedir.'thros.ttf'),40);
		$captcha->create_image();
		
		$renderer = $doc->use_gdimage_renderer();
		$renderer->set_image($captcha->get_image());
		
		$user->set_session_data('security_code',$captcha->get_chars());
	}
	
	// TODO we have to consider this!
	public function require_board_access()
	{
		return false;
	}
}
?>