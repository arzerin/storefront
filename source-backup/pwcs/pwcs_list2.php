<?php
/* $Id: pwcs_category.php,v 1.0 2010/08/23 Rasel Kabir Shuvro Exp $ */

/**
 * Site Admin Category page theme
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_listings($action=NULL, $listing_id=NULL) {

	if ($action=='delete'){
		if($listing_id!=NULL){
			pwcs_listing_delete($listing_id);
			pwcs_update_product_listing();
		}
	}

	if ($action=='edit'){
		if($listing_id!=NULL){
			return drupal_get_form('pwcs_listing_form', $listing_id);
		}
	}
	pwcs_process_noncorelistings();
	$total_category = pwcs_listings_count();

	$output = '';
	// List of all Listings
	$output .= '<div class="pwcs-page-content-inner">';
	if ($total_category>0)
	$output .= '<h3>'.t('Total '.$total_category. ' Listing(s) Found').'</h3>';
	else
	$output .= '<h3>'.t('Total '.$total_category. ' Listing Found').'</h3>';
	
	$output .= '<div id="category_list" class="pwcs-content-list">';
	$output .= drupal_get_form('pwcs_listing_sort_form');
	$output .= '</div>';
	$output .= '</div>';
	pwcs_make_order_table();
	return $output;
}

/**
 * Category list with drag and drop sorting [List as a form]
 * Added by Rasel Kabir 23 September 2010
 */
function pwcs_listing_sort_form() {
	
	global $base_url;
	
	$form = array();
	$form['#theme'] = 'pwcs_listing_sort_form';
	$category_array = pwcs_get_listings_array(0);
	$form = _pwcs_listing_sort_form($category_array);

	if (element_children($form)) {
		$form['submit'] = array(
			'#type' => 'submit',
			'#value' => t('Save Order'),
		);
	}
	else {
		$form['empty_menu'] = array('#value' => t('There are no listing items yet.'));
	}

	return $form;
}
/**
 * Implementation of category_form()
 * Added by Rasel Kabir
 */
function pwcs_listing_form($form_info = array(), $pwc_category_id = NULL,$pwc_refer_category_id = NULL) {

    if ($pwc_category_id=="-1")
	    $pwc_category_id =NULL;


	// SQL to get the current $list_id if exists
	if ((isset($pwc_category_id)) && ($pwc_category_id>0)) {
		$results = db_query("SELECT * FROM {pwcs_listings} WHERE `category_id` = %d", $pwc_category_id);
		while($result = db_fetch_object($results)) {
			$category = (array) $result;
		}
	}

	$weight=0;
	// pwc_category_id hidden
	if ($pwc_category_id!=NULL) {
		$form['category_id'] = array(
			'#type' => 'hidden',
			'#weight' => $weight++,
			'#value' => $pwc_category_id,
		);
		$form['pwc_category_id'] = array(
			'#type' => 'hidden',
			'#weight' => $weight++,
			'#value' => ($pwc_category_id!=NULL) ? $category['pwc_category_id'] : '',
		);
	}
	$form['category_title'] = array(
		'#type' => 'textfield',
		'#title' => t('Listing Name'),
		'#default_value' => ($pwc_category_id!=NULL) ? $category['category_title']:'',
		'#weight' => $weight++,
	);

	$categories = array('0'=>'<'.t('Parent Listing').'>');
	$categories = $categories+pwcs_listing_array_selection_combo();
	
	if ($pwc_category_id!=NULL)
	{
		$form['parent_category_id'] = array(
			'#type' => 'select',
			'#options' => $categories,
			'#default_value' => $category['parent_category_id'],
			'#title' => t('Parent Listing'),
			'#weight' => $weight++,
		);
	}else if ($pwc_refer_category_id!=NULL){
		$form['parent_category_id'] = array(
			'#type' => 'select',
			'#options' => $categories,
			'#default_value' => $pwc_refer_category_id,
			'#title' => t('Parent Listing'),
			'#weight' => $weight++,
		);
	}else{
		$form['parent_category_id'] = array(
			'#type' => 'select',
			'#options' => $categories,
			'#title' => t('Parent Listing'),
			'#weight' => $weight++,
		);
	}
	$listing_options = array('0'=>t('Listing'),'1'=>t('External Web Link'),'2'=>t('Site Page / Internal Link'));
	$form['listing_type'] = array(
			'#type' => 'select',
	 		'#id' => 'listing_type',
			'#options' => $listing_options,
			'#default_value' => ($pwc_category_id!=NULL) ? $category['category_type']:0,
			'#title' => t('Type'),
			'#weight' => $weight++,
	);
				
    $form['listing_link'] = array(
		'#type' => 'textfield',
		'#title' => t('Link to External Website'),
		'#default_value' => ($pwc_category_id!=NULL) ? $category['weblink']: t('http://'),
		'#description' => t('Please type the URL where End User will be taken when they will click on the Listings from Front Side'),
   		'#prefix' => '<div id="my_external_weblink">', 
  		'#suffix' => '</div>',     
		'#weight' => $weight++,
	);	
	
	$browser_open_options = array('1'=>t('New Window'),'0'=>t('In this Window'));
	$form['browser_open_type'] = array(
			'#type' => 'select',
	 		'#id' => 'browser_open_type',
			'#options' => $browser_open_options,
			'#default_value' => ($pwc_category_id!=NULL) ? $category['weblink_type']:0,
			'#title' => t('Browser Open Settings'),
   			'#prefix' => '<div id="my_weblink_type">', 
  			'#suffix' => '</div>',			
			'#weight' => $weight++,
	);	
	
	$page_listing_array= pwcs_advancepages_resultset();
	
	$listing_array=pwcs_listing_option_values();

    $form['listing_page'] = array(
		'#type' => 'radios',
		'#options' => $page_listing_array,		
		'#title' => t('Link to Pages'),
    	'#default_value' => ($pwc_category_id!=NULL) ? $category['pagelink'] : '',
		'#size' => 20,
		'#weight' => $weight++,
   		'#prefix' => '<div id="my_listing_pages">', 
  		'#suffix' => '</div>',    
	);

	
	$button_title = ($pwc_category_id==NULL)?t('Create New Listing'):t('Update Listing');
	$form['button'] = array(
		'#type' => 'submit',
		'#weight' => $weight++,
		'#default_value' => $button_title
	);
	
	$js='$(function() {
			';
			if ($pwc_category_id!=NULL) {
				if ($category['category_type']==1){
					$js.='$("div#my_external_weblink").show();$("div#my_listing_pages").hide();$("div#my_weblink_type").show();';	
				}
				else if ($category['category_type']==2){
					$js.='$("div#my_listing_pages").show();$("div#my_external_weblink").hide();$("div#my_weblink_type").hide();';	
				}else {
					$js.='$("div#my_listing_pages").hide();$("div#my_external_weblink").hide();$("div#my_weblink_type").hide();';	
				}
			}else{
			$js.='$("div#my_listing_pages").hide();$("div#my_external_weblink").hide();$("div#my_weblink_type").hide();';	
				
			}
			$js.='
			//alert($("#listing_type option:selected").text());
			$("#listing_type").change(
				function() { 
					if ($("#listing_type option:selected").val()=="2"){
						$("div#my_listing_pages").show();
						$("div#my_external_weblink").hide();
						$("div#my_weblink_type").hide();
					}	
					else if ($("#listing_type option:selected").val()=="1"){
						$("div#my_external_weblink").show();
						$("div#my_listing_pages").hide(); 
						$("div#my_weblink_type").show();
					}							
					else{ 
						$("div#my_listing_pages").hide();
						$("div#my_external_weblink").hide(); 
						$("div#my_weblink_type").hide();
					}
				}
			);
		});';
	drupal_add_js($js, 'inline', 'footer');
	
	return $form;
}

/**
 * Category form submit
 * Added by Rasel Kabir
 */
