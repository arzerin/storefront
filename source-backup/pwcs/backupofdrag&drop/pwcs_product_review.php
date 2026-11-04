<?php
/* $Id: pwcs_product_review.php,v 1.0 2011/04/20 Rasel Kabir Shuvro Exp $ */

/**
 * Fucntion for PRODUCT REVIEW OF A PRODUCT
 * Added By Rasel Kabir
 */
function pwcs_product_comments_summary($nid = 'all') {

	$output = '';
	$output .= '<div class="customer_review_summary" id="customer_review_summary_'.$nid.'">';

	//Total Count
	$review_count = pwcs_product_review_count($nid);
	if(!empty($review_count)) {
		$review_total_text = '<div>'.t('Total !total_published Reviews Published', array( '!total_published' => '<span class="total">'.($review_count[COMMENT_PUBLISHED]?$review_count[COMMENT_PUBLISHED]:0).'</span>')).'</div>';
		if(!empty($review_count[COMMENT_NOT_PUBLISHED]))
			$review_total_text .= '<div>'.t('!total_not_published more waiting for approval', array( '!total_not_published' => '<span class="totalwaiting">'.$review_count[COMMENT_NOT_PUBLISHED].'</span>')).'</div>';
	} else {
		$review_total_text = '<em>'.t('No Review').'</em>';
	}
	$output .= '<div class="total_text" style="">'.$review_total_text.'</div>';

	if($nid != 'all') {
		//Average Rating
		if (module_exists('fivestar_comment') && (variable_get('fivestar_product', 1) == 1)) {
			$rating = db_result(db_query('SELECT Avg(fc.value) average FROM {fivestar_comment} fc Inner Join {comments} c ON c.cid = fc.cid WHERE c.status = %d AND c.nid = %d', COMMENT_PUBLISHED, $nid));
			if($rating){
				$output .= '<div class="average_rating" style="">'.t('Average Rating').': '.theme('fivestar_static', $rating).'</div>';
			} else {
				$output .= '<div class="average_rating" style=""><em>'.t('No Rating').'</em></div>';
			}
		}
	}

	//Add New Review Link
	$new_review_link = l(t('Post Your Review'),'#customer_reviews_form_'.$nid, 
							array('attributes' => 
								array(	'class' => 'pwcs-default-link-button productreview_addnew', 
										'product_nid'=>$nid, 
										'name' => 'productreview_addnew', 
										'id' => 'productreview_addnew_'.$nid, 
										'title' => 'Click Here To Post Your Review'
						)));
	$output .= '<div class="productreview_addnew_span" style="">'.$new_review_link.'</div>';

	$output .= '<div style="clear:both;"></div>';
	$output .= '</div>';
	return $output;
}

/**
 * Fucntion for ALL REVIEWS OF A PRODUCT
 * Added By Rasel Kabir
 */
function pwcs_product_comments($nid = 'all') {

	$output = '';

	$sql = "SELECT * FROM {comments}";
	$sql .= " WHERE status = ".COMMENT_PUBLISHED;
	if(is_numeric($nid) && ($nid != 'all'))
		$sql .= " AND  nid = ".$nid;
	$sql .= " ORDER BY timestamp DESC";

	$results = db_query($sql);

	$output .= '<div class="customer_reviews" id="customer_reviews_'.$nid.'">';
	while($result = db_fetch_object($results)) {
		if (module_exists('fivestar_comment') && (variable_get('fivestar_product', 1) == 1)) {
			$result->rating = db_result(db_query('SELECT value FROM {fivestar_comment} WHERE cid = %d', $result->cid));
		}
		$output .= theme_pwcs_productreview_publicview($result);
	}
	$output .= '</div>';

	return $output;
}

