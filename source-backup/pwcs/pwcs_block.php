<?php
/* $Id: pwcs_block.php,v 1.0 2011/02/20 Rasel Kabir Exp $ */

//function pwcs_get_block_resultset($themename=NULL)
//{
//	if(!empty($themename))
//	{
//		$sql  = "SELECT bid, delta, title, status FROM {blocks} WHERE (delta = 'fatured_products' ";
//		$sql .= "OR delta = 'sidebar_category_menu'  OR delta = 'sidebar_manufacture_block' OR delta = 'sidebar_listing_menu') ";
//		//$sql .= "OR delta = 'sidebar_category_menu'  OR delta = 'homepage_products' OR delta = 'sidebar_manufacture_block') ";
//		$sql .= "AND theme='%s'";
//		$results = db_query($sql,$themename);
//		return $results;
//	}else{
//		return NULL;
//	}
//}
//function pwcs_block_configure($themename=NULL) {
//	
//
//	$output = '';
//	$output .= drupal_get_form('pwcs_siteadmin_block_form',$themename);
//	return $output;
//}
//function pwcs_siteadmin_block_form($action=NULL, $themename='') {
//
//	$breadcrumb = array();
//	$breadcrumb[] = l(t('Home'), '<front>');
//	$breadcrumb[] = l(t('Theme Configuration'), 'siteadmin/theme');
//	$breadcrumb[] = l(t('Rename Block Title'), 'siteadmin/theme/block/'.$themename);
//	drupal_set_breadcrumb($breadcrumb);
//
//  $form = array();
//
//  $form['groups']['#tree'] = TRUE;
//  $position = 0;
//
//  $groups_result = pwcs_get_block_resultset($themename);
//
//	while($result = db_fetch_object($groups_result)) {
//	
//		if($result->delta=='fatured_products')
//		{
//	 		$form['groups'][$result->bid]['block_name'] = array(
//		      '#value' => 'Feature Product Block',
//		    );
//	    }	
//		else if($result->delta=='sidebar_category_menu')
//		{
//	 		$form['groups'][$result->bid]['block_name'] = array(
//		      '#value' => 'Product Category Block',
//		    );
//	    }	
//		else if($result->delta=='homepage_products')
//		{
//	 		$form['groups'][$result->bid]['block_name'] = array(
//		      '#value' => 'Homepage Block',
//		    );
//	    }	
//		else if($result->delta=='sidebar_manufacture_block')
//		{
//	 		$form['groups'][$result->bid]['block_name'] = array(
//		      '#value' => 'Manufacturer Block',
//		    );
//	    }
//	    else if($result->delta=='sidebar_listing_menu')
//		{
//	 		$form['groups'][$result->bid]['block_name'] = array(
//		      '#value' => 'Listing Block',
//		    );
//	    }
//	    else{}
//	    
//	    $form['groups'][$result->delta]['title'] = array(
//	      '#type' => 'textfield',
//	      '#default_value' => t($result->title),
//	    );
//	    if ($result->status=='1')
//		{
//		    $form['groups'][$result->delta]['status'] = array(
//		      '#type' => 'checkbox',
//		      '#attributes' => array('checked' => 'checked'),
//		    );
//        }else {
//		    $form['groups'][$result->delta]['status'] = array(
//		      '#type' => 'checkbox',
//		    );
//        }
//	    
//	    
//	    $form['groups'][$result->delta]['id'] = array(
//	      '#type' => 'hidden',
//	      '#value' => $result->delta,
//	    );
//	}
//
//  $form['submit'] = array(
//    '#type' => 'submit',
//    '#value' => t('Save the Changes'),
//  );
//  return $form;
//}
//
//function theme_pwcs_siteadmin_block_form($form) {
//  $headers = array(t('Block Name'),t('Your Custom Block Title'),  t('Visible'));
//  $rows = array();
//  foreach (element_children($form['groups']) as $mid) {
//    $row = array();
//    $row[] = drupal_render($form['groups'][$mid]['block_name']);
//    $row[] = drupal_render($form['groups'][$mid]['title']);
//    $row[] = drupal_render($form['groups'][$mid]['status']);
//    $rows[] = array('data' => $row);
//  }
//
//  if (empty($rows)) {
//    $rows[] = array(array('colspan' => 3, 'data' => t('There are currently no Blocks.')));
//  }
//  else {
//  }
//
//
//  $output = '';
//  $output .= theme('table', $headers, $rows, array('id' => 'table-attr-group'));
//  $output .= drupal_render($form);
//
//  return $output;
//}
///**
// * Submit handler for the pwcs_siteadmin_groups_form().
// */
//function pwcs_siteadmin_block_form_submit($form, $form_state) {
//
//  $record_affected = 0;
//  $record_deleted = 0;
//  foreach ($form_state['values']['groups'] as $group)
//  {
//    $gid = $group['id'];
//
//    $sql="UPDATE  {blocks} SET title = '".$group['title']."',status='".$group['status']."' WHERE delta = '".$gid."'";
//    if(db_query($sql))
//    {
//		$record_affected++;
//    }
//  }
///*
//  if ($record_deleted > 0)
//  	drupal_set_message($record_deleted." ".PWCS_ATTRIBUTE_GROUP_NAME."Block Title has been Changed");
//*/
//  //drupal_goto('siteadmin/attribute-group');  
//}  