function pwcs_listing_form_submit($form_id, $form_values)
{
	global $user;

	if($form_values[submitted] == 1) {

		// Update Listing
		if (isset ($form_values['clicked_button']['#post']['category_id']) &&  ($form_values['clicked_button']['#post']['category_id']!= NULL)) {

			$category['pwc_category_id']= $form_values['clicked_button']['#post']['pwc_category_id'];
			$category['category_id']= $form_values['clicked_button']['#post']['category_id'];
			$category['parent_category_id']= $form_values['clicked_button']['#post']['parent_category_id'];
		    $category['category_type']      = $form_values['clicked_button']['#post']['listing_type'];

			$category['category_title']= $form_values['clicked_button']['#post']['category_title'];
			$category['listing_type']= $form_values['clicked_button']['#post']['listing_type'];
			$category['listing_link']= $form_values['clicked_button']['#post']['listing_link'];
			
			$category['browser_open_type']  = isset($form_values['clicked_button']['#post']['browser_open_type']) ? $form_values['clicked_button']['#post']['browser_open_type'] : '';
			$category['pagelink']           = isset($form_values['clicked_button']['#post']['listing_page']) ? $form_values['clicked_button']['#post']['listing_page'] : 0;	
			
			$category['menu_name']          =	'';
			$category['menu_path']          =	'';			
			$category['modified_on'] = time();
			//die($category['pwc_category_id']);
			if (db_result(db_query("SELECT `category_title` FROM {pwcs_listings} WHERE `category_title` = '%s' AND `category_id` != %d", $category['category_title'], $category['category_id'])) == $category['category_title']) {
				//drupal_set_message(t('Sorry').'! '.$category['category_title'].' '.t(' already exists'), 'error');
			} else {
				
			// SQL to get the current $list_id if exists
			if ((isset($category['category_id'])) && ($category['category_id']>0)) {
				$results = db_query("SELECT * FROM {pwcs_listings} WHERE `category_id` = %d", $category['category_id']);
				while($result = db_fetch_object($results)) {
					$old_category = (array) $result;
				}
			}
			
			
				
				
				if ($category['category_type']==2)
				{
					$node_result = pwcs_advancepages_mlid_resultset($category['pagelink']);
					$tree        = menu_tree_data($node_result);
	
					$node_links  = array();
					$node        = array();
					menu_tree_collect_node_links($tree, $node_links);
					menu_tree_check_access($tree, $node_links);
					
					$node_id = 0;
					foreach($node_links as $nid=>$menu_link_array)
					{
						$node_id=$nid;
						$node = node_load($nid);
						$category['menu_name']  =	$node->title;
						$category['menu_path']  =	$node->path;
					}				
				}		
				pwcs_listing_edit($category);
				
				//Sync req to PWC CORE
				if (($old_category['category_type']!=$category['category_type']) && ($category['category_type']==0))
					$api_data_category['pwc_category_id'] = 0;
				else
				    $api_data_category['pwc_category_id'] = $category['pwc_category_id'];
				
			    
			    $api_data_category['parent_category_id'] = $category['parent_category_id'];
			    $api_data_category['category_title'] = $category['category_title'];
			    $api_data_category['category_level'] = '';
			    //$api_data_category['prev_parent_cateogry_id'] = ;
			    if($category['category_type']==0){
				if($user->merchant_enc_id != NULL) {
					if ($category['pwc_category_id'] != -1 )
					{
						$responce_xml = pwcs_api_insertCategoryAPI($user->merchant_enc_id, $api_data_category);

						if (module_exists('xml_parser')){
							if ($responce_xml != "error"){
								$responce_xml = xml_clean($responce_xml);
								$responce_xml = xml_parser_string_to_array($responce_xml);
							}
						}else{
						}
					    if ($responce_xml != "error"){

							if( isset($responce_xml['categories']['category'])) {
								$cat_sync = 1;
								db_query("UPDATE {pwcs_listings} SET `is_sync` = %d, `modified_on` = %d WHERE pwc_category_id = %d", $cat_sync , $category['modified_on'], $category['pwc_category_id'] );
								drupal_set_message(t('Listing Update Notification sent to Premium Web Cart'));
							}else{
								drupal_set_message(t('Could Not Send Listing Update Notification to Premium Web Cart as part of Synchronization'), 'error');
							}
					    }else {
							drupal_set_message(t('Could Not Send Listing Update Notification to Premium Web Cart as part of Synchronization'), 'error');
	            		}
					}
				}
			    }

			}

		// Add New Listing
		} else {

			$category['category_title']     = $form_values['values']['category_title'];
			$category['category_type']       = $form_values['values']['listing_type'];
			$category['parent_category_id'] = $form_values['values']['parent_category_id'];
			$category['weblink']            = isset($form_values['values']['listing_link']) ? $form_values['values']['listing_link'] : '';
			$category['browser_open_type']  = isset($form_values['values']['browser_open_type']) ? $form_values['values']['browser_open_type'] : '';
			$category['pagelink']           = isset($form_values['values']['listing_page']) ? $form_values['values']['listing_page'] : 0;	
			$category['menu_name']          =	'';
			$category['menu_path']          =	'';
			$category['created_on']         = time();
			$category['weight']             = db_last_insert_id('pwcs_listings', 'weight') + 1;


			if (db_result(db_query("SELECT `category_title` FROM {pwcs_listings} WHERE `category_title` = '%s'", $category['category_title'])) == $category['category_title']) {

				drupal_set_message(t('Sorry').'! '.$category['category_title'].' '.t('is already added'), 'error');
					if (user_access('is siteadmin')){
						$_REQUEST[destination] = 'siteadmin/listings';
					}

			} else {
		
						
				if ($category['category_type']==2)
				{
					$node_result = pwcs_advancepages_mlid_resultset($category['pagelink']);
					$tree        = menu_tree_data($node_result);
	
					$node_links  = array();
					$node        = array();
					menu_tree_collect_node_links($tree, $node_links);
					menu_tree_check_access($tree, $node_links);
					
					$node_id = 0;
					foreach($node_links as $nid=>$menu_link_array)
					{
						$node_id=$nid;
						$node = node_load($nid);
						$category['menu_name']  =	$node->title;
						$category['menu_path']  =	$node->path;
					}				
				}
				
	

				$category_id = pwcs_listing_add($category);
				//Sync req to PWC CORE
			    //$api_data_category['category_id'];
			    $api_data_category['category_title'] = $category['category_title'];
			    $api_data_category['parent_category_id'] = $category['parent_category_id'];
			    $api_data_category['category_level'] = 1;
			    $api_data_category['pwc_category_id'] = 0;
			    
			    if($category['category_type']==0){
			    if($user->merchant_enc_id != NULL) {
					if ($category['pwc_category_id'] != -1 )
					{
						$response_xml = pwcs_api_insertCategoryAPI($user->merchant_enc_id, $api_data_category);
						drupal_set_message(t('Listing Update Notification Request sent to Premium Web Cart.'));
						if (module_exists('xml_parser')){
							if ($response_xml != "error"){
								$response_xml = xml_clean($response_xml);
								$response_xml = xml_parser_string_to_array($response_xml);
							}
						}else{

						}

						if ($response_xml != "error"){
							if( isset($response_xml['categories']['category'])) {
								$cat_sync = 1;
								
								db_query("UPDATE {pwcs_listings} SET `pwc_category_id` = %d, `parent_category_id` = %d, `is_editable` = %d, `is_sync` = %d WHERE category_id = %d",
										$response_xml['categories']['category']['categoryid'],
										$response_xml['categories']['category']['parentcategoryid'],
										$response_xml['categories']['category']['iseditable'],
										$cat_sync,
										$category_id
								);
								

							}
						}else {
							drupal_set_message(t('Could Not Send Category Update Notification TO Premium Web Cart as part of Synchronization'), 'error');
						}
					}			    	
			    }
			    }else{
			    }


			}

		}

		if (user_access('is siteadmin')){
			$_REQUEST[destination] = 'siteadmin/listings';
		}
	}
	else {
		drupal_set_message(t('Oooops! unfortunate error'), 'error');
	}
}

/**
 * Implementation of pwcs_listing_add()
 * Added by Rasel Kabir
 */
