<?php
/* $Id: product node pwcs_product.php,v 1.0 2010/04/30 Rasel Kabir Shuvro <shuvro.mist@gmail.com> Exp $ */

/**
 * INITIATE CART INFO
 * @return update the $_SESSION variable for site use
 * Added by Rasel Kabir
 */
function pwcs_get_cart_info(){

	if(isset($_SESSION['pwcs_cart']['pwc_order_temp_id']) && isset($_SESSION['pwcs_cart']['pwc_order_token'])) {
		$cartinfo_response = pwcs_api_cartinfo($_SESSION['pwcs_cart']['pwc_order_temp_id']);
		
		if (module_exists('xml_parser')){
			$cartinfo_response = xml_parser_string_to_array($cartinfo_response);

			if(!isset($cartinfo_response['ordertempid'])) {
				unset($_SESSION['pwcs_cart']);
			} else {
				$cart_items_updated = array();
				foreach($cartinfo_response['products'] as $key => $cartitem) {
					//$existing = $_SESSION['pwcs_cart']['cart_items'][$cartitem['orderitemid']];
					//$_SESSION['pwcs_cart']['cart_items'][$cartitem['orderitemid']] = array_merge($existing, $cartitem);
					$existing = $_SESSION['pwcs_cart']['cart_items'][$cartitem['orderitemid']];
					$cart_items_updated[$cartitem['orderitemid']] = array_merge($existing, $cartitem);
				}
				unset($_SESSION['pwcs_cart']['cart_items']);
				$_SESSION['pwcs_cart']['cart_items'] = $cart_items_updated;
			}
		}else{
			unset($_SESSION['pwcs_cart']);
		}
	} else {
		unset($_SESSION['pwcs_cart']);
	}
}

/**
 * Cart Item Count from $_SESSION
 * @return ARRAY  //default : array('amount' => 0, 'totalqty' => 0)
 * Added by Rasel Kabir
 */
function pwcs_cart_item_count() {

	$count = array('amount' => 0, 'totalqty' => 0);
	if(isset($_SESSION['pwcs_cart']['cart_items'])) {
		// Number of Product
		$count['amount'] = count($_SESSION['pwcs_cart']['cart_items']);
		// Sum of Total Product Quantity
		$totalqty = 0;
		foreach($_SESSION['pwcs_cart']['cart_items'] as $k=>$item){
			$totalqty = $totalqty + $item['quantity'];
		}
		$count['totalqty'] = $totalqty;
	}
	return $count;
}

/**
 * BLOCK for CART link
 * @return image/html output of cart block
 * Added by Rasel Kabir
 */
function pwcs_cart_block(){

	//echo '<pre>'.print_r($_SESSION, true).'</pre>';
	pwcs_get_cart_info();
	$cart_count = pwcs_cart_item_count();

	$output = '';
	//$output .= '<div style="text-align:right;">';
	$output .= '<div class="cartlinkblock">';
	$cart_link_text = t(variable_get('pwcs_cart_link_title', 'Cart: total !total_quantity items for !amount products'), array(
						'!amount' => '<span id="cartlink_cartamount" class="carttotalitemcount">'.$cart_count['amount'].'</span>',
						'!total_quantity' => '<span id="cartlink_carttotalqty" class="carttotalitemcount_totalqty">'.$cart_count['totalqty'].'</span>'
					  ));
	$output .= l($cart_link_text, 'cartinfo', array('html'=> TRUE,'attributes' => array('class' => 'pwcs-default-link-button cart_link', 'ajax_href' => base_path().'pwcsajax/cartinfo' , 'checkouturl' => base_path().'checkouturl' , 'id' => 'cart-block-cart-link' , 'title' => 'Click here to view cart')));
	$output .= '<div id="cart-block-cart-pane" title="Cart"></div>';
	$output .= '</div>';

	return $output;
}

/**
 * Function for menu item 'checkouturl'
 * Redirect to PWC CORE checkout page
 * Added by Rasel Kabir
 */
