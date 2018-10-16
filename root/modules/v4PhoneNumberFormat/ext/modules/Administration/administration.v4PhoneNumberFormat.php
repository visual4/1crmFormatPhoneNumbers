<?php

//$admin_option_defs = array();
//$admin_option_defs['v4FormatPhoneNumbers'] = array('Calls',array('LBL_ADMIN_FORMAT_PHONENUMBERS_TITLE', 'Administration'),array('LBL_ADMIN_FORMAT_PHONENUMBERS_DESC', 'Administration'),'./index.php?module=v4PhoneNumberFormat&action=FormatPhoneNumbers');
//
//foreach ($admin_group_header as &$group_header) {
//    if ($group_header[0] == "LBL_ADMINISTRATION_HOME_TITLE") {
//        $group_header[3][] = $admin_option_defs['v4FormatPhoneNumbers'];
//    }
//}

$admin_option_defs = array();
$admin_option_defs['Settings'] = array('Calls', array('LBL_ADMIN_FORMAT_PHONENUMBERS_SETTINGS_TITLE', 'Administration'), array('LBL_ADMIN_FORMAT_PHONENUMBERS_SETTINGS_DESC', 'Administration'), './index.php?module=Configurator&action=EditView&layout=PhoneNumberFormat');
$admin_option_defs['Format'] = array('Calls', array('LBL_ADMIN_FORMAT_PHONENUMBERS_FORMAT_TITLE', 'Administration'), array('LBL_ADMIN_FORMAT_PHONENUMBERS_FORMAT_DESC', 'Administration'), './index.php?module=v4PhoneNumberFormat&action=FormatPhoneNumbers');
$admin_group_header[] = array(array('LBL_ADMIN_FORMAT_PHONENUMBERS_TITLE', 'Administration'),'',false,$admin_option_defs);