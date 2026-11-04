<?php
/**
 * Created on Aug 25, 2010 by Rasel Kabir
 */

/**
 * Get Theme Data
 * 
 */
function pwcs_get_theme_data ($pwcsthemetype = 'pwcs_static_themes') {
  $files = array();
  $result = db_query("SELECT filename, name, status, weight, info FROM {system} WHERE type = 'theme' AND status = 1 AND info LIKE '%%".$pwcsthemetype."%%' ORDER BY weight, name");
  while ($file = db_fetch_object($result)) {
      $themeinfo = $file;
      $themeinfo->basename = basename($file->filename);
      $themeinfo->info = unserialize($file->info);
      $files[$file->name] = $themeinfo;
  }
  uasort($files, 'pwcs_sort_themes_by_info_releasetime');  
  
  return $files;
}

/**
 * SiteAdmin Theme select function
 */
function pwcs_siteadmin_theme(){

	$breadcrumb = array();
	$breadcrumb[] = l(t('Home'), '<front>');
	$breadcrumb[] = l(t('Themes'), 'siteadmin/theme');
	drupal_set_breadcrumb($breadcrumb);

	//$system_theme_data = system_theme_data();
	//echo '<pre>'.print_r ($system_theme_data, true).'</pre>';
	$output = '';
	$output .= pwcs_siteadmin_theme_list_static_gridview();
	$output .= pwcs_siteadmin_theme_list_default();
	$output .= pwcs_siteadmin_theme_list_customer_theme();
	//$output .= pwcs_siteadmin_theme_list_static_tableview();
	return $output;

}

/**
 * SiteAdmin Theme select function
 */
function pwcs_siteadmin_theme_list_default(){

  $pwcsthemedata = pwcs_get_theme_data('pwcs_default');
  
	$current_theme = variable_get('theme_default','none');

	$output = '';
	$output .= '<div class="pwcs_theme_list pwcs_theme_list_default" >';
	$output .= '<h3>'.t('PWCStore Versatile Theme').'</h3>';
	$output .= '<div class="pwcs_table">';

	$rows = array();
	$header = array();
	$count = 0;

	foreach ($pwcsthemedata as $themeid => $theme) {
		$row = array();
    $screenshot_full_path = drupal_get_path('theme',  $themeid).'/screenshot_full.png';
    $preview = l(theme('image',$theme->info['screenshot'], 'Preview', 'Click to Preview', array('class'=>'theme_preview_thumbnail','width'=>'150px'), FALSE), $screenshot_full_path, array('html' => TRUE, 'attributes' => array('rel'=>'lightbox['.$themeid.'][]')));
		$row[] = array('data' => $preview);
		$row[] = array('data' => '<b>'.$theme->info['name'].'</b>'.'<br/>'.$theme->info['description'],  'width'=> '280');
		$row[] = l(t('Config UI/Block'),'admin/build/block/list/'.$themeid, array( 'attributes' => array('class' => 'button',)));
		$row[] = l(t('Config Theme'),'admin/build/themes/settings/'.$themeid, array( 'attributes' => array('class' => 'button',)));
		$row[] = ($themeid == $current_theme)? '<span class="current_item">'.t('Active Theme').'</span>' : l(t('Activate'),'siteadmin/theme/switch/'.$themeid, array( 'attributes' => array('class' => 'button',)));
		$rows[] = $row;
		$count++;
	}

	$output .= theme('table', NULL, $rows, array('width'=>'100%'));

	$output .= '</div>';
	$output .= '</div>';

	return $output;
}

/**
 * SiteAdmin Theme select function
 */