function pwcs_listing_add($category) {

	
	if($category['category_type']==1){
		$category['pagelink'] = 0;

	}
	
	if($category['category_type']==2){
		$category['weblink'] = '';	
		$category['menu_name']  =	isset($category['menu_name']) ? $category['menu_name'] : '';
		$category['menu_path']  =	isset($category['menu_path']) ? $category['menu_path'] : '';
	}else{
		$category['menu_name']  =	'';
		$category['menu_path']  =	'';
	}	
	
	$result = db_query("INSERT INTO {pwcs_listings}" .
			" (`pwc_category_id`, `category_title`,`parent_category_id`, `category_type`, `is_editable`, `weblink`,`weblink_type`, `pagelink`, `menu_name`, `menu_path`, `weight`,`created_on`)" .
			" VALUES (%d, '%s', %d, %d, %d,'%s', %d, %d, '%s','%s', %d, %d)",
			time(), $category['category_title'], $category['parent_category_id'], $category['category_type'], 1, $category['weblink'], $category['browser_open_type'], $category['pagelink'], $category['menu_name'], $category['menu_path'], $category['weight'], $category['created_on']
	);

	if (db_last_insert_id('pwcs_listings', 'category_id')>0)
	drupal_set_message(t('New Listing has been Added Successfully!'));

	return db_last_insert_id('pwcs_listings', 'category_id');

}


/**
 * Implementation of pwcs_listing_edit()
 * Added by Rasel Kabir
 */
function pwcs_listing_edit($category) {

	//echo "<pre>";
	//print_r($category);
	//echo "</pre>";
	//die('Here update clause');

	if (($category['parent_category_id'] == $category['category_id']) && ($category['category_id']!=0)){
		drupal_set_message(t('Sorry! category itself cant be its parent category.'), 'error');
		drupal_goto('siteadmin/categories');
	}

//	db_query("UPDATE {pwcs_category}
//		SET `category_title`='%s', `parent_category_id`= %d, `modified_on`=%d WHERE pwc_category_id = %d",
//		$category['category_title'], $category['parent_category_id'], $category['modified_on'], $category['pwc_category_id']
//	);

	if($category['category_type']==1){
		$category['pagelink'] = 0;

	}
	
	if($category['category_type']==2){
		$category['weblink'] = '';	
		$category['menu_name']  =	isset($category['menu_name']) ? $category['menu_name'] : '';
		$category['menu_path']  =	isset($category['menu_path']) ? $category['menu_path'] : '';
	}else{
		$category['menu_name']  =	'';
		$category['menu_path']  =	'';
	}	


	$result = db_query("UPDATE {pwcs_listings}" .
			" SET `category_title`='%s', `category_type`=%d, `weblink`='%s', `weblink_type`=%d,`pagelink`=%d,`menu_name`='%s',`menu_path`='%s' " .
			" WHERE category_id = %d",
			$category['category_title'], $category['category_type'], $category['listing_link'], $category['browser_open_type'], $category['pagelink'], $category['menu_name'], $category['menu_path'], $category['category_id']
	);

	

	drupal_set_message(t('Listing has been Updated.'));
}

/**
 * Implementation of pwcs_listing_delete()
 * Added by Rasel Kabir
 */
function pwcs_listing_delete($category_id = NULL) {

	if ($category_id > 0) {
		db_query("DELETE FROM {pwcs_listings} WHERE category_id = %d", $category_id);
		drupal_set_message(t(PWCS_LISTING_NAME.' Item has been Deleted Successfully!'));
		drupal_goto('siteadmin/listings');
	} else {
		drupal_set_message(t(PWCS_LISTING_NAME.' cann\'t be deleted'), 'error');
	}
}

/**
 * @parm $view - particular category id
 * @return view of that category
 */
//function theme_pwcs_category_view($pwc_category_id) {
//	$output = '';
//	return $output;
//}

/**
 * category list in an array [non-recursive]
 * Added by Rasel Kabir
 */
function pwcs_listing_array($parent_category_id = NULL)
{
	$cat_array = array();
	$sql = "SELECT * FROM {pwcs_category} pc";
	$sql .= " WHERE pc.status = 1";
	if ($parent_category_id != NULL)
		$sql .= " AND pc.parent_category_id = ".$parent_category_id;
	$sql .= " ORDER BY pc.category_id, pc.category_title";
	//$sql .= " LIMIT 0,".$amount."";

	$results = db_query($sql);
	$cat_array[PWCS_CATEGORY_DEFAULT_VALUE] = PWCS_CATEGORY_DEFAULT_NAME;
	while($result = db_fetch_object($results)) {
		$cat_array[$result->pwc_category_id] = check_plain($result->category_title);
	}

	return $cat_array;
}

/**
 * Get Category As a Array for selection combo [recursive]
 * @param parent_category_id
 * @return ARRAY pattern of category with sub category
 * Added by Rasel Kabir
 */
function pwcs_listing_array_selection_combo($parent=0, $spacer=' ') {

	$sql  = "SELECT pc.*";
	$sql .= " FROM {pwcs_listings} pc";
	$sql .= " WHERE pc.parent_category_id = ".$parent;
	$sql .= " ORDER BY pc.weight ASC, pc.pwc_category_id";

	$num = pwcs_result_count($sql);
	$results = db_query($sql);

	$category_array = array();
	while($result = db_fetch_object($results)) {
		$category_array[$result->pwc_category_id] = $spacer.$result->category_title;
	    if(!(($result->pwc_category_id == 0) && ($result->parent_category_id == 0))){
		    $category_array = $category_array+pwcs_listing_array_selection_combo($result->pwc_category_id, '--'.$spacer);
	    }
	}

	return $category_array;
}
function pwcs_listing_array_list() {

	$sql  = "SELECT pc.*";
	$sql .= " FROM {pwcs_listings} pc";
	$sql .= " ORDER BY pc.weight ASC, pc.pwc_category_id";

	$num = pwcs_result_count($sql);
	$results = db_query($sql);

	$listing_array = array();
	while($result = db_fetch_object($results)) {
		$listing_array[] = array('list-id'=>$result->pwc_category_id,'list-name'=>$result->category_title);
	
	}

	return $listing_array;
}
function pwcs_listing_option_values() {

	$sql  = "SELECT pc.*";
	$sql .= " FROM {pwcs_listings} pc WHERE category_type=0";

	$sql .= " ORDER BY pc.weight ASC, pc.pwc_category_id";

	$num = pwcs_result_count($sql);
	$results = db_query($sql);

	$listing_array = array();
	while($result = db_fetch_object($results)) {
		$listing_array[$result->pwc_category_id ] = t($result->category_title);
	
	}

	return $listing_array;
}

/**
 * PWCS category menu block for end user
 * Added by Rasel Kabir
 */
function pwcs_listing_menu(){

	$output = pwcs_listings_menu_tree(0);
	return $output;
}

/**
 * Recurssive category output.
 * Added by Rasel Kabir
 */
