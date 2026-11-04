<?php
/* $Id: pwcs_product_single_view.php,v 1.0 2010/12/01 Rasel Kabir Shuvro <shuvro.mist@gmail.com> Exp $ */

/**
 * Pre defined options;
 * 
 * These options are used in pwcs_siteadmin_sitesettings() function as admin settings.
 * where default is set to "normal"
 * Added by Rasel Kabir
 */
function pwcs_product_single_view_options($deom = true){

	$spv_theme['spv_mini']						= '<div class="title">'.t('Minimum').'</div>'; // . '<div class="description">'.t('Title and Image').'</div>';
	$spv_theme['spv_small']						= '<div class="title">'.t('Small').'</div>'; // . '<div class="description">'.t('Title, Image and Price').'</div>';
	$spv_theme['spv_small_cartlink']	= '<div class="title">'.t('Small With CartLink').'</div>'; // . '<div class="description">'.t('Title, Image, Price, Add to Cart Link').'</div>';

	$spv_theme['spv_normal']					= '<div class="title">'.t('Normal').'</div>';// . '<div class="description">'.t('Fixed Width, Title, Image, Category, Price, Add to Cart Link').'</div>';
	$spv_theme['spv_normal_details']	= '<div class="title">'.t('Normal With Short Details').'</div>'; // .	'<div class="description">'.t('Fixed Width, Title, Image, Category, Price, Short details, Add to Cart Link').'</div>';
	$spv_theme['spv_normal2']					= '<div class="title">'.t('Normal Alternative view').'</div>'; // . '<div class="description">'.t('Fixed Width, Title, Image, Category, Price, Short details, Add to Cart Link').'</div>';
	$spv_theme['spv_normal3']					= '<div class="title">'.t('Normal Large view').'</div>'; // . '<div class="description">'.t('260px X 260px Product Thumbnail, Fixed Width, Title, Image, Category, Price, Short details, Add to Cart Link').'</div>';

	$spv_theme['spv_wide']						= '<div class="title">'.t('Wide with Short Details').'</div>'; // . '<div class="description">'.t('Liquid Width, Title, Image, Category, Price, Short details, Add to Cart Buttons').'</div>';
	$spv_theme['spv_wide2']						= '<div class="title">'.t('Wide with Short Details, Quantity and Options').'</div>'; // . '<div class="description">'.t('Liquid Width, Title, Short details, Image, Category, Price, Quantity field, Add To Cart button, Details button and Options in a box').'</div>';
	$spv_theme['spv_wide3']						= '<div class="title">'.t('Wide with Short Details, Quantity and Options (Inline)').'</div>'; // . '<div class="description">'.t('Liquid Width, Title, Short details, Image, Category, Price, Quantity field, Add To Cart button, Details button and inline Options').'</div>';

	$spv_theme['spv_search']					= '<div class="title">'.t('Search Result view').'</div>'; // . '<div class="description">'.t('Liquid Width, Title, Image, Category, Price, Short details, Add to Cart Buttons').'</div>';

	if ($deom == false) {
		return $spv_theme;
	} else {
		$nid  = db_result(db_query("SELECT n.nid FROM {node} n Inner Join {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product' AND n.status = 1 ORDER BY n.changed desc"));
		$product = node_load($nid);
		$categories = pwcs_cat_array();
		$product->view_category = $categories[$product->pwc_category_id]?$categories[$product->pwc_category_id]:'<em>'.t('None').'</em>';
		
		$spv_preview_path = PWCS_MODULE_IMAGE.'spv_demo/';
		$active_theme = variable_get('theme_default','none');
		$spv_preview_path_active_theme = drupal_get_path('theme',  $active_theme).'/spv_demo/';
		
		$spv_demo_image = array();
		$spv_demo_image_multiple = array();
		foreach ($spv_theme as $spv_theme_key => $title) {
			$output  = '';
			$output .= '<div class="admin_psv_options">';
			$output .= '<div class="option_title_details">';
			$output .= $title;
			if(file_exists($spv_preview_path_active_theme.$spv_theme_key.'.png')) {
				$spv_demo_image[$spv_theme_key] = $spv_preview_path_active_theme.$spv_theme_key.'.png';
			} else {
				$spv_demo_image[$spv_theme_key] = $spv_preview_path.$spv_theme_key.'.png';
			}
//			if(file_exists($spv_preview_path_active_theme.$spv_theme_key.'_multiple.png')) {
//				$spv_demo_image_multiple[$spv_theme_key] = $spv_preview_path_active_theme.$spv_theme_key.'_multiple.png';
//			} else {
//				$spv_demo_image_multiple[$spv_theme_key] = $spv_preview_path.$spv_theme_key.'_multiple.png';
//			}
			$output .= '<div class="preview_image">'
									.l(t('Preview'), $spv_demo_image[$spv_theme_key], array('html' => TRUE, 'attributes' => array('class' => 'button', 'rel'=>'lightbox[]')))
									//.l(t('Multiple Preview'), $spv_demo_image_multiple[$spv_theme_key], array('html' => TRUE, 'attributes' => array('class' => 'button', 'rel'=>'lightbox[]')))
									.'</div>';
			$output .= '</div>';

			//$output .= '<div style="clear:both;"></div>';
			//$output .= '<div id="showhidelink_'.$spv_theme_key.'" class="showhidelink">';
			//$output .= '<a href="#" psv_key="'.$spv_theme_key.'">'.t('Show/Hide Preview').'</a>';
			//$output .= '</div>';
			//$output .= '<div class="psv_view" id="psv_template_'.$spv_theme_key.'">';
			//$output .= pwcs_single_product_view($spv_theme_key, $product);
			//$output .= '<div style="clear:both;"></div>';
			//$output .= '</div>';
			$output .= '</div>';

			$output_array[$spv_theme_key] = $output;
		}
		return $output_array;
	}
}

