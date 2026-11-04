<?php
/* $Id: API LIBRARY functions pwcs_api_lib.php,v 1.0 2010/08/24 Rasel Kabir Shuvro <shuvro.mist@gmail.com> Exp $ */

/**
 * MAIN SYNC FUNCTION
 * THIS IS A MASS SYNC, WHICH SYNC BASCI SETTINGS, ALL PRODUCTS, ALL CATEGORIES, MANUFACTURERS, OPTIONS, ATTRIBUTES
 * by Rasel Kabir, AR Zerin
 */
function pwcs_fetchMerchantData($merchant_id, $api_key = '')
{
	set_time_limit (0);
	
	
	global $user, $finalmsgs;
	$finalmsgs = array();

	pwcs_debug_file_write('--- Start All Sync ---');

	//unset($_SESSION["messages"]);
    if (empty($merchant_id))
    {
		drupal_set_message(t('Please Make Sure You Have Provided Merchant ID.'), 'error');
		drupal_goto('siteadmin/sync-settings');
    }
    if (empty($api_key))
    {
		drupal_set_message(t('Please Make Sure You Have Provided API Key Signature.'), 'error');
		drupal_goto('siteadmin/sync-settings');
    }

	pwcs_debug_file_write('Merchant ID:'.$merchant_id.' - Merchant Security key:'.$api_key);

	// -------------------- Basic Settings --------------------------
	if (variable_get('pwcs_load_basic_settings_data', '1') == 1)
	{
		syncBasicSettings($merchant_id, $api_key);
	}
	// -------------------- Basic Settings End Sync --------------------------

	// -------------------- Categories --------------------------
	if (variable_get('pwcs_load_category_data', '1') == 1)
	{
		syncCategory($merchant_id, $api_key);
		pwcs_cleanListingProblem();
	}
	
	// -------------------- Categories End Sync --------------------------


		// -------------------- Listing --------------------------
	if (variable_get('pwcs_load_listing_data', '1') == 1)
	{
		syncListing($merchant_id, $api_key);
		pwcs_cleanListingProblem();
	}
	// -------------------- Listings End Sync --------------------------

	// -------------------- Products --------------------------
	if (variable_get('pwcs_load_product_data', '1') == 1)
	{
		// -------------------- Products Sync All Products --------------------------
		if (variable_get('pwcs_load_product_data_options', '1') == 1){
			syncProducts($merchant_id, $api_key);
		}
		// -------------------- Products Sync Update Only --------------------------
		if (variable_get('pwcs_load_product_data_options', '1') == 2){
			syncProducts2($merchant_id, $api_key);
		}
	}
	// -------------------- Products  End Sync--------------------------


	// -------------------- Products Sync Update Only - End Sync --------------------------
	
	// -------------------- Attributes Group & Attributes Information --------------------------
	if (variable_get('pwcs_load_attribute_data', '1') == 1)
	{
		syncAttributes($merchant_id, $api_key);
	}

	// -------------------- Importing Manufacture Data --------------------------
	if (variable_get('pwcs_load_manufacturer_data', '1') == 1)
	{
		syncManufacturer($merchant_id, $api_key);

	}

	// Final messages
	pwcs_debug_file_write($_SESSION, true);

	unset($_SESSION["messages"]);
	foreach ($finalmsgs as $k=>$message){
		drupal_set_message($message, 'status');
	}
	if (variable_get('pwcs_load_listing_data', '1') == 1){
		syncProductsWithListing();
	}

	syncProductsWithListing();
	pwcs_update_listing_table();
	pwcs_bulk_clean_listing_table();
	
	//return 'done';
	unset($_SESSION['pwcs_cart']);
	unset($_SESSION['pwcs_cart_backup']);
	pwcs_debug_file_write('--- End All Sync ---');

	pwcs_flush_cache();
	//cache_clear_all();
	//drupal_flush_all_caches();

}


/**
 * CURL calling for categoryList
 * Added by Rasel Kabir
 */
function pwcs_getBasicSettings($merchant_id, $api_key='')
{
	$target_url = PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_basicSettings', 'testapi/basicSettings.html');
	pwcs_debug_file_write('Basic settings URL :'.$target_url);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'merchantid=' . urlencode($merchant_id);
	$request.= '&api_signature=' . urlencode($api_key);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch))
	{
		print "<b>Sorry, We could Not Connect to PremiumWebCart</b>";
	}
	else
	{
		curl_close($ch);
	}

	return $response;
}

/**
 * CURL calling for categoryList
 * Added by Rasel Kabir
 */
function pwcs_getParsedCategory($merchant_id, $api_key='')
{
	$target_url =  PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_categorylist', 'testapi/categoryList.html');
	pwcs_debug_file_write('categoryList URL :'.$target_url);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'merchantid=' . urlencode($merchant_id);
	$request.= '&api_signature=' . urlencode($api_key);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch))
	{
		print "<b>Sorry, We could Not Connect to PremiumWebCart</b>";
	}
	else
	{
		curl_close($ch);
	}

	return $response;
}

/**
 * CURL calling for productList
 * Added by Rasel Kabir
 */
function pwcs_getParsedProduct($merchant_id, $api_key='')
{
	$target_url =  PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_productlist', 'testapi/productList.html');
	pwcs_debug_file_write('productList URL :'.$target_url);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'merchantid='    . urlencode($merchant_id);
	$request.= '&api_signature=' . urlencode($api_key);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch))
	{
//		print "<pre>\n";
//		print_r(curl_getinfo($ch));  // get error info
//		echo 'CURL ERROR NO : ', curl_errno($ch);
//		echo '<br>CURL ERROR: ', curl_error($ch);
//		print "</pre>\n";
		print "<b>Sorry, We could Not Connect to PremiumWebCart</b>";

	}
	else
	{
		  curl_close($ch);
	}

	return $response;
}
function pwcs_getParsedProduct2($merchant_id, $api_key='')
{
	$target_url =  PWCS_PWC_CORE_URL .  'testapi/productList/0.html';
	pwcs_debug_file_write('productList URL :'.$target_url);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'merchantid='    . urlencode($merchant_id);
	$request.= '&api_signature=' . urlencode($api_key);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch))
	{
//		print "<pre>\n";
//		print_r(curl_getinfo($ch));  // get error info
//		echo 'CURL ERROR NO : ', curl_errno($ch);
//		echo '<br>CURL ERROR: ', curl_error($ch);
//		print "</pre>\n";
		print "<b>Sorry, We could Not Connect to PremiumWebCart</b>";

	}
	else
	{
		  curl_close($ch);
	}

	return $response;
}



/**
 * Add to Cart API by Rasel Kabir Shuvro
 * This function will connect to PWC CORE for add to cart funtionality
 * @param pwc_product_link_id [required for all time add to cart], pwc_order_temp_id [required, From 2nd time add to cart], pwc_order_token  [required, From 2nd time add to cart]
 * @return XML data with pwc_order_temp_id and pwc_order_token
 * Added by Rasel Kabir
 */
function pwcs_api_addtocart($pwc_product_link_id = NULL, $addtocart_quantiy= 1, $csv_options=NULL, $pwc_order_temp_id= NULL, $pwc_order_token = NULL) {

	$target_url = PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_addtocart', 'api/addToCart.html');
	$API_KEY = variable_get('pwcs_target_url_cart_apikey', 'PS4s1S3DF5rw5Fod5s4w8e4xds5d7w5e=');

	if($pwc_product_link_id != NULL) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $target_url);
		curl_setopt($ch, CURLOPT_POST, 1);

		if(($pwc_order_temp_id != NULL) && ($pwc_order_token != NULL)) { // for 2nd time and more

			$request = 'apikey=' . urlencode($API_KEY)
					   . '&productlinkid=' . urlencode($pwc_product_link_id)
					   . '&quantity='   . urlencode($addtocart_quantiy)
					   . '&attributerowid='   . urlencode($csv_options)
					   . '&ordertempid='   . urlencode($pwc_order_temp_id)
					   . '&token='         . urlencode($pwc_order_token);
		} else { // for 1st time add to cart

			$request = 'apikey=' . urlencode($API_KEY)
					   . '&productlinkid=' . urlencode($pwc_product_link_id)
					   . '&quantity='   . urlencode($addtocart_quantiy)
					   . '&attributerowid='   . urlencode($csv_options);
		} // else

		pwcs_debug_file_write($request, true);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			//print "<b>Sorry, We could Not Connect to Premium Web Cart</b>";
			drupal_set_message(t('Sorry, We could Not Connect to Premium Web Cart'), 'error');
			return 'error';
		} else {
		      curl_close($ch);
		}
		pwcs_debug_file_write($response, true);

		//pwcs_debug_file_write(print_r($response, true));

		return $response;
	} // if($pwc_product_link_id != NULL)
	else {
		return false;
	}

}

/**
 * Add to Cart API by Rasel Kabir Shuvro
 * This function will connect to PWC CORE for add to cart funtionality
 * @param pwc_product_link_id [required for all time add to cart], pwc_order_temp_id [required, From 2nd time add to cart], pwc_order_token  [required, From 2nd time add to cart]
 * @return XML data with pwc_order_temp_id and pwc_order_token
 * Added by Rasel Kabir
 */
function pwcs_api_cartinfo($pwc_order_temp_id = NULL) {

	$target_url = PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_getcartinfo', 'api/getCartInfo.html');
	$API_KEY = variable_get('pwcs_target_url_cart_apikey', 'PS4s1S3DF5rw5Fod5s4w8e4xds5d7w5e=');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'apikey=' . urlencode($API_KEY)
			   . '&ordertempid=' . urlencode($pwc_order_temp_id);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
			//print "<b>Sorry, We could Not Connect to Premium Web Cart</b>";
			drupal_set_message(t('Sorry, We could Not Connect to Premium Web Cart'), 'error');
			return 'error';
	} else {
	      curl_close($ch);
	}
	return $response;
}

