<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

function mail_header($params) {
	global $group_id;

	//required for site_project_header
	$params['group']=$group_id;
	$params['toptab']='mail';

	$project=project_get_object($group_id);

	if (!$project->usesMail()) {
		exit_error('Error','This Project Has Turned Off Mailing Lists');
	}


	site_project_header($params);
	echo '
		<P><B><A HREF="/mail/admin/?group_id='.$group_id.'">Admin</A>';
	if ($params['help']) {
	    echo ' | '.help_button($params['help'],false,'Help');
	}
	echo '</B><P>';
}
function mail_header_admin($params) {
	global $group_id;

	//required for site_project_header
	$params['group']=$group_id;
	$params['toptab']='mail';

	$project=project_get_object($group_id);

	if (!$project->usesMail()) {
		exit_error('Error','This Project Has Turned Off Mailing Lists');
	}


	site_project_header($params);
	echo '
		<P><B><A HREF="/mail/admin/?group_id='.$group_id.'">Admin</A></B>
 | <B><A HREF="/mail/admin/?group_id='.$group_id.'&add_list=1">Add List</A></B>
 | <B><A HREF="/mail/admin/?group_id='.$group_id.'&change_status=1">Update List</A></B>
';
	if ($params['help']) {
	    echo ' | <B>'.help_button($params['help'],false,'Help').'</B>';
	}

}

function mail_footer($params) {
	site_project_footer($params);
}

?>