/**
 * Function to view a single product.
 * Added by Rasel Kabir
 */
function pwcs_single_product_view($spv_theme_key='spv_normal', $product){
	
	$spv_theme_array = explode("_",$spv_theme_key);
	$spv_theme_name = $spv_theme_array[1];
	unset($spv_theme_array[0]);
	unset($spv_theme_array[1]);
	foreach ($spv_theme_array as $k => $v) {
		$options[$v] = TRUE;
	}
	return call_user_func('theme_pwcs_spv_'.$spv_theme_name, $product, $options);
}

/**
 * Function to view a single product.
 * 'theme_mini'							=> t('Theme Minimum : Title and Image'),
 * Added by Rasel Kabir
 */
function theme_pwcs_spv_mini($product, $options = array()) {

	$output = '';
	$output .= '<div class="product_mini_box_class product_box_class" product_id = "'.$product->nid.'">';
	$output .= '<div class="single_product_image">';
	$image_path = pwcs_get_product_primary_image_path($product);
	if(is_numeric(_pwcs_isset_imagecache_preset('spv_70x70'))){
		$output .= theme('imagecache', 'spv_70x70', $image_path, '', $product->title);
	}	else {
		$output .= theme('image', $image_path, '', $product->title, array('width'=>'70px', 'height'=>'70px'), FALSE);
	}
	$output .= '</div>';
	$options['attributes']['title'] = $product->title;
	$options['html'] = TRUE;
	$output .= '<div class="product_title">'.l(truncate_utf8($product->title, 14, FALSE, TRUE),'node/'.$product->nid, $options).'</div>';
	$output .= '</div>';

	return $output;
}

/**
 * Function to view a single product.
 * 'theme_small'							=> t('Theme Small : Title, Image and Price'),
 * 'theme_small_cartlink'		=> t('Theme Small With CartLink : Title, Image, Price, Add to Cart Link'),
 * Added by Rasel Kabir
 */
