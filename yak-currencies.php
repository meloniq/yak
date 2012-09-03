<?php

define('SELECTED_CURRENCY', 'yak_selected_currency');
define('SYMBOL_BEFORE_FORMAT', '%2$s%1$s');
define('SYMBOL_AFTER_FORMAT', '%1$s%2$s');

global $currencies;
$currencies = array();

// format, thousands separator, decimal point, decimal places, symbol, name
$currencies['AFN'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "؋", "AFN - Afghan afghani (؋)");
$currencies['ALL'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 2, "L", "ALL - Albanian Lek (L)");
$currencies['AMD'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "դր.", "AMD - Armenian Dram (դր.)");
$currencies['ANG'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "ƒ", "ANG - Netherlands Antillean guilder (ƒ)");
$currencies['AOA'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Kz", "AOA - Angolan kwanza (Kz)");
$currencies['ARS'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "$", "ARS - Argentine Peso ($)");
$currencies['AUD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "AUD - Australian Dollar ($)");
$currencies['AWG'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "ƒ", "AWG - Aruban Florin (ƒ)");
$currencies['BAM'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 2, "KM", "BAM - Bosnia and Herzegovina convertible mark (KM)");
$currencies['BBD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "BBD - Barbadian Dollar ($)");
$currencies['BDT'] = array(SYMBOL_AFTER_FORMAT, ",", ".", 2, "৳", "BDT - Bangladeshi Taka (৳)");
$currencies['BGN'] = array(SYMBOL_AFTER_FORMAT, "", ".", 2, "лв", "BGN - Bulgarian Lev (лв)");
$currencies['BHD'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, ".د.ب", "BHD - Bahraini Dinar (.د.ب)");
$currencies['BIF'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Fr", "BIF - Burundian Franc (Fr)");
$currencies['BMD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "BMD - Bermudian Dollar ($)");
$currencies['BND'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "BND - Brunei Dollar ($)");
$currencies['BOB'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "\$b", "BOB - Bolivian Boliviano (\$b)");
$currencies['BRL'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "R$", "BRL - Brazilian Real (R$)");
$currencies['BSD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "BSD - Bahamian Dollar ($)");
$currencies['BTN'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Nu.", "BTN - Bhutanese ngultrum (Nu.)");
$currencies['BWP'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "P", "BWP - Botswana Pula (P)");
$currencies['BYR'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 2, "p.", "BYR - Belarusian Ruble (p.)");
$currencies['BZD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "BZD - Belize Dollar ($)");
$currencies['CAD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "CAD - Canadian Dollar ($)");
$currencies['CDF'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Fr", "CDF - Congolese Franc (Fr)");
$currencies['CHF'] = array(SYMBOL_BEFORE_FORMAT, "'", ".", 2, "SFr", "CHF - Swiss Franc (SFr)");
$currencies['CLP'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 0, "$", "CLP - Chilean Peso ($)");
$currencies['CNY'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "¥", "CNY - Chinese Yen (¥)");
$currencies['COP'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "$", "COP - Columbian Peso ($)");
$currencies['CRC'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "₡", "CRC - Costa Rican colón (₡)");
$currencies['CUC'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "CUC - Cuban Peso ($)");
$currencies['CZK'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 2, "Kč", "CZK - Czech Koruna (Kč)");
$currencies['DJF'] = array(SYMBOL_BEFORE_FORMAT, "", "", 0, "Fr", "DJF - Djiboutian franc (Fr)");
$currencies['DKK'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "kr", "DKK - Danish Krone (kr)");
$currencies['DOP'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "DOP - Dominican Peso ($)");
$currencies['DZD'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "د.ج", "DZD - Algerian Dinar (د.ج)");
$currencies['EEK'] = array(SYMBOL_AFTER_FORMAT, ",", ".", 2, "kr", "EEK - Estonian Kroon (kr)");
$currencies['EGP'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "ج.م", "EGP - Egyptian Pound (ج.م)");
$currencies['ERN'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 2, "Nfk", "Nfk - Eritrean Nakfa (Nfk)");
$currencies['ETB'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 2, "Br", "ETB - Ethiopian Birr (Br)");
$currencies['EUR'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "€", "EUR - Euro (€)");
$currencies['FJD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "FJD - Fijian Dollar ($)");
$currencies['FKP'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "£", "FKP - Falkland Islands Pound (£)");
$currencies['GBP'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "£", "GBP - Great British Pound (£)");
$currencies['GEL'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "ლ", "GEL - Georgian Lari (ლ)");
$currencies['GHS'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 2, "₵", "GHS - Ghana Cedi (₵)");
$currencies['GIP'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "£", "GIP - Gilbraltar Pound (£)");
$currencies['GMD'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 2, "D", "GMD - Gambian Dalasi (D)");
$currencies['GNF'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "Fr", "GNF - Guinean Franc (Fr)");
$currencies['GTQ'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Q", "GTQ - Guatemalan Quetzal (Q)");
$currencies['GYD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "GYD - Guyanese Dollar ($)");
$currencies['HKD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "HKD - Hong Kong Dollar ($)");
$currencies['HNL'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "L", "HNL - Honduran Lempira (L)");
$currencies['HRK'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 2, "kn", "HRK - Croatian Kuna (kn)");
$currencies['HTG'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "G", "HTG - Haitian Gourde (G)");
$currencies['HUF'] = array(SYMBOL_AFTER_FORMAT, " ", ",", 0, "Ft", "HUF - Hungarian Forint (Ft)");
$currencies['IDR'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 0, "Rp", "IDR - Indonesian Rupiah (Rp)");
$currencies['ILS'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₪", "ILS - Israeli Shekel (₪)");
$currencies['INR'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Rs", "INR - Indian Rupee (Rs)"); //#,##,###.##
$currencies['IQD'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 3, "د.ع", "IQD - Iraqi Dinar (د.ع)");
$currencies['IRR'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "﷼", "IRR - Iranian Dinar (﷼)");
$currencies['ISK'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 0, "kr.", "ISK - Icelandic króna (kr.)");
$currencies['JMD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "JMD - Jamaican Dollar ($)");
$currencies['JOD'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "د.ا", "JOD - Jordanian Dinar (د.ا)");
$currencies['JPY'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 0, "¥", "JPY - Japanese Yen (¥)");
$currencies['KES'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Sh", "KES - Kenyan Shilling (Sh)");
$currencies['KGS'] = array(SYMBOL_AFTER_FORMAT, " ", "-", 2, "лв", "KGS - Kyrgyzstani Som (лв)");
$currencies['MRO'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 2, "UM", "MRO - Mauritanian Ouguiya (UM)");
$currencies['KHR'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 2, "៛", "KHR - Cambodian Riel (៛)");
$currencies['KMF'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 0, "Fr", "KMF - Comorian Franc (Fr)");
$currencies['KPW'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₩", "KPW - North Korean Won (₩)");
$currencies['KPW'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₩", "KRW - South Korean Won (₩)");
$currencies['KWD'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "د.ك", "KWD - Kuwaiti Dinar (د.ك)");
$currencies['KYD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "KYD - Cayman Islands Dollar ($)");
$currencies['KZT'] = array(SYMBOL_BEFORE_FORMAT, " ", "-", 2, "₸", "KZT - Kazakhstani Tenge (₸)");
$currencies['LAK'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₭", "LAK - Lao Kip (₭)");
$currencies['LBP'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "ل.ل", "LBP - Lebanese Pound (ل.ل)");
$currencies['LKR'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Rs", "LKR - Sri Lankan Rupee (Rs)");
$currencies['LRD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "LRD - Liberian Dollar ($)");
$currencies['LSL'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "L", "LSL - Lesotho Loti (L)");
$currencies['LTL'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 2, "Lt", "LTL - Lithuanian Litas (Lt)");
$currencies['LVL'] = array(SYMBOL_BEFORE_FORMAT, " ", ",", 2, "Ls", "LVL - Latvian Lats (Ls)");
$currencies['LYD'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 3, "ل.د", "LYD - Libyan Dinar (ل.د)");
$currencies['MAD'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "د.م.", "MAD - Moroccan Dirham (د.م.)");
$currencies['MDL'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "L", "MDL - Moldovan Leu (L)");
$currencies['MGA'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 0, "Ar", "MGA - Malagasy Ariary (Ar)");
$currencies['MKD'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 2, "ден", "MKD - Macedonian Denar (ден)");
$currencies['MMK'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "K", "MMK - Burmese Kyat (K)");
$currencies['MNT'] = array(SYMBOL_AFTER_FORMAT, " ", ",", 2, "₮", "MNT - Mongolian Tögrög (₮)");
$currencies['MOP'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "P", "MOP - Macanese Pataca (P)");
$currencies['MRO'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "UM", "MRO - Mauritanian Ouguiya (UM)");
$currencies['MUR'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₨", "MUR - Mauritian Rupee (₨)");
$currencies['MVR'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "MVR", "MVR - Maldavian Rufiyaa (MVR)");
$currencies['MWK'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "MK", "MWK - Malawian Kwacha (MK)");
$currencies['MXN'] = array(SYMBOL_BEFORE_FORMAT, "'", ".", 2, "$", "MXN - Mexican Peso ($)", 'yak_mex_format'); 
$currencies['MYR'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 0, "RM", "MYR - Malaysian Ringgit (RM)");
$currencies['MZN'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "MT", "MZN - Mozambican Metical (MT)");
$currencies['NAD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "NAD - Namibian Dollar ($)");
$currencies['NGN'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₦", "NGN - Nigerian Naira (₦)");
$currencies['NIO'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "C$", "NIO - Nicaraguan Córdoba (C$)");//C$ 123456,789.00
$currencies['NOK'] = array(SYMBOL_BEFORE_FORMAT, " ", ",", 2, "kr", "NOK - Norwegian Krone (kr)");
$currencies['NPR'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₨", "NPR - Nepalese Rupee (₨)");
$currencies['NZD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "NZD - New Zealand Dollar ($)");
$currencies['OMR'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "ر.ع.", "OMR - Omani Rial (ر.ع.)");
$currencies['PAB'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "B/.", "PAB - Panamanian Balboa (B/.)");
$currencies['PEN'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "S/.", "PEN - Peruvian Nuevo Sol (S/.)");
$currencies['PGK'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "K", "PKG Papua New Guinean Kina (K)");
$currencies['PHP'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₱", "PHP - Philippine Peso (₱)");
$currencies['PKR'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₨", "PKR - Pakistani Rupee (₨)");
$currencies['PLN'] = array(SYMBOL_AFTER_FORMAT, " ", ",", 2, "zł", "PLN - Polish Złoty (zł)");
$currencies['PYG'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₲", "PYG - Paraguayan Guaraní (₲)");
$currencies['QAR'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "ر.ق", "QAR - Qatari Riyal (ر.ق)");
$currencies['RON'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "L", "RON - Romanian Leu (L)");
$currencies['RSD'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 2, "дин", "RSD - Serbian Dinar (дин)");
$currencies['RUB'] = array(SYMBOL_BEFORE_FORMAT, " ", ",", 2, "руб", "RUB - Russian Ruble (руб)");
$currencies['RWF'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "Fr", "RWF - Rwandan Franc (Fr)");
$currencies['SAR'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "ر.س", "SAR - Saudi Riyal (ر.س)");
$currencies['SBD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "SBD - Solomon Islands Dollar (SBD)");
$currencies['SCR'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₨", "SCR - Seychellois Rupee (₨)");
$currencies['SDG'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "£", "SDG - Sudanese Pound (£)");
$currencies['SEK'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 2, "kr", "SEK - Swedish Krona (kr)");
$currencies['SHP'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "£", "SHP - Saint Helena Pound (£)");
$currencies['SLL'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 2, "Le", "SLL - Sierra Leonean Leone (Le)");
$currencies['SOS'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Sh", "SOS - Somali Shilling (Sh)");
$currencies['SRD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "SRD - Surinamese Dollar ($)");
$currencies['STD'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 2, "Db", "STD - São Tomé and Príncipe Dobra (Db)");
$currencies['SVC'] = array(SYMBOL_BEFORE_FORMAT, "", ".", 2, "₡", "SVC - Salvadoran Colón (₡)");
$currencies['SYP'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "ل.س", "SYP - Syrian Pound (ل.س)");
$currencies['SZL'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "L", "SZL - Swazi Lilangeni (L)");
$currencies['THB'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "฿", "THB - Thai Baht (฿)");
$currencies['TJS'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "SM", "TJS - Tajikistani Somoni (SM)");
$currencies['TMT'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "m", "TMT - Turkmenistani Manat (m)");
$currencies['TND'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "د.ت", "TND - Tunisian Dinar (د.ت)");
$currencies['TOP'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "T$", "TOP - Tongan Paʻanga (T$)");
$currencies['TRY'] = array(SYMBOL_AFTER_FORMAT, ".", ",", 2, "TL", "TRY - Turkish Lira (TL)");
$currencies['TTD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "TT$", "TTD - Trinidad and Tobago Dollar (TT$)");
$currencies['TWD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "TWD - New Taiwan Dollar ($)");
$currencies['TZS'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Sh", "TZD - Tanzanian Shilling (Sh)");
$currencies['UAH'] = array(SYMBOL_BEFORE_FORMAT, " ", ",", 2, "₴", "UAH - Ukrainian Hryvnia (₴)");
$currencies['UGX'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Sh", "UGX - Ugandan Shilling (sh)");
$currencies['USD'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "$", "USD - United States Dollars ($)");
$currencies['UYU'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "\$U", "UYU - Uruguayan Peso (\$U)");
$currencies['UZS'] = array(SYMBOL_AFTER_FORMAT, " ", ",", 2, "сўм", "UZS - Uzbekistani Som (сўм)");
$currencies['VEF'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "Bs F", "VEF - Venezuelan Bolívar (Bs F)");
$currencies['VND'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "₫", "VND - Vietnamese đồng (₫)");
$currencies['VUV'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Vt", "VUV - Vanuatu Vatu (Vt)");
$currencies['WST'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "T", "WST - Samoan Tālā (T)");
$currencies['XAF'] = array(SYMBOL_BEFORE_FORMAT, ".", ",", 2, "Fr", "XAF - Central African CFA Franc (Fr)");
$currencies['YER'] = array(SYMBOL_BEFORE_FORMAT, "٬", "٫", 2, "﷼", "YER - Yemeni Rial (﷼)");
$currencies['ZAR'] = array(SYMBOL_BEFORE_FORMAT, " ", ",", 2, "R", "ZAR - South African Rand (R)");
$currencies['ZMK'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "ZK", "ZMK - Zambian Kwacha (ZK)");
$currencies['ZWL'] = array(SYMBOL_BEFORE_FORMAT, ",", ".", 2, "Z$", "ZWL - Zimbabwean Dollar (Z$)");

if (!function_exists('yak_mex_format')) {
    function yak_mex_format($m) {
        // special case, handling millions separator. eg. 1'234,567.89
        $pos = strrpos($m, "'");
        if ($pos === false) {
            return $m;
        }
        else {
            return substr_replace($m, ',', $pos, 1);
        }
    }
}

/**
 * Return a monetary value properly formatted using the current currency settings.
 *
 * @param $money the money value
 * @param $include_symbol include the symbol in the formatted value
 */
if (!function_exists('yak_format_money')) {
    function yak_format_money($money, $include_symbol = false) {        
        global $currencies;
        $selected = yak_get_option(SELECTED_CURRENCY, 'USD');
        $ccy = $currencies[$selected];
        
        $cformat = $ccy[0];
        $thousands_sep = $ccy[1];
        $dec_point = $ccy[2];
        $dec_places = $ccy[3];
        $sym = $ccy[4];
        
        $money = number_format($money, $dec_places, '.', ',');
        $money = str_replace('.', $dec_point, str_replace(',', $thousands_sep, $money));
        
        if (count($ccy) > 6 && $ccy[6] != null) {
            $money = call_user_func($ccy[6], $money);
        }
        
        if ($include_symbol) {
            return sprintf($cformat, $money, $sym);
        }
        else {
            return $money;
        }
    }
}
?>