function pwcs_listings_menu_tree($parent='0') {

	global $base_url;

	$sql = "SELECT COUNT(ppl.pwc_product_row_id) as product_amount, ppl.pwc_product_row_id, pl.category_title, pl.pwc_category_id, pl.*";
	$sql .= " FROM {pwcs_listings} pl";
	$sql .= " LEFT JOIN {pwcs_product_listing} ppl ON pl.pwc_category_id = ppl.listing_id";
	//$sql .= " WHERE pl.parent_category_id = ".$parent." AND pl.status=1  AND ppl.pwc_product_row_id>0 ";
	$sql .= " WHERE pl.parent_category_id = ".$parent." AND pl.status=1 ";
	$sql .= " GROUP BY pl.pwc_category_id";
	$sql .= " ORDER BY pl.weight ASC, pl.category_title ASC, pl.category_id";
	
	//pwcs_update_listing_table();
	//die('Hello');
	
	$num = pwcs_result_count($sql);
	$results = db_query($sql);

	if($num==0) {
		return '';
	}

	//$spacer .= '- ';
	$output = '';
	$output .= '<ul class="pwcs-category-menu flyoutmenu">';
	while($result = db_fetch_object($results)) {

		if($num==0 || $result->parent_category_id=='0') {
		  //$spacer = '';
		}
		$output .= '<li>';
		//$output .= '<span>'.$spacer;
		
		if ($result->category_type == 0)
		{
			if ($result->product_amount > 0)
				$output .= l( $result->category_title.' ('.$result->product_amount.')', 'products/all/all/'.$result->pwc_category_id, array('attributes' => array('class'=>'pwcs-cat-menu-item', 'id'=>'pwcs-cat-'.$result->pwc_category_id)));
			else
				$output .= l( $result->category_title.' (0)', 'products/all/all/'.$result->pwc_category_id, array('attributes' => array('class'=>'pwcs-cat-menu-item no-product', 'id'=>'pwcs-cat-'.$result->pwc_category_id)));
		}	
		else if ($result->category_type == 1)
		{
			if ($result->weblink_type==1)
				$output .= l( $result->category_title, $result->weblink, array('attributes' => array('class'=>'pwcs-cat-menu-item no-product', 'id'=>'pwcs-cat-'.$result->pwc_category_id, 'target'=> '_blank')));
			else
				$output .= l( $result->category_title, $result->weblink);
				
		}
		else if ($result->category_type == 2)
		{
				$output .= l( $result->category_title, $base_url.'/'.$result->menu_path, array('attributes' => array('class'=>'pwcs-cat-menu-item no-product', 'id'=>'pwcs-cat-'.$result->pwc_category_id, 'target'=> '_blank')));
		}				
		//$output .= '</span>';
	    if(!(($result->pwc_category_id == 0) && ($result->parent_category_id == 0))){
		    $output .= pwcs_listings_menu_tree($result->pwc_category_id); //Recurrsion
	    }
		$output .='</li>';
	}
	$output .='</ul>';

	return $output;
}

/**
 * Get Category As a Array
 * @param parent_category_id
 * @return ARRAY pattern of category with sub category
 * Added by Rasel Kabir 23 September 2010
 */
function pwcs_get_listings_array($parent=0, $depth=1) {

	$sql  = "SELECT pc.*, COUNT(ppl.pwc_product_row_id) AS total_product ";
	$sql .= " FROM {pwcs_listings} pc";
	$sql .= " LEFT JOIN {pwcs_product_listing} ppl ON pc.pwc_category_id = ppl.listing_id";	
	$sql .= " LEFT JOIN {pwcs_product} pp  ON ppl.pwc_product_row_id=pp.pwc_product_row_id";
	$sql .= " WHERE pc.parent_category_id = ".$parent;
	$sql .= " GROUP BY pc.pwc_category_id";
	$sql .= " ORDER BY pc.weight ASC, pc.category_title ASC, pc.category_id";


	$results = db_query($sql);
	

	$category_array = array();
	while($result = db_fetch_object($results)) {
		$category_array[$result->pwc_category_id] = (array)$result;
		$category_array[$result->pwc_category_id]['category_id'] = $result->category_id;		
		$category_array[$result->pwc_category_id]['depth'] = $depth;
		
		if ($parent==0)
			$category_array[$result->pwc_category_id]['parent'] = 1;
		else
			$category_array[$result->pwc_category_id]['parent'] = 0;
		
		$category_array[$result->pwc_category_id]['is_editable'] = $result->is_editable;
		$category_array[$result->pwc_category_id]['products_no'] = $result->total_product;
		$category_array[$result->pwc_category_id]['status'] = $result->status;

	    if(!(($result->pwc_category_id == 0) && ($result->parent_category_id == 0))){
		    $temp = pwcs_get_listings_array($result->pwc_category_id, $depth+1);
		    if(!empty($temp)) $category_array[$result->pwc_category_id]['child'] = $temp; //Recurrsion
	    }
	}
	
	return $category_array;
}



/**
 * Category list with drag and drop sorting [Building form in recurssive way]
 * Added by Rasel Kabir 23 September 2010
 */
function _pwcs_listing_sort_form($category_array) {

	global $base_url;

	static $form = array('#tree' => TRUE);
	foreach($category_array as $pwc_category_id => $category_item) {

		$form[$pwc_category_id]['#item']['depth'] 	  = $category_item['depth'];
		if ($category_item['category_type']==1)
         $form[$pwc_category_id]['title']			  = array('#value' => $category_item['category_title'].t(' ').l(t('[web]'),$category_item['weblink'],array( 'attributes' => array('target' => '_blank',))));
		else
		 $form[$pwc_category_id]['title']			  = array('#value' => $category_item['category_title']);
		
		$form[$pwc_category_id]['weight']			  = array('#type' => 'weight', '#delta' => 50, '#default_value' => isset($form_state[$pwc_category_id]['weight']) ? $form_state[$pwc_category_id]['weight'] : $category_item['weight']);
		$form[$pwc_category_id]['pwc_category_id'] 	  = array('#type' => 'hidden', '#value' => $pwc_category_id );
		$form[$pwc_category_id]['parent_category_id'] = array('#type' => 'textfield', '#default_value' => isset($form_state[$pwc_category_id]['parent_category_id']) ? $form_state[$pwc_category_id]['parent_category_id'] : $category_item['parent_category_id'], '#size' => 6 );
	
		if ($category_item['category_type']==1)
		{
			$form[$pwc_category_id]['listtype']			  = array('#value' => t('WEB LINK'));
			$form[$pwc_category_id]['products']			  = array('#value' => t('N/A'));
		}
		else if ($category_item['category_type']==2){
			$form[$pwc_category_id]['listtype']			  = array('#value' => t('SITE PAGE LINK'));
			$form[$pwc_category_id]['products']			  = array('#value' => t('N/A'));
		}
		else{
			$form[$pwc_category_id]['listtype']			  = array('#value' => t('LISTING'));
			$form[$pwc_category_id]['products']			      = array('#value' => t($category_item['products_no']));	
		}
		
		if ($category_item['category_type']==1){ 
			$form[$pwc_category_id]['urllink']			  = array('#value' => l(t($category_item['weblink']),$category_item['weblink'],array( 'attributes' => array('target' => '_blank',))));
		}
		else if ($category_item['category_type']==2){ 
			$form[$pwc_category_id]['urllink']			  = array('#value' => l(t($category_item['menu_path']),$base_url.'/siteadmin/preview/'.$category_item['menu_path'],array( 'attributes' => array('target' => '_blank',))));
		}
		else 
			$form[$pwc_category_id]['urllink']			  = array('#value' => t(''));
			
		$form[$pwc_category_id]['status']			      = array('#value' => t($category_item['status']));
		if($category_item['status']=='1')
		$form[$pwc_category_id]['status']			      = array('#value' => '<input type="checkbox" id="" name="listid['.$category_item['category_id'].']" value="1" checked=checked />');
		else
		$form[$pwc_category_id]['status']			      = array('#value' => '<input type="checkbox" id="" name="listid['.$category_item['category_id'].']" value="0" />');
		
						
		//Only Editable List will have the Options
		if ($category_item['category_type']==0)
		{
			if (($category_item['is_editable']==1) || ($category_item['pwc_category_id']>0))
			{
				if ($category_item['products_no']>0)
					$form[$pwc_category_id]['action']	= array('#value' => l(t('Product Order'),'siteadmin/product-order/'.$category_item['pwc_category_id']).t(' | ').l(t('Delete'),'siteadmin/listings/delete/'.$category_item['category_id']));
				else{
					
					if (($category_item['parent']) && $category_item['child'])
					$form[$pwc_category_id]['action']	= array('#value' => t('Product Order').t(' | ').l(t('Update Description'),'siteadmin/product-order/'.$category_item['category_id']).t(' | ').t('<i>Delete</i>'));
					else
					$form[$pwc_category_id]['action']	= array('#value' => t('Product Order').t(' | ').l(t('Update Description'),'siteadmin/listings/edit/'.$category_item['category_id']).t(' | ').l(t('Delete'),'siteadmin/listings/delete/'.$category_item['category_id']));
				}	
				
			}
			else
			{
				$form[$pwc_category_id]['action']	= array('#value' =>  l(t('Product Order'),'siteadmin/product-order/'.$category_item['pwc_category_id']).t(' | ').l(t('Update Description'),'siteadmin/product-order/'.$category_item['category_id']).t(' | ').t('<i>Delete</i>'));
			}
		}else {
			$form[$pwc_category_id]['action']	= array('#value' => l(t('Update Description'),'siteadmin/listings/edit/'.$category_item['category_id']).t(' | ').l(t('Delete'),'siteadmin/listings/delete/'.$category_item['category_id']));
		}
		

		if($category_item['child']) {
			_pwcs_listing_sort_form($category_item['child']);
		}
	}
	return $form;
}

