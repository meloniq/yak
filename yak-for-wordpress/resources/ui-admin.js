var $j = jQuery.noConflict();

function clearInputs(frm, type) {
    var inputs = $j(frm + ' :input');
    inputs.each(function() {
        if ($j(this).attr('type') == type) {
            $j(this).val('');
        }
    });
}

function copyRow(tableId) {
	var tr = $j(tableId).find('tr:last').clone();
    $j(tableId).append(tr);
}

function exportData(baseUrl) {
    var link = $j('#export-data-link');
    
    var href = baseUrl;
    
    var status = $j('#status :selected').val();
    var year = $j('#year_order_date');
    var month = $j('#month_order_date :selected').val();
    
    href += "&status=" + status;
    
    if (!$j.isEmptyObject(year)) {
        href += "&year=" + year.val();
        
        if (month != null && month != '') {
            href += "&month=" + month;
        }
    }
    
    var prod = $j('#product_ident');
    if (!$j.isEmptyObject(prod)) {
        p = prod.val();
        if (p != null && p != '') {
            href += "&product_ident=" + p;
        }
    }

    var order_num = $j('#order_num');
    if (!$j.isEmptyObject(order_num)) {
        num = order_num.val();
        if (num != null && num != '') {
            href += "&order_num=" + escape(order_num);
        }
    }
    
    var customer = $j('#customer'); 
    if (!$j.isEmptyObject(customer)) {
        cust = customer.val();
        if (cust != null && cust != '') {
            href += "&customer=" + escape(cust);
        }
    }
    
    var payment_type = $j('#payment_type'); 
    if (!$j.isEmptyObject(payment_type)) {
        ptype = payment_type.val();
        if (ptype != null && ptype != '') {
            href += "&payment_type=" + escape(ptype);
        }
    }
    
    link.attr('href', href);
    return true;
}

function removeCouponSet(name) {
    var del = $j('#delete_coupon_set');
    del.val(name);
    $j('#options_update_coupons').click();
}

function removeRow(input, inputName) {
    row = $j(input.parentNode.parentNode);
    input.parentNode.parentNode.cells[0].firstChild.value = '';
    row.css({'visibility' : 'hidden', 'display' : 'none'});
}

function submitSettingsForm(form, anchor) {
    document.forms[form].action = '#' + anchor;
    return true;
}

function testConfEmail(elemId, updateButtonId) {
    $j(elemId).val('test');
    $j(updateButtonId).click();
    return false;
}

function addProductRow(tableId) {
    lastrow = $j(tableId + ' tr:last');
    if (lastrow.css('visibility') == 'hidden') {
        lastrow.css('visibility', 'visible');
    }
    else {
        copyRow(tableId);
    }
}

function newProductToggle(tableId) {
    table = $j(tableId);
    firstCell = table.find('tr:last').find('td:first');
    
    firstCell.children().each(function() {
        n = $j(this);
        tag = this.nodeName;

        if (tag == null || tag == 'button' || tag == 'BUTTON' || tag == 'undefined') {
            return;
        }
        else if (n.css('visibility') == 'hidden') {
            n.css('visibility', 'visible');
            n.css('display', 'block');
        }
        else {
            n.css('visibility', 'hidden');
            n.css('display', 'none');
        }
    });
}

function removeExistingProductType(elem, deleteInputName) {
    elem = $j(elem);
    tr = elem.parent().parent();
    input = tr.find('[name = ' + deleteInputName + ']');
    input.val('true');
    tr.css('visibility', 'hidden');
    tr.css('display', 'none');
}

function setupAdminPage() {
    $j("ul.tabs").tabs("div.panes > div", {history: true});
    $j(":input[title]").tooltip({
          position: "center right",
          offset: [-2, 10],
          effect: "fade",
          opacity: 0.9,
          events: { input: "mouseenter,mouseleave" }
    });
    $j(":date").dateinput({ format : 'yyyy-mm-dd' });
}

/** 
 * Initialize tables 
 */
$j(function() {
    var defaults={classCollapse:"collapsible",classExpand:"expand-child",classAnchor:["collapsed","expanded"]},bHideParentRow=!!$j.browser.msie;$j.fn.collapsible=function(options){var self=this,settings=$j.extend({},defaults,options);return this.each(function(){var $td=$j("td."+settings.classCollapse,this).append('<a href="#" class="button-secondary '+settings.classAnchor[0]+'">Details</a>').find('a').bind("click",function(){var $a=$j(this),$tr=$a.parent().parent(),$trc=$tr.next(),bIsCollapsed=$a.hasClass(settings.classAnchor[1]);$a[bIsCollapsed?"removeClass":"addClass"](settings.classAnchor[1])[!bIsCollapsed?"removeClass":"addClass"](settings.classAnchor[0]);while($trc.hasClass(settings.classExpand)){if(bHideParentRow){var ts_config=$j.data(self[0],"tablesorter");$trc[bIsCollapsed?"hide":"show"]();if(!bIsCollapsed&&ts_config){if($tr.hasClass(ts_config.widgetZebra.css[0]))$trc.addClass(ts_config.widgetZebra.css[0]).removeClass(ts_config.widgetZebra.css[1]);else if($tr.hasClass(ts_config.widgetZebra.css[1]))$trc.addClass(ts_config.widgetZebra.css[1]).removeClass(ts_config.widgetZebra.css[0]);}}
    $j("td",$trc)[bIsCollapsed?"hide":"show"]();$trc=$trc.next();}
    return false;}).end();if(bHideParentRow){$td.parent().each(function(){var $tr=$j(this).next();while($tr.hasClass(settings.classExpand)){$tr=$tr.hide().next();}});}});}
    $j('.collapsible').collapsible();
});

