<?php return; ?>

fields
    default_phonenumber_country
        config: phonenumberformat.country
        vname: LBL_DEFAULT_PHONENUMBER_COUNTRY
        type: enum
        options: country_codes
    default_phonenumber_format
        config: phonenumberformat.format
        vname: LBL_DEFAULT_PHONENUMBER_FORMAT
        type: enum
        options_add_blank: false
        options: phonenumber_formats
    default_phonenumber_validate
        config: phonenumberformat.validate
        vname: LBL_DEFAULT_PHONENUMBER_VALIDATE
        type: bool
        default: 0
    phonenumber_format_modules
        config: phonenumberformat.modules
        vname: LBL_PHONENUMBER_FORMAT_MODULES
        type: multienum
        options: phonenumber_modules_dom
