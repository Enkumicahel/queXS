<?
/**
 * Display the main page including all panels and tabs
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
 * @subpackage user
 * @link http://www.deakin.edu.au/dcarf/ queXS was writen for DCARF - Deakin Computer Assisted Research Facility
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL) Version 2
 * 
 *
 */

/**
 * Configuration file
 */
include ("config.inc.php");

/**
 * XHTML functions
 */
include ("functions/functions.xhtml.php");

/**
 * Operator functions
 */
include("functions/functions.operator.php");

$operator_id = get_operator_id();

if (ALTERNATE_INTERFACE && !is_voip_enabled($operator_id))
{
	include_once("waitnextcase_interface2.php");
	die();
}

$db->StartTrans();

if (isset($_GET['endwork']))
{
	
	if (isset($_GET['note']))
	{
		$case_id = get_case_id($operator_id,false);
		$note = $db->qstr($_GET['note']);
		$sql = "INSERT INTO `case_note` (case_note_id,case_id,operator_id,note,datetime)
			VALUES (NULL,'$case_id','$operator_id',$note,CONVERT_TZ(NOW(),'System','UTC'))";
		$db->Execute($sql);
	}
	end_call_attempt($operator_id);
	end_case($operator_id);

	//if ($db->HasFailedTrans()){ print "<p>FAILED AT ENDWORK</p>";  exit();}
	$db->CompleteTrans();

	include("endwork.php");
	exit();
}


if (isset($_GET['endcase']))
{

	if (isset($_GET['note']))
	{
		$case_id = get_case_id($operator_id,false);
		$note = $db->qstr($_GET['note']);
		$sql = "INSERT INTO `case_note` (case_note_id,case_id,operator_id,note,datetime)
			VALUES (NULL,'$case_id','$operator_id',$note,CONVERT_TZ(NOW(),'System','UTC'))";
		$db->Execute($sql);
	}
	end_call_attempt($operator_id);
	end_case($operator_id);

	$db->CompleteTrans(); //need to complete here otherwise getting the case later will fail

	$db->StartTrans();
	//if ($db->HasFailedTrans()) {print "<p>FAILED AT ENDCASE</p>"; exit();}
}

$js = array("js/popup.js","js/tabber.js","include/jquery-ui/js/jquery-1.4.2.min.js","include/jquery-ui/js/jquery-ui-1.8.2.custom.min.js");
$body = true;
$script = "";
if (AUTO_LOGOUT_MINUTES !== false)
{
	$js[] = "include/nap-1.0.0/js/jquery.nap-1.0.0.js";
	$script = "<script type='text/javascript'>
		   $(document).nap(
			function() { 
				location.replace('" . QUEXS_URL . "?endwork=endwork&auto=auto');
			},
			function() { 
				//do nothing if woken up as shouldn't get here
			},
			" . (AUTO_LOGOUT_MINUTES * 60) . "
		);</script></head><body>";
	$body = false;
}

if (HEADER_EXPANDER) 
{
	$js[] = "js/headerexpand.js";
	$js[] = "js/headerexpandauto.js";
}
else if (HEADER_EXPANDER_MANUAL) 
{
	$js[] = "js/headerexpand.js";
	$js[] = "js/headerexpandmanual.js";
}

xhtml_head(T_("queXS"), $body, array("css/index.css","css/tabber.css","include/jquery-ui/css/smoothness/jquery-ui-1.8.2.custom.css") , $js);
print $script;

?>

<div id="casefunctions" class="header">
	<div class='box'><a href="javascript:poptastic('call.php?end=end');"><? echo T_("End"); ?></a></div>
	<div class='box'><a href="javascript:poptastic('appointment.php');"><? echo T_("Appointment"); ?></a></div>
	<div class='box important'><a href="javascript:poptastic('call.php');"><? echo T_("Call/Hangup"); ?></a></div>
	<div class='box'><a href="javascript:poptastic('supervisor.php');"><? echo T_("Supervisor"); ?></a></div>
	<div class='box' id='recbox'><a id='reclink' class='offline' href="javascript:poptastic('record.php?start=start');"><? echo T_("Start REC"); ?></a></div>
	<? if (HEADER_EXPANDER_MANUAL){ ?> <div class='headerexpand'><img id='headerexpandimage' src='./images/arrow-up-2.png' alt='<? echo T_('Arrow for expanding or contracting'); ?>'/></div> <? } ?>
	<div class='box'><a href='index.php?'><? echo T_("Restart"); ?></a></div>
</div>

<div id="content" class="content">
<? 

$case_id = get_case_id($operator_id,true);
$ca = get_call_attempt($operator_id,true);
$appointment = false;
$availability = is_using_availability($case_id);
if ($ca)
{
	if (is_on_appointment($ca))
	{
		$appointment= true;
	}
}

if (!is_respondent_selection($operator_id))
	$data = get_limesurvey_url($operator_id);
else 
	$data = get_respondentselection_url($operator_id);

xhtml_object($data,"main-content"); 

?>
</div>

<div id="respondent" class="header">
<?xhtml_object("respondent.php","main-respondent");?>
</div>

<div id="qstatus" class="header">
<?xhtml_object("status.php","main-qstatus");?>
</div>


<div id="calllist" class="header">