/**
 * Category list with drag and drop sorting [Table Theme]
 * Added by Rasel Kabir 24 September 2010
 */
function theme_pwcs_listing_sort_form($form) {

  drupal_add_tabledrag('categorylist_sort', 'match', 'parent', 'cat_parent_category_id', 'cat_parent_category_id', 'cat_pwc_category_id', TRUE, 100);
  drupal_add_tabledrag('categorylist_sort', 'order', 'sibling', 'cat_weight');

  $header = array(
    t(''),
    t('No.'),
    t('Description'),
    t('Weight'),
	t('Type'),
    t('URL Link'),
    t('Product Count'),
  	t('Visible'),    
    t('Action'),
  );

  $rows = array();
  $i=0;	
  foreach (element_children($form) as $pwc_category_id) {

    if(isset($form[$pwc_category_id]['title'])) {
    //if (isset($form[$pwc_category_id]['hidden'])) {
      $element = &$form[$pwc_category_id];

      // Add special classes to be used for tabledrag.js.
      $element['parent_category_id']['#attributes']['class'] = 'cat_parent_category_id';
      $element['pwc_category_id']['#attributes']['class'] = 'cat_pwc_category_id';
      $element['weight']['#attributes']['class'] = 'cat_weight';
      // Change the parent field to a hidden. This allows any value but hides the field.
      $element['parent_category_id']['#type'] = 'hidden';

	  $i = $i+1;
      $row = array();
      $row[] = array('data' => '', 'width'=>'2px');
      $row[] = array('data' =>  $i, 'width'=>'10px');
      $row[] = theme('indentation', $element['#item']['depth'] - 1) . drupal_render($element['title']);
      $row[] = drupal_render($element['weight']) . drupal_render($element['parent_category_id']) . drupal_render($element['pwc_category_id']);
	  $row[] = drupal_render($element['listtype']);
	  $row[] = drupal_render($element['urllink']);
	  $row[] = drupal_render($element['products']);

	  $row[] = drupal_render($element['status']);
	  $row[] = drupal_render($element['action']);
      $row = array('data' => $row);
      //$row = array_merge(array('data' => $row), $element['#attributes']);
      $row['class'] = !empty($row['class']) ? $row['class'] .' draggable' : 'draggable';
      $rows[] = $row;
    }
  }
  $output = '';
  if ($rows) {
    $output .= theme('table', $header, $rows, array('id' => 'categorylist_sort'));
  }
  $output .= drupal_render($form);
  return $output;
}

/**
 * Listing list with drag and drop sorting [After Save/Submit]
 * Added by ZERIN 28 january 2011
 */
function pwcs_listing_sort_form_submit($form, $formid) {

	global $user;

	if($form['submit']['#type'] == 'submit') {
			$active_listing_id='';
			$i=0;
			foreach ($form['submit']['#post']['listid'] as $my_list_id=> $value){
				$i++;
				if($i!=count($form['submit']['#post']['listid']))
				$active_listing_id .= $my_list_id.',';
				else
				$active_listing_id .= $my_list_id;
				//echo 'Here'.$my_list_id;
			}
		$sql = "UPDATE {pwcs_listings} SET status = '1'";
		db_query($sql,$active_listing_id);
		$sql = "UPDATE {pwcs_listings} SET status = '0'  WHERE category_id NOT IN (%s)";
		db_query($sql,$active_listing_id);
							
		foreach ($form['submit']['#post'] as $pwc_category_id=> $value){

			if ((is_numeric($value['pwc_category_id'])) && ($value['pwc_category_id']==0)){
				pwcs_process_noncorecatlistings2($value['weight']);

			}
			if ((is_numeric($value['pwc_category_id'])) && ($value['pwc_category_id']>0)){
				$cat_sync = 0;
				$sql = "UPDATE {pwcs_listings} SET weight = '". $value['weight']."', parent_category_id = '".$value['parent_category_id']."' WHERE pwc_category_id = ".$value['pwc_category_id']."";
				//drupal_set_message(t($sql));
				db_query($sql);
			}
		}//foreach ($form['submit']['#post'] as $pwc_category_id=> $value)


		drupal_set_message(t('Sorting Order for the '.PWCS_LISTING_NAME.' has been Saved Successfully.'));
		
		
	} else {
		drupal_set_message(t('Sorting Order could not be Saved. May be nothing to Save.'));
	}//if-else($form['submit']['#type'] == 'submit')

}
function pwcs_process_noncorelistings()
{
	$sql = "UPDATE {pwcs_category} SET parent_category_id=0 WHERE pwc_category_id = 0 AND is_editable = 0";
	$results = db_query($sql);
}
function pwcs_process_noncorecatlistings2($weight=0)
{
	$sql = "UPDATE {pwcs_category} SET weight = '".$weight."' WHERE pwc_category_id = 0 AND is_editable = 0";
	$results = db_query($sql);
	//drupal_set_message(t($sql));
}

/**
 * Get Category Details of a category
 * @param pwc_category_id
 * @return category array
 * Added by Rasel Kabir
 */
function pwcs_clistings_details($pwc_category_id) {

	$sql  = "SELECT * FROM {pwcs_category} pc WHERE pc.pwc_category_id = ".$pwc_category_id;
	$results = db_query($sql);
	$category_array = array();
	while($result = db_fetch_object($results)) {
		$category_array = (array)$result;
	}
	return $category_array;
}
function pwcs_listings_product_count($pwc_category_id) {
	$countrows = db_result(db_query("SELECT COUNT(*) FROM {pwcs_product} WHERE pwc_category_id='%d'",$pwc_category_id));
	return ($countrows > 0) ? $countrows : 0;
}
function pwcs_listings_count() {
	$countrows = db_result(db_query("SELECT COUNT(*) FROM {pwcs_listings}"));
	return ($countrows > 0) ? $countrows : 0;
}
function pwcs_advancepages_resultset()
{
	
    $sql = "
			    SELECT   ml.*
			    FROM {menu_links} ml 
			    WHERE ml.menu_name = '%s'
			    ORDER BY ml.weight ASC, p1 ASC, p2 ASC, p3 ASC, p4 ASC, p5 ASC, p6 ASC, p7 ASC, p8 ASC, p9 ASC";
    $results = db_query($sql,'primary-links');
    
	$listing_array = array();
	while($result = db_fetch_object($results)) {
		$listing_array[$result->mlid ] = t($result->link_title);
	
	}
	return $listing_array;    
}
function pwcs_advancepages_mlid_resultset($mlid = 0)
{
	
    $sql = "
			    SELECT m.load_functions, m.to_arg_functions, m.access_callback, m.access_arguments, m.page_callback, m.page_arguments, m.title, m.title_callback,
			           m.title_arguments, m.type, m.description, ml.*
			    FROM {menu_links} ml LEFT JOIN {menu_router} m ON m.path = ml.router_path
			    WHERE ml.menu_name = '%s' AND mlid = %d
			    ORDER BY ml.weight ASC, p1 ASC, p2 ASC, p3 ASC, p4 ASC, p5 ASC, p6 ASC, p7 ASC, p8 ASC, p9 ASC";
    $result = db_query($sql,'primary-links', $mlid);
	
	
	return $result;
}

