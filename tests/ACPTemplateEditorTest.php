<?php
/**
 * Unittest
 * 
 * @package			test
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

class ACPTemplateEditorTest extends BaseTest
{
  function testTplEditor()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_10");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=default");
    $this->waitForPageToLoad("30000");
    $this->click("link=templates");
    $this->waitForPageToLoad("30000");
    $this->click("//img[@alt='Editieren']");
    $this->waitForPageToLoad("30000");
    $this->type("taedit", "{include \"inc_header.htm\"}\n\n{if action_result < 1}\n\n<div>\n	<div style=\"float: left; width: 50%;\">\n	<form method=\"get\" action=\"{target}\">\n	{loop hidden_fields as k => v}\n	<input type=\"hidden\" name=\"{k}\" value=\"{v}\" />\n	{endloop}\n	{month_combo} {year_combo}\n	<input type=\"submit\" value=\"Go!\" />\n	</form>\n	</div>\n	\n	<div style=\"float: right; width: 49%;\">\n	{if view_add_event}\n	<a class=\"bs_button_big\" href=\"{add_event_url}\">{glocale.lang('add_event')}</a>\n	{endif}\n	</div>\n	<br clear=\"all\" />\n</div>\n\n<div class=\"bs_padtop\">\n	<div class=\"bs_calendar_left\">\n		{loop months as month}\n		<div class=\"bs_border\" style=\"margin-bottom: 0.5em;\">\n			<h1 class=\"bs_topic\"><a href=\"{month:url}\">{month:title}</a></h1>\n			<table width=\"100%\" class=\"bs_main\" cellpadding=\"0\" cellspacing=\"2\">\n				<tr>\n					<td width=\"8\" align=\"center\" class=\"bs_main\"></td>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:1}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:2}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:3}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:4}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:5}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:6}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:0}</th>\n				</tr>\n				{loop month:weeks as week}\n				<tr>\n					<td width=\"8\" align=\"center\" class=\"bs_main\">\n					<a href=\"{week:url}\">&raquo;</a>\n					</td>\n					{loop week:days as day}\n					<td width=\"8\" align=\"center\" class=\"{day:class}\">{day:days}</td>\n					{endloop}\n				</tr>\n				{endloop}\n			</table>\n		</div>\n		{endloop}\n	</div>\n	\n	<div class=\"bs_calendar_right\">\n		<div style=\"padding-left: 1.0em;\">\n			{include submoduletpl}\n		</div>\n	</div>\n	<br style=\"clear: both;\" />\n</div>\n\n{endif}\n{endif}\n\n{include \"inc_footer.htm\"}");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("{include \"inc_header.htm\"}\n\n{if action_result < 1}\n\n<div>\n	<div style=\"float: left; width: 50%;\">\n	<form method=\"get\" action=\"{target}\">\n	{loop hidden_fields as k => v}\n	<input type=\"hidden\" name=\"{k}\" value=\"{v}\" />\n	{endloop}\n	{month_combo} {year_combo}\n	<input type=\"submit\" value=\"Go!\" />\n	</form>\n	</div>\n	\n	<div style=\"float: right; width: 49%;\">\n	{if view_add_event}\n	<a class=\"bs_button_big\" href=\"{add_event_url}\">{glocale.lang('add_event')}</a>\n	{endif}\n	</div>\n	<br clear=\"all\" />\n</div>\n\n<div class=\"bs_padtop\">\n	<div class=\"bs_calendar_left\">\n		{loop months as month}\n		<div class=\"bs_border\" style=\"margin-bottom: 0.5em;\">\n			<h1 class=\"bs_topic\"><a href=\"{month:url}\">{month:title}</a></h1>\n			<table width=\"100%\" class=\"bs_main\" cellpadding=\"0\" cellspacing=\"2\">\n				<tr>\n					<td width=\"8\" align=\"center\" class=\"bs_main\"></td>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:1}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:2}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:3}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:4}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:5}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:6}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:0}</th>\n				</tr>\n				{loop month:weeks as week}\n				<tr>\n					<td width=\"8\" align=\"center\" class=\"bs_main\">\n					<a href=\"{week:url}\">&raquo;</a>\n					</td>\n					{loop week:days as day}\n					<td width=\"8\" align=\"center\" class=\"{day:class}\">{day:days}</td>\n					{endloop}\n				</tr>\n				{endloop}\n			</table>\n		</div>\n		{endloop}\n	</div>\n	\n	<div class=\"bs_calendar_right\">\n		<div style=\"padding-left: 1.0em;\">\n			{include submoduletpl}\n		</div>\n	</div>\n	<br style=\"clear: both;\" />\n</div>\n\n{endif}\n{endif}\n\n{include \"inc_footer.htm\"}", $this->getValue("taedit"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("taedit", "{include \"inc_header.htm\"}\n\n{if action_result < 1}\n{if module_error == false}\n\n<div>\n	<div style=\"float: left; width: 50%;\">\n	<form method=\"get\" action=\"{target}\">\n	{loop hidden_fields as k => v}\n	<input type=\"hidden\" name=\"{k}\" value=\"{v}\" />\n	{endloop}\n	{month_combo} {year_combo}\n	<input type=\"submit\" value=\"Go!\" />\n	</form>\n	</div>\n	\n	<div style=\"float: right; width: 49%;\">\n	{if view_add_event}\n	<a class=\"bs_button_big\" href=\"{add_event_url}\">{glocale.lang('add_event')}</a>\n	{endif}\n	</div>\n	<br clear=\"all\" />\n</div>\n\n<div class=\"bs_padtop\">\n	<div class=\"bs_calendar_left\">\n		{loop months as month}\n		<div class=\"bs_border\" style=\"margin-bottom: 0.5em;\">\n			<h1 class=\"bs_topic\"><a href=\"{month:url}\">{month:title}</a></h1>\n			<table width=\"100%\" class=\"bs_main\" cellpadding=\"0\" cellspacing=\"2\">\n				<tr>\n					<td width=\"8\" align=\"center\" class=\"bs_main\"></td>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:1}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:2}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:3}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:4}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:5}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:6}</th>\n					<th width=\"8\" class=\"bs_coldesc\">{wd_short:0}</th>\n				</tr>\n				{loop month:weeks as week}\n				<tr>\n					<td width=\"8\" align=\"center\" class=\"bs_main\">\n					<a href=\"{week:url}\">&raquo;</a>\n					</td>\n					{loop week:days as day}\n					<td width=\"8\" align=\"center\" class=\"{day:class}\">{day:days}</td>\n					{endloop}\n				</tr>\n				{endloop}\n			</table>\n		</div>\n		{endloop}\n	</div>\n	\n	<div class=\"bs_calendar_right\">\n		<div style=\"padding-left: 1.0em;\">\n			{include submoduletpl}\n		</div>\n	</div>\n	<br style=\"clear: both;\" />\n</div>\n\n{endif}\n{endif}\n\n{include \"inc_footer.htm\"}");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    $this->click("link=themes");
    $this->waitForPageToLoad("30000");
  }
}
?>