function pwcs_siteadmin_theme_list_customer_theme(){

  $pwcsthemedata = pwcs_get_theme_data('customer_theme');
	$current_theme = variable_get('theme_default','none');

	$output = '';
	$output .= '<div class="pwcs_theme_list pwcs_theme_list_customer" >';

	$rows = array();
	$output .= '<h3>'.t('Your Custom Theme').'</h3>';

	$output .= '<div class="pwcs_table">';
	$count = 0;
	foreach ($pwcsthemedata as $themeid => $theme) {
		$subdomain = pwcs_get_current_subdomain();
		if($subdomain != '') {
			if ($subdomain == $theme->info['customer_subdomain']) {
				$row = array();
				$preview = l(theme('image',$theme->info['screenshot'], 'Preview', 'Click to Preview', array('class'=>'theme_preview_thumbnail','width'=>'150px'), FALSE),'siteadmin/theme/preview/'.$themeid, array('html' => TRUE, 'attributes' => array('target' => '_blank',)));
				$row[] = array('data' => $preview);
				$row[] = array('data' => '<b>'.$theme->info['name'].'</b>'.'<br/>'.$theme->info['description'],  'width'=> '280');
				$row[] = l(t('Config UI/Block'),'admin/build/block/list/'.$themeid, array( 'attributes' => array('class' => 'button',)));
				$row[] = l(t('Theme Settings'),'admin/build/themes/settings/'.$themeid, array( 'attributes' => array('class' => 'button',)));
				$row[] = ($themeid == $current_theme)? '<span class="current_item">'.t('Active Theme').'</span>' : l(t('Activate'),'siteadmin/theme/switch/'.$themeid, array( 'attributes' => array('class' => 'button',)));
				$rows[] = $row;
				$count++;
			}
//		} else {
//				$row = array();
//				$preview = l(theme('image',$theme->info['screenshot'], 'Preview', 'Click to Preview', array('class'=>'theme_preview_thumbnail','width'=>'150px'), FALSE),'siteadmin/theme/preview/'.$themeid, array('html' => TRUE, 'attributes' => array('target' => '_blank',)));
//				$row[] = array('data' => $preview);
//				$row[] = array('data' => '<b>'.$theme->info['name'].'</b>'.'<br/>'.$theme->info['description'],  'width'=> '280');
//				$row[] = l(t('Config UI/Block'),'admin/build/block/list/'.$themeid, array( 'attributes' => array('class' => 'button',)));
//				$row[] = l(t('Theme Settings'),'admin/build/themes/settings/'.$themeid, array( 'attributes' => array('class' => 'button',)));
//				$row[] = ($themeid == $current_theme)? '<span class="current_item">'.t('Active Theme').'</span>' : l(t('Activate'),'siteadmin/theme/switch/'.$themeid, array( 'attributes' => array('class' => 'button',)));
//				$rows[] = $row;
//				$count++;
		}
	}

	if($count == 0){
		$output .= '<div class="warning" style="margin:10px;">'.t('There is currently no custom theme configured. Please submit a request in order to have a custom theme built for your account.').'</div>';
	} else {
		$output .= theme('table', NULL, $rows, array('width'=>'100%'));
	}

	$output .= '</div>';
	$output .= '</div>';

	return $output;
}

/**
 * SiteAdmin Theme select function
 */
function pwcs_siteadmin_theme_list_static_tableview(){

  $pwcsthemedata = pwcs_get_theme_data ('pwcs_static_themes');
	if(count($pwcsthemedata) < 1) return '';

	$current_theme = variable_get('theme_default','none');
	$header = array(
		t('Preview'),
		t('Details'),
		t('UI settings'),
		t('Switch theme')
	);
	$rows = array();

	$output = '';
	$output .= '<div class="pwcs_theme_list pwcs_theme_list_tableview" >';
	$output .= '<h3>'.t('Static Themes').'</h3>';
	$output .= '<div class="pwcs_table">';
	foreach ($pwcsthemedata as $themeid => $theme) {
		$row = array();
		//$preview = l(theme('image',$theme->info['screenshot'], 'Preview', 'Click to Preview', array('class'=>'theme_preview_thumbnail','width'=>'150px'), FALSE),'siteadmin/theme/preview/'.$themeid, array('html' => TRUE, 'attributes' => array('target' => '_blank',)));
    $screenshot_full_path = drupal_get_path('theme',  $themeid).'/screenshot_full.png';
    //if(file_exists(base_path().$screenshot_full_path)) {
      $preview = l(theme('image',$theme->info['screenshot'], 'Preview', 'Click to Preview', array('class'=>'theme_preview_thumbnail','width'=>'150px'), FALSE), $screenshot_full_path, array('html' => TRUE, 'attributes' => array('rel'=>'lightbox['.$themeid.'][]')));
    //}
		$row[] = array('data' => $preview);
		$row[] = array('data' => '<b>'.$theme->info['name'].'</b>'.'<br/>'.$theme->info['description'],  'width'=> '280');
		$row[] = l(t('Config UI/Block'),'admin/build/block/list/'.$themeid, array( 'attributes' => array('class' => 'button',)));
		$row[] = l(t('Theme Settings'),'admin/build/themes/settings/'.$themeid, array( 'attributes' => array('class' => 'button',)));
		$row[] = ($themeid == $current_theme)? '<span class="current_item">'.t('Active Theme').'</span>' : l(t('Activate'),'siteadmin/theme/switch/'.$themeid, array( 'attributes' => array('class' => 'button',)));
		$rows[] = $row;
	}
	$output .= theme('table', $header, $rows, array('width'=>'100%'));
 	$output .= '</div>';
  $output .= '</div>';

	return $output;
}

/**
 * SiteAdmin Theme select function
 */