<div class="tabber" id="tab-main">

<? if (TAB_CASENOTES) { ?>
     <div class="tabbertab <? if ((DEFAULT_TAB == 'casenotes' && !$appointment) || (DEFAULT_TAB_APPOINTMENT == 'casenotes' && $appointment)) 
					print "tabbertabdefault"; ?>">
	  <h2><? echo T_("Notes"); ?></h2>
	  <div id="div-casenotes" class="tabberdiv"><?xhtml_object("casenote.php","main-casenotes");?></div>
   </div>
<? }?>

<? if ($availability) { ?>
     <div class="tabbertab <? if ((DEFAULT_TAB == 'availability' && !$appointment) || (DEFAULT_TAB_APPOINTMENT == 'availability' && $appointment)) 
					print "tabbertabdefault"; ?>">
	  <h2><? echo T_("Availability"); ?></h2>
	  <div id="div-casenotes" class="tabberdiv"><?xhtml_object("availability.php","main-casenotes");?></div>
   </div>
<? }?>



<? if (TAB_CONTACTDETAILS) { ?>
     <div class="tabbertab <? if ((DEFAULT_TAB == 'contactdetails' && !$appointment) || (DEFAULT_TAB_APPOINTMENT == 'contactdetails' && $appointment)) 
					print "tabbertabdefault"; ?>">
	  <h2><? echo T_("Contact details"); ?></h2>
	  <div id="div-contactdetails" class="tabberdiv"><?xhtml_object("contactdetails.php","main-contactdetails");?></div>
   </div>
<? }?>


<? if (TAB_CALLLIST) { ?>
     <div class="tabbertab <? if ((DEFAULT_TAB == 'calllist' && !$appointment) || (DEFAULT_TAB_APPOINTMENT == 'calllist' && $appointment)) 
					print "tabbertabdefault"; ?>">
	  <h2><? echo T_("Call history"); ?></h2>
	  <div id="div-calllist" class="tabberdiv"><?xhtml_object("calllist.php","main-calllist");?></div>
     </div>
<? }?>


<? if (TAB_SHIFTS) { ?>
     <div class="tabbertab <? if ((DEFAULT_TAB == 'shifts' && !$appointment) || (DEFAULT_TAB_APPOINTMENT == 'shifts' && $appointment)) 
					print "tabbertabdefault"; ?>" id="tab-shifts">
	  <h2><? echo T_("Shifts"); ?></h2>
	  <div id="div-shifts" class="tabberdiv"><?xhtml_object("shifts.php","main-shifts");?></div>
     </div>
<? }?>


<? if (TAB_APPOINTMENTLIST) { ?>
     <div class="tabbertab <? if ((DEFAULT_TAB == 'appointmentlist' && !$appointment) || (DEFAULT_TAB_APPOINTMENT == 'appointmentlist' && $appointment)) 
					print "tabbertabdefault"; ?>">
	  <h2><? echo T_("Appointments"); ?></h2>
	  <div id="div-appointmentlist" class="tabberdiv"><?xhtml_object("appointmentlist.php","main-appointmentlist");?></div>
     </div>
<? }?>


<? if (TAB_PERFORMANCE) { ?>
     <div class="tabbertab <? if ((DEFAULT_TAB == 'performance' && !$appointment) || (DEFAULT_TAB_APPOINTMENT == 'performance' && $appointment)) 
					print "tabbertabdefault"; ?>">
	  <h2><? echo T_("Performance"); ?></h2>
	  <div id="div-performance" class="tabberdiv"><?xhtml_object("performance.php","main-performance");?></div>
     </div>
<? }?>

<? if (TAB_CALLHISTORY) { ?>
     <div class="tabbertab <? if ((DEFAULT_TAB == 'callhistory' && !$appointment) || (DEFAULT_TAB_APPOINTMENT == 'callhistory' && $appointment)) 
					print "tabbertabdefault"; ?>">
	  <h2><? echo T_("Work history"); ?></h2>
	  <div id="div-callhistory" class="tabberdiv"><?xhtml_object("callhistory.php","main-callhistory");?></div>
     </div>
<? }?>

<? if (TAB_PROJECTINFO) { ?>
     <div class="tabbertab <? if ((DEFAULT_TAB == 'projectinfo' && !$appointment) || (DEFAULT_TAB_APPOINTMENT == 'projectinfo' && $appointment)) 
					print "tabbertabdefault"; ?>">
	  <h2><? echo T_("Project information"); ?></h2>
	  <div id="div-projectinfo" class="tabberdiv"><?xhtml_object("project_info.php","main-projectinfo");?></div>
     </div>
<? }?>


<? if (TAB_INFO) { ?>
     <div class="tabbertab <? if ((DEFAULT_TAB == 'info' && !$appointment) || (DEFAULT_TAB_APPOINTMENT == 'info' && $appointment)) 
					print "tabbertabdefault"; ?>">
	  <h2><? echo T_("Info"); ?></h2>
	  <div id="div-info" class="tabberdiv"><?xhtml_object("info.php","main-info");?></div>
     </div>
<? }?>


</div>


</div>

<?

xhtml_foot();


	//if ($db->HasFailedTrans()){ print "<p>FAILED AT END of index</p>"; exit();}
$db->CompleteTrans();

?>
