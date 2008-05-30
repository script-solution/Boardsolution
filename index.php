<?php
/**
 * The file for the frontend which may be called by the browser
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	main
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * Tries to build a path from the one path to the other
 * 
 * @param string $path1 the first path
 * @param string $path2 the second path
 * @return string the path from $path2 to $path1
 */
function BS_synchronize_path($path1,$path2)
{
	/**
	 * Converts the path to a common format and splits it
	 * 
	 * @param string $path the path
	 * @return array the parts of the path
	 */
	function BS_split_path($path)
	{
		$path = str_replace('\\','/',$path);
		if(isset($_SERVER['DOCUMENT_ROOT']))
			$path = str_replace($_SERVER['DOCUMENT_ROOT'],'',$path);
		$path = preg_replace('/^\/*/','',$path);
		return explode('/',$path);
	}
	$split1 = BS_split_path($path1);
	$split2 = BS_split_path($path2);

	$num1 = count($split1) - 1;
	$num2 = count($split2) - 1;
	unset($split1[$num1]);
	unset($split2[$num2]);

	// walk from the bottom of the source-path back until the base-paths are equal
	$sync = '';
	for($i = $num2 - 1;$i >= 0;$i--)
	{
		if(isset($split1[$i]) && $split1[$i] == $split2[$i])
			break;

		$sync .= '../';
	}

	// now add all remaining parts of the target-path
	for($i++;$i < $num1;$i++)
		$sync .= $split1[$i].'/';

	// if the config-file can't be found the path must be wrong
	// so we guess that the user doesn't include the script (which should be the default-case)
	if(!is_file($sync.'config/general.php'))
		return '';

	return $sync;
}
$bspath = BS_synchronize_path(__FILE__,$_SERVER['PHP_SELF']);

// check if the calculated path is correct
if(!is_file($bspath.'config/general.php'))
{
	die(
		'<div style="font-size: 12px; font-family: verdana, tahoma, arial, helvetica, sans-serif;">
			 <b>Der berechnete / festgelegte Pfad ist nicht korrekt.</b><br />
			 Bitte &uuml;berpr&uuml;fen Sie den Wert von "$bspath" in Zeile 64 der index.php.<br />
			 Legen Sie den Pfad bitte folgenderma&szlig;en manuell fest:
			 <div style="font-family: courier new, monospace; padding: 5px; margin: 2px;
									 background-color: #EBEBEB;">
			 $bspath = \'derPfadZuBoardsolution/\';
			 </div>
			 Wobei "derPfadZuBoardsolution/" ein relativer Pfad sein sollte!<br />
			 <br />
			 <br />
			 <b>The calculated / specified path is not correct.</b><br />
			 Please verify the value of "$bspath" in the index.php in line 64.<br />
			 You can set the path as follows:
			 <div style="font-family: courier new, monospace; padding: 5px; margin: 2px;
									 background-color: #EBEBEB;">
			 $bspath = \'thePathToBoardsolution/\';
			 </div>
			 Note that "thePathToBoardsolution/" should be a relative path!
		 </div>'
	);
}

// Not yet installed?
if(!is_file($bspath.'config/mysql.php'))
{
	header('Location: '.$bspath.'install.php');
	exit;
}

// does the install.php exist?
if(is_file($bspath.'install.php'))
{
	die(
		'<center><b>Bitte l&ouml;schen Sie zun&auml;chst die install.php!<br />
			Please delete the install.php first!</b></center>'
	);
}


include_once($bspath.'config/userdef.php');

// define libpath for init.php
define('PLIB_PATH',BS_LIB_PATH);

// init the library
include_once(BS_LIB_PATH.'init.php');

// if somebody specifies "bspath" via GET/POST/... the init.php would delete it
if(!isset($bspath))
	PLIB_Helper::error('Invalid request!');

// set the path
PLIB_Path::set_inner($bspath);

// init the autoloader
include_once(PLIB_Path::inner().'src/autoloader.php');
PLIB_AutoLoader::register_loader('BS_Autoloader');

// ok, now show the page
new BS_Front_Page();
?>