/**
 * BLOCK for siteadmin navigation
 * @return html output of user, Synchronize, logout menu
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_navigation(){

	global $user;

	include_once(drupal_get_path('module', 'imce').'/inc/imce.page.inc');
	//include_once(drupal_get_path('module', 'pwcs').'/pwcs_navigation.php');
	
	/* Getting the Uploaded Filesize Information to display in the Left Sidebar Navigation */
	if (!$imce = imce_initiate_profile($user)) {
	      return '';
	}
	$imce['dir'] = "imagelibrary";
        
	imce_process_profile($imce);//get active directory content
	$imce_ref = array('imce' => &$imce);
	$files_uploaded = count($imce['files']);  

	//$output = pwcs_siteadmin_leftside_nav($files_uploaded);
	$output .='<div id="pwcs_siteadmin_navigation">';

	// Product - Category - Options management
	$output .='<ul class="menu pwcs-siteadminnav">';
	$output .='<li class="leaf">'.l(t('Category Management ('.pwcs_category_count().')'), 'siteadmin/categories').'</li>';
	$output .='<li class="leaf">'.l(t('List Management ('.pwcs_listings_count().')'), 'siteadmin/listings').'</li>';	
	$output .='<li class="leaf">'.l(t('Product Management ('.pwcs_product_count().')'), 'siteadmin/products').'</li>';
	$output .='<li class="leaf">'.l(t('Media Manager ('.$files_uploaded.')'), 'siteadmin/media').'</li>';
	$output .='<li class="leaf">'.l(t('Manufacturers ('.pwcs_manufacturer_count().')'), 'siteadmin/manufacturers').'</li>';
	$output .='<li class="leaf">'.l(t(PWCS_ATTRIBUTE_GROUP_NAME.' ('.pwcs_attribute_group_count().')'), 'siteadmin/attribute-group').'</li>';
	$review_count = pwcs_product_review_count('all');
	$total = $review_count[COMMENT_PUBLISHED] + $review_count[COMMENT_NOT_PUBLISHED];
	$output .='<li class="leaf">'.l(t('Product Review ('.$total.')'), 'siteadmin/productreview').'</li>';
	$output .='</ul>';

	// Settings - Theme - Reports management
	$output .='<ul class="menu pwcs-siteadminnav">';
	$output .='<li class="leaf">'.l(t('Site Configuration'), 'admin/settings/site-information').'</li>';
	$output .='<li class="leaf">'.l(t('Theme Configuration'), 'siteadmin/theme').'</li>';
	$output .='<li class="leaf">'.l(t('Block Configuration'), 'admin/build/block').'</li>';
	$output .='<li class="leaf">'.l(t('Display Settings'), 'siteadmin/sitesettings').'</li>';
	$output .='<li class="leaf">'.l(t('Block Settings'), 'siteadmin/blockconfiguration').'</li>';
	$output .='<li class="leaf">'.l(t('Header Banner'), 'admin/build/block/configure/pwcs/header_banner').'</li>';
	$output .='</ul>';

	// Report
	$output .='<ul class="menu pwcs-siteadminnav">';
	$output .='<li class="leaf">'.l(t('Top Searched Keyword'), 'siteadmin/reports/search').'</li>';
	$output .='</ul>';

	// Theme management
	$output .='<ul class="menu pwcs-siteadminnav">';
	$output .='<li class="leaf">'.l(t('Site Menu'), 'admin/build/menu-customize/primary-links').'</li>';
	$output .='<li class="leaf">'.l(t('Pages (CMS)'), 'advancepages/siteadmin/sitepage').'</li>';
	$output .='</ul>';

	// PREMIUMWEBCART management
	$iframeurl = l(t('My Orders'), PWCS_PWC_AUTO_LOGIN_URL . PWCS_PWC_CORE_URL . 'orders/orders/currentOrders.html' , array('attributes' => array('class'=>'pwcs-popup-iframe', 'title'=>'PREMIUMWEBCART ORDER PAGE')));
	
	$output .='<ul class="menu pwcs-siteadminnav">';
	$output .='<li class="leaf">'.$iframeurl.'</li>';
	
	l(t('My Orders'), 'admin/build/block/configure/pwcs/header_banner').'</li>';
	$output .='</ul>';

	// User management
	$output .='<ul class="menu pwcs-siteadminnav">';
	$output .='<li class="leaf">'.l(t('Synchronize'), 'siteadmin/sync-settings').'</li>';
	$output .='<li class="leaf">'.l(t('Sitemap'), 'siteadmin/mysitemap').'</li>';
	$output .='<li class="leaf">'.l(t('Edit Account'), 'user/'.$user->uid.'/edit').'</li>';
	$output .='<li class="leaf">'.l(t('Logout'), 'logout').'</li>';
	$output .='</ul>';

	//$output .='<li class="leaf">'.l(t('Rearrange Block'), 'siteadmin/arrange-blocks').'</li>';
	$output .='</div>';

	return $output;
}


