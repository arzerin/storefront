// $Id: pwcs.js,v 1.1.2.2 2008/09/30 20:07:51 rasel kabir Exp $

$(document).ready(function() {

//    $('#main-wrapper img').not("#header-group-wrapper img").each( function() {	
//		var imagePath = $(this).attr('src');
//		$(this).removeAttr('src');
//		$(this).wrap("<div class='pwcs-image-pre-loader'></div>");
//		$(this).load( function() {
//			//$(this).fadeIn('slow', function(){ $(this).unwrap(); });
//			$(this).fadeIn('slow', function(){ $(this).parent().removeClass("pwcs-image-pre-loader"); });
//		}).attr('src', imagePath);
//    });

	$loading_image = Drupal.settings.basePath+'sites/all/modules/pwcs/images/loading.gif';
	$('<img/>')[0].src = $loading_image;
	//(new Image()).src = $loading_image;

	// Initializing cart dialog
	$("#cart-block-cart-pane").dialog({
		open: function(event, ui) { 
				$('.ui-widget-overlay').bind('click', function(){ 
					$("#cart-block-cart-pane").dialog('close'); 
				});
			},
		//close: function(event, ui) { 
			//setTimeout($("#cart-block-cart-pane").dialog("close"),5000);
		//},
		//dialogClass: 'alert',
		//bgiframe: true,
		resizable: true,
		draggable: false,
		modal: true,
		width: '580px',
		show: "blind",
		position: ['center',100],
		autoOpen: false,
		buttons: {
			'Continue Shopping': function() {
				$(this).dialog('close');
			},
			'Check Out': function() {
				window.location.replace(Drupal.settings.basePath+'checkouturl');
			},
		}
	});

	// On click cart link
	$('.cart_link').click(function(e) {

		e.preventDefault();
		$("#cart-block-cart-pane").html('');
		$("#cart-block-cart-pane").addClass('pwcs-loading');
		$("#cart-block-cart-pane").dialog('open');

		var calling_url = $(this).attr('ajax_href');
	    $.ajax({
			type: "POST",
			url: calling_url,
			//data: formdata,
			success: function(html_data){
				$("#cart-block-cart-pane").removeClass('pwcs-loading');
				$("#cart-block-cart-pane").html(html_data);
				pwcs_js_removefromcart();
				//pwcs_js_addtocart();
			},
			error: function(){
				alert('Error!');
			},
		});

	});

	$('input#productdetails_addtocart_qty').change(function() {
		var product_id = $(this).attr('product_id');
		$('#addtocart-id-'+product_id).attr('addtocart_quantiy',$(this).val());
	});

	$('input.checkbox_checkall').click(function() {
		var formid = $(this).parents('form').attr('id');
		var checked_status = this.checked;
		$("#"+formid+" input[type=checkbox]").each(function(){
			this.checked = checked_status;
		});
	});
	$("input.checkbox_checkall_products").click(function()	{
		var formid = $(this).parents('form').attr('id');
		var checked_status = this.checked;
		$("input[id=productnid]").each(function()
		{
			this.checked = checked_status;
		});		
	});	
	$("input.checkbox_checkall2").click(function()	{
		var formid = $(this).parents('form').attr('id');
		var checked_status = this.checked;
		$("input[id=activatesales]").each(function()
		{
			this.checked = checked_status;
		});		
	});
	$("input.checkbox_markallvisible").click(function()	{
		var formid = $(this).parents('form').attr('id');
		var checked_status = this.checked;
		$("input[id=makevisible]").each(function(){
			this.checked = checked_status;
		});		
	});	
	// Floating div position set (ie. cart link)
	var main_width = $('#main').width();
	var window_width = $(document).width();
	var pwcs_floating_region_width = $('#pwcs_floating_region').width();
	var position= ((window_width - main_width) /2)-pwcs_floating_region_width;
	//var position= ((window_width - main_width) /2);
	$('#pwcs_floating_region').css('right', position+'px');
	
	// product additional image hover START
	$('#productdetails_additionalimage img').each(function(){
		(new Image()).src = $(this).attr('alt');
	});		
	var default_img_src = $('#productdetails_defaultimage img').attr('src');
	$('#productdetails_additionalimage img').mouseover(function () {
		var additional_img_src = $(this).attr('alt');
		$('#productdetails_defaultimage img').attr('src', additional_img_src);
	}).mouseout(function () {
		$('#productdetails_defaultimage img').attr('src', default_img_src);
	});
	// product additional image hover END

	// Product Details Options Show All
	$('.pwcs_productdeatils_options_showall').click(function(e) {

		var calling_url = $(this).attr('ajax_url');
		var product_nid = $(this).attr('product_nid');
		var formdata = "product_nid="+product_nid+"&ajax=1";
		var current_selection = $(this).attr('current_selection');

        $('<div id="pwcs_productdeatils_options_showall_modal"></div>').dialog({
            title: "Products Options",
            autoOpen: true,
            width: 700,
            height: 400,
            modal: true,
            resizable: true,
            draggable: false,
            autoResize: true,
			open: function(event, ui) { 
				$('#pwcs_productdeatils_options_showall_modal').addClass('pwcs-loading');
				$.ajax({
					type: "POST",
					url: calling_url,
					data: formdata,
					success: function(html_data){
						$('#pwcs_productdeatils_options_showall_modal').removeClass('pwcs-loading');
						var data = Drupal.parseJson(html_data);
//						if(data.status == 'ok') {
//						}
//						if(data.status == 'notfound') {
//						}
						$('#pwcs_productdeatils_options_showall_modal').html(data.msgs);
						if(current_selection != ''){
							$('#pwcs_productdeatils_options_showall_modal input:radio[name=pwcs-productdetails-select-options]').filter("[value="+current_selection+"]").attr("checked","checked");
						}
						pwcs_filter_table_row('#pwcs_productdeatils_alloptions_table');
					},
					error: function(){
						alert('Error!');
					},
				});
			},
			close: function(event, ui) { 
				$('#pwcs_productdeatils_options_showall_modal').remove();
			},
            overlay: { opacity: 0.7, background: "black" },
			buttons: {
				'Close': function() {
					$(this).dialog('close');
				},
				'Done': function() {
					// need to update hidden values on change
					var selected_options = $('#pwcs_productdeatils_options_showall_modal input:radio[name=pwcs-productdetails-select-options]:checked').val();
					if(typeof selected_options == 'undefined') {
					  alert('No Options Selected');
					} else {
						$('#pwcs_productdeatils_options_showall_'+product_nid).attr('current_selection', selected_options);
						pwcs_productdetails_set_select_combo(product_nid, selected_options);
						$('#pwcs_productdeatils_options_chkavail_'+product_nid).click();
						$(this).dialog('close');
					}
				},

			}
        });

	});	

	// Product Details Options Check Availity
	$('.pwcs_productdeatils_options_chkavail').click(function(e) {

		var calling_url = $(this).attr('ajax_url');
		var product_nid = $(this).attr('product_nid');
		var realprice = $(this).attr('product_real_price');
		var realsku = $(this).attr('product_real_sku');

		var statusdiv = '#pwcs_productdeatils_options_main_'+product_nid+' .ajax_status';

		$(statusdiv).html('Please Wait..'); //.html('<div class="pwcs-small-loading"></div>');

		var idcount = $(this).attr('count');
		var csv_options = '';
		for(var i=1; i<=idcount; i++){
			if($("#pwcs_productdeatils_select_"+product_nid+"_"+i).val() != '')
				csv_options += $("#pwcs_productdeatils_select_"+product_nid+"_"+i).val()+',';
		}
		csv_options = csv_options.substring(0, csv_options.length-1);
		var formdata = "csv_options="+csv_options+"&product_nid="+product_nid+"&ajax=1";	

		$.ajax({
			type: "POST",
			url: calling_url,
			async: false,
			data: formdata,
			success: function(html_data){
				var data = Drupal.parseJson(html_data);
				if(data.status == 'ok') {
					var newPrice = parseFloat(realprice) + parseFloat(data.affected_price);
					$('#productpriceamount_'+product_nid).text(newPrice);
					if(data.subsku != ''){
						$('#productskutext_'+product_nid).html(data.subsku);
					} else {
						$('#productskutext_'+product_nid).html(realsku);
					}
				}
				if(data.status == 'notfound') { 
					//$('#product_details #productdetails_price_amount').text(realprice);
					//$('#product_details .sku_value').html(realsku);
					$('#productpriceamount_'+product_nid).text(realprice);
					$('#productskutext_'+product_nid).html(realsku);
				}
				$(statusdiv).html(data.msgs);
			},
			error: function(){
				alert('Error!');
				$('#productpriceamount_'+product_nid).text(realprice);
				$('#productskutext_'+product_nid).html(realsku);
				$(statusdiv).html('Error!');
			},
		});
	});

	//add to cart
	pwcs_js_addtocart();

	// Initializing product review form dialog
	$('.productreview_addnew_span').show();
	$(".customer_reviews_form").dialog({
		open: function(event, ui) { 
				$('.ui-widget-overlay').bind('click', function(){ 
					$(".customer_reviews_form").dialog('close'); 
				});
			},
		resizable: false,
		draggable: false,
		modal: true,
		width: '580px',
		show: "blind",
		position: 'center',
		autoOpen: false,
		buttons: { }
	});
	// On click add new review link
	$('.productreview_addnew').click(function(e) {
		e.preventDefault();
		$(".customer_reviews_form").dialog('open');
	});

	pwcs_filter_content('.siteadmin-productimage-dragdrop-images #image_div');

	//Search
	$("#edit-search-keyword").defaultify("Search...");

});

