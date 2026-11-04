<?php
/* $Id: product node pwcs_product.php,v 1.0 2010/04/30 Rasel Kabir Shuvro <shuvro.mist@gmail.com> Exp $ */


//drupal_add_js(drupal_get_path('module', 'pwcs') .'/js/pwcs.js', 'module');

/**
 * Implementation of hook_perm()
 * Added by Rasel Kabir
 */
function pwcs_product_perm()
{
	return array(
		'access pwcs product',
		'manage pwcs product',
		'create pwcs product',
	);
}
/**
 * Implementation of hook_form()
 * Added additional fields with title and body for product node.
 * Added by Rasel Kabir
 */
function pwcs_product_form(&$node)
{
	$weight=0;

	$form['#prefix'] = '<div id="pwcs_add_edit_products">';
	$form['#suffix'] = '</div>';

	// TAB LINKS START
	$tabs = '<ul>';
	if(isset($node->nid)) {
		$tabs .= '<li><a href="#product_quickview">'.t('Quick View').'</a></li>';
	}
	$tabs .= '<li><a href="#product_basic">'.t('Basic').'</a></li>';
	$tabs .= '<li><a href="#field-product-image-items">'.t('Images').'</a></li>';
	//$tabs .= '<li><a href="#edit-field-product-image-multiple">'.t('Images Multi').'</a></li>';
	$tabs .= '<li><a href="#product_image_selection">'.t('Default Image').'</a></li>';
	$tabs .= '<li><a href="#product_videos">'.t('Videos').'</a></li>';
	$tabs .= '<li><a href="#product_listings">'.t('Listings').'</a></li>';
	$tabs .= '<li><a href="#product_others">'.t('Details').'</a></li>';
	$tabs .= '<li><a href="#product_manufacture">'.t('Manufacturer').'</a></li>';
	$tabs .= '<li><a href="#product_attribute_tab">'.t('Options Set').'</a></li>';
	$tabs .= '<li><a href="#product_related_products_selection">'.t('Related Products').'</a></li>';
	$tabs .= '<li><a href="#product_more_options">'.t('More Options').'</a></li>';
	$tabs .= '<li><a href="#product_path">'.t('SEO').'</a></li>';
	$tabs .= '</ul>';
	$form['form_tabs'] = array( '#type' => 'markup', '#value' => $tabs, '#weight' => -50 );
	// TAB LINKS END

	// This items are necessary at this form START
	if(isset($node->nid)) {
		$form['pwc_product_row_id'] = array(
			'#type' => 'hidden',
			'#default_value' => $node->pwc_product_row_id,
		);

		$form['product_basic']['product_link_id'] = array(
			'#type' => 'hidden',
			'#default_value' => $node->product_link_id,
		);

		$form['product_basic']['regular_buy_link'] = array(
			'#type' => 'hidden',
			'#default_value' => $node->regular_buy_link,
		);

		$form['product_basic']['one_click_buy_link'] = array(
			'#type' => 'hidden',
			'#default_value' => $node->one_click_buy_link,
		);

		$form['product_basic']['product_image_path'] = array(
			'#type' => 'hidden',
			'#default_value' => $node->product_image_path,
		);
	}
	// This items are necessary at this form END


	// Start TAB product_quickview
	if(isset($node->nid)) {
	$form['product_quickview']['#prefix'] = '<div id="product_quickview">';
	$form['product_quickview']['#suffix'] = '<div style="clear:both;"></div></div>';
	$form['product_quickview']['html']=array(
		'#type' => 'markup',
		'#value' => pwcs_product_edit_quickview($node),
	);
	}
	// Start TAB product_basic
	// Title
	$form['product_basic']['#prefix'] = '<div id="product_basic">';
	$form['product_basic']['#suffix'] = '</div>';
	$form['product_basic']['title'] = array(
		'#type' => 'textfield',
		'#title' => t('Product Name'),
		'#description' => t('Name of the product'),
		'#default_value' => $node->title,
		'#attributes' => array('class' => 'medium_text_field'),
		'#required' => TRUE,
		'#weight' => $weight++
	);

	$form['product_basic']['product_sku'] = array(
		'#type' => 'textfield',
		'#title' => t('Product SKU'),
		//'#description' => t(''),
		'#default_value' => $node->product_sku,
		'#attributes' => array('class' => 'small_text_field'),
	 	'#maxlength' => 60,
		'#weight' => $weight++
	);

	$form['product_basic']['price'] = array(
		'#type' => 'textfield',
		'#title' => t('Product Price'.' ('.PWCS_BASIC_CURRENCY_SYMBOL.')'),
		'#description' => t('Please do not provide currency symbol here. Just provide the price. Ex. 23.97'),
		'#default_value' => $node->price,
		'#attributes' => array('class' => 'small_text_field'),
	 	'#maxlength' => 8,
		'#weight' => $weight++
	);
	// categories checkboxes
	$categories = pwcs_cat_array_selection_combo();
	$form['product_basic']['pwc_category_id'] = array(
		'#type' => 'select',
		'#options' => $categories,
		'#default_value' => $node->pwc_category_id ? $node->pwc_category_id : 1,
		'#title' => t('Product Category'),
		'#weight' => $weight++
	);

	// End TAB product_basic
	
	// START TAB Listing
	$form['product_listings_tab']['#prefix'] = '<div id="product_listings">';
	$form['product_listings_tab']['#suffix'] = '<div style="clear:both;"></div></div>';
	

	$listing_array=pwcs_listing_option_values();
	$product_listings_instruction = '<div class="messages warning">Please <b>Select</b> all the below categories under which you want to show this products.</div>';
	$form['product_listings_tab']['instuct']=array(
			'#type' => 'markup',
			'#value' =>$product_listings_instruction
	);	
    $form['product_listings_tab']['product_listing_checkboxes'] = array(
		'#type' => 'checkboxes',
		'#options' => $listing_array,		
		'#title' => t('Listing Categories'),
    	'#default_value' => isset($node->product_listing_array)?$node->product_listing_array:array(),
		'#size' => 20,
		'#weight' => $weight++
	);	
	
	// END TAB Listings
	
	// START TAB product_others
	$form['product_others']['#prefix'] = '<div id="product_others">';
	$form['product_others']['#suffix'] = '</div>';
	$form['product_others']['teaser'] = array(
		'#type' => 'textarea',
		'#title' => t('Product Short Details'),
		'#description' => t('Short information about the product'),
		'#default_value' => $node->teaser,
		'#weight' => $weight++
	);
	$form['product_others']['body'] = array(
		'#type' => 'textarea',
		'#title' => t('Product Long Details'),
		'#description' => t('More details about the product'),
		'#default_value' => $node->body,
		'#weight' => $weight++
	);
	// END TAB product_others

	// START TAB product_manufacture
	$form['product_manufacture_deatils']['#prefix'] = '<div id="product_manufacture">';
	$form['product_manufacture_deatils']['#suffix'] = '<div style="clear:both;"></div></div>';
	
	$options_manufacturer[0] = '----- '.t('Select').' -----';
	$options_manufacturer += pwcs_manufacturer_array();

	if(variable_get('pwcs_sitesettings_product_detail_show_manufacturer', '1') == 1) 
	{
		$message_manufacture_instruct_not_set = '<div class="messages">Please <b>Enable</b> Show Manufacturer Tab from <b>Display Settings>>Product Details Page Settings</b> if you want to show Product Manufacturer Information to your End User.</div>';
		$form['product_manufacture_deatils']['instuct_not_set']=array(
			'#type' => 'markup',
			'#value' =>$message_manufacture_instruct_not_set
		);
	}

/*
	$message_manufacture_instruct = '<div class="messages warning">Please Manage ( Add / Edit / Delete ) Manufacturer Name from your Premium Web Cart Merchant Panel.</div>';
	$form['product_manufacture_deatils']['instuct']=array(
		'#type' => 'markup',
		'#value' =>$message_manufacture_instruct
	);
*/
	$form['product_manufacture_deatils']['manufacturer_id'] = array(
		'#title' => t('Manufacturer'),
		'#type' => 'select',
		'#description' => t('example: Apple Computer Inc., Microsoft etc.'),
		'#options' => $options_manufacturer,
		'#default_value' => isset($node->manufacturer_id) ? $node->manufacturer_id : 0,
		'#weight' => $weight++
	);

	$form['product_manufacture_deatils']['product_manufacture'] = array(
		'#type' => 'textarea',
		'#title' => t('Product Manufacturer Details'),
		//'#description' => t(''),
		'#default_value' => $node->product_manufacture,
		'#weight' => $weight++
	);
	// END TAB product_manufacture

	// START TAB Image selection
	$form['product_image_selection']['#prefix'] = '<div id="product_image_selection">';
	$form['product_image_selection']['#suffix'] = '<div style="clear:both;"></div></div>';
	if(isset($node->nid)) {
		$product_images = pwcs_get_product_images($node->nid);
		if (!empty($product_images)){
			$message = '<div class="messages">Please Select the Image that you want to show as your Product Default Image to your User.</div>';
			$form['product_image_selection']['instuct']=array(
				'#type' => 'markup',
				'#value' =>$message
			);
			$form['product_image_selection']['pwc_image_select'] = array(
				'#type' => 'radios',
				'#options' => $product_images,
				'#default_value' => $node->pwc_image_select,
				'#title' => t('Select Your Product Primary Image: '),
				'#weight' => $weight++
			);
		}else {
			$message_noimage_uploaded = '<div class="messages">You have not uploaded any images for the Product from Upload Images (<b>click the Tab Upload Images</b>), please upload image and then Select the Image from here that you want to show as your Product Default Image to your User.</div>';
			$form['product_image_selection']['instuct_noimage_uploaded']=array(
				'#type' => 'markup',
				'#value' =>$message_noimage_uploaded
			);
		}
	}
	else
	{
		$message = '<div class="messages">You will be able to select product default image once the product is added/saved.</div>';
		$form['product_image_selection']['instuct']=array(
			'#type' => 'markup',
			'#value' =>$message
		);
	}
	// END TAB image selection

	// START TAB Videos
	$form['product_videos']['#prefix'] = '<div id="product_videos">';
	$form['product_videos']['#suffix'] = '<div style="clear:both;"></div></div>';

	$media_manager_videos = pwcs_get_videos_for_options();

	if (!empty($media_manager_videos)){
		$message = '<div class="messages">Please Select the video that you want to show with this product.</div>';
		$form['product_videos']['instuct']=array(
			'#type' => 'markup',
			'#value' =>$message
		);
		$form['product_videos']['videos_checkboxes'] = array(
			'#type' => 'checkboxes',
			'#options' => $media_manager_videos,
			'#default_value' => isset($node->videos_array)?$node->videos_array:array(),
			'#title' => t('Select videos for this product'),
			'#weight' => $weight++
		);
		$message_add_more .= '<div class="messages">'.t('Goto '.l('MEDIA MANAGER','siteadmin/media/videos').' to add more videos.').'</div>';
		$form['product_videos']['instuct_add_more']=array(
			'#type' => 'markup',
			'#weight' => $weight++,
			'#value' => $message_add_more
		);
	}else {
		$message = '<div class="messages warning">'.t('There\'s no video added at your '.l('MEDIA MANAGER','siteadmin/media/videos').'. Please goto '.l('MEDIA MANAGER','siteadmin/media/videos').' to add some videos then come here again to select those videos with your product.').'</div>';
		$form['product_videos']['instuct_novideos']=array(
			'#type' => 'markup',
			'#value' => $message
		);
	}
	// END TAB Videos


	// START TAB Related products
	$form['product_related_products_selection']['#prefix'] = '<div id="product_related_products_selection">';
	$form['product_related_products_selection']['#suffix'] = '<div style="clear:both;"></div></div>';
	$products_array = array();
	$products_array = pwcs_products_array($node->nid);
	$form['product_related_products_selection']['related_products_checkboxes'] = array(
		'#type' => 'checkboxes',
		'#options' => $products_array,
		'#default_value' => isset($node->related_products_array)?$node->related_products_array:array(),
		'#title' => t('Select related products for this product'),
		'#weight' => $weight++
	);
	$related_products_instruct = '<div class="messages">Add Products As Related Products For This Product.</div>';
	$form['product_related_products_selection']['instuct']=array(
		'#type' => 'markup',
		'#value' =>$related_products_instruct
	);
	// END TAB Related products

	// START TAB product attributes
	$form['product_attribute']['#prefix'] = '<div id="product_attribute_tab">';
	$form['product_attribute']['#suffix'] = '</div>';
	$form['product_attribute']['#collapsible'] = 0;
	$form['product_attribute']['#collapsed'] = 0;
	if(isset($node->nid)) {
		$attributes_instruction = '<div class="messages">'.t('To Add/Edit/Delete Product Options from PWC Core, Click the button below.').'</div>'
															.'<div class="button-container">'
															.l(t('Edit Options'), PWCS_PWC_AUTO_LOGIN_URL . PWCS_PWC_CORE_URL . 'products/products/productEdit/'.$node->pwc_product_row_id.'/7.html' , array('attributes' => array('iframewidth'=>'960', 'iframeheight'=>'600', 'class'=>'button pwcs-popup-iframe', 'title'=>'Premium Web Cart - Product Options')))
															.'</div>';

		$form['product_attribute']['attrib_list_tab']['instuct'] = array(
			'#type' => 'markup',
			'#value' => $attributes_instruction,
		);

		$form['product_attribute']['attrib_list_tab']['optionlist'] = array(
			'#type' => 'markup',
			'#value' => theme_pwcs_product_attributes_list($node),
		);

	} else {
		$message = '<div class="messages">You will be able to create option set once the product is added. After the product is saved, you will be able to assign option sets from the Edit Product section.</div>';
		$form['product_attribute']['attrib_list_tab']['instuct']=array(
			'#type' => 'markup',
			'#value' =>$message
		);
	}
	// END TAB product attributes


	// START TAB product attributes
	$form['product_more_options']['#prefix'] = '<div id="product_more_options">';
	$form['product_more_options']['#suffix'] = '</div>';
	$form['product_more_options']['#collapsible'] = 0;
	$form['product_more_options']['#collapsed'] = 0;

	$moreoption_html =	'<div class="messages">'.t('To Add/Edit Affiliate, Recurring, fulfillment etc., Click the button below.').'</div>'
											.'<div class="button-container">'
											.l(t('More Options from PWC Core'), PWCS_PWC_AUTO_LOGIN_URL . PWCS_PWC_CORE_URL . 'products/products/productEdit/'.$node->pwc_product_row_id.'.html' , array('attributes' => array('iframewidth'=>'960', 'iframeheight'=>'600', 'class'=>'button pwcs-popup-iframe', 'title'=>'Premium Web Cart - Product Edit')))
											.'</div>';

	$form['product_more_options']['iframe']=array(
		'#type' => 'markup',
		'#value' => $moreoption_html,
	);

	$form['#attributes'] = array('enctype' => "multipart/form-data");

	$js='$(function() {
			$("#pwcs_add_edit_products .standard").tabs();
		});';
	drupal_add_js($js, 'inline', 'footer');

	//pwcs_update_listing_table();
	//pwcs_bulk_clean_listing_table();
	//pwcs_make_order_table();
	//cache_clear_all();
  	//drupal_flush_all_caches();
	//unset($_SESSION['messages']);

	return $form;
}

function pwcs_product_edit_quickview($node) {
	
	//echo '<pre>';print_r($node);echo '</pre>';
	$quickview_status_yes = '<div class="pwcs_quickview_yes"></div>';
	$quickview_status_no = '<div class="pwcs_quickview_no"></div>';

	$output = '';

	// Basics
	$header = array(array('data' => t('Product Basics'), 'colspan'=>'3'));
	$rows = array();

	$quickview_status = $node->title?$quickview_status_yes:$quickview_status_no;
	$rows[] = array(
					array('data' => $quickview_status, 'width'=>'5%'), 
					array('data' => t('Product Name'), 'width'=>'20%'), 
					array('data' => $node->title, 'width'=>'75%')
					);

	$quickview_status = $node->product_sku?$quickview_status_yes:$quickview_status_no;
	$rows[] = array($quickview_status, t('Product SKU'), $node->product_sku);

	$quickview_status = $node->price?$quickview_status_yes:$quickview_status_no;
	$rows[] = array($quickview_status, t('Product Regular Price'), PWCS_BASIC_CURRENCY_SYMBOL.' '.$node->price);

	//$quickview_status = $node->salesprice?$quickview_status_yes:$quickview_status_no;
	//$rows[] = array($quickview_status, t('Product Sale Price'), PWCS_BASIC_CURRENCY_SYMBOL.' '.$node->salesprice);

	//$quickview_status = ($node->issalespriceactive==1) ? $quickview_status_yes : $quickview_status_no;
	//$rows[] = array($quickview_status, t('Sale Price is Active'), PWCS_BASIC_CURRENCY_SYMBOL.' '.$node->salesprice);


	$quickview_status = $node->product_category_title?$quickview_status_yes:$quickview_status_no;
	$rows[] = array($quickview_status, t('Product Category'), $node->product_category_title);

	$output .= theme('table', $header, $rows, array('width'=>'100%'));
	
	
	// Images
	$header = array(array('data' => t('Product Images'), 'colspan'=>'3'));
	$rows = array();
	$amount = count($node->field_product_image);
	if(isset($node->field_product_image[0]['fid']) && ($amount>0)) {
		$quickview_status = $quickview_status_yes;
		$image_details = $amount.' '.t('Images');
	} else {
		$quickview_status = $quickview_status_no;
		$image_details = t('No Image');
	}
	$rows[] = array(
					array('data' => $quickview_status, 'width'=>'5%'), 
					array('data' => t('Product Images'), 'width'=>'20%'), 
					array('data' => $image_details, 'width'=>'75%')
				);

	if($node->pwc_image_select) {
		$quickview_status = $quickview_status_yes;
		$ans = t('Set');
	} else {
		$quickview_status = $quickview_status_no;
		$ans = t('Not set');
	}
	$rows[] = array($quickview_status, t('Default Image'), $ans);

	$output .= theme('table', $header, $rows, array('width'=>'100%'));

	// Details
	$header = array(array('data' => t('Product Details'), 'colspan'=>'3'));
	$rows = array();

	$quickview_status = $node->teaser?$quickview_status_yes:$quickview_status_no;
	$rows[] = array(
					array('data' => $quickview_status, 'width'=>'5%'), 
					array('data' => t('Short Text'), 'width'=>'20%'), 
					array('data' => truncate_utf8($node->teaser, 70, true, true), 'width'=>'75%')
					);

	$quickview_status = $node->body?$quickview_status_yes:$quickview_status_no;
	$rows[] = array($quickview_status, t('Detail Text'), truncate_utf8($node->body, 70, true, true));

	$output .= theme('table', $header, $rows, array('width'=>'100%'));

	// Manufacture
	$header = array(array('data' => t('Product Manufacture'), 'colspan'=>'3'));
	$rows = array();

	if($node->manufacturer_id) {
		$quickview_status = $quickview_status_yes;
		$ans = t('Yes');
	} else {
		$quickview_status = $quickview_status_no;
		$ans = t('No');
	}
	$rows[] = array(
					array('data' => $quickview_status, 'width'=>'5%'), 
					array('data' => t('Product Manufacture?'), 'width'=>'20%'), 
					array('data' => $ans, 'width'=>'75%')
					);

	$quickview_status = $node->product_manufacture?$quickview_status_yes:$quickview_status_no;
	$rows[] = array($quickview_status, t('Manufacture Details'), truncate_utf8($node->product_manufacture, 70, true, true));

	$output .= theme('table', $header, $rows, array('width'=>'100%'));

	// Options
	$header = array(array('data' => t('Product Options'), 'colspan'=>'3'));
	$rows = array();

	if($node->product_options) {
		$quickview_status = $quickview_status_yes;
		$ans = t('Yes').', '.count($node->product_options).' '.t('options');
		if($node->default_attribute) {
			$ans .= '<br/>'.t('Default').': '.theme_pwcs_attributes_csv_to_textval($node->default_attribute);
		} else {
			$ans .= '<br/>'.t('But No Default Option Set');
		}
	} else {
		$quickview_status = $quickview_status_no;
		$ans = t('No options');
	}
	$rows[] = array(
					array('data' => $quickview_status, 'width'=>'5%'), 
					array('data' => t('Product Options?'), 'width'=>'20%'), 
					array('data' => $ans, 'width'=>'75%')
					);

	//$quickview_status = $node->product_manufacture?$quickview_status_yes:$quickview_status_no;
	//$rows[] = array($quickview_status, t('Manufacture Details'), truncate_utf8($node->product_manufacture, 70, true, true));

	$output .= theme('table', $header, $rows, array('width'=>'100%'));


	// Others
	$header = array(array('data' => t('Others'), 'colspan'=>'3'));
	$rows = array();

	if($node->featured) {
		$quickview_status = $quickview_status_yes;
		$ans = t('Yes');
	} else {
		$quickview_status = $quickview_status_no;
		$ans = t('No');
	}
	$rows[] = array(
					array('data' => $quickview_status, 'width'=>'5%'), 
					array('data' => t('Featured Product?'), 'width'=>'20%'), 
					array('data' => $ans, 'width'=>'75%')
					);


	if($node->related_products) {
		$quickview_status = $quickview_status_yes;
		$ans = t('Yes');
	} else {
		$quickview_status = $quickview_status_no;
		$ans = t('No');
	}
	$rows[] = array($quickview_status, t('Related Products?'), $ans);

	if($node->path) {
		$quickview_status = $quickview_status_yes;
		$ans = t('Yes');
	} else {
		$quickview_status = $quickview_status_no;
		$ans = t('No');
	}
	$rows[] = array($quickview_status, t('SEO?'), $ans);

	$quickview_status = $node->regular_buy_link?$quickview_status_yes:$quickview_status_no;
	$rows[] = array($quickview_status, t('Regular Buy Link'), $node->regular_buy_link);

	$quickview_status = $node->one_click_buy_link?$quickview_status_yes:$quickview_status_no;
	$rows[] = array($quickview_status, t('One Click Buy Link'), $node->one_click_buy_link);

	$output .= theme('table', $header, $rows, array('width'=>'100%'));

	return $output;
}