/**
 * Add to Cart API by Rasel Kabir Shuvro
 * This function will connect to PWC CORE for add to cart funtionality
 * @param pwc_product_link_id [required for all time add to cart], pwc_order_temp_id [required, From 2nd time add to cart], pwc_order_token  [required, From 2nd time add to cart]
 * @return XML data with pwc_order_temp_id and pwc_order_token
 * Added by Rasel Kabir
 */
function pwcs_api_removefromcart($pwc_order_item_id = NULL) {

	$target_url = PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_removefromcart', 'api/removeFromCart.html');
	$API_KEY = variable_get('pwcs_target_url_cart_apikey', 'PS4s5e75sdSD457d5s4w8e4xds5d7w5e=');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'apikey=' . urlencode($API_KEY)
			   . '&orderitemid=' . urlencode($pwc_order_item_id);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
			//print "<b>Sorry, We could Not Connect to Premium Web Cart</b>";
			drupal_set_message(t('Sorry, We could Not Connect to Premium Web Cart'), 'error');
			return 'error';
	} else {
	      curl_close($ch);
	}
	return $response;
}

/**
 * CURL calling for addProductFromStore
 * Added by Rasel Kabir
 */
function pwcs_api_insertProductAPI($merchant_id = NULL, $product = NULL)
{
  if($product)
  {
    $product_id          = $product['product_nid'];
    $pwc_product_row_id  = $product['pwc_product_row_id'];
    $product_name        = $product['product_name'];
//  $product_name = str_replace(  array("&","<",">","\"", "'", "-"), 
//                                array("&amp;", "&lt;", "&gt;", "&quot;", "&#39;", "&#45;"), 
//                                $product_name);
      
    $parent_category_id  = $product['pwc_category_id'];
    $product_price       = $product['price'];
    $product_sku         = $product['product_sku'];
    $product_shorttext   = $product['shorttext'];
    $product_longtext    = $product['longtext'];
    $product_manufacture = $product['product_manufacture'];
    $product_manufac_id  = $product['manufacturer_id'];

    $target_url = PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_addproductfromstore', 'testapi/addProductFromStore.html');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    //$request =     'product_description=' . urlencode(stripslashes($product_name))
    $request =     'product_description=' . urlencode($product_name)
                 . '&merchant_id='        . urlencode($merchant_id)
                 . '&parent_category='    . urlencode($parent_category_id)
                 . '&pwc_product_row_id=' . urlencode($pwc_product_row_id)
                 . '&price='              . urlencode($product_price)
                 . '&product_sku='        . urlencode($product_sku)
                 . '&product_shorttext='  . urlencode($product_shorttext)
                 . '&product_longtext='   . urlencode($product_longtext)
                 . '&product_manufacture='. urlencode($product_manufacture)
                 . '&manufacturer_id='    . urlencode($product_manufac_id);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
      //print "<b>Sorry, We could Not Connect to Premium Web Cart</b>";
      drupal_set_message(t('Sorry, We could Not Connect to Premium Web Cart'), 'error');
      return 'error';
    } else {
      curl_close($ch);
    }
    return $response;
  }
  else
  {
    return 'error';
  }
}

/**
 * CURL calling for addCategoryFromStore
 * Added by Rasel Kabir
 */
function pwcs_api_insertCategoryAPI($merchant_id = NULL, $category = NULL)
{
	if($merchant_id!=NULL && $category!=NULL) {

	    $pwc_category_id = $category['pwc_category_id'];
	    $parent_category_id=$category['parent_category_id'];
	    $cat_title = $category['category_title'];
	    $category_level = $category['category_level'];
	    $prev_parent_cateogry_id = $category['prev_parent_cateogry_id'];

	    $target_url = PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_addcategoryfromstore', 'testapi/addCategoryFromStore.html');

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $target_url);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    $request = 'category_title=' . urlencode(stripslashes($cat_title))
	               . '&merchant_id=' . urlencode($merchant_id)
	               . '&parent_category_id=' . urlencode($parent_category_id)
	               . '&pwc_category_id=' . urlencode($pwc_category_id)
	               . '&level=' . urlencode($category_level);

	    if($prev_parent_cateogry_id)
	    {
	       $request .= '&prev_parent='.urlencode($prev_parent_cateogry_id);
	    }
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	    curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_VERBOSE, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	    $response = curl_exec($ch);
	    if (curl_errno($ch))
	    {
			//print "<b>Sorry, We could Not Connect to Premium Web Cart</b>";
			drupal_set_message(t('Sorry, We could Not Connect to Premium Web Cart'), 'error');
			return 'error';
	    }
	    else
	    {
	          curl_close($ch);
	          return $response;
	    }
	    return $response;
	} else {
		return 'error';
	}

}
function pwcs_modulename_save_image($img, $fullpath){

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $img);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$rawdata  = curl_exec($ch);
	if (curl_errno($ch))
	{
		//print "<b>Sorry, We could Not Connect to PremiumWebCart</b>";
	}
	else
	{
		  curl_close($ch);
	}
   if(file_exists($fullpath)){
     unlink($fullpath);
   }
   $fp = fopen($fullpath, 'x');
   fwrite($fp, $rawdata);
   fclose($fp);
}


function pwcs_save_file_files($file='')
{
	global $user;
	//$file = $thisfilename;
    //$details = stat($file);
    //$filesize = $details['size'];
    //$dest = $file_save_path;
	//$name = basename($file);
    $file_obj = new stdClass();
    $file_obj->filename = basename($file);
    $file_obj->filepath = $file;
    $file_obj->filemime =  file_get_mimetype($file);
    $file_obj->filesize = filesize($file);//$filesize;
    //$file_obj->filesource = basename($file);
    $file_obj->uid = $user->uid;
    $file_obj->status = FILE_STATUS_PERMANENT;//FILE_STATUS_TEMPORARY
    $file_obj->timestamp = time();
    //$file_obj->list = 1;
    //$file_obj->new = true;

    if ($file_obj->filesize > 0) {
    	drupal_write_record('files', $file_obj);
    }
    // change file status to permanent
    //file_set_status($file_obj,1);
    //field_file_load($file);

    return $file_obj;
}

//function pwcs_save_file_files($thisfilename='')
//{
//	global $user;
//
//	$file = $thisfilename;
//    // Get the file size
//    $details = stat($file);
//    $filesize = $details['size'];
//
//    // Get the path to your Drupal site's files directory
//    $dest = $file_save_path;
//
//
//	$name = basename($file);
//
//	// Build the file object
//    $file_obj = new stdClass();
//    $file_obj->filename = $name;
//    $file_obj->filepath = $file;
//    $file_obj->filemime =  file_get_mimetype($name);
//    $file_obj->filesize = $filesize;
//    $file_obj->filesource = $name;
//    // You can change this to the UID you want
//    $file_obj->uid = $user->uid;
//    $file_obj->status = FILE_STATUS_PERMANENT;//FILE_STATUS_TEMPORARY
//    $file_obj->timestamp = time();
//    $file_obj->list = 1;
//    $file_obj->new = true;
//
//    if ($file_obj->filesize > 0)
//    {
//    // Save file to files table
//    	drupal_write_record('files', $file_obj);
//    }
//    // change file status to permanent
//    file_set_status($file_obj,1);
//    return $file_obj;
//}

function pwcs_getParsedManufacturer($merchant_id, $api_key='')
{
	$target_url =  PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_maufacturerlist', 'testapi/manufacturerList.html');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'merchantid=' . urlencode($merchant_id);
	$request.= '&api_signature=' . urlencode($api_key);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
			drupal_set_message(t('Sorry, We could Not Connect to Premium Web Cart'), 'error');
			return 'error';
	} else {
	      curl_close($ch);
	}
	return $response;
}
function pwcs_sendCategoryOrder($merchant_id, $api_key='')
{
	$target_url =  PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_categoryorderupdate', 'api/saveCategoryOrder.html');

	$category_order = pwcs_category_sort_request_to_core();
	pwcs_debug_file_write($category_order, true);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'merchantid=' . urlencode($merchant_id);
	$request.= '&signature=' . urlencode($api_key);
	$request.= '&categoryorder=' . $category_order;
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
			drupal_set_message(t('Sorry, We could Not Connect to Premium Web Cart'), 'error');
			return 'error';
	} else {
			drupal_set_message(t('Sorting Order has been Sent to Premium Web Cart Successfully.'));
	      curl_close($ch);
	}
	pwcs_debug_file_write($request, true);
	pwcs_debug_file_write($response);
	return $response;

}
function pwcs_getAttributes($merchant_id, $api_key='')
{
	$target_url =  PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_attributesinfo', 'testapi/productOption.html');


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'merchantid=' . urlencode($merchant_id);
	$request.= '&api_signature=' . urlencode($api_key);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
			drupal_set_message(t('Sorry, We could Not Connect to Premium Web Cart'), 'error');
			return 'error';
	} else {
	      curl_close($ch);
	}
	return $response;
}
function pwcs_getMerchantInformation()
{
	$target_url =  PWCS_PWC_CORE_URL . 'testapi/getMerchantInformation.html';
			
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'merchantid=' . urlencode($merchant_id);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch))
	{
		$response = 'error';
	}
	else
	{
		curl_close($ch);
	}
	return $response;
}
function pwcs_ping_search_engine($engine_name,$target_url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
			//drupal_set_message(t('Sorry, We could Not Connect to Premium Web Cart'), 'error');
			//return 'error';
	} else {
	      curl_close($ch);
	}
	pwcs_debug_file_write($engine_name.' - '.$target_url);
	pwcs_debug_file_write($response);
	return $response;
}
function pwcs_get_store_attr_group_id($pwc_attr_group_id=0) {
	$store_attr_group_id = db_result(db_query("SELECT id FROM {pwcs_attr_groups} WHERE pwc_group_row_id='%d'",$pwc_attr_group_id));
	return ($store_attr_group_id > 0) ? $store_attr_group_id : 0;
}
function pwcs_get_store_attr_id($pwc_attr_id=0) {
	$store_attr_id = db_result(db_query("SELECT id FROM {pwcs_attributes} WHERE pwc_attribute_id='%d'",$pwc_attr_id));
	return ($store_attr_id > 0) ? $store_attr_id : 0;
}
function pwcs_get_product_nid($pwc_product_id=0) {
	$nid = db_result(db_query("SELECT nid FROM {pwcs_product} WHERE pwc_product_row_id='%d'",$pwc_product_id));
	return ($nid > 0) ? $nid : 0;
}
function pwcs_update_productlistingin_nid($pwc_product_id=0,$newlistingid=-1) {
	db_query("UPDATE {pwcs_product} SET listing_id = '%s' WHERE pwc_product_row_id='%d'",$newlistingid,$pwc_product_id);
}
function syncBasicSettings($merchant_id, $api_key)
{
		global $finalmsgs;
		
		pwcs_debug_file_write('Start basic settings');
		$response_basicSettings = pwcs_getBasicSettings($merchant_id, $api_key);
		pwcs_debug_file_write($response_basicSettings, true);
		if (module_exists('xml_parser')){
			$response_basicSettings = xml_clean($response_basicSettings);
			$response_array_basicSettings = xml_parser_string_to_array($response_basicSettings);
		} else
			$response_array_basicSettings = 'module not enabled';

		pwcs_debug_file_write($response_array_basicSettings, true);

		if (is_array($response_array_basicSettings['pwcbasicsettings']))
		foreach($response_array_basicSettings['pwcbasicsettings'] as $key => $value) {
			variable_set('pwcs_pwcbasicsettings_'.$key, $value);
			pwcs_debug_file_write(variable_get('pwcs_pwcbasicsettings_'.$key), true);
		}
		variable_set('pwcs_load_basic_settings_data', '0');
		$finalmsgs[] = t('Basic Settings has been Synched Properly from PremiumWebCart!');

		pwcs_debug_file_write('End basic settings\n\n');
}		

