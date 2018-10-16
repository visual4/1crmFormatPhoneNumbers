<?php

require_once('include/libphonenumber/autoload.php');

global $mod_strings,$app_strings;

$def_country = 'DE';
global $app_list_strings;
$countries = $app_list_strings['country_codes'];
$formats = $app_list_strings['phonenumber_formats'];

$def_format = \libphonenumber\PhoneNumberFormat::INTERNATIONAL;
$addr = ListQuery::quick_fetch_key('CompanyAddress', 'main', 1);
if ($addr && $addr_country = $addr->getField('address_country')) {
    $addr_country_code = array_search($addr_country, $countries) ;
    if ($addr_country_code !== false)
        $def_country = $addr_country_code;
}
if (AppConfig::setting("phonenumberformat.country")) {
    $def_country = AppConfig::setting("phonenumberformat.country");
}
if (AppConfig::setting("phonenumberformat.format")) {
    $def_format = AppConfig::setting("phonenumberformat.format");
}

$country = array_get_default($_REQUEST, 'country', $def_country);
$format = array_get_default($_REQUEST, 'format', $def_format);
$do_run = array_get_default($_REQUEST, 'do_run');
$test_run = array_get_default($_REQUEST, 'test_run', $do_run ? 0 : 1) ? 'checked="checked"' : '';


echo get_module_title('v4PhoneNumberFormat', $mod_strings['LBL_NORMALIZE_PHONES'], true);

echo '<div style="max-width: 950px;">';
echo $mod_strings['LBL_NORMALIZE_PHONES_DESC_LONG'];



$options_countries = get_select_options_with_id($countries, $country);
$options_formats = get_select_options_with_id($formats, $format);


$modules = array('Contacts' => 'Contact', 'Leads' => 'Lead', 'Accounts' => 'Account');

if ($do_run) {

    $result = [];
    $count = 0;
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

    $phone_fields = array('phone_office', 'phone_alternate', 'phone_work', 'phone_home', 'phone_mobile', 'phone_other');
    foreach ($modules as $module => $model) {

        $lq = new ListQuery($model);
        $lq->addFields($phone_fields);
        $res = $lq->runQuery(0, null, false, null, 50);
        while (true) {
            foreach ($res->rows as $row_idx => $row) {
                $updates = array();
                foreach ($phone_fields as $f) {
                    if (isset($row[$f]) && strlen($row[$f])) {

                        $modified = $row[$f];
                        try {
                            $NumberProto = $phoneUtil->parse($row[$f], $country);
                            $modified = $phoneUtil->format($NumberProto, $format);
                        } catch (Exception $e) {
                        }

                        $changed = $row[$f] === $modified ? false : true;

                        if ($changed) {
                            $count++;
                            $result[$module][] = ['before' => $row[$f], 'after' => $modified];
                            if (sizeof($result[$module]) >=$rowcount)
                                $rowcount = sizeof($result[$module]);
                        }

                        if ($changed && !$test_run) {
                            $updates[$f] = $modified;
                        }
                    }
                }
                if (!empty($updates)) {
                    $row_result = $res->getRowResult($row_idx);
                    $upd = RowUpdate::for_result($row_result);
                    $upd->update_date_modified = false;
                    $upd->prohibit_workflow = true;
                    $upd->source = "PhoneNumberFormat";
                    $upd->set($updates);
                    $upd->save();
                }
            }
            if ($res->page_finished)
                break;
            $lq->pageResult($res);
        }
    }
}

