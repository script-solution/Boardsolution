<?php
/**
 * Contains the property-loader-class
 *
 * @version			$Id: proploader.php 54 2008-12-01 10:26:23Z nasmussen $
 * @package			Boardsolution
 * @subpackage	install.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The property-loader for the install-script
 *
 * @package			Boardsolution
 * @subpackage	install.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_PropLoader extends BS_PropLoader
{
	/**
	 * @see BS_PropLoader::sessions()
	 *
	 * @return BS_Session_Manager
	 */
	protected function sessions()
	{
		return new FWS_Session_Manager(new FWS_Session_Storage_PHP(),true);
	}

	/**
	 * @see BS_PropLoader::user()
	 *
	 * @return BS_User_Current
	 */
	protected function user()
	{
		return new FWS_User_Current(new FWS_User_Storage_Empty());
	}
	
	/**
	 * Loads the document
	 *
	 * @return BS_Install_Document the document
	 */
	protected function doc()
	{
		return new BS_Install_Document();
	}
	
	/**
	 * @return FWS_Template_Handler the template-handler
	 */
	protected function tpl()
	{
		$tpl = parent::tpl();
		
		// replace the URL-class
		$url = new BS_DBA_URL();
		$tpl->add_global_ref('gurl',$url);
		$tpl->remove_allowed_methods('gurl');
		$tpl->add_allowed_method('gurl','build_url');
		
		return $tpl;
	}

	/**
	 * @see BS_PropLoader::db()
	 *
	 * @return FWS_MySQL
	 */
	protected function db()
	{
		return FWS_MySQL::get_instance();
	}
}
?>