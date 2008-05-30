<?php
/**
 * Contains the standalone-class for the security-code
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */


/**
 * The security-code
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_security_code extends BS_Standalone
{
	public function use_output_buffering()
	{
		return false;
	}
	
	public function run()
	{
		$imagedir = PLIB_Path::inner().'images/gd/';
		$captcha = new PLIB_GD_Captcha();
		$captcha->add_ttf_font(new PLIB_GD_Font_TTF($imagedir.'scratch.ttf'),35);
		$captcha->add_ttf_font(new PLIB_GD_Font_TTF($imagedir.'veramono.ttf'),35);
		$captcha->add_ttf_font(new PLIB_GD_Font_TTF($imagedir.'thros.ttf'),40);
		$captcha->create_image();
		
		$this->user->set_session_data('security_code',$captcha->get_chars());
	}
	
	public function require_board_access()
	{
		return false;
	}
}
?>