<?php
/* $Id: pwcs_manufacturers.php,v 1.0 2010/08/23 Rasel Kabir Shuvro Exp $ */

/**
 * Site Admin Manufacturer page theme
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_manufacturers($action=NULL, $pwc_category_id=NULL) {

	$output = '';

	$iframeurl = l(t('Load PremiumWebCart Manufacturer Page'), PWCS_PWC_AUTO_LOGIN_URL . PWCS_PWC_CORE_URL . 'products/manage_manufacturer.html' , array('attributes' => array('class'=>'pwcs-popup-iframe', 'title'=>'PREMIUMWEBCART Manufacturer')));
	$pwc_manufacturer_link_text .= '<div class="">'.$iframeurl.'</div>';

	// List of all category
	$output .= '<div class="pwcs-page-content-inner">';
	//$output .= '<h3>'.t('Set Featured Manufacturers').'</h3>';
	$output .= '<div id="manufacturer_list" class="pwcs-content-list">';
	$output .= $pwc_manufacturer_link_text;
	$output .= '</div><br/>';	
	$output .= '<div id="manufacturer_list" class="pwcs-content-list">';
	$output .= drupal_get_form('pwcs_manufacturer_setfeatured_form');
	$output .= '</div>';
	$output .= '</div>';
	return $output;
}

/**
 * featured manufacturer form
 * Added by Rasel Kabir
 */
function pwcs_manufacturer_setfeatured_form($form_info = array()) {

	$form = array();
	$weight = 0;

	//$input_select_all = '<input type="checkbox" class="form-checkbox checkbox_checkall" title="Select all rows in this table">';
	//$input_select_all = '(<a class="checkbox_checkall">'.t('Select/Unselect All').'</a>)';
	//'<span id="pwcs_manufacturer_setfeatured_form_featured_checkboxes_checkall">'.t('Select/Unselect All').'</span> '

	$manufacturer_array = pwcs_manufacturer_array();
	$featured_manufacturer = pwcs_get_featured_manufacture_array();
	
	//print_r($featured_manufacturer);

	$form['#prefix'] = '<div id="pwcs_manufacturer_setfeatured_form">';
	$form['#suffix'] = '<div style="clear:both;"></div></div>';

	$output .= '<div class="messages warning">No Manufacturer is Found. Please add Manufacturer from your PremiumWebCart Merchant Area</div>';
	

	if (pwcs_manufacturer_count()<=0)
	{
		$form['instuct']=array(
				'#type' => 'markup',
				'#value' =>$output
			);
	}	
	$form['featured'] = array(
		'#type' => 'checkboxes',
		'#options' => $manufacturer_array,
		'#default_value' => array_keys($featured_manufacturer),
		'#title' => t('List of Manufacturer(s)'),
		'#weight' => $weight++,
		'#prefix' => '<div id="pwcs_manufacturer_setfeatured_form_featured_checkboxes">',
		'#suffix' => '<div style="clear:both;"></div></div>',
	);

	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Save as Featured'),
		'#weight' => $weight++,
	);

	return $form;
}

/**
 * featured manufacturer form submit
 * Added by Rasel Kabir
 */
function pwcs_manufacturer_setfeatured_form_submit($form_id, $form_values)
{
	global $user;
	//print_r ($form_values);
	if($form_values[submitted] == 1) {

		// Update Manufacturers Table
		if (isset ($form_values['clicked_button']['#post']['featured'])) {
			$manufacturers['featured']= $form_values['clicked_button']['#post']['featured'];
			$manufacturers['modified_on'] = time();
			db_query("UPDATE {pwcs_manufacturer} SET `featured`=0");
			foreach ($manufacturers['featured'] as $k=>$mfg_id) {
				db_query("UPDATE {pwcs_manufacturer} SET `featured`=1 WHERE pwc_mfg_id = %d", $mfg_id );
			}
		}
		if (user_access('is siteadmin')){
			$_REQUEST[destination] = 'siteadmin/manufacturers';
		}
	}
	else {
		drupal_set_message(t('Opps! unfortunate error'), 'error');
	}
}

/**
 * manufacturer list in an array
 * Added by Rasel Kabir
 */
function pwcs_manufacturer_array()
{
	$sql  = "SELECT * FROM {pwcs_manufacturer} ";
	$sql .= "ORDER BY mfg_name ASC";
	//$sql .= " LIMIT 0,".$amount."";
	$results = db_query($sql);
	$array = array();
	while($result = db_fetch_object($results)) {
		$array[$result->pwc_mfg_id] = check_plain($result->mfg_name);
	}
	return $array;
}

/**
 * Get featured manufacturer in an array
 * @return array
 * Added by Rasel Kabir
 */
function pwcs_get_featured_manufacture_array() {

	$sql  = "SELECT * FROM {pwcs_manufacturer} pm WHERE pm.featured = 1";
	$results = db_query($sql);
	$array = array();
	while($result = db_fetch_object($results)) {
		$array[$result->pwc_mfg_id] = check_plain($result->mfg_name);
	}
	return $array;
}

/**
 * Get Manufacturer Details of a manufacturer
 * @param pwc_mfg_id
 * @return array
 * Added by Rasel Kabir
 */
function pwcs_get_manufacturer_details($pwc_mfg_id) {

	$sql  = "SELECT * FROM {pwcs_manufacturer} pm WHERE pm.pwc_mfg_id = ".$pwc_mfg_id;
	$results = db_query($sql);
	$array = array();
	while($result = db_fetch_object($results)) {
		$array = (array)$result;
	}
	return $array;
}