function pwcs_siteadmin_theme_list_static_gridview(){

  $pwcsthemedata = pwcs_get_theme_data('pwcs_static_themes');
  //echo '<pre>'.print_r ($pwcsthemedata, true).'</pre>'; exit;
  
	$count = count($pwcsthemedata);
	if($count < 1) return '';

	$current_theme = variable_get('theme_default','none');

	$output = '';
  $output .= '<div class="pwcs_theme_list pwcs_theme_list_gridview">';
	$output .= '<h3>'.t("Static Themes").'</h3>';
	$output .= '<p class="sub-heading">'.t("We have !count static themes. You can use any of these themes just by clicking \"activate\" button.", array('!count'=>'<b>'.$count.'</b>')).'</p>';
	$output_gird = '';
  $output_current = '';

	foreach ($pwcsthemedata as $themeid => $theme) {

    $new = '';
		if($theme->info['releasetime'] > (time() - 30 * 24 * 60 * 60)) {
		  $new = 'newtheme';
		}

		if($themeid == $current_theme) {
			$output_current .= '<div class="themelist_gridview_currenttheme '.$new.'">';
			$output_current .= '<div class="themelist_gridview_currenttheme_inner">';

			$output_current .= '<div class="screenshot">';
			$output_current .= theme('image',$theme->info['screenshot'], '', '', array('class'=>'theme_preview_thumbnail','width'=>'150px'), FALSE);
			$output_current .= '</div>';

			$output_current .= '<div class="name">'.$theme->info['name'].'</div>';
			$output_current .= '<div class="description">'.$theme->info['description'].'</div>';

			$output_current .= '<div class="current_item">'.t('Active Theme').'</div>';
			$output_current .= '<div class="buttons">';
	  	$output_current .= l(t('Site Information'),'admin/settings/site-information', array('query' => 'destination=siteadmin/theme', 'attributes' => array('class' => 'button siteinformation',)));
	  	$output_current .= l(t('Basic Settings'),'siteadmin/sitesettings', array('query' => 'destination=siteadmin/theme', 'attributes' => array('class' => 'button sitesettings',)));
	  	$output_current .= l(t('Config UI/Block'),'admin/build/block/list/'.$themeid, array('query' => 'destination=siteadmin/theme', 'attributes' => array('class' => 'button blockconfig',)));
		  $output_current .= l(t('Theme Settings'),'admin/build/themes/settings/'.$themeid, array('query' => 'destination=siteadmin/theme', 'attributes' => array('class' => 'button themeconfig',)));
	  	$output_current .= l(t('Preview Site'),'siteadmin/theme/preview/'.$themeid, array('query' => 'destination=siteadmin/theme', 'html' => TRUE, 'attributes' => array('class' => 'button demotheme', 'rel'=>'lightframe[|width:1080px; height:580px;]')));
			$output_current .= '</div>';

      $output_current .= '<div style="clear:both;"></div>';
      $output_current .= '</div>';
	
			$output_current .= '</div>';
		} 
		else 
		{
			$output_each = '';
			$output_each .= '<div class="themelist_gridview_each '.$new.'">';
			$output_each .= '<div class="themelist_gridview_each_inner">';
	
			$output_each .= '<div class="screenshot">';
			$screenshot_full_path = drupal_get_path('theme',  $themeid).'/screenshot_full.png';
			if(file_exists($screenshot_full_path)) {
				$output_each .= l(theme('image',$theme->info['screenshot'], 'Zoom', 'Click To Zoom The Screenshot', array('class'=>'theme_preview_thumbnail','width'=>'150px'), FALSE), $screenshot_full_path, array('html' => TRUE, 'attributes' => array('rel'=>'lightbox[]')));
			} else {
				$output_each .= theme('image',$theme->info['screenshot'], '', '', array('class'=>'theme_preview_thumbnail','width'=>'150px'), FALSE);
			}
			$output_each .= '</div>';
	
			$output_each .= '<div class="name">'.$theme->info['name'].'</div>';
			$output_each .= '<div class="description">'.$theme->info['description'].'</div>';

			$output_each .= '</div>';
	
			$output_each .= '<div class="buttons">';
			if($theme->info['demourl']) {
	  		$output_each .= l(t('Demo'),$theme->info['demourl'], array('html' => TRUE, 'attributes' => array('class' => 'button demotheme', 'rel'=>'lightframe[|width:1080px; height:580px;]')));
			}
  		$output_each .= l(t('Preview'),'siteadmin/theme/preview/'.$themeid, array('html' => TRUE, 'attributes' => array('class' => 'button demotheme', 'rel'=>'lightframe[|width:1080px; height:580px;]')));
		  $output_each .= l(t('Activate'),'siteadmin/theme/switch/'.$themeid, array( 'attributes' => array('class' => 'button activate',)));
			$output_each .= '</div>';
	
			$output_each .= '</div>';
			$output_gird .= $output_each;
		}
	}
	$output .= $output_current.$output_gird;
  $output .= '<div style="clear:both;"></div>';
  $output .= '</div>';
	return $output;
}

