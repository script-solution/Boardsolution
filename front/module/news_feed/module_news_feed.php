<?php
/**
 * Contains the news-feed-module
 * 
 * @version			$Id: news_feed.php 9 2008-05-30 18:46:42Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Outputs the news in a given feed-syntax.
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_news_feed extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->set_output_enabled(false);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$cfg = PLIB_Props::get()->cfg();
		$input = PLIB_Props::get()->input();
		$doc = PLIB_Props::get()->doc();

		if(!$cfg['enable_news_feeds'])
		{
			$this->report_error();
			return;
		}
		
		$mode = $input->get_var(BS_URL_MODE,'get',PLIB_Input::STRING,array('rss20','atom'),'rss20');
		
		// load feed-generator
		if($mode == 'rss20')
			$rss = new BS_Front_Feed_RSS20();
		else
			$rss = new BS_Front_Feed_Atom();
		
		// output
		$xml = $rss->get_news();
		
		$doc->set_mimetype('application/xml');
		$doc->set_header('Content-Type','application/xml');
		$res = $doc->gzip($xml);
		$doc->send_header();
		echo $res;
	}
}
?>