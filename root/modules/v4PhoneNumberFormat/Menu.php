<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


global $mod_strings;
global $sugar_version, $sugar_flavor, $server_unique_key, $current_language;

$module_menu = Array(
	Array(
		"index.php?module=Users&action=EditView&return_module=Users&return_action=DetailView",
		translate('LNK_NEW_USER','Administration'),
		"CreateUsers"
	),
	Array(
		"index.php?module=Administration&action=Maintain",
		translate('LBL_MAINTAIN_TITLE','Administration'),
		"Repair"
	),
);


?>