/*****************/

/**
 * Manufacture 
 * Added by Rasel Kabir
 */
function pwcs_manufacture_menu_block($only_product_available = false) {

//	$sql = "SELECT pm.*, Count(pp.nid) AS product_amount";
//	$sql .= " FROM {pwcs_manufacturer} pm";
//	$sql .= " Left Join {pwcs_product} pp ON pm.pwc_mfg_id = pp.manufacturer_id";
//	//$sql .= " WHERE product_amount > 0";
//	$sql .= " GROUP BY pm.pwc_mfg_id";
//	$sql .= " ORDER BY RAND()";
//	$sql .= " LIMIT 5";
	
	$viewtype = variable_get('pwcs_manufacturer_block_type', '0');
	if($viewtype == '1') {
		$sql = "SELECT pm.*";
		$sql .= " FROM {pwcs_manufacturer} pm";
		$sql .= " WHERE featured = 1";
		$sql .= " ORDER BY mfg_name ASC";
	} else {
		$amount = variable_get('pwcs_manufacturer_block_item_amount', '10');
		$sql = "SELECT pm.*";
		$sql .= " FROM {pwcs_manufacturer} pm";
		$sql .= " ORDER BY RAND()";
		$sql .= " LIMIT $amount";
	}


	$results = db_query($sql);

	$output = '';
	$output .= '<div class="pwcs-manufacturer-menu-div">';
	$output .= '<ul class="pwcs-manufacturer-menu">';
	while($result = db_fetch_object($results)) {
		$output .= '<li id="pwcs-manufacturer-menu-'.$result->pwc_mfg_id.'">';
		$output .= l( $result->mfg_name, 'products/all/'.$result->pwc_mfg_id, array('attributes' => array('class'=>'pwcs-manufacturer-menu-item', 'id'=>'pwcs-manufacture-menu-'.$result->pwc_mfg_id)));
		$output .='</li>';
	}
	$output .='</ul>';
	$output .='<div class="manufacturers_all">'.l(t('Manufacturers'), 'manufacturers', array('attributes' => array('class'=>'moredetails'))).'</div>';
	$output .='</div>';

	return $output;
}

/**
 * Public manufacturers url
 * Added by Rasel Kabir
 */
function pwcs_public_manufacturers_url($featured_manfacturers= FALSE){

	drupal_set_title(t('Products'));
	$output	= '';
	$options = array(
				//'featured_manfacturers' => $featured_manfacturers,
				//'view_type' => 'page',
				);
	$output .= pwcs_public_manufacturers_list($options);
	return $output;
}

/**
 * Public manufacturers list
 * Added by Rasel Kabir
 */
function pwcs_public_manufacturers_list($options = array()){

	$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
	$pagelimit = 12;
	$pageelement = 0;
	$pageparameters = array();
	$pagequantity = 3;

	drupal_set_title(t('Manufacturers'));
	$breadcrumb = array();
	$breadcrumb[] = l(t('Home'), '<front>');
	$breadcrumb[] = t('Manufacturers');
	drupal_set_breadcrumb($breadcrumb);

	$sql = "SELECT pm.*, Count(pp.nid) AS product_amount";
	$sql .= " FROM {pwcs_manufacturer} pm";
	$sql .= " Left Join {pwcs_product} pp ON pm.pwc_mfg_id = pp.manufacturer_id";
	//$sql .= " WHERE pc.parent_category_id = ".$parent;
	$sql .= " GROUP BY pm.pwc_mfg_id";
	$sql .= " ORDER BY pm.mfg_name ASC";

	$output	= '';
	if(pwcs_result_count($sql) > 0) {
		$output .= '<div id="pwcs_manufacturers_page">';
		//$results = pager_query($sql, $pagelimit);
		$results = db_query($sql);
		$manufacturers = pwcs_cat_array();
		while($result = db_fetch_object($results))
		{
			//$manufacture_item = theme_pwcs_single_manufacturer_view($result);
			//$output .= l( $manufacture_item, 'products/all/'.$result->pwc_mfg_id, array('html'=> true, 'attributes' => array('class'=>'pwcs-manufacture-page-item', 'id'=>'pwcs-manufacture-page-'.$result->pwc_mfg_id)));
			$output .= theme_pwcs_single_manufacturer_view($result);
		}
		$output .= '<div style="clear:both;"></div>';
		$output .= '</div>';
		$output .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);
	} else {
		$output .= '<div class="warning"> ' . t('Sorry! There is no manufacturers!') . ' </div>';
	}
	return $output;
}

/**
 * Theme Manufacture item for page
 * Added by Rasel Kabir
 */
function theme_pwcs_single_manufacturer_view($manufacturers){
	
	$output = '';
	$output .= '<div class="pwcs_single_manufacturer_view_item">';
	//$output .= '<div class="pwcs_smvi_name">'.$manufacturers->mfg_name.'</div>';
	$output .= '<div class="pwcs_smvi_name">';
	$output .= l( $manufacturers->mfg_name, 'products/all/'.$manufacturers->pwc_mfg_id, array('html'=> false, 'attributes' => array('class'=>'pwcs-manufacture-page-item', 'id'=>'pwcs-manufacture-page-'.$result->pwc_mfg_id)));
	$output .= '</div>';
	$output .= '<div class="pwcs_smvi_amount">'.$manufacturers->product_amount.'</div>';
	$output .= '<div style="clear:both;"></div>';
	$output .= '</div>';
	return $output;
}
