<?php
/**
 * Contains the community-manager-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The community-mananger. Contains the import-/export-implementations and provides methods
 * to work with them (fire events, check wether the community is imported/exported, ...).
 * 
 * @package			Boardsolution
 * @subpackage	src.community
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Community_Manager extends FWS_Singleton
{
	/**
	 * @return BS_Community_Manager the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * The export-implementation
	 *
	 * @var BS_Community_Export
	 */
	private $_export = null;
	
	/**
	 * The import-implementation
	 *
	 * @var BS_Community_Import
	 */
	private $_import = null;
	
	/**
	 * @return boolean true if the community is imported
	 */
	public function is_imported()
	{
		return $this->_import !== null;
	}
	
	/**
	 * @return boolean true if the community is exported
	 */
	public function is_exported()
	{
		return $this->_export !== null;
	}
	
	/**
	 * Registers the given import-implementation.
	 *
	 * @param BS_Community_Import $import the import-implementation
	 */
	public function register_import($import)
	{
		if(!($import instanceof BS_Community_Import))
			FWS_Helper::def_error('instance','import','BS_Community_Import',$import);
		if($this->_export !== null)
			FWS_Helper::error('You can\'t export and import the community at the same time!');
		
		$this->_import = $import;
	}
	
	/**
	 * Registers the given export-implementation.
	 *
	 * @param BS_Community_Export $export the export-implementation
	 */
	public function register_export($export)
	{
		if(!($export instanceof BS_Community_Export))
			FWS_Helper::def_error('instance','export','BS_Community_Export',$export);
		if($this->_import !== null)
			FWS_Helper::error('You can\'t export and import the community at the same time!');
		
		$this->_export = $export;
	}
	
	/**
	 * Fires the 'user-registered'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_registered($user)
	{
		if($this->_export !== null)
			$this->_export->user_registered($user);
	}
	
	/**
	 * Fires the 'login'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_login($user)
	{
		if($this->_export !== null)
			$this->_export->user_login($user);
	}
	
	/**
	 * Fires the 'logout'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_logout($user)
	{
		if($this->_export !== null)
			$this->_export->user_logout($user);
	}
	
	/**
	 * Fires the 'user-reactivated'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_reactivated($user)
	{
		if($this->_export !== null)
			$this->_export->user_reactivated($user);
	}
	
	/**
	 * Fires the 'user-deactivated'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_deactivated($user)
	{
		if($this->_export !== null)
			$this->_export->user_deactivated($user);
	}
	
	/**
	 * Fires the 'user-changed'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_changed($user)
	{
		if($this->_export !== null)
			$this->_export->user_changed($user);
	}
	
	/**
	 * Fires the 'user-deleted'-event for the given user
	 *
	 * @param BS_Community_User $user the user-data
	 */
	public function fire_user_deleted($user)
	{
		if($this->_export !== null)
			$this->_export->user_deleted($user);
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>