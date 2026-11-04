// $Id: pwcs.js,v 1.1.2.2 2008/09/30 20:07:51 rasel kabir Exp $

$(document).ready(function() {

    $('a.pwcs-popup-iframe').click(function(e) {
        e.preventDefault();
        var $this = $(this);
		var iframewidth = parseInt($(this).attr('iframewidth')?$(this).attr('iframewidth'):960);
		var iframeheight = parseInt($(this).attr('iframeheight')?$(this).attr('iframeheight'):500);

        var horizontalPadding = 5;
        var verticalPadding = 5;
        $('<iframe id="pwcs-external-site" class="pwcs-external-site" src="' + this.href + '" />').dialog({
            title: ($this.attr('title')) ? $this.attr('title') : 'PWC Store - External Site',
            autoOpen: true,
            width: iframewidth+(horizontalPadding*2),
            height: iframeheight+(verticalPadding*2),
            modal: true,
            resizable: true,
            draggable: false,
            autoResize: true,
			//close: function(event, ui) { window.location.reload(); },
            overlay: { opacity: 0.7, background: "blue" },
			buttons: {
				'Done': function() {
					$(this).dialog('close');
					window.location.reload();
				},
			}
        }).width(iframewidth).height(iframeheight);
    });

	// Product edit product default image START
	var fid = $('#product_image_selection input[name=pwc_image_select]:checked').val();
	$('#product_image_selection .form-radios .form-item').removeClass('pwcs-item-selected');
	$("#product_image_selection #edit-pwc-image-select-"+fid+"-wrapper").addClass('pwcs-item-selected');

	$('#product_image_selection input[name=pwc_image_select]').change(function() {
		//var fid = $(this).val();
		var fid = $('#product_image_selection input[name=pwc_image_select]:checked').val();
		$('#product_image_selection .form-radios .form-item').removeClass('pwcs-item-selected');
		$("#product_image_selection #edit-pwc-image-select-"+fid+"-wrapper").addClass('pwcs-item-selected');
	});	
	// Product edit product default image END

	// Product edit related product START
	$('#product_related_products_selection .form-checkboxes input.form-checkbox').removeClass('pwcs-item-selected');
    $('#product_related_products_selection .form-checkboxes input.form-checkbox').each( function() {
		if($(this).attr('checked') == true) {
			var product_nid = $(this).val();
			$("#product_related_products_selection #edit-related-products-checkboxes-"+product_nid+"-wrapper").addClass('pwcs-item-selected');
		}
    });	
	$('#product_related_products_selection .form-checkboxes input.form-checkbox').change(function() {
		var product_nid = $(this).val();
		if($(this).attr('checked') == true) {
			$("#product_related_products_selection #edit-related-products-checkboxes-"+product_nid+"-wrapper").addClass('pwcs-item-selected');
		} else {
			$("#product_related_products_selection #edit-related-products-checkboxes-"+product_nid+"-wrapper").removeClass('pwcs-item-selected');
		}
	});	
	// Product edit related product END

	//$('#product_related_products_selection .form-checkboxes input.form-checkbox').removeClass('pwcs-item-selected');
//    $('.form-checkboxes input.form-checkbox').each( function() {
//		if($(this).attr('checked') == true) {
//			$(this).parent().addClass('pwcs-item-selected');
//		}
//	});	
//	$('.form-checkboxes input.form-checkbox').change(function() {
//		$(this).parent().toggleClass('pwcs-item-selected');
//	});	
	
	// Product Single View - SITEADMIN
	$('.psv_view').css('display', 'none');
	$('.showhidelink a').click(function (e) {
		e.preventDefault();
		var psv_key = $(this).attr('psv_key');
		$('#psv_template_'+psv_key).toggle();

	});

	// Filter/Search Products form - SITEADMIN

	//$('#pwcs_product_list_filter').css('display', 'none');
//	$('.pwcs_product_list_filter_title').click(function (e) {
//		e.preventDefault();
//		$(this).toggleClass("expanded");
//		$('#pwcs_product_list_filter').toggle('slow');
//	});

	$(".collapsiblebox .collapsiblebox-button").click(function(e) {
		//alert('hit');
		e.preventDefault();
		$(this).toggleClass("collapsed");
		$(this).parent().children('.collapsiblebox-item').toggle('fast');
	});


	// Toggle Featured - SITEADMIN
	$('.pwcs_plfs a.toggle-featured').click(function(e) {
		e.preventDefault();
		var calling_url = $(this).attr('href');
		var action = $(this).attr('action');
		var toaction = $(this).attr('toaction');
		var title = $(this).attr('title');
		var totitle = $(this).attr('totitle');

		$(this).attr('href', '');
		$(this).children(".pwcs-checkbox-icon").removeClass('pwcs-checkbox-checked');
		$(this).children(".pwcs-checkbox-icon").removeClass('pwcs-checkbox-notchecked');
		$(this).children(".pwcs-checkbox-icon").addClass('pwcs-small-loading');

	    $.ajax({ type: "POST", url: calling_url });

		$(this).children(".pwcs-checkbox-icon").removeClass('pwcs-small-loading');

		if(action == 'removefeatured'){
			$(this).children(".pwcs-checkbox-icon").addClass('pwcs-checkbox-notchecked');
		}
		if(action == 'makeitfeatured'){
			$(this).children(".pwcs-checkbox-icon").addClass('pwcs-checkbox-checked');
		}
		var new_url = calling_url.replace(action, toaction);

		$(this).attr('href', new_url);
		$(this).attr('action', toaction);
		$(this).attr('toaction', action);
		$(this).attr('title', totitle);
		$(this).attr('totitle', title);
	});

	$("a#pwcs-header-banner-add-image").click(function(e) {
		e.preventDefault();
		window.open(Drupal.settings.basePath+'imce?app=myApp|sendto@headerImageFileHandler', '', 'width=760,height=560,resizable=1');
		//$("#pwcs-header-banner-add-image-preview").val(id);
	});

	$("a#pwcs-header-banner-add-image-remove").click(function(e) {
		e.preventDefault();
		var image_tag = ''; //'<img title="" alt="" src="">';
		$("#pwcs-header-banner-add-image-preview").html(image_tag);
		$("input[name=pwcs_headerbanner_image]").val(image_tag);
		//$("#pwcs-header-banner-add-image-preview").val(id);
	});

	$("#system-theme-settings .colorsettings .form-item .description").hide();
	$("#system-theme-settings .colorsettings .form-item").hover(function(e) {
		e.preventDefault();
		$("#system-theme-settings .colorsettings .form-item .description").hide();
		$(this).children('.description').show();
	});

	$(".siteadmin_dashboard .sd_item a.pwcs-commingsoon").click(function(e) {
		e.preventDefault();
	});
	var sd_item_inner_height = $('.siteadmin_dashboard .sd_item_inner').height();
    $('.siteadmin_dashboard .sd_item_inner .text').each( function() {
        var dashboard_text_padding = ( (sd_item_inner_height - $(this).height()) / 2 );
        $(this).css('padding-top', dashboard_text_padding);
    });

	var draggable_total_width = 0;
	$('.product-image-dragdrop .siteadmin-productimage-dragdrop-images #image_div div.draggable').each( function() {
	  draggable_total_width = $(this).width() + draggable_total_width + 12; //(margin 3px*2 + padding 2px*2 + border 1px*2) = 12
	});
	$('.product-image-dragdrop .siteadmin-productimage-dragdrop-images #image_div').css('width', draggable_total_width+10+'px');

	//$(".siteadmin_dashboard .sd_item a.pwcs-commingsoon").mouseover(function(e) {
	//	e.preventDefault();
	//	$(this).children('.sd_item_inner').append('<span class="comingsoon">Coming Soon..</span>');
	//}).mouseout(function () {
	//	$(this).children('.sd_item_inner').children('.comingsoon').remove();
	//});

	// SiteAdmin product edit check boxes - by Ilias START
	$("#siteadmin-productlist-form #products-check-all").change(function(e) {
		var mode = this.checked;
		$("#siteadmin-productlist-form .product-main-select").each(function(){
			this.checked = mode;
		});
	});
	//select a product when any changes in that row
	$("#siteadmin-productlist-form .change-main-checkbox").change(function(){
		$(this).parents('tr.product-itemrow').find('.product-main-select').attr('checked',true);
	});
	// SiteAdmin product edit check boxes - by Ilias END
	
	//$('#pwcs_product_list #image_div').stickyfloat({ duration: 400 });

	// Added by AR Zerin
	$(".draggable").draggable({
		revert: true,
		opacity: .75,
		containment: '.pwcs_table',
		cursor: 'move',
		cursorAt: { top: 35, left: 45 },
		helper: function(event) {
			return $('<div class="excerpt">' + $(this).children("div").html() + '</div>');
		},
		drag: function(event, ui) {
			$('.droppable').removeClass('drophighlight')
		}
	});
	$(".droppable").droppable({
		activeClass: 'dragactive',
		hoverClass: 'drophover',
		drop: function(event, ui) {
			$(this).addClass('drophighlight')
					.find('p')
					.html('' + ui.draggable.children("div").html()+ '');	
					//alert('Hello Pic Id:'+ui.draggable.children("div").attr("id"))
					//alert($(this).attr("id"));
					a='#productimageid-'+$(this).attr("id")
					b='productnid['+$(this).attr("id")+']'
					
					$("input[name="+b+"]").each(function()
					{
						this.checked = true;
					});		
					
					
					$(a).val(''+ui.draggable.children("div").attr("id"))
					//alert('Hidden value:'+$(a).val())
					//productimageid['.$result->nid.']
					//alert(ui.draggable.children('p').html());
		}
	});
	$(".droppable2").droppable({
		activeClass: 'dragactive',
		hoverClass: 'drophover',
		drop: function(event, ui) {
			$(this).addClass('drophighlight')
					.find('p')
					.html('' + ui.draggable.children("div").html()+ '');	
					a='#productimageid2-'+$(this).attr("id")
					b='productnid['+$(this).attr("id")+']'
					
					$("input[name="+b+"]").each(function()
					{
						this.checked = true;
					});		
					//alert('This function 2'+b);
					
					$(a).val(''+ui.draggable.children("div").attr("id"))
					//alert('Hidden value:'+$(a).val())
					//productimageid['.$result->nid.']
					//alert(ui.draggable.children('p').html());
		}
	});
	$(".droppable3").droppable({
		activeClass: 'dragactive',
		hoverClass: 'drophover',
		drop: function(event, ui) {
			$(this).addClass('drophighlight')
					.find('p')
					.html('' + ui.draggable.children("div").html()+ '');	
					a='#productimageid3-'+$(this).attr("id")
					b='productnid['+$(this).attr("id")+']'
					
					$("input[name="+b+"]").each(function()
					{
						this.checked = true;
					});		
					//alert('This function 2'+b);
					
					$(a).val(''+ui.draggable.children("div").attr("id"))
					//alert('Hidden value:'+$(a).val())
					//productimageid['.$result->nid.']
					//alert(ui.draggable.children('p').html());
		}
	});
	$(".droppable4").droppable({
		activeClass: 'dragactive',
		hoverClass: 'drophover',
		drop: function(event, ui) {
			$(this).addClass('drophighlight')
					.find('p')
					.html('' + ui.draggable.children("div").html()+ '');	
					a='#productimageid4-'+$(this).attr("id")
					b='productnid['+$(this).attr("id")+']'
					
					$("input[name="+b+"]").each(function()
					{
						this.checked = true;
					});		
					//alert('This function 2'+b);
					
					$(a).val(''+ui.draggable.children("div").attr("id"))
					//alert('Hidden value:'+$(a).val())
					//productimageid['.$result->nid.']
					//alert(ui.draggable.children('p').html());
		}
	});
	$(".droppable5").droppable({
		activeClass: 'dragactive',
		hoverClass: 'drophover',
		drop: function(event, ui) {
			$(this).addClass('drophighlight')
					.find('p')
					.html('' + ui.draggable.children("div").html()+ '');	
					a='#productimageid5-'+$(this).attr("id")
					b='productnid['+$(this).attr("id")+']'
					
					$("input[name="+b+"]").each(function()
					{
						this.checked = true;
					});		
					//alert('This function 2'+b);
					
					$(a).val(''+ui.draggable.children("div").attr("id"))
					//alert('Hidden value:'+$(a).val())
					//productimageid['.$result->nid.']
					//alert(ui.draggable.children('p').html());
		}
	});
	$(".droppable6").droppable({
		activeClass: 'dragactive',
		hoverClass: 'drophover',
		drop: function(event, ui) {
			$(this).addClass('drophighlight')
					.find('p')
					.html('' + ui.draggable.children("div").html()+ '');	
					a='#productimageid6-'+$(this).attr("id")
					b='productnid['+$(this).attr("id")+']'
					
					$("input[name="+b+"]").each(function()
					{
						this.checked = true;
					});		
					//alert('This function 2'+b);
					
					$(a).val(''+ui.draggable.children("div").attr("id"))
					//alert('Hidden value:'+$(a).val())
					//productimageid['.$result->nid.']
					//alert(ui.draggable.children('p').html());
		}
	});
	$(".droppable7").droppable({
		activeClass: 'dragactive',
		hoverClass: 'drophover',
		drop: function(event, ui) {
			$(this).addClass('drophighlight')
					.find('p')
					.html('' + ui.draggable.children("div").html()+ '');	
					a='#productimageid7-'+$(this).attr("id")
					b='productnid['+$(this).attr("id")+']'
					
					$("input[name="+b+"]").each(function()
					{
						this.checked = true;
					});		
					//alert('This function 2'+b);
					
					$(a).val(''+ui.draggable.children("div").attr("id"))
					//alert('Hidden value:'+$(a).val())
					//productimageid['.$result->nid.']
					//alert(ui.draggable.children('p').html());
		}
	});
});

function headerImageFileHandler (file, win) {
	//alert(file.url);
	var image_tag = '<img title="" alt="" src="'+file.url+'">';
	$("#pwcs-header-banner-add-image-preview").html(image_tag);
	$("input[name=pwcs_headerbanner_image]").val(image_tag);
  	win.close();//close IMCE
}