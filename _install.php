<?php
/**
 * The file for the installation which may be called by the browser
 * 
 * @version			$Id: _install.php 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	main
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

$bspath = '';

// we need some basic files
include_once($bspath.'config/general.php');
include_once($bspath.'config/userdef.php');
include_once($bspath.'config/actions.php');
include_once($bspath.'src/general_functions.php');
include_once($bspath.'src/base.php');
include_once($bspath.'install/install_base.php');

$steps = array(
	'intro',
	'type',
	'config',
	'dbcheck',
	'process',
	'finished'
);

$step = BS_get_input_value('get','step');
if(!$step)
	$step = 0;

if(isset($steps[$step]) && is_file($bspath.'install/modules/'.$steps[$step].'.php'))
{
	include_once($bspath.'install/modules/'.$steps[$step].'.php');
	$class = 'BS_Install_'.$steps[$step];
	if(class_exists($class))
	{
		$c = new $class($bspath,$step);
		$c->display();
	}
}
?>