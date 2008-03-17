<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// 

require_once('pre.php');    

$request =& HTTPRequest::instance();

$Language->loadLanguageMsg('admin/admin');

session_require(array('group'=>'1','admin_flags'=>'A'));

$src = $title = '';

switch($request->get('tool')) {
    case 'munin':
        $title = 'munin';
        $src   = 'munin/';
        break;
    case 'phpMyAdmin':
        $title = 'phpMyAdmin';
        $src   = 'phpMyAdmin/';
        break;
    case 'info':
        $title = 'PHP info';
        $src   = 'info.php';
        break;
    default:
        break;
}

$HTML->header(array('title'=>$title));

if ($src) {
    echo '<h1>'. $title .'</h1>';
    $HTML->iframe('/'. $src, array('class' => 'iframe_service'));
}
$HTML->footer(array());
?>