/**
 * BLOCK for siteadmin navigation top
 * @return html output of user, Synchronize, logout menu
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_navigation_top(){

	global $user;

	include_once(drupal_get_path('module', 'imce').'/inc/imce.page.inc');
	//include_once(drupal_get_path('module', 'pwcs').'/pwcs_navigation.php');
	
	/* Getting the Uploaded Filesize Information to display in the Left Sidebar Navigation */
	if (!$imce = imce_initiate_profile($user)) {
	      return '';
	}
	$imce['dir'] = "imagelibrary";
        
	imce_process_profile($imce);//get active directory content
	$imce_ref = array('imce' => &$imce);
	$files_uploaded = count($imce['files']);  

	$review_count = pwcs_product_review_count('all');
	$total = $review_count[COMMENT_PUBLISHED] + $review_count[COMMENT_NOT_PUBLISHED];

	//$output = pwcs_siteadmin_leftside_nav($files_uploaded);

	$output ='';
	$output .='<div id="pwcs_siteadmin_navigation">';

	$output .='<ul class="pwcs-topnav megamenu">';

	//Home icon
    $output .='<li><a href="'.base_path().'siteadmin'.'"><span class="siteadmin_navigation_homelink">Home</span></a>';
	$output .='<div style="width: auto;">';
	$output .='<em>'.t('Click To Go Back To Siteadmin HomePage!').'</em>';
	//$output .= theme_pwcs_siteadmin_dashboard();
	$output .='</div>';
	$output .='</li>';

	// Products
    $output .='<li><a href="javascript:void(0)">'.t('Products').'</a>';
	$output .='<div style="width: 200px;">';
	$output .='<ul>';
	$output .='<h4>'.t('Products').' ('.pwcs_product_count().')'.'</h4>';
	$output .='<li>'.l(t('Add New Product'), 'siteadmin/products/addnew').'</li>';
	$output .='<li>'.l(t('Manage Products'), 'siteadmin/products').'</li>';
	$output .='<li>'.l(t('Drag & Drop Images'), 'siteadmin/products/draganddropimages').'</li>';
	$output .='<li>'.l(t('Featured Products'), 'siteadmin/products/featured').'</li>';
	$output .='</ul>';
	$output .='<ul>';
	$output .='<h4>'.t('Categories').' ('.pwcs_category_count().')'.'</h4>';
	$output .='<li>'.l(t('Add New Category'), 'siteadmin/categories/addnew').'</li>';	
	$output .='<li>'.l(t('Manage Categories'), 'siteadmin/categories').'</li>';
	$output .='</ul>';
	$output .='<ul>';
	$output .='<h4>'.t('Listings').' ('.pwcs_listings_count().')'.'</h4>';
	$output .='<li>'.l(t('Add New List'), 'siteadmin/listings/addnew').'</li>';	
	$output .='<li>'.l(t('Manage List'), 'siteadmin/listings').'</li>';	
	$output .='<li>'.l(t('Product Order List'), 'siteadmin/listings/productorder').'</li>';	
	$output .='</ul>';
	$output .='<ul>';
	$output .='<h4>'.t('Others').'</h4>';
	$output .='<li>'.l(t('Manufacturers ('.pwcs_manufacturer_count().')'), 'siteadmin/manufacturers').'</li>';
	$output .='<li>'.l(PWCS_ATTRIBUTE_GROUP_NAME.' ('.pwcs_attribute_group_count().')', 'siteadmin/attribute-group').'</li>';
	$output .='</ul>';
	$output .='</div>';
	$output .='</li>';

	// Orders
    $output .='<li><a href="javascript:void(0)">'.t('Orders').'</a>';
	$output .='<div style="width: 200px;">';
	$output .='<ul>';
	$output .='<li>'.l(t('My Orders'), PWCS_PWC_AUTO_LOGIN_URL . PWCS_PWC_CORE_URL . 'orders/orders/currentOrders.html' , array('attributes' => array('class'=>'pwcs-popup-iframe', 'title'=>'PREMIUMWEBCART ORDER PAGE'))).'</li>';
	$output .='</ul>';
	$output .='</div>';
	$output .='</li>';

	// Media Manager
    $output .='<li><a href="javascript:void(0)">'.t('Media Manager').'</a>';
	$output .='<div style="width: 200px;">';
	$output .='<ul>';
	$output .='<li>'.l(t('Image Manager').' ('.$files_uploaded.')', 'siteadmin/media').'</li>';
	$output .='<li>'.l(t('Mass Image Upload'), 'siteadmin/media/massupload').'</li>';
	$output .='<li>'.l(t('Video Manager'), 'siteadmin/media/videos').'</li>';
	$output .='</ul>';
	$output .='</div>';
	$output .='</li>';

	// Page Builder
    $output .='<li><a href="javascript:void(0)">'.t('Site Builder').'</a>';
	$output .='<div style="width: 200px;">';
	$output .='<ul>';
	$output .='<h4>'.t('Site Settings').'</h4>';
	$output .='<li>'.l(t('Site Information'), 'admin/settings/site-information').'</li>';
	$output .='<li>'.l(t('Basic Settings'), 'siteadmin/sitesettings').'</li>';
	$output .='</ul>';
	$output .='<ul>';
	$output .='<h4>'.t('Theme Settings').'</h4>';
	$output .='<li>'.l(t('Templates'), 'siteadmin/theme').'</li>';
	//$output .='<li>'.l(t('Theme Builder'), 'siteadmin/sitesettings').'</li>';
	$output .='<li>'.l(t('Block Configuration'), 'admin/build/block').'</li>';
	$output .='</ul>';
	$output .='<ul>';
	$output .='<h4>'.t('Page Builder').'</h4>';
	$output .='<li>'.l(t('Add Menu'), 'admin/build/menu-customize/primary-links/add').'</li>';
	$output .='<li>'.l(t('Site Menus'), 'admin/build/menu-customize/primary-links').'</li>';
	$output .='<li>'.l(t('Add New Pages'), 'advancepages/siteadmin/sitepage/addnew').'</li>';
	$output .='<li>'.l(t('Site Pages'), 'advancepages/siteadmin/sitepage').'</li>';
	$output .='</ul>';
	$output .='<ul>';
	$output .='<h4>'.t('Others').'</h4>';
	$output .='<li>'.l(t('Header Banner'), 'admin/build/block/configure/pwcs/header_banner').'</li>';
	$output .='<li>'.l(t('Block Settings'), 'siteadmin/blockconfiguration').'</li>';
	$output .='</ul>';
	$output .='</div>';
	$output .='</li>';

	// Tool Box
    $output .='<li><a href="javascript:void(0)">'.t('Tool Box').'</a>';
	$output .='<div style="width: 200px;">';
	$output .='<ul>';
	$output .='<li>'.l(t('Top Searched Keyword'), 'siteadmin/reports/search').'</li>';
	$review_count = pwcs_product_review_count('all');
	$total = $review_count[COMMENT_PUBLISHED] + $review_count[COMMENT_NOT_PUBLISHED];
	$output .='<li>'.l(t('Product Review ('.$total.')'), 'siteadmin/productreview').'</li>';
	$output .='<li>'.l(t('Social Media'), 'siteadmin/socialmedia').'</li>';
	$output .='<li>'.l(t('Live Chat'), 'siteadmin/tools').'</li>';
	$output .='<li>'.l(t('Click To Call'), 'siteadmin/tools').'</li>';
	$output .='<li>'.l(t('Instant Answers'), 'siteadmin/tools').'</li>';
	$output .='<li>'.l(t('Social Buzz'), 'siteadmin/socialbuzz').'</li>';
	$output .='<li>'.l(t('Sitemap'), 'siteadmin/mysitemap').'</li>';
	$output .='</ul>';
	$output .='</div>';
	$output .='</li>';

	// Help
    $output .='<li><a href="javascript:void(0)">'.t('Help').'</a>';
	$output .='<div style="width: 200px;">';
	$output .='<div>'.t('Send us mail, if you are facing any problem with any functions').'</div>';
	$output .='<ul>';
	//$output .='<li>'.l(t('Tutorial Videos'), '').'</li>';
	$output .='<li>'.l(t('Ticket Support'), 'http://pwcanswers.com/sales/ticket/29/Premium__Web__Cart').'</li>';
	$output .='</ul>';
	$output .='</div>';
	$output .='</li>';

	// My Account
    $output .='<li><a href="javascript:void(0)">'.t('My Account').'</a>';
	$output .='<div style="width: 150px;">';
	$output .='<ul>';
	$output .='<li>'.l(t('Edit Your Account'), 'user/'.$user->uid.'/edit').'</li>';
	$output .='<li>'.l(t('Synchronize Data'), 'siteadmin/sync-settings').'</li>';
	$output .='<li>'.l(t('Switch to Old Siteadmin Theme'), 'siteadmin/siteadmintheme/switch/pwcs_admin').'</li>';
	$output .='</ul>';
	//$output .='<div style="border-top: 1px solid #EEEEEE; margin-top: 15px; padding-top: 5px; text-align: center;">'
	//			.l(t('Logout'),'logout', array( 'attributes' => array('class' => 'button',)))
	//			.'</div>';
	$output .='</div>';
	$output .='</li>';

	$output .='<li style="clear:both;"></li>';
	$output .='</ul>';
	$output .='</div>';

	return $output;
}