function pwcs_checkouturl() {

	//print_r ($_SESSION); exit;

	if(isset($_SESSION['pwcs_cart']['pwc_order_temp_id']) && isset($_SESSION['pwcs_cart']['pwc_order_token'])) {
		if($_SESSION['pwcs_cart']['redirecturl']) {
			$redirecturl = $_SESSION['pwcs_cart']['redirecturl'];
			unset($_SESSION['pwcs_cart']['redirecturl']);
			drupal_goto ($redirecturl);
		} else {
			$checkout_url = PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_checkout', 'myCheckout/<#order tempid#>/<#token#>.html');
			$trans = array("<#order tempid#>" => $_SESSION['pwcs_cart']['pwc_order_temp_id'], "<#token#>" => $_SESSION['pwcs_cart']['pwc_order_token']);
			$checkout_url = strtr($checkout_url, $trans);
			drupal_goto ($checkout_url);
		}
	}
	else {
		drupal_goto('<front>');
	}
}

/**
 * Function for menu item 'cartinfo'
 * @return html output of cart details (when JS not enabled)
 * Added by Rasel Kabir
 */
function pwcs_cartinfo(){

	drupal_set_title('Cart');
	$output = '';
	$output .= pwcs_cart_list();

//	if(isset($_SESSION['pwcs_cart']['last_added_product_nid'])) {
//		$output .= pwcs_relatedproducts_list($_SESSION['pwcs_cart']['last_added_product_nid']);
//	}

	$output .= '<div style="clear:both; margin-top:20px;"></div>';
	$output .= l(t('Continue Shopping'), '<front>' , array('attributes' => array('class' => 'pwcs-default-link-button', 'id' => 'cart-continue-shopping')));
	$output .= l(t('Check Out'), 'checkouturl' , array('attributes' => array('class' => 'pwcs-default-link-button', 'id' => 'cart-continue-shopping')));
	$output .= '<div style="clear:both;"></div>';
	return $output;
}

/**
 * Function for menu item 'ajax_cartinfo'
 * @return html output of cart details in a dialog box (when JS enabled)
 * Added by Rasel Kabir
 */
function pwcs_ajax_cartinfo(){

	//pwcs_debug_file_write($_SESSION);
	$output = '';
	$output .= pwcs_cart_list();
//	if(isset($_SESSION['pwcs_cart']['last_added_product_nid'])) {
//		$output .= pwcs_relatedproducts_list($_SESSION['pwcs_cart']['last_added_product_nid']);
//	}
	echo $output;
}

/**
 * Function pwcs_cart_list returns/echo the cart output
 * @return output cart list in a table
 * Added by Rasel Kabir
 */
function pwcs_cart_list(){

	//pwcs_debug_file_write('SESSION :');
	pwcs_debug_file_write($_SESSION);
	
	if(!isset($_SESSION['pwcs_cart'])){
		return '<div class="message ui-state-highlight">'.t('Sorry! the cart is empty.').'</div>';
	}

	//$grand_total = 0;
	if(isset($_SESSION['pwcs_cart']['pwc_order_temp_id']) && isset($_SESSION['pwcs_cart']['pwc_order_token']))
	{
		$pwc_order_temp_id = $_SESSION['pwcs_cart']['pwc_order_temp_id'];
		$cartinfo_response = pwcs_api_cartinfo($pwc_order_temp_id);
		pwcs_debug_file_write($cartinfo_response);

		if (module_exists('xml_parser')){
			//$cartinfo_response = xml_clean($cartinfo_response);
			$cartinfo_response = xml_parser_string_to_array($cartinfo_response);
			pwcs_debug_file_write($cartinfo_response);
		}else
			return false;

		if(is_array($cartinfo_response ['products']) && !empty($cartinfo_response ['products'])){
			if(variable_get('pwcs_sitesettings_cart_theme', '1') == '1')
				return theme_pwcs_cartlist_row_table($cartinfo_response);
			else if (variable_get('pwcs_sitesettings_cart_theme', '1') == '2')
				return theme_pwcs_cartlist_column_table($cartinfo_response);
		} else {
			unset($_SESSION['pwcs_cart']);
			return '<div class="message error ui-state-error">'.t('Cart Error! Please try once again.').'</div>';
		}

	} else {
		return '<div>'.t('Cart Error!').'</div>';
	}

}

/**
 * Theme for cart output (row based)
 * @return output html
 * Added by Rasel Kabir <shuvro.mist@gmail.com>
 */
function theme_pwcs_cartlist_row_table($cartinfo_response = NULL){

		$output = '';
		$output .= '<div id="pwcs_temp_orders" class="pwcs_table">';
		$header = array(
			'',
			t('Product'),
			t('Qty'),
			array('style'=>'text-align:right;', 'data'=>t('Price')),
			array('style'=>'text-align:right;', 'data'=>t('Total Price')),
			'',
		);

		//$count = count($cartinfo_response ['products']);
		$count = count($_SESSION['pwcs_cart']['cart_items']);
		$output .= '<div class="total_count" id="total_count_id"><span id="carttotalitemcount">'.$count.'</span> '.t('item(s) in your cart').'</div>';

		$row = array();
		foreach($cartinfo_response ['products'] as $key => $cart_item)
		{
			$col = array();
			$col[] = theme('image', $cart_item['thumbnail'], '', NULL, array('width'=>'80px'), FALSE);
			$col[] = $cart_item['name'];
			$col[] = $cart_item['quantity'];

			if($cartinfo_response['currency'] == 'USD')
				$col[] = array('style'=>'text-align:right; padding-right:6px;', 'data'=>PWCS_BASIC_CURRENCY_SYMBOL.$cart_item['price']);
			else
				$col[] = array('style'=>'text-align:right; padding-right:6px;', 'data'=>$cart_item['price']);

			if($cartinfo_response['currency'] == 'USD')
				$col[] = array('style'=>'text-align:right; padding-right:6px;', 'data'=>PWCS_BASIC_CURRENCY_SYMBOL.$cart_item['totalprice']);
			else
				$col[] = array('style'=>'text-align:right; padding-right:6px;', 'data'=>$cart_item['totalprice'].' '. $cartinfo_response['currency']);

			$removelink = '('.l(t('Remove'),'removefromcart/'.$cart_item['orderitemid'], array('attributes' => array('class' => 'removefromcart-link', 'ajax_removefromcart_url' => base_path().'pwcsajax/removefromcart' , 'name' => 'product-removefromcart', 'onclick' => 'pwcs_js_removefromcart(var pwc_orderitemid)', 'pwc_orderitemid' => $cart_item['orderitemid'] , 'id' => 'removefromcart-id-'.$cart_item['orderitemid'], 'title' => 'Click here to remove this product from your cart')));
			$removelink .= ' | '.l(t('Edit'),'editcartitem/'.$cart_item['orderitemid'].'/'.$cart_item['productrowid']).')';
			$removelink .= '<div class="removestatus"></div>';
			$col[] = array('style'=>'width:60px;text-align:center;', 'class'=>'removelink', 'data'=>$removelink);

			$row[] = array('data'=>$col, 'id'=>'cartitem-'.$cart_item['orderitemid']);
		}

		// Table row for Total
		$output .= theme('table', $header, $row, array('width'=>'100%'));
		$output .= '<div class="total_amount" id="total_amount_id">'.t('Total').' : $ <span id="carttotalprice">'.$cartinfo_response ['ordertotal'].'</span></div>';
		$output .= '<div style="clear:both;"></div>';
		$output .= '</div>';
		return $output;

}

/**
 * Theme for cart output (column based)
 * @return output html
 * Added by Rasel Kabir <shuvro.mist@gmail.com>
 */
function theme_pwcs_cartlist_column_table($cartinfo_response = NULL){

		$item_amount = variable_get('pwcs_sitesettings_cart_itemamount', '4');
		$scroll_amount = variable_get('pwcs_sitesettings_cart_scrollamount', '1');
		$auto_scroll = variable_get('pwcs_sitesettings_cart_autoscroll', '0');

		$output = '';
		$output .= '<div id="pwcs_temp_orders" class="pwcs_item_list">';

		$js = '$(function() {
	    	    $("#mycarousel").jcarousel({ visible: '.$item_amount.', scroll: '.$scroll_amount.', auto: '.$auto_scroll.' });
			     });';
		$output .= '<script type="text/javascript">'.$js.'</script>';
		//drupal_add_js($js, 'inline');

		//$count = count($_SESSION['pwcs_cart']['cart_items']);
		//$output .= '<div class="total_count" id="total_count_id"><span id="carttotalitemcount_'.$_SESSION['pwcs_cart']['pwc_order_temp_id'].'" class="carttotalitemcount">'.$count.'</span> '.t('item(s) in your cart').'</div>';

		$cart_count = pwcs_cart_item_count();
		$output .= t('Total !amount products and !total_quantity items in your cart', array(
						'!amount' => '<span id="carttotalitemcount_'.$_SESSION['pwcs_cart']['pwc_order_temp_id'].'" class="carttotalitemcount">'.$cart_count['amount'].'</span>',
						'!total_quantity' => '<span id="carttotalitemcount_totalqty_'.$_SESSION['pwcs_cart']['pwc_order_temp_id'].'" class="carttotalitemcount_totalqty">'.$cart_count['totalqty'].'</span>'
					));

		$output .= '<ul id="mycarousel" class="jcarousel-skin-tango">';
		foreach($cartinfo_response ['products'] as $key => $cart_item) {
			
			$product_thumbnail = $cart_item['thumbnail'];
			$product_details = pwcs_product_details_pwc_product_row_id($cart_item['productrowid']); //needed only for product primary image
			$product_thumbnail = pwcs_get_product_primary_image_path($product_details);
			
			if($_SESSION['pwcs_cart']['last_orderitemid'] == $cart_item['orderitemid'])
				$classes = 'last-added-item';
			else
				$classes = '';
			
			$output .= '<li id="cartitem-'.$cart_item['orderitemid'].'" class="'.$classes.'">';
			$output .= '<div class="cartitem-inner">';

			$output .= '<div class="tmb_image_holder">';
			if(is_numeric(_pwcs_isset_imagecache_preset('spv_80xAuto'))){
		 		$output .= theme('imagecache', 'spv_80xAuto', $product_thumbnail, '', '');
			}	else {
				$output .= theme('image', $product_thumbnail, '', NULL, array('width'=>'80px'), FALSE);
			}
			$output .= '</div>';
			$output .= '<div class="info-holder">';
			$output .= '<b>'.truncate_utf8($cart_item['name'], 15, FALSE, TRUE).'</b>';
			$output .= '<p>'.t('Quantity').': '.$cart_item['quantity'].'</p>';

			//print_r ($cart_item);
			//$sale_price = pwcs_get_sale_price_info($cart_item['product_nid']);
			//print_r ($sale_price);
			
			if(isset($product_details->product_saleprice['product_sale_price'])){
				$saletag_icon = theme('image', variable_get('pwcs_product_saleprice_icon_'.$product_details->product_saleprice['sale_tag_icon'], PWCS_MODULE_IMAGE.'saleprice_icons'.'/saletag'.$product_details->product_saleprice['sale_tag_icon'].'.png'), '', '', array('width'=>'auto'), FALSE);
				$output .= '<p class="price_strike_through">'.t('Regular Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$cart_item['price'].'</p>';
				$output .= '<p class="saleprice">'.t('Sale Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$product_details->product_saleprice['product_sale_price'].'</p>';
				$output .= '<p class="salestag">'.$saletag_icon.'</p>';
			} else {
				$output .= '<p>'.t('Unit Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$cart_item['price'].'</p>';
			}
			$output .= '<p>'.t('Total Price').': '.PWCS_BASIC_CURRENCY_SYMBOL.$cart_item['totalprice'].'</p>';

			if($_SESSION['pwcs_cart']['cart_items'][$cart_item['orderitemid']]['csv_options']){
				$option_text = theme_pwcs_attributes_csv_to_textval($_SESSION['pwcs_cart']['cart_items'][$cart_item['orderitemid']]['csv_options'], '<br>');
				$output .= '<p>'.$option_text.'</p>';
			}

			$output .= '</div>';

			$output .= '<div class="cartitem-operation">';
			$removelink = ''.l(t('Remove'),'removefromcart/'.$cart_item['orderitemid'], array('attributes' => array('class' => 'removefromcart-link', 'ajax_removefromcart_url' => base_path().'pwcsajax/removefromcart' , 'name' => 'product-removefromcart', 'pwc_orderitemid' => $cart_item['orderitemid'] , 'id' => 'removefromcart-id-'.$cart_item['orderitemid'], 'title' => 'Click here to remove this product from your cart'))).'';
			//$product_nid = db_result(db_query('SELECT nid FROM {pwcs_product} WHERE pwc_product_row_id = '.$cart_item['productrowid']));
			$output .= '<div class="removelink" style="font-weight:bold;">'.$removelink.' | '.l(t('Edit'),'editcartitem/'.$cart_item['orderitemid'].'/'.$cart_item['productrowid']).'</div>';
			$output .= '<div class="removestatus"></div>';
			$output .= '</div>';

			$output .= '</div>';
			$output .= '</li>';
		}
		$output .= '</ul>';
		$output .= '<div style="clear:both;"></div>';

		//Total
		$output .= '<div class="total_amount" id="total_amount_id">'.t('Total').' : $ <span id="carttotalprice">'.$cartinfo_response ['ordertotal'].'</span></div>';

		$output .= '</div>';

		return $output;

}

/**
 * when JS not enabled.
 * Added by Rasel Kabir
 */
function pwcs_addtocart($product_id, $addtocart_quantiy = 1, $csv_options = NULL){
	$product_link_id = node_load($product_id)->product_link_id;

	if( pwcs_addtocart_action($product_id, $product_link_id, $addtocart_quantiy, NULL, $csv_options) == 'done'){
		drupal_set_message('New Product added to cart');
		drupal_goto('cartinfo');
	}
}

/**
 * when JS enabled and AJAX called
 * Added by Rasel Kabir
 */
function pwcs_ajax_addtocart(){

	$product_id = $_REQUEST['product_id'];
	$product_link_id = $_REQUEST['product_link_id'];
	$addtocart_quantiy = ($_REQUEST['addtocart_quantiy']!=NULL)?$_REQUEST['addtocart_quantiy']:1;
	$csv_options = ($_REQUEST['csv_options']!=NULL)?$_REQUEST['csv_options']:'';

	$writedata = 'product_id:' . $product_id . ' - product_link_id:' . $product_link_id . ' - addtocart_quantiy:' . $addtocart_quantiy . ' - csv_options:' . $csv_options;
	pwcs_debug_file_write($writedata);
	echo pwcs_addtocart_action($product_id, $product_link_id, $addtocart_quantiy, 1, $csv_options);
}

/**
 * for both JS enabled and not enabled
 * Added by Rasel Kabir
 */
function pwcs_addtocart_action($product_id=NULL, $product_link_id=NULL, $addtocart_quantiy=1, $is_ajax = NULL, $csv_options = NULL){

	// SESSION - CHECK TEMP ORDER IF EXISTS - START
	$second_time_SESSION = 0;
	if(isset($_SESSION['pwcs_cart']['pwc_order_temp_id']) && isset($_SESSION['pwcs_cart']['pwc_order_token'])){
		$second_time_SESSION = 1;
		$pwc_order_temp_id = $_SESSION['pwcs_cart']['pwc_order_temp_id'];
		$pwc_order_token = $_SESSION['pwcs_cart']['pwc_order_token'];
	}
	// SESSION - CHECK TEMP ORDER IF EXISTS - END

	pwcs_debug_file_write('current session');
	pwcs_debug_file_write($_SESSION);

	// CALL API - START
	if($second_time_SESSION == 1){ // 2nd time
		
		/////////////////////
		$duplicate_cart_item = 0;
		$duplicate_cart_item_values = array();
		foreach($_SESSION['pwcs_cart']['cart_items'] as $k=>$cartitem) {
			if($cartitem['product_nid'] == $product_id){
				if($cartitem['csv_options'] == $csv_options){
					$duplicate_cart_item = 1;
					$duplicate_cart_item_values = $cartitem;
				}
			}
		}

		pwcs_debug_file_write('duplicate_cart_item : '.$duplicate_cart_item);
		pwcs_debug_file_write('duplicate_cart_item_values');
		pwcs_debug_file_write($duplicate_cart_item_values);

		if($duplicate_cart_item == 1) {
			
			pwcs_debug_file_write('addtocart_quantiy (old) : '.$addtocart_quantiy);
			$old_amount = $duplicate_cart_item_values['quantity'];
			pwcs_debug_file_write('old_amount : '.$old_amount);

			if(pwcs_removefromcart_action($duplicate_cart_item_values['orderitemid']) == 'done') {
				pwcs_debug_file_write($duplicate_cart_item_values['orderitemid'].' removed from cart');
				//pwcs_debug_file_write($duplicate_cart_item_values);
				
				if($_SESSION['pwcs_cart']['editcartitem']['product_nid'] != $product_id) {
					$addtocart_quantiy = $old_amount + $addtocart_quantiy;
				} 
				// else for EDIT option it will not add old quantity
			}
		}

		pwcs_debug_file_write('addtocart_quantiy (new) : '.$addtocart_quantiy);
		pwcs_debug_file_write('duplicate_cart_item_values');
		//pwcs_debug_file_write($duplicate_cart_item_values);

		/////////////////////

		$addtocart_response = pwcs_api_addtocart($product_link_id, $addtocart_quantiy, $csv_options, $pwc_order_temp_id, $pwc_order_token);
		if (module_exists('xml_parser')) {
			$addtocart_response = xml_clean($addtocart_response);
			$addtocart_response = xml_parser_string_to_array($addtocart_response);
		}else
			return false;

		
		if(isset($addtocart_response['order']['ordertempid'])) {
			$orderitemid = $addtocart_response['order']['orderitemid'];

			if(isset($_SESSION['pwcs_cart']['editcartitem']['orderitemid']) && ($product_id == $_SESSION['pwcs_cart']['editcartitem']['product_nid'])) {
				pwcs_removefromcart_action($_SESSION['pwcs_cart']['editcartitem']['orderitemid'], NULL);
			}
		
			$_SESSION['pwcs_cart']['last_modfied_time'] = time();
			$_SESSION['pwcs_cart']['last_added_product_nid'] = $product_id;
			$_SESSION['pwcs_cart']['last_orderitemid'] = $orderitemid;

			$order_session = session_name();
			$_SESSION['pwcs_cart']['cart_items'][$orderitemid]['orderitemid'] = $orderitemid;
			$_SESSION['pwcs_cart']['cart_items'][$orderitemid]['product_nid'] = $product_id;
			$_SESSION['pwcs_cart']['cart_items'][$orderitemid]['temp_order_session'] = $order_session;
			$_SESSION['pwcs_cart']['cart_items'][$orderitemid]['quantity'] = $addtocart_quantiy;
			$_SESSION['pwcs_cart']['cart_items'][$orderitemid]['csv_options'] = $csv_options;
			
			// TODO Need to insert data into temp table, its currently off now due to some technical issue
			//db_query("INSERT INTO {pwcs_temp_order} (`temp_order_session`,`pwc_order_temp_id`,`pwc_order_token`,`product_nid`) VALUES ('%s', %d, '%s', %d)",
			//	$order_session, $pwc_order_temp_id, $pwc_order_token, $product_id
			//);
		} 

		unset($_SESSION['pwcs_cart']['editcartitem']);

	} else { // 1st time
		
		$addtocart_response = pwcs_api_addtocart($product_link_id, $addtocart_quantiy, $csv_options);
		//$addtocart_response_initxml = $addtocart_response.'<br> product_link_id:'.$product_link_id.'<br> addtocart_quantiy:'.$addtocart_quantiy;
		pwcs_debug_file_write($addtocart_response, false);
		if (module_exists('xml_parser')){
			$addtocart_response = xml_clean($addtocart_response);
			$addtocart_response = xml_parser_string_to_array($addtocart_response);
			pwcs_debug_file_write($addtocart_response);
		}else
			return false;

		if(isset($addtocart_response['order']['ordertempid'])) {

			$orderitemid = $addtocart_response['order']['orderitemid'];
	
			$_SESSION['pwcs_cart']['pwc_order_temp_id'] = $addtocart_response['order']['ordertempid'];
			$_SESSION['pwcs_cart']['pwc_order_token'] = $addtocart_response['order']['token'];
			$_SESSION['pwcs_cart']['init_time'] = time();
			$_SESSION['pwcs_cart']['last_modfied_time'] = time();
			$_SESSION['pwcs_cart']['last_added_product_nid'] = $product_id;
			$_SESSION['pwcs_cart']['last_orderitemid'] = $orderitemid;
			if($addtocart_response['order']['redirecturl']){
    	  $_SESSION['pwcs_cart']['redirecturl'] = $addtocart_response['order']['redirecturl'];
    	}
	
			$order_session = session_name();
			$_SESSION['pwcs_cart']['cart_items'][$orderitemid]['orderitemid'] = $orderitemid;
			$_SESSION['pwcs_cart']['cart_items'][$orderitemid]['product_nid'] = $product_id;
			$_SESSION['pwcs_cart']['cart_items'][$orderitemid]['temp_order_session'] = $order_session;
			$_SESSION['pwcs_cart']['cart_items'][$orderitemid]['quantity'] = 1;
			$_SESSION['pwcs_cart']['cart_items'][$orderitemid]['csv_options'] = $csv_options;

			// TODO Need to insert data into temp table, its currently off now due to some technical issue
			//db_query("INSERT INTO {pwcs_temp_order} (`temp_order_session`,`pwc_order_temp_id`,`pwc_order_token`,`product_nid`) VALUES ('%s', %d, '%s', %d)",
			//	$order_session, $addtocart_response['order']['ordertempid'], $addtocart_response['order']['token'], $product_id
			//);
		} 
	}
	// CALL API - END

	// RETURN TO LANDING PAGE - START
	//$response = 'not ajax calling '.$product_id;
	if($is_ajax == 1) {

		if(isset($addtocart_response['order']['ordertempid'])) {

			$cartinfo = pwcs_cart_list();
			//$cartinfo .= pwcs_relatedproducts_list($product_id);
			$cartcount = pwcs_cart_item_count();
			$ret = array(
			  'response' => 'added',
			  'status' => $addtocart_response['order']['ordertempid'],
			  'cartinfo' => $cartinfo, // . print_r($addtocart_response_initxml, true),
			  'cartamount' => $cartcount['amount'],
			  'cartamount_totalqty' => $cartcount['totalqty'],
			);
		} else {
			$ret = array(
			  'response' => 'error',
			  'status' => $addtocart_response['error'],
			  'error_msgs' => '<div class="message error ui-state-error">'.t('Cannot Connect to PWC CORE!').'</div>'
			);
		}
  	drupal_json($ret);
  	exit;

	} else {
		if(isset($addtocart_response['error'])) {
			return 'error';
		} else {
			return 'done';
		}
	}
	// RETURN TO LANDING PAGE - END


}


/**
 * when JS not enabled.
 * Added by Rasel Kabir
 */
function pwcs_removefromcart($pwc_orderitemid){
	if( pwcs_removefromcart_action($pwc_orderitemid) == 'done'){
		drupal_set_message('Item removed from cart');
		drupal_goto('cartinfo');
	} else {
		drupal_set_message('Item cant be removed','error');
		drupal_goto('cartinfo');
	}
}

/**
 * when JS enabled and AJAX called
 * Added by Rasel Kabir
 */
function pwcs_ajax_removefromcart(){
	$pwc_orderitemid = $_REQUEST['pwc_orderitemid'];
	$is_ajax = $_REQUEST['ajax'];
	echo pwcs_removefromcart_action($pwc_orderitemid, $is_ajax);
}

/**
 * for both JS enabled and not enabled
 * Added by Rasel Kabir
 */
function pwcs_removefromcart_action($pwc_orderitemid=NULL, $is_ajax = NULL){

	// CALL API - START
	$removefromcart_response = pwcs_api_removefromcart($pwc_orderitemid);
	if (module_exists('xml_parser')) {
		$removefromcart_response = xml_clean($removefromcart_response);
		$removefromcart_response = xml_parser_string_to_array($removefromcart_response);
	}else
		return false;

	//pwcs_debug_file_write($removefromcart_response); // array write in a file

	if($removefromcart_response['order']['status'] == 'removed') {
			unset($_SESSION['pwcs_cart']['cart_items'][$pwc_orderitemid]);
			// TODO Need to update data into temp table, its currently off now due to some technical issue
			//db_query("DELETE FROM {pwcs_temp_order} WHERE product_nid = %d", $product_id );
	}
	// CALL API - END

	// RETURN TO LANDING PAGE - START
	//$response = 'not ajax calling '.$product_id;
	//$output = '';
	//$output .= pwcs_cart_list();
	//return $output;

	if($is_ajax == 1) {
		if(isset($removefromcart_response['error'])) {
	    $ret = array(
	      'response' => 'error',
	      'status' => $removefromcart_response['error'],
	    );
		} else {
			
		$cartcount = pwcs_cart_item_count();
	    $ret = array(
	      'response' => 'removed',
	      'status' => $removefromcart_response['order']['status'],
	      'totalprice' => $removefromcart_response['order']['totalprice'],
	      'cart_amount' => $cartcount['amount'],
	      'cart_totalqty' => $cartcount['totalqty'],
	    );
		}
  	drupal_json($ret);
  	exit;
	}
	else {
		if(isset($removefromcart_response['error'])) {
			return 'error';
		} else {
			return 'done';
		}
	}
	// RETURN TO LANDING PAGE - END
}

function pwcs_editcartitem($orderitemid = NULL, $productrowid = NULL){
	$_SESSION['pwcs_cart']['editcartitem']['orderitemid'] = $orderitemid;
	$product_nid = db_result(db_query('SELECT nid FROM {pwcs_product} WHERE pwc_product_row_id = '.$productrowid));
	$_SESSION['pwcs_cart']['editcartitem']['product_nid'] = $product_nid;
	drupal_goto('node/'.$product_nid);
}

/**
 * Rest Cart - Clean all item from cart
 * Added by Rasel Kabir
 */
function pwcs_resetcart(){
	if(isset($_SESSION['pwcs_cart'])){
		//$_SESSION['pwcs_cart_backup'] = $_SESSION['pwcs_cart'];
		unset($_SESSION['pwcs_cart_backup']);
		unset($_SESSION['pwcs_cart']);
	}
	drupal_goto('<front>');
}