/**
 *
 * Implementation of hook_validate()
 * Validates the required form fields
 * Added by Rasel Kabir
 */
function pwcs_product_validate($node, &$form_values)
{
	//echo '<pre>';
	//print_r ($form_values);
	//echo '</pre>';
}

/**
 * Implementation of hook_load()
 * Added by Rasel Kabir
 */
function pwcs_product_load($node) {

	$additions = db_fetch_object(db_query("SELECT * FROM {pwcs_product} WHERE nid = %d", $node->nid));

	if($additions->related_products)
		$additions->related_products_array = explode(",", $additions->related_products);

	if($additions->videos)
		$additions->videos_array = explode(",", $additions->videos);

	if($additions->listing_id){
		$additions->product_listing_array = explode(",", $additions->listing_id);
	}	

	// Product Additional Images
	$results=db_query("SELECT * FROM {pwcs_product_images} WHERE nid = %d", $node->nid);
	$additional_images = array();
	while($result=db_fetch_array($results)) {
		$additional_images[] = $result;
    }
	$additions->product_additional_images = $additional_images;
	// Product Additional Images

	// Product Attributes
//    $query=db_query("SELECT *
//                     FROM {pwcs_product_attributes} pa
//                     WHERE pa.nid = %d", $node->nid."  GROUP BY pa.attributes_id");
//    $set_name='';
//    $attribute_list=array();
//    $counter=0;
//    $group_counter=0;
//    while($row=db_fetch_object($query))
//    {
//        if($set_name!=$row->set_name)
//        {
//            if($counter!=0)
//            {
//                $group_counter++;
//            }
//            $attribute_list[$row->set_name]['quantity']=$row->quantity;
//            $attribute_list[$row->set_name]['price']=$row->inc_desc_amount;
//            $attribute_list[$row->set_name][$row->attr_group_id]=$row->attr_id;
//            $set_name=$row->set_name;
//        }
//        else
//        {
//            $attribute_list[$row->set_name][$row->attr_group_id]=$row->attr_id;
//
//        }
//
//        $counter++;
//
//    }
//
//    $additions->product_attributes = $attribute_list;
	// Product Attributes

	// Products Options
	$results=db_query("SELECT * FROM {pwcs_product_attributes} WHERE nid = %d", $node->nid);
	$product_options = array();
	while($result=db_fetch_array($results)) {
		$product_options[] = $result;
  }
	$additions->product_options = $product_options;

	// Category Name
	$category_details = pwcs_category_details($additions->pwc_category_id);
	$additions->product_category_title = $category_details['category_title'];

	//	// category
	//	$lists = db_query("SELECT * FROM {pwcs_product_category} WHERE product_nid = %d", $node->nid);
	//	while ($list = db_fetch_object($lists)) {
	//		$category_list[$list->category_id] = db_result(db_query("SELECT `category_name` FROM {pwcs_category} WHERE category_id = %d", $list->category_id)); //query to get the category name
	//	}
	//	$additions->product_category = $category_list;

	$padded_pwc_product_row_id = str_pad($additions->pwc_product_row_id, PWCS_BASIC_PRODUCTID_PADDING_AMOUNT, "0", STR_PAD_LEFT);	
	$additions->pwc_product_id = PWCS_BASIC_PRODUCTID_PREFIX.'-'.$padded_pwc_product_row_id;

	// Product Sale Price
	$additions->product_saleprice = pwcs_get_sale_price_info($node->nid);

	$node->title = html_entity_decode($node->title);

	return $additions;
}

function pwcs_get_sale_price_info($nid = 0){

	$sql  = 'SELECT * FROM {pwcs_product_sales}';
	$sql .= ' WHERE enable_sales_price = 1';
	$sql .= ' AND (((sale_price_start_date = 0) && (sale_price_end_date = 0)) || ((sale_price_start_date < '.time().' < sale_price_end_date)))';
	if($nid != 0) $sql .= ' AND nid = '.$nid;

	$product_saleprice = NULL;
	$results=db_query($sql);
	if ($results->num_rows == 1) {
		while($result=db_fetch_array($results)) {
			$product_saleprice = $result;
		}
	}
	if ($results->num_rows > 1) {
		while($result=db_fetch_array($results)) {
			$product_saleprice[$result['nid']] = $result;
		}
	}
	return $product_saleprice;
}
/**
 * function for file upload and preparing node variable
 * Added by Rasel Kabir
 */
function pwcs_product_prepare($node) {

	//comma separated related product image nid
	if($node->related_products_checkboxes) {
		$node->related_products = implode(",", pwcs_checkboxes_to_array($node->related_products_checkboxes));
	}
	if($node->videos_checkboxes) {
		$node->videos = implode(",", pwcs_checkboxes_to_array($node->videos_checkboxes));
	}
	if($node->product_listing_checkboxes) {
		$node->listing_id = implode(",", pwcs_checkboxes_to_array($node->product_listing_checkboxes));
	} else {
		$node->listing_id = -1;
	}

	// START: Setting product primary image
	if(isset($node->field_product_image[0]['fid'])) {
		if(($node->is_new == 1)||($node->pwc_image_select == 0)){
			$node->pwc_image_select = $node->field_product_image[0]['fid'];
			$node->product_image_path = $node->field_product_image[0]['filepath'];
		}
		else {
			$imageSelectExists = 0;
			foreach( $node->field_product_image as $key => $item){
				if($item['fid'] == $node->pwc_image_select) {
					$imageSelectExists = 1;
				}
			}
			if($imageSelectExists == 0) {
				$node->pwc_image_select = $node->field_product_image[0]['fid'];
				$node->product_image_path = $node->field_product_image[0]['filepath'];
			}
		}
	} else {
		unset($node->pwc_image_select);
	}
	// END: Setting product primary image

	return $node;
}

/**
 * Implementation of hook_insert()
 * Added by Rasel Kabir
 */
function pwcs_product_insert($node) {

	global $user;

	$node = pwcs_product_prepare($node);
	//$product_image = $node->product_image_upload;
	if(!isset($node->sync_api)) {
		$node->is_sync = 0;
		$node->created_on = time();
		$node->modified_on = time();
	}

	db_query("INSERT INTO {pwcs_product}
				(`nid` , `pwc_product_row_id` , `product_link_id` , `product_sku` , `product_name` ,
				`pwc_category_id` , `price` , `regular_buy_link` , `one_click_buy_link` , `product_image_path` ,
				`is_sync` , `created_on` , `modified_on`, `pwc_image_select`, `related_products`, `product_manufacture`, `manufacturer_id`, `default_attribute`,`listing_id`, `videos` )
			VALUES (%d, '%s', '%s', '%s', '%s',
					'%s' , '%s' , '%s', '%s', '%s',
					%d, %d, %d, %d, '%s', '%s', %d, '%s', '%s', '%s')",
			$node->nid,	$node->pwc_product_row_id, $node->product_link_id, $node->product_sku, $node->product_name,
			$node->pwc_category_id, $node->price, $node->regular_buy_link, $node->one_click_buy_link, $node->product_image_path,
			$node->is_sync, $node->created_on, $node->modified_on, $node->pwc_image_select, $node->related_products, $node->product_manufacture, $node->manufacturer_id, $node->default_attribute,$node->listing_id,$node->videos
	);

	$node->issalespriceactive  = isset($node->issalespriceactive) ? $node->issalespriceactive : 0; 
	$node->salesprice          = isset($node->salesprice) ? $node->salesprice : 0.00; 
	$node->salestag            = isset($node->salestag) ? $node->salestag : 0; 
	$node->salepricestartdate  = isset($node->salepricestartdate) ? $node->salepricestartdate : ''; 
	$node->salepriceenddate    = isset($node->salepriceenddate) ? $node->salepriceenddate : ''; 
			
	if (db_result(db_query('SELECT pwc_product_row_id FROM {pwcs_product_sales} WHERE pwc_product_row_id = %d', $node->pwc_product_row_id))!= $node->pwc_product_row_id){			
			$result = db_query("INSERT INTO {pwcs_product_sales}" .
								" (`nid`, `pwc_product_row_id`, `product_sale_price`,`enable_sales_price`, `sale_tag_icon`,`sale_price_start_date`,`sale_price_end_date`)" .
								" VALUES (%d, %d, '%f', %d, %d, %d, %d)",
								$node->nid, $node->pwc_product_row_id, $node->salesprice , $node->issalespriceactive, $node->salestag, $node->salepricestartdate, $node->salepriceenddate );
	}else{
			$result = db_query("UPDATE {pwcs_product_sales}" .
								" SET `product_sale_price`='%f', `enable_sales_price`=%d, `sale_tag_icon`=%d, `sale_price_start_date`=%d, `sale_price_end_date`=%d " .
								" WHERE pwc_product_row_id = %d",
								$node->salesprice, $node->issalespriceactive, $node->salestag, $node->salepricestartdate, $node->salepriceenddate, $node->pwc_product_row_id);
	} 


//	$category_selected = $node->category_selected;
//	foreach ($category_selected as $i => $category_id) {
//		db_query("INSERT INTO {pwcs_product_category} (`product_nid`, `category_id`) VALUES (%d, %d)", $node->nid, $category_id);
//	}

	//CALL API FOR PRODUCT INSERT

	if(!isset($node->sync_api)) {

		$api_data_product['product_nid'] = $node->nid;
		$api_data_product['pwc_product_row_id'] = '';
		$api_data_product['product_name'] = $node->title;
		$api_data_product['product_sku'] = $node->product_sku;
		$api_data_product['pwc_category_id'] = $node->pwc_category_id;
		$api_data_product['price'] = $node->price;

		$api_data_product['shorttext']            = isset($node->teaser) ? $node->teaser : '';
		$api_data_product['longtext']             = isset($node->body) ? $node->body : '';
		$api_data_product['product_manufacture']  = isset($node->product_manufacture) ? $node->product_manufacture : '';
		$api_data_product['manufacturer_id']      = isset($node->manufacturer_id) ? $node->manufacturer_id : '0';


		if($user->merchant_enc_id != NULL) {
			$responce_xml = pwcs_api_insertProductAPI($user->merchant_enc_id, $api_data_product);

			if (module_exists('xml_parser')){
				$responce_xml = xml_clean($responce_xml);
				if ($responce_xml != "error")
				$responce_xml = xml_parser_string_to_array($responce_xml);
			}else{
				return false;
			}
            if ($responce_xml != "error"){
		 		$product_pwc_data = $responce_xml['products']['product'];
				db_query("UPDATE {pwcs_product}
						SET `pwc_product_row_id`='%s', `product_link_id`='%s', `regular_buy_link`='%s', `one_click_buy_link`='%s', `is_sync`=%d WHERE nid = %d",
						$product_pwc_data['product_row_id'], $product_pwc_data['product_link_id'],$product_pwc_data['regularbuylink'],$product_pwc_data['oneclickbuylink'] , 1, $node->nid
				);
				$message['status'] = t('Product Update Notification Request sent to Premium Web Cart.');
            }else {
				$message['error'] = t('Could Not Connect to Premium Web Cart to send Product Update Notification as part of Synchronization.');
            }
		}
		else{
	   		drupal_set_message(t('Missing Merchant Id'), 'error');
			$_REQUEST[destination] = 'user/'.$user->uid.'/edit';
		}

		if (user_access('is siteadmin')){
			//$_REQUEST[destination] = 'siteadmin/products';
			pwcs_update_listing_table();
			pwcs_bulk_clean_listing_table();
			pwcs_make_order_table();
			cache_clear_all();
		  	drupal_flush_all_caches();
			unset($_SESSION['messages']);
			foreach ($message as $type => $message_text) {
				drupal_set_message($message_text, $type);
			}

			pwcs_sitemap_file_write();
	   		pwcs_pingto_search_engine();
			$_REQUEST[destination] = 'node/'.$node->nid.'/edit';
		}
	} else {

	}
}


/**
 * Implementation of hook_update()
 * Added by Rasel Kabir
 */
function pwcs_product_update($node) {

	global $user;

  if($node->product_attribute_edit_form == 1) {
		pwcs_insert_product_attribute_group($node);
  }

	if(!isset($node->sync_api)) {
		$node = pwcs_product_prepare($node);
		$node->is_sync = 0;
		$node->modified_on = time();
	}

	db_query("UPDATE {pwcs_product}
			SET `pwc_product_row_id`='%s', `product_link_id`='%s', `product_sku`='%s', `product_name`='%s',
				`pwc_category_id`='%s', `price`='%s', `regular_buy_link`='%s', `one_click_buy_link`='%s', `product_image_path`='%s',
				`is_sync`=%d, `modified_on`=%d, `pwc_image_select`=%d, `related_products`='%s', `product_manufacture`='%s', `manufacturer_id` =%d, `default_attribute`='%s',`listing_id`='%s', `videos`='%s'  WHERE nid = %d",
			$node->pwc_product_row_id, $node->product_link_id, $node->product_sku, $node->product_name,
			$node->pwc_category_id, $node->price, $node->regular_buy_link, $node->one_click_buy_link, $node->product_image_path,
			$node->is_sync, $node->modified_on, $node->pwc_image_select, $node->related_products, $node->product_manufacture, $node->manufacturer_id, $node->default_attribute, $node->listing_id, $node->videos, $node->nid
	);


	$node->issalespriceactive  = isset($node->issalespriceactive) ? $node->issalespriceactive : 0; 
	$node->salesprice  = isset($node->salesprice) ? $node->salesprice : 0.00; 
	$node->salestag  = isset($node->salestag) ? $node->salestag : 0; 
	$node->salepricestartdate  = isset($node->salepricestartdate) ? $node->salepricestartdate : ''; 
	$node->salepriceenddate    = isset($node->salepriceenddate) ? $node->salepriceenddate : ''; 
	
			
	if (db_result(db_query('SELECT pwc_product_row_id FROM {pwcs_product_sales} WHERE pwc_product_row_id = %d', $node->pwc_product_row_id))!= $node->pwc_product_row_id){			
			$result = db_query("INSERT INTO {pwcs_product_sales}" .
								" (`nid`, `pwc_product_row_id`, `product_sale_price`,`enable_sales_price`, `sale_tag_icon`,`sale_price_start_date`,`sale_price_end_date`)" .
								" VALUES (%d, %d, '%f', %d, %d, %d, %d)",
								$node->nid, $node->pwc_product_row_id, $node->salesprice , $node->issalespriceactive, $node->salestag, $node->salepricestartdate, $node->salepriceenddate );
	}else{
			$result = db_query("UPDATE {pwcs_product_sales}" .
								" SET `product_sale_price`='%f', `enable_sales_price`=%d, `sale_tag_icon`=%d, `sale_price_start_date`=%d, `sale_price_end_date`=%d " .
								" WHERE pwc_product_row_id = %d",
								$node->salesprice, $node->issalespriceactive, $node->salestag, $node->salepricestartdate, $node->salepriceenddate, $node->pwc_product_row_id);
	} 
  
 

	// CALL API FOR PRODUCT EDIT
	if((!isset($node->sync_api)) && ($user->uid != 1)) {

		$api_data_product['product_nid'] = $node->nid;

		$product = node_load($node->nid);
		$api_data_product['pwc_product_row_id'] = $product->pwc_product_row_id;

		$api_data_product['product_name'] = $node->title;
		$api_data_product['product_sku'] = $node->product_sku;
		$api_data_product['pwc_category_id'] = $node->pwc_category_id;
		$api_data_product['price'] = $node->price;

		$api_data_product['shorttext'] = isset($node->teaser) ? $node->teaser : '';
		$api_data_product['longtext']  = isset($node->body) ? $node->body : '';
		$api_data_product['product_manufacture']  = isset($node->product_manufacture) ? $node->product_manufacture : '';
		$api_data_product['manufacturer_id']      = isset($node->manufacturer_id) ? $node->manufacturer_id : '0';


		if($user->merchant_enc_id != NULL) {
			$responce_xml = pwcs_api_insertProductAPI($user->merchant_enc_id, $api_data_product);

			if (module_exists('xml_parser')){
				$responce_xml = xml_clean($responce_xml);
				if ($responce_xml != "error")
				$responce_xml = xml_parser_string_to_array($responce_xml);
			}else{
				return false;
			}

			if ($responce_xml != "error"){
				$product_pwc_data = $responce_xml['products']['product'];
				if($product_pwc_data['name'] == $node->title) {
					$product_pwc_data = $responce_xml['products']['product'];
					db_query("UPDATE {pwcs_product} SET `is_sync`=%d WHERE nid = %d", 1, $node->nid );
					drupal_set_message(t('Selected Product has been Synchronized in both Premium Web Cart & Your Store Site.'));
				}
            }else {
				drupal_set_message(t('Selected Product Could Not be Synchronized with Premium Web Cart.'), 'error');
            }
		} else {
	   		drupal_set_message(t('Missing Merchant Id'), 'error');
			$_REQUEST[destination] = 'user/'.$user->uid.'/edit';
		}

	}// CALL END OF API PRODUCT EDIT
	
	//Update Listing
	pwcs_update_listing_table();
	pwcs_bulk_clean_listing_table();
	pwcs_make_order_table();
  	
	pwcs_sitemap_file_write();
	pwcs_pingto_search_engine();
	
	cache_clear_all();
  	drupal_flush_all_caches();
	unset($_SESSION['messages']);    

    $node->returnhere = isset($node->returnhere) ? $node->returnhere : 0;
    //Switching the User to Product Update Page after Product Update
	if (user_access('is siteadmin') && ($node->returnhere==0)){
		$_REQUEST[destination] = 'node/'.$node->nid.'/edit';
	}

}