function syncCategory($merchant_id, $api_key)
{
		GLOBAL $finalmsgs;
		
		
	    pwcs_debug_file_write('Start Categories\n');

	    // Emplty the category table
	    db_query("TRUNCATE TABLE {pwcs_category}");

		$product_no = 0;
		$category_no = 0;

		$total_category_data_found = 0;
		$tmp_total_category_data_found = 0;

		// Get Category from PWC CORE
	    $response_categories = pwcs_getParsedCategory($merchant_id, $api_key);
		pwcs_debug_file_write($response_categories, true);

		if (module_exists('xml_parser')){
			$response_categories = xml_clean($response_categories);
			$response_array_categories = xml_parser_string_to_array($response_categories);
		} else
			$response_array_categories = '';

		pwcs_debug_file_write($response_array_categories, true);

		$categorydat_is_sync = 1;
		$weight = 0;
		$status = 1;

		// A Default category Miscellaneous is going to be created so that we can make this category
		// sortable & update the category name that Merchant will like.
		$sql_default_category = "INSERT INTO {pwcs_category}".
		    	                " ( category_title, is_editable, created_on, modified_on )".
		    	                " VALUES (  '%s', %d, %d, %d)";
	    db_query(	$sql_default_category,
	        			mysql_escape_string(PWCS_CATEGORY_DEFAULT_NAME),
	        			0,
	        			isset($categorydata['createdon']) ? strtotime($categorydata['createdon']) : time(),
	        			isset($categorydata['modifiedon']) ? strtotime($categorydata['modifiedon']) : time()
	        			);
		if (is_array($response_array_categories))
		{
			$total_category_data_found = count($response_array_categories['categories']);
		    foreach($response_array_categories['categories'] as $categorydata)
		    {
				$weight++;
			    if($categorydata['categoryid'] && $categorydata['categorytitle'] && $categorydata['merchantrowid'])
			    {
				    $sql = "INSERT INTO {pwcs_category}".
				    	   " (pwc_category_id, parent_category_id, category_title, category_level, has_child, is_editable, is_sync, weight, status, created_on, modified_on )".
				    	   " VALUES ( %d, %d, '%s', %d, %d, %d, %d, %d, %d, %d, %d)";
			        db_query(	$sql,
			        			$categorydata['categoryid'],
			        			$categorydata['parentcategoryid'],
			        			xml_text_clean($categorydata['categorytitle']),
			        			$categorydata['level'],
			        			$categorydata['haschild'],
			        			$categorydata['iseditable'],
			        			$categorydat_is_sync,
			        			$weight,
			        			$status,
			        			isset($categorydata['createdon']) ? strtotime($categorydata['createdon']) : time(),
			        			isset($categorydata['modifiedon']) ? strtotime($categorydata['modifiedon']) : time()
			        			);
			        $category_no++;
			        $tmp_total_category_data_found++;
			    }
		    }
		}

    if($total_category_data_found==$tmp_total_category_data_found)
		{
			variable_set('pwcs_load_category_data', '0');
			$finalmsgs[] = t(($total_category_data_found+1).' '.'Category(s) has been Synched Properly from PremiumWebCart!');
		} else {
			variable_set('pwcs_load_category_data', '0');
			$finalmsgs[] = t(($tmp_total_category_data_found+1).' ( of '.$total_category_data_found. ') Category(s) has been Synched Properly from PremiumWebCart!');
		}
		pwcs_debug_file_write('End Categories\n\n');
		
}


function syncListing($merchant_id, $api_key)
{
		global $finalmsgs;
	    // Emplty the category table
	    //db_query("TRUNCATE TABLE {pwcs_listings}");
	    db_query("DELETE FROM {pwcs_listings} WHERE category_type=0");
		//db_query("TRUNCATE TABLE {pwcs_product_listing}");

		$product_no = 0;
		$category_no = 0;

		$total_category_data_found = 0;
		$tmp_total_category_data_found = 0;

		// Get Category from PWC CORE
	    $response_categories = pwcs_getParsedCategory($merchant_id, $api_key);
		pwcs_debug_file_write($response_categories, true);

		if (module_exists('xml_parser')){
			$response_categories = xml_clean($response_categories);
			$response_array_categories = xml_parser_string_to_array($response_categories);
		} else
			$response_array_categories = '';

		pwcs_debug_file_write($response_array_categories, true);

		$categorydat_is_sync = 1;
		$weight = 0;
		$status = 1;

		// A Default category Miscellaneous is going to be created so that we can make this category
		// sortable & update the category name that Merchant will like.
		$sql_default_category = "INSERT INTO {pwcs_listings}".
		    	                " ( category_title, pwc_category_id, is_editable, created_on, modified_on )".
		    	                " VALUES (  '%s', %d, %d, %d, %d)";
	    db_query(	$sql_default_category,
	        			mysql_escape_string(PWCS_CATEGORY_DEFAULT_NAME),
	        			-1,
	        			0,
	        			isset($categorydata['createdon']) ? strtotime($categorydata['createdon']) : time(),
	        			isset($categorydata['modifiedon']) ? strtotime($categorydata['modifiedon']) : time()
	        			);


		if (is_array($response_array_categories))
		{
			$total_category_data_found = count($response_array_categories['categories']);
		    foreach($response_array_categories['categories'] as $categorydata)
		    {
				$weight++;
			    if($categorydata['categoryid'] && $categorydata['categorytitle'] && $categorydata['merchantrowid'])
			    {
				    $sql = "INSERT INTO {pwcs_listings}".
				    	   " (pwc_category_id, parent_category_id, category_title, category_level, has_child, is_editable, is_sync, weight, status, created_on, modified_on )".
				    	   " VALUES ( %d, %d, '%s', %d, %d, %d, %d, %d, %d, %d, %d)";
			        db_query(	$sql,
			        			$categorydata['categoryid'],
			        			$categorydata['parentcategoryid'],
			        			xml_text_clean($categorydata['categorytitle']),
			        			$categorydata['level'],
			        			$categorydata['haschild'],
			        			$categorydata['iseditable'],
			        			$categorydat_is_sync,
			        			$weight,
			        			$status,
			        			isset($categorydata['createdon']) ? strtotime($categorydata['createdon']) : time(),
			        			isset($categorydata['modifiedon']) ? strtotime($categorydata['modifiedon']) : time()
			        			);
			        $category_no++;
			        $tmp_total_category_data_found++;
			    }
		    }
		}

    if($total_category_data_found==$tmp_total_category_data_found)
		{
			variable_set('pwcs_load_listing_data', '0');
			$finalmsgs[] = t(($total_category_data_found+1).' '.PWCS_LISTING_NAME.'(s) has been Synched Properly from PremiumWebCart!');
		} else {
			variable_set('pwcs_load_listing_data', '0');
			$finalmsgs[] = t(($tmp_total_category_data_found+1).' ( of '.$total_category_data_found. ') '.PWCS_LISTING_NAME.'(s) has been Synched Properly from PremiumWebCart!');
		}
		
	
	pwcs_debug_file_write('End Listing\n\n');	
}


