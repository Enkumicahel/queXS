<?
/**
 * Respondent selection - Project End due to full quota
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
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2009
 * @package queXS
 * @subpackage user
 * @link http://www.acspri.org.au/ queXS was writen for ACSPRI
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL) Version 2
 * 
 */

/**
 * Configuration file
 */
include ("config.inc.php");

/**
 * Database file
 */
include ("db.inc.php");

/**
 * XHTML functions
 */
include ("functions/functions.xhtml.php");

/**
 * Operator functions
 */
include ("functions/functions.operator.php");

$js = array("js/popup.js","include/jquery-ui/js/jquery-1.4.2.min.js","include/jquery-ui/js/jquery-ui-1.8.2.custom.min.js");

if (AUTO_LOGOUT_MINUTES !== false)
{  
        $js[] = "js/childnap.js";
}



xhtml_head(T_("Respondent Selection - Project Quota End"),true,array("css/rs.css","include/jquery-ui/css/smoothness/jquery-ui-1.8.2.custom.css"), $js);

$operator_id = get_operator_id();
$case_id = get_case_id($operator_id);
$questionnaire_id = get_questionnaire_id($operator_id);

print "<p class='rstext'>" . template_replace($_GET['message'],$operator_id,$case_id) . "</p>";

if (ALTERNATE_INTERFACE && !is_voip_enabled($operator_id))
{
?>
<p class='rsoption'><a href="javascript:parent.location.href = 'index_interface2.php?outcome=32&endcase=endcase'"><? echo T_("End call with outcome: Quota filled"); ?></a></p>
<?
} 
else
{
?>
<p class='rsoption'><a href="javascript:parent.poptastic('call.php?defaultoutcome=32');"><? echo T_("End call with outcome: Quota filled"); ?></a></p>
<?
}

xhtml_foot();

?>