/**
 * BLOCK for Header Banner
 * @return image/html output of header banner
 * Added by Rasel Kabir
 */
function pwcs_header_banner_block() {

	$image = variable_get('pwcs_headerbanner_image', '');
	$text_position = variable_get('pwcs_headerbanner_text_position', 'center');
	$text = variable_get('pwcs_headerbanner_text', '');

	$output  = '';
	$output .= '<div id="header_banner" class="pwcs-header-banner">';
	$output .= $image;
	$output .= '<div class="banner-text" style="text-align:'.$text_position.';">'.$text.'</div>';
	$output .= '</div>';

	return $output;
}

/**
 * BLOCK for Welcome Text
 * @return image/html output of welcome text
 * Added by Rasel Kabir
 */
function pwcs_welcome_text_block() {

	$title = variable_get('pwcs_welcome_text_title', 'Welcome to our Online Store');
	$content = variable_get('pwcs_welcome_text_content', 'Welcome to our online store. We hope and wish you a nice and comfortable experience with our online presentation.');

	$output  = '';
	$output .= '<div id="welcome_text" class="pwcs-welcome-text">';
	$output .= '<h2 class="title">'.$title.'</h2>';
	$output .= '<p class="content">'.$content.'</p>';
	$output .= '</div>';

	return $output;
}