function syncProducts($merchant_id, $api_key)
{
		global $user, $finalmsgs;
		pwcs_debug_file_write('Start Products\n');


		db_query("TRUNCATE TABLE {pwcs_product_sales}");
		/*
		$t='';
		$my_old_product = array();
		$old_product_fid = array();

	    // Empty the product table / Delete all product node
		$sql  = "SELECT * FROM {node} n Inner Join {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product'";
		$results = db_query($sql);
		while($result = db_fetch_object($results))
		{
			$old_node = node_load($result->nid);
			//echo 'Image count'.count($old_node->field_product_image).'<br/>';


			$my_old_product[$result->pwc_product_row_id]['field_product_image']=$old_node->field_product_image;
			$my_old_product[$result->pwc_product_row_id]['pwc_image_select']=$old_node->pwc_image_select;
			$my_old_product[$result->pwc_product_row_id]['product_image_path']=$old_node->product_image_path;

			foreach( $old_node->field_product_image as $key => $item){
				//echo 'Hello Here I am'.$item['fid'].'<br/>';

				if ($item['fid']>0)
				{
				$file = field_file_load($item['fid']);
				//$filepath = $file['filepath'];
				//echo 'Path'.$filepath.'<br/>';
				$old_product_fid[$result->pwc_product_row_id][] = $file;
			}


			}
		}



		// Emplty the category table
	    //db_query("TRUNCATE TABLE {pwcs_product_images}");
		//db_query("TRUNCATE TABLE {pwcs_product}");
		*/
		
	    // Empty the product table / Delete all product node
		$sql  = "SELECT n.nid FROM {node} n WHERE n.type = 'product'";
		$results = db_query($sql);
		while($result = db_fetch_object($results))
		{
			node_delete($result->nid);
		}

		db_query("UPDATE {node} SET type = 'product-back' WHERE type = 'product'");
		db_query("TRUNCATE TABLE {pwcs_product}");
		$total_product_data_found = 0;
		$tmp_total_product_data_found = 0;


		// Get products from PWC CORE
	    $response_products = pwcs_getParsedProduct($merchant_id, $api_key);

		pwcs_debug_file_write($response_products, true);
		if (module_exists('xml_parser')) {
			$response_products = xml_clean($response_products);
			$response_array_products = xml_parser_string_to_array($response_products);
		}else{
			
		}
		//echo '<br/>Count:'.count( $response_products);
		//echo '<br/>Count:'.count( $response_products);
		//die('HERE 22');
		//pwcs_debug_file_write($_SESSION, true);
		//pwcs_debug_file_write($response_products, true);

		$product_is_sync = 1;
		
		$total_product_data_found = count($response_array_products['products']);
		
		if ($total_product_data_found<=0)
		{
	    $response_products = pwcs_getParsedProduct2($merchant_id, $api_key);
	    $response_array_products = array();
		//pwcs_debug_file_write($response_products, true);
		if (module_exists('xml_parser')) {
			$response_products = xml_clean($response_products);
			$response_array_products = xml_parser_string_to_array($response_products);
		}else{
			
		}
		//pwcs_debug_file_write($_SESSION, true);
		//pwcs_debug_file_write($response_array_products, true);

		$product_is_sync = 1;
				
				
		}
		$total_synched_products = 0;
		if (is_array($response_array_products['products']))
	    foreach($response_array_products['products'] as $product)
	    {
			$total_synched_products += 1;
			
/*			if (($total_synched_products>0) && ($total_synched_products<150))
			continue;
*/		    
			//if ($total_synched_products>50)
			//continue;

			if($product['product_row_id']) {
			    $node = new stdClass();
			    $node->uid                  = $user->uid;
			    $node->type                 = 'product';
			    $node->title                = xml_text_clean($product['name']); //stripslashes(mysql_escape_string($product['name']));
			    $node->teaser				= xml_text_clean($product['shortdesc']); //stripslashes(mysql_escape_string($product['shortdesc']));
			    $node->body                 = xml_text_clean($product['longdesc']); //stripslashes(mysql_escape_string($product['longdesc']));
			    $node->product_manufacture  = xml_text_clean($product['manufacturedesc']);
			    $node->manufacturer_id      = $product['manufactureid'];
			    //$node->product_name       = $product['name'];
			    $node->pwc_product_row_id   = $product['product_row_id'];
			    $node->product_link_id      = $product['product_link_id'];
			    $node->product_sku          = xml_text_clean($product['sku']);
			    $node->pwc_category_id      = $product['categoryid'];
			    $node->price          		= $product['price'];
			    $node->issalespriceactive   = $product['salepriceisactive'];
			    $node->salesprice          	= $product['saleprice'];
			    $node->salepricestartdate   = $product['salepricestartdate'];
			    $node->salepriceenddate     = $product['salepriceenddate'];
			    $node->salestag             = $product['saletagicon'];
			    $node->regular_buy_link     = $product['regularbuylink'];
			    $node->one_click_buy_link   = $product['oneclickbuylink'];
			    $node->default_attribute    = $product['defaultattribute'];
			    $node->product_image_path   = '';
				$node->pwc_image_select 	= 0;
			    $node->created_on           = isset($product['createdon']) ? strtotime($product['createdon']) : time();
			    $node->modified_on          = isset($product['modifiedon']) ? strtotime($product['modifiedon']) : time();
			    $node->is_sync          	= $product_is_sync;
			    $node->comment          	= COMMENT_NODE_READ_WRITE ? COMMENT_NODE_READ_WRITE : '0';
			    $node->sync_api          	= 1; // not for DB, status flag to detect node_save

			    $image_array_data = array();

			    //pwcs_debug_file_write('--prodctarray--');
			    //pwcs_debug_file_write($product['imagepath'], true);
				
				//echo '<br/>Product Name:'.$product['name'];
				//echo '<br/>Product Image:'.$product['imagepath'];
				
			    if(!empty($product['imagepath'])) {
					//pwcs_debug_file_write($product['imagepath']);

					//////
					

					$ifstoreimage = substr(strrchr($product['imagepath'],'/'),1);
					$tmpimageloc= 'https://www.secureinfossl.com/'.$ifstoreimage;
					
					if($product['imagepath']!=$tmpimageloc)
					{
					$ifstoreimage = '';
					}
					if (!empty($ifstoreimage))
					{
						//echo '<br/>Store image :'.$ifstoreimage ;
						
						$storeimageext = substr(strrchr($ifstoreimage,'.'),1);
						if (empty($storeimageext)){
							$ifstoreimage .= '.jpg';
						}
						
						$results = db_query("SELECT * FROM {files} WHERE filename='%s'",$ifstoreimage);
						$foundimage=0;
						while($result = db_fetch_object($results)) {
							if (file_exists(file_directory_path() .'/imagelibrary/'.$ifstoreimage))
							{
							$foundimage=1;
							
							$main_image_array_data = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
							$image_array_data = array($main_image_array_data);						
							}
						}
					}else{
						//echo '<br/> Remote image :'.$product['imagepath'];
					$file_save_path = file_directory_path() .'/imagelibrary/';
					$ext = substr(strrchr($product['imagepath'],'.'),1);
					
					
					if ((!empty($ext)) && (!empty($product['imagepath']))){
						pwcs_modulename_save_image($product['imagepath'], $file_save_path . $product['product_row_id'] . ".".$ext);
					}

					//pwcs_debug_file_write($file_save_path . $product['product_row_id'] . ".".$ext);

				    $file_obj = pwcs_save_file_files($file_save_path . $product['product_row_id'] . "." . $ext);

//pwcs_debug_file_write($file_obj, true);

				    $main_image_array_data = array("fid" => $file_obj->fid, "list" => $file_obj->list, "data" => array('alt' => '', 'title' => ''),"uid" => $file_obj->uid, "filename" => $file_obj->filename, "filepath" => $file_obj->filepath, "filemime" => $file_obj->filemime, "filesize" => $file_obj->filesize, "status" => $file_obj->status, "timestamp" => $file_obj->timestamp);
				    $image_array_data = array($main_image_array_data);
					}
					//////



					$product['imagepath'] = '';
				
//					$node->field_product_image = array(
//					    array(
//					      'fid' => $file_obj->fid,
//					      'title' => basename($file_obj->filename),
//					      'filename' => $file_obj->filename,
//					      'filepath' => $file_obj->filepath,
//					      'filesize' => $file_obj->filesize,
//					      'mimetype' => $file_obj->filemime,
//					      'data' => array(
//					        'description' => basename($file->filename),
//					      ),
//					      'list' => 1,
//					    ),
//					  );
					$node->pwc_image_select = $file_obj->fid;
					$node->product_image_path   = ''; //$product['imagepath'];
			    }

				

				$node->field_product_image = $image_array_data;

				//pwcs_debug_file_write('Node SAVE');
				//pwcs_debug_file_write((array)$node, true);
				//print_r($image_array_data);
				//node_save($node);
				//echo '<br/>Node Id:'.count($product['additionalimages']);
				
				//// Save additional images:
				if (is_array($product['additionalimages'])){

					$additional_images = array();
					$error = array();
					$i = 0;
					//echo '<br/>Product Additional Images: HERE';
					//echo print_r($product['additionalimages']);
					$additional_images_list ='';
					foreach ($product['additionalimages'] as $image_path) {
						if (!empty($image_path)) {
						
							//echo '<br/>Additional Images Name:'.$image_path;
							$additional_images_list =$image_path;
							$addimgifstoreimage = substr(strrchr($image_path,'/'),1);
							if (!empty($addimgifstoreimage))
							{
								//echo '<br/> Is store Additional image :'.$ifstoreimage ;
								//echo '<br/>'.file_directory_path() .'/imagelibrary/';
								
								$sql = "INSERT INTO {pwcs_product_images} (nid, ppi_filepath, ppi_title, ppi_status, ppi_remotepath)".
										" VALUES ( %d, '%s', '%s', %d, %d)";
								//db_query($sql, $node->nid, $image_path, basename($image_path), 1, 1);
	
								$ext_add = substr(strrchr($image_path,'.'),1);
								if ((!empty($ext_add)) && (!empty($image_path))){
									//download the file in our server files folder
									pwcs_modulename_save_image($image_path, $file_save_path .$product['product_row_id']."-".$i.".".$ext_add);
									//save the file in database enrty
									$file_obj_add = new stdClass();
									$file_obj_add = pwcs_save_file_files($file_save_path. $product['product_row_id']."-".$i.".".$ext_add);
	
									//if the filesize is ZERO than it means that image does not exist, we should not save that image that dow
									if ($file_obj_add->filesize > 2048)
									{
										//echo '<br/>Product Additional Images:'.$file_obj_add->filename;
										//creating the array so that they can be saved with node entry.
										$add_image_array = array("fid" => $file_obj_add->fid, "list" => $file_obj_add->list, "data" => array('alt' => '', 'title' => ''),"uid" => $file_obj_add->uid, "filename" => $file_obj_add->filename, "filepath" => $file_obj_add->filepath, "filemime" => $file_obj_add->filemime, "filesize" => $file_obj_add->filesize, "status" => $file_obj_add->status, "timestamp" => $file_obj_add->timestamp);
										$image_array_data[$i+1] = $add_image_array;
									}
								}								
								
							}else {
								$addstoreimageext = substr(strrchr($image_path,'.'),1);
								if (empty($addstoreimageext)){
									$image_path .= '.jpg';
								}
								//echo '<br/>Additional Image Path & Name:'.$image_path;
								$sql = "INSERT INTO {pwcs_product_images} (nid, ppi_filepath, ppi_title, ppi_status, ppi_remotepath)".
										" VALUES ( %d, '%s', '%s', %d, %d)";
								//db_query($sql, $node->nid, $image_path, basename($image_path), 1, 1);
	
								$ext_add = substr(strrchr($image_path,'.'),1);								
								
								if ((!empty($ext_add)) && (!empty($image_path)))
								{
								
									$results = db_query("SELECT * FROM {files} WHERE filename='%s'",$image_path);
									$foundimage=0;
									while($result = db_fetch_object($results)) {
										$foundimage=1;
										$add_image_array = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
										$image_array_data[$i+1] = $add_image_array;					
									}

								}								
								
							}



						}
						$i++;
					}
				
				}
				$node->field_product_image = $image_array_data;
				//$new_node = node_load($node->nid);
				//$new_node->field_product_image = $image_array_data;
				//print_r($image_array_data);
				node_save($node);
//// End Save of Additional Images

			    $product_no++;
			    $tmp_total_product_data_found++;
		    }
	    }

  // die('last');
  //unset($_SESSION['messages']);

    if($total_product_data_found==$tmp_total_product_data_found)
		{
			variable_set('pwcs_load_product_data', '0');
			$finalmsgs[] = t($total_product_data_found.' '.'Product(s) has been Synched Properly from PremiumWebCart!');
		} else {
			variable_set('pwcs_load_product_data', '0');
			$finalmsgs[] = t($tmp_total_product_data_found.' ( of '.$total_product_data_found. ') Product(s) has been Synched Properly from PremiumWebCart!');
		}
		pwcs_debug_file_write('End Products');	
}