function pwcs_js_addtocart() {
	// add to cart
	$('a[name=product-addtocart]').click(function(e) {
		e.preventDefault();
		$("#cart-block-cart-pane").html('');
		$("#cart-block-cart-pane").addClass('pwcs-loading');
		$("#cart-block-cart-pane").dialog('open');

		var calling_url = $(this).attr('ajax_addtocart_url');
		var product_id = $(this).attr('product_id');
		var product_link_id = $(this).attr('product_link_id');

		var addtocart_quantiy = $(this).attr('addtocart_quantiy');
		if(addtocart_quantiy == undefined) { addtocart_quantiy = '1'; }

		var idcount = $('#pwcs_productdeatils_options_chkavail_'+product_id).attr('count');
		var csv_options = '';
		for(var i=1; i<=idcount; i++){
			if($('#pwcs_productdeatils_select_'+product_id+'_'+i).val() != '')
				csv_options += $('#pwcs_productdeatils_select_'+product_id+'_'+i).val()+',';
		}
		csv_options = csv_options.substring(0, csv_options.length-1);
		if(csv_options == undefined) { csv_options = ''; }

		var formdata = "csv_options="+csv_options+"&product_id="+product_id+"&product_link_id="+product_link_id+"&addtocart_quantiy="+addtocart_quantiy+"&ajax=1";
		
	    $.ajax({
			type: "POST",
			url: calling_url,
			data: formdata,
			//async: false,
			success: function(data){
				var data = Drupal.parseJson(data);
				$("#cart-block-cart-pane").removeClass('pwcs-loading');
				if(data.response == 'added') { 
					$("#cart-block-cart-pane").html(data.cartinfo);
					$('.carttotalitemcount').text(data.cartamount);
					$('.carttotalitemcount_totalqty').text(data.cartamount_totalqty);
					pwcs_js_removefromcart();
				} else {
					$("#cart-block-cart-pane").html(data.error_msgs);
					//$("#cart-block-cart-pane").dialog('close');
				}
			},
			error: function(){
				alert('Error!');
			},
		});
		//$("#cart-block-cart-pane").dialog('close');
	});
}