/**
 * BLOCK for Image Slideshow
 * @return image/html output of image slideshow
 * Added by Rasel Kabir
 */
function pwcs_image_slideshow_block_ordinary() {

	if(variable_get('pwcs_admin_enable_imageslideshow', 0) == 0) {
		return '';	
	}

	// Get Node details of URL ALIAS 'siteadmin/imageslideshow'
	$node = node_load(array('type'=>'image_slideshow'));
	if(($node->path == 'siteadmin/imageslideshow')&& (isset($node->field_slideshow_images[0]['filepath']))) {

		drupal_add_css(drupal_get_path('module', 'pwcs') .'/css/pwcs_slideshow.css', 'module');
		drupal_add_js(drupal_get_path('module', 'pwcs') .'/js/pwcs_slideshow.js', 'module');
		$output = '';

		$w = variable_get('pwcs_image_slideshow_width', '650');
		$h = variable_get('pwcs_image_slideshow_height', '250');
		$pos = variable_get('pwcs_image_slideshow_position', 'center');

		$style  = '';
		$style .= 'height:'.$h.'px;width:'.$w.'px;';
		$style .= ($pos=='center') ? 'margin:auto;' : '';
		$style .= ($pos=='left')   ? 'float:left;'  : '';
		$style .= ($pos=='right')  ? 'float:right;' : '';

		$output .= '<div id="pwcs_image_slideshow">';
		$output .= '<div id="primaryCallout" style="'.$style.'">';
		$output_calloutLinks = '';
		$output_image = '';
		$i = 1;
		foreach ($node->field_slideshow_images as $key => $image) {
			$callingurl = '';
			if($image['data']['description'] != '') $callingurl = trim($image['data']['description']);
			$output_calloutLinks .= '<a title="'.$image['data']['title'].'" href="#" rel="'.$i.'">'.$i.'</a>';
			$attributes = array('width'=>$w.'px', 'height'=>$h.'px');
			$output_image .= '<div>'.l(theme('image', $image['filepath'], $image['data']['alt'], $image['data']['title'], $attributes, FALSE), $callingurl, array('attributes' => array('target' => '_self'),'html'=>true )).'</div>';
			$i++;
		}
		$output .= '<span id="calloutLinks">'.$output_calloutLinks.'</span>';
		$output .= $output_image;
		$output .= '<div style="clear:both;"></div>';
		$output .= '</div>';
		$output .= '</div>';
		return $output;
		
	} else {
		return '';
	}
}


