<?php
/* $Id: pwcs_search.php,v 1.0 2010/12/26 Rasel Kabir Shuvro <shuvro.mist@gmail.com> Exp $ */

define ('MIN_SEARCH_KEYWORD_LENGTH', variable_get('pwcs_sitesettings_min_search_keyword_length', '3'));

/**
 * Implementation of SiteWide Search for PWCS
 * Added by Rasel Kabir
 */
function pwcs_sitesearch_input_block() {
	return drupal_get_form('pwcs_sitesearch_form');
}

/**
 * Implementation of pwcs_sitesearch_form()
 * Added by Rasel Kabir
 */
function pwcs_sitesearch_form($form_info = array(), $keyword = NULL) {
	$form['#prefix'] = '<div class="pwcs_sitesearch_form_div">';
	$form['search_keyword'] = array( '#title' => t('Enter Keyword'), '#type' => 'textfield', '#default_value' => $keyword, '#size'=>40);
	$form['search_op'] = array( '#type' => 'submit', '#default_value' => t('Search') );
	$form['#suffix'] = '</div>';
	return $form;
}

/**
 * pwcs_sitesearch_form form submit
 * Added by Rasel Kabir
 */
function pwcs_sitesearch_form_submit($form_id, $form_values)
{
	global $user;
	if($form_values[submitted] == 1) {
		if($form_values['clicked_button']['#parents'][0] == 'search_op') {
			//print_r ($form_values['values']);exit;
			$keyword = $form_values['values']['search_keyword'];
			//$_SESSION['pwcs_sitesearch_keyword'] = $keyword;
			$keyword=trim($keyword);
			$keyword=strip_tags($keyword);
			$keyword=stripslashes($keyword);
			if(strlen($keyword) > MIN_SEARCH_KEYWORD_LENGTH) {
				watchdog('search', '%keys (@type).', array('%keys' => $keyword, '@type' => 'pwcs'), WATCHDOG_NOTICE, l(t('results'), 'sitesearch/all/'.$keyword));
			}
			$_REQUEST[destination] = 'sitesearch/all/'.$keyword;
		} else {
			//unset($_SESSION['pwcs_sitesearch_keyword']);
		}
	}
}

/**
 * Implementation of SiteWide Search for PWCS
 * Added by Rasel Kabir
 */
function pwcs_sitesearch($type='all', $keyword = '') {
	
	$output = '';
	$output .= drupal_get_form('pwcs_sitesearch_form', $keyword);

	if(strlen($keyword) < MIN_SEARCH_KEYWORD_LENGTH) {
		$output .= '<div class="warning"> ' . t('Your search keyword should have at least 3 character!') . ' </div>';
		return $output;
	}

	$keyword=strtolower($keyword);
	$keyword=str_replace("'", "\\'", $keyword);
	$keyword_array=explode(" ",$keyword);
	foreach($keyword_array as $key=>$val){
		if(strlen($val) < MIN_SEARCH_KEYWORD_LENGTH){
			unset($keyword_array[$key]);
		}
	}
	if(empty($keyword_array)) {
		//drupal_set_messgae();
		$output .= '<div class="warning"> ' . t('None of your search keyword(s) have at least 3 character!') . ' </div>';
	} else {
		
		//log for each word
//		foreach($keyword_array as $k=>$v) {
//			watchdog('search', '%keys (@type).', array('%keys' => $v, '@type' => module_invoke($type, 'search', 'name')), WATCHDOG_NOTICE, l(t('results'), 'search/'. $type .'/'. $keys));
//		}
		
		// do search on product, category, manufacturer, sitepage
		if($type=='all'){
			$limit = 5;
			$searchresult = '';
			$searchresult .= pwcs_search_action($keyword_array, 'category', $limit);
			$searchresult .= pwcs_search_action($keyword_array, 'manufacturer', $limit);
			$searchresult .= pwcs_search_action($keyword_array, 'product', $limit);
			$searchresult .= pwcs_search_action($keyword_array, 'sitepage', $limit);
			
			if($searchresult == ''){
				$output .= '<div class="warning"> ' . t('No items found!') . ' </div>';
			} else {		
					$output .= $searchresult;
			}
		} else {
			$output .= pwcs_search_action($keyword_array, $type);
		}
	}
	return $output;
}

