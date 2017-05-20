<?php
/**
 * Contains the unread-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * The unread-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_unread extends BS_Front_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_breadcrumb($locale->lang('unread_threads'),BS_URL::build_mod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$cfg = FWS_Props::get()->cfg();
		$unread = FWS_Props::get()->unread();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();

		$end = $cfg['threads_per_page'];
		$num = $unread->get_length();

		// collect topic-ids
		$tids = '';
		$unread_topics = $unread->get_unread_topics();
		if(is_array($unread_topics))
			$tids = implode(',',array_keys($unread_topics));

		// display the topics
		$topics = new BS_Front_Topics(
			$locale->lang('unread_threads'),
			' t.id IN ('.$tids.') AND t.moved_tid = 0',
			'lastpost',
			'DESC',
			$end
		);
		$topics->set_total_topic_num($num);
		$topics->set_show_forum(true);
		$topics->set_middle_width(80);
		$topics->add_topics();
		
		$pagination = new BS_Pagination($end,$num);
		$pagination->populate_tpl(BS_URL::get_mod_url());
		
		$rurl = BS_URL::get_mod_url('redirect');
		$rurl->set(BS_URL_LOC,'topic_action');
		
		$jsurl = BS_URL::get_mod_url();
		$jsurl->set(BS_URL_LOC,'read');
		$jsurl->set(BS_URL_MODE,'topics');
		$jsurl->set(BS_URL_ID,'__ID__');
		$jsurl->set(BS_URL_AT,BS_ACTION_CHANGE_READ_STATUS);
		$jsurl->set_sid_policy(BS_URL::SID_FORCE);
		$tpl->add_variables(array(
			'target_url' => $rurl->to_url(),
			'js_url' => $jsurl->to_url(),
		));
	}
}
?>