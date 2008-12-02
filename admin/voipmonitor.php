<?
/**
 * Run the VoIP monitoring process and monitor it via the database
 *
 *
 *	This file is part of queXS
 *	
 *	queXS is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *	
 *	queXS is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *	
 *	You should have received a copy of the GNU General Public License
 *	along with queXS; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @author Adam Zammit <adam.zammit@deakin.edu.au>
 * @copyright Deakin University 2007,2008
 * @package queXS
 * @subpackage admin
 * @link http://www.deakin.edu.au/dcarf/ queXS was writen for DCARF - Deakin Computer Assisted Research Facility
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL) Version 2
 * 
 */

/**
 * Configuration file
 */
include ("../config.inc.php");

/**
 * Database file
 */
include ("../db.inc.php");

/**
 * Process
 */
include ("../functions/functions.process.php");

/**
 * XHTML functions
 */
include("../functions/functions.xhtml.php");



if (isset($_GET['watch']))
{
	//start watching process
	start_process(realpath(dirname(__FILE__) . "/process.php"));
}

$p = is_process_running();


if ($p)
{
	if (isset($_GET['kill']))
		kill_process($p);

	xhtml_head(T_("Monitor VoIP Process"),true,false,false,false,10,true);

	print "<h1>" . T_("Running process:") . " $p</h1>";

	print "<h2>" . T_("Note: This page will automatically refresh every 10 seconds") . "</h2>";

	if (is_process_killed($p))
		print "<h3>" . T_("Kill signal sent: Please wait... (Note: Process will be stalled until there is activity on the VoIP Server)") . "</h3>";
	else
		print "<p><a href='?kill=kill'>" . T_("Kill the running process") . "</a> ". T_("(requires activity on the VoIP Server to take effect)") . "</p>";

	print process_get_data($p);
}
else
{
	xhtml_head(T_("Monitor VoIP Process"));
	print "<h2>" . T_("Monitor VoIP Process") . "</h2>";
	print "<p><a href='?watch=watch'>" . T_("Click here to begin monitoring the VoIP Process") . "</a></p>";
	print "<h2>" . T_("Outcome of last process run (if any)") . "</h2>";
	print process_get_last_data();
}
xhtml_foot();

?>