/**
 * Contains javascript-functions to extend and collaps rows
 * 
 * @version			$Id: row_extension.js 543 2008-04-10 07:32:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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

	det.style.display = det.style.display == 'none' ? (document.all && !window.opera ? 'block' : 'table-row') : 'none';

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