//remove from cart product-removefromcart
function pwcs_js_removefromcart() {
		
	$('a[name=product-removefromcart]').click(function(e) {
		e.preventDefault();
		var calling_url = $(this).attr('ajax_removefromcart_url');
		var pwc_orderitemid = $(this).attr('pwc_orderitemid');
		var formdata = "pwc_orderitemid="+pwc_orderitemid+"&ajax=1";
		var itemcount = $(".carttotalitemcount").html();
		var itemcount_totalqty = $(".carttotalitemcount_totalqty").html();

		var remove_link = $("#cart-block-cart-pane #cartitem-"+pwc_orderitemid+" .removelink").html();
		$("#cart-block-cart-pane #cartitem-"+pwc_orderitemid+" .removelink").html('<div class="pwcs-small-loading"></div>');

	    $.ajax({
			type: "POST",
			url: calling_url,
			data: formdata,
			async: false,
			success: function(data){
			var data = Drupal.parseJson(data);
				//alert(data.cart_item_remove);
				//alert(data.status);
				//alert(data.totalprice);
				if(data.response == 'removed') { 
					$("#cart-block-cart-pane #cartitem-"+pwc_orderitemid+" .removelink").html('<em>Item removed</em>');
					$("#cart-block-cart-pane #cartitem-"+pwc_orderitemid).fadeOut("slow");
					$("#carttotalprice").html(data.totalprice);
					itemcount = parseInt(data.cart_amount);
					$(".carttotalitemcount").text(itemcount);
					itemcount_totalqty = parseInt(data.cart_totalqty);
					$(".carttotalitemcount_totalqty").text(itemcount_totalqty);
					if(itemcount < 1) {
						$("#cart-block-cart-pane").html(Drupal.t('All Items Removed'));
						//setTimeout("$('#cart-block-cart-pane').dialog('close')",5000);
						$("#cart-block-cart-pane").dialog('close');
					}
				} else {
					$("#cart-block-cart-pane #cartitem-"+pwc_orderitemid+" .removelink").html(remove_link);
					$("#cart-block-cart-pane #cartitem-"+pwc_orderitemid+" .removestatus").html('<em>Opps! Cannot be removed</em>');
				}
			},
			error: function(){
				alert('Error!');
			},
		});

	});
	
}