function pwcs_search_action($keyword_array, $item, $limit=NULL){

	$keyword = implode (" ", $keyword_array);

	switch ($item) {
		case 'product':
			// Product
			// {pwcs_product} >> product_sku
			// {node} >> type=product, title
			// {node_revisions} >> body, teaser
			$sql  = "SELECT n.nid, n.title, nr.body, nr.teaser, pp.product_sku FROM {node} n";
			$sql .= " Inner Join {pwcs_product} pp ON n.nid = pp.nid";
			$sql .= " Inner Join {node_revisions} nr ON n.nid = nr.nid";
			$sql .= " WHERE n.type = 'product' AND n.status = 1";
			$q = array();
			foreach($keyword_array as $key=>$val){
				$q[] = "LOWER(n.title) like '%%$val%%'";
				$q[] = "LOWER(nr.teaser) like '%%$val%%'";
				$q[] = "LOWER(nr.body) like '%%$val%%'";
				$q[] = "LOWER(pp.product_sku) like '%%$val%%'";
			}
			$sql .= " AND ( ".implode(' OR ', $q)." )";
			$sql .= " ORDER BY n.changed desc";
			if($limit!=NULL) {
			$sql .= " LIMIT 0, $limit";
			}

			$sql = str_replace("  ", " ", $sql);		
			//print_r ($sql); exit;

			$countsql = pwcs_result_count($sql);
			$output = '';
			$output .= '<div class="search_results_product_list" style="margin: 15px 0; ">';
			$output .= '<h3 style="border-bottom:1px solid #ddd; margin:2px 0;">'.t('@count Products Found', array('@count' => $countsql)).':'.'</h3>';
			if($countsql < 1) {
				$output .= '<div class="warning"> ' . t('No products found!') . ' </div>';
				return '';
			} else {
				$output .= '<div class="search_result_products">';
				$results = db_query($sql);
				$categories = pwcs_cat_array();
				while($result = db_fetch_object($results)) {
					$product = node_load($result->nid);
					$product->view_category = $categories[$product->pwc_category_id]?$categories[$product->pwc_category_id]:'<em>'.t('None').'</em>';
					$output .= pwcs_single_product_view('spv_search', $product);
				}
				$output .= '<div style="clear:both;"></div>';
				$output .= '</div>';
				if(($limit != NULL) && ($countsql > $limit))
				$output .='<div class="search_results_full">'.l(t('See Full Search results for Products'), 'sitesearch/product/'.$keyword, array('attributes' => array('class'=>'moredetails'))).'</div>';
			}
			$output .= '</div>';
			return $output;
			//break;

		case 'category':
			// Category
			// {pwcs_category} >> category_title
			$sql  = "SELECT * FROM {pwcs_category}";
			$sql .= " WHERE 1";
			$q = array();
			foreach($keyword_array as $key=>$val){
				$q[] = "LOWER(category_title) like '%%$val%%'";
			}
			$sql .= " AND ( ".implode(' OR ', $q)." )";
			$sql .= " ORDER BY category_title";
			$sql = str_replace("  ", " ", $sql);		
			//print_r ($sql);

			$countsql = pwcs_result_count($sql);
			$output = '';
			$output .= '<div class="search_results_category_list" style="margin: 15px 0; ">';
			$output .= '<h3 style="border-bottom:1px solid #ddd; margin:2px 0;">'.t('@count Categories Found', array('@count' => $countsql)).':'.'</h3>';
			if($countsql < 1) {
				$output .= '<div class="warning"> ' . t('No category found!') . ' </div>';
				return '';
			} else {
				$output .= '<div class="search_result_categories">';
				$results = db_query($sql);
				while($result = db_fetch_object($results)) {
					$output .= theme_pwcs_search_category($result);
				}
				$output .= '<div style="clear:both;"></div>';
				$output .= '</div>';
				if(($limit != NULL) && ($countsql > $limit))
				$output .='<div class="search_results_full">'.l(t('See Full Search results for Category'), 'sitesearch/category/'.$keyword, array('attributes' => array('class'=>'moredetails'))).'</div>';
			}
			$output .= '</div>';
			return $output;
			//break;

		case 'manufacturer':
			// Manufacturer
			// {pwcs_manufacturer} >> mfg_name
			$sql  = "SELECT * FROM {pwcs_manufacturer}";
			$sql .= " WHERE 1";
			$q = array();
			foreach($keyword_array as $key=>$val){
				$q[] = "LOWER(mfg_name) like '%%$val%%'";
			}
			$sql .= " AND ( ".implode(' OR ', $q)." )";
			$sql .= " ORDER BY mfg_name";
			$sql = str_replace("  ", " ", $sql);		
			//print_r ($sql);

			$countsql = pwcs_result_count($sql);

			$output = '';
			$output .= '<div class="search_results_manufacturer_list" style="margin: 15px 0; ">';
			$output .= '<h3 style="border-bottom:1px solid #ddd; margin:2px 0;">'.t('@count Manufacturers Found', array('@count' => $countsql)).':'.'</h3>';
			if($countsql < 1) {
				//$output .= '<div class="warning"> ' . t('No manufacturer found!') . ' </div>';
				return '';
			} else {
				$output .= '<div class="search_result_manufacturer">';
				$results = db_query($sql);
				while($result = db_fetch_object($results)){
					$output .= theme_pwcs_search_manufacturer($result);
				}
				$output .= '<div style="clear:both;"></div>';
				$output .= '</div>';
				if(($limit != NULL) && ($countsql > $limit))
				$output .='<div class="search_results_full">'.l(t('See Full Search results for Manufacturers'), 'sitesearch/manufacturer/'.$keyword, array('attributes' => array('class'=>'moredetails'))).'</div>';
			}
			$output .= '</div>';
			return $output;
			//break;

		case 'sitepage':
			// Page
			// {node} >> type=sitepage, title
			// {node_revisions} >> body, teaser
			$sql  = "SELECT n.nid, n.title, nr.body, nr.teaser FROM {node} n";
			$sql .= " Inner Join {node_revisions} nr ON n.nid = nr.nid";
			$sql .= " WHERE n.type = 'sitepage' AND n.status = 1";
			$q = array();
			foreach($keyword_array as $key=>$val){
				$q[] = "LOWER(n.title) like '%%$val%%'";
				$q[] = "LOWER(nr.teaser) like '%%$val%%'";
				$q[] = "LOWER(nr.body) like '%%$val%%'";
			}
			$sql .= " AND ( ".implode(' OR ', $q)." )";
			$sql .= " ORDER BY n.changed desc";
			$sql = str_replace("  ", " ", $sql);		
			//print_r ($sql);
			
			$countsql = pwcs_result_count($sql);
			$output = '';
			$output .= '<div class="search_results_sitepage_list" style="margin: 15px 0; ">';
			$output .= '<h3 style="border-bottom:1px solid #ddd; margin:2px 0;">'.t('@count Pages Found', array('@count' => $countsql)).':'.'</h3>';
			if($countsql < 1) {
				$output .= '<div class="warning"> ' . t('No pages found!') . ' </div>';
				return '';
			} else {
				$output .= '<div class="search_result_sitepages">';
				$results = db_query($sql);
				while($result = db_fetch_object($results)) {
					$output .= theme_pwcs_search_sitepages($result->nid);
				}
				$output .= '<div style="clear:both;"></div>';
				$output .= '</div>';
				if(($limit != NULL) && ($countsql > $limit))
				$output .='<div class="search_results_full">'.l(t('See Full Search results for Site Pages'), 'sitesearch/sitepage/'.$keyword, array('attributes' => array('class'=>'moredetails'))).'</div>';
			}
			$output .= '</div>';
			return $output;
			//break;

		default:
			break;
	}
}