/**
 * BLOCK for Image Slideshow Left Right Slide
 * @return image/html output of image slideshow
 * Added by Rasel Kabir
 */
function pwcs_image_slideshow_block() {

	if(variable_get('pwcs_admin_enable_imageslideshow', 0) == 0) {
		return '';	
	}

	// Get Node details of URL ALIAS 'siteadmin/imageslideshow'
	$node = node_load(array('type'=>'image_slideshow'));
	if(($node->path == 'siteadmin/imageslideshow')&& (isset($node->field_slideshow_images[0]['filepath']))) {

		$slidertype = variable_get('pwcs_image_slideshow_slidertype', 'ordinary');
		$w = variable_get('pwcs_image_slideshow_width', '650');
		$h = variable_get('pwcs_image_slideshow_height', '250');
		$style_wh = 'height:'.$h.'px;width:'.$w.'px;';

		$pos = variable_get('pwcs_image_slideshow_position', 'center');
		$style_pos = ($pos=='center') ? 'margin:auto;' : '';
		$style_pos .= ($pos=='left')   ? 'float:left;'  : '';
		$style_pos .= ($pos=='right')  ? 'float:right;' : '';

    $arrows = variable_get('pwcs_image_slideshow_arrows', 'true');
    $navigation = variable_get('pwcs_image_slideshow_navigation', 'false');
    $startstop = variable_get('pwcs_image_slideshow_startstop', 'false');
		$speed = variable_get('pwcs_image_slideshow_speed', '3000');
    $autoPlay = variable_get('pwcs_image_slideshow_autoplay', 'true');

    drupal_add_css(drupal_get_path('module', 'pwcs') .'/anythingslider/css/anythingslider.css', 'module');
    drupal_add_js(drupal_get_path('module', 'pwcs') .'/anythingslider/js/jquery.anythingslider.js', 'module');

		$output = '';
		$output .= '<div id="pwcs_image_slideshow" style="'.$style_pos.'">'; // style="'.$style_wh.'"
		$output .= '<ul id="slider" style="'.$style_wh.'">';
		$output_calloutLinks = '';
		$output_image = '';
		$i = 1;
		foreach ($node->field_slideshow_images as $key => $image) {
			$callingurl = '';
			if($image['data']['description'] != '') $callingurl = trim($image['data']['description']);
			//$output_calloutLinks .= '<a title="'.$image['data']['title'].'" href="#" rel="'.$i.'">'.$i.'</a>';
			$attributes = array('width'=>$w.'px', 'height'=>$h.'px');
			$output_image .= '<li>'.l(theme('image', $image['filepath'], $image['data']['alt'], $image['data']['title'], $attributes, FALSE), $callingurl, array('attributes' => array('target' => '_self'),'html'=>true )).'</li>';
			$i++;
		}
		$output .= $output_image;
		$output .= '</ul>';
		$output .= '</div>';

		$js='$(function() {'.
					'$("#pwcs_image_slideshow #slider").anythingSlider({' .
						'autoPlay : '.$autoPlay.',' .
						'startStopped : false,' .
						'pauseOnHover : true,' .
						'buildArrows : '.$arrows.',' .
						'buildNavigation : '.$navigation.',' .
						'buildStartStop : '.$startstop.',' .
						'delay : '.$speed.',' .
					'});' .
				'});';
		drupal_add_js($js, 'inline', 'footer');

		return $output;
		
	} else {
		return '';
	}
}

