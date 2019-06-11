<?php


require_once('data/SugarBean.php');
require_once('include/libphonenumber/autoload.php');

class PhoneNumberFormat extends SugarBean
{

    public function before_save(RowUpdate $upd)
    {
        if ($upd->source != "editview")
            return;

        $modules = explode("^,^", AppConfig::setting("phonenumberformat.modules"));
        $allchanges = AppConfig::setting("phonenumberformat.allchanges",0);
        if (!in_array($upd->getModuleDir(), $modules))
            return;

        $countrycode = "";
        $address_fields = array('primary_address_countrycode', 'billing_address_countrycode');
        foreach ($address_fields as $address_field) {
            if (!$countrycode && $upd->getField($address_field)) {
                $countrycode = $upd->getField($address_field);
            }
        }
        if (!$countrycode) {
            $countrycode = AppConfig::setting("phonenumberformat.country");
            $note = true;
        }


        if ($countrycode) {

            $default_phonenumber_format = AppConfig::setting("phonenumberformat.format", \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

            $phone_fields = array('phone_office', 'phone_alternate', 'phone_work', 'phone_home', 'phone_mobile', 'phone_other', 'phone_fax');
            $invalid = $notes = array();
            $changes = $upd->getChanges();
            foreach ($phone_fields as $phone_field) {
                $update = $allchanges ? true : isset($changes[$phone_field]);
                if ($update && $upd->getField($phone_field)) {
                    try {
                        $NumberProto = $phoneUtil->parse($upd->getField($phone_field), $countrycode);
                        $formatted = $phoneUtil->format($NumberProto, $default_phonenumber_format);
                        $upd->set($phone_field, $formatted);
                        if (AppConfig::setting("phonenumberformat.validate")) {
                            if (!$phoneUtil->isValidNumber($NumberProto))
                                $invalid[] = $upd->getField($phone_field);
                        }
                        $notes[] = $upd->getField($phone_field);
                    } catch (Exception $e) {
                        $invalid[] = $upd->getField($phone_field);
                    }
                }
            }

            if ($note && sizeof($notes))
                add_flash_message(sprintf(translate("LBL_PHONENUMBER_COUNTRYCODE_NOT_FOUND"),$countrycode) . ': <br> ' . implode(', ', $notes));

            if (sizeof($invalid))
                add_flash_message(translate("LBL_PHONENUMBER_INVALID") . ': <br> ' . implode(', ', $invalid));

        }
    }

}