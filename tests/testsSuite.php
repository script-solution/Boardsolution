<?php
/**
 * Contains the testsuite
 *
 * @version			$Id: testsSuite.php 31 2008-08-17 19:55:54Z nasmussen $
 * @package			FrameWorkSolution
 * @subpackage	tests
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

define('BS_PATH','../');
include_once(BS_PATH.'extern/bs_api.php');

/**
 * The autoloader for the test-cases
 * 
 * @param string $item the item to load
 * @return boolean wether the file has been loaded
 */
function BS_UnitTest_autoloader($item)
{
	if(FWS_String::ends_with($item,'Test'))
	{
		$path = FWS_Path::server_app().'tests/'.$item.'.php';
		if(is_file($path))
		{
			include($path);
			return true;
		}
	}
	
	return false;
}

FWS_AutoLoader::register_loader('BS_UnitTest_autoloader');

/**
 * Static test suite.
 * 
 * @package			FrameWorkSolution
 * @subpackage	tests
 * @author			Nils Asmussen <nils@script-solution.de>
 */
class testsSuite extends PHPUnit_Framework_TestSuite
{
	private $_logger;
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct()
	{
    $this->setName('testsSuite');
    $filter = array(
    	'BaseTest.php',
    	'InstallTest.php'
    );
    $this->addTestSuite('InstallTest');
    /*$this->addTestSuite('ReplyTest');*/
    foreach(FWS_FileUtils::get_list('./',false,false) as $item)
    {
    	if(!in_array($item,$filter) && preg_match('/^.*?Test\.php$/',$item))
    	{
    		$name = FWS_FileUtils::get_name($item,false);
    		$this->addTestSuite($name);
    	}
    }
	}
	
	protected function tearDown()
	{
		$db = FWS_Props::get()->db();
		$qry = $db->sql_qry('SELECT * FROM '.BS_TB_LOG_ERRORS);
		if($db->sql_num_rows($qry))
		{
			echo "\n*** Errors occurred: ***\n";
			while($row = $db->sql_fetch_assoc($qry))
			{
				echo '"'.$row['message'].'" @ '.$row['query'].':'."\n";
				foreach(explode("\n",$row['backtrace']) as $bt)
					echo "\t".$bt."\n";
				echo "\n";
			}
		}
		$db->sql_free($qry);
	}
	
	/**
	 * We overwrite this method to autoload the class
	 * 
	 * @param string $name the class-name
	 */
	public function addTestSuite($name)
	{
		new $name();
		parent::addTestSuite($name);
	}

	/**
	 * Creates the suite.
	 */
	public static function suite()
	{
		$suite = new self();
		return $suite;
	}
}
?>