/**
 * Function to view search result of site pages
 * Added by Rasel Kabir
 */
function theme_pwcs_search_sitepages($sitepage_nid){

	$sitepage = node_load($sitepage_nid);

	$output ='';
	$output .= '<div class="product_search_box_class product_box_class">';
	$output .= '<div class="header">'.l(truncate_utf8($sitepage->title, 40, true, true),'node/'.$sitepage->nid).'</div>';
	$output .= '<div class="shortdetails">'.truncate_utf8(strip_tags($sitepage->teaser), 200, true, true).'</div>';
	$output .= '</div>';
	$output .= '<div style="clear: both;"></div>';

	return $output;
}

/**
 * Function to view search result of category
 * Added by Rasel Kabir
 */
function theme_pwcs_search_category($category){
	$output ='';
	$output .= '<div class="pwcs_search_result_singleitem" style="border: 1px solid #DDDDDD; float: left; font-size: 1.2em; line-height: 1.2em; margin: 5px; padding: 5px;">';
	$output .= l( $category->category_title, 'products/'.$category->pwc_category_id, array('html'=> false, 'attributes' => array('class'=>'pwcs-category-page-item',)));
	$output .= '</div>';
	return $output;
}


/**
 * Function to view search result of manufacturer
 * Added by Rasel Kabir
 */