function pwcs_productdetails_set_select_combo(product_nid, attr_id) {

	var selected_options_array = new Array();
	selected_options_array = attr_id.split(",");
	var idcount = $('#pwcs_productdeatils_options_chkavail_'+product_nid).attr('count');
	for(var i=1; i<=idcount; i++){
		$("#pwcs_productdeatils_select_"+product_nid+"_"+i).val('');
	}
	for (a in selected_options_array ) {
		var attr_id = parseFloat(selected_options_array[a]);
		for(var i=1; i<=idcount; i++){
			if($("#pwcs_productdeatils_select_"+product_nid+"_"+i+" option[value='"+attr_id+"']").val() == attr_id) {
				$("#pwcs_productdeatils_select_"+product_nid+"_"+i).val(attr_id);
			}
		}
	}
}

function pwcs_filter_table_row(tableid){

	var filter_form = '<div style="padding: 5px 0 15px; text-align: center;">Filter : <input type="text" name="filter-'+tableid+'" value="" size="50" /></div>';
	$(tableid).parent().prepend(filter_form);
	$('input[name=filter-'+tableid+']').focus().keyup(function() {
		var filterword = $(this).val()
		filterword = filterword.replace('(', '');
		filterword = filterword.replace(')', '');
		filterword = filterword.replace('"', '');
		filterword = filterword.replace('#', '');
		filterword = filterword.replace('$', '');
		//$('#testing').html(filterword);
	    $(tableid+' tbody tr:not(:contains("' + filterword + '"))').hide();
	    $(tableid+' tbody tr:contains("' + filterword + '")').show();
	});
}

function pwcs_filter_content(divid){

	var filter_form = '<div class="filter-input">Filter : <input type="text" name="filter-'+divid+'" value="" size="50" /></div>';
	$(divid).parent().prepend(filter_form);
	$('input[name=filter-'+divid+']').focus().keyup(function() {
		var filterword = $(this).val()
		filterword = filterword.replace('(', '');
		filterword = filterword.replace(')', '');
		filterword = filterword.replace('"', '');
		filterword = filterword.replace('#', '');
		filterword = filterword.replace('$', '');
		//alert(filterword);
		//$('#testing').html(filterword);
	    $(divid+' div:not(:contains("' + filterword + '"))').hide();
	    $(divid+' div:contains("' + filterword + '")').show();
	});
}
