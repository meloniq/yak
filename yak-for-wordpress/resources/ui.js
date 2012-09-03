var $j = jQuery.noConflict();

function updateQuantities() {
    $j('#yakaction').val('update');
    return true;
}

function deleteProduct(id) {
    $j(id).val(0);
    return updateQuantities();
}

function billing() {
    elem = $j('#billing')
    if (elem.css('visibility') == 'hidden') {
        elem.css('visibility', 'visible');
        elem.css('display', 'block');
    }
    else {
        elem.css('visibility', 'hidden');
        elem.css('display', 'none');
    }
}

function changeRegion(regionPrefix) {
    var prefix = '#' + regionPrefix;
    var selectedCountry = $j(prefix + '_country_input').val();
    regionLabel = $j(prefix + '_region_label');    

    var stateText = yak_ui_l10n.state_text;
    var regionText = yak_ui_l10n.region_text;

    if (selectedCountry == 'US') {
        regionLabel.html(stateText);
        $j(prefix + '_state_input').css('display', '');
        $j(prefix + '_state_input').removeAttr('disabled');
        $j(prefix + '_ca_state_input').css('display', 'none');
        $j(prefix + '_ca_state_input').attr('disabled', 'disabled')
        $j(prefix + '_region_input').css('display', 'none');
        $j(prefix + '_region_input').attr('disabled', 'disabled')
    }
    else if (selectedCountry == 'CA') {
        regionLabel.html(stateText);
        $j(prefix + '_ca_state_input').css('display', '');
        $j(prefix + '_ca_state_input').removeAttr('disabled')
        $j(prefix + '_state_input').css('display', 'none');
        $j(prefix + '_state_input').attr('disabled', 'disabled');
        $j(prefix + '_region_input').css('display', 'none');
        $j(prefix + '_region_input').attr('disabled', 'disabled');
    }
    else {
        regionLabel.html(regionText);
        $j(prefix + '_state_input').css('display', 'none');
        $j(prefix + '_ca_state_input').css('display', 'none');
        $j(prefix + '_region_input').css('display', '');
        $j(prefix + '_region_input').removeAttr('disabled');
        $j(prefix + '_state_input').attr('disabled', 'disabled');
        $j(prefix + '_ca_state_input').attr('disabled', 'disabled');
    }
}

function editAddress() {
    copyAddress('shipping');
    copyAddress('billing');
    return false;
}

function copyAddress(type) {
    old_email = $j('#oldaddress :input[name=' + type + '_email]');
    old_recip = $j('#oldaddress :input[name=' + type + '_recipient]');
    old_cpy = $j('#oldaddress :input[name=' + type + '_company_name]');
    old_phone = $j('#oldaddress :input[name=' + type + '_phone]');
    old_addr1 = $j('#oldaddress :input[name=' + type + '_addr1]');
    old_addr2 = $j('#oldaddress :input[name=' + type + '_addr2]');
    old_suburb = $j('#oldaddress :input[name=' + type + '_suburb]');
    old_city = $j('#oldaddress :input[name=' + type + '_city]');
    old_region = $j('#oldaddress :input[name=' + type + '_region]');
    old_state = $j('#oldaddress :input[name=' + type + '_state]');
    old_postcode = $j('#oldaddress :input[name=' + type + '_postcode]');
    old_country = $j('#oldaddress :input[name=' + type + '_country]');
    
    new_email = $j('#address :input[name=' + type + '_email]');
    new_recip = $j('#address :input[name=' + type + '_recipient]');
    new_cpy = $j('#address :input[name=' + type + '_company_name]');
    new_phone = $j('#address :input[name=' + type + '_phone]');
    new_addr1 = $j('#address :input[name=' + type + '_addr1]');
    new_addr2 = $j('#address :input[name=' + type + '_addr2]');
    new_suburb = $j('#address :input[name=' + type + '_suburb]');
    new_city = $j('#address :input[name=' + type + '_city]');
    new_region = $j('#address :input[name=' + type + '_region]');
    new_postcode = $j('#address :input[name=' + type + '_postcode]');

    copyInputValue(new_email, old_email);
    copyInputValue(new_recip, old_recip);
    copyInputValue(new_cpy, old_cpy);
    copyInputValue(new_phone, old_phone);
    copyInputValue(new_addr1, old_addr1);
    copyInputValue(new_addr2, old_addr2);
    copyInputValue(new_suburb, old_suburb);
    copyInputValue(new_city, old_city);
    copyInputValue(new_region, old_region);
    copyInputValue(new_postcode, old_postcode);
    
    if (old_state != null) {
        new_state = $j('#address :input[name=' + type + '_state]');
        copyInputValue(new_state, old_state);
    }
    
    if (old_country != null) {
        new_country = $j('#address :input[name=' + type + '_country]');
        copyInputValue(new_country, old_country);
    }
    
    changeRegion(type);
}

function copyInputValue(destInput, srcInput) {
    if (destInput != null && srcInput != null) {
        destInput.val(srcInput.val());
    }
}

function copyValue(srcId, destId) {
    $j(destId).val($j(srcId).val());
}

function limitTextArea(limitField, limitNum) {
    if (limitField.value.length > limitNum) {
        limitField.value = limitField.value.substring(0, limitNum);
    }
}

function validateBuy(button, multiSelectMin, multiSelectMax, customPrice, groups, ajaxMode, baseUrl) {
    var evt = jQuery.Event('yak:buyvalidate');
    evt.valid = true;
    evt.target = button;
    
    $j(document).trigger(evt);
    
    if (!evt.valid) {
        return false;
    }
    
    frm = $j(button).closest('form');
    for (i = 1; i <= groups; i++) {
        count = 0;
        chkname = 'multiselect' + i + '[]';
        frm.find('input').each(function() {
            var a = $j(this);
            name = a.attr('name');
            if (name == chkname && a.attr('checked') == 'checked') {
                count++;
            }
        });
        if (count < multiSelectMin || count > multiSelectMax) {
            return false;
        }
    }
    
    if (customPrice && frm.closest('yak_custom_price').isEmptyObject()) {
        return false;
    }
    
    if (ajaxMode) {
        ajaxBuy(button, baseUrl);
        return false;
    }
    
    return true;
}

function yakConfirmOrder(incTandC) {
    if (incTandC) {
        cb = $j('#tandcConfirmation');
        if (!cb.prop('checked')) {
            return false;
        }
    }
    return true;
}

function ajaxBuy(button, baseUrl) {
    btn = $j(button);
    originalHTML = btn.innerHTML;
    btn.disabled = true;
    
    w = btn.getWidth();
    btn.setStyle({ width: w + 'px' })
    btn.innerHTML = yak_ui_l10n.ajax_button_adding;
    
    frm = btn.up('form');
    
    new Ajax.Request(baseUrl + '/index.php', {
        method: 'post',
        parameters: frm.serialize(),
        onSuccess: function(transport) {
            if ($$('li.yak_order_widget').size() > 0) {
                new Ajax.Request(baseUrl + '/wp-content/plugins/yak-for-wordpress/yak-view-order-widget.php', {
                    method: 'get',
                    onSuccess: function(transport) {
                        elem = $$('li.yak_order_widget');
                        elem.first().update(transport.responseText);
                    }
                });
            }
            btn.innerHTML = yak_ui_l10n.ajax_button_added;
            btn.disabled = false;
        }
    });
}