/**
 * Implementation of hook_delete()
 * Added by Rasel Kabir
 */
function pwcs_product_delete($node) {
	db_query("DELETE FROM {pwcs_product} WHERE nid = %d", $node->nid);
	db_query("DELETE FROM {pwcs_product_images} WHERE nid = %d", $node->nid);
	db_query("DELETE FROM {pwcs_product_attributes} WHERE nid = %d", $node->nid);

	if(!isset($node->sync_api)) {
		if (user_access('is siteadmin')){
			$_REQUEST[destination] = 'siteadmin/products';
		}
	}
}

/**
 * Implementation of hook_access()
 * Added by Rasel Kabir
 */
function pwcs_product_access($op, $node) {

	global $user;
	$return = false;

	switch ($op)
	{
		case 'view':
			$return = user_access('access pwcs product');
			break;

		case 'create':
			$return = user_access('create pwcs product');
			break;

		case 'update':
		case 'delete':
			$return = user_access('manage pwcs product');
			break;
	}
	return $return;
}

/**
 * Implementation of hook_view()
 * Added by Rasel Kabir
 */
function pwcs_product_view($node, $teaser = FALSE, $page = FALSE) {

	if($page == TRUE) {
		$breadcrumb = array();
		$breadcrumb[] = l(t('Home'), '<front>');
		$breadcrumb[] = l($node->product_category_title, 'products/'.$node->pwc_category_id);
		$breadcrumb[] = drupal_get_title(); //html_entity_decode(drupal_get_title());
		drupal_set_breadcrumb($breadcrumb);
	
		//drupal_set_title(t('Product Details'));
		$product = node_load($node->nid);
	
	 	$node->product_details['image'] = theme_pwcs_product_details_defaultimage($product);
	 	$node->product_details['additional_images'] = theme_pwcs_product_details_additionalimage($product);
	
		if($product->product_options) {
			$node->product_details['attributes'] = theme_pwcs_product_details_attributes($product);
		}
	
		if($product->product_saleprice['product_sale_price']){
			$saletag_icon = theme('image', variable_get('pwcs_product_saleprice_icon_'.$product->product_saleprice['sale_tag_icon'], PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag'.$product->product_saleprice['sale_tag_icon'].'.png'), '', '', array('width'=>'auto'), FALSE);
			$node->product_details['price'] = '<span class="price_strike_through">'.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</span>'
											  .' <span class="saleprice">'
											  .PWCS_BASIC_CURRENCY_SYMBOL.'<span id="productpriceamount_'.$product->nid.'">'.$product->product_saleprice['product_sale_price'].'</span> '
											  .$saletag_icon
											  .'</span>';
		} else {
		 	$node->product_details['price'] = PWCS_BASIC_CURRENCY_SYMBOL.'<span id="productpriceamount_'.$product->nid.'">'.$product->price.'</span>';
		}

	 	$node->product_details['sku'] = '<span id="productskutext_'.$product->nid.'">'.(($product->product_sku)?$product->product_sku:'<em>'.t('No SKU').'</em>').'</span>';
	 	$node->product_details['quantityform'] = '<input type="text" id="productdetails_addtocart_qty" product_id= "'.$product->nid.'" class="form-text" value="1" size="1" maxlength="5">';

		if($_SESSION['pwcs_cart']['editcartitem']['product_nid'] == $product->nid) {
			$cartbuttontext = t('Update Cart');
		} else {
			$cartbuttontext = variable_get('pwcs_productdetails_addtocartlink_text', 'Add To Cart'); //t('Add To Cart');
		}
	 	$node->product_details['addtocart'] = l($cartbuttontext,'addtocart/'.$product->nid, array('attributes' => array('class' => 'pwcs-default-link-button addtocart-link', 'ajax_addtocart_url' => base_path().'pwcsajax/addtocart' , 'product_id'=>$product->nid, 'addtocart_quantiy'=>1, 'product_link_id'=>$product->product_link_id, 'name' => 'product-addtocart', 'id' => 'addtocart-id-'.$product->nid, 'title' => 'Click here to add this product to your cart')));

		if(variable_get('pwcs_sitesettings_oneclickbuy', '1') == 1){
			$node->product_details['oneclickbuy'] = l(variable_get('pwcs_sitesettings_oneclickbuy_text', 'One Click Buy'), $product->one_click_buy_link, array('attributes' => array('class' => 'pwcs-default-link-button one_click_buy_link', 'id' => 'cart-continue-shopping')));
		} else {
			$node->product_details['oneclickbuy'] = '';
		}
		$node->product_details['backbutton'] = '<a class="pwcs-default-link-button" href="javascript: history.go(-1)">Back</a>';
	
	 	$node->product_details['body'] = $product->body;//html_entity_decode($product->body);
	 	
	 	if(variable_get('pwcs_sitesettings_product_detail_show_manufacturer', '1') == 1) {
			if($product->product_manufacture) 
				$product_manufacturer_name = '<div class="product_manufacturer_details_text">'.$product->product_manufacture.'</div>';
		
			if($product->product_manufacture) 
				$product_manufacturer_details_text = '<div class="product_manufacturer_details_text">'.$product->product_manufacture.'</div>';
		
		 	$node->product_details['product_manufacture'] = '<div class="product_manufacturer_name">'.$product->product_manufacture.'</div>'
														 	.'<div class="product_manufacturer_details_text">'.$product->product_manufacture.'</div>';
	
		}
		
	 	if(variable_get('pwcs_sitesettings_product_detail_relatedproducts', '1') == 1) {
			if($product->related_products_array) {
				$related_products_html = '';
				foreach($product->related_products_array as $k => $related_nid){
					$related_product = node_load($related_nid);
					$related_products_html .= pwcs_single_product_view('spv_small', $related_product);
				}
				$related_products_html .= '<div style="clear:both"></div>';
			 	$node->product_details['product_related_products'] = '<div class="product_related_products">'.$related_products_html.'</div>';
			}
		}
	
		if(variable_get('pwcs_product_video_showat_productdetails', '1') == 1) {
			if($product->videos_array) {
				$videos_html = '';
				foreach($product->videos_array as $k => $video_id){
					$mediavideo = pwcs_video_manager_video_array($video_id);
					$videos_html .= theme_pwcs_product_video_each($mediavideo);
				}
			 	$node->product_details['product_videos'] = '<div class="product_videos">'.$videos_html.'<div style="clear:both"></div></div>';
			}
		}

		if(variable_get('pwcs_sitesettings_productreview_tab', '1') == 1) {
			$comments_summary_html = pwcs_product_comments_summary ($node->nid);
			$comments_html = pwcs_product_comments ($node->nid);
			$comments_form = pwcs_comment_form($node->nid);
			$node->product_details['product_customer_review'] = $comments_summary_html . $comments_html . $comments_form;
		}
	}

  return $node;
}
/**
 * PWCS THEME for Video View
 * Added By Rasel Kabir
 */
function theme_pwcs_product_video_each($mediavideo = array()){

	$videos_html = '';

	$video_text = t(variable_get('pwcs_product_video_icon_text', 'Play Video (!video_title)'), array( '!video_title' => $mediavideo['video_title'] ));
	$video_icon = variable_get('pwcs_product_video_icon', 'auto_thumb');

	$videos_html .= '<div class="product_videos_each">';
	if($video_icon == ''){
		$videos_html .= '<a href="'.$mediavideo['video_url'].'" rel="lightvideo['.$product->nid.'|width:400px;]">';
		$videos_html .= $video_text;
		$videos_html .= '</a>';
	} else if($video_icon == 'auto_thumb') {
		
		$parse_url = parse_url( $mediavideo['video_url'] );

		if($parse_url['host'] == 'www.youtube.com') {
			$v = '';
			parse_str( parse_url( $mediavideo['video_url'], PHP_URL_QUERY ) );
			$video_thumb = 'http://img.youtube.com/vi/'.$v.'/default.jpg';
		}
		else if($parse_url['host'] == 'www.metacafe.com') {
			if(preg_match("/((http:\/\/)?(www\.)?metacafe\.com)(\/watch\/)(\d+)(.*)/i", $mediavideo['video_url'], $match)) {
			    $video_id = $match[5];
			}
			$video_thumb = 'http://www.metacafe.com/thumb/'.$video_id.'.jpg';
		} else {
			$video_thumb = PWCS_MODULE_IMAGE.'video_thumb_default.jpg';
		}
		$videos_html .= '<a href="'.$mediavideo['video_url'].'" rel="lightvideo['.$product->nid.'|width:400px;]">';
		$videos_html .= theme('image', $video_thumb, '', '', array('width'=>'auto', 'style'=>'vertical-align: text-bottom;'), FALSE);
		$videos_html .= '</a>';
		$videos_html .= '<div>'.$video_text.'</div>';
	} else {
		$videos_html .= '<a href="'.$mediavideo['video_url'].'" rel="lightvideo['.$product->nid.'|width:400px;]">';
		$videos_html .= theme('image', $video_icon, '', '', array('width'=>'auto', 'style'=>'vertical-align: text-bottom;'), FALSE).$video_text;
		$videos_html .= '</a>';
		//$videos_html .= '<div>'.$video_text.'</div>';
	}
	$videos_html .= '</div>';

	return $videos_html;
}

/**
 * Fucntion for Product Details - Attributes
 * Added By Rasel Kabir
 */
function theme_pwcs_product_details_attributes($product) {

	$result1 = db_query("SELECT * FROM {pwcs_product_attributes} WHERE nid = %d ORDER BY product_attributes_id", $product->nid);
	while($item = db_fetch_object($result1))
	{
		$sql = "SELECT g.*,
			       a.attr_name,
			       a.pwc_attribute_id AS attri_id,
			       a.pwc_attribute_id
			       FROM {pwcs_attr_groups} AS g JOIN 
		            {pwcs_attributes} AS a ON(a.group_id = g.id)
		            WHERE a.pwc_attribute_id IN({$item->attr_id}) ORDER BY a.weight ASC";
		$result2 = db_query($sql);
		while($item2 = db_fetch_object($result2))
		{
			$productOptions[$item2->id][$item2->attri_id] = $item2;
		}        
	}

  $optionHtml  = '<div id="pwcs_productdeatils_options_main_'.$product->nid.'" class="pwcs_productdeatils_options_main">';
  $id = 0;
	if(is_array($productOptions))
	foreach($productOptions as $key => $options)
	{
		$count = 1;
		
		foreach($options as $optn)
		{
		    if($count == 1)
		    {
		        $requiredText = $optn->is_required ? ' <span class="required">*</span> ' : ' ';
		        $isRequired   = $optn->is_required ? ' required="1" exclude=" "' : ' ';
		        
		        $optionHtml  .= '<div class="each_option">';
		        $optionHtml  .= '<span class="attr_group_public_name">'.$optn->attr_group_public_name.'</span>';
		        $id++ ;
		        $optionHtml  .= '<span class="attr_group_select"><select ' . $isRequired . ' pwc_group_row_id="'.$optn->pwc_group_row_id.'" id="pwcs_productdeatils_select_'.$product->nid.'_'.$id.'" name="product_option[' . $optn->attri_id . ']" realName="' . $optn->attr_group_public_name . '">';
		        //$optionHtml  .= '<option value="">'.t('Select').' '.$optn->attr_group_public_name.'</option>';
		        $optionHtml  .= '<option value="">- - - - - - - - - - - - - - -</option>';
		        $count = 0;
		    }
		    $selected =  ''; //in_array($optn->attri_id, $selected_attributes) ? 'selected' : '';
		    $optionHtml .= '<option value='.$optn->attri_id.' '.$selected.'>'.$optn->attr_name.'</option>';
		}
		$optionHtml .= '</select></span>'.$requiredText.'</div>';
	} 

	if($product->product_saleprice['product_sale_price']){
		$product_real_price = $product->product_saleprice['product_sale_price'];
	} else {
		$product_real_price = $product->price;
	}
	
	$optionHtml .= '<div class="option_buttons">';
	$optionHtml .= '<div class="ajax_status"></div>';
	$optionHtml .= '<a id="pwcs_productdeatils_options_chkavail_'.$product->nid.'" class="pwcs-default-link-button pwcs_productdeatils_options_chkavail" ' .
									'csv_options="" ' .
									'product_real_price="'.$product_real_price.'" ' .
									'product_real_sku="'.$product->product_sku.'" ' .
									'product_nid="'.$product->nid.'" ' .
									'ajax_url="'.PWCS_STORE_URL.'pwcsajax/product_details_options_checkavail'.'" ' .
									'count="' . $id . '"' .
									'>' .
									t('Check Availability').
									'</a>';
	$optionHtml .= '<a id="pwcs_productdeatils_options_showall_'.$product->nid.'" class="pwcs-default-link-button pwcs_productdeatils_options_showall" ' .
									'product_nid="'.$product->nid.'" ' .
									'current_selection="" ' .
									'ajax_url="'.PWCS_STORE_URL.'pwcsajax/product_details_options_showall'.'" ' .
									'>' .
									t('Show All') .
									'</a>';
	$optionHtml .= '</div>';
	$optionHtml .= '</div>';

	return $optionHtml;
}


/**
 * Fucntion for Product Details - Options - Check Available - AJAX
 * Added By Rasel Kabir
 */
function pwcs_ajax_product_details_options_checkavail() {
	
		$product_nid = $_REQUEST['product_nid'];
		$csv_options = $_REQUEST['csv_options'];
		
		//Sorting $csv_options
		$csv_options_temp = explode(",",$csv_options);
		sort($csv_options_temp);
		$csv_options = implode(",",$csv_options_temp);

		if(isset($product_nid) && isset($csv_options)) {

			$sql = 'SELECT * FROM {pwcs_product_attributes} WHERE attr_id= "'.$csv_options.'" AND nid = '.$product_nid;
			
			if(pwcs_result_count($sql) > 0) {
				$results = db_query($sql);
				while($result = db_fetch_object($results))
				{
				    $ret = array(
				      'status' => 'ok',
				      'msgs' => t('Item in stock for immediate shipment!'),
				      'quantity' => $result->quantity,
				      'affected_price' => $result->affected_price,
				      'subsku' => $result->subsku,
				    );
				}
			} else {
		    $ret = array(
		      'status' => 'notfound',
		      'msgs' => '<font style="color:red;">'.t('Sorry! Option not available!').'</font>',
		    );
			}

		} else {
			//echo ('error');
			$ret = array(
	      'status' => 'error',
	      'msgs' => t('Product ID and/or Options not found'),
	    );
		}
		//echo $ret['status']; exit;
		drupal_json($ret); //exit;
}


/**
 * Fucntion for Product Details - Options - Show All - AJAX
 * Added By Rasel Kabir
 */
function pwcs_ajax_product_details_options_showall() {
	
		$product_nid = $_REQUEST['product_nid'];
		if(isset($product_nid)) {
			$node = node_load($product_nid);
			if(isset($node->product_options)) {
		    $ret = array(
		      'status' => 'ok',
		      'msgs' => theme_pwcs_product_attributes_list($node, TRUE),
		    );
			} else {
		    $ret = array(
		      'status' => 'notfound',
		      'msgs' => t('Options not found'),
		    );
			}

		} else {
			$ret = array(
	      'status' => 'error',
	      'msgs' => t('Product ID and/or Options not found'),
	    );
		}
		//echo $ret['status']; exit;
		drupal_json($ret); //exit;
}


/**
 * Fucntion for Product Details - Default Image
 * Added By Rasel Kabir
 */
function theme_pwcs_product_details_defaultimage($product){

	$default_image = pwcs_get_product_primary_image_path($product);
	$output  = '';
	$output .= '<div class="product-defaultimage" id="productdetails_defaultimage">';
	//$output .= '<a href="'.base_path().$default_image.'" rel="lightbox['.$product->nid.']['.$product->title.']">';
	$output .= theme('image', $default_image, '', $product->title, array('width'=>'95%'), FALSE);
	//$output .= '</a>';
	$output .= '</div>';
	return $output;
}


/**
 * Fucntion for Product Details - Additional Image
 * Added By Rasel Kabir
 */
function theme_pwcs_product_details_additionalimage($product){

	//$product->field_product_image
	$additonal_images = array();
	if(isset($product->product_additional_images)) {
		foreach($product->product_additional_images as $key=>$val){
			if($val['ppi_filepath'] != '') {
				$image = array();
				$image['pwcs_product_imagetype'] = 'imagetable';
				$image['image_source'] = 'remote';
				$image['nid'] = $val['nid'];
				$image['imagepath'] = $val['ppi_filepath'];
				$additonal_images[] = $image;
			}
		}
	}

	if(isset($product->field_product_image)) {
		foreach($product->field_product_image as $key=>$val){
			if($val['filepath'] != '') {
				$image = array();
				$image['pwcs_product_imagetype'] = 'cckimage';
				$image['image_source'] = 'not_remote';
				$image['nid'] = $val['nid'];
				$image['imagepath'] = $val['filepath'];
				$image['fid'] = $val['fid'];
				$additonal_images[] = $image;
			}
		}
	}

	if(empty($additonal_images)){
		return '';
	}
	$output = '';
	$output .= '<div class="product-additional-images" id="productdetails_additionalimage">';
	foreach($additonal_images as $key=>$val){
		$output .= '<div class="product-additional-images-each" style="">';
		if($val['pwcs_product_imagetype'] == 'cckimage') {
			$link_path = base_path().$val['imagepath'];
		} else {
			$link_path = $val['imagepath'];
		}
		$output .= '<a href="'.$link_path.'" rel="lightbox['.$product->nid.']['.$product->title.']">';
		$output .= theme('image', $val['imagepath'], '', $product->title, array('width'=>'60px', 'height'=>'60px'), FALSE);
		$output .= '</a>';
		$output .= '</div>';
	}
	$output .= '<div style="clear:both;"></div>';
	$output .= '</div>';
	return $output;
}

/********************* END OF NODE/HOOK FUNTIONS **************/

/**
 * Site Admin product page theme
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_products($action=NULL) {

	$output = '';
	$output .= '<div class="pwcs-page-content-inner">';
	if($action == 'featured') {
		unset($_SESSION['pwcs_products_filter']);
		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
		$output .= pwcs_product_list('featured', array('featured'=>'1'));
		$output .= '</div>';
	}
//	else if($action == 'reconditioned') {
//		unset($_SESSION['pwcs_products_filter']);
//		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
//		$output .= pwcs_product_list('reconditioned', array('reconditioned'=>'1'));
//		$output .= '</div>';
//	}
	else if ($action == 'filterd'){
		$filter = $_SESSION['pwcs_products_filter']? $_SESSION['pwcs_products_filter'] : NULL;
		$output .= '<div id="pwcs_product_list_filter_box" style="margin-bottom:15px;">';
		$output .= '<h3 class="pwcs_product_list_filter_title expanded">'.t('Search Products').'</h3>';
		$output .= drupal_get_form('pwcs_siteadmin_products_filter_form', $filter);
		$output .= '</div>';
		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
		$output .= pwcs_product_list($action, $filter);
		$output .= '</div>';
	}
	else {
		unset($_SESSION['pwcs_products_filter']);
		$output .= '<div id="pwcs_product_list_filter_box" class="collapsiblebox" style="margin-bottom:15px;">';
		$output .= '<h3 class="pwcs_product_list_filter_title collapsiblebox-button">'.t('Search Products').'</h3>';
		$output .= '<div class="collapsiblebox-item">'.drupal_get_form('pwcs_siteadmin_products_filter_form').'</div>';
		$output .= '</div>';
		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
		$output .= pwcs_product_list($action);
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}

/**
 * Site Admin add new product
 * Added by Rasel Kabir
 */
function pwcs_add_product() {

	$node = '';
	module_load_include('inc', 'node', 'node.pages');
	$node_type = 'product';
	$form_id = $node_type . '_node_form';
	global $user;
	$node->uid = $user->uid;
	$node->name = (isset($user->name) ? $user->name : '');
	$node->type = $node_type;
	node_object_prepare($node);

	return drupal_get_form($form_id, $node);
	//drupal_goto('node/add/product');
}

/**
 * Site Admin add new product
 * Added by Rasel Kabir
 */
function pwcs_edit_product($nid = NULL) {

	$node = node_load($nid);
	return drupal_get_form('product_node_form', $node);
	//drupal_goto('node/$nid/edit');
}

/**
 * Implementation of siteadmin_products_filter_form()
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_products_filter_form($form_info = array(), $filter = NULL) {

	$form['#prefix'] = '<div id="pwcs_product_list_filter" class="list-filter-form">';
	$form['#suffix'] = '</div>';
	
	//product id
	$form['pwc_product_row_id'] = array(
		'#title' => t('Product ID'),
		'#type' => 'textfield',
		'#default_value' => $filter['pwc_product_row_id'],
	);

	//SKU
	$form['product_sku'] = array(
		'#title' => t('Product SKU'),
		'#type' => 'textfield',
		'#default_value' => $filter['product_sku'],
	);

	
	//product name	
	$form['title'] = array(
		'#title' => t('Product Name'),
		'#type' => 'textfield',
		'#default_value' => $filter['title'],
	);

	//display per page
	$dpp = pwcs_option_values('product_filter_display_per_page');
	$form['display_per_page'] = array(
		'#title' => t('Display per page'),
		'#type' => 'select',
		'#options' => $dpp,
		'#default_value' => $filter['display_per_page'],
	);

	//Category
	$categories = array(''=>t('Any Category'))+pwcs_cat_array_selection_combo();
	$form['pwc_category_id'] = array(
		'#title' => t('Category'),
		'#type' => 'select',
		'#options' => $categories,
		'#default_value' => $filter['pwc_category_id'],
	);
	
	// Featured
	$form['featured'] = array(
		'#title' => t('Featured Products'),
		'#type' => 'checkboxes',
		'#options' => array('1' => t('Show Only Featured Products')),
		'#default_value' => array($filter['featured']),
	);


	$form['buttons']['#prefix'] = '<div class="button-container">';
	$form['buttons']['#suffix'] = '</div>';
	$form['buttons']['filter'] = array(
		'#type' => 'submit',
		'#default_value' => t('Search')
	);
/*
	$form['buttons']['reset'] = array(
		'#type' => 'submit',
		'#weight' => $weight++,
		'#default_value' => t('Show All')
	);
*/

	return $form;
}

/**
 * Category form submit
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_products_filter_form_submit($form_id, $form_values)
{
	global $user;
	if($form_values[submitted] == 1) {
		if($form_values['clicked_button']['#parents'][0] == 'filter') {
			//print_r ($form_values['values']);exit;
			$_SESSION['pwcs_products_filter'] = $form_values['values'];
			$_SESSION['pwcs_products_filter']['featured'] = $form_values['values']['featured'][1];
		} else {
			unset($_SESSION['pwcs_products_filter']);
		}
	}
	$_REQUEST[destination] = 'siteadmin/products/filterd';
}
/**
 * Implementation of siteadmin_products_filter_form()
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_productsdraganddrop_filter_form($form_info = array(), $filter = NULL) {

	$form['#prefix'] = '<div id="pwcs_product_list_filter" class="list-filter-form">';
	$form['#suffix'] = '</div>';
	
	//product id
	$form['pwc_product_row_id'] = array(
		'#title' => t('Product ID'),
		'#type' => 'textfield',
		'#default_value' => $filter['pwc_product_row_id'],
	);

	//SKU
	$form['product_sku'] = array(
		'#title' => t('Product SKU'),
		'#type' => 'textfield',
		'#default_value' => $filter['product_sku'],
	);

	
	//product name	
	$form['title'] = array(
		'#title' => t('Product Name'),
		'#type' => 'textfield',
		'#default_value' => $filter['title'],
	);

	//display per page
	$dpp = pwcs_option_values('product_filter_display_per_page');
	$form['display_per_page'] = array(
		'#title' => t('Display per page'),
		'#type' => 'select',
		'#options' => $dpp,
		'#default_value' => $filter['display_per_page'],
	);

	//Category
	$categories = array(''=>t('Any Category'))+pwcs_cat_array_selection_combo();
	$form['pwc_category_id'] = array(
		'#title' => t('Category'),
		'#type' => 'select',
		'#options' => $categories,
		'#default_value' => $filter['pwc_category_id'],
	);
	
	// Featured
	$form['featured'] = array(
		'#title' => t('Featured Products'),
		'#type' => 'checkboxes',
		'#options' => array('1' => t('Show Only Featured Products')),
		'#default_value' => array($filter['featured']),
	);


	$form['buttons']['#prefix'] = '<div class="button-container">';
	$form['buttons']['#suffix'] = '</div>';
	$form['buttons']['filter'] = array(
		'#type' => 'submit',
		'#default_value' => t('Search')
	);
/*
	$form['buttons']['reset'] = array(
		'#type' => 'submit',
		'#weight' => $weight++,
		'#default_value' => t('Show All')
	);
*/

	return $form;
}
function pwcs_siteadmin_productsdraganddrop_filter_form_submit($form_id, $form_values)
{
	global $user;
	if($form_values[submitted] == 1) {
		if($form_values['clicked_button']['#parents'][0] == 'filter') {
			//print_r ($form_values['values']);exit;
			$_SESSION['pwcs_products_filter'] = $form_values['values'];
			$_SESSION['pwcs_products_filter']['featured'] = $form_values['values']['featured'][1];
		} else {
			unset($_SESSION['pwcs_products_filter']);
		}
	}
	$_REQUEST[destination] = 'siteadmin/products/draganddropimages/filterd';
}
/**
 * List of all products (siteadmin)
 * Added by Rasel Kabir
 */
