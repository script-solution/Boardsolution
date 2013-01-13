/**
 * Contains javascript-functions to extend and collaps rows
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
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

function toggleAll(id,rowPrefix,detailsPrefix,imagePrefix)
{
	for(var i = 0;i < id;i++)
		showEntry(rowPrefix + i,detailsPrefix + i,imagePrefix + i);
}

function showEntry(rowId,detailId,imgId)
{
	var row = document.getElementById(rowId);
	var det = document.getElementById(detailId);

	det.style.display = det.style.display == 'none' ? (Browser.isIE ? 'block' : 'table-row') : 'none';

	var border = det.style.display == 'none' ? '0px' : '2px solid #777777';
	var cols = getColumnsOfRow(row);
	for(var i = 0;i < cols.length;i++)
	{
		if(i == 0)
			cols[i].style.borderLeft = border;
		cols[i].style.borderTop = border;
		if(i == cols.length - 1)
			cols[i].style.borderRight = border;
	}
	
	var img = document.getElementById(imgId);
	if(det.style.display == 'none')
		img.src = img.src.replace(/open/,'closed');
	else
		img.src = img.src.replace(/closed/,'open');
}

function getColumnsOfRow(node)
{
	var cols = new Array();
	var a = 0;
	for(var i = 0;i < node.childNodes.length;i++)
	{
		if(node.childNodes[i].style)
			cols[a++] = node.childNodes[i];
	}
	
	return cols;
}