function theme_pwcs_search_manufacturer($manufacturers){

	$output ='';
	$output .= '<div class="pwcs_search_result_singleitem" style="border: 1px solid #DDDDDD; float: left; font-size: 1.2em; line-height: 1.2em; margin: 5px; padding: 5px;">';
	$output .= l( $manufacturers->mfg_name, 'products/all/'.$manufacturers->pwc_mfg_id, array('html'=> false, 'attributes' => array('class'=>'pwcs-manufacture-page-item',)));
	$output .= '</div>';
	return $output;
}

function pwcs_topsearch_keywords_block (){

	$output = '';
	if(variable_get('pwcs_topsearch_keywords_themetype', 1) == 1 ) {
		$output .= pwcs_topitems_tagcloud('search');
	} else {
		$output .= pwcs_topitems('search');
	}
	return $output;
}

/**
 * Menu callback; generic function to display a page of the most frequent search keywords
 * Added by Rasel Kabir
 */
function pwcs_topitems($type = 'search') {

  $result = pager_query("SELECT COUNT(wid) AS count, message, variables FROM {watchdog} WHERE type = '%s' GROUP BY message, variables ORDER BY count DESC", 30, 0, "SELECT COUNT(DISTINCT(message)) FROM {watchdog} WHERE type = '%s'", $type);

  $output = '';
  $output .= '<ul>';
  while ($dblog = db_fetch_object($result)) {
	$dblog->message = str_replace(' (@type).','',$dblog->message);
	$message = t($dblog->message, unserialize($dblog->variables));
    $output .= '<li>' . $message . '('.$dblog->count.')' . '</li>';
  }
  $output .= '</ul>';

  return $output;
}

/**
 * Menu callback; generic function to display a page of the most frequent search keywords
 * Added by Rasel Kabir
 */
function pwcs_topitems_tagcloud($type = 'search') {

  $result = pager_query("SELECT COUNT(wid) AS count, message, variables FROM {watchdog} WHERE type = '%s' GROUP BY message, variables", 30, 0, "SELECT COUNT(DISTINCT(message)) FROM {watchdog} WHERE type = '%s'", $type);

  $output = '';
  $topsearchitem = array();
  while ($dblog = db_fetch_object($result)) {
	$dblog->message = str_replace(' (@type).','',$dblog->message);
	$message = t($dblog->message, unserialize($dblog->variables));
    //$tmp[$message] =  $dblog->count;
	$topsearchitem[] = array( 'name' =>  strip_tags($message) , 'amount' => $dblog->count , 'link' => 'sitesearch/all/'.strip_tags($message) );
  }
  
  return pwcs_print_tag_cloud($topsearchitem);
}

/**
 * Google Search
 * Added by Rasel Kabir
 */
//function pwcs_search_by_google($keyword = NULL){
//	//$search = 'google keyword search';
//	$results = json_decode( file_get_contents( 'http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=' . urlencode( $keyword ) ) );
//	return $results->responseData->cursor->estimatedResultCount;
//}