/**
 * Site Admin Block Configuration Page
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_blockconfiguration(){

	$output = '';
	$header = array(
		array('data' => t('Name')),
		array('data' => t('Edit')),
	);
	$block_deltas = pwcs_option_values('block_deltas');
	$rows = array();
	foreach($block_deltas as $delta=>$name){
		$editurl = 'admin/build/block/configure/pwcs/'.$delta;
		$rows[] = array($name, l(t('Edit'), $editurl, array('html' => true, 'query' => 'destination=siteadmin/blockconfiguration')));
	}
	$output .= theme('table', $header, $rows);
	
	return $output;
}

/**
 * BLOCK for social media block
 * @return html output of social media icon/text
 * Added by Rasel Kabir
 */
function pwcs_socialmedia_block(){

	$output ='';
	$output .='<div id="pwcs_socialmedia" class="pwcs_socialmedia">';
	$output .='<ul class="socialmedialinks">';

	if(variable_get('pwcs_socialmedia_url_facebook', '') != '')
  $output .='<li class="facebook">'.l(t('Facebook'), variable_get('pwcs_socialmedia_url_facebook', ''), array( 'attributes' => array('target' => '_blank',))).'</li>';

	if(variable_get('pwcs_socialmedia_url_twitter', '') != '')
  $output .='<li class="twitter">'.l(t('Twitter'), variable_get('pwcs_socialmedia_url_twitter', ''), array( 'attributes' => array('target' => '_blank',))).'</li>';

	if(variable_get('pwcs_socialmedia_url_linkedin', '') != '')
  $output .='<li class="linkedin">'.l(t('Linkedin'), variable_get('pwcs_socialmedia_url_linkedin', ''), array( 'attributes' => array('target' => '_blank',))).'</li>';

	if(variable_get('pwcs_socialmedia_url_youtube', '') != '')
  $output .='<li class="youtube">'.l(t('Youtube'), variable_get('pwcs_socialmedia_url_youtube', ''), array( 'attributes' => array('target' => '_blank',))).'</li>';

	if(variable_get('pwcs_socialmedia_url_digg', '') != '')
  $output .='<li class="digg">'.l(t('Digg'), variable_get('pwcs_socialmedia_url_digg', ''), array( 'attributes' => array('target' => '_blank',))).'</li>';

	if(variable_get('pwcs_socialmedia_url_stumbleupon', '') != '')
  $output .='<li class="stumbleupon">'.l(t('Stumbleupon'), variable_get('pwcs_socialmedia_url_stumbleupon', ''), array( 'attributes' => array('target' => '_blank',))).'</li>';

	$output .='</ul>';
	$output .='</div>';

	return $output;
}


