<?php
/* $Id: Helper/Common functions pwcs_common.php,v 1.0 2010/04/30 Rasel Kabir Shuvro Exp $ */

/**
 * Function (Common) to get Option values
 * @param $ch = option type/name
 * @return array for select/radios options
 * Added by Rasel Kabir
 */
function pwcs_option_values($ch = null)
{
  switch($ch)
  {    
//    case 'ws_photographer':
//		$photographer_role = user_roles ( FALSE, 'is photographer');
//		$photographer_role1 = array_keys($photographer_role);
//		$rid = $photographer_role1[0];
//	
//		$sql  = "SELECT u.uid, u.name, u.status FROM {users} u";
//		$sql .= " INNER JOIN {users_roles} ur ON u.uid=ur.uid";
//		$sql .= " WHERE ur.rid = %d AND u.status = 1";
//		$sql .= " ORDER BY u.name ASC";
//
//		$result = db_query($sql, $rid);
//		while ($u = db_fetch_object($result)) {
//		  $values[$u->uid] = $u->name;
//		}
//
//    return $values;  

//    case 'categories':
//	  
//		$values['1'] = t('Cat Name');
//	
//		return $values;

    case 'product_filter_display_per_page':
		for($i=5; $i<=400; $i=$i+5){
			$values[$i] = $i.' '.t('Products');
  		}	
		return $values;

/*
    case 'product_reviews':
		$values = array(	'item1' => t('Item 1'),
											'item2' => t('Item 2'),
											'item3' => t('Item 3'),
											'item4' => t('Item 4'),
											'item5' => t('Item 5'),
											'item6' => t('Item 6'),
											'item7' => t('Item 7'),
										);
		return $values;
*/

    case 'block_deltas':
		$values = array(
			'sidebar_category_menu' => t('Category Menu'),
			'sidebar_listing_menu' => t('Listing Menu'),
			'sidebar_manufacture_block' => t('Manufacturer Block'),
			'homepage_products' => t('Home Page Products Block'),
			'header_banner' => t('Header Banner'),
			'cart_link' => t('Cart Link'),
			'fatured_products' => t('Featured Products'),
			'sitesearch_input' => t('Search Box'),
			'topsearch_keywords' => t('Top Search Keywords'),
		);
		return $values;

    default:

		return array();
  }

}


/**
 * Function to convert drupal checkboxes item to an array
 * @param $checkbox_drupal = drupal checkbox array
 * @return array in php
 * Added by Rasel Kabir
 */
function pwcs_checkboxes_to_array($checkbox_drupal = array())
{
	$checkbox_array = array();
	if (is_array($checkbox_drupal)){
		$checkbox_drupal = @array_filter ($checkbox_drupal);
		$checkbox_array = @array_values($checkbox_drupal);
	}
	return $checkbox_array;
}

/**
 * Function to convert php array to an drupal checkboxes item
 * @param $checkbox_array = array in php 
 * @return drupal checkbox array
 * Added by Rasel Kabir
 */
function pwcs_array_to_checkboxes($checkbox_array = array())
{
  $checkbox_drupal = array();
	if (is_array($checkbox_array)){
	  $checkbox_drupal = array_keys($checkbox_array);
	}
  return $checkbox_drupal;
}


/**
 * Helper function to return formatted date from given drupal date array
 * @param array date array
 * @param string date format
 * @return string formatted date
 * Added by Rasel Kabir
 */
function pwcs_format_date_array($date, $format = TIME_FORMAT)
{
  if( !is_array($date) )
    throw new Exception('Given date is not array');

  return date($format, mktime(0, 0, 0, $date['month'], $date['day'], $date['year']));
}

/**
 * Helper function to gen drupal date array
 * @param int epoc
 * @return array current date or given timestamp in array
 * Added by Rasel Kabir
 */
function pwcs_get_date_array($date_string = NULL)
{
  
  if( empty($date_string) )
  {
    $date = array(
      'year' => date('Y'),
      'month' => date('n'),
      'day' => date('j')
    );
  }
  else
  {
    $timestamp = strtotime($date_string);
    $date = array(
      'year' => date('Y', $timestamp),
      'month' => date('n', $timestamp),
      'day' => date('j', $timestamp)
    );
  }

  return $date;
}

/**
 * Helper function to Count the number of results of a SQL [Rasel Kabir]
 * @param object $sql
 * @return count of result
 * Added by Rasel Kabir
 */