function pwcs_update_listing_table(){
	
		
	 	db_query("TRUNCATE TABLE {pwcs_product_listing}");
	 	
		$sql  = "SELECT *, pp.listing_id FROM {node} n LEFT JOIN {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product' AND n.status = 1 ";
		$results = db_query($sql);
		$i=0;
		while($result = db_fetch_object($results))
		{
			
			$listing_id_array = explode(",", $result->listing_id);
			foreach($listing_id_array as $val)
				{
					$sql = "INSERT INTO {pwcs_product_listing} (pwc_product_row_id, listing_id)".
									" VALUES ( %d, %d)";
					if($val!=0){
						$i=$i+1;
						db_query($sql, $result->pwc_product_row_id, $val);
					}
				}
			
		}
		$sql = "UPDATE {pwcs_product} SET listing_id = '-1' WHERE listing_id = 0 OR listing_id IS NULL";
		db_query($sql);		
		$sql = "UPDATE {pwcs_product_listing} SET listing_id = '-1' WHERE listing_id = 0";
		db_query($sql);	
				
}

function pwcs_hide_inv_product(){
	
	 	
		$sql  = "SELECT pp.pwc_product_row_id FROM {node} n LEFT JOIN {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product' AND n.status = 0 ";
		$results = db_query($sql);
		while($result = db_fetch_object($results))
		{
			//echo $result->pwc_product_row_id;
			$sql = "DELETE FROM {pwcs_product_listing} WHERE pwc_product_row_id = ".$result->pwc_product_row_id." AND listing_id='-1'";
			//echo $sql;
			db_query($sql);
			
		}
}
function pwcs_update_product_listing(){
	
	
		$sql  = "SELECT * FROM {pwcs_listings} ";
		$results = db_query($sql);
		$new_array = array();
		while($result = db_fetch_object($results))
		{
			$new_array[]=$result->pwc_category_id; 
			//echo $result->pwc_category_id.'<br/>';
		}	
		//print_r($new_array);
		$sql  = "SELECT *,pp.listing_id FROM {node} n LEFT JOIN {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product' AND n.status = '1'";
		//$sql  = "SELECT *,pp.listing_id FROM {node} n LEFT JOIN {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product'";
		$results = db_query($sql);
		while($result = db_fetch_object($results))
		{
			$need_to_update = 0;
			$new_listing_id = array();
			$new_listing_val = '';
			
			$listing_id_array = explode(",", $result->listing_id);
			foreach($listing_id_array as $val)
			{
				if (!in_array($val, $new_array)) {
					$need_to_update = 1;
				}else{
					$new_listing_id[]= $val;
				}
			}
			$new_listing_val = implode(",",$new_listing_id);
			$new_listing_val = str_replace(NULL,'',$new_listing_val);
			if ($need_to_update==1)
			{
				if($new_listing_val!="")
				$sql = "UPDATE {pwcs_product} SET listing_id ='%s' WHERE nid=%d";
				else
				$sql = "UPDATE {pwcs_product} SET listing_id ='-1' WHERE nid=%d";
				db_query($sql, $result->nid);
				//echo "".$new_listing_val." >> NID".$result->nid."<br/>";
			}			
		}
}
function pwcs_siteadmin_productsordermanager($list_id=NULL) {

	$breadcrumb = array();
	$breadcrumb[] = l(t('Home'), '<front>');
	$breadcrumb[] = l(t('List Management'), 'siteadmin/listings');
	$breadcrumb[] = t('Product Order List');
	drupal_set_breadcrumb($breadcrumb);
	//pwcs_make_order_table();


		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
		$output .= drupal_get_form('pwcs_siteadmin_product_order_form',$list_id);
		$output .= '</div>';
/*
		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
		$output .= pwcs_product_list_order_manager($list_id);
		$output .= '</div>';
*/
	return $output;
}
function pwcs_siteadmin_product_order_form($action=NULL, $list_id=NULL) {


  $form = array();

  $form['groups']['#tree'] = TRUE;
  $position = 0;

  $groups_result = pwcs_get_attr_group_resultset2($list_id);
  
	$product_quantity = db_affected_rows($groups_result);

	while($result = db_fetch_object($groups_result)) {

		$node = node_load($result->nid);

		// Product Primary Image
		$image_path = pwcs_get_product_primary_image_path($node);
		$image_output = theme('image', $image_path, '', NULL, array('width'=>'60px'), FALSE);


		$position = $position+1;

	    $form['groups'][$result->nid]['product_image'] = array(
	      '#value' => t($image_output),
	    );

		$product_name = ($result->status==1) ? t($result->title) : '<font style="color:red;">'.t($result->title).'</font><br/>[Hidden from Your Customer]';

	    $form['groups'][$result->nid]['product_name'] = array(
	      '#value' => $product_name,
	    );
	    $form['groups'][$result->nid]['price'] = array(
	      '#value' => t(PWCS_BASIC_CURRENCY_SYMBOL." ".$result->price),
	    );
	    $form['groups'][$result->nid]['listid'] = array(
	      '#type' => 'hidden',
	      '#value' => ($list_id!=NULL) ? t($list_id): '0',
	    );
	    $form['groups'][$result->nid]['button'] = array(
	      '#value' => t(''),
	    );
	    $form['groups'][$result->nid]['product_quantity'] = array(
	      '#type' => 'hidden',	
	      '#value' => $product_quantity,
	    );
	    $form['groups'][$result->nid]['listing_id'] = array(
	      '#type' => 'hidden',	
	      '#value' => $list_id,
	    );

	    
	    /*
	    $form['groups'][$result->nid]['total_attribute'] = array(
	      '#value' => '',
	    );
	    $form['groups'][$result->nid]['action'] = array(
	      '#value' => t(''),
	    );
		*/
		
	    $form['groups'][$result->nid]['pid'] = array(
	      '#type' => 'hidden',
	      '#value' => $result->pwc_product_row_id,
	    );

	    $form['groups'][$result->nid]['position'] = array(
	      '#type' => 'textfield',
	      '#default_value' => $position ,
	      '#size' => 3,
	      '#maxlenth' => 4,
	      '#attributes' => array('class' => 'macro-position'),
	    );
	}

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save the Order'),
  );
  
  return $form;
}
/**
 * Theme the display of the settings form as a table.
 */