function theme_pwcs_spv_small($product, $options = array()) {

	$output = '';
	$output .= '<div class="product_small_box_class product_box_class" product_id = "'.$product->nid.'">';
	$output .= '<div class="single_product_image">';
	$image_path = pwcs_get_product_primary_image_path($product);

	if(is_numeric(_pwcs_isset_imagecache_preset('spv_100x100'))){
		$output .= theme('imagecache', 'spv_100x100', $image_path, '', $product->title);
	}	else {
		$output .= theme('image', $image_path, '', $product->title, array('width'=>'100px', 'height'=>'100px'), FALSE);
	}

	$output .= '</div>';
	$options['attributes']['title'] = $product->title;
	$options['html'] = TRUE;
	$output .= '<div class="product_title">'.l(truncate_utf8($product->title, 18, FALSE, TRUE),'node/'.$product->nid, $options).'</div>';

	if(!isset($options['noprice'])) {
		//$output .= '<div class="product_element">'.t('Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</div>';
		if($product->product_saleprice['product_sale_price']){
			$saletag_icon = theme('image', variable_get('pwcs_product_saleprice_icon_'.$product->product_saleprice['sale_tag_icon'], PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag'.$product->product_saleprice['sale_tag_icon'].'.png'), '', '', array('width'=>'auto'), FALSE);
			$output .= '<div class="product_element_price">';
			$output .= '<p class="price_strike_through">'.t('Regular Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</p>';
			$output .= '<p class="saleprice">'.t('Sale Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->product_saleprice['product_sale_price'].'</p>';
			$output .= '<p class="salestag">'.$saletag_icon.'</p>';
			$output .= '</div>';
		} else {
			$output .= '<div class="product_element_price">'.t('Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</div>';
		}
	}
	if(isset($options['cartlink'])) {
		$output .= '<div class="product_buy_button">';
		$output .= l(variable_get('pwcs_spv_addtocartlink_text', 'Add To Cart'),'addtocart/'.$product->nid, array('attributes' => array('class' => 'pwcs-link-text addtocart-link-text', 'ajax_addtocart_url' => base_path().'pwcsajax/addtocart' , 'product_id'=>$product->nid, 'product_link_id'=>$product->product_link_id, 'name' => 'product-addtocart', 'id' => 'addtocart-id-'.$product->nid, 'title' => 'Click here to add this product to your cart')));
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}

/**
 * Function to view a single product.
 * 'theme_normal'						=> t('Theme Normal : Fixed Width, Title, Image, Category, Price, Add to Cart Link'),
 * 'theme_normal_details'		=> t('Theme Normal With Short Details : Fixed Width, Title, Image, Category, Price, Short details, Add to Cart Link'),
 * Added by Rasel Kabir
 */
function theme_pwcs_spv_normal($product, $options = array()){

	$output ='';
	//$output .= '<div class="product_box_class" id="product_box_'.$product->nid.'">';
	$output .= '<div class="product_normal_box_class product_box_class" product_id = "'.$product->nid.'">';
	$output .= '<div class="single_product_image">';
	$output .= '<a href="'.base_path().'node/'.$product->nid.'">';
	$image_path = pwcs_get_product_primary_image_path($product);
	$alter = '';
	$title = '';
	if(is_numeric(_pwcs_isset_imagecache_preset('spv_135x160'))){
		$output .= theme('imagecache', 'spv_135x160', $image_path, $alter, $title);
	}	else {
		$output .= theme('image', $image_path, $alter, $title, array('width'=>'135px', 'height'=>'160px'), FALSE);
	}
	$output .= '</a>';
	$output .= '</div>';

	$options['attributes']['title'] = $product->title;
	$options['html'] = TRUE;
	$output .= '<div class="product_title">'.l(truncate_utf8($product->title, 22, FALSE, TRUE),'node/'.$product->nid, $options).'</div>';
	$output .= '<div class="product_element">'.t('Category').': '.$product->view_category.'</div>';

	if($product->product_saleprice['product_sale_price']){
		$saletag_icon = theme('image', variable_get('pwcs_product_saleprice_icon_'.$product->product_saleprice['sale_tag_icon'], PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag'.$product->product_saleprice['sale_tag_icon'].'.png'), '', '', array('width'=>'auto'), FALSE);
		$output .= '<div class="product_element_price">';
		$output .= '<p class="price_strike_through">'.t('Regular Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</p>';
		$output .= '<p class="saleprice">'.t('Sale Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->product_saleprice['product_sale_price'].'</p>';
		$output .= '<p class="salestag">'.$saletag_icon.'</p>';
		$output .= '</div>';
	} else {
		$output .= '<div class="product_element_price">'.t('Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</div>';
	}

	if(isset($options['details'])) {
		$output .= '<div class="product_shortdetails">'.truncate_utf8($product->teaser, 90, true, true).'</div>';
	}

	$output .= '<div class="product_buy_button">';
	$output .= l(variable_get('pwcs_spv_addtocartlink_text', 'Add To Cart'),'addtocart/'.$product->nid, array('attributes' => array('class' => 'pwcs-link-text addtocart-link-text', 'ajax_addtocart_url' => base_path().'pwcsajax/addtocart' , 'product_id'=>$product->nid, 'product_link_id'=>$product->product_link_id, 'name' => 'product-addtocart', 'id' => 'addtocart-id-'.$product->nid, 'title' => 'Click here to add this product to your cart')));
	if(($product->one_click_buy_link) && variable_get('pwcs_sitesettings_oneclickbuy', '1')){
		$output .= ' | '.l(variable_get('pwcs_sitesettings_oneclickbuy_text', 'One Click Buy'), $product->one_click_buy_link, array('attributes' => array('class' => 'pwcs-link-text oneclickbuy-link-text', )));
	}
	$output .= '</div>';

	$output .= '</div>';

	return $output;
}

/**
 * Function to view a single product.
 * 'theme_normal'						=> t('Theme Normal2 : Fixed Width, Title, Image, Price, Add to Cart Link'),
 * Added by Rasel Kabir
 */
function theme_pwcs_spv_normal2($product, $options = array()){

	$output ='';
	//$output .= '<div class="product_box_class" id="product_box_'.$product->nid.'">';
	$output .= '<div class="product_normal2_box_class product_box_class" product_id = "'.$product->nid.'">';

	$options['attributes']['title'] = $product->title;
	$options['html'] = TRUE;
	$output .= '<div class="product_title">'.l(truncate_utf8($product->title, 30, FALSE, true),'node/'.$product->nid, $options).'</div>';

	$output .= '<div class="imageandprice">';
	$output .= '<div class="single_product_image">';
	$output .= '<a href="'.base_path().'node/'.$product->nid.'">';
	$image_path = pwcs_get_product_primary_image_path($product);
	$alter = '';
	$title = '';
	if(is_numeric(_pwcs_isset_imagecache_preset('spv_85x85'))){
		$output .= theme('imagecache', 'spv_85x85', $image_path, $alter, $title);
	}	else {
		$output .= theme('image', $image_path, $alter, $title, array('width'=>'85px', 'height'=>'85px'), FALSE);
	}
	$output .= '</a>';
	$output .= '</div>';

	//$output .= '<div class="product_element">'.t('Category').': '.$product->view_category.'</div>';
	if($product->product_saleprice['product_sale_price']){
		$saletag_icon = theme('image', variable_get('pwcs_product_saleprice_icon_'.$product->product_saleprice['sale_tag_icon'], PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag'.$product->product_saleprice['sale_tag_icon'].'.png'), '', '', array('width'=>'auto'), FALSE);
		$output .= '<div class="product_element_price">';
		$output .= '<p class="price_strike_through">'.t('Regular Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</p>';
		$output .= '<p class="saleprice">'.t('Sale Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->product_saleprice['product_sale_price'].'</p>';
		$output .= '<p class="salestag">'.$saletag_icon.'</p>';
		$output .= '</div>';
	} else {
		$output .= '<div class="product_element_price">'.t('Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</div>';
	}
	$output .= '</div>';

	$output .= '<div class="product_shortdetails">'.truncate_utf8($product->teaser, 90, true, true).'</div>';

	$output .= '<div class="product_buy_button">';
	$output .= l(variable_get('pwcs_spv_addtocartlink_text', 'Add To Cart'),'addtocart/'.$product->nid, array('attributes' => array('class' => 'pwcs-link-text addtocart-link-text', 'ajax_addtocart_url' => base_path().'pwcsajax/addtocart' , 'product_id'=>$product->nid, 'product_link_id'=>$product->product_link_id, 'name' => 'product-addtocart', 'id' => 'addtocart-id-'.$product->nid, 'title' => 'Click here to add this product to your cart')));
	if(($product->one_click_buy_link) && variable_get('pwcs_sitesettings_oneclickbuy', '1')){
		$output .= ' | '.l(variable_get('pwcs_sitesettings_oneclickbuy_text', 'One Click Buy'), $product->one_click_buy_link, array('attributes' => array('class' => 'pwcs-link-text oneclickbuy-link-text', )));
	}
	$output .= '</div>';

	$output .= '</div>';

	return $output;
}

/**
 * Function to view a single product.
 * 'theme_normal'						=> t('Theme Normal3 : 260px X 260px Product Thumbnail, Fixed Width, Title, Image, Price, Add to Cart Link'),
 * Added by Rasel Kabir
 */
function theme_pwcs_spv_normal3($product, $options = array()){

	$output ='';
	//$output .= '<div class="product_box_class" id="product_box_'.$product->nid.'">';
	$output .= '<div class="product_normal3_box_class product_box_class" product_id = "'.$product->nid.'">';

  //Image
	$output .= '<div class="single_product_image">';
	$output .= '<a href="'.base_path().'node/'.$product->nid.'">';
	$image_path = pwcs_get_product_primary_image_path($product);
	$alter = '';
	$title = '';
	if(is_numeric(_pwcs_isset_imagecache_preset('spv_260x260'))){
		$output .= theme('imagecache', 'spv_260x260', $image_path, $alter, $title);
	}	else {
		$output .= theme('image', $image_path, $alter, $title, array('width'=>'260px', 'height'=>'260px'), FALSE);
	}
	$output .= '</a>';
	$output .= '</div>';

  //Title
 	$options['attributes']['title'] = $product->title;
	$options['html'] = TRUE;
	$output .= '<div class="product_title">'.l(truncate_utf8($product->title, 42, FALSE, TRUE),'node/'.$product->nid, $options).'</div>';

  //Price
	if($product->product_saleprice['product_sale_price']){
		$saletag_icon = theme('image', variable_get('pwcs_product_saleprice_icon_'.$product->product_saleprice['sale_tag_icon'], PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag'.$product->product_saleprice['sale_tag_icon'].'.png'), '', '', array('width'=>'auto'), FALSE);
		$output .= '<div class="product_element_price both-regularprice-saleprice">';
		$output .= '<p class="regularprice price_strike_through">'.t('Regular Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</p>';
		$output .= '<p class="saleprice">'.t('Sale Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->product_saleprice['product_sale_price'].'</p>';
		$output .= '<p class="salestag">'.$saletag_icon.'</p>';
		$output .= '</div>';
	} else {
		$output .= '<div class="product_element_price only-regularprice">'.t('Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</div>';
	}

  //Buttons
	$output .= '<div class="button_holder">';
	if(variable_get('pwcs_spv_moredetailslink', '1') == 1){
		$output .= l(variable_get('pwcs_spv_moredetailslink_text', 'Details'), 'node/'.$product->nid, array('attributes' => array('class' => 'pwcs-default-link-button detail-link', )));
	}
	$output .= l(variable_get('pwcs_spv_addtocartlink_text', 'Add To Cart'),'addtocart/'.$product->nid, array('attributes' => array('class' => 'pwcs-default-link-button addtocart-link-button', 'ajax_addtocart_url' => base_path().'pwcsajax/addtocart' , 'product_id'=>$product->nid, 'product_link_id'=>$product->product_link_id, 'name' => 'product-addtocart', 'id' => 'addtocart-id-'.$product->nid, 'title' => 'Click here to add this product to your cart')));
	if(($product->one_click_buy_link) && variable_get('pwcs_sitesettings_oneclickbuy', '1')){
		$output .= l(variable_get('pwcs_sitesettings_oneclickbuy_text', 'One Click Buy'), $product->one_click_buy_link, array('attributes' => array('class' => 'pwcs-default-link-button oneclickbuy-button', )));
	}
	$output .= '</div>';

	$output .= '</div>';

	return $output;
}

/**
 * Function to view a single product.
 * 'theme_wide'							=> t('Theme Wide : Liquid Width, short details, Buttons'),
 * Added by Rasel Kabir
 */
function theme_pwcs_spv_wide($product, $options = array()){

	$output ='';
	$output .= '<div class="product_wide_box_class product_box_class" product_id = "'.$product->nid.'">';

	//$output .= '<div class="product_header">'.l(truncate_utf8($product->title, 40, true, true),'node/'.$product->nid).'</div>';

  //$product->title = str_replace ("&#039;", "'", $product->title); 
  //$product->title = str_replace ("&#39;", "'", $product->title); 
  
  // Temporary Solution
	// There are some product name like > Dr. Paul&#039;s Joint Relief Super-Saver
	
 	$options['attributes']['title'] = $product->title;
	$options['html'] = TRUE;
	$output .= '<div class="product_header">'.l($product->title,'node/'.$product->nid,$options).'</div>';

	$output .= '<div class="single_product_image">';
	$output .= '<a href="'.base_path().'node/'.$product->nid.'">';
	$image_path = pwcs_get_product_primary_image_path($product);
	$alter = '';
	$title = '';
	if(is_numeric(_pwcs_isset_imagecache_preset('spv_150xAuto'))){
		$output .= theme('imagecache', 'spv_150xAuto', $image_path, $alter, $title);
	}	else {
		$output .= theme('image', $image_path, $alter, $title, array('width'=>'150px'), FALSE);
	}
	$output .= '</a>';
	$output .= '</div>';

	$output .= '<div class="info_holder">';

	if($product->product_saleprice['product_sale_price']){
		$saletag_icon = theme('image', variable_get('pwcs_product_saleprice_icon_'.$product->product_saleprice['sale_tag_icon'], PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag'.$product->product_saleprice['sale_tag_icon'].'.png'), '', '', array('width'=>'auto'), FALSE);
		$output .= '<div class="product_element_price">';
		$output .= ' <p class="price_strike_through"><span class="title">'.t('Regular Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</p>';
		$output .= ' <p class="saleprice"><span class="title">'.t('Sale Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.$product->product_saleprice['product_sale_price'];
		$output .= ' <span class="salestag">'.$saletag_icon.'</span>'.'</p>';
		$output .= '</div>';
	} else {
		$output .= '<div class="product_element_price"><span class="title">'.t('Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</div>';
	}

	$output .= '<div class="product_element"><span class="title">'.t('Category').'</span>: '.$product->view_category.'</div>';
	$output .= '<div class="product_shortdetails">'.$product->teaser.'</div>';
	$output .= '</div><!-- <div class="info_holder"> -->';

	$output .= '<div class="button_holder">';
	$output .= l(variable_get('pwcs_spv_addtocartlink_text', 'Add To Cart'),'addtocart/'.$product->nid, array('attributes' => array('class' => 'pwcs-default-link-button addtocart-link-button', 'ajax_addtocart_url' => base_path().'pwcsajax/addtocart' , 'product_id'=>$product->nid, 'product_link_id'=>$product->product_link_id, 'name' => 'product-addtocart', 'id' => 'addtocart-id-'.$product->nid, 'title' => 'Click here to add this product to your cart')));
	if(($product->one_click_buy_link) && variable_get('pwcs_sitesettings_oneclickbuy', '1')){
		$output .= l(variable_get('pwcs_sitesettings_oneclickbuy_text', 'One Click Buy'), $product->one_click_buy_link, array('attributes' => array('class' => 'pwcs-default-link-button oneclickbuy-button', )));
	}
	if(variable_get('pwcs_spv_moredetailslink', '1') == 1){
		$output .= l(variable_get('pwcs_spv_moredetailslink_text', 'Details'), 'node/'.$product->nid, array('attributes' => array('class' => 'pwcs-default-link-button detail-link', )));
	}
	$output .= '</div>';
	$output .= '<div style="clear: both;"></div>';
	$output .= '</div>';
	$output .= '<div style="clear: both;"></div>';

	return $output;
}


/**
 * Function to view a single product.
 * 'theme_wide2'							=> t('Theme Wide 2 : Liquid Width, Short details, Price, Quantity field, Add To Cart button, Details button'),
 * Added by Rasel Kabir
 */
function theme_pwcs_spv_wide2($product, $options = array()){

	$output ='';
	$output .= '<div class="product_wide2_box_class product_box_class" product_id = "'.$product->nid.'">';

	$output .= '<div class="leftcol">';
	$output .= '<div class="single_product_image">';
	$output .= '<a href="'.base_path().'node/'.$product->nid.'">';
	$image_path = pwcs_get_product_primary_image_path($product);
	$alter = '';
	$title = '';
	if(is_numeric(_pwcs_isset_imagecache_preset('spv_200xAuto'))){
		$output .= theme('imagecache', 'spv_200xAuto', $image_path, $alter, $title);
	}	else {
		$output .= theme('image', $image_path, $alter, $title, array('width'=>'200px'), FALSE);
	}
	$output .= '</a>';
	$output .= '</div>';
	if($product->product_options) {
		$output .= '<div class="productlist_attribute">';
		$output .= theme_pwcs_product_details_attributes($product);
		$output .= '</div>';
	}
	$output .= '</div>';

	$output .= '<div class="center_liquid">';
	$output .= '<div class="info_holder">';

 	$options['attributes']['title'] = $product->title;
	$options['html'] = TRUE;
	//$output .= '<div class="product_header">'.l((truncate_utf8($product->title, 40, true, true)),'node/'.$product->nid).'</div>';
	$output .= '<div class="product_header">'.l($product->title, 'node/'.$product->nid, $options).'</div>';

	//$output .= '<span class="product_element"><span class="title">'.t('Category').'</span>: '.$product->view_category.'</span>';
	$output .= '<div class="product_shortdetails">'.$product->teaser.'</div>';
	if(variable_get('pwcs_spv_moredetailslink', '1') == 1){
		$output .= '<div class="moredetailslink">' . l(variable_get('pwcs_spv_moredetailslink_text', t('More Details with Options')), 'node/'.$product->nid, array('attributes' => array('class' => 'moredetails'))) . '</div>';
	}	
	$output .= '</div>';
	$output .= '</div>';

	$output .= '<div class="button_holder">';
//	$output .= '<span class="product_element"><span class="title">'.t('Category').'</span>: '.$product->view_category.'</span>';
	if($product->product_saleprice['product_sale_price']){
		$saletag_icon = theme('image', variable_get('pwcs_product_saleprice_icon_'.$product->product_saleprice['sale_tag_icon'], PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag'.$product->product_saleprice['sale_tag_icon'].'.png'), '', '', array('width'=>'auto'), FALSE);
		$output .= '<div class="product_element_price">';
		$output .= '<p class="price_strike_through"><span class="title">'.t('Regular Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</p>';
		$output .= '<p class="saleprice"><span class="title">'.t('Sale Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.'<span id="productpriceamount_'.$product->nid.'">'.$product->product_saleprice['product_sale_price'].'</span></p>';
		$output .= '<p class="salestag">'.$saletag_icon.'</p>';
		$output .= '</div>';
	} else {
		//$output .= '<div class="product_element_price"><span class="title">'.t('Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</div>';
		$output .= '<div class="product_element_price"><span class="title">'.t('Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.'<span id="productpriceamount_'.$product->nid.'">'.$product->price.'</span></div>';
	}

	//$output .= '<div class="button_element"><span class="title">'.t('SKU').'</span>: '.'<span id="productskutext_'.$product->nid.'">'.$product->product_sku.'</span></div>';
	$output .= '<div class="button_element"><span class="title">' . t('Quantity').'</span>: '.'<input style="text-align:center;width:25px;"type="text" id="productdetails_addtocart_qty" product_id= "'.$product->nid.'" class="form-text" value="1" size="1" maxlength="3">'.'</div>';

	$output .= '<div class="button_div">';
	$output .= l(variable_get('pwcs_spv_addtocartlink_text', 'Add To Cart'),'addtocart/'.$product->nid, array('attributes' => array('class' => 'pwcs-default-link-button addtocart-link-button', 'ajax_addtocart_url' => base_path().'pwcsajax/addtocart' , 'product_id'=>$product->nid, 'addtocart_quantiy'=>1, 'product_link_id'=>$product->product_link_id, 'name' => 'product-addtocart', 'id' => 'addtocart-id-'.$product->nid, 'title' => 'Click here to add this product to your cart')));
	if(($product->one_click_buy_link) && variable_get('pwcs_sitesettings_oneclickbuy', '1')){
		$output .= l(variable_get('pwcs_sitesettings_oneclickbuy_text', 'One Click Buy'), $product->one_click_buy_link, array('attributes' => array('class' => 'pwcs-default-link-button oneclickbuy-button', )));
	}
	$output .= '</div>';
	$output .= '</div>';

	$output .= '<div style="clear: both;"></div>';
	$output .= '</div>';
//	$output .= '<div style="clear: both;"></div>';

	return $output;
}

/**
 * Function to view a single product.
 * 'theme_wide2'							=> t('Theme Wide 2 : Liquid Width, Short details, Price, Quantity field, Add To Cart button, Details button'),
 * Added by Rasel Kabir
 */
function theme_pwcs_spv_wide3($product, $options = array()){

	$output ='';
	$output .= '<div class="product_wide3_box_class product_box_class" product_id = "'.$product->nid.'">';

	$output .= '<div class="leftcol">';
	$output .= '<div class="single_product_image">';
	$output .= '<a href="'.base_path().'node/'.$product->nid.'">';
	$image_path = pwcs_get_product_primary_image_path($product);
	$alter = '';
	$title = '';
	if(is_numeric(_pwcs_isset_imagecache_preset('spv_200xAuto'))){
		$output .= theme('imagecache', 'spv_200xAuto', $image_path, $alter, $title);
	}	else {
		$output .= theme('image', $image_path, $alter, $title, array('width'=>'200px'), FALSE);
	}
	$output .= '</a>';
	$output .= '</div>';
	$output .= '</div>';

	$output .= '<div class="center_liquid">';
	$output .= '<div class="info_holder">';

 	$options['attributes']['title'] = $product->title;
	$options['html'] = TRUE;
	//$output .= '<div class="product_header">'.l((truncate_utf8($product->title, 40, true, true)),'node/'.$product->nid).'</div>';
	$output .= '<div class="product_header">'.l($product->title,'node/'.$product->nid, $options).'</div>';

	//$output .= '<span class="product_element"><span class="title">'.t('Category').'</span>: '.$product->view_category.'</span>';
	$output .= '<div class="product_shortdetails">'.$product->teaser.'</div>';

//	if(variable_get('pwcs_spv_moredetailslink', '1') == 1){
//		$output .= '<div class="moredetailslink">' . l(t('More Details with Options'), 'node/'.$product->nid, array('attributes' => array('class' => 'moredetails'))) . '</div>';
//	}	
	$output .= '</div>';
	$output .= '</div>';

	$output .= '<div class="button_holder">';
//	$output .= '<span class="product_element"><span class="title">'.t('Category').'</span>: '.$product->view_category.'</span>';

	if($product->product_saleprice['product_sale_price']){
		$saletag_icon = theme('image', variable_get('pwcs_product_saleprice_icon_'.$product->product_saleprice['sale_tag_icon'], PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag'.$product->product_saleprice['sale_tag_icon'].'.png'), '', '', array('width'=>'auto'), FALSE);
		$output .= '<div class="product_element_price">';
		$output .= '<p class="price_strike_through"><span class="title">'.t('Regular Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</p>';
		$output .= '<p class="saleprice"><span class="title">'.t('Sale Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.'<span id="productpriceamount_'.$product->nid.'">'.$product->product_saleprice['product_sale_price'].'</span></p>';
		$output .= '<p class="salestag">'.$saletag_icon.'</p>';
		$output .= '</div>';
	} else {
		$output .= '<div class="product_element_price"><span class="title">'.t('Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.'<span id="productpriceamount_'.$product->nid.'">'.$product->price.'</span></div>';
	}

	//$product_sku = '<span id="productskutext_'.$product->nid.'">'.(($product->product_sku)?$product->product_sku:'<em>'.t('No SKU').'</em>').'</span>';
	//$output .= '<div class="button_element"><span class="title">'.t('SKU').'</span>: '.$product_sku.'</div>';
	$output .= '<div class="button_element"><span class="title">' . t('Quantity').'</span>: '.'<input style="text-align:center;width:25px;"type="text" id="productdetails_addtocart_qty" product_id= "'.$product->nid.'" class="form-text" value="1" size="1" maxlength="3">'.'</div>';

	$output .= '<div class="button_div">';

	$output .= l(variable_get('pwcs_spv_addtocartlink_text', 'Add To Cart'),'addtocart/'.$product->nid, array('attributes' => array('class' => 'pwcs-default-link-button addtocart-link-button', 'ajax_addtocart_url' => base_path().'pwcsajax/addtocart' , 'product_id'=>$product->nid, 'addtocart_quantiy'=>1, 'product_link_id'=>$product->product_link_id, 'name' => 'product-addtocart', 'id' => 'addtocart-id-'.$product->nid, 'title' => 'Click here to add this product to your cart')));

	if(($product->one_click_buy_link) && variable_get('pwcs_sitesettings_oneclickbuy', '1')){
		$output .= l(variable_get('pwcs_sitesettings_oneclickbuy_text', 'One Click Buy'), $product->one_click_buy_link, array('attributes' => array('class' => 'pwcs-default-link-button oneclickbuy-button', )));
	}
	$output .= '</div>';
	$output .= '</div>';
	$output .= '<div style="clear: both;"></div>';

	if($product->product_options) {
		$output .= '<div class="productlist_attribute">';
		$output .= theme_pwcs_product_details_attributes($product);
		$output .= '</div>';
	}
	$output .= '</div>';
//	$output .= '<div style="clear: both;"></div>';

	return $output;
}

/**
 * Function to view a single product.
 * 'theme_wide'							=> t('Theme Wide : Liquid Width, short details, Buttons'),
 * Added by Rasel Kabir
 */
function theme_pwcs_spv_search($product, $options = array()){

	$output ='';
	$output .= '<div class="product_search_box_class product_box_class" product_id = "'.$product->nid.'">';

	$output .= '<div class="single_product_image">';
	$output .= '<a href="'.base_path().'node/'.$product->nid.'">';
	$image_path = pwcs_get_product_primary_image_path($product);
	$alter = '';
	$title = '';
	if(is_numeric(_pwcs_isset_imagecache_preset('spv_200xAuto'))){
		$output .= theme('imagecache', 'spv_80xAuto', $image_path, $alter, $title);
	}	else {
		$output .= theme('image', $image_path, $alter, $title, array('width'=>'80px'), FALSE);
	}
	$output .= '</a>';
	$output .= '</div>';

	$output .= '<div class="info_holder">';
 	$options['attributes']['title'] = $product->title;
	$options['html'] = TRUE;
	$output .= '<div class="product_header">'.l(truncate_utf8($product->title, 40, true, true),'node/'.$product->nid, $options ).'</div>';
	$output .= '<div class="product_element">';

	if($product->product_saleprice['product_sale_price']){
		$saletag_icon = theme('image', variable_get('pwcs_product_saleprice_icon_'.$product->product_saleprice['sale_tag_icon'], PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag'.$product->product_saleprice['sale_tag_icon'].'.png'), '', '', array('width'=>'auto'), FALSE);
		$output .= '<span class="title">'.t('Price').'</span>: '.'<span class="price_strike_through">'.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.'</span>'.' <span>'.PWCS_BASIC_CURRENCY_SYMBOL.$product->product_saleprice['product_sale_price'].'</span>'.' <span class="salestag">'.$saletag_icon.'</span>'.' | ';
	} else {
		$output .= '<span class="title">'.t('Price').'</span>: '.PWCS_BASIC_CURRENCY_SYMBOL.$product->price.' | ';
	}

	$output .= '<span class="title">'.t('SKU').'</span>: '.$product->product_sku.' | ';
	$output .= '<span class="title">'.t('Category').'</span>: '.$product->view_category.'';
	$output .= '</div>';
	$output .= '<div class="product_shortdetails">'.$product->teaser.'</div>';
	$output .= '</div><!-- <div class="info_holder"> -->';
	$output .= '<div style="clear: both;"></div>';
	$output .= '</div>';
	$output .= '<div style="clear: both;"></div>';

	return $output;
}