function syncProducts2($merchant_id, $api_key)
{
		global $user, $finalmsgs;
		pwcs_debug_file_write('Start Products\n');

		$t='';
		$my_old_product = array();
		$old_product_fid = array();

	   
		$total_product_data_found = 0;
		$tmp_total_product_data_found = 0;
		$tmp_total_updated_product_found = 0;
		$tmp_total_newly_synched_product_found = 0;


		// Get products from PWC CORE
	    $response_products = pwcs_getParsedProduct($merchant_id, $api_key);
		pwcs_debug_file_write($response_products, true);
		if (module_exists('xml_parser')) {
			$response_products = xml_clean($response_products);
			$response_array_products = xml_parser_string_to_array($response_products);
		}else{
			
		}
		//pwcs_debug_file_write($_SESSION, true);
		pwcs_debug_file_write($response_array_products, true);

		$product_is_sync = 1;
		
		$total_product_data_found = count($response_array_products['products']);

	
		if (is_array($response_array_products['products']))
	    foreach($response_array_products['products'] as $product)
	    {
	    	$node_id ='';
	    	$pid = '';
	    	$results = db_query('SELECT n.nid AS node_id, pp.* FROM {node} n LEFT JOIN {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = "product" AND pp.pwc_product_row_id = %d', $product['product_row_id']);
	    	while($result = db_fetch_object($results))
	    	{
	    		$node_id = $result->node_id;
	    		$pid = $result->pwc_product_row_id;
	    	}
	    	//echo '<br/>Found, PID'.$product['product_row_id'].'NODE ID:'.$node_id;
	    	if ($pid == $product['product_row_id'])
	    	{
	    		$node = node_load($node_id);
	    		
			    $node->uid                  = $user->uid;
			    //$node->type               = 'product';
			    $node->title                = xml_text_clean($product['name']); //stripslashes(mysql_escape_string($product['name']));
			    $node->teaser				= xml_text_clean($product['shortdesc']); //stripslashes(mysql_escape_string($product['shortdesc']));
			    $node->body                 = xml_text_clean($product['longdesc']); //stripslashes(mysql_escape_string($product['longdesc']));
			    $node->product_manufacture  = xml_text_clean($product['manufacturedesc']);
			    $node->manufacturer_id      = $product['manufactureid'];
			    //$node->pwc_product_row_id = $product['product_row_id'];
			    $node->product_link_id      = $product['product_link_id'];
			    $node->product_sku          = xml_text_clean($product['sku']);
			    $node->pwc_category_id      = $product['categoryid'];
			    $node->price          		= $product['price'];
			    $node->issalespriceactive   = $product['salepriceisactive'];
			    $node->salesprice          	= $product['saleprice'];
			    $node->salepricestartdate   = $product['salepricestartdate'];
			    $node->salepriceenddate     = $product['salepriceenddate'];
			    $node->salestag             = $product['saletagicon'];
			    //$node->regular_buy_link   = $product['regularbuylink'];
			    //$node->one_click_buy_link = $product['oneclickbuylink'];
			    $node->default_attribute    = $product['defaultattribute'];
			    //$node->created_on         = isset($product['createdon']) ? strtotime($product['createdon']) : time();
			    $node->modified_on          = isset($product['modifiedon']) ? strtotime($product['modifiedon']) : time();
			    $node->is_sync          	= 1;
			    $node->sync_api             = 0;

/*				$node->field_product_image = $my_old_product[$node->pwc_product_row_id]['field_product_image'];
				$node->pwc_image_select = $my_old_product[$node->pwc_product_row_id]['pwc_image_select'];
				$node->product_image_path   = $my_old_product[$node->pwc_product_row_id]['product_image_path'];
*/
				node_save($node);
			    $tmp_total_updated_product_found++;	    		
	    		
	    	}else{
	    		$node = new stdClass();
	    		
			    $node->uid                  = $user->uid;
			    $node->type                 = 'product';
			    $node->title                = xml_text_clean($product['name']); //stripslashes(mysql_escape_string($product['name']));
			    $node->teaser				= xml_text_clean($product['shortdesc']); //stripslashes(mysql_escape_string($product['shortdesc']));
			    $node->body                 = xml_text_clean($product['longdesc']); //stripslashes(mysql_escape_string($product['longdesc']));
			    $node->product_manufacture  = xml_text_clean($product['manufacturedesc']);
			    $node->manufacturer_id      = $product['manufactureid'];
			    $node->pwc_product_row_id   = $product['product_row_id'];
			    $node->product_link_id      = $product['product_link_id'];
			    $node->product_sku          = xml_text_clean($product['sku']);
			    $node->pwc_category_id      = $product['categoryid'];
			    $node->price          		= $product['price'];
			    $node->issalespriceactive   = $product['salepriceisactive'];
			    $node->salesprice          	= $product['saleprice'];
			    $node->salepricestartdate   = $product['salepricestartdate'];
			    $node->salepriceenddate     = $product['salepriceenddate'];
			    $node->salestag             = $product['saletagicon'];
			    $node->regular_buy_link     = $product['regularbuylink'];
			    $node->one_click_buy_link   = $product['oneclickbuylink'];
			    $node->default_attribute    = $product['defaultattribute'];
			    $node->created_on           = isset($product['createdon']) ? strtotime($product['createdon']) : time();
			    $node->modified_on          = isset($product['modifiedon']) ? strtotime($product['modifiedon']) : time();
			    $node->is_sync          	= 1;
			    $node->sync_api             = 1;

			    $image_array_data = array();
			    if(!empty($product['imagepath']))
			    {
						$file_save_path = file_directory_path() .'/imagelibrary/';
						$ext = substr(strrchr($product['imagepath'],'.'),1);
						if ((!empty($ext)) && (!empty($product['imagepath']))){
							pwcs_modulename_save_image($product['imagepath'], $file_save_path . $product['product_row_id'].".".$ext);
						}
				    $file_obj = pwcs_save_file_files($file_save_path. $product['product_row_id'].".".$ext);
				    $main_image_array_data = array("fid" => $file_obj->fid, "list" => $file_obj->list, "data" => array('alt' => '', 'title' => ''),"uid" => $file_obj->uid, "filename" => $file_obj->filename, "filepath" => $file_obj->filepath, "filemime" => $file_obj->filemime, "filesize" => $file_obj->filesize, "status" => $file_obj->status, "timestamp" => $file_obj->timestamp);
				    $image_array_data = array($main_image_array_data);

					$product['imagepath'] = '';
					if($file_obj->fid>0)
					$node->pwc_image_select = $file_obj->fid;
					else
					$node->pwc_image_select = 0;
					$node->product_image_path   = '';
			    }else{
					$node->pwc_image_select = 0;
				}

				if (is_array($product['additionalimages'])){

					$additional_images = array();
					$error = array();
					$i = 0;
					foreach ($product['additionalimages'] as $image_path) {

						if (!empty($image_path)) {
							$sql = "INSERT INTO {pwcs_product_images} (nid, ppi_filepath, ppi_title, ppi_status, ppi_remotepath)".
									" VALUES ( %d, '%s', '%s', %d, %d)";
							db_query($sql, $node->nid, $image_path, basename($image_path), 1, 1);

							$ext_add = substr(strrchr($image_path,'.'),1);
							if ((!empty($ext_add)) && (!empty($image_path))){
								//download the file in our server files folder
								pwcs_modulename_save_image($image_path, $file_save_path .$product['product_row_id']."-".$i.".".$ext_add);
								//save the file in database enrty
								$file_obj_add = new stdClass();
								$file_obj_add = pwcs_save_file_files($file_save_path. $product['product_row_id']."-".$i.".".$ext_add);

								//if the filesize is ZERO than it means that image does not exist, we should not save that image that dow
								if ($file_obj_add->filesize > 2048)
								{
									//creating the array so that they can be saved with node entry.
									$add_image_array = array("fid" => $file_obj_add->fid, "list" => $file_obj_add->list, "data" => array('alt' => '', 'title' => ''),"uid" => $file_obj_add->uid, "filename" => $file_obj_add->filename, "filepath" => $file_obj_add->filepath, "filemime" => $file_obj_add->filemime, "filesize" => $file_obj_add->filesize, "status" => $file_obj_add->status, "timestamp" => $file_obj_add->timestamp);
									$image_array_data[$i+1] = $add_image_array;
								}
							}
						}
						$i++;
					}
				}



				$node->field_product_image = $image_array_data;
				node_save($node);
			    $tmp_total_newly_synched_product_found++;		    		
	    	}
	    }		
		
	//variable_set('pwcs_load_updated_product_data', '0');
	$finalmsgs[] = t($tmp_total_newly_synched_product_found.' '.'Product(s) has been Synched Newly from PremiumWebCart!');
	$finalmsgs[] = t($tmp_total_updated_product_found.' '.'Product(s) has been Updated from PremiumWebCart!');
	
	pwcs_debug_file_write('End Synching Products for Update Only');	
}
function syncAttributes($merchant_id, $api_key)
{
		global $finalmsgs;
		pwcs_debug_file_write('Start Attributes Group & Attributes Information');

		$response_attributes = pwcs_getAttributes($merchant_id, $api_key);
		pwcs_debug_file_write($response_attributes, true);
		if (module_exists('xml_parser')){
			$response_attributes = xml_clean($response_attributes);
			$response_array_attributes = xml_parser_string_to_array($response_attributes);
		} else {
			$response_array_attributes = '';
		}
		pwcs_debug_file_write($response_array_attributes, true);

		db_query("TRUNCATE TABLE {pwcs_attr_groups}");
		db_query("TRUNCATE TABLE {pwcs_attributes}");
		db_query("TRUNCATE TABLE {pwcs_product_attributes}");

		$total_attr_group_data_found = 0;
		$tmp_total_attr_group_data_found = 0;

		$weight = -100;
	    if (is_array($response_array_attributes))
		{
			$total_attr_group_data_found = count($response_array_attributes['groups']);
			if (is_array($response_array_attributes['groups']))
		    foreach($response_array_attributes['groups'] as $groupdata)
		    {
			     $sql = " INSERT INTO {pwcs_attr_groups}".
			    	    " (attr_group_public_name, is_required, pwc_group_row_id, weight )".
			    	    " VALUES ( '%s', %d, %d, %d)";
		        db_query($sql, xml_text_clean($groupdata['groupname']), $groupdata['isrequired'], $groupdata['grouprowid'],$weight);
		        $weight++;
		        $tmp_total_attr_group_data_found++;
			}
		}

		$total_attr_data_found = 0;
		$tmp_total_attr_data_found = 0;

		$weight = -100;
	    if (is_array($response_array_attributes))
		{
			$total_attr_data_found = count($response_array_attributes['attributes']);
			if (is_array($response_array_attributes['attributes']))
		    foreach($response_array_attributes['attributes'] as $attributedata)
		    {
		    	//echo pwcs_get_store_attr_group_id($attributedata["grouprowid"]).'<br/>';
		    	$sql = " INSERT INTO {pwcs_attributes}".
			    	    " (pwc_attribute_id, group_id, attr_name, weight )".
			    	    " VALUES ( %d, %d, '%s', %d)";
			    db_query($sql, $attributedata['attributerowid'], pwcs_get_store_attr_group_id($attributedata["grouprowid"]), xml_text_clean($attributedata['attributename']),$attributedata['weight']);
			    $tmp_total_attr_data_found++;
			}
		}

		//pwcs_debug_file_write($response_array_attributes, true);
	  if (is_array($response_array_attributes))
		{
		    foreach($response_array_attributes['assignedattributes'] as $attributedata)
		    {
// 	Commented bt Rasel
//		    	$new_attributerowid = '';
//		    	if(strstr($attributedata["attributerowid"],","))
//		    	{
//		    		$attributerowid_array = explode(",", $attributedata["attributerowid"]);
//		    		if (is_array($attributerowid_array))
//			    	{
//			    		foreach($attributerowid_array as $attributerowid_val)
//				    	{
//				    		$tmp_attr_row_id = pwcs_get_store_attr_id($attributerowid_val);
//
//				    		//We will not save the attribute that does not exist in PWCSTORE
//				    		if ($tmp_attr_row_id>0)
//					    	{
//					    		if(!empty($new_attributerowid))
//					    		$new_attributerowid .= ','.$tmp_attr_row_id;
//					    		else
//					    		$new_attributerowid .= ''.$tmp_attr_row_id;
//			    			}
//			    		}
//	    			}
//	    		}else{
//	    			$tmp_attr_row_id = pwcs_get_store_attr_id($attributedata["attributerowid"]);
//	    			$new_attributerowid = $tmp_attr_row_id;
//	    			if ($tmp_attr_row_id>0)
//		    		{
//		    		 	$new_attributerowid = $tmp_attr_row_id;
//		    		}else{
//		    			$new_attributerowid = '';
//		    		}
//	    		}
//				$attributerowid = $new_attributerowid;

				// modified by Rasel
				$attributerowid = $attributedata["attributerowid"];
				$attributerowid_tmp = explode(",",$attributerowid);
				sort($attributerowid_tmp);
				$attributerowid = implode(",",$attributerowid_tmp);

	    	if (!empty($attributerowid))
		    {
	    		$sql = " INSERT INTO {pwcs_product_attributes}".
			    	    " (nid, attr_id, quantity, affected_price, subsku )".
			    	    " VALUES ( %d, '%s', %d, %f, '%s')";
			    db_query($sql, pwcs_get_product_nid($attributedata["productrowid"]),$attributerowid, $attributedata['quantity'],$attributedata['pricechange'],$attributedata['subsku']);
				}

		  }//End of  foreach($response_array_attributes['assignedattributes'] as $attributedata)
		}
		if (($total_attr_group_data_found==$tmp_total_attr_group_data_found) && ($total_attr_data_found==$tmp_total_attr_data_found))
		{
				variable_set('pwcs_load_attribute_data', '0');
				$finalmsgs[] = t($total_attr_group_data_found. ' Attributes Group & '.$total_attr_data_found.' '.'Attribute(s) Data has been Synched Properly from PremiumWebCart!');
		} else {
				variable_set('pwcs_load_attribute_data', '0');
				$finalmsgs[] = t($tmp_total_attr_group_data_found.' ( of '.$total_attr_group_data_found. ') Attributes Group & '.$total_attr_data_found.' ( of '.$tmp_total_attr_data_found. ') Attribute(s) Data has been Synched Properly from PremiumWebCart!');
		}
		pwcs_debug_file_write('End Attributes Group & Attributes Information');	
}