/**
* Action to change the theme.
*/
function pwcs_siteadmin_theme_switch($themeid) {
  global $user;
  if ($user->uid > 0 && user_access('is siteadmin')) {

	pwcs_flush_cache();
    
  variable_set('theme_default', $themeid);
	//drupal_set_message(t('Theme Switched Successfully. You Can Change Basic Settings From !basic-settings.', array('!basic-settings' => l('Site Builder >> Basic Settings', 'siteadmin/sitesettings'))));
  drupal_set_message(t('Theme Switched Successfully.'));
	//variable_set('pwcs_theme_switch_set_block_'.$themeid, 0);
	if(variable_get('pwcs_theme_switch_set_block_'.$themeid, 0) == 0) {
	    $set_block = pwcs_siteadmin_theme_switch_set_block($themeid);
		if($set_block == TRUE) {
			drupal_set_message(t('And Blocks are set to there positions.'));
		}
		else {
			drupal_set_message(t('But Blocks are not set to there positions.'), 'warning');
		}
	}
    
  } else {
  	drupal_set_message('Invalid Access!', 'error');
  }

  drupal_goto('siteadmin/theme');
}

/**
* Action to change the theme.
*/
function pwcs_siteadmin_theme_preview($themeid) {
  global $user,$base_url;
  
  $old_theme = variable_get('theme_default','pwcs_default'); 
  variable_set('theme_default', $themeid);
  $target_url =  $base_url.'/';

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
  variable_set('theme_default', $old_theme);
  die($response);
}

/**
 * SiteAdmin User Theme Switch > set block
 * Added By Rasel Kabir
 */
function pwcs_siteadmin_theme_switch_set_block($themeid = NULL) {

	if($themeid == NULL) $themeid = 'pwcs_default';

	$block_settings = pwcs_siteadmin_theme_switch_get_block_settings($themeid);

	if(!empty($block_settings)) {
		db_query("UPDATE {blocks} SET `status` = %d WHERE theme = '%s'", 0, $themeid);
		$weight=0;
		foreach($block_settings as $delta => $region) {
			db_query("UPDATE {blocks} SET `status` = %d, `weight` = %d, `region` = '%s' WHERE `delta` = '%s' AND theme = '%s'", 1, $weight, $region, $delta, $themeid);
			$weight++;
		}
		variable_set('pwcs_theme_switch_set_block_'.$themeid, 1);
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * SiteAdmin User Theme Switch > get block settings from .info file
 * Added By Rasel Kabir
 */
function pwcs_siteadmin_theme_switch_get_block_settings($themeid = NULL){

	if($themeid == NULL) return array();
	
	$block_settings = array();

	$system_theme_data = system_theme_data();
	if(isset($system_theme_data[$themeid]->info['setblock'])){
		$block_settings[$themeid] = $system_theme_data[$themeid]->info['setblock'];
	}

	return $block_settings[$themeid];
}


/**
 * SiteAdmin SiteadminTheme Change
 * Added By Rasel Kabir
 */
function pwcs_siteadmin_siteadmintheme_switch($themeid = 'siteadmintheme') {

	if (user_access('is siteadmin')) {
		global $user;
		db_query("UPDATE {users} SET  theme = '%s' WHERE uid = %d", $themeid, $user->uid);
		pwcs_siteadmin_theme_switch_set_block($themeid);
		pwcs_flush_cache();

		if($themeid == 'siteadmintheme') {
			drupal_set_message('You Site Admin Theme Has Successfully Switched To New One');
			$button = l('Switch Back To Old Theme', 'siteadmin/siteadmintheme/switch/pwcs_admin', array('html' => true, 'attributes' => array('class'=>'button', 'title'=>'Switch Siteadmin Theme to New One')));
			drupal_set_message('To Switch Back To The Previous User Interface > '.$button);
		} else {
			drupal_set_message('You Site Admin Theme Has Successfully Reverted To Old One');
		}

		drupal_goto('siteadmin');
	} else {
		drupal_goto('<front>');
	}

}

/**
 * Array sorting callback; sorts themes by their name.
 */
function pwcs_sort_themes_by_info_name($a, $b) {
  return strcasecmp($a->info['name'], $b->info['name']);
}

function pwcs_sort_themes_by_info_releasetime($a, $b) {
  return ($a->info['releasetime'] < $b->info['releasetime']);
}