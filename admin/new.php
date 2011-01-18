<?
/**
 * Create a queXS questionnaire and link it to a LimeSurvey questionnaire
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
 * XHTML functions
 */
include ("../functions/functions.xhtml.php");

/**
 * Input functions
 */
include("../functions/functions.input.php");

/**
 * CKEditor
 */
include("../include/ckeditor/ckeditor.php");

global $db;

xhtml_head(T_("New: Create new questionnaire"),true,false,array("../js/new.js"));

if (isset($_POST['import_file']))
{
	//file has been submitted
	global $db;	

	$ras =0;
	$rws = 0;
	$testing = 0;
	$rs = 0;
	$lime_sid = 0;
	$lime_rs_sid = "NULL";
	if (isset($_POST['ras'])) $ras = 1;
	if (isset($_POST['rws'])) $rws = 1;
	if (isset($_POST['testing'])) $testing = 1;
	if ($_POST['selectrs'] != "none") $rs = 1;
	
	$name = $db->qstr($_POST['description'],get_magic_quotes_gpc());
	$rs_intro = $db->qstr(html_entity_decode($_POST['rs_intro'],get_magic_quotes_gpc()));
	$rs_project_intro = $db->qstr(html_entity_decode($_POST['rs_project_intro'],get_magic_quotes_gpc()));
	$rs_project_end = $db->qstr(html_entity_decode($_POST['rs_project_end'],get_magic_quotes_gpc()));
	$rs_callback = $db->qstr(html_entity_decode($_POST['rs_callback'],get_magic_quotes_gpc()));
	$rs_answeringmachine = $db->qstr(html_entity_decode($_POST['rs_answeringmachine'],get_magic_quotes_gpc()));
	$info  = $db->qstr(html_entity_decode($_POST['info'],get_magic_quotes_gpc()));

	if ($_POST['select'] == "new")
	{
		//create one from scratch
		include_once("../functions/functions.limesurvey.php");
		$lime_sid = create_limesurvey_questionnaire($name);
	}
	else
	{
		//use existing lime instrument
		$lime_sid = bigintval($_POST['select']);
	}

	if ($_POST['selectrs'] == "new")
	{
		//create one from scratch
		include_once("../functions/functions.limesurvey.php");
		$lime_rs_sid = create_limesurvey_questionnaire($db->qstr(T_("Respondent Selection for ") . $_POST['description']),false);
	}
	else if (is_numeric($_POST['selectrs']))
	{
		$lime_rs_sid = bigintval($_POST['selectrs']);
	}

	$sql = "INSERT INTO questionnaire (questionnaire_id,description,lime_sid,restrict_appointments_shifts,restrict_work_shifts,respondent_selection,rs_intro,rs_project_intro,rs_project_end,rs_callback,rs_answeringmachine,testing,lime_rs_sid,info)
		VALUES (NULL,$name,'$lime_sid','$ras','$rws','$rs',$rs_intro,$rs_project_intro,$rs_project_end,$rs_callback,$rs_answeringmachine,'$testing',$lime_rs_sid,$info)";

	$rs = $db->Execute($sql);

	if ($rs)
	{
		$qid = $db->Insert_ID();
		print "<p>" . T_("Successfully inserted") . " $name " . T_("as questionnaire") . " $qid, " . T_("linked to") . " $lime_sid</p>";
		print "<p>" . T_("You must now edit and activate the questionnaire") . "</p>";
	}else
	{
		print "<p>" . T_("Error: Failed to insert questionnaire") . "</p>";
	}

	
}


//create new questionnaire
?>
	<form enctype="multipart/form-data" action="" method="post">
	<p><input type="hidden" name="MAX_FILE_SIZE" value="1000000000" /></p>
	<p><? echo T_("Name for questionnaire:"); ?> <input type="text" name="description"/></p>
	<p><? echo T_("Select creation type:"); ?> <select name="select"><option value="new"><? echo T_("Create new questionnaire in Limesurvey"); ?></option><?