function syncManufacturer($merchant_id, $api_key)
{
		global $finalmsgs;
		
		pwcs_debug_file_write('Start Manufacture Data');
		$total_manufacture_data_found = 0;
		$tmp_total_manufacture_data_found = 0;
	    db_query("TRUNCATE TABLE {pwcs_manufacturer}");

		// Get Category from PWC CORE
    	$response_manufacturer = pwcs_getParsedManufacturer($merchant_id, $api_key);
		pwcs_debug_file_write($response_manufacturer, true);

		if (module_exists('xml_parser')){
			$response_manufacturer = xml_clean($response_manufacturer);
			$response_array_manufacturer = xml_parser_string_to_array($response_manufacturer);
		} else
			$response_array_manufacturer = '';

		pwcs_debug_file_write($response_array_manufacturer, true);

	    if (is_array($response_array_manufacturer))
		{
			$total_manufacture_data_found = count($response_array_manufacturer['manufacturer']);
		    foreach($response_array_manufacturer['manufacturer'] as $manufacturedata)
		    {
			    if($manufacturedata['pwcmfgid'] && $manufacturedata['mfgname'] && $manufacturedata['merchantrowid'])
			    {
				    $sql = "INSERT INTO {pwcs_manufacturer}".
				    	   " (pwc_mfg_id, mfg_name, is_sync, created_on, modified_on)".
				    	   " VALUES ( %d, '%s', %d, %d, %d)";
			        db_query(	$sql,
			        			$manufacturedata['pwcmfgid'],
			        			xml_text_clean($manufacturedata['mfgname']),
			        			1,
			        			isset($manufacturedata['createdon']) ? $manufacturedata['createdon'] : time(),
			        			isset($manufacturedata['modifiedon']) ? $manufacturedata['modifiedon'] : time()
			        		);
			        $tmp_total_manufacture_data_found++;
			    }
		    }
		}

		if($total_manufacture_data_found==$tmp_total_manufacture_data_found)
		{
			variable_set('pwcs_load_manufacturer_data', '0');
			$finalmsgs[] = t($total_manufacture_data_found. ' Manufacturer(s) Data has been Synched Properly from PremiumWebCart!');
		} else {
			variable_set('pwcs_load_manufacturer_data', '0');
			$finalmsgs[] = t($tmp_total_manufacture_data_found.' ( of '.$total_manufacture_data_found. ') Manufacturer(s) Data has been Synched Properly from PremiumWebCart!');
		}
		pwcs_debug_file_write('End Manufacture Data');	
}