function theme_pwcs_productreview_publicview($review = NULL){
	
	if(empty($review)) {
		return '';
	}
	
	$output = '';
	$output .= '<div class="review_box" id="review_box_'.$review->cid.'">';
	$output .= '<h2 class="review_subject">'.$review->subject.'</h2>';
	if (module_exists('fivestar_comment') && (variable_get('fivestar_product', 1) == 1)) {
		if($review->rating)
			$output .= theme('fivestar_static', $review->rating);
		//else
		//	$output .= '<em>'.t('No Rating').'</em>';
	}
	$output .= '<p class="review_submittedby">'
				.t('Submitted by @user_email, @time ago', 
					array(
						'@user_email' => ($review->name ? $review->name : variable_get('anonymous', t('Anonymous'))), 
						'@time' => format_interval(time() - $review->timestamp) 
						)
					)
				.'</p>';
	$output .= '<p class="review_comment">'.$review->comment.'</p>';
	$output .= '</div>';

	return $output;
}
function theme_pwcs_productreview_publicview2($review = NULL){
	
	if(empty($review)) {
		return '';
	}
	
	$output = '';
	$output .= '<div class="review_box" id="review_box_'.$review->cid.'">';
	$output .= '<h2 class="review_subject">Sub:'.$review->subject.'</h2>';
	if (module_exists('fivestar_comment') && (variable_get('fivestar_product', 1) == 1)) {
		if($review->rating)
			$output .= theme('fivestar_static', $review->rating);
	}

	$output .= '<p class="review_comment">'.$review->comment.'</p>';
	$output .= '</div>';

	return $output;
}
function theme_pwcs_productreview_details($review = NULL){
	
	if(empty($review)) {
		return '';
	}
	
	$output = '';
	$output .= '<div class="review_box" id="review_box_'.$review->cid.'">';
	$output .= '<h2 class="review_subject">Title: '.$review->subject.'</h2>';
	$output .= '<p class="review_comment"><b>Review:</b> '.$review->comment.'</p>';
	$output .= '<p class="review_comment"><b>Reviewer:</b> '.$review->name.'</p>';
	$output .= '</div>';

	return $output;
}
/**
 * Fucntion for PRODUCT REVIEW FORM
 * Added By Rasel Kabir
 */
function pwcs_comment_form($nid, $pid = NULL) {

  $output = '';
	$output .= '<div class="customer_reviews_form" id="customer_reviews_form_'.$nid.'">';
  if (user_access('access comments')) {
      if (node_comment_mode($nid) != COMMENT_NODE_READ_WRITE) {
        $output .= t('This discussion is closed: you can\'t post new comments.');
      }
      else if (user_access('post comments')) {
				$output .= drupal_get_form('comment_form', array('pid' => $pid, 'nid' => $nid), t('Post Your Review'));
      }
      else {
        $output .= t('You are not authorized to post comments.');
      }
  }
  else {
    $output .= t('You are not authorized to view comments.');
  }
	$output .= '</div>';
  return $output;
}


