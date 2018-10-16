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
        if (!in_array($upd->getModuleDir(), $modules))
            return;

        $default_phonenumber_country = AppConfig::setting("phonenumberformat.country");

        if ($default_phonenumber_country) {


            $default_phonenumber_format = AppConfig::setting("phonenumberformat.format", \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

            $phone_fields = array('phone_office', 'phone_alternate', 'phone_work', 'phone_home', 'phone_mobile', 'phone_other', 'phone_fax');
            $invalid = array();
            foreach ($phone_fields as $phone_field) {
                if ($upd->getField($phone_field)) {
                    try {
                        $NumberProto = $phoneUtil->parse($upd->getField($phone_field), $default_phonenumber_country);
                        $formatted = $phoneUtil->format($NumberProto, $default_phonenumber_format);
                        $upd->set($phone_field, $formatted);
                        if (AppConfig::setting("phonenumberformat.validate")) {
                            if (!$phoneUtil->isValidNumber($NumberProto))
                                $invalid[] = $upd->getField($phone_field);
                        }
                    } catch (Exception $e) {
                        $invalid[] = $upd->getField($phone_field);
                    }
                }
            }
            if (sizeof($invalid))
                add_flash_message(translate("LBL_PHONENUMBER_INVALID") . ': <br> ' . implode(', ', $invalid));
        }
    }

}