function syncProductsWithListing()
{
		$sql = "UPDATE {pwcs_product} SET listing_id = ''";
		db_query($sql);

		$sql = "UPDATE {pwcs_product} SET listing_id = (pwc_category_id+0)";
		db_query($sql);

		$sql = "UPDATE {pwcs_product} SET listing_id = '-1' WHERE listing_id = 0 OR listing_id IS NULL";
		db_query($sql);	
}
function pwcs_get_file_obj($thisfilename='')
{
	global $user;

	$file = $thisfilename;
    // Get the file size
    $details = stat($file);
    $filesize = $details['size'];


	$name = basename($file);

	// Build the file object
    $file_obj = new stdClass();
    $file_obj->filename = $name;
    $file_obj->filepath = $file;
    $file_obj->filemime =  file_get_mimetype($name);
    $file_obj->filesize = $filesize;
    $file_obj->filesource = $name;
    // You can change this to the UID you want
    $file_obj->uid = $user->uid;
    $file_obj->status = FILE_STATUS_PERMANENT;//FILE_STATUS_TEMPORARY
    $file_obj->timestamp = time();
    $file_obj->list = 1;
    $file_obj->new = true;

    if ($file_obj->filesize > 0)
    {
    // Save file to files table
    	drupal_write_record('files', $file_obj);
    }
    // change file status to permanent
    file_set_status($file_obj,1);
    return $file_obj;
}
function pwcs_storefile_data($file='')
{
	global $user;
    $file_obj = new stdClass();
    $file_obj->filename = basename($file);
    $file_obj->filepath = $file;
    $file_obj->filemime =  file_get_mimetype($file);
    $file_obj->filesize = filesize($file);//$filesize;
    //$file_obj->filesource = basename($file);
    $file_obj->uid = $user->uid;
    $file_obj->status = FILE_STATUS_PERMANENT;//FILE_STATUS_TEMPORARY
    $file_obj->timestamp = time();
    //$file_obj->list = 1;
    //$file_obj->new = true;

    if ($file_obj->filesize > 0) {
    	drupal_write_record('files', $file_obj);
    }
    // change file status to permanent
    //file_set_status($file_obj,1);
    //field_file_load($file);

    return $file_obj;
}