function pwcs_siteadmin_productreview_list($type=NULL, $filter = NULL, $nid='', $status=NULL)
{
	$breadcrumb = array();
	$breadcrumb[] = l(t('Home'), '<front>');
	$breadcrumb[] = l(t("Product Review"), 'siteadmin/productreview/');
	drupal_set_breadcrumb($breadcrumb);
		
	drupal_set_title(t("Product Reviews"));
	
	if (!empty($nid) && ($nid != 'all')) {
		$title = check_plain(db_result(db_query('SELECT title FROM {node} WHERE nid = %d',$nid)));
		$selnode = node_load($nid);
		$image_path = pwcs_get_product_primary_image_path($selnode);
		$image_output = theme('image', $image_path, '', NULL, array('width'=>'60px'), FALSE);		
		drupal_set_title(t($image_output." Reviews of !title", array('!title' => '<em>"'.$title.'"</em>')));
	}
	
	$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
	$pagelimit = isset($filter['display_per_page'])? $filter['display_per_page'] : 15;
	$pageelement = 0;
	$pageparameters = array();
	$pagequantity = 5;

	$output	= '';
	$rows = array();
	$categories = pwcs_cat_array();

	$header = array(
			'<input type="checkbox" id="products-check-all" class="form-checkbox checkbox_checkall" title="Select all rows in this table">',
			//array('data' => '', 'width'=> '10'),
			//array('data' => t('Product Name'), 'width'=> '150'),
			array('data' => t('Review'), 'width'=> '350'),
			array('data' => t('Email'),  'width'=> '150'),
			array('data' => t('Date'),  'width'=> '150'),
			array('data' => t('Rating'),'width'=> '150'),
			array('data' => t('Action'), 'width'=> '150'),
		);
	

	$sql  = "SELECT n.*, pp.*, pps.product_sale_price, pps.enable_sales_price, pps.sale_tag_icon, nc.totalcount, c.* FROM {node} n";
	$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
	$sql .= " LEFT JOIN {comments} c ON n.nid = c.nid";
	$sql .= " LEFT JOIN {node_counter} nc ON n.nid = nc.nid";
	$sql .= " LEFT JOIN {pwcs_product_sales} pps ON pps.pwc_product_row_id = pp.pwc_product_row_id";
	$sql .= " WHERE n.type = 'product'";
	$sql .= " AND n.nid = ".$nid;		
	if($status!=NULL)
	$sql .= " AND c.status = ".$status;



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


	$tablesort = tablesort_sql($header);
	$results = pager_query($sql . $tablesort, $pagelimit);
	

	$product_amount = pwcs_result_count($sql);
	$review_count = pwcs_product_review_count('all');
		
	if($type == 'featured')
		$output .= '<h3>'.t('List of all featured products').'</h3>';
	else{
		if (($nid != 'all')||!empty($nid))
		{
			if($status==1)
			$output .= '<h3>'.t("Review(s) - Wating for Approval List").'</h3>';
			else if($status==0)
			$output .= '<h3>'.t("Approved Review(s) List").'</h3>';			
			else
			$output .= '<h3>'.t("Products & Their Review(s) Information").'</h3>';
		}
		else
		$output .= '<h3>'.t('Products ('.$product_amount.') & their Reviews').'</h3>';
	}

	
	$nidd = 'all';
	$review_text = '';
	$review_text .= '<span class="status">';
	if($review_count[COMMENT_PUBLISHED] || $review_count[COMMENT_NOT_PUBLISHED])
		$review_text .= t('Total Reviews').' '.l($review_count[COMMENT_PUBLISHED]+$review_count[COMMENT_NOT_PUBLISHED], 'siteadmin/productreview/'.$nidd, array ( 'attributes' => array('class' => 'button'))).' for All Your Product(s)<br/>';
	else
		$review_text .= t('No Reviews Published');
	$review_text .= '</span>';
	//$output .= $review_text ;
	
	$review_text2 .= ' | ';
	$review_text2 .= '<span class="warning">';
	if($review_count[COMMENT_NOT_PUBLISHED])
		$review_text2 .= t('Wating for Approval').' '.l($review_count[COMMENT_NOT_PUBLISHED], 'siteadmin/productreview/'.$nidd.'/'.COMMENT_NOT_PUBLISHED, array ( 'attributes' => array('class' => 'button')));
	else
		$review_text2 .= t('No Wating for Approval');
	$review_text2 .= '</span>';
	
	if (($nid != 'all')||!empty($nid))		
	$output .= '';
	else
	$output .= '<div class="pwcs_prodcut_list_count" style="margin:5px 0; font-style: italic;">'.t('Total').' '.$product_amount.' '.t('product(s) found. ').$review_text.$review_text2.'</div>';

	$output .= '<div class="pwcs_table">';
	while($result = db_fetch_object($results))
	{
		$row = array();
		$node = node_load($result->nid);

		// Delete check box
		$row[] = array('data' => '<input type="checkbox" id="product-'.$result->nid.'" class="form-checkbox" name="productcid[]" value="'.$result->cid.'">', 'width' => '10');



		//Delete the below line when you will use the Reconditioned Product
		$recon = '';
	
		$row[] = theme_pwcs_productreview_details($result);
		$row[] = array('data' => $result->mail);
		$row[] = date(ADMIN_TIME_FORMAT, $result->timestamp);//format_interval(time() - $result->timestamp)
		$row[] = array('data' => $result->rating);
		if($status==1)
		$row[] = l(t('<img src="'.base_path().path_to_theme().'/images/icon_edit.png" />'),'siteadmin/productreviewaction/edit/'.$nid.'/'.$result->cid.'/1',array ( 'html'=>true, 'attributes' => array ('title'=>'Edit'),)).'&nbsp;&nbsp;'.l(t('<img src="'.base_path().path_to_theme().'/images/icon_delete.png" />'),'siteadmin/productsaction/delete-unapproved-review/'.$nid.'/'.$result->cid,array ( 'html'=>true, 'attributes' => array ('title'=>'Delete'),)).t('&nbsp;&nbsp;').l(t('<img src="'.base_path().path_to_theme().'/images/approve.png" />'),'siteadmin/productsaction/approve-review/'.$nid.'/'.$result->cid,array ( 'html'=>true, 'attributes' => array ('title'=>'Approve'),));
		else
		$row[] = l(t('<img src="'.base_path().path_to_theme().'/images/icon_edit.png" />'),'siteadmin/productreviewaction/edit/'.$nid.'/'.$result->cid.'/0',array ( 'html'=>true, 'attributes' => array ('title'=>'Edit'),)).'&nbsp;&nbsp;'.l(t('<img src="'.base_path().path_to_theme().'/images/icon_delete.png" />'),'siteadmin/productsaction/delete-approved-review/'.$nid.'/'.$result->cid,array ( 'html'=>true, 'attributes' => array ('title'=>'Delete'),)).t('&nbsp;&nbsp;').l(t('<img src="'.base_path().path_to_theme().'/images/unapprove.gif" />'),'siteadmin/productsaction/unapprove-review/'.$nid.'/'.$result->cid,array ( 'html'=>true, 'attributes' => array ('title'=>'Unapprove'),));
				

		$rows[] = $row;
	}
	$row = array();
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	
	//$row[] = '';

	$rows[] = $row;
	
	$row = array();
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';


	$rows[] = $row;

	$output .= '<form id="siteadmin-productlist-form" method="post" accept-charset="UTF-8" action="'.base_path().'siteadmin/productsaction/reviews">';

	$output .= theme('table', $header, $rows, array('width'=>'100%'));
	$output .= '<input type="submit" class="form-submit form-save-submit" value="'.t('Delete').'" id="delete-all" name="saveproduct">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	if($status==1)
	$output .= '<input type="submit" class="form-submit" value="'.t('Approve Selected').'" id="approve-all" name="saveproduct">';
	else if($status==0)
	$output .= '<input type="submit" class="form-submit" value="'.t('Unapprove Selected').'" id="unapprove-all" name="saveproduct">';
	else
	$output .= '<input type="submit" class="form-submit" value="'.t('Remove Approval').'" id="approve-all" name="saveproduct">';
	
	$output .= '</form>';

	$output .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);
	$output .= '</div>';

	return $output;
}
function pwcs_siteadmin_productreview_list_products($type=NULL, $filter = NULL, $nid='', $status=NULL)
{
	$breadcrumb = array();
		$breadcrumb[] = l(t('Home'), '<front>');
		$breadcrumb[] = l(t("Product Review"), 'siteadmin/productreview/');
		drupal_set_breadcrumb($breadcrumb);
		
	drupal_set_title(t("Product Reviews"));

	
	$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
	$pagelimit = isset($filter['display_per_page'])? $filter['display_per_page'] : 15;
	$pageelement = 0;
	$pageparameters = array();
	$pagequantity = 5;

	$output	= '';
	$rows = array();
	$categories = pwcs_cat_array();

	$header = array(
			array('data' => '', 'width'=> '10'),
			array('data' => t('Product Image'), 'width'=> '110'),
			array('data' => t('Product Name'), 'field' => 'n.title', 'width'=> '300'),
			array('data' => t('Total Reviews'),  'width'=> '150'),
			array('data' => t('Reviews Approved'),'width'=> '150'),
			array('data' => t('Wating for Approval'), 'width'=> '150'),
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


	$tablesort = tablesort_sql($header);
	$results = pager_query($sql . $tablesort, $pagelimit);
	

	$product_amount = pwcs_result_count($sql);
	$review_count = pwcs_product_review_count('all');
		
	if($type == 'featured')
		$output .= '<h3>'.t('List of all featured products').'</h3>';
	else{
		if (($nid != 'all')||!empty($nid))
		{
			if($status==1)
			$output .= '<h3>'.t("Wating for Approval Review(s) List").'</h3>';
			else
			$output .= '<h3>'.t("Approved Review(s) List").'</h3>';
		}
		else
		$output .= '<h3>'.t('Products ('.$product_amount.') & their Reviews').'</h3>';
	}

	
	$nidd = 'all';
	$review_text = '';
	$review_text .= '<span class="status">';
	if($review_count[COMMENT_PUBLISHED] || $review_count[COMMENT_NOT_PUBLISHED])
		$review_text .= t('Total Reviews').' '.l($review_count[COMMENT_PUBLISHED]+$review_count[COMMENT_NOT_PUBLISHED], 'siteadmin/productreview/'.$nidd, array ( 'attributes' => array('class' => 'button'))).' for All Your Product(s)';
	else
		$review_text .= t('No Reviews Published');
	$review_text .= '</span>';
	//$output .= $review_text ;
	
	$review_text2 .= ' | ';
	$review_text2 .= '<span class="warning">';
	if($review_count[COMMENT_NOT_PUBLISHED])
		$review_text2 .= t('Wating for Approval').' '.l($review_count[COMMENT_NOT_PUBLISHED], 'siteadmin/productreview/'.$nidd.'/'.COMMENT_NOT_PUBLISHED, array ( 'attributes' => array('class' => 'button')));
	else
		$review_text2 .= t('No Wating for Approval');
	$review_text2 .= '</span>';
	
	$output .= '<div class="pwcs_prodcut_list_count" style="margin:5px 0; font-style: italic;">'.t('Total').' '.$product_amount.' '.t('product(s) found. ').$review_text.$review_text2.'</div>';

	$output .= '<div class="pwcs_table">';
	while($result = db_fetch_object($results))
	{
		$row = array();
		$node = node_load($result->nid);

		// Delete check box
		$row[] = array('data' => '<input type="checkbox" id="product-'.$result->nid.'" class="form-checkbox" name="productnid[]" value="'.$result->cid.'">', 'width' => '10');//'<input type="checkbox" id="product-'.$result->nid.'" class="form-checkbox" name="productnid[]" value="'.$result->nid.'">';

		if (empty($nid)){
			// Product Primary Image
			$image_path = pwcs_get_product_primary_image_path($node);
			$image_output = theme('image', $image_path, '', NULL, array('width'=>'60px'), FALSE);
			$row[] = $image_output;
		}

		//Delete the below line when you will use the Reconditioned Product
		$recon = '';
		

		if ($node->status==1)
			$row[] = array('data' => trim($result->title).'<br/>['.$node->pwc_product_id.']<br/>'.$recon.'', 'width' => '100');
		else
			$row[] = array('data' => '<i>'.trim($result->title).'</i>'.'<br/>['.$node->pwc_product_id.']<br/><div class="product_hidden">Not Visible On Site</div><br/>'.$recon.'', 'width' => '100');


		$review_count = pwcs_product_review_count($result->nid);
		$review_text = '';
		$total_review_text ='';
		$total_review_approved_text ='0';
		$total_review_waiting_text ='0';
		if(!empty($review_count)) {
			$total_review_text ='';
			$total = $review_count[COMMENT_PUBLISHED] + $review_count[COMMENT_NOT_PUBLISHED];
			$total_review_text = l(t($total), 'siteadmin/productreview/'.$result->nid, array ( 'attributes' => array (), 'html' => true,) );
			
			if($review_count[COMMENT_PUBLISHED])
			$total_review_approved_text = l(t($review_count[COMMENT_PUBLISHED]), 'siteadmin/productreview/0/'.$result->nid.'/'.COMMENT_PUBLISHED, array ( 'attributes' => array (), 'html' => true,) );

			if($review_count[COMMENT_NOT_PUBLISHED])
			$total_review_waiting_text = l(t($review_count[COMMENT_NOT_PUBLISHED]), 'siteadmin/productreview/0/'.$result->nid.'/'.COMMENT_NOT_PUBLISHED, array ( 'attributes' => array (), 'html' => true,) );
		} else {
			$total_review_text ='<em>No Review</em>';
			$total_review_approved_text ='0';
			$total_review_waiting_text ='0';
		}
				
		$row[] = array('data' => $total_review_text);
		$row[] = array('data' => $total_review_approved_text);
		$row[] = array('data' => $total_review_waiting_text);

		$rows[] = $row;
	}
	$row = array();
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
	$rows[] = $row;

	$output .= '<form id="siteadmin-productlist-form" method="post" accept-charset="UTF-8" action="'.base_path().'siteadmin/productsaction/reviews">';

	$output .= theme('table', $header, $rows, array('width'=>'100%'));
	$output .= '</form>';

	$output .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);
	$output .= '</div>';

	return $output;
}
function pwcs_siteadmin_productreview_list_backup($nid='all',$type=NULL, $filter = NULL)
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
		array('data' => '', 'width'=> '10'),
		array('data' => t('Product Name'), 'field' => 'n.title', 'width'=> '100'),
			/*array('data' => t('Product ID'), 'field' => 'pp.pwc_product_row_id'),*/
		array('data' => t('Review Details'),  'width'=> '300'),
		array('data' => t('Email'),'width'=> '150'),
		array('data' => t('Time'), 'width'=> '120'),
		//array('data' => t('Status'), 'width'=> '120'),
	);

	$sql  = "SELECT n.*, pp.*, pps.product_sale_price, pps.enable_sales_price, pps.sale_tag_icon, nc.totalcount,c.* FROM {node} n";
	$sql .= " INNER JOIN {pwcs_product} pp ON n.nid = pp.nid";
	$sql .= " LEFT JOIN {comments} c ON n.nid = c.nid";
	$sql .= " LEFT JOIN {node_counter} nc ON n.nid = nc.nid";
	$sql .= " LEFT JOIN {pwcs_product_sales} pps ON pps.pwc_product_row_id = pp.pwc_product_row_id";
	$sql .= " WHERE n.type = 'product' AND c.comment != ''";


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


	$tablesort = tablesort_sql($header);
	$results = pager_query($sql . $tablesort, $pagelimit);
	
	if($type == 'featured')
	$output .= '<h3>'.t('List of all featured products').'</h3>';
	else
	$output .= '<h3>'.t('Products List & their Reviews').'</h3>';

	$product_amount = pwcs_result_count($sql);
	$review_count = pwcs_product_review_count($nid);
	

	$review_text = '';
	$review_text .= '<span class="status">';
	if($review_count[COMMENT_PUBLISHED] || $review_count[COMMENT_NOT_PUBLISHED])
		$review_text .= t('Total Reviews').' '.l($review_count[COMMENT_PUBLISHED]+$review_count[COMMENT_NOT_PUBLISHED], 'siteadmin/productreview/'.$nid, array ( 'attributes' => array('class' => 'button')));
	else
		$review_text .= t('No Reviews Published');
	$review_text .= '</span>';
	//$output .= $review_text ;
	
	$review_text2 .= ' | ';
	$review_text2 .= '<span class="warning">';
	if($review_count[COMMENT_NOT_PUBLISHED])
		$review_text2 .= t('Wating for Approval').' '.l($review_count[COMMENT_NOT_PUBLISHED], 'siteadmin/productreview/'.$nid.'/'.COMMENT_NOT_PUBLISHED, array ( 'attributes' => array('class' => 'button')));
	else
		$review_text2 .= t('No Wating for Approval');
	$review_text2 .= '</span>';
			
	$output .= '<div class="pwcs_prodcut_list_count" style="margin:5px 0; font-style: italic;">'.t('Total').' '.$product_amount.' '.t('product(s) found. ').$review_text.$review_text2.'</div>';

	$output .= '<div class="pwcs_table">';
	while($result = db_fetch_object($results))
	{
		$row = array();
		$node = node_load($result->nid);

		// Delete check box
		$row[] = array('data' => '<input type="checkbox" id="product-'.$result->nid.'" class="form-checkbox" name="productnid[]" value="'.$result->cid.'">', 'width' => '10');//'<input type="checkbox" id="product-'.$result->nid.'" class="form-checkbox" name="productnid[]" value="'.$result->nid.'">';

		// Product Primary Image
		$image_path = pwcs_get_product_primary_image_path($node);
		$image_output = theme('image', $image_path, '', NULL, array('width'=>'60px'), FALSE);
		$row[] = $image_output;

		//Delete the below line when you will use the Reconditioned Product
		$recon = '';
		
		// Product Title
		if ($node->status==1)
		$row[] = array('data' => trim($result->title).'<br/>['.$node->pwc_product_id.']<br/>'.$recon.'', 'width' => '100');
		else
		$row[] = array('data' => '<i>'.trim($result->title).'</i>'.'<br/>['.$node->pwc_product_id.']<br/><div class="product_hidden">Not Visible On Site</div><br/>'.$recon.'', 'width' => '100');

		$row[] = theme_pwcs_productreview_publicview2($result);
		$row[] = array('data' => $result->mail, 'width' => '100');
		$row[] = array('data' => date(ADMIN_TIME_FORMAT, $result->timestamp), 'width' => '80');
		//$row[] = array('data' => trim('Not Approve'), 'width' => '120');

	

		$rows[] = $row;
	}
	$row = array();
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
	
	//$row[] = '';

	$rows[] = $row;
	
	$row = array();
	$row[] = '';
	$row[] = '';
	$row[] = '';
	$row[] = '';
		$row[] = '';
	$row[] = '';

	//$row[] = '';


	$rows[] = $row;

	$output .= '<form id="siteadmin-productlist-form" method="post" accept-charset="UTF-8" action="'.base_path().'siteadmin/productsaction/reviews">';

	$output .= theme('table', $header, $rows, array('width'=>'100%'));
	$output .= '<input type="submit" class="form-submit form-save-submit" value="'.t('Delete').'" id="delete-all" name="saveproduct">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="form-submit" value="'.t('Approve').'" id="approve-all" name="saveproduct">';
	$output .= '</form>';

	$output .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);
	$output .= '</div>';

	return $output;
}
/**
 * Fucntion for PRODUCT REVIEW - Siteadmin
 * Added By Rasel Kabir
 */