//			/**
//			 * Implementation of hook_search().
//			 */
//			function pwcs_search($op = 'search', $keys = NULL, $skip_access_check = FALSE) {
//			
//				switch ($op) {
//				    case 'name':
//				      return t('Search');
//				
//			//	    case 'reset':
//			//	      db_query("UPDATE {search_dataset} SET reindex = %d WHERE type = 'node'", time());
//			//	      return;
//				
//				    case 'status':
//				      $total = db_result(db_query('SELECT COUNT(*) FROM {node} WHERE status = 1'));
//				      $remaining = db_result(db_query("SELECT COUNT(*) FROM {node} n LEFT JOIN {search_dataset} d ON d.type = 'node' AND d.sid = n.nid WHERE n.status = 1 AND (d.sid IS NULL OR d.reindex <> 0)"));
//				      return array('remaining' => $remaining, 'total' => $total);
//				
//			//	    case 'admin':
//			//	      $form = array();
//			//	      // Output form for defining rank factor weights.
//			//	      $form['content_ranking'] = array(
//			//	        '#type' => 'fieldset', 
//			//	        '#title' => t('Content ranking'),
//			//	      );
//			//	      $form['content_ranking']['#theme'] = 'node_search_admin';
//			//	      $form['content_ranking']['info'] = array(
//			//	        '#value' => '<em>' . t('The following numbers control which properties the content search should favor when ordering the results. Higher numbers mean more influence, zero means the property is ignored. Changing these numbers does not require the search index to be rebuilt. Changes take effect immediately.') . '</em>',
//			//	      );
//			//	
//			//	      $ranking = array(
//			//	        'node_rank_relevance' => t('Keyword relevance'), 
//			//	        'node_rank_recent' => t('Recently posted'),
//			//	      );
//			//	      if (module_exists('comment')) {
//			//	        $ranking['node_rank_comments'] = t('Number of comments');
//			//	      }
//			//	      if (module_exists('statistics') && variable_get('statistics_count_content_views', 0)) {
//			//	        $ranking['node_rank_views'] = t('Number of views');
//			//	      }
//			//	
//			//	      // Note: reversed to reflect that higher number = higher ranking.
//			//	      $options = drupal_map_assoc(range(0, 10));
//			//	      foreach ($ranking as $var => $title) {
//			//	        $form['content_ranking']['factors'][$var] = array(
//			//	          '#title' => $title, 
//			//	          '#type' => 'select', 
//			//	          '#options' => $options, 
//			//	          '#default_value' => variable_get($var, 5),
//			//	        );
//			//	      }
//			//	      return $form;
//				
//				    case 'search':
//				      // Build matching conditions
//				      list($join1, $where1) = _db_rewrite_sql();
//				      $arguments1 = array();
//				      $conditions1 = 'n.status = 1';
//				
//			//	      if ($type = search_query_extract($keys, 'type')) {
//			//	        $types = array();
//			//	        foreach (explode(',', $type) as $t) {
//			//	          $types[] = "n.type = '%s'";
//			//	          $arguments1[] = $t;
//			//	        }
//			//	        $conditions1 .= ' AND (' . implode(' OR ', $types) . ')';
//			//	        $keys = search_query_insert($keys, 'type');
//			//	      }
//				
//			//	      if ($category = search_query_extract($keys, 'category')) {
//			//	        $categories = array();
//			//	        foreach (explode(',', $category) as $c) {
//			//	          $categories[] = "tn.tid = %d";
//			//	          $arguments1[] = $c;
//			//	        }
//			//	        $conditions1 .= ' AND (' . implode(' OR ', $categories) . ')';
//			//	        $join1 .= ' INNER JOIN {term_node} tn ON n.vid = tn.vid';
//			//	        $keys = search_query_insert($keys, 'category');
//			//	      }
//				
//				      // Build ranking expression (we try to map each parameter to a
//				      // uniform distribution in the range 0..1).
//				      $ranking = array();
//				      $arguments2 = array();
//				      $join2 = '';
//				      // Used to avoid joining on node_comment_statistics twice
//				      $stats_join = FALSE;
//				      $total = 0;
//				      if ($weight = (int) variable_get('node_rank_relevance', 5)) {
//				        // Average relevance values hover around 0.15
//				        $ranking[] = '%d * i.relevance';
//				        $arguments2[] = $weight;
//				        $total += $weight;
//				      }
//				      if ($weight = (int) variable_get('node_rank_recent', 5)) {
//				        // Exponential decay with half-life of 6 months, starting at last indexed node
//				        $ranking[] = '%d * POW(2, (GREATEST(MAX(n.created), MAX(n.changed), MAX(c.last_comment_timestamp)) - %d) * 6.43e-8)';
//				        $arguments2[] = $weight;
//				        $arguments2[] = (int) variable_get('node_cron_last', 0);
//				        $join2 .= ' LEFT JOIN {node_comment_statistics} c ON c.nid = i.sid';
//				        $stats_join = TRUE;
//				        $total += $weight;
//				      }
//			//	      if (module_exists('comment') && $weight = (int) variable_get('node_rank_comments', 5)) {
//			//	        // Inverse law that maps the highest reply count on the site to 1 and 0 to 0.
//			//	        $scale = variable_get('node_cron_comments_scale', 0.0);
//			//	        $ranking[] = '%d * (2.0 - 2.0 / (1.0 + MAX(c.comment_count) * %f))';
//			//	        $arguments2[] = $weight;
//			//	        $arguments2[] = $scale;
//			//	        if (!$stats_join) {
//			//	          $join2 .= ' LEFT JOIN {node_comment_statistics} c ON c.nid = i.sid';
//			//	        }
//			//	        $total += $weight;
//			//	      }
//				      if (module_exists('statistics') && variable_get('statistics_count_content_views', 0) && 
//				          $weight = (int) variable_get('node_rank_views', 5)) {
//				        // Inverse law that maps the highest view count on the site to 1 and 0 to 0.
//				        $scale = variable_get('node_cron_views_scale', 0.0);
//				        $ranking[] = '%d * (2.0 - 2.0 / (1.0 + MAX(nc.totalcount) * %f))';
//				        $arguments2[] = $weight;
//				        $arguments2[] = $scale;
//				        $join2 .= ' LEFT JOIN {node_counter} nc ON nc.nid = i.sid';
//				        $total += $weight;
//				      }
//				
//				      // When all search factors are disabled (ie they have a weight of zero),
//				      // the default score is based only on keyword relevance and there is no need to
//				      // adjust the score of each item.
//				      if ($total == 0) {
//				        $select2 = 'i.relevance AS score';
//				        $total = 1;
//				      }
//				      else {
//				        $select2 = implode(' + ', $ranking) . ' AS score';
//				      }
//				
//				      // Do search.
//				      $find = do_search($keys, 'node', 'INNER JOIN {node} n ON n.nid = i.sid ' . $join1, $conditions1 . (empty($where1) ? '' : ' AND ' . $where1), $arguments1, $select2, $join2, $arguments2);
//				
//				      // Load results.
//				      $results = array();
//				      foreach ($find as $item) {
//				        // Build the node body.
//				        $node = node_load($item->sid);
//				        $node->build_mode = NODE_BUILD_SEARCH_RESULT;
//				        $node = node_build_content($node, FALSE, FALSE);
//				        $node->body = drupal_render($node->content);
//				
//				        // Fetch comments for snippet.
//				        $node->body .= module_invoke('comment', 'nodeapi', $node, 'update index');
//				        // Fetch terms for snippet.
//				        $node->body .= module_invoke('taxonomy', 'nodeapi', $node, 'update index');
//				
//				        $extra = node_invoke_nodeapi($node, 'search result');
//				        $results[] = array(
//				          'link' => url('node/' . $item->sid, array('absolute' => TRUE)), 
//				          'type' => check_plain(node_get_types('name', $node)), 
//				          'title' => $node->title, 
//				          'user' => theme('username', $node), 
//				          'date' => $node->changed, 
//				          'node' => $node, 
//				          'extra' => $extra, 
//				          'score' => $item->score / $total, 
//				          'snippet' => search_excerpt($keys, $node->body),
//				        );
//				      }
//				      return $results;
//				}
//			}