function pwcs_product_list($type=NULL, $filter = NULL)
{
	$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
	$pagelimit = isset($filter['display_per_page'])? $filter['display_per_page'] : 15;
	$pageelement = 0;
	$pageparameters = array();
	$pagequantity = 5;

	$output	= '';
	$rows = array();
	$categories = pwcs_cat_array();

	$header = array(
		//'['.'<input type="checkbox" id="products-check-all" class="form-checkbox checkbox_checkall" title="Select all rows in this table">'.t('Select All').']',
		'<input type="checkbox" id="products-check-all" class="form-checkbox checkbox_checkall" title="Select all rows in this table">',
		array('data' => '', 'width'=> '60'),
		array('data' => t('Product Name'), 'field' => 'n.title', 'width'=> '200'),
			/*array('data' => t('Product ID'), 'field' => 'pp.pwc_product_row_id'),*/
		array('data' => t('Category / Listing'), 'field' => 'pp.pwc_category_id',  'width'=> '100'),
		array('data' => t('SKU'), 'field' => 'pp.product_sku',  'width'=> '100'),
		array('data' => t('Price'), 'field' => 'pp.price',  'width'=> '80'),
		array('data' => t('Sale Price(act.)'), 'field' => 'pp.price',  'width'=> '120'),
		array('data' => t('Sale Icon*'), 'field' => 'pp.price',  'width'=> '110'),
		//array('data' => t('Review'), 'width'=> '50'),
		array('data' => t('Featured'), 'field' => 'pp.featured',  'width'=> '20'),
		//array('data' => t('Viewed'), 'field' => 'nc.totalcount', 'sort' => 'desc',  'width'=> '15'),
		array('data' => t('Action'), 'field' => 'n.changed', 'sort' => 'desc', 'width'=> '200'),
	);

	$sql  = "SELECT n.*, pp.*, pps.product_sale_price, pps.enable_sales_price, pps.sale_tag_icon, nc.totalcount FROM {node} n";
	$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
	$sql .= " LEFT JOIN {node_counter} nc ON n.nid = nc.nid";
	$sql .= " LEFT JOIN {pwcs_product_sales} pps ON pps.pwc_product_row_id = pp.pwc_product_row_id";
	$sql .= " WHERE n.type = 'product'";


	if($filter != NULL) {
		if($filter['pwc_product_row_id'])
			$sql .= " AND pp.pwc_product_row_id LIKE '%".$filter['pwc_product_row_id']."%'";
		if($filter['title'])
			$sql .= " AND n.title LIKE '%".$filter['title']."%'";
		if(isset($filter['pwc_category_id']) && ($filter['pwc_category_id'] != ''))
			$sql .= " AND pp.pwc_category_id = '".$filter['pwc_category_id']."'";
		if($filter['featured'])
			$sql .= " AND pp.featured = '".$filter['featured']."'";
//		if($filter['reconditioned'])
//			$sql .= " AND pp.reconditioned = '".$filter['reconditioned']."'";

		if($filter['product_sku'])
			$sql .= " AND pp.product_sku LIKE '%".$filter['product_sku']."%'";
	}
	//$sql .= " ORDER BY n.changed desc";
	//die($sql);

	$tablesort = tablesort_sql($header);
	$results = pager_query($sql . $tablesort, $pagelimit);
	
	if($type == 'featured')
	$output .= '<h3>'.t('List of all featured products').'</h3>';
	else
	$output .= '<h3>'.t('Products List').'</h3>';

	$product_amount = pwcs_result_count($sql);
	$output .= '<div class="pwcs_prodcut_list_count" style="margin:5px 0; font-style: italic;">'.t('Total').' '.$product_amount.' '.t('product(s) found').'</div>';

	$output .= '<div class="pwcs_table">';
	while($result = db_fetch_object($results))
	{
		$row = array();
		$node = node_load($result->nid);

		// Delete check box
		$row[] = '<input type="checkbox" id="product-'.$result->nid.'" class="form-checkbox" name="productnid[]" value="'.$result->nid.'">';

		// Product Primary Image
		$image_path = pwcs_get_product_primary_image_path($node);
		$image_output = theme('image', $image_path, '', NULL, array('width'=>'60px'), FALSE);
		$row[] = $image_output;



		// Reconditioned Product Checkbox in Product Title
		/*
		if($node->reconditioned == 1){
			$plfs_html = '<div class="pwcs-checkbox-icon pwcs-checkbox-checked"></div>';
			$plfs_title = t('Click to mark as Not-Reconditioned');
			$plfs_totitle = t('Click to mark as Reconditioned');
			$plfs_action = 'removerecondition';
			$plfs_toaction = 'makeitrecondition';
		}else{
			$plfs_html = '<div class="pwcs-checkbox-icon pwcs-checkbox-notchecked"></div>';
			$plfs_title = t('Click to mark as Reconditioned');
			$plfs_totitle = t('Click to mark as Not-Reconditioned');
			$plfs_action = 'makeitrecondition';
			$plfs_toaction = 'removerecondition';
		}
		$row_featured = l($plfs_html ,'siteadmin/productsaction/'.$plfs_action.'/'.$result->nid, 
							array ( 'attributes' => array ('nid'=> $result->nid, 'class' => 'toggle-featured', 'title' => $plfs_title, 'totitle'=> $plfs_totitle, 'action' => $plfs_action, 'toaction' => $plfs_toaction, ), 
									'html' => true,	) );
		$recon = '<div class="pwcs_plfs2">Reconditioned:'.$row_featured.'</div>';
		*/
		
		//Delete the below line when you will use the Reconditioned Product
		$recon = '';
		
		// Product Title
		if ($node->status==1)
		$row[] = array('data' => trim($result->title).'<br/>['.$node->pwc_product_id.']<br/>'.$recon.'', 'width' => '100');
		else
		$row[] = array('data' => '<i>'.trim($result->title).'</i>'.'<br/>['.$node->pwc_product_id.']<br/><div class="product_hidden">Not Visible On Site</div><br/>'.$recon.'', 'width' => '100');



		// Product ID
		//$row[] = $node->pwc_product_id;

		// Product Category
		$row[] = array('data' => $categories[$result->pwc_category_id]?$categories[$result->pwc_category_id]:'<em>'.t('No Category Found').'</em>', 'width' => '100');
		

		// Product SKU
		$row[] = array('data' => trim($result->product_sku), 'width' => '70');

		// Product Price
		//$row[] = PWCS_BASIC_CURRENCY_SYMBOL.$result->price;
		$row[] = PWCS_BASIC_CURRENCY_SYMBOL.'<input type="textbox" id="product-'.$result->nid.'" class="form-checkbox" name="productprice['.$result->nid.']" value="'.$result->price.'" style="width:45px;">';
	
	
		
		// Product Sales Price
		//$row[] = PWCS_BASIC_CURRENCY_SYMBOL.$result->price;
		$row[] = PWCS_BASIC_CURRENCY_SYMBOL.'<input type="textbox" id="productsalesprice-'.$result->nid.'" class="form-checkbox" name="productsalesprice['.$result->nid.']" value="'.$result->product_sale_price.'" style="width:45px;">'.'&nbsp;<input type="checkbox" id="activatesales" name="activatesales['.$node->nid.']" value="1" '.(($result->enable_sales_price==1) ? ' checked="checked" ':'').' />';
		//$row[] = '<input type="checkbox" id="activatesales" name="activatesales['.$node->nid.']" value="1" '.(($result->enable_sales_price==1) ? ' checked="checked" ':'').' />';
		
		$saletag_icon_1 = theme('image', variable_get('pwcs_product_saleprice_icon_1', PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag1.png'), '', '', array('width'=>'auto'), FALSE);
		$saletag_icon_2 = theme('image', variable_get('pwcs_product_saleprice_icon_2', PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag2.png'), '', '', array('width'=>'auto'), FALSE);
		$saletag_icon_3 = theme('image', variable_get('pwcs_product_saleprice_icon_closeout', PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag3.png'), '', '', array('width'=>'auto'), FALSE);
		
		// Product Sales Icon
		//$row[] = 'ooo';	
		$row[] = '<p style="margin:0;"><input type="radio" id="" name="nodetagid['.$node->nid.']" value="1" ' . (($result->sale_tag_icon==1) ? ' checked="checked" ':'') . '><label for="sale_tag_radio_1">Sale</label></p>'.
					 '<p style="margin:0;"><input type="radio" id="" name="nodetagid['.$node->nid.']" value="2" ' . (($result->sale_tag_icon==2) ? ' checked="checked" ':'') . '><label for="sale_tag_radio_2">Closeout</label></p>'.
					 '<p style="margin:0;"><input type="radio" id="" name="nodetagid['.$node->nid.']" value="3" ' . (($result->sale_tag_icon==3) ? ' checked="checked" ':'') . '><label for="sale_tag_radio_3">Clearance</label></p>';		

		/*
		$row[] = '<p style="margin:0;"><input type="radio" id="" name="nodetagid['.$node->nid.']" value="1" ' . (($result->sale_tag_icon==1) ? ' checked="checked" ':'') . '><label for="sale_tag_radio_1">'.$saletag_icon_1.'</label></p>'.
					 '<p style="margin:0;"><input type="radio" id="" name="nodetagid['.$node->nid.']" value="2" ' . (($result->sale_tag_icon==2) ? ' checked="checked" ':'') . '><label for="sale_tag_radio_2">'.$saletag_icon_2.'</label></p>'.
					 '<p style="margin:0;"><input type="radio" id="" name="nodetagid['.$node->nid.']" value="3" ' . (($result->sale_tag_icon==3) ? ' checked="checked" ':'') . '><label for="sale_tag_radio_3">'.$saletag_icon_3.'</label></p>';
		*/			 		

/*

		if($result->sale_tag_icon==0)
		{
			$row[] = '<p style="margin:0;"><input type="radio" id="sale_tag_radio_1" name="nodetagid['.$node->nid.']" value="1" checked="checked"><label for="sale_tag_radio_1">'.$saletag_icon_1.'</label></p>'.
					 '<p style="margin:0;"><input type="radio" id="sale_tag_radio_2" name="nodetagid['.$node->nid.']" value="2"><label for="sale_tag_radio_2">'.$saletag_icon_2.'</label></p>'.
					 '<p style="margin:0;"><input type="radio" id="sale_tag_radio_3" name="nodetagid['.$node->nid.']" value="3"><label for="sale_tag_radio_3">'.$saletag_icon_3.'</label></p>';
		}else{
			$row[] = '<p style="margin:0;"><input type="radio" id="" name="nodetagid['.$node->nid.']" value="1" ' . (($result->sale_tag_icon==1) ? ' checked="checked" ':'') . '><label for="sale_tag_radio_1">'.$saletag_icon_1.'</label></p>'.
					 '<p style="margin:0;"><input type="radio" id="" name="nodetagid['.$node->nid.']" value="2" ' . (($result->sale_tag_icon==2) ? ' checked="checked" ':'') . '><label for="sale_tag_radio_2">'.$saletag_icon_2.'</label></p>'.
					 '<p style="margin:0;"><input type="radio" id="" name="nodetagid['.$node->nid.']" value="3" ' . (($result->sale_tag_icon==3) ? ' checked="checked" ':'') . '><label for="sale_tag_radio_3">'.$saletag_icon_3.'</label></p>';
		}

 */			

		// Review
		$review_count = pwcs_product_review_count($result->nid);
		
		$review_text = '';
		if(!empty($review_count)) {
			$total = $review_count[COMMENT_PUBLISHED] + $review_count[COMMENT_NOT_PUBLISHED];
			$review_text .= '<div>';
			$review_text .= l(t('Total: !total', array( '!total' => '<b>'.$total.'</b>')), 'siteadmin/productreview/'.$result->nid, array ( 'attributes' => array (), 'html' => true,) );
			$review_text .= '</div>';
			if($review_count[COMMENT_NOT_PUBLISHED]){
				$review_text .= '<div style="color"red;">';
				$review_text .= l(t('Wating for Approval: !waiting', array( '!waiting' => '<b>'.$review_count[COMMENT_NOT_PUBLISHED].'</b>')), 'siteadmin/productreview/'.$result->nid.'/'.COMMENT_NOT_PUBLISHED, array ( 'attributes' => array (), 'html' => true,) );
				$review_text .= '</div>';
			}
		} else {
			$review_text = '<em>No Review</em>';
		}
		
		//$row[] = array('data' => $review_text);

		// Featured Product
		if($node->featured == 1){
			$plfs_html = '<div class="pwcs-checkbox-icon pwcs-checkbox-checked"></div>';
			$plfs_title = t('Click to mark as Not-Featured');
			$plfs_totitle = t('Click to mark as Featured');
			$plfs_action = 'removefeatured';
			$plfs_toaction = 'makeitfeatured';
		}else{
			$plfs_html = '<div class="pwcs-checkbox-icon pwcs-checkbox-notchecked"></div>';
			$plfs_title = t('Click to mark as Featured');
			$plfs_totitle = t('Click to mark as Not-Featured');
			$plfs_action = 'makeitfeatured';
			$plfs_toaction = 'removefeatured';
		}
		$row_featured = l($plfs_html ,'siteadmin/productsaction/'.$plfs_action.'/'.$result->nid, 
							array ( 'attributes' => array ('nid'=> $result->nid, 'class' => 'toggle-featured', 'title' => $plfs_title, 'totitle'=> $plfs_totitle, 'action' => $plfs_action, 'toaction' => $plfs_toaction, ), 
									'html' => true,	) );
		$row[] = '<div class="pwcs_plfs">'.$row_featured.'</div>';

		// Count
		//$count = statistics_get($node->nid);
		//$row[] = $result->totalcount?$result->totalcount:'0';
		
		
		
		// Last Edited
		//$row[] =  $result->changed ? t('@time ago', array('@time' => format_interval(time() - $result->changed))) : t('never');

		// Action
		$lastedited = $result->changed ? t('@time', array('@time' => format_interval(time() - $result->changed))) : t('never');
		$lastedited = str_replace('hours','h',$lastedited);
		$iframeurl = l(t('<img src="'.base_path().path_to_theme().'/images/pwc_logo.png" width="16" height="16" />'), PWCS_PWC_AUTO_LOGIN_URL . PWCS_PWC_CORE_URL . 'products/products/productEdit/'.$result->pwc_product_row_id.'.html' , array('html'=>true, 'attributes' => array('html'=>true,'iframewidth'=> '960','iframeheight'=> '600', 'class'=>'pwcs-popup-iframe', 'title'=>'PREMIUMWEBCART Edit Product')));
		//$row[] = l(t('<img src="'.base_path().path_to_theme().'/images/icon_edit.png" />'),'node/'.$node->nid.'/edit',array ( 'html'=>true, 'attributes' => array (),)).' '.l(t('&nbsp;&nbsp;<img src="'.base_path().path_to_theme().'/images/icon_copy.png" />'),'node/'.$node->nid.'/edit',array ( 'html'=>true, 'attributes' => array (),)).'  '.l(t('&nbsp;&nbsp;<img src="'.base_path().path_to_theme().'/images/icon_delete.png" />&nbsp;&nbsp;'),'node/'.$result->nid.'/delete',array ( 'html'=>true, 'attributes' => array (),)).l(t('Test'),'siteadmin/preview/'.$node->path, array( 'html'=>true, 'attributes' => array('target' => '_blank','class'=>'product-test-link'))).'&nbsp;&nbsp;'.''.$iframeurl.'<BR/>'.'<BR/>'.t('Last edit').': '.$lastedited;
		$row[] = l(t('<img src="'.base_path().path_to_theme().'/images/icon_edit.png" />'),'node/'.$node->nid.'/edit',array ( 'html'=>true, 'attributes' => array (),)).'&nbsp;&nbsp;'.l(t('<img src="'.base_path().path_to_theme().'/images/icon_delete.png" />&nbsp;&nbsp;'),'node/'.$result->nid.'/delete',array ( 'html'=>true, 'attributes' => array (),))
				.l(t('Test'),'siteadmin/preview/'.$node->path, array( 'html'=>true, 'attributes' => array('target' => '_blank','class'=>'product-test-link'))).'&nbsp;&nbsp;'.''.$iframeurl;

		$rows[] = $row;
	}
	$row = array();
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$rows[] = $row;
	
	$row = array();
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = array('data' => t('* '.$saletag_icon_1.' '.$saletag_icon_2.' '.$saletag_icon_3), 'colspan' => 5);

	$rows[] = $row;
	/*
    $output .= '<div class="messages warning">You will be able to set Images for Each Product. Switch to in Edit Product Mode for a product, upload images for the Product (See Tab: Upload Images on Product Edit Area) and  Set Product Default Image (See Tab: Primary Image Set on Product Edit Area).</div>';
	*/
	$output .= '<form id="siteadmin-productlist-form" method="post" accept-charset="UTF-8" action="'.base_path().'siteadmin/productsaction/multiple_product_action">';
	if (count($rows)>10)
	$output .= '<input type="submit" class="form-submit" value="'.t('Delete All').'" id="delete-all" name="delete"> <input type="submit" class="form-submit" value="'.t('Visible On Site').'" id="appearable-all" name="appearable"> <input type="submit" class="form-submit" value="'.t('Not Visible On Site').'" id="nonappearable-all" name="nonappearable">';
	
	//$output .= '<input type="submit" class="form-submit" value="'.t('Delete All').'" id="delete-all" name="delete">';
	//if($type == NULL)
	//$output .= '<input type="submit" class="form-submit" value="'.t('Save Selected Item As Featured Products').'" id="savefeatured" name="savefeatured">';
	$output .= theme('table', $header, $rows, array('width'=>'100%'));
	$output .= '<input type="submit" class="form-submit form-save-submit" value="'.t('Save').'" id="delete-all" name="saveproduct">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="form-submit" value="'.t('Delete All').'" id="delete-all" name="delete"> <input type="submit" class="form-submit" value="'.t('Visible On Site').'" id="appearable-all" name="appearable"> <input type="submit" class="form-submit" value="'.t('Not Visible On Site').'" id="nonappearable-all" name="nonappearable">';
//	if($type == NULL)
//	$output .= '<input type="submit" class="form-submit" value="'.t('Save Selected As Featured Products').'" id="savefeatured" name="savefeatured">';
//	else if($type == 'featured')
//	$output .= '<input type="submit" class="form-submit" value="'.t('Remove Selected From Featured Products').'" id="removefeatured" name="removefeatured">';
	$output .= '</form>';

	$output .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);
	$output .= '</div>';

	return $output;
}

/**
 * Siteadmin product actions
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_productsaction($action = NULL, $nid = 0, $cid = 0){
global $user;

//	if($action == 'makeitrecondition') {
//		if(is_numeric($nid) && $nid>0) {
//			db_query("UPDATE {pwcs_product} pp SET pp.reconditioned = '1' WHERE pp.nid = %d", $nid);
//			drupal_set_message(t('Item made as reconditioned'));
//		}
//	}else if($action == 'removerecondition') {
//		if(is_numeric($nid) && $nid>0) {
//			db_query("UPDATE {pwcs_product} pp SET pp.reconditioned = '0' WHERE pp.nid = %d", $nid);
//			drupal_set_message(t('Item made as not reconditioned'));
//		}
//	}else 
	if($action == 'approve-review') {
		db_query("UPDATE {comments} SET status = %d WHERE cid = %d", COMMENT_PUBLISHED, $cid);
		drupal_set_message(t('Selected Review Has Been Approved.'));
		drupal_goto('siteadmin/productreview/0/'.$nid.'/1');
	}
	if($action == 'unapprove-review') {
		db_query("UPDATE {comments} SET status = %d WHERE cid = %d", COMMENT_NOT_PUBLISHED, $cid);
		drupal_set_message(t('Selected Review Has Been Marked as Unapproved. ').l(t('Click here'),'siteadmin/productreview/0/'.$nid.'/1').' to see Unapproved List.');
		drupal_goto('siteadmin/productreview/0/'.$nid.'/0');
	}
	if($action == 'delete-approved-review') {
		db_query("DELETE FROM {comments} WHERE cid = %d", $cid);
		drupal_set_message(t('Selected Review(s) Has Been Deleted.'));
		drupal_goto('siteadmin/productreview/0/'.$nid.'/0');
	}	
	if($action == 'delete-unapproved-review') {
		db_query("DELETE FROM {comments} WHERE cid = %d", $cid);
		drupal_set_message(t('Selected Review(s) Has Been Deleted.'));
		drupal_goto('siteadmin/productreview/0/'.$nid.'/1');
	}		
	if($action == 'reviews') {
		//print_r($_POST['productcid']);
		foreach($_POST['productcid'] as $cid){
			//echo '<br/>CID:'.$cid;
		}
		//die();
		if(empty($_POST['productcid'])) {
			drupal_set_message(t('No item has been selected!'),'error');
			drupal_goto('siteadmin/productreview');
		}else{
			//print_r($_POST['productnid']);
		}
		if($_POST['saveproduct']=='Delete'){
			foreach($_POST['productcid'] as $cid)
			db_query("DELETE FROM {comments} WHERE cid = %d", $cid);
			drupal_set_message(t('Selected Review(s) Has Been Deleted.'));
			drupal_goto('siteadmin/productreview');			
		}else if($_POST['saveproduct']=='Approve Selected'){

			foreach($_POST['productcid'] as $cid)
			db_query("UPDATE {comments} SET status = %d WHERE cid = %d", COMMENT_PUBLISHED, $cid);
			drupal_set_message(t('Review(s) Has Been Marked as Approved.'));
			drupal_goto('siteadmin/productreview');			
		}else if($_POST['saveproduct']=='Unapprove Selected'){
			foreach($_POST['productcid'] as $cid)
			db_query("UPDATE {comments} SET status = %d WHERE cid = %d", COMMENT_NOT_PUBLISHED, $cid);
			drupal_set_message(t('Review(s) Has Been Marked as Unapproved.'));
			drupal_goto('siteadmin/productreview');
		}		
		else{
		}
		drupal_goto('siteadmin/productreview');
	}else if($action == 'makeitfeatured') {
		if(is_numeric($nid) && $nid>0) {
			db_query("UPDATE {pwcs_product} pp SET pp.featured = '1' WHERE pp.nid = %d", $nid);
			drupal_set_message(t('Item made as featured'));
		}
	}else if ($action == 'removefeatured'){
		if(is_numeric($nid) && $nid>0) {
			db_query("UPDATE {pwcs_product} pp SET pp.featured = '0' WHERE pp.nid = %d", $nid);
			drupal_set_message(t('Item made as not featured'));
		}
	}else if ($action == 'multiple_product_action'){
		
		if(empty($_POST['productnid'])) {
			drupal_set_message(t('No item has been selected!'),'error');
		}
		else {
			if(isset($_POST['saveproduct'])) {
				
				$record_affected = 0;
				foreach($_POST['productnid'] as $nid){
					$productprice = isset($_POST['productprice'][$nid]) ? $_POST['productprice'][$nid] : 0;
					$activatesalesprice = isset($_POST['activatesales'][$nid]) ? $_POST['activatesales'][$nid] : 0;
					$productsalesprice = isset($_POST['productsalesprice'][$nid]) ? trim($_POST['productsalesprice'][$nid]) : "";
					$tag_type = isset($_POST['nodetagid'][$nid]) ? trim($_POST['nodetagid'][$nid]) : "";
					
					$node = node_load($nid);
		
					$node->price  = $productprice; 
					$node->returnhere  = 1; 
					$node->salesprice  = $productsalesprice; 
					$node->salestag  = $tag_type; 
					$node->issalespriceactive  = $activatesalesprice; 
					node_save($node);
					$record_affected++;
				}
				drupal_set_message(t($record_affected.' Product(s) Price has been Updated!'));
				drupal_goto('siteadmin/products');				
				
			}	//End of if(isset($_POST['saveproduct'])) {		
			
			if(isset($_POST['appearable'])) {
				foreach($_POST['productnid'] as $nid){
					db_query("UPDATE {node} n SET n.status = '1' WHERE n.nid = %d", $nid);
				}
				drupal_set_message(t('Selected products are now visible on your website.'));
			}			
			if(isset($_POST['nonappearable'])) {
				foreach($_POST['productnid'] as $nid){
					db_query("UPDATE {node} n SET n.status = '0' WHERE n.nid = %d", $nid);
				}
				drupal_set_message(t('Selected products are now invisible on your website.'));
			}			
			if(isset($_POST['savefeatured'])) {
				db_query("UPDATE {pwcs_product} pp SET pp.featured = '0'");
				foreach($_POST['productnid'] as $nid){
					db_query("UPDATE {pwcs_product} pp SET pp.featured = '1' WHERE pp.nid = %d", $nid);
				}
				drupal_set_message(t('Selected products are now featured products!'));
			}

			if(isset($_POST['removefeatured'])) {
				foreach($_POST['productnid'] as $nid){
					db_query("UPDATE {pwcs_product} pp SET pp.featured = '0' WHERE pp.nid = %d", $nid);
				}
				drupal_set_message(t('Selected products are removed from featured products!'));
				drupal_goto('siteadmin/products/featured');
			}
			
			if(isset($_POST['delete'])) {
				foreach($_POST['productnid'] as $nid){
					node_delete($nid);
				}
				drupal_set_message(t('Selected products are deleted!'));
			}
		}
	}
	else if ($action == 'pricemanager_product_action'){//This block is added for 1page price manager
		$record_affected = 0;

		if(isset($_POST['delete'])) {
			
			foreach($_POST['productnid'] as $nid){
				$node = node_load($nid);
				$node->field_product_image = array();
				$node->returnhere  = 1; 
				$record_affected++;
				node_save($node);				
			}
		}
		//print_r($_POST);
//		/die();
		if(isset($_POST['save'])){ 
		foreach($_POST['productnid'] as $nid){
			$productimagelinkid = isset($_POST['productimageid'][$nid]) ? $_POST['productimageid'][$nid] : '';
			$productimagelinkid2 = isset($_POST['productimageid2'][$nid]) ? $_POST['productimageid2'][$nid] : '';
			$productimagelinkid3 = isset($_POST['productimageid3'][$nid]) ? $_POST['productimageid3'][$nid] : '';
			$productimagelinkid4 = isset($_POST['productimageid4'][$nid]) ? $_POST['productimageid4'][$nid] : '';
			$productimagelinkid5 = isset($_POST['productimageid5'][$nid]) ? $_POST['productimageid5'][$nid] : '';
			$productimagelinkid6 = isset($_POST['productimageid6'][$nid]) ? $_POST['productimageid6'][$nid] : '';
			$productimagelinkid7 = isset($_POST['productimageid7'][$nid]) ? $_POST['productimageid7'][$nid] : '';
			
			//echo $nid.' >> File1: '.$productimagelinkid;
			//echo $nid.' >> File2:'.$productimagelinkid2;
			//die();
			
			$node = node_load($nid);
			
			$image_array_data = array();
			//$image_array_data[0] = array();
			//$image_array_data[1] = array();
			//$image_array_data[2] = array();
			//$image_array_data[3] = array();
			//$image_array_data[4] = array();
			//$image_array_data[5] = array();
			//$image_array_data[6] = array();
			
			if(!empty($productimagelinkid))
			{
				
				$results = db_query("SELECT * FROM {files} WHERE filepath='%s'",$productimagelinkid);
				$foundimage=0;
				while($result = db_fetch_object($results)) {
					if (file_exists($productimagelinkid))
					{
						$foundimage=1;
								
						$add_image_array = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
						$image_array_data[0] = $add_image_array;
					}
				}
						
			//echo 'File 1 : '.$productimagelinkid.' Found Image:'.$foundimage;
			//die();
						
			if (!isset($file_obj->fid))
				$node->pwc_image_select = $file_obj->fid;
			else
				$node->pwc_image_select = 0;
				
			}
			if(!empty($productimagelinkid2))
			{
			
				$results = db_query("SELECT * FROM {files} WHERE filepath='%s'",$productimagelinkid2);
				$foundimage=0;
				while($result = db_fetch_object($results)) {
					if (file_exists($productimagelinkid2))
					{
						$foundimage=1;
							
						$add_image_array = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
						$image_array_data[1] = $add_image_array;
					}
				}
			}
			if(!empty($productimagelinkid3))
			{
			
				$results = db_query("SELECT * FROM {files} WHERE filepath='%s'",$productimagelinkid3);
				$foundimage=0;
				while($result = db_fetch_object($results)) {
					if (file_exists($productimagelinkid3))
					{
						$foundimage=1;
							
						$add_image_array = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
						$image_array_data[2] = $add_image_array;
					}
				}
			}
			if(!empty($productimagelinkid4))
			{
			
				$results = db_query("SELECT * FROM {files} WHERE filepath='%s'",$productimagelinkid4);
				$foundimage=0;
				while($result = db_fetch_object($results)) {
					if (file_exists($productimagelinkid4))
					{
						$foundimage=1;
							
						$add_image_array = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
						$image_array_data[3] = $add_image_array;
					}
				}
			}
			if(!empty($productimagelinkid5))
			{
			
				$results = db_query("SELECT * FROM {files} WHERE filepath='%s'",$productimagelinkid5);
				$foundimage=0;
				while($result = db_fetch_object($results)) {
					if (file_exists($productimagelinkid5))
					{
						$foundimage=1;
							
						$add_image_array = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
						$image_array_data[4] = $add_image_array;
					}
				}
			}
			if(!empty($productimagelinkid6))
			{
			
				$results = db_query("SELECT * FROM {files} WHERE filepath='%s'",$productimagelinkid6);
				$foundimage=0;
				while($result = db_fetch_object($results)) {
					if (file_exists($productimagelinkid6))
					{
						$foundimage=1;
							
						$add_image_array = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
						$image_array_data[5] = $add_image_array;
					}
				}
			}
			if(!empty($productimagelinkid7))
			{
			
				$results = db_query("SELECT * FROM {files} WHERE filepath='%s'",$productimagelinkid7);
				$foundimage=0;
				while($result = db_fetch_object($results)) {
					if (file_exists($productimagelinkid7))
					{
						$foundimage=1;
							
						$add_image_array = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
						$image_array_data[6] = $add_image_array;
					}
				}
			}		
			//$node->price  = $productprice; 
			$node->field_product_image = $image_array_data;
			$node->returnhere  = 1; 
			
			$record_affected++;
			//print_r($image_array_data);
			//die();
			//$node->status = 1;
			node_save($node);
			
			
	}
	}
		//die();
		drupal_set_message(t($record_affected.' Product(s) has been Updated!'));
		drupal_goto('siteadmin/products/1pagepricemanager');
	}
	drupal_goto('siteadmin/products');
}

/**
 * Featured product block
 * Added by Rasel Kabir
 */
function pwcs_fatured_products() {

	$amount = variable_set('pwcs_fatured_products_block_amount', 0);
	$theme = variable_get('pwcs_fatured_products_block_theme', 'spv_small');

	$output	= '';
	$output .= '<div id="pwcs_feature_products_block">';

	$sql  = "SELECT n.nid FROM {node} n";
	$sql .= " Inner Join {pwcs_product} pp ON n.nid = pp.nid";
	$sql .= " WHERE n.type = 'product' AND n.status = 1";
	$sql .= " AND pp.featured = 1";
	$sql .= " ORDER BY n.changed desc";

	if($amount != 0)
		$sql .= " Limit 0, ".$amount;

	$results = db_query($sql);
	while($result = db_fetch_object($results))
	{
		$product = node_load($result->nid);
		$output .= pwcs_single_product_view($theme, $product);
	}
	$output .= '<div style="clear:both;"></div>';
	$output .= '</div>';
	return $output;
}

/**
 * get related item in cart list
 * @return related product list
 * Added by Rasel Kabir
 */
function pwcs_relatedproducts_list($product_nid = NULL){
		
		$theme = 'spv_small';
		if($product_nid == NULL) {
			return NULL;
		}
		$sql  = "SELECT related_products FROM {pwcs_product} WHERE nid = %d";
		$results = db_result(db_query($sql, $product_nid));

		$output	= '';

		if($results != NULL) {
		$output .= '<div id="pwcs_related_product_block" style="margin-top:15px;">';
		$output .= '<h4>'.t('Items That you can buy also:').'</h4>';
		$output .= '<hr/>';

			if($results == NULL) {
				$output .= '<em>'.t('Sorry! This product has no related product').'</em>';
			} else {
				$related_products = explode(",", $results);
				$relatedproductamount = variable_get('pwcs_sitesettings_cart_relatedproductamount', '5');
				$related_products = array_slice($related_products, 0, $relatedproductamount);
				foreach($related_products as $key => $related_product_nid) {
					$product = node_load($related_product_nid);
					$output .= pwcs_single_product_view($theme, $product);
				}
				$output .= '<div style="clear:both;"></div>';
			}
		$output .= '</div>';
		}

		return $output;
}

/**
 * Public product block
 * Added by Rasel Kabir
 */
function pwcs_public_products_block(){

	$output	= '';
	$options = array(
				'pwc_category_id' => NULL,
				'manufacturer_id' => NULL,
				'view_type' => 'block',
				'onlyfeatured' => variable_get('pwcs_homepage_products_block_featured', 1),
				'theme' => variable_get('pwcs_homepage_products_block_theme', 'spv_normal'),
				'pagelimit' => variable_get('pwcs_homepage_products_block_amount', 10),
				'pagequantity' > 10,
				);
	$output .= pwcs_public_products_list($options);
	return $output;
}

/**
 * Public product url
 * Added by Rasel Kabir
 */
function pwcs_public_products_url($pwc_category_id=NULL, $manufacturer_id=NULL, $listing_id=NULL){

	/*
	// We will have to delete this code after 15 days Today: 04/03/2011 - ZERIN
	*/
	if($listing_id != NULL) {
		$sql  = "SELECT * FROM {pwcs_product_order} ppo WHERE ppo.listing_id = ".$listing_id." AND ppo.pwc_product_row_id > 0";
		$results2 = db_query($sql);
		if ( !db_result($results2) ){
			if((db_affected_rows($results2)<=0)){
				pwcs_make_order_table();
				pwcs_bulk_clean_listing_table();
			}
		}
	}else{
		$sql  = "SELECT * FROM {pwcs_product_order} ppo WHERE ppo.listing_id = 0 AND ppo.pwc_product_row_id > 0";
		$results2 = db_query($sql);
		if ( !db_result($results2) ){
			if((db_affected_rows($results2)<=0)){
				pwcs_make_order_table();
				pwcs_bulk_clean_listing_table();
			}
		}
	}
	/*
		// END - ZERIN
	*/	

	$pwc_category_id=($pwc_category_id=='all')?NULL:$pwc_category_id;
	$manufacturer_id=($manufacturer_id=='all')?NULL:$manufacturer_id;
	$listing_id=($listing_id=='all')?NULL:$listing_id;

	drupal_set_title(t('Products'));

	if($pwc_category_id != NULL) {
		$category_title = db_result(db_query('SELECT category_title FROM {pwcs_category} pc WHERE pc.pwc_category_id = '.$pwc_category_id));
		$breadcrumb = array();
		$breadcrumb[] = l(t('Home'), '<front>');
		$breadcrumb[] = $category_title;
		drupal_set_breadcrumb($breadcrumb);
		drupal_set_title($category_title);
	}
	if($manufacturer_id != NULL) {
		$mfg_name = db_result(db_query('SELECT mfg_name FROM {pwcs_manufacturer} WHERE pwc_mfg_id='.$manufacturer_id));
		$breadcrumb = array();
		$breadcrumb[] = l(t('Home'), '<front>');
		$breadcrumb[] = l(t('Manufacturers'), 'manufacturers');
		$breadcrumb[] = $mfg_name;
		drupal_set_breadcrumb($breadcrumb);
		drupal_set_title($mfg_name);
	}
	if($listing_id != NULL) {
		$category_title = db_result(db_query('SELECT category_title FROM {pwcs_listings} pc WHERE pc.pwc_category_id = '.$listing_id));
		$breadcrumb = array();
		$breadcrumb[] = l(t('Home'), '<front>');
		$breadcrumb[] = $category_title;
		drupal_set_breadcrumb($breadcrumb);
		drupal_set_title($category_title);
	}

	$output	= '';
	$options = array(
				'pwc_category_id' => $pwc_category_id,
				'manufacturer_id' => $manufacturer_id,
				'listing_id' => $listing_id,
				'view_type' => 'page',
				'onlyfeatured' => 0,
				'theme' => variable_get('pwcs_sitesettings_productview_theme', 'spv_normal'),
				'pagelimit' => variable_get('pwcs_categorypage_products_paging_amount', 10),
				'pagequantity' > 10,
				);
	$output .= pwcs_public_products_list($options);

	if(variable_get('pwcs_listings_menu_tree_options', 0) == 1) {
		//Child List Products
		$child_listing_array = pwcs_get_child_listing_id($listing_id);
		if(!empty($child_listing_array)){
			foreach($child_listing_array as $child_listing_id){
				$listtitle = db_result(db_query('SELECT category_title FROM {pwcs_listings} WHERE pwc_category_id = %d', $child_listing_id));
				$output .= '<h1>'.$listtitle.'</h1>';
				$options ['listing_id'] = $child_listing_id;
				$output .= pwcs_public_products_list($options);
			}
		}
	}

	return $output;
}

/**
 * Public product list
 * Added by Rasel Kabir
 */
function pwcs_public_products_list($options = array()){

	$spv_theme = $options['theme'] ? $options['theme'] : 'spv_normal';

	$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
	$pagelimit = $options['pagelimit'] ? $options['pagelimit'] : 10;
	$pageelement = 0;
	$pageparameters = array();
	$pagequantity =  $options['pagequantity'] ?  $options['pagequantity'] : 5;

	$sql  = "SELECT DISTINCT(n.nid), pp.* FROM {node} n";
	$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
	//if($options['listing_id'] != NULL){
		$sql .= " LEFT JOIN {pwcs_product_order} ppo ON ppo.pwc_product_row_id = pp.pwc_product_row_id";
	//}
	$sql .= " WHERE n.type = 'product' AND n.status = 1";

	if($options['pwc_category_id'] != NULL)
		$sql .= " AND pp.pwc_category_id = ".$options['pwc_category_id'];

	if($options['manufacturer_id'] != NULL)
		$sql .= " AND pp.manufacturer_id = ".$options['manufacturer_id'];

	if($options['listing_id'] != NULL) {
			$sql .= " AND (" .
					" ppo.listing_id = '".$options['listing_id']."'" .
					" OR ppo.listing_id LIKE '%%,".$options['listing_id'].",%%'" .
					" OR ppo.listing_id LIKE '".$options['listing_id'].",%%'" .
					" OR ppo.listing_id LIKE '%%,".$options['listing_id']."'" .
					" )";
	} 
	else{
			if($options['pwc_category_id'] != NULL){
			$sql .= " AND (" .
					" ppo.listing_id = '".$options['pwc_category_id']."'" .
					" )";
			}		
			else{
			$sql .= " AND (" .
					" ppo.listing_id = '0'" .
					" )";				
			}		
		
	}	
	if ($options['pwc_category_id'] != NULL) {
			$sql .= " AND " .
					" ppo.listing_id = pp.pwc_category_id" .
					" ";
	}	


	if($options['onlyfeatured'] == 1) 
		$sql .= " AND pp.featured = 1";

	$orderby_list_weight = '';
	//if($options['listing_id'] != NULL) {
		$orderby_list_weight = "ppo.weight ASC,";
	//}
	$sql .= " ORDER BY ".$orderby_list_weight." n.title ASC";
	
    //print_r($sql); exit;
	//echo "Hrere";
	//die($sql.'<he>'.pwcs_result_count($sql) );
	
	$output	= '';
	$output .= '<div class="pwcs_product_block">';
	
	if(pwcs_result_count($sql) > 0) {
		$results = pager_query($sql, $pagelimit);
		$categories = pwcs_cat_array();
		while($result = db_fetch_object($results))
		{
			$product = node_load($result->nid);
			$product->view_category = $categories[$product->pwc_category_id]?$categories[$product->pwc_category_id]:'<em>'.t('None').'</em>';
			$output .= pwcs_single_product_view($spv_theme, $product);
		}
	} else {
		$output .= '<div class="warning"> ' . t('There are currently no products in this category!') . ' </div>';
	}
	$output .= '<div style="clear:both; padding:10px 0;"></div>';
	$output .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);
	$output .= '</div>';

	return $output;

}

/** 
 * CHILD LISTING ID FROM A LISTING ID
 * Added By Rasel Kabir
 */
function pwcs_get_child_listing_id($listing_id){

	static $clist;

    if (!is_numeric ($listing_id)) {
    	return false;
    }

	$sql  = "SELECT pl.*";
	$sql .= " FROM {pwcs_listings} pl";
	$sql .= " WHERE pl.parent_category_id = %d";
	$sql .= " ORDER BY pl.category_id";

	$results = db_query($sql, $listing_id);
	while($result = db_fetch_object($results)) {
		$clist[] = $result->pwc_category_id;
		if( !( ($result->pwc_category_id == 0) && ($result->parent_category_id == 0) ) ){
			pwcs_get_child_listing_id($result->pwc_category_id);
	    }
	}
	return $clist;
}


/**
 * Product array
 * Added by Rasel Kabir
 */
function pwcs_products_array($except_product_nid = NULL) {

	$theme = 'spv_small';

	$sql  = "SELECT n.nid FROM {node} n";
	$sql .= " Inner Join {pwcs_product} pp ON n.nid = pp.nid";
	$sql .= " WHERE n.type = 'product' AND n.status = 1";
	if ($except_product_nid != NULL)
		$sql .= " AND pp.nid != ".$except_product_nid;
	$sql .= " ORDER BY n.changed desc";

	$product_array = array();
	if(pwcs_result_count($sql) > 0) {
		$results = db_query($sql);

		while($result = db_fetch_object($results))
		{
			$product = node_load($result->nid);
			$product_array[$product->nid] = pwcs_single_product_view($theme, $product);
		}
	}

	return $product_array;
}

/**
 * Get product image list
 * @param nid
 * @return array with image array (nid => <img html tag>)
 * Added by Rasel Kabir
 */
function pwcs_get_product_images($nid = NULL) {

	$node = node_load($nid);
	if($node->field_product_image[0] != NULL) {
		foreach($node->field_product_image as $value) {
			$img_path = str_replace("sites/default/files", "sites/default/files/imagefield_thumbs", $value['filepath']);
		    $images[$value['fid']] = theme('image', $img_path, '', NULL, array(), FALSE);
		}
	}
	return $images;
}
/**
 * Get product attribute list
 * @param none
 * @return array with attribute array
 * Added by Ali Tareque Chowdhury
 */
//function pwcs_get_product_attributes() {
//
//    $result = db_query("SELECT attr.id as attr_id,
//                             attr.attr_name as attr_name,
//                             attrgrp.id as attr_group_id,
//                             attrgrp.attr_group_public_name as group_name,
//                             attrgrp.attr_group_public_name as group_name_public
//                             FROM {pwcs_attributes} attr
//                             INNER JOIN {pwcs_attr_groups} attrgrp ON attrgrp.id=attr.group_id
//                             WHERE 1 Order By attr_group_id ASC, attr_id ASC");
//
//    $attributes= array();
//    $attr_group_id=0;
//    while($row = db_fetch_array($result))
//    {
//        if($attr_group_id!=$row['attr_group_id'])
//        {
//           $attributes[$row['group_name'].'_'.$row['attr_group_id']][$row['group_name']][0]="--- SELECT IF THIS PRODUCT HAVE BELOW OPTIONS ----";
//           $attr_group_id=$row['attr_group_id'];
//        }
//        $attributes[$row['group_name'].'_'.$row['attr_group_id']][$row['group_name']][$row['attr_id']]=$row['attr_name'];
//
//    }
//   return $attributes;
//}


//
//function pwcs_siteadmin_productattribute($action=null,$nid, $set_name='')
//{
//    if($action=='delete') {
//
//        $sql="DELETE FROM {pwcs_product_attributes} WHERE set_name='".$set_name."' AND nid='".$nid."'";
//        if(db_query($sql))
//        {
//            drupal_set_message("Product Option ".$set_name." has been deleted.");
//        }
//        else
//        {
//            drupal_set_message("Cannot delete ".$set_name);
//        }
//
//    }
//    drupal_goto('node/'.$nid.'/edit');
//}

/**
 *
 * Added by ALI TAREQUE
 */
//function pwcs_insert_product_attribute_group($node)
//{
//
//    if($node->price_inc_desc==1)
//    {
//        $node->inc_desc_amount= -$node->inc_desc_amount;
//    }
//    else if($node->price_inc_desc==2)
//    {
//        $node->inc_desc_amount=0;
//    }
//
//    $product_attribute_array = pwcs_get_product_attributes();
//
//    $sql="SELECT Count(DISTINCT(set_name)) as total_row FROM {pwcs_product_attributes} WHERE nid=%d";
//    $total_rows=db_result(db_query($sql, $node->nid));
//
//    if($total_rows==''){$total_rows=0;}
//    $total_rows++;
//    $set_name="GROUP-".$total_rows;
//
//    foreach($product_attribute_array as $key=>$val)
//    {
//        if($node->$key!=0){
//            $attr_grp_id=explode("_",$key);
//
//            db_query("INSERT INTO {pwcs_product_attributes}
//                (`nid` , `attr_group_id` , `attr_id`,`set_name`, `quantity` ,`inc_desc_amount`)
//            VALUES (%d, '%d', '%d','%s', '%d','%d')",
//            $node->nid, $attr_grp_id[1], $node->$key, $set_name , $node->quantity, $node->inc_desc_amount);
//        }
//    }
//    return true;
//}

/**
 * Get product primary image path
 * @param node nid or product node object [usually returned from node_load()]
 * @return path of the parimary image
 * Added by Rasel Kabir
 */
function pwcs_get_product_primary_image_path($product = NULL){

	// if $product not object then it will consider as an node id
	if(!is_object($product) && is_numeric($product)) {
		$product = noad_load($product);
	}
	$image_path = PWCS_PRODUCT_DEFAULT_IMAGE;
	if($product->field_product_image[0] != NULL) {
		foreach ($product->field_product_image as $key=>$val) {
			if($product->pwc_image_select == $val['fid']) {
				$image_path = $val['filepath'];
			}
		}
	}
	return $image_path;
}


/**
 * Get Product Details of a pwc_product_row_id
 * @param pwc_product_row_id
 * @return product details array
 * Added by Rasel Kabir
 */
function pwcs_product_details_pwc_product_row_id($pwc_product_row_id) {

	$sql  = "SELECT nid FROM {pwcs_product} WHERE pwc_product_row_id = ".$pwc_product_row_id;
	$product_nid = db_result(db_query($sql));
	return node_load($product_nid);
}

/**
 * Product Options in Table Theme
 * by Rasel Kabir
 */
function theme_pwcs_product_attributes_list($node, $selection = FALSE) {

	$header = array(
		array('data' => t('Attribute Group : Attribute'), 'colspan'=>'2', 'style'=>'padding-left: 30px;'),
		t('Sub SKU'),
		t('Stock on hand'),
		t('Price Change'),
	);

		//pwcs_debug_file_write($node, true);
    $rows = array();
    foreach($node->product_options as $key => $product_attribute)
    {
				$row_class = '';
				$default_title = '';
				$radio_checked = '';
				if($product_attribute['attr_id'] == $node->default_attribute) {
					$row_class = "default_attribute";
					$default_title = ' <em>('.t('Default Option').')</em>';
					$radio_checked = 'checked="checked"';
				}
        $row = array();
        if($selection == TRUE)
        	$row[] = '<input type="radio" id="pwcs-productdetails-select-options-'.$product_attribute['product_attributes_id'].'" name="pwcs-productdetails-select-options" value="'.$product_attribute['attr_id'].'" '.$radio_checked.' class="form-radio">';
        else 
        	$row[] = '';
        $row[] = theme_pwcs_attributes_csv_to_textval($product_attribute['attr_id']).$default_title;
        $row[] = $product_attribute['subsku'];
        $row[] = ($product_attribute['quantity']<1)?t('Out of Stock'):$product_attribute['quantity'];
        $row[] = theme_pwcs_product_options_price_sentence($product_attribute['affected_price']);
        $rows[] = array('data' => $row, 'class'=>$row_class);
    }

    return theme('table', $header, $rows, array('width'=>'100%', 'id'=>'pwcs_productdeatils_alloptions_table'));
}

function theme_pwcs_product_options_price_sentence($affected_price){

	if($affected_price == '0') {
		return t('No change');
	} else if($affected_price[0] == '-') {
		$affected_price = ($affected_price * -1);
		return t('Less').' '.PWCS_BASIC_CURRENCY_SYMBOL.$affected_price;
	} else {
		return t('Add').' '.PWCS_BASIC_CURRENCY_SYMBOL.$affected_price;
	}
}

/**
 * Product Options in Table Theme
 * by Rasel Kabir
 */
function theme_pwcs_attributes_csv_to_textval($comma_sep_attrid = NULL, $separator = ' | ', $showtitle = true) {
		
		if($comma_sep_attrid != NULL) {
			$attr_id = explode(",", $comma_sep_attrid);
			$attr_group_attr_name = '';
			$sep = 1;
			foreach ($attr_id as $k=>$v){
				$results = db_query("SELECT	group_id, attr_name FROM {pwcs_attributes} WHERE pwc_attribute_id = ".$v);
				while($result = db_fetch_object($results)) {
					$attr_name = $result->attr_name;
					$attr_group_name = db_result(db_query("SELECT attr_group_public_name FROM {pwcs_attr_groups} WHERE id = ".$result->group_id));
					if($sep == 1) {
						if($showtitle == true)
						$attr_group_attr_name .= $attr_group_name.' : <b>'.$attr_name.'</b>';
						else
						$attr_group_attr_name .= $attr_name;
						
						$sep = 0;
					} else {
						if($showtitle == true)
						$attr_group_attr_name .= $separator.$attr_group_name.' : <b>'.$attr_name.'</b>';
						else
						$attr_group_attr_name .= $separator.$attr_name;
					}
				}	
			}
			return $attr_group_attr_name;
		} else {
			return t('No options');
		}
}
/**
 * Site Admin product page theme
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_productsmanager($action=NULL) {

	//die('action:'.$action);
	$output = '';
	$output .= '<div class="pwcs-page-content-inner">';
	if($action == 'featured') {
		unset($_SESSION['pwcs_products_filter']);
		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
		$output .= pwcs_product_list_price_manager('featured', array('featured'=>'1'));
		$output .= '</div>';
	}
	else if ($action == 'filterd'){
		$filter = $_SESSION['pwcs_products_filter']? $_SESSION['pwcs_products_filter'] : NULL;
		$output .= '<div id="pwcs_product_list_filter_box" style="margin-bottom:15px;">';
		$output .= '<h3 class="pwcs_product_list_filter_title expanded">'.t('Search Products').'</h3>';
		$output .= drupal_get_form('pwcs_siteadmin_productsdraganddrop_filter_form', $filter);
		$output .= '</div>';
		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
		$output .= pwcs_product_list_price_manager($action, $filter);
		$output .= '</div>';
	}
	else {
		unset($_SESSION['pwcs_products_filter']);
		$output .= '<div id="pwcs_product_list_filter_box" style="margin-bottom:15px;">';
		$output .= '<h3 class="pwcs_product_list_filter_title">'.t('Search Products').'</h3>';
		$output .= drupal_get_form('pwcs_siteadmin_productsdraganddrop_filter_form');
		$output .= '</div>';
		$output .= '<div id="pwcs_product_list" class="ws-content-list product-image-dragdrop">';
		$output .= pwcs_product_list_price_manager($action);
		//$output .= 'output';
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}
/**
 * List of all products (siteadmin)
 * Added by Rasel Kabir
 */
function pwcs_product_list_price_manager($type=NULL, $filter = NULL)
{
	global $user;
	$breadcrumb = array();
	$breadcrumb[] = l(t('Home'), '<front>');
	$breadcrumb[] = l(t('Product Management'), 'siteadmin/products');
	$breadcrumb[] = l(t('Drag & Drop Product Images'), 'siteadmin/products/1pagepricemanager');
	drupal_set_breadcrumb($breadcrumb);
	
	drupal_add_js(drupal_get_path('module', 'pwcs') .'/js/ui.js');
	drupal_add_js(drupal_get_path('module', 'pwcs') .'/js/ui_002.js');
	drupal_add_js(drupal_get_path('module', 'pwcs') .'/js/ui_003.js');
	//drupal_add_js(drupal_get_path('module', 'pwcs') .'/js/floating-1.7.js');
	//drupal_add_js(drupal_get_path('module', 'pwcs') .'/js/sticky-lib.js');

	$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
	$pagelimit = isset($filter['display_per_page'])? $filter['display_per_page'] : 200;
	$pageelement = 0;
	$pageparameters = array();
	$pagequantity = 5;

	$output	= '';
	$rows = array();
	$categories = pwcs_cat_array();

	$header = array(
		array('data' => '<input type="checkbox" id="products-check-all" class="form-checkbox checkbox_checkall_products"  title="Select all rows in this table">', 'width'=> '10px'),
		array('data' => t('Product Name'), 'field' => 'n.title', 'width'=> '80'),
		array('data' => 'Main Image', 'width'=> '60px'),
		array('data' => 'Image 2', 'width'=> '60px'),
		array('data' => 'Image 3', 'width'=> '60px'),
		array('data' => 'Image 4', 'width'=> '60px'),
		array('data' => 'Image 5', 'width'=> '60px'),
		array('data' => 'Image 6', 'width'=> '60px'),
		array('data' => 'Image 7', 'width'=> '60px'),
	);

	$sql  = "SELECT n.*, pp.*, pps.product_sale_price, pps.enable_sales_price, pps.sale_tag_icon, nc.totalcount FROM {node} n";
	$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
	$sql .= " LEFT JOIN {node_counter} nc ON n.nid = nc.nid";
	$sql .= " LEFT JOIN {pwcs_product_sales} pps ON pps.pwc_product_row_id = pp.pwc_product_row_id";
	$sql .= " WHERE n.type = 'product'";

	if($filter != NULL) {
		if($filter['pwc_product_row_id'])
			$sql .= " AND pp.pwc_product_row_id LIKE '%".$filter['pwc_product_row_id']."%'";
		if($filter['title'])
			$sql .= " AND n.title LIKE '%".$filter['title']."%'";
		if(isset($filter['pwc_category_id']) && ($filter['pwc_category_id'] != ''))
			$sql .= " AND pp.pwc_category_id = '".$filter['pwc_category_id']."'";
		if($filter['featured'])
			$sql .= " AND pp.featured = '".$filter['featured']."'";
		if($filter['product_sku'])
			$sql .= " AND pp.product_sku LIKE '%".$filter['product_sku']."%'";
	}
	//$sql .= " ORDER BY n.changed desc";
	//die($sql);
	$tablesort = tablesort_sql($header);
	$results = pager_query($sql . $tablesort, $pagelimit);
	
	if($type == 'featured')
	$output .= '<h3>'.t('List of all featured products').'</h3>';
	else
	$output .= '<h3>'.t('Products List').'</h3>';
	$product_amount = pwcs_result_count($sql);

	//$product_amount = pwcs_result_count($sql);
	//$output .= '<div class="pwcs_prodcut_list_count" style="margin:5px 0; font-style: italic;">'.t('Total').' '.$product_amount.' '.t('product(s) found').'</div>';

	$output_table = '';
	$output_table .= '<div class="pwcs_table">';
	$invisible_products = 0;
	while($result = db_fetch_object($results))
	{
		$row = array();
		$node = node_load($result->nid);

		// Delete check box
		$row[] = '<input type="checkbox" id="productnid" class="form-checkbox" name="productnid['.$result->nid.']"  value="'.$result->nid.'">';

		// Product Title
		if ($node->status==1)
		$row[] = array('data' => l(trim($result->title),'node/'.$node->nid.'/edit', array('attributes' => array('target'=> '_blank'))).'<br/>['.$node->pwc_product_id.']', 'width' => '80px');
		else
		$row[] = array('data' => '<i>'.l(trim($result->title),'node/'.$node->nid.'/edit', array('attributes' => array('target'=> '_blank'))).'</i>'.'<br/>['.$node->pwc_product_id.']<div class="product_hidden">Not Visible On Site</div>', 'width' => '80px');

		$image_path = array();
		// Product Primary Image
		$image_path = pwcs_get_product_primary_imageandname_path($node);
		//print_r($image_path);
		$image_output = theme('image', $image_path['file1-path'], '', NULL, array('width'=>'60px','height'=>'60px'), FALSE);
		if(PWCS_PRODUCT_DEFAULT_IMAGE==$image_path['file1-path'])
		$row[] = array('data' => '<div class="droppable ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file1-filename'].'</div></div><input type="hidden" value="'.$image_path['file1-path'].'" name="productimageid['.$result->nid.']" id="productimageid-'.$result->nid.'"/>', 'width' => '60px');
		else
		$row[] = array('data' => '<div class="product-trash-area"><a href="'.base_path().'siteadmin/draganddropimages/'.$result->nid.'/1"><img src="'.base_path().path_to_theme().'/images/icon_delete.png" /></a></div><div class="droppable ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file1-filename'].'</div></div><input type="hidden" value="'.$image_path['file1-path'].'" name="productimageid['.$result->nid.']" id="productimageid-'.$result->nid.'"/>', 'width' => '60px');

				
		// Image 2
		$image_output = theme('image', $image_path['file2-path'], '', NULL, array('width'=>'60px','height'=>'60px'), FALSE);
		if(PWCS_PRODUCT_DEFAULT_IMAGE==$image_path['file2-path'])
		$row[] = array('data' => '<div class="droppable2 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file2-filename'].'</div></div><input type="hidden" value="'.$image_path['file2-path'].'" name="productimageid2['.$result->nid.']" id="productimageid2-'.$result->nid.'"/>', 'width' => '60px');
		else
		$row[] = array('data' => '<div class="product-trash-area"><a href="'.base_path().'siteadmin/draganddropimages/'.$result->nid.'/2"><img src="'.base_path().path_to_theme().'/images/icon_delete.png" /></a></div><div class="droppable2 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file2-filename'].'</div></div><input type="hidden" value="'.$image_path['file2-path'].'" name="productimageid2['.$result->nid.']" id="productimageid2-'.$result->nid.'"/>', 'width' => '60px');

		// Image 3
		$image_output = theme('image', $image_path['file3-path'], '', NULL, array('width'=>'60px','height'=>'60px'), FALSE);
		if(PWCS_PRODUCT_DEFAULT_IMAGE==$image_path['file3-path'])
		$row[] = array('data' => '<div class="droppable3 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file3-filename'].'</div></div><input type="hidden" value="'.$image_path['file3-path'].'" name="productimageid3['.$result->nid.']" id="productimageid3-'.$result->nid.'"/>', 'width' => '60px');
		else
		$row[] = array('data' => '<div class="product-trash-area"><a href="'.base_path().'siteadmin/draganddropimages/'.$result->nid.'/3"><img src="'.base_path().path_to_theme().'/images/icon_delete.png" /></a></div><div class="droppable3 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file3-filename'].'</div></div><input type="hidden" value="'.$image_path['file3-path'].'" name="productimageid3['.$result->nid.']" id="productimageid3-'.$result->nid.'"/>', 'width' => '60px');
		
		// Image 4
		$image_output = theme('image', $image_path['file4-path'], '', NULL, array('width'=>'60px','height'=>'60px'), FALSE);
		if(PWCS_PRODUCT_DEFAULT_IMAGE==$image_path['file4-path'])
		$row[] = array('data' => '<div class="droppable4 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file4-filename'].'</div></div><input type="hidden" value="'.$image_path['file4-path'].'" name="productimageid4['.$result->nid.']" id="productimageid4-'.$result->nid.'"/>', 'width' => '60px');
		else
		$row[] = array('data' => '<div class="product-trash-area"><a href="'.base_path().'siteadmin/draganddropimages/'.$result->nid.'/4"><img src="'.base_path().path_to_theme().'/images/icon_delete.png" /></a></div><div class="droppable4 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file4-filename'].'</div></div><input type="hidden" value="'.$image_path['file4-path'].'" name="productimageid4['.$result->nid.']" id="productimageid4-'.$result->nid.'"/>', 'width' => '60px');

		// Image 5
		$image_output = theme('image', $image_path['file5-path'], '', NULL, array('width'=>'60px','height'=>'60px'), FALSE);
		if(PWCS_PRODUCT_DEFAULT_IMAGE==$image_path['file5-path'])
		$row[] = array('data' => '<div class="droppable5 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file5-filename'].'</div></div><input type="hidden" value="'.$image_path['file5-path'].'" name="productimageid5['.$result->nid.']" id="productimageid5-'.$result->nid.'"/>', 'width' => '60px');
		else
		$row[] = array('data' => '<div class="product-trash-area"><a href="'.base_path().'siteadmin/draganddropimages/'.$result->nid.'/5"><img src="'.base_path().path_to_theme().'/images/icon_delete.png" /></a></div><div class="droppable5 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file5-filename'].'</div></div><input type="hidden" value="'.$image_path['file5-path'].'" name="productimageid5['.$result->nid.']" id="productimageid5-'.$result->nid.'"/>', 'width' => '60px');

		// Image 6
		$image_output = theme('image', $image_path['file6-path'], '', NULL, array('width'=>'60px','height'=>'60px'), FALSE);
		if(PWCS_PRODUCT_DEFAULT_IMAGE==$image_path['file6-path'])
		$row[] = array('data' => '<div class="droppable6 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file6-filename'].'</div></div><input type="hidden" value="'.$image_path['file6-path'].'" name="productimageid6['.$result->nid.']" id="productimageid6-'.$result->nid.'"/>', 'width' => '60px');
		else
		$row[] = array('data' => '<div class="product-trash-area"><a href="'.base_path().'siteadmin/draganddropimages/'.$result->nid.'/6"><img src="'.base_path().path_to_theme().'/images/icon_delete.png" /></a></div><div class="droppable6 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file6-filename'].'</div></div><input type="hidden" value="'.$image_path['file6-path'].'" name="productimageid6['.$result->nid.']" id="productimageid6-'.$result->nid.'"/>', 'width' => '60px');

		// Image 7
		$image_output = theme('image', $image_path['file7-path'], '', NULL, array('width'=>'60px','height'=>'60px'), FALSE);
		if(PWCS_PRODUCT_DEFAULT_IMAGE==$image_path['file7-path'])		
		$row[] = array('data' => '<div class="droppable7 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file7-filename'].'</div></div><input type="hidden" value="'.$image_path['file7-path'].'" name="productimageid7['.$result->nid.']" id="productimageid7-'.$result->nid.'"/>', 'width' => '60px');
		else
		$row[] = array('data' => '<div class="product-trash-area"><a href="'.base_path().'siteadmin/draganddropimages/'.$result->nid.'/7"><img src="'.base_path().path_to_theme().'/images/icon_delete.png" /></a></div><div class="droppable7 ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p><div class="product-picture-filename">'.$image_path['file7-filename'].'</div></div><input type="hidden" value="'.$image_path['file7-path'].'" name="productimageid7['.$result->nid.']" id="productimageid7-'.$result->nid.'"/>', 'width' => '60px');

		// Image 8
		//$row[] = array('data' => '<div class="droppable ui-droppable" id="'.$result->nid.'"><p>'.$image_output.'</p></div>', 'width' => '60px');

		//$row[] = '';
		$rows[] = $row;
	}
	$row = array();
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$rows[] = $row;	
	$output_table .= '<form id="siteadmin-productlist-form" method="post" accept-charset="UTF-8" action="'.base_path().'siteadmin/productsaction/pricemanager_product_action">';
	$output_table .= theme('table', $header, $rows, array('width'=>'100%'));
	$output_table .= '<input type="submit" class="form-submit form-save-submit" value="'.t('Save').'" id="save" name="save">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="form-submit" value="'.t('Delete Images').'" id="delete" name="delete"> ';
	//$output .= '<input type="submit" class="form-submit form-save-submit" value="'.t('Save').'" id="delete-all" name="saveproduct">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="form-submit" value="'.t('Delete All').'" id="delete-all" name="delete"> <input type="submit" class="form-submit" value="'.t('Visible On Site').'" id="appearable-all" name="appearable"> <input type="submit" class="form-submit" value="'.t('Not Visible On Site').'" id="nonappearable-all" name="nonappearable">';
	$output_table .= '</form>';

	$output_table .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);
	$output_table .= '</div>';
	$output1= '<div class="pwcs_prodcut_list_count" style="margin:5px 0; font-style: italic;">'.t('Total').' '.$product_amount.' '.t('product(s) found').'. Visible Products: '.($product_amount-$invisible_products).'. Not Visible Products:'.$invisible_products.'.</div>';
	$output_table = $output1.$output_table;

	$imce['dir'] = "imagelibrary";
   
	
	$output_images ='';
		
	$output_images.=pwcs_getImageDirectoryList($_SERVER['DOCUMENT_ROOT'].base_path().file_directory_path().DIRECTORY_SEPARATOR.$imce['dir']);
	//$output .= '<div  class="container">';
	$output .= '<div style="width:85%;float:left">'.$output_table.'</div>';
	$output .= '<div style="border:0;background: none repeat scroll 0 0 #FFFFFF;padding-top:15px;"><div id="image_div"><b>Please Drop The Images to the Product Image Area</b><br/><hr/><br/>'.$output_images.'</div></div>';
	//$output .= '<div style="height:600px;overflow:auto;" id="image_div2">hellll</div>';
	//$output .= '</div>';
	//$output .= '<div id="floatdiv" style="position:absolute;width:232px;    background:#FFFFFF;    border:2px solid #2266AA;    top:2px;    left:10px;    z-index:100"> My Float</div><script type="text/javascript"> function doMove(objname){obj=document.getElementById(objname);obj.style.top="125px";t=setTimeout("doMove(\"floatdiv\");",500);} doMove("floatdiv"); </script>';
	//$js="$('#image_div').stickyfloat({ duration: 400 });";
	//drupal_add_js($js, 'inline', 'footer');
	return $output;
}
function pwcs_getImageDirectoryList($dirname)
{
  
  $not_allowed_exten = array(".svn", "thumbnail");
  if (is_dir($dirname))
  {
  //echo "<br/><b>Directory Name:<i> $dirname --></i></b><br/>";
  if($dirname[strlen($dirname)-1]!='\\')
    $dirname.='/';

  $dirname = str_replace("//", "/", $dirname);
 
  static $output_images_list='';  
  $handle=opendir($dirname);
  while ($file = readdir($handle))
  {
		if (!is_dir($dirname.$file)){
			//echo "File Name: <i>".$file."<br/></i>";
						//$results = db_query("SELECT * FROM {files} WHERE filename='%s'",$file);
						//$foundimage=0;
						//while($result = db_fetch_object($results)) {
							//if (file_exists(file_directory_path() .'/imagelibrary/'.$file))
							//{
							//$foundimage=1;
							
							//}
						  //}	
			//if($foundimage==1)						
			$output_images_list .='' .
							'<div class="draggable ui-draggable">
				        	<div id="'.file_directory_path() .'/imagelibrary/'.$file.'"><img src="'.base_path().file_directory_path().'/imagelibrary/'.$file.'" alt="Product Image" width="50" height="50" /><div style="padding-top:0px;" class="draggable-picname">'.$file.'</div></div>
							<p></p>
							</div>';
							
										
		}elseif (is_dir($dirname.$file)){
			if($file=='.'||$file=='..')
     		        continue;
		  if (!(in_array($file, $not_allowed_exten))){
		  //if((strpos($file, ".svn")===false)){
		  	//echo "Directory Name: <i>".$file."<br/></i>";
		  	pwcs_getImageDirectoryList($dirname.$file);
		  }
		}
  }
 
   closedir($handle);
   return $output_images_list;
  } 
}
function pwcs_product_list_order_manager($list_id=NULL)
{
	$breadcrumb = array();
	$breadcrumb[] = l(t('Home'), '<front>');
	$breadcrumb[] = l(t('List Management'), 'siteadmin/listings');
	$breadcrumb[] = l(t('Product Order List'), '#');
	drupal_set_breadcrumb($breadcrumb);


	$output	= '';
	$rows = array();
	$categories = pwcs_cat_array();

	$header = array(
		array('data' => '<input type="checkbox" id="products-check-all" class="form-checkbox checkbox_checkall" checked="checked"  title="Select all rows in this table">', 'width'=> '10px'),
		array('data' => '', 'width'=> '60px'),
		array('data' => t('Product Name'), 'field' => 'n.title', 'width'=> '350'),
		array('data' => t('Price'), 'field' => 'pp.price',  'width'=> '200'),
		array('data' => t('Visible On Site'), 'field' => 'pp.price',  'width'=> '50'),
		array('data' => '<input type="submit" class="form-submit" value="'.t('Save').'" id="delete-all" name="delete">',   'width'=> ''),
	);

	$sql  = "SELECT n.*, pp.* FROM {node} n";
	$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
    $sql .= " LEFT JOIN {pwcs_product_listing} ppl ON ppl.pwc_product_row_id = pp.pwc_product_row_id";		
	$sql .= " WHERE n.type = 'product' AND ppl.listing_id =  '".$list_id."'";
	
	/**
	
	//SELECT n.nid, ppl.listing_id FROM `dru_node` n INNER JOIN dru_pwcs_product pp ON n.nid = pp.nid  LEFT JOIN dru_pwcs_product_listing ppl ON ppl.pwc_product_row_id=pp.pwc_product_row_id WHERE n.type = 'product' LIMIT 0, 120
	
	*/
	

	$results = db_query($sql);
	$product_quantity = db_affected_rows($results);
	
	$output .= '<h3>'.t('Products List').'</h3>';

	$product_amount = db_affected_rows($results);
	$output .= '<div class="pwcs_prodcut_list_count" style="margin:5px 0; font-style: italic;">'.t('Total').' '.$product_quantity.' '.t('product(s) found').'</div>';

	$output .= '<div class="pwcs_table">';
	while($result = db_fetch_object($results))
	{
		$row = array();
		$node = node_load($result->nid);

		// Delete check box
		$row[] = '<input type="checkbox" id="product-'.$result->nid.'" class="form-checkbox" name="productnid[]" checked="checked" value="'.$result->nid.'">';

		// Product Primary Image
		$image_path = pwcs_get_product_primary_image_path($node);
		$image_output = theme('image', $image_path, '', NULL, array('width'=>'60px'), FALSE);
		$row[] = array('data' => $image_output, 'width' => '60px');

		// Product Title
		$row[] = array('data' => l(trim($result->title),'node/'.$node->nid.'/edit', array('attributes' => array('target'=> '_blank'))).'<br/>['.$node->pwc_product_id.']', 'width' => '100');
				
		// Product Price
		$row[] = PWCS_BASIC_CURRENCY_SYMBOL.'<input type="textbox" id="product-'.$result->nid.'" class="form-checkbox" name="productprice['.$result->nid.']" value="'.$result->price.'">';
		if($node->status==1)
			$row[] = '<input type="checkbox" id="" name="nodeid['.$node->nid.']" value="1" checked=checked />';
		else
			$row[] = '<input type="checkbox" id="" name="nodeid['.$node->nid.']" value="0" />';

		$row[] = '';
		$rows[] = $row;
	}
	$output .= '<form id="siteadmin-productlist-form" method="post" accept-charset="UTF-8" action="'.base_path().'siteadmin/productsaction/pricemanager_product_action">';
	$output .= theme('table', $header, $rows, array('width'=>'100%'));
	$output .= '<input type="submit" class="form-submit" value="'.t('Save').'" id="delete-all" name="delete">';

	$output .= '</form>';

	$output .= '</div>';

	return $output;
}
/*
function pwcs_public_list_products_url($pwc_category_id=NULL, $manufacturer_id=NULL){

	$pwc_category_id=($pwc_category_id=='all')?NULL:$pwc_category_id;
	$manufacturer_id=($manufacturer_id=='all')?NULL:$manufacturer_id;

	drupal_set_title(t('Products'));
	$output	= '';
	$options = array(
				'pwc_category_id' => $pwc_category_id,
				'manufacturer_id' => $manufacturer_id,
				'view_type' => 'page',
				);
	$output .= pwcs_public_products_list($options);
	return $output;
}

function pwcs_public_list_products_list($options = array()){

	$theme = variable_get('pwcs_sitesettings_productview_theme', 'spv_normal');

	if(isset($options['view_type']) && ($options['view_type'] == 'block')) {
		$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
		$pagelimit = variable_get('pwcs_product_block_page_amount', 10);
		$pageelement = 0;
		$pageparameters = array();
		$pagequantity = 3;
	} else {
		$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
		$pagelimit = 12;
		$pageelement = 0;
		$pageparameters = array();
		$pagequantity = 3;

		if($options['pwc_category_id'] != NULL) {
			$category_details = pwcs_category_details($options['pwc_category_id']);
			drupal_set_title($category_details['category_title']);
			$breadcrumb = array();
			$breadcrumb[] = l(t('Home'), '<front>');
			$breadcrumb[] = $category_details['category_title'];
			drupal_set_breadcrumb($breadcrumb);
		}
		if($options['manufacturer_id'] != NULL) {
			$mfg_name = db_result(db_query('SELECT mfg_name FROM {pwcs_manufacturer} WHERE pwc_mfg_id='.$options['manufacturer_id']));
			// Set Page Title
			drupal_set_title($mfg_name);
			$breadcrumb = array();
			$breadcrumb[] = l(t('Home'), '<front>');
			$breadcrumb[] = l(t('Manufacturers'), 'manufacturers');
			$breadcrumb[] = $mfg_name;
			drupal_set_breadcrumb($breadcrumb);
		}
	}

	$output	= '';
	$output .= '<div id="pwcs_product_block" style="margin-bottom: 10px;">';

	$sql  = "SELECT n.nid, pp.* FROM {node} n";
	$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
	$sql .= " INNER JOIN {pwcs_listings} pc ON pc.pwc_category_id IN (pp.listing_id) ";
	$sql .= " WHERE n.type = 'product' AND n.status = 1";

	if($options['pwc_category_id'] != NULL)
		$sql .= " AND pp.listing_id  LIKE '%".$options['pwc_category_id']."%' ";

	if($options['manufacturer_id'] != NULL)
		$sql .= " AND pp.manufacturer_id = ".$options['manufacturer_id'];

	$sql .= " ORDER BY n.changed desc";

	if(pwcs_result_count($sql) > 0) {
		$results = pager_query($sql, $pagelimit);
		$categories = pwcs_cat_array();
		while($result = db_fetch_object($results))
		{
			$product = node_load($result->nid);
			$product->view_category = $categories[$product->pwc_category_id]?$categories[$product->pwc_category_id]:'<em>'.t('None').'</em>';
			$output .= pwcs_single_product_view($theme, $product);
		}
	} else {
		$output .= '<div class="warning"> ' . t('There are currently no products in this category!') . ' </div>';
	}
	$output .= '<div style="clear:both; padding:10px 0;"></div>';
	$output .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);
	$output .= '</div>';

	return $output;
}
*/
/**
 * Get product primary image path
 * @param node nid or product node object [usually returned from node_load()]
 * @return path of the parimary image
 * Added by ZERIN
 */