/**
 * BLOCK for Image Slideshow Left Right Slide
 * @return image/html output of image slideshow
 * Added by Rasel Kabir
 */
function pwcs_simplevideo_block() {

		$video_url = variable_get('pwcs_simplevideo_url', '');
		$width = variable_get('pwcs_simplevideo_width', '220');
		$height = variable_get('pwcs_simplevideo_height', '160');
		$video_popup = variable_get('pwcs_simplevideo_popup', 0);
		//$style = 'height:'.$height.'px;width:'.$width.'px;';

		if($video_url == '') return '';

		$output = '';
		$output .= '<div id="pwcs_simplevideo">';
		if($video_popup == 1) {
			$video_play_thumb = '';
			$output .= '<a href="'.$video_url.'" rel="lightvideo['.$result->video_id.'|width:400px;]" title="'.t('Click Here To Preview The Video').'">';
			$output .= theme('image', $video_play_thumb, '', '', array('width'=>'auto', 'style'=>'vertical-align: text-bottom;'), FALSE);
			$output .= '</a>';
		} else {
			$trans = array("/watch?v=" => "/embed/", "/v/" => "/embed/");
			$video_url = strtr($video_url, $trans);
			$output .= '<iframe width="'.$width.'" height="'.$height.'" src="'.$video_url.'?rel=0" frameborder="0" allowfullscreen></iframe>';
			//$output .= '<object width="'.$width.'" height="'.$height.'">';
			//$output .= '<param name="movie" value="'.$video_url.'&rel=0"></param>';
			//$output .= '<param name="allowFullScreen" value="true"></param>';
			//$output .= '<param name="allowscriptaccess" value="always"></param>';
			//$output .= '<embed src="'.$video_url.'&rel=0" type="application/x-shockwave-flash" width="'.$width.'" height="'.$height.'" allowscriptaccess="always" allowfullscreen="true"></embed>';
			//$output .= '</object>';
		}
		$output .= '</div>';

		return $output;
}

