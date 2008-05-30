<?php
/**
 * Contains the list-bbcode-content class
 *
 * @version			$Id: list.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The list-content-implementation.
 * 
 * @package			Boardsolution
 * @subpackage	src.bbcode
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_BBCode_Content_List extends BS_BBCode_Content_Default
{
	public function get_text($inner,$param)
	{
		switch($param)
		{
			case 'circle':
			case 'square':
			case 'disc':
				$result = "\n".'<ul type="'.$param.'">';
				$end = '</ul>';
				break;

			case '1':
			case 'I':
			case 'i':
			case 'A':
			case 'a':
				$result = "\n".'<ol type="'.$param.'">';
				$end = '</ol>';
				break;

			default:
				$result = "\n".'<ul>';
				$end = '</ul>';
				break;
		}
		
		$inner = PLIB_String::substr($inner,PLIB_String::strpos($inner,'[*]') + 3);
		$split = explode('[*]',$inner);
		for($i = 0;$i < count($split);$i++)
			$result .= '	<li>'.$split[$i].'</li>';
		$result .= $end;

		return $result;
	}
}
?>