function pwcs_get_product_primary_imageandname_path($product = NULL){

	// if $product not object then it will consider as an node id
	if(!is_object($product) && is_numeric($product)) {
		$product = noad_load($product);
	}
	$image_path = array();
	$image_path['file1-path'] = PWCS_PRODUCT_DEFAULT_IMAGE;
	$image_path['file1-filename'] = '';
	$image_path['file2-path'] = PWCS_PRODUCT_DEFAULT_IMAGE;
	$image_path['file2-filename'] = '';
	$image_path['file3-path'] = PWCS_PRODUCT_DEFAULT_IMAGE;
	$image_path['file3-filename'] = '';
	$image_path['file4-path'] = PWCS_PRODUCT_DEFAULT_IMAGE;
	$image_path['file4-filename'] = '';
	$image_path['file5-path'] = PWCS_PRODUCT_DEFAULT_IMAGE;
	$image_path['file5-filename'] = '';
	$image_path['file6-path'] = PWCS_PRODUCT_DEFAULT_IMAGE;
	$image_path['file6-filename'] = '';
	$image_path['file7-path'] = PWCS_PRODUCT_DEFAULT_IMAGE;
	$image_path['file7-filename'] = '';
	
	if($product->field_product_image[0] != NULL) {
		//echo count($product->field_product_image);
		//die('1');
		$i=0;
		foreach ($product->field_product_image as $key=>$val) {
				//echo 'key'.$key;
				if ($i==0){
				$image_path['file1-path']  = $val['filepath'];
				$image_path['file1-filename']  = $val['filename'];
				}
				if ($i==1){
				$image_path['file2-path']  = $val['filepath'];
				$image_path['file2-filename']  = $val['filename'];
				}
				if ($i==2){
				$image_path['file3-path']  = $val['filepath'];
				$image_path['file3-filename']  = $val['filename'];
				}
				if ($i==3){
				$image_path['file4-path']  = $val['filepath'];
				$image_path['file4-filename']  = $val['filename'];
				}
				if ($i==4){
				$image_path['file5-path']  = $val['filepath'];
				$image_path['file5-filename']  = $val['filename'];
				}
				if ($i==5){
				$image_path['file6-path']  = $val['filepath'];
				$image_path['file6-filename']  = $val['filename'];
				}
				if ($i==6){
				$image_path['file7-path']  = $val['filepath'];
				$image_path['file7-filename']  = $val['filename'];
				}				
				$i++;
		}
	}
	return $image_path;
}
function pwcs_delete_noexist_images()
{
	$imce['dir'] = "imagelibrary";
    pwcs_delete_unassociated_images($_SERVER['DOCUMENT_ROOT'].base_path().file_directory_path().DIRECTORY_SEPARATOR.$imce['dir']);
    die('DONE');
}
function pwcs_delete_unassociated_images($dirname)
{
  
  $not_allowed_exten = array(".svn", "thumbnail");
  if (is_dir($dirname))
  {
  if($dirname[strlen($dirname)-1]!='\\')
    $dirname.='/';

  $dirname = str_replace("//", "/", $dirname);
 
  static $output_images_list='';  
  $handle=opendir($dirname);
  while ($file = readdir($handle))
  {
		if (!is_dir($dirname.$file)){
						$results = db_query("SELECT * FROM {files} WHERE filename='%s'",$file);
						$foundimage=0;
						while($result = db_fetch_object($results)) {
							if (file_exists(file_directory_path() .'/imagelibrary/'.$file))
							{
								$foundimage=1;
							}
						}	
			if($foundimage==0)
			@unlink(file_directory_path() .'/imagelibrary/'.$file);						
		}elseif (is_dir($dirname.$file)){
		  if($file=='.'||$file=='..')
     		continue;
		  if (!(in_array($file, $not_allowed_exten))){
		  	pwcs_delete_unassociated_images($dirname.$file);
		  }
		}
  }
 
   closedir($handle);
   return $output_images_list;
  } 
}
function pwcs_siteadmin_socialmedia_page($action=NULL) {

	//die('action:'.$action);
	$output = '';
	$output .= '<div class="pwcs-page-content-inner">';
	$filter = $_SESSION['pwcs_products_filter']? $_SESSION['pwcs_products_filter'] : NULL;
	$output .= '<div id="pwcs_product_list_filter_box" style="margin-bottom:15px;">';
	$output .= '<h3 class="pwcs_product_list_filter_title expanded">'.t('Social Media').'</h3>';
	$output .= drupal_get_form('pwcs_siteadmin_socialmedia');
	$output .= '</div>';
	$output .= '</div>';

	return $output;
}
function pwcs_siteadmin_tools_page($action=NULL) {

	//die('action:'.$action);
	$output = '';
	$output .= '<div id="pwcs_product_list" class="ws-content-list">';
	$output .= '<h3 class="pwcs_product_list_filter_title expanded">'.t('Tools').'</h3>';
	$output .= drupal_get_form('pwcs_siteadmin_soacialmediathirdparty');
	$output .= '</div>';
	$output .= '</div>';

	return $output;
}
/**
 * Site Admin Category page theme
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_socialbuzz($action=NULL, $listing_id=NULL) {

	$output = '';
	// List of all Listings
	$output .= '<div class="pwcs-page-content-inner">';
	
	$output .= '<div id="category_list" class="pwcs-content-list">';
	$output .= drupal_get_form('pwcs_siteadmin_socialbuzz_form');
	$output .= '</div>';
	$output .= '</div>';
	return $output;
}

/**
 * Category list with drag and drop sorting [List as a form]
 * Added by Rasel Kabir 23 September 2010
 */