function pwcs_siteadmin_productreview2($nid='all', $status=NULL) {

	drupal_set_title(t("Product Reviews"));
	if($nid != 'all') {
		$title = check_plain(db_result(db_query('SELECT title FROM {node} WHERE nid = %d',$nid)));
		drupal_set_title($title = t("Reviews of !title", array('!title' => '<em>"'.$title.'"</em>')));
	}

	$output = '';
	$review_count = pwcs_product_review_count($nid);
	if(empty($review_count)) {
		return '<div class="messages warning">No Review For This Product</div>';
	} else {
		$review_text = '';
		$review_text .= '<span class="status">';
		if($review_count[COMMENT_PUBLISHED] || $review_count[COMMENT_NOT_PUBLISHED])
			$review_text .= t('Total Reviews').' '.l($review_count[COMMENT_PUBLISHED]+$review_count[COMMENT_NOT_PUBLISHED], 'siteadmin/productreview/'.$nid, array ( 'attributes' => array('class' => 'button')));
		else
			$review_text .= t('No Reviews Published');
		$review_text .= '</span>';
		
		$review_text .= ' | ';
		$review_text .= '<span class="warning">';
		if($review_count[COMMENT_NOT_PUBLISHED])
			$review_text .= t('Wating for Approval').' '.l($review_count[COMMENT_NOT_PUBLISHED], 'siteadmin/productreview/'.$nid.'/'.COMMENT_NOT_PUBLISHED, array ( 'attributes' => array('class' => 'button')));
		else
			$review_text .= t('No Wating for Approval');
		$review_text .= '</span>';

		$output .= '<div style="margin: 10px 0; padding: 5px; border-bottom: 1px solid #ddd;">'.$review_text.'</div>';
	}

	$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
	$pagelimit = isset($filter['display_per_page'])? $filter['display_per_page'] : 15;
	$pageelement = 0;
	$pageparameters = array();
	$pagequantity = 5;

	$sql  = "SELECT * FROM {comments}";
	$sql .= " WHERE comment != ''";
	if($status!=NULL)
	$sql .= " AND status = ".$status;
	if($nid != 'all')
	$sql .= " AND nid = ".$nid;
	//$sql .= " ORDER BY timestamp DESC";

	$header = array(
		//array('data' => t('Name/Email'),	'field' => 'name', 			'sort' => 'ASC'),
		array('data' => t('Product Name')),
		array('data' => t('Review Details')),
		array('data' => t('Email'), 'field' => 'mail', 'sort' => 'ASC', 'width'=> '150'),
		array('data' => t('Time'), 'field' => 'timestamp', 'sort' => 'DESC', 'width'=> '120'),
		array('data' => t('Status'), 'field' => 'status', 'sort' => 'ACS', 'width'=> '130'),
	);

	$tablesort_sql = tablesort_sql($header); 
	if($tablesort_sql == '') $tablesort_sql = " ORDER BY timestamp DESC";
	$results = pager_query($sql . $tablesort_sql, $pagelimit);

	$rows = array();
	$output .= '<div class="siteadmin_product_comments">';
	while ($result = db_fetch_object($results)) {
		$row = array();
		$row[] = 'Test Product 1';
		$row[] = theme_pwcs_productreview_publicview($result);
		$row[] = $result->mail;
		$row[] = date(ADMIN_TIME_FORMAT, $result->timestamp);
		if($result->status == COMMENT_NOT_PUBLISHED) {
			$row[] = '<div class="messages warning" style="text-align:center;">'.t('Not Approved').'</div>'
				.l(t('Approve') ,'siteadmin/productreviewaction/approve/'.$result->cid, array( 'attributes' => array('class' => 'button',)) )
				.l(t('Delete') ,'siteadmin/productreviewaction/delete/'.$result->cid, array( 'attributes' => array('class' => 'button', 'onclick'=>'return confirm(\'Are you sure?\');')) );
		} else {
			$row[] = '<div class="messages status" style="text-align:center;">'.t('Approved').'</div>'
				.l(t('Unpublish') ,'siteadmin/productreviewaction/removeapprove/'.$result->cid, array( 'attributes' => array('class' => 'button',)) )
				.l(t('Delete') ,'siteadmin/productreviewaction/delete/'.$result->cid, array( 'attributes' => array('class' => 'button', 'onclick'=>'return confirm(\'Are you sure?\');')) );
		}
		$rows[] = $row;
	}
	
	$output .= theme('table', $header, $rows);
	$output .= theme('pager', NULL, 30, 0);

	$output .= '</div>';
	return $output;
}
function pwcs_siteadmin_productreview($action=NULL, $nid='', $status=NULL) {

	$output = '';
	$output .= '<div class="pwcs-page-content-inner">';
	if($action == 'featured') {
		unset($_SESSION['pwcs_products_filter']);
		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
		$output .= pwcs_siteadmin_productreview_list('featured', array('featured'=>'1'));
		$output .= '</div>';
	}
	else if ($action == 'filterd'){
		$filter = $_SESSION['pwcs_products_filter']? $_SESSION['pwcs_products_filter'] : NULL;
		$output .= '<div id="pwcs_product_list_filter_box" style="margin-bottom:15px;">';
		$output .= '<h3 class="pwcs_product_list_filter_title expanded">'.t('Search Products').'</h3>';
		$output .= drupal_get_form('pwcs_siteadmin_productsreview_filter_form', $filter);
		$output .= '</div>';
		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
		$output .= pwcs_siteadmin_productreview_list($action, $filter, $nid, $status);
		$output .= '</div>';
	}
	else {
		unset($_SESSION['pwcs_products_filter']);
		$output .= '<div id="pwcs_product_list_filter_box" class="collapsiblebox" style="margin-bottom:15px;">';
		$output .= '<h3 class="pwcs_product_list_filter_title collapsiblebox-button">'.t('Search Products').'</h3>';
		$output .= '<div class="collapsiblebox-item">'.drupal_get_form('pwcs_siteadmin_productsreview_filter_form').'</div>';
		$output .= '</div>';
		$output .= '<div id="pwcs_product_list" class="ws-content-list">';
		if (is_numeric($nid) && ($nid>0))
		$output .= pwcs_siteadmin_productreview_list($action, NULL, $nid, $status);
		else
		$output .= pwcs_siteadmin_productreview_list_products($action, NULL, $nid, $status);
		//$output .= pwcs_siteadmin_productreview_list($action, NULL, $nid, $status);
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}
/**
 * Siteadmin product actions
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_productreviewaction($action = NULL, $nid = NULL, $cid = NULL, $status = NULL){
	
	global $user;
	if ($action=='edit'){
		if($cid!=NULL){
			$breadcrumb = array();
			$breadcrumb[] = l(t('Home'), '<front>');
			$breadcrumb[] = l(t("Product Review"), 'siteadmin/productreview/0/'.$nid.'/'.$status);
			drupal_set_breadcrumb($breadcrumb);
			drupal_set_title(t("Reviews Update Form"));
			return drupal_get_form('pwcs_review_edit_form', $nid, $cid, $status);
		}
	}    
    die();
	if($action == 'approve') {
		if(is_numeric($cid)) {
			db_query("UPDATE {comments} SET status = %d WHERE cid = %d", COMMENT_PUBLISHED, $cid);
			drupal_set_message(t('Review Approved'));
		}
	}else if ($action == 'removeapprove'){
		if(is_numeric($cid)) {
			db_query("UPDATE {comments} SET status = %d WHERE cid = %d", COMMENT_NOT_PUBLISHED, $cid);
			drupal_set_message(t('Review Removed from Approved'));
		}
	}else if ($action == 'delete'){
		if(is_numeric($cid)) {
			db_query("DELETE FROM {comments} WHERE cid = %d", $cid);
			drupal_set_message(t('Review Removed from Approved'));
		}
	} else {
			drupal_set_message(t('Invalid action choosen!'), 'warning');
	}

	drupal_goto('siteadmin/productreview');
}
function pwcs_review_edit_form($form_info = array(), $nid = NULL, $cid = NULL, $status = NULL) {

	if ((isset($cid)) && ($cid>0)) {
		$results = db_query("SELECT * FROM {comments} WHERE `cid` = %d", $cid);
		while($result = db_fetch_object($results)) {
			$comments = (array) $result;
		}
	}

	$weight=0;
	// cid hidden
	if ($cid!=NULL) {
		$form['cid'] = array(
			'#type' => 'hidden',
			'#weight' => $weight++,
			'#value' => $cid,
		);
	}
	$form['nid'] = array(
			'#type' => 'hidden',
			'#weight' => $weight++,
			'#value' => $nid,
	);	
	$form['review-status'] = array(
			'#type' => 'hidden',
			'#weight' => $weight++,
			'#value' => $status,
	);		
	$form['review_subject'] = array(
		'#type' => 'textfield',
		'#title' => t('Review Title/Subject'),
		'#default_value' => $comments['subject'],
		'#weight' => $weight++,
	);
	$form['review_comment'] = array(
		'#type' => 'textarea',
		'#title' => t('Review Content'),
		'#default_value' => $comments['comment'],
		'#weight' => $weight++,
	);	
	$form['reviewer_name'] = array(
		'#type' => 'textfield',
		'#title' => t('Reviewed By'),
		'#default_value' =>  $comments['name'],
		'#weight' => $weight++,
	);		
	$form['reviewer_email'] = array(
		'#type' => 'textfield',
		'#title' => t('Reviewer Email'),
		'#default_value' => $comments['mail'],
		'#weight' => $weight++,
	);	
		
	$form['button1'] = array(
		'#type' => 'submit',
		'#weight' => $weight++,
		'#default_value' => 'Update'
	);
		

	$button_title = ($status==0)?t('Update & Unapprove Now'):t('Update & Approve Now');
	$form['button2'] = array(
		'#type' => 'submit',
		'#weight' => $weight++,
		'#default_value' => $button_title
	);
	
	$form['button3'] = array(
		'#type' => 'submit',
		'#weight' => $weight++,
		'#default_value' => 'Close'
	);
	
	return $form;
}
function pwcs_review_edit_form_submit($form_id, $form_values)
{
	global $user;
	die($form_values['clicked_button']['#post']['op']);
	print_r($form_values);
	if($form_values[submitted] == 1) {
		if ($form_values['clicked_button']['#post']['button1']!='')
		drupal_set_message(t('Button 1.'));
		if ($form_values['clicked_button']['#post']['button2']!='')
		drupal_set_message(t('Button 2.'));
		if ($form_values['clicked_button']['#post']['button3']!='')
		drupal_set_message(t('Button 3.'));		
	}

	//drupal_set_message(t('Review Has Been Updated.'));
}
function pwcs_siteadmin_productsreview_filter_form($form_info = array(), $filter = NULL) {

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
	return $form;
}
function pwcs_siteadmin_productsreview_filter_form_submit($form_id, $form_values)
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
	$_REQUEST[destination] = 'siteadmin/productreview/filterd';
}
function pwcs_product_review_count($nid = 'all') {
	
	$sql  = "SELECT status, count(status) as count FROM {comments}";
	$sql .= " WHERE comment != ''";
	if((is_numeric($nid)) && ($nid!='all'))
		$sql .= " AND nid = ".$nid;
	$sql .= " GROUP BY status";

	$review_count = array();
	$results = db_query($sql);
	while ($result = db_fetch_object($results)) {
		$review_count [$result->status] = $result->count;
	}
	return $review_count;
}