function pwcs_result_count($sql)
{
  return db_result(db_query(preg_replace(array('/SELECT.*?FROM /As', '/ORDER BY .*/'), array('SELECT COUNT(*) FROM ', ''), $sql)));
}

/**
 * 
 * Validation pattern for webshop forms
 * 
 * Regular Expression 	Will match...
 * foo 					The string "foo"
 * ^foo 				"foo" at the start of a string
 * foo$ 				"foo" at the end of a string
 * ^foo$ 				"foo" when it is alone on a string
 * [abc] 				a, b, or c
 * [a-z] 				Any lowercase letter
 * [^A-Z] 				Any character that is not a uppercase letter
 * (gif|jpg) 			Matches either "gif" or "jpeg"
 * [a-z]+ 				One or more lowercase letters
 * [0-9\.\-] 			?ny number, dot, or minus sign
 * ^[a-zA-Z0-9_]{1,}$ 	Any word of at least one letter, number or _
 * ([wx])([yz]) 		wy, wz, xy, or xz
 * [^A-Za-z0-9] 		Any symbol (not a number or a letter)
 * ([A-Z]{3}|[0-9]{4}) 	Matches three letters or four numbers
 * 
 * Added by Rasel Kabir
 */

function pwcs_check_validation($type=NULL, $string=NULL)
{
	if ($type != NULL){

		switch ($type) {
		case 'name': 
			$regex = '/([a-zA-Z]){2,}$/'; 
			break;
		case 'email':
			$regex = '/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/';
		    break;
		case 'phone':
			$regex = '/([0-9_\+\-]){2,}$/';
		    break;
		default:
			$regex = '/([0-9a-zA-Z])$/';
			break;
		}

	} else {
		return FALSE;
	}

	if ($string != NULL) {
		if(preg_match($regex,$string)) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}


/**
 * Flatten a nested array
 * Added by Rasel Kabir
 */
function _pwcs_flatten_array($array) {
  $return = array();

  foreach ($array as $key => $value) {
    if (is_array($value)) {
      $return += _pwcs_flatten_array($value);
    }
    else {
      $return[$key] = $value;
    }
  }
  return $return;
}

/**
 * Developer Tools to Write Debug File
 * Added By Rasel Kabir
 */
function pwcs_debug_file_write($stringData, $is_array=true , $myFile = "pwcsdebug.txt") {

	if(variable_get('pwcs_dev_debug', 1) == 0) {
		//debug mode off
		return;
	}

	if(is_array($stringData)) {
		$stringData = print_r($stringData, true);
	}
	$fh = fopen($myFile, 'a') or die("can't open file");
	fwrite($fh, "\n");
	fwrite($fh, "\n");
	fwrite($fh, "File write time: ".date('r'));
	fwrite($fh, "\n");
	fwrite($fh, $stringData);
	fwrite($fh, "\n");
	fwrite($fh, "\n");
	fclose($fh);
}

/**
 * Developer Tools to clean Debug File
 * Added By Rasel Kabir
 */
function pwcs_dev_debugfileclean($myFile = "pwcsdebug.txt") {

	$fh = fopen($myFile, 'w') or die("can't open file");
	fclose($fh);
	drupal_set_message($myFile . ' is cleared!');
	
	//DELETE ALL UNUSED TABLES
	//pwcs_dev_dbclear();

	drupal_goto('admin/settings/pwcs');
}

/**
 * Developer Tools to clean DB
 * Added By Rasel Kabir
 */
function pwcs_dev_dbclear(){

    // Clear the node table where type = NULL, type = product-back
	$sql  = "SELECT n.nid FROM {node} n WHERE n.type = '' OR n.type = 'product-back'";
	$results = db_query($sql);
	while($result = db_fetch_object($results))
	{
		node_delete($result->nid);
	}
	unset($_SESSION['messages']);
	drupal_set_message('Node Table Cleared!');
	
	// Delete Aggregator tables, this was used in development, later on its not needed any more.
	if(db_query("DROP TABLE {aggregator_category} , {aggregator_category_feed} , {aggregator_category_item} , {aggregator_feed}, {aggregator_item}"))
	drupal_set_message('Aggregator tables are removed!');
	else 
	drupal_set_message('No Aggregator tables.', 'warning');

	// Delete Ctools tables, this was used in development, later on its not needed any more.
	if(db_query("DROP TABLE {ctools_access_ruleset} , {ctools_css_cache} , {ctools_custom_content} , {ctools_object_cache}"))
	drupal_set_message('Ctools tables are removed!');
	else 
	drupal_set_message('No Ctools tables.', 'warning');

	// Delete Fupload tables, this was used in development, later on its not needed any more.
	if(db_query("DROP TABLE {fupload_previewlist}"))
	drupal_set_message('Fupload tables are removed!');
	else 
	drupal_set_message('No Fupload tables.', 'warning');

	// Delete PageManager tables, this was used in development, later on its not needed any more.
	if(db_query("DROP TABLE {page_manager_handlers} , {page_manager_pages} , {page_manager_weights}"))
	drupal_set_message('PageManager tables are removed!');
	else 
	drupal_set_message('No PageManager tables.', 'warning');

	// Delete Panels tables, this was used in development, later on its not needed any more.
	if(db_query("DROP TABLE {panels_display} , {panels_layout} , {panels_pane} , {panels_renderer_pipeline}"))
	drupal_set_message('Panels tables are removed!');
	else 
	drupal_set_message('No Panels tables.', 'warning');

	// Delete PluginManager tables, this was used in development, later on its not needed any more.
	if(db_query("DROP TABLE {plugin_manager_files} , {plugin_manager_queue} , {plugin_manager_repository} , {plugin_manager_taxonomy}"))
	drupal_set_message('PluginManager tables are removed!');
	else 
	drupal_set_message('No PluginManager tables.', 'warning');

	// Delete Webfm tables, this was used in development, later on its not needed any more.
	if(db_query("DROP TABLE {webfm_attach} , {webfm_file}"))
	drupal_set_message('Webfm tables are removed!');
	else 
	drupal_set_message('No Webfm tables.', 'warning');

	//LISTING PRODUCT ORDER TABLE CLEAN
	db_query('TRUNCATE TABLE {pwcs_product_order}');
    drupal_set_message('pwcs_product_order table cleared!');

	drupal_goto('admin/settings/pwcs');
}

/**
 * Developer FUNCTION for TESTING
 * Added By Rasel Kabir
 */
function pwcs_developer_test(){

	//TEST URL FOR DEVELOPER
	//$perms = array('access comments', 'post comments', 'post comments without approval');
	//pwcs_addpermission($perms , 'anonymous user');
	//pwcs_removepermission($perms , 'anonymous user');

	return;
}

/**
 * Tag cloud generator
 * $tags[0]['name'] = 'Tag name'
 * $tags[0]['amount'] = 12
 * $tags[0]['link'] = url
 * Added by Rasel Kabir
 */
function pwcs_print_tag_cloud($tags) {
       
    //arsort($tags);
	$max_size = 32;
	$min_size = 12;
	
	$tagstmp = array();
	foreach ($tags as $key => $value) {
		$tagstmp[$value['name']] = $value['amount'];
	}
	$max_qty = @max(array_values($tagstmp));
	$min_qty = @min(array_values($tagstmp));

	$spread = $max_qty - $min_qty;
	if ($spread == 0) $spread = 1;
	$step = ($max_size - $min_size) / ($spread);
	$output = '';
	foreach ($tags as $key => $value) {
		$size = round($min_size + (($value['amount'] - $min_qty) * $step));
        //$output .=  '<a href="'.$value['link'].'" style="font-size:'.$size.'px;" class="tagcloud-link" title="'.$value['amount'].' times">'.$value['name'].'</a>';
		$output .=  l(t($value['name']), $value['link'] , array('attributes' => array('style'=>'font-size:'.$size.'px;', 'class'=>'pwcs-tagcloud-link', 'title'=> $value['amount'].' '.t('times'))));
	}
	return $output;
}

function pwcs_get_current_subdomain() {
	global $base_root;
	$currentdomain = parse_url($base_root);	
	if(substr_count($currentdomain['host'], '.') > 1) {
		$currentdomain_array = explode(".", $currentdomain['host']);
		return $currentdomain_array[0];
	} else {
		return '';
	}
}
function pwcs_invisibleproduct_count() {
	$countrows = db_result(db_query("SELECT COUNT(*) FROM {node} n WHERE n.type = 'product' AND n.status=0"));
	return ($countrows > 0) ? $countrows : 0;
}

/**
 * Drupal Set Permissions for a Role
 * Added By Rasel Kabir
 */
function pwcs_addpermission($permission='', $role=NULL) {

  if(($permission == '') || ($role == NULL)) {
	  drupal_set_message(t('No permissions or role has provided.'));
  } else {
      $role = trim($role);
  	  if(is_numeric($role)){
        $rid = $role;
      } else {
	    $rid = db_result(db_query("SELECT rid FROM {role} WHERE name = '%s'", $role));
      }
	  $perm = db_result(db_query("SELECT perm FROM {permission} WHERE rid = %d", $rid));
	  $perm_array = explode(", ", $perm);
	
	  if(is_array($permission)) {
	    foreach($permission as $k=>$perm_val) {
            if(!in_array($perm_val, $perm_array)) {
		      $perm_array[] = $perm_val;
            }
	    }
	  } else {
        if(!in_array($permission, $perm_array)) {
		  $perm_array[] = $permission;
        }
	  }
	  asort($perm_array);
	
	  db_query('DELETE FROM {permission} WHERE rid = %d', $rid);
	  db_query("INSERT INTO {permission} (rid, perm) VALUES (%d, '%s')", $rid, implode(', ', $perm_array));
	
	  //drupal_set_message(t('The permissions have been saved.'));
	  cache_clear_all();
  }
}

/**
 * Drupal Set Permissions for a Role
 * Added By Rasel Kabir
 */
function pwcs_removepermission($permission='', $role=NULL) {

  if(($permission == '') || ($role == NULL)) {
	  drupal_set_message(t('No permissions or role has provided.'));
  } else {
      $role = trim($role);
  	  if(is_numeric($role)){
        $rid = $role;
      } else {
	    $rid = db_result(db_query("SELECT rid FROM {role} WHERE name = '%s'", $role));
      }
	  $perm = db_result(db_query("SELECT perm FROM {permission} WHERE rid = %d", $rid));
	  $perm_array = explode(", ", $perm);

	  if(is_array($permission)) {
	    foreach($permission as $k=>$perm_val) {
			$key = array_search($perm_val, $perm_array);
            if($key != NULL) {
				unset($perm_array[$key]);
            }
	    }
	  } else {
		$key = array_search($permission, $perm_array);
        if($key != NULL) {
			unset($perm_array[$key]);
        }
	  }
	  asort($perm_array);
	
	  db_query('DELETE FROM {permission} WHERE rid = %d', $rid);
	  db_query("INSERT INTO {permission} (rid, perm) VALUES (%d, '%s')", $rid, implode(', ', $perm_array));
	
	  //drupal_set_message(t('The permissions have been saved.'));
	  cache_clear_all();
  }
}

/**
 * 
 */
function _pwcs_getnid_from_path($path = NULL) {
	
	if ($path == NULL) return FALSE;
	$src = db_result(db_query("SELECT src FROM {url_alias} WHERE dst = '%s'", $path));
	$nid = str_replace ('node/', '', trim($src));
	if(is_numeric ($nid)) return $nid;
	else return FALSE;
}


function pwcs_cleanListingProblem()
{
	$results=db_query("SELECT * FROM {pwcs_listings}");
	while($result=db_fetch_object($results)) 
	{
		if (db_result(db_query('SELECT pwc_category_id FROM {pwcs_listings} WHERE pwc_category_id = %d', $result->parent_category_id))!= $result->parent_category_id){
			db_query('UPDATE {pwcs_listings} SET parent_category_id = "0"  WHERE `category_id` = '.$result->category_id);
		}else{
		}
  	}	

	$results=db_query("SELECT * FROM {pwcs_category}");
	while($result=db_fetch_object($results)) 
	{
		if (db_result(db_query('SELECT pwc_category_id FROM {pwcs_category} WHERE pwc_category_id = %d', $result->parent_category_id))!= $result->parent_category_id){
			db_query('UPDATE {pwcs_category} SET parent_category_id = "0"  WHERE `category_id` = '.$result->category_id);
		}else{
		}
  	}	

	
}

/**
 * Check If Image Preset Available or not
 * Added By Rasel Kabir
 */
function _pwcs_isset_imagecache_preset($presetname) {
	if (module_exists('imagecache')) {
  	return db_result(db_query("SELECT presetid FROM {imagecache_preset} WHERE presetname = '%s'", $presetname));
	} else 
	  return NULL;
}