function theme_pwcs_siteadmin_product_order_form($form) {

global $base_url;	
	
  $rows = array();
  $rows = array();
  $product_amount = 0;
  $listing_id = 0;
  $product_no = 0;
  foreach (element_children($form['groups']) as $mid) {
  	$product_no+=1;
    $row = array();
    $row[] =  array('data' => '');
    $row[] = array('data' => $product_no);
    $product_amount = $form['groups'][$mid]['product_quantity']['#value'];
    $listing_id = $form['groups'][$mid]['listing_id']['#value'];
    $row[] = drupal_render($form['groups'][$mid]['product_image']);
    $row[] = drupal_render($form['groups'][$mid]['product_name']);
    $row[] = drupal_render($form['groups'][$mid]['price']);
    //$row[] = drupal_render($form['groups'][$mid]['total_attribute']);
    $row[] = drupal_render($form['groups'][$mid]['position']);
    $row[] = drupal_render($form['groups'][$mid]['button']);
    $row[] = drupal_render($form['groups'][$mid]['button']);
    //$row[] = drupal_render($form['groups'][$mid]['action']);
    $rows[] = array('data' => $row, 'class' => 'draggable');
  }

  if (empty($rows)) {
    $rows[] = array(array('colspan' => 8, 'data' => t('There are currently no Products.')));
  }
  else {
    drupal_add_tabledrag('table-productorder-group', 'order', 'sibling', 'macro-position');
  }


  $output = '';		
  if($listing_id==0)
  {
  	$output .= '<h3>'.t('All Products Order').'</h3>';
  	$site_preview = $base_url.'/'.'siteadmin/sort-preview/0';
  }
  else if($listing_id==-1)
  {
  	$output .= '<h3>'.t('Miscellaneous Listings Products Order').'</h3>';
  	$site_preview = '';
  	$site_preview = $base_url.'/'.'siteadmin/sort-preview/'.$listing_id;
  }	
  else{
  	$category_title = db_result(db_query('SELECT category_title FROM {pwcs_listings} pc WHERE pc.pwc_category_id = '.$listing_id));
  	$output .= '<h3>'.t($category_title.' Listings Products Order').'</h3>';
  	$site_preview = $base_url.'/'.'siteadmin/sort-preview/'.$listing_id;
  }

  $output .= '<div class="pwcs_prodcut_list_count" style="margin:5px 0; font-style: italic;">'.t('Total').' '.$product_amount.' '.t('product(s) found').'</div>';
  //$output .= '<div class="" style="margin:5px 0;text-align:right;width:100%;">PREVIEW</div>';

  $headers = array(t(''),t('SL.'),t('Image'),t('Product Name'), t('Price'), t('Position'),'<input type="submit" class="form-submit" value="'.t('Save the Order').'" name="delete">','<a class="form-submit" href="'.$site_preview.'" target="_blank" style="background-color: #36708B;border: 1px solid white;color: white !important;cursor: pointer;font: bold 1em Verdana,sans-serif;margin: 2px;outline: 1px solid #9BABB0;padding: 3px;">Preview</a>',   'width'=> '');
  //$headers = array(t('Image'),t('Product Name'), t('Price'), t(PWCS_ATTRIBUTE_NAME.' Count'), t('Position'), t('Action'));

  
  $output .= theme('table', $headers, $rows, array('id' => 'table-productorder-group'));
  $output .= drupal_render($form);

  return $output;
}
function pwcs_siteadmin_product_order_form_submit($form, $form_state) {
 
  $record_affected = 0;
  $record_deleted = 0;
  $listid = -1;
  
  foreach ($form_state['values']['groups'] as $group)
  {
    $listid   = $group['listid'];
  }  

  $sql = "DELETE FROM {pwcs_product_order} WHERE listing_id = %d";
  db_query($sql, $listid);

  $sql = "DELETE FROM {pwcs_product_order} WHERE listing_id = 0 and pwc_product_row_id=0";
  db_query($sql);

  foreach ($form_state['values']['groups'] as $group)
  {
    $pid = $group['pid'];
    $listid   = $group['listid'];
    $position = $group['position'];

					$sql = "INSERT INTO {pwcs_product_order} (pwc_product_row_id, listing_id,weight)".
									" VALUES ( %d, %d, %d)";


    //$sql="UPDATE  {pwcs_product_listing} SET weight = '" . $group['position'] . "' WHERE pwc_product_row_id = ".$pid;
    if(db_query($sql,  $pid, $listid,$position))
    {
		$record_affected++;
    }
  }
  
  if ($record_affected > 0)
  	drupal_set_message(t('Order has been Saved Successfully.'));

  if ($listid>0)
  	drupal_goto('siteadmin/product-order/'.$listid);
  else if ($listid==-1)
  	drupal_goto('siteadmin/product-order/'.$listid);
  else
  	drupal_goto('siteadmin/product-order');
}
function pwcs_get_attr_group_resultset2($list_id=NULL)
{
  	$sql  = "SELECT n.*, pp.*,ppo.*,ppo.weight AS position FROM {node} n";
	$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
    $sql .= " LEFT JOIN {pwcs_product_order} ppo ON ppo.pwc_product_row_id = pp.pwc_product_row_id";	
    if($list_id!=NULL)	
		$sql .= " WHERE n.type = 'product' AND pp.pwc_product_row_id > 0 AND ppo.listing_id =  '".$list_id."'";
        //$sql .= " WHERE n.type = 'product' AND n.status = '1' AND pp.pwc_product_row_id > 0 AND ppl.listing_id =  '".$list_id."'";
	else
		$sql .= " WHERE n.type = 'product' AND pp.pwc_product_row_id > 0  AND ppo.listing_id = 0 ";
	$sql .= " ORDER BY ppo.weight ASC, n.title ASC";

	$results = db_query($sql);
	return $results;
}
/**
 * Product Order List Mapping Function
 *
 * This function is required to make the order mapping for products under listing. 
 * If the order mapping does not exist in the database then it will create mapping for all listings with type category
 *
 * @todo we will have to make sure that there is no bug, so need code review again in future.
 *       
 *
 * @author A.R. ZERIN <a.r.zerin@gmail.com>
 * @version 1.3
 * @copyright 
 * @package PWC Store
 * @link http://project_url/siteadmin/listings/productorder
 * @example http://www.pwcstores.com/siteadmin/listings/productorder
 * @access private
 * @param 
 * @see  pwcs_make_listorder_table(), pwcs_update_listorder_table(), pwcs_make_listorder_all_product()
 *
 * @global 
 *
 * @usedby listings
 *
 * @last-modified on 03 March, 2011 by A.R. ZERIN <a.r.zerin@gmail.com>
 *
 * @return NULL, nothing to return
 *
 */

