<?php
/**
 * Contains the standalone-class for the smiley-popup
 * 
 * @version			$Id: smileys.php 751 2008-05-24 15:51:00Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Displays the smiley-popup
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_smileys extends BS_Standalone
{
	public function get_template()
	{
		return 'popup_smileys.htm';
	}
	
	public function run()
	{
		$smileys = array();
		foreach(BS_DAO::get_smileys()->get_all() as $data)
		{
			$primcode = $data['primary_code'];
			$smileys[] = array(
				'smiley_code' => BS_SPACES_AROUND_SMILEYS ? '%20'.$primcode.'%20' : $primcode,
				'display_code' => $primcode,
				'smiley_path' => PLIB_Path::inner().'images/smileys/'.$data['smiley_path']
			);
		}
		
		$this->tpl->add_array('smileys',$smileys);
	}
}
?>