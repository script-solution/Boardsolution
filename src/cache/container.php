<?php
/**
 * Contains the cache-container-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.cache
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The container for Boardsolution
 * 
 * TODO use composite instead of inheritance here!
 *
 * @package			Boardsolution
 * @subpackage	src.cache
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Cache_Container extends PLIB_Cache_Container implements PLIB_Initable
{
	public function __construct()
	{
		$storage = new PLIB_Cache_Storage_DB(BS_TB_CACHE,'table_name','table_content');
		parent::__construct($storage);
		
		// config
		$s = new BS_Cache_Source_Config();
		$this->init_content('config',$s);
		
		$config = $this->get_cache('config');
		if($config->get_element_count() == 0)
		{
			$config->reload();
			$config = $this->get_cache('config');
			if($config->get_element_count() == 0)
				PLIB_Helper::error('The Config-entries are missing',false);
		}
		$cfg_badwords = $config->get_element('badwords_definitions');
		$cfg_bw_repl = $config->get_element('badwords_default_replacement');
		
		// badwords
		$badwords = array();
		$lines = explode("\n",$cfg_badwords);
		foreach($lines as $line)
		{
			$split = explode('=',$line);
			if(isset($split[1]))
			{
				$badwords[] = array(
					'word' => trim($split[0]),
					'replacement' => trim($split[1])
				);
			}
			else
			{
				$badwords[] = array(
					'word' => trim($split[0]),
					'replacement' => $cfg_bw_repl
				);
			}
		}
		$this->set_content('badwords',new PLIB_Array_2Dim($badwords));
		
		// stats
		$s = new BS_Cache_Source_Stats();
		$this->init_content('stats',$s);
		
		// default ones without key
		$defs = array(
			'config' =>				BS_TB_DESIGN,
			'acp_access' =>		BS_TB_ACP_ACCESS,
		);
		foreach($defs as $name => $table)
		{
			$s = new PLIB_Cache_Source_SimpleDB($table,null,null);
			$this->init_content($name,$s);
		}
		
		// default ones with id as key
		$defids = array(
			'banlist' =>			BS_TB_BANS,
			'themes' =>				BS_TB_THEMES,
			'languages' =>		BS_TB_LANGS,
			'bots' =>					BS_TB_BOTS,
			'intern' =>				BS_TB_INTERN,
			'user_groups' =>	BS_TB_USER_GROUPS,
			'tasks' =>				BS_TB_TASKS
		);
		foreach($defids as $name => $table)
		{
			$s = new PLIB_Cache_Source_SimpleDB($table);
			$this->init_content($name,$s);
		}
		
		// user-fields
		$s = new PLIB_Cache_Source_SimpleDB(BS_TB_USER_FIELDS,'id','field_sort','ASC');
		$this->init_content('user_fields',$s);
		
		// user_ranks
		$s = new PLIB_Cache_Source_SimpleDB(BS_TB_RANKS,'id','post_from','ASC');
		$this->init_content('user_ranks',$s);
		
		// moderators
		$s = new BS_Cache_Source_CustomDB(
			"SELECT m.id,m.user_id,m.rid,u.`".BS_EXPORT_USER_NAME."` user_name
			 FROM ".BS_TB_MODS." m
 			 LEFT JOIN ".BS_TB_USER." u ON m.user_id = u.`".BS_EXPORT_USER_ID."`"
		);
		$this->init_content('moderators',$s);
		
		// add theme and language to config
		// TODO keep that?
		$cfg_def_forum_style = $config->get_element('default_forum_style');
		$cfg_def_forum_lang = $config->get_element('default_forum_lang');
		
		$themes = $this->get_cache('themes');
		$theme_data = $themes->get_element($cfg_def_forum_style);
		$config->add_element(
			array('name' => 'theme_folder','value' => $theme_data['theme_folder']),'theme_folder'
		);
	
		$langs = $this->get_cache('languages');
		$lang_data = $langs->get_element($cfg_def_forum_lang);
		$config->add_element(
			array('name' => 'lang_folder','value' => $lang_data['lang_folder']),'lang_folder'
		);
	}
	
	public function init()
	{
		$tid = $this->input->get_var(BS_URL_TID,'get',PLIB_Input::ID);
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
	
		if($tid != null && $fid != null)
		{
			$cache = new PLIB_Array_2Dim();
			$topic_data = BS_DAO::get_topics()->get_topic_for_cache($fid,$tid);
			$cache->add_element($topic_data);
			$this->set_content('topic',$cache);
		}
	}
}
?>