echo <<<EOF
<form method="POST" action="index.php?module=v4PhoneNumberFormat&action=FormatPhoneNumbers">
	<input type="hidden" name="module" value="v4PhoneNumberFormat">
	<input type="hidden" name="action" value="FormatPhoneNumbers">
	<div class="button-bar form-bottom opaque"><table class="form-buttons" width="100%" border="0"><tbody><tr><td align="left"><button id="ConfigureSettings_cancel2" name="ConfigureSettings_cancel2" type="button" class="form-button group-left group-left input-outer" style="" tabindex="" onclick="return SUGAR.util.loadUrl('index.php?module=Administration&amp;action=index');"><div class="input-icon left icon-cancel"></div><span class="input-label" id="ConfigureSettings_cancel2-label">{$app_strings['LBL_CANCEL_BUTTON_LABEL']}</span></button></td><td align="center"></td><td align="right"></td></tr></tbody></table></div>
	<table class="tabForm" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td class="dataLabel">
				{$mod_strings['LBL_NORMALIZE_PHONES_COUNTRY']}
			</td>
			<td class="dataLabel">
				<select name="country">
					{$options_countries}
				</select>
			</td>
			
		</tr>
		<tr>
		    <td class="dataLabel">
				{$mod_strings['LBL_NORMALIZE_PHONES_FORMAT']}
			</td>
			<td class="dataLabel">
				<select name="format">
					{$options_formats}
				</select>
			</td>
		</tr>
		<tr>
			<td class="dataLabel" colspan="2">
				<input type="checkbox" name="test_run" value="1" {$test_run} > {$mod_strings['LBL_NORMALIZE_PHONES_TEST']}
			</td>
		</tr>
		<tr>
			<td class="dataLabel" colspan="2">
				<input class="input-button input-outer" type="submit" name="do_run" value="{$mod_strings['LBL_NORMALIZE_PHONES_RUN']}">
			</td>
		</tr>
EOF;
if ($do_run) {
    ini_set("memory_limit", "-1");
    ini_set("max_execution_time", "0");
    $label = $test_run ? $mod_strings['LBL_NORMALIZE_PHONES_RESULTS_TEST'] : $mod_strings['LBL_NORMALIZE_PHONES_RESULTS'] ."<br>". sprintf($mod_strings['LBL_NORMALIZE_PHONES_RESULTS_COUNT'],$count) ;
    echo <<<EOF
		<tr>
			<th class="dataLabel" colspan="2">
				<h2 style="text-align:center"><b>{$label}</b></h2>
			</td>
		</tr>
		<tr>
			<td class="dataLabel" colspan="2">
				<table cellpadding="5" cellspacing="1" border="1" width="100%">
					<tr>
EOF;
    foreach ($modules as $k => $v)
        echo '<th colspan="2" class="dataLabel" style="text-align:left">' . $k . '</th>';
    echo '</tr>';
    echo '<tr>';



    foreach ($modules as $k => $v) {
        echo '<th class="dataLabel" style="text-align:left">' . $mod_strings['LBL_NORMALIZE_BEFORE'] . '</th>
            <th class="dataLabel" style="text-align:left">' . $mod_strings['LBL_NORMALIZE_AFTER'] . '</th>';
    }
        echo '</tr>';

        for($i = 0;$i< $rowcount; $i++) {
            echo '<tr>';
            foreach ($modules as $k => $v) {
                echo '<td class="dataLabel">' .$result[$k][$i]['before'] . '</td><td class="dataLabel">' .$result[$k][$i]['after'] . '</td>';
            }
            echo '</tr>';
        }


        echo '</table></td>';


    EOF;
}
echo <<<EOF
	</table>
	<div class="button-bar form-bottom opaque"><table class="form-buttons" width="100%" border="0"><tbody><tr><td align="left"><button id="ConfigureSettings_cancel2" name="ConfigureSettings_cancel2" type="button" class="form-button group-left group-left input-outer" style="" tabindex="" onclick="return SUGAR.util.loadUrl('index.php?module=Administration&amp;action=index');"><div class="input-icon left icon-cancel"></div><span class="input-label" id="ConfigureSettings_cancel2-label">{$app_strings['LBL_CANCEL_BUTTON_LABEL']}</span></button></td><td align="center"></td><td align="right"></td></tr></tbody></table></div>
</form>
EOF;
echo '</div>';