function pwcs_siteadmin_socialbuzz_form(&$form_state) {
	
	global $base_url;
	
	$form = array();
	$form['#tree'] = TRUE;
	$form['#theme'] = 'pwcs_siteadmin_tellafriendlist_form';
	$result_query = db_query("SELECT id, socialname,logofile, isvisible, weight FROM {pwcs_sociallinks} ORDER BY weight");

	while ($row = db_fetch_array($result_query)) {
		$form[$row['id']]['#item'] = $row['id'];
		
		$form[$row['id']]['name']['#value'] = $row['socialname'];
		$form[$row['id']]['icon']['#value'] = $row['logofile'];
		
		//The id of the field must be included so each  
		$form[$row['id']]['id'] = array(
			'#type' => 'hidden',
			'#value' => $row['id']
		);
		if($row['isvisible']==1)
		$form[$row['id']]['status']	= array('#value' => '<input type="checkbox" id="" name="listid['.$row['id'].']" value="1" checked=checked />');
		else
		$form[$row['id']]['status']	= array('#value' => '<input type="checkbox" id="" name="listid['.$row['id'].']" value="0"  />');
		//This is the weight field that determines where the item is sorted. The delta value is half of the maximum number of items plus 1 that can be sorted in this table. For example, we can sort 41 items in this table (-20 to 20).
		$form[$row['id']]['weight'] = array(
			'#type' => 'weight',
			'#delta' => 20,
			'#default_value' => $row['weight']
		);
	}
		
	
			
	//$form = _pwcs_siteadmin_tellafriendlist_form();
//	$form[0]['No']= array('#value' => t('1'));
//	$form[0]['title']= array('#value' => t('StumbleUpon'));
//	
//	$form[1]['No']= array('#value' => t('2'));
//	$form[1]['title']= array('#value' => t('Digg'));
	
	
	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Save'),
	);

	return $form;
}
function theme_pwcs_siteadmin_socialbuzz_form($form) {
global $theme_path;
drupal_add_tabledrag('thetable', 'order', 'sibling', 'theweight');
  $header = array(
    t('SL.'),
    t(''),
    t('Name'),
    t('Visible'), 
    t('Weight'),
  );

  $rows = array();

	//Loop through each item to display in the sortable table
  foreach (element_children($form) as $key) {
    if (isset($form[$key]['name'])) {
			//Make this variable the weight class defined in the drupal_add_tabledrag function.
			$form[$key]['weight']['#attributes']['class'] = 'theweight';
			$row = array();
			$row[] = array('data' => '', 'width'=>'2px');
			$row[] = array('data' => '<img src="'.base_path().path_to_theme().'/images/'.drupal_render($form[$key]['icon']).'" width="16" height="16" />', 'width'=>'16px');
			$row[] = drupal_render($form[$key]['name']);
			$row[] = drupal_render($form[$key]['status']);
			$row[] = drupal_render($form[$key]['weight']);
			$rows[] = array(
				'data' => $row,
				'class' => 'draggable'
			);
		}
	}
	
	//Finally, output the sortable table. Make sure the id variable is the same as the table id in drupal_add_tabledrag
	$output = theme('table', $header, $rows, array('id' => 'thetable'));
	$output .= drupal_render($form);
  return $output;
}
function pwcs_siteadmin_socialbuzz_form_submit($form, &$form_state)  {
	
	$values = $form_state['values']; //All form values
	$sql = "UPDATE {sociallinks} SET isvisible = '0'";
	db_query($sql);
	$i=0;
	foreach ($form['submit']['#post']['listid'] as $my_list_id=> $value)
	{
		$i++;
		if($i!=count($form['submit']['#post']['listid']))
			$active_listing_id .= $my_list_id.',';
		else
			$active_listing_id .= $my_list_id;
		//echo 'Here'.$my_list_id;
	
		$sql = "UPDATE {sociallinks} SET isvisible = '1' WHERE id=%d";
		db_query($sql,$my_list_id);		
	}

    foreach ($values as $arrkey => $valuearr) { //Cycle through each row of values
		if (is_array($valuearr)) {
		$updateWeight = array(
				"id"=>$valuearr['id'],
				"weight"=>$valuearr['weight'],
			);
			db_query("UPDATE {pwcs_sociallinks} SET weight='".$valuearr['weight']."' WHERE id='".$valuearr['id']."'");
		}
	}		
}