<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2016 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

global $menu, $config;
$using_guest_account = false;
$show_console_tab = true;

$oper_mode = api_plugin_hook_function('top_graph_header', OPER_MODE_NATIVE);
if ($oper_mode == OPER_MODE_RESKIN) {
	return;
}

/* ================= input validation ================= */
get_filter_request_var('local_graph_id');
get_filter_request_var('graph_start');
get_filter_request_var('graph_end');
/* ==================================================== */

if (read_config_option('auth_method') != 0) {
	/* at this point this user is good to go... so get some setting about this
	user and put them into variables to save excess SQL in the future */
	$current_user = db_fetch_row_prepared('SELECT * FROM user_auth WHERE id = ?', array($_SESSION['sess_user_id']));

	/* find out if we are logged in as a 'guest user' or not */
	if (db_fetch_cell_prepared('SELECT id FROM user_auth WHERE username = ?', array(read_config_option('guest_user'))) == $_SESSION['sess_user_id']) {
		$using_guest_account = true;
	}

	/* find out if we should show the "console" tab or not, based on this user's permissions */
	if (sizeof(db_fetch_assoc_prepared('SELECT realm_id FROM user_auth_realm WHERE realm_id = 8 AND user_id = ?', array($_SESSION['sess_user_id']))) == 0) {
		$show_console_tab = false;
	}
}

/* need to correct $_SESSION["sess_nav_level_cache"] in zoom view */
if (isset_request_var('action') && get_nfilter_request_var('action') == 'zoom') {
	$_SESSION['sess_nav_level_cache'][2]['url'] = 'graph.php?local_graph_id=' . get_filter_request_var('local_graph_id') . '&rra_id=0';
}

$page_title = api_plugin_hook_function('page_title', draw_navigation_text('title'));

global $graph_views;
load_current_session_value('action', 'sess_cacti_graph_action', $graph_views['2']);

//<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv='X-UA-Compatible' content='IE=edge'>
	<meta content='width=720, initial-scale=0.8, maximum-scale=2.0, minimum-scale=0.5' name='viewport'>
	<title><?php echo $page_title; ?></title>
	<meta http-equiv='Content-Type' content='text/html;charset=utf-8'>
	<link href='<?php echo $config['url_path']; ?>include/themes/<?php print get_selected_theme();?>/main.css' type='text/css' rel='stylesheet'>
	<link href='<?php echo $config['url_path']; ?>include/themes/<?php print get_selected_theme();?>/jquery.zoom.css' type='text/css' rel='stylesheet'>
	<link href='<?php echo $config['url_path']; ?>include/themes/<?php print get_selected_theme();?>/jquery-ui.css' type='text/css' rel='stylesheet'>
	<link href='<?php echo $config['url_path']; ?>include/themes/<?php print get_selected_theme();?>/default/style.css' type='text/css' rel='stylesheet'>
	<link href='<?php echo $config['url_path']; ?>include/themes/<?php print get_selected_theme();?>/jquery.multiselect.css' type='text/css' rel='stylesheet'>
	<link href='<?php echo $config['url_path']; ?>include/themes/<?php print get_selected_theme();?>/jquery.timepicker.css' type='text/css' rel='stylesheet'>
	<link href='<?php echo $config['url_path']; ?>include/themes/<?php print get_selected_theme();?>/jquery.colorpicker.css' type='text/css' rel='stylesheet'>
	<link href='<?php echo $config['url_path']; ?>include/themes/<?php print get_selected_theme();?>/pace.css' type='text/css' rel='stylesheet'>
	<link href='<?php echo $config['url_path']; ?>include/fa/css/font-awesome.css' type='text/css' rel='stylesheet'>
	<link href='<?php echo $config['url_path']; ?>images/favicon.ico' rel='shortcut icon'>
	<link rel='icon' type='image/gif' href='<?php echo $config['url_path']; ?>images/cacti_logo.gif' sizes='96x96'>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery-migrate.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery-ui.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.cookie.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.storageapi.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jstree.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.hotkeys.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.tablednd.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.zoom.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.multiselect.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.multiselect.filter.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.timepicker.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.colorpicker.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.tablesorter.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.metadata.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/jquery.sparkline.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/Chart.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/dygraph-combined.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/js/pace.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/realtime.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/layout.js'></script>
	<script type='text/javascript' src='<?php echo $config['url_path']; ?>include/themes/<?php print get_selected_theme();?>/main.js'></script>
	<?php api_plugin_hook('page_head'); ?>
</head>
<body>
<div id='cactiPageHead' class='cactiPageHead' role='banner'>
	<?php if ($oper_mode == OPER_MODE_NATIVE) { ;?>
	<div id='tabs'><?php html_show_tabs_left(true);?></div>
	<div class='cactiGraphHeaderBackground'><div id='gtabs'><?php print html_graph_tabs_right($current_user);?></div></div>
</div>
<div id='breadCrumbBar' class='breadCrumbBar'>
	<div id='navBar' class='navBar'><?php echo draw_navigation_text();?></div>
	<div class='scrollBar'></div>
	<div class='infoBar'><?php echo draw_login_status($using_guest_account);?></div>
</div>
<div id='cactiContent' class='cactiContent'>
	<?php if (basename($_SERVER['PHP_SELF']) == 'graph_view.php' && (get_nfilter_request_var('action') == 'tree' || (isset_request_var('view_type') && get_nfilter_request_var('view_type') == 'tree'))) { ?>
	<div id='navigation' class='cactiTreeNavigationArea'><?php grow_dhtml_trees();?></div>
	<div id='navigation_right' class='cactiGraphContentArea'>
	<?php }else{ ?>
	<div id='navigation_right' class='cactiGraphContentAreaPreview'>
	<?php } ?>
		<div id='message_container'><?php print display_output_messages();?></div>
		<div style='position:static;' id='main' role='main'>
	<?php } ?>