$sql = "SELECT s.sid as sid, sl.surveyls_title AS title
	FROM " . LIME_PREFIX . "surveys AS s
	LEFT JOIN " . LIME_PREFIX . "surveys_languagesettings AS sl ON ( s.sid = sl.surveyls_survey_id
	AND sl.surveyls_language = '" . DEFAULT_LOCALE . "' )
	WHERE s.active = 'Y'";

$surveys = $db->GetAll($sql);

if (!empty($surveys))
{
	foreach($surveys as $s)
	{
		print "<option value=\"{$s['sid']}\">" . T_("Existing questionnaire:") . " {$s['title']}</option>";
	}
}
?></select></p>
	<p><? echo T_("Respondent selection type:"); ?> <select name="selectrs"><option value="none" onclick="hide(this,'rstext');"><? echo T_("No respondent selection (go straight to questionnaire)"); ?></option><option value="old" onclick="show(this,'rstext');" ><? echo T_("Use basic respondent selection text (below)"); ?></option><option onclick="hide(this,'rstext');" value="new"><? echo T_("Create new respondent selection questionnaire in Limesurvey"); ?></option><?
$sql = "SELECT s.sid as sid, sl.surveyls_title AS title
	FROM " . LIME_PREFIX . "surveys AS s
	LEFT JOIN " . LIME_PREFIX . "surveys_languagesettings AS sl ON ( s.sid = sl.surveyls_survey_id
	AND sl.surveyls_language = 'en' )
	WHERE s.active = 'Y'";

$surveys = $db->GetAll($sql);

if (!empty($surveys))
{
	foreach($surveys as $s)
	{
		print "<option onclick=\"hide(this, 'rstext');\"  value=\"{$s['sid']}\">" . T_("Existing questionnaire:") . " {$s['title']}</option>";
	}
}

$CKEditor = new CKEditor();

$ckeditorConfig = array("toolbar" => array(array("tokens","-","Source"),
	array("Cut","Copy","Paste","PasteText","PasteFromWord","-","Print","SpellChecker"),
	array("Undo","Redo","-","Find","Replace","-","SelectAll","RemoveFormat"),
	"/",
	array("Bold","Italic","Underline","Strike","-","Subscript","Superscript"),
	array("NumberedList","BulletedList","-","Outdent","Indent","Blockquote"),
	array('JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'),
	array('BidiLtr', 'BidiRtl'),
	array('Link','Unlink','Anchor'),
	array('Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'),
	"/",
	array('Styles','Format','Font','FontSize'),
	array('TextColor','BGColor'),
	array('About')),
	"extraPlugins" => "tokens");
	

?></select></p>
<p><? echo T_("Restrict appointments to shifts?"); ?> <input name="ras" type="checkbox" checked="checked"/></p>
<p><? echo T_("Restrict work to shifts?"); ?> <input name="rws" type="checkbox" checked="checked"/></p>
<p><? echo T_("Questionnaire for testing only?"); ?> <input name="testing" type="checkbox"/></p>
<div id='rstext' style='display:none;'>
<p><? echo T_("Respondent selection introduction:"); echo $CKEditor->editor("rs_intro","",$ckeditorConfig);?></p>
<p><? echo T_("Respondent selection project introduction:"); echo $CKEditor->editor("rs_project_intro","",$ckeditorConfig);?></p>
<p><? echo T_("Respondent selection callback (already started questionnaire):"); echo $CKEditor->editor("rs_callback","",$ckeditorConfig);?> </p>
<p><? echo T_("Message to leave on an answering machine:"); echo $CKEditor->editor("rs_answeringmachine","",$ckeditorConfig);?> </p>
</div>
<p><? echo T_("Project end text (thank you screen):");echo $CKEditor->editor("rs_project_end","",$ckeditorConfig); ?></p>
<p><? echo T_("Project information for interviewers/operators:");echo $CKEditor->editor("info","",$ckeditorConfig);?></p>
<p><input type="submit" name="import_file" value="<? echo T_("Create Questionnaire"); ?>"/></p>
</form>
<?
xhtml_foot();



?>
