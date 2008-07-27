<?php
/**
 * Contains the properties-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The properties-class for Boardsolution. It is simply copied just for code-completion :-)
 *
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class PLIB_Props extends PLIB_UtilBase
{
	/**
	 * The property-accessor
	 *
	 * @var BS_PropAccessor
	 */
	private static $_accessor;
	
	/**
	 * @return BS_PropAccessor the property-accessor-instance
	 */
	public static function get()
	{
		return self::$_accessor;
	}
	
	/**
	 * Prints all properties
	 */
	public static function print_all()
	{
		echo '<pre>'.PLIB_PrintUtils::to_string(self::$_accessor->get_all()).'</pre>';
	}
	
	/**
	 * Sets the property-accessor for the properties
	 *
	 * @param PLIB_PropAccessor $accessor the accessor
	 */
	public static function set_accessor($accessor)
	{
		if(!($accessor instanceof PLIB_PropAccessor))
			PLIB_Helper::def_error('instance','accessor','PLIB_PropAccessor',$accessor);
		
		self::$_accessor = $accessor;
	}
}
?>