<?php
/**
 * Contains the standalone-class for the news-feed
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Outputs the news in a given feed-syntax.
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_news_feed extends BS_Standalone
{
	public function run()
	{
		if(!$this->cfg['enable_news_feeds'])
			die($this->locale->lang('invalid_page'));
		
		$mode = $this->input->get_var(BS_URL_MODE,'get',PLIB_Input::STRING,array('rss20','atom'),'rss20');
		
		// load feed-generator
		if($mode == 'rss20')
			$rss = new BS_Front_Feed_RSS20();
		else
			$rss = new BS_Front_Feed_Atom();
		
		// output
		$xml = $rss->get_news();
		header('Content-type: application/xml');
		echo $xml;
	}
	
	public function require_board_access()
	{
		return false;
	}
}
?>