function pwcs_fetchMerchantData2($merchant_id, $api_key = '')
{
	set_time_limit (0);
	
	global $user, $finalmsgs;
	$finalmsgs = array();

	pwcs_debug_file_write('--- Start All Sync ---');

	//unset($_SESSION["messages"]);
    if (empty($merchant_id))
    {
		drupal_set_message(t('Please Make Sure You Have Provided Merchant ID.'), 'error');
		drupal_goto('siteadmin/sync-settings');
    }
    if (empty($api_key))
    {
		drupal_set_message(t('Please Make Sure You Have Provided API Key Signature.'), 'error');
		drupal_goto('siteadmin/sync-settings');
    }

	pwcs_debug_file_write('Merchant ID:'.$merchant_id.' - Merchant Security key:'.$api_key);

	// -------------------- Basic Settings --------------------------
	if (variable_get('pwcs_load_basic_settings_data', '1') == 1)
	{
		//syncBasicSettings($merchant_id, $api_key);
	}
	// -------------------- Basic Settings End Sync --------------------------

	// -------------------- Categories --------------------------
	if (variable_get('pwcs_load_category_data', '1') == 1)
	{
		//syncCategory($merchant_id, $api_key);
		//pwcs_cleanListingProblem();
	}
	
	// -------------------- Categories End Sync --------------------------


		// -------------------- Listing --------------------------
	if (variable_get('pwcs_load_listing_data', '1') == 1)
	{
		//syncListing($merchant_id, $api_key);
		//pwcs_cleanListingProblem();
	}
	// -------------------- Listings End Sync --------------------------

	// -------------------- Products --------------------------
	if (variable_get('pwcs_load_product_data', '1') == 1)
	{
		// -------------------- Products Sync All Products --------------------------
		if (variable_get('pwcs_load_product_data_options', '1') == 1){
			syncProductsNewly($merchant_id, $api_key);
		}
		// -------------------- Products Sync Update Only --------------------------
		if (variable_get('pwcs_load_product_data_options', '1') == 2){
			//syncProducts2($merchant_id, $api_key);
		}
	}
	// -------------------- Products  End Sync--------------------------


	// -------------------- Products Sync Update Only - End Sync --------------------------
	
	// -------------------- Attributes Group & Attributes Information --------------------------
	if (variable_get('pwcs_load_attribute_data', '1') == 1)
	{
		//syncAttributes($merchant_id, $api_key);
	}

	// -------------------- Importing Manufacture Data --------------------------
	if (variable_get('pwcs_load_manufacturer_data', '1') == 1)
	{
		//syncManufacturer($merchant_id, $api_key);

	}

	unset($_SESSION["messages"]);
	foreach ($finalmsgs as $k=>$message){
		drupal_set_message($message, 'status');
	}
	if (variable_get('pwcs_load_listing_data', '1') == 1){
		syncProductsWithListing();
	}

	
	//return 'done';
	unset($_SESSION['pwcs_cart']);
	unset($_SESSION['pwcs_cart_backup']);
	pwcs_debug_file_write('--- End All Sync ---');

	pwcs_flush_cache();
	//cache_clear_all();
	//drupal_flush_all_caches();

}
function syncProductsNewly($merchant_id, $api_key)
{
		global $user, $finalmsgs;
		pwcs_debug_file_write('Start Products\n');


		db_query("TRUNCATE TABLE {pwcs_product_sales}");
		
		/*
		// Emplty the category table
	    //db_query("TRUNCATE TABLE {pwcs_product_images}");
		//db_query("TRUNCATE TABLE {pwcs_product}");
		*/
		
	    // Empty the product table / Delete all product node
		$sql  = "SELECT n.nid FROM {node} n WHERE n.type = 'product'";
		
		
		$results = db_query($sql);
		while($result = db_fetch_object($results))
		{
			node_delete($result->nid);
		}
		db_query("TRUNCATE TABLE {pwcs_product}");
		$total_product_data_found = 0;
		$tmp_total_product_data_found = 0;


		// Get products from PWC CORE
	    $response_products = pwcs_getParsedProductNew($merchant_id, $api_key);
		pwcs_debug_file_write($response_products);

		pwcs_debug_file_write($response_products, true);
		if (module_exists('xml_parser')) {
			$response_products = xml_clean($response_products);
			$response_array_products = xml_parser_string_to_array($response_products);
		}else{
			
		}
		//echo '<br/>Count:'.count( $response_products);
		//echo '<br/>Count:'.count( $response_products);
		//die();
		//pwcs_debug_file_write($_SESSION, true);
		//pwcs_debug_file_write($response_products, true);
		//die();
		$product_is_sync = 1;
		$product_no=0;
		$total_product_data_found = count($response_array_products['products']);
		
		
		
		if ($total_product_data_found<=0)
		{
			$response_products = pwcs_getParsedProduct2($merchant_id, $api_key);
			$response_array_products = array();
			//pwcs_debug_file_write($response_products, true);
			if (module_exists('xml_parser')) {
				$response_products = xml_clean($response_products);
				$response_array_products = xml_parser_string_to_array($response_products);
			}else{
				
			}
			//pwcs_debug_file_write($_SESSION, true);
			//pwcs_debug_file_write($response_array_products, true);
	
			$product_is_sync = 1;
		}
		//die('HERE'.'<br/>Product Count:'.count( $response_array_products['products']));

	$output = '';
	// Basics
	$header = array(
					array('data' => t('SL. '), 'width'=>'1%'), 
					array('data' => t('Product Name'), 'width'=>'49%'), 
					array('data' => t('Price'), 'width'=>'10%'), 
					array('data' => t('Images'), 'width'=>'20%'),
					array('data' => t('Additional Images'), 'width'=>'20%')
					);
	$rows = array();

		$total_synched_products = 0;
		if (is_array($response_array_products['products']))
	    foreach($response_array_products['products'] as $product)
	    {
			$total_synched_products += 1;
			
/*			if (($total_synched_products>0) && ($total_synched_products<150))
			continue;
*/		    
			//if ($total_synched_products>50)
			//continue;

			if($product['product_row_id']) {
			    $node = new stdClass();
			    $node->uid                  = $user->uid;
			    $node->type                 = 'product';
			    $node->title                = xml_text_clean($product['name']); //stripslashes(mysql_escape_string($product['name']));
			    $node->teaser				= xml_text_clean($product['shortdesc']); //stripslashes(mysql_escape_string($product['shortdesc']));
			    $node->body                 = xml_text_clean($product['longdesc']); //stripslashes(mysql_escape_string($product['longdesc']));
			    $node->product_manufacture  = xml_text_clean($product['manufacturedesc']);
			    $node->manufacturer_id      = $product['manufactureid'];
			    //$node->product_name       = $product['name'];
			    $node->pwc_product_row_id   = $product['product_row_id'];
			    $node->product_link_id      = $product['product_link_id'];
			    $node->product_sku          = xml_text_clean($product['sku']);
			    $node->pwc_category_id      = $product['categoryid'];
			    $node->price          		= $product['price'];
			    $node->issalespriceactive   = $product['salepriceisactive'];
			    $node->salesprice          	= $product['saleprice'];
			    $node->salepricestartdate   = $product['salepricestartdate'];
			    $node->salepriceenddate     = $product['salepriceenddate'];
			    $node->salestag             = $product['saletagicon'];
			    $node->regular_buy_link     = $product['regularbuylink'];
			    $node->one_click_buy_link   = $product['oneclickbuylink'];
			    $node->default_attribute    = $product['defaultattribute'];
			    $node->product_image_path   = '';
				$node->pwc_image_select 	= 0;
			    $node->created_on           = isset($product['createdon']) ? strtotime($product['createdon']) : time();
			    $node->modified_on          = isset($product['modifiedon']) ? strtotime($product['modifiedon']) : time();
			    $node->is_sync          	= $product_is_sync;
			    $node->comment          	= COMMENT_NODE_READ_WRITE ? COMMENT_NODE_READ_WRITE : '0';
			    $node->sync_api          	= 1; // not for DB, status flag to detect node_save

			    $image_array_data = array();

			    //pwcs_debug_file_write('--prodctarray--');
			    //pwcs_debug_file_write($product['imagepath'], true);
				
				//echo '<br/>Product Name:'.$product['name'];
				//echo '<br/>Product Image:'.$product['imagepath'];
				
			    if(!empty($product['imagepath'])) {
					//pwcs_debug_file_write($product['imagepath']);

					//////
					
				
					$ifstoreimage = substr(strrchr($product['imagepath'],'/'),1);
					$tmpimageloc= 'https://www.secureinfossl.com/'.$ifstoreimage;
					
					if($product['imagepath']!=$tmpimageloc)
					{
					$ifstoreimage = '';
					}
					if (!empty($ifstoreimage))
					{
						//echo '<br/>Store image :'.$ifstoreimage ;
						
						$storeimageext = substr(strrchr($ifstoreimage,'.'),1);
						if (empty($storeimageext)){
							$ifstoreimage .= '.jpg';
						}
						
						$results = db_query("SELECT * FROM {files} WHERE filename='%s'",$ifstoreimage);
						$foundimage=0;
						while($result = db_fetch_object($results)) {
							if (file_exists(file_directory_path() .'/imagelibrary/'.$ifstoreimage))
							{
							$foundimage=1;
							
							$main_image_array_data = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
							$image_array_data = array($main_image_array_data);						
							}
						}
						//if ($foundimage==1)
						//echo '<br/> Store image Found.';
						//else
						//echo '<br/> Store image Not Found.';
						
					}else{
						//echo '<br/> Remote image :'.$product['imagepath'];
						$file_save_path = file_directory_path() .'/imagelibrary/';
						$ext = substr(strrchr($product['imagepath'],'.'),1);
						
						
						if ((!empty($ext)) && (!empty($product['imagepath']))){
							pwcs_modulename_save_image($product['imagepath'], $file_save_path . $product['product_row_id'] . ".".$ext);
						}
	
						//pwcs_debug_file_write($file_save_path . $product['product_row_id'] . ".".$ext);
	
						$file_obj = pwcs_save_file_files($file_save_path . $product['product_row_id'] . "." . $ext);
	
	//pwcs_debug_file_write($file_obj, true);
	
						$main_image_array_data = array("fid" => $file_obj->fid, "list" => $file_obj->list, "data" => array('alt' => '', 'title' => ''),"uid" => $file_obj->uid, "filename" => $file_obj->filename, "filepath" => $file_obj->filepath, "filemime" => $file_obj->filemime, "filesize" => $file_obj->filesize, "status" => $file_obj->status, "timestamp" => $file_obj->timestamp);
						$image_array_data = array($main_image_array_data);
					}
					//////

					$product['imagepath'] = '';
				
					$node->pwc_image_select = $file_obj->fid;
					$node->product_image_path   = ''; //$product['imagepath'];
			    }

				

				$node->field_product_image = $image_array_data;

				//pwcs_debug_file_write('Node SAVE');
				//pwcs_debug_file_write((array)$node, true);
				//print_r($image_array_data);
				
				//echo '<br/>Node Id:'.count($product['additionalimages']);
				
				//// Save additional images:
				if (is_array($product['additionalimages'])){

					$additional_images = array();
					$error = array();
					$i = 0;
					//echo '<br/>Product Additional Images: HERE';
					//echo print_r($product['additionalimages']);
					$additional_images_list ='';
					foreach ($product['additionalimages'] as $image_path) {
						if (!empty($image_path)) {
						
							//echo '<br/>Additional Images Name:'.$image_path;
							$additional_images_list =$image_path;
							$addimgifstoreimage = substr(strrchr($image_path,'/'),1);
							if (!empty($addimgifstoreimage))
							{
								//echo '<br/> Is store Additional image :'.$ifstoreimage ;
								//echo '<br/>'.file_directory_path() .'/imagelibrary/';
								
								$sql = "INSERT INTO {pwcs_product_images} (nid, ppi_filepath, ppi_title, ppi_status, ppi_remotepath)".
										" VALUES ( %d, '%s', '%s', %d, %d)";
								//db_query($sql, $node->nid, $image_path, basename($image_path), 1, 1);
	
								$ext_add = substr(strrchr($image_path,'.'),1);
								if ((!empty($ext_add)) && (!empty($image_path))){
									//download the file in our server files folder
									pwcs_modulename_save_image($image_path, $file_save_path .$product['product_row_id']."-".$i.".".$ext_add);
									//save the file in database enrty
									$file_obj_add = new stdClass();
									$file_obj_add = pwcs_save_file_files($file_save_path. $product['product_row_id']."-".$i.".".$ext_add);
	
									//if the filesize is ZERO than it means that image does not exist, we should not save that image that dow
									if ($file_obj_add->filesize > 2048)
									{
										//echo '<br/>Product Additional Images:'.$file_obj_add->filename;
										//creating the array so that they can be saved with node entry.
										$add_image_array = array("fid" => $file_obj_add->fid, "list" => $file_obj_add->list, "data" => array('alt' => '', 'title' => ''),"uid" => $file_obj_add->uid, "filename" => $file_obj_add->filename, "filepath" => $file_obj_add->filepath, "filemime" => $file_obj_add->filemime, "filesize" => $file_obj_add->filesize, "status" => $file_obj_add->status, "timestamp" => $file_obj_add->timestamp);
										$image_array_data[$i+1] = $add_image_array;
									}
								}								
								
							}else {
								$addstoreimageext = substr(strrchr($image_path,'.'),1);
								if (empty($addstoreimageext)){
									$image_path .= '.jpg';
								}
								//echo '<br/>Additional Image Path & Name:'.$image_path;
								$sql = "INSERT INTO {pwcs_product_images} (nid, ppi_filepath, ppi_title, ppi_status, ppi_remotepath)".
										" VALUES ( %d, '%s', '%s', %d, %d)";
								//db_query($sql, $node->nid, $image_path, basename($image_path), 1, 1);
	
								$ext_add = substr(strrchr($image_path,'.'),1);								
								
								if ((!empty($ext_add)) && (!empty($image_path)))
								{
								
									$results = db_query("SELECT * FROM {files} WHERE filename='%s'",$image_path);
									$foundimage=0;
									while($result = db_fetch_object($results)) {
										$foundimage=1;
										$add_image_array = array("fid" => $result->fid, "list" => NULL, "data" => array('alt' => '', 'title' => ''),"uid" => $result->uid, "filename" => $result->filename, "filepath" => $result->filepath, "filemime" => $result->filemime, "filesize" => $result->filesize, "status" => $result->status, "timestamp" => $result->timestamp);
										$image_array_data[$i+1] = $add_image_array;					
									}

								}								
								
							}



						}
						$i++;
					}
				
				}
				$node->field_product_image = $image_array_data;
				//$new_node = node_load($node->nid);
				//$new_node->field_product_image = $image_array_data;
				//print_r($image_array_data);
				//node_save($new_node);
//// End Save of Additional Images

				node_save($node);

			    $product_no++;
			    $tmp_total_product_data_found++;

				
					if ((!empty($ifstoreimage)) && ($foundimage==1))
					$rows[] = array($product_no, $product['name'].'<br/>[PRO-'.$product['product_row_id'].']', $product['price'], $ifstoreimage,$additional_images_list); 
					elseif ((!empty($ifstoreimage)) && ($foundimage==0))
					$rows[] = array($product_no, $product['name'].'<br/>[PRO-'.$product['product_row_id'].']', $product['price'], $ifstoreimage.'<br/><b>Not Found in Media Manager</b>',$additional_images_list); 
					elseif(!empty($product['imagepath']))
					$rows[] = array($product_no, $product['name'].'<br/>[PRO-'.$product['product_row_id'].']', $product['price'], $product['imagepath'],$additional_images_list); 
					else
					$rows[] = array($product_no, $product['name'].'<br/>[PRO-'.$product['product_row_id'].']', $product['price'], 'No Image',$additional_images_list); 
				
		    }
	    }


  //unset($_SESSION['messages']);

    if($total_product_data_found==$tmp_total_product_data_found)
		{
			variable_set('pwcs_load_product_data', '0');
			$finalmsgs[] = t($total_product_data_found.' '.'Product(s) has been Synched Properly from PremiumWebCart!');
		} else {
			variable_set('pwcs_load_product_data', '0');
			$finalmsgs[] = t($tmp_total_product_data_found.' ( of '.$total_product_data_found. ') Product(s) has been Synched Properly from PremiumWebCart!');
		}
		pwcs_debug_file_write('End Products');	
		
	$output .= theme('table', $header, $rows, array('width'=>'100%'));
	
	drupal_set_message($output, 'status');	
	drupal_goto('siteadmin/sync');	  	
}
/**
 * CURL calling for productList
 * Added by Rasel Kabir
 */
function pwcs_getParsedProductNew($merchant_id, $api_key='')
{
	$target_url =  PWCS_PWC_CORE_URL . variable_get('pwcs_target_url_productlist', 'testapi/productListByLimit.html');
	$target_url = 'https://www.secureinfossl.com/testapi/productListByLimit.html';
	pwcs_debug_file_write('productList URL :'.$target_url);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$request = 'merchantid='    . urlencode($merchant_id);
	$request.= '&api_signature=' . urlencode($api_key);
	$request.= '&limitstart=0';
	$request.= '&limitend=1000';
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch))
	{
//		print "<pre>\n";
//		print_r(curl_getinfo($ch));  // get error info
//		echo 'CURL ERROR NO : ', curl_errno($ch);
//		echo '<br>CURL ERROR: ', curl_error($ch);
//		print "</pre>\n";
		print "<b>Sorry, We could Not Connect to PremiumWebCart</b>";

	}
	else
	{
		  curl_close($ch);
	}

	return $response;
}