function pwcs_make_order_table(){
	
	$sql  = "SELECT pc.*, COUNT(ppl.pwc_product_row_id) AS total_product ";
	$sql .= " FROM {pwcs_listings} pc";
	$sql .= " LEFT JOIN {pwcs_product_listing} ppl ON pc.pwc_category_id = ppl.listing_id";	
	$sql .= " LEFT JOIN {pwcs_product} pp  ON ppl.pwc_product_row_id=pp.pwc_product_row_id";
	$sql .= " WHERE pc.category_type = 0";	
	$sql .= " GROUP BY pc.pwc_category_id";
	$sql .= " ORDER BY pc.weight ASC, pc.category_title ASC, pc.category_id";

	$results = db_query($sql);
	
	//echo db_affected_rows($results).'<br/>';
	
	while($result = db_fetch_object($results)) {
		//echo 	$result->category_title.'>>'.$result->total_product.'<br/>';
		
		$sql  = "SELECT * FROM {pwcs_product_order} ppo WHERE ppo.listing_id = ".$result->pwc_category_id." AND ppo.pwc_product_row_id > 0";
		$results2 = db_query($sql);
		//echo 'Recorded:'.db_affected_rows($results2).'<br/>';
		
		if ( !db_result($results2) ){
		if((db_affected_rows($results2)<=0) && ($result->total_product>0)){
			pwcs_make_listorder_table($result->pwc_category_id);
		}}else{
			pwcs_update_listorder_table($result->pwc_category_id);
			//echo 'Here then.<br/>';
		}
	}	
	pwcs_make_listorder_all_product();
	/*
	 	db_query("TRUNCATE TABLE {pwcs_product_order}");
	 	
		$sql  = "SELECT *,pp.listing_id FROM {node} n LEFT JOIN {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product' AND n.status = '1'";
		$results = db_query($sql);
		$position = 0;
		while($result = db_fetch_object($results))
		{
			$position+=1;
			$sql = "INSERT INTO {pwcs_product_order} (pwc_product_row_id, listing_id, weight)".
									" VALUES ( %d, %d, %d)";
		    if(db_query($sql,  $result->pwc_product_row_id, 0, $position))
		    {
				
		    }
    
		}

		$sql  = "SELECT *,pp.listing_id FROM {node} n LEFT JOIN {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product' AND n.status = '1'";
		$results = db_query($sql);
		
		$position = 0;
		while($result = db_fetch_object($results))
		{
			$position+=1;
			$listing_id_array = explode(",", $result->listing_id);
			foreach($listing_id_array as $val)
				{
					$sql = "INSERT INTO {pwcs_product_order} (pwc_product_row_id, listing_id, weight)".
									" VALUES ( %d, %d, %d)";
					db_query($sql, $result->pwc_product_row_id, $val, $position);
				}
			
		}
	*/	
}
function pwcs_make_listorder_table($listid=NULL){
	
	  	$sql  = "SELECT n.*, pp.*,ppl.*,ppl.weight AS position FROM {node} n";
		$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
	    $sql .= " LEFT JOIN {pwcs_product_listing} ppl ON ppl.pwc_product_row_id = pp.pwc_product_row_id";	
	    if($listid!=NULL)	
			$sql .= " WHERE n.type = 'product' AND n.status = '1' AND pp.pwc_product_row_id > 0 AND ppl.listing_id =  '".$listid."'";
		else
			$sql .= " WHERE n.type = 'product' AND n.status = '1' AND pp.pwc_product_row_id > 0 ";
		
	
		//$sql  = "SELECT *,pp.listing_id FROM {node} n LEFT JOIN {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product' AND n.status = '1'";
		$results = db_query($sql);
		$position = 0;
		while($result = db_fetch_object($results))
		{
			$position+=1;
			$sql = "INSERT INTO {pwcs_product_order} (pwc_product_row_id, listing_id, weight)".
									" VALUES ( %d, %d, %d)";
			if($listid!=NULL)						
		    if(db_query($sql,  $result->pwc_product_row_id, $listid, $position))
		    {
				
		    }
		    //echo 'PID:'.$result->pwc_product_row_id.'<br/>';
    
		}
}
function pwcs_update_listorder_table($listid=NULL){
	
		//echo 'ListingId:'.	$listid . '<br/>';
/*
SELECT pp.pwc_product_row_id FROM `dru_node` n INNER JOIN `dru_pwcs_product` pp ON n.nid = pp.nid LEFT JOIN `dru_pwcs_product_listing` ppl ON ppl.pwc_product_row_id = pp.pwc_product_row_id WHERE n.type = 'product' AND ppl.listing_id = '4481' AND pp.pwc_product_row_id NOT IN (
	SELECT pwc_product_row_id FROM `dru_pwcs_product_order` WHERE listing_id=ppl.listing_id
)
	
	  	$sql  = "SELECT pp.pwc_product_row_id FROM {node} n";
		$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
		$sql .= " LEFT JOIN {pwcs_product_listing} ppl ON ppl.pwc_product_row_id = pp.pwc_product_row_id";			
	    if($listid!=NULL)	
			$sql .= " WHERE n.type = 'product'  AND ppl.listing_id =  '".$listid."' AND pp.pwc_product_row_id NOT IN (SELECT pwc_product_row_id FROM {pwcs_product_order}  WHERE listing_id=ppl.listing_id)";
		echo $sql. '<br/>';
			$results = db_query($sql);
			$position = 0;
			while($result = db_fetch_object($results))
			{
				echo $result->pwc_product_row_id. '<br/>';
			}

*/	
	  	$sql  = "SELECT pp.pwc_product_row_id FROM {node} n";
		$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
		$sql .= " LEFT JOIN {pwcs_product_listing} ppl ON ppl.pwc_product_row_id = pp.pwc_product_row_id";			
	    
		$sql .= " WHERE n.type = 'product'  AND ppl.listing_id =  '".$listid."' AND pp.pwc_product_row_id NOT IN (";
		$sql .= " SELECT pwc_product_row_id FROM {pwcs_product_order}  WHERE listing_id=ppl.listing_id           )";
		//echo $sql;
		
		if($listid!=NULL)	
		{	
			$results = db_query($sql);
			$position = 0;
			$sql2  = "SELECT * FROM {pwcs_product_order} ppo WHERE ppo.listing_id = '".$listid."' AND ppo.pwc_product_row_id > 0";
			$results2 = db_query($sql2);
			$position = db_affected_rows($results2);
			while($result = db_fetch_object($results))
			{
				$position+=1;
				//echo 'pwc_product_row_id:'.$result->pwc_product_row_id.'>>'.$listid.'>>'.$position;
				
				$sql = "INSERT INTO {pwcs_product_order} (pwc_product_row_id, listing_id, weight)".
										" VALUES ( %d, %d, %d)";
				if($listid!=NULL)						
			    if(db_query($sql,  $result->pwc_product_row_id, $listid, $position))
			    {
					
			    }
			    
				if ($result->pwc_product_row_id>0)						
				if(db_query($sql,  $result->pwc_product_row_id, 0, $position))
				{
				}
				
			}
	    } //end of if($listid!=NULL)	

}
function pwcs_make_listorder_all_product(){
	
	  	$sql  = "SELECT n.*, pp.*, ppl.*, ppl.weight AS position FROM {node} n";
		$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
	    $sql .= " LEFT JOIN {pwcs_product_listing} ppl ON ppl.pwc_product_row_id = pp.pwc_product_row_id";	
		$sql .= " WHERE n.type = 'product'  AND pp.pwc_product_row_id > 0 ";
	
		$sql  = "SELECT * FROM {pwcs_product_order} ppo WHERE ppo.listing_id = 0 AND ppo.pwc_product_row_id > 0";
		$results2 = db_query($sql);
		
		if ( !db_result($results2) ){
		if(db_affected_rows($results2)<=0){
			//////////////////
			$sql2  = "SELECT *,pp.listing_id FROM {node} n LEFT JOIN {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product'";
			$results = db_query($sql2);
			$position = 0;
			while($result = db_fetch_object($results))
			{
				$position+=1;
				$sql = "INSERT INTO {pwcs_product_order} (pwc_product_row_id, listing_id, weight)".
										" VALUES ( %d, %d, %d)";
				if ($result->pwc_product_row_id>0)						
			    if(db_query($sql,  $result->pwc_product_row_id, 0, $position))
			    {
					
			    }
	    
			}			
			///////////////////
		}}else {
			
		  	$sql2  = "SELECT pp.pwc_product_row_id AS pid  FROM {node} n";
			$sql2 .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
			$sql2 .= " WHERE pp.pwc_product_row_id NOT IN (SELECT pwc_product_row_id FROM {pwcs_product_order} WHERE pwc_product_row_id>0 AND listing_id=0)";
			$results3 = db_query($sql2);
			$position = db_affected_rows($results2)+100;
			while($result3 = db_fetch_object($results3))
			{
				$position+=1;
				
				$sql = "INSERT INTO {pwcs_product_order} (pwc_product_row_id, listing_id, weight)".
										" VALUES ( %d, %d, %d)";
				if ($result3->pid>0)						
			    if(db_query($sql,  $result3->pid, 0, $position))
			    {
					//echo 'Done';
			    }else{
			    	//echo 'Not Done';
			    }
			    //echo 'List Id:'.$result3->pid;
			}
		}	
}
function pwcs_placein_listorder_table(){
	
	  	$sql  = "SELECT n.*, pp.*,ppl.*,ppl.weight AS position FROM {node} n";
		$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
	    $sql .= " LEFT JOIN {pwcs_product_listing} ppl ON ppl.pwc_product_row_id = pp.pwc_product_row_id";	
		$sql .= " WHERE n.type = 'product' AND n.status = '1' AND pp.pwc_product_row_id > 0  AND ppl.listing_id IS NULL";
	
		$sql  = "SELECT * FROM {pwcs_product_order} ppo WHERE ppo.listing_id = 0 AND ppo.pwc_product_row_id > 0";
		$results2 = db_query($sql);
		
		if ( !db_result($results2) )
		if(db_affected_rows($results2)<=0){
			//////////////////
			$sql  = "SELECT *,pp.listing_id FROM {node} n LEFT JOIN {pwcs_product} pp ON n.nid = pp.nid WHERE n.type = 'product' AND n.status = '1'";
			$results = db_query($sql);
			$position = 0;
			while($result = db_fetch_object($results))
			{
				$position+=1;
				$sql = "INSERT INTO {pwcs_product_order} (pwc_product_row_id, listing_id, weight)".
										" VALUES ( %d, %d, %d)";
				if ($result->pwc_product_row_id>0)						
			    if(db_query($sql,  $result->pwc_product_row_id, 0, $position))
			    {
					
			    }
	    
			}			
			///////////////////
		}	

}
function sort_preview($path1)
{
	global $base_url;
	if ($path1!=0)
	$target_url =  $base_url.'/products/all/all/'.$path1;
	else
	$target_url =  $base_url.'/products';


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_HEADER, 0); // Display headers
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
	} else {
	      
	}
	curl_close($ch);
	echo $response;	
}