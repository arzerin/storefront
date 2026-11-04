<?php
/* $Id: pwcs_category.php,v 1.0 2010/08/23 Rasel Kabir Shuvro Exp $ */

/**
 * Site Admin Category page theme
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_categories($action=NULL, $pwc_category_id=NULL) {

	if ($action=='delete'){
		if($pwc_category_id!=NULL){
			pwcs_category_delete($pwc_category_id);
		}
	}

	if ($action=='edit'){
		if($pwc_category_id!=NULL){
			return drupal_get_form('pwcs_category_form', $pwc_category_id);
		}
	}

	pwcs_process_noncorecat();
	$total_category = pwcs_category_count();

	$output = '';

	// List of all category
	$output .= '<div class="pwcs-page-content-inner">';
	if ($total_category>0)
	$output .= '<h3>'.t('Total '.$total_category. ' Categories Found').'</h3>';
	else
	$output .= '<h3>'.t('Total '.$total_category. ' Category Found').'</h3>';

	$output .= '<div id="category_list" class="pwcs-content-list">';
	$output .= drupal_get_form('pwcs_categorylist_sort_form');
	$output .= '</div>';
	$output .= '</div>';

	return $output;
}

/**
 * Implementation of category_form()
 * Added by Rasel Kabir
 */
function pwcs_category_form($form_info = array(), $pwc_category_id = NULL,$pwc_refer_category_id = NULL) {

    if ($pwc_category_id=="-1")
	    $pwc_category_id =NULL;

	// SQL to get the current $pwc_category_id if exists
	if ((isset($pwc_category_id)) && ($pwc_category_id>0)) {
		$results = db_query("SELECT * FROM {pwcs_category} WHERE `pwc_category_id` = %d", $pwc_category_id);
		while($result = db_fetch_object($results)) {
			$category = (array) $result;
		}
	}

	$weight=0;
	// pwc_category_id hidden
	if ($pwc_category_id!=NULL) {
		$form['pwc_category_id'] = array(
			'#type' => 'hidden',
			'#weight' => $weight++,
			'#value' => $pwc_category_id,
		);
	}

	$form['category_title'] = array(
		'#type' => 'textfield',
		'#title' => t('Category Name'),
		'#default_value' => ($pwc_category_id!=NULL) ? $category['category_title']:'',
		'#weight' => $weight++,
	);

	$categories = array('0'=>'<'.t('Parent Category').'>');
	$categories = $categories+pwcs_cat_array_selection_combo();

	if ($pwc_category_id!=NULL)
	{
		$form['parent_category_id'] = array(
			'#type' => 'select',
			'#options' => $categories,
			'#default_value' => $category['parent_category_id'],
			'#title' => t('Parent Category'),
			'#weight' => $weight++,
		);
	}else if ($pwc_refer_category_id!=NULL){
		$form['parent_category_id'] = array(
			'#type' => 'select',
			'#options' => $categories,
			'#default_value' => $pwc_refer_category_id,
			'#title' => t('Parent Category'),
			'#weight' => $weight++,
		);
	}else{
		$form['parent_category_id'] = array(
			'#type' => 'select',
			'#options' => $categories,
			'#title' => t('Parent Category'),
			'#weight' => $weight++,
		);
	}


	$button_title = ($pwc_category_id==NULL)?t('Create new category'):t('Update Category');
	$form['button'] = array(
		'#type' => 'submit',
		'#weight' => $weight++,
		'#default_value' => $button_title
	);

	return $form;
}

/**
 * Category form submit
 * Added by Rasel Kabir
 */
function pwcs_category_form_submit($form_id, $form_values)
{
	global $user;

	if($form_values[submitted] == 1) {

		// Update Category
		if (isset ($form_values['clicked_button']['#post']['pwc_category_id']) &&  ($form_values['clicked_button']['#post']['pwc_category_id']!= NULL)) {

			$category['pwc_category_id']= $form_values['clicked_button']['#post']['pwc_category_id'];
			$category['category_title']= $form_values['clicked_button']['#post']['category_title'];
			$category['parent_category_id']= $form_values['clicked_button']['#post']['parent_category_id'];
			$category['modified_on'] = time();

			if (db_result(db_query("SELECT `category_title` FROM {pwcs_category} WHERE `category_title` = '%s' AND `pwc_category_id` != %d", $category['category_title'], $category['pwc_category_id'])) == $category['category_title']) {
				drupal_set_message(t('Sorry').'! '.$category['category_title'].' '.t('is already added'), 'error');
			} else {
				pwcs_category_edit($category);

				//Sync req to PWC CORE
			    $api_data_category['pwc_category_id'] = $category['pwc_category_id'];
			    $api_data_category['parent_category_id'] = $category['parent_category_id'];
			    $api_data_category['category_title'] = $category['category_title'];
			    $api_data_category['category_level'] = '';
			    //$api_data_category['prev_parent_cateogry_id'] = ;

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
								db_query("UPDATE {pwcs_category} SET `is_sync` = %d, `modified_on` = %d WHERE pwc_category_id = %d", $cat_sync , $category['modified_on'], $category['pwc_category_id'] );
								drupal_set_message(t('Category Update Notification sent to Premium Web Cart'));
							}else{
								drupal_set_message(t('Could Not Send Category Update Notification to Premium Web Cart as part of Synchronization'), 'error');
							}
					    }else {
							drupal_set_message(t('Could Not Send Category Update Notification to Premium Web Cart as part of Synchronization'), 'error');
	            		}
					}
				}
			}

		// Add New category
		} else {

			$category['category_title']= $form_values['values']['category_title'];
			$category['parent_category_id']= $form_values['values']['parent_category_id'];
			$category['created_on'] = time();
			$category['weight'] = db_last_insert_id('pwcs_category', 'weight') + 1;

			if (db_result(db_query("SELECT `category_title` FROM {pwcs_category} WHERE `category_title` = '%s'", $category['category_title'])) == $category['category_title']) {

				drupal_set_message(t('Sorry').'! '.$category['category_title'].' '.t('is already added'), 'error');

			} else {

				$category_id = pwcs_category_add($category);

				//Sync req to PWC CORE
			    //$api_data_category['category_id'];
			    $api_data_category['category_title'] = $category['category_title'];
			    $api_data_category['parent_category_id'] = $category['parent_category_id'];
			    $api_data_category['category_level'] = 1;
			    $api_data_category['pwc_category_id'] = 0;

				if($user->merchant_enc_id != NULL) {
					if ($category['pwc_category_id'] != -1 )
					{
						$response_xml = pwcs_api_insertCategoryAPI($user->merchant_enc_id, $api_data_category);
						drupal_set_message(t('Category Update Notification Request sent to Premium Web Cart.'));
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
								db_query("UPDATE {pwcs_category} SET `pwc_category_id` = %d, `parent_category_id` = %d, `is_editable` = %d, `is_sync` = %d WHERE category_id = %d",
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

			}

		}

		if (user_access('is siteadmin')){
			$_REQUEST[destination] = 'siteadmin/categories';
		}
	}
	else {
		drupal_set_message(t('Opps! unfortunate error'), 'error');
	}
}

/**
 * Implementation of pwcs_category_add()
 * Added by Rasel Kabir
 */
function pwcs_category_add($category) {

    $table = 'pwcs_category';
    $object = new stdClass();
	$object->pwc_category_id = time();
	$object->category_title = $category['category_title'];
	$object->parent_category_id = $category['parent_category_id'];
	$object->weight = $category['weight'];
	$object->is_editable = '1';
	$object->created_on = $category['created_on'];
	drupal_write_record($table, $object);

/*
	$result = db_query("INSERT INTO {pwcs_category}" .
			" (`pwc_category_id`, `category_title`,`parent_category_id`,`weight`,`created_on`)" .
			" VALUES (%d, '%s', %d, %d, %d)",
			time(), $category['category_title'], $category['parent_category_id'], $category['weight'], $category['created_on']
	);

*/
	drupal_set_message(t('New category added!'));
	return db_last_insert_id('pwcs_category', 'category_id');

}


/**
 * Implementation of pwcs_category_edit()
 * Added by Rasel Kabir
 */
function pwcs_category_edit($category) {

	if (($category['parent_category_id'] == $category['pwc_category_id']) && ($category['pwc_category_id']!=0)){
		drupal_set_message(t('Sorry! category itself cant be its parent category.'), 'error');
		drupal_goto('siteadmin/categories');
	}

//	db_query("UPDATE {pwcs_category}
//		SET `category_title`='%s', `parent_category_id`= %d, `modified_on`=%d WHERE pwc_category_id = %d",
//		$category['category_title'], $category['parent_category_id'], $category['modified_on'], $category['pwc_category_id']
//	);

	$table = 'pwcs_category';

	$object = new stdClass();
	$object->category_title = $category['category_title'];
	$object->parent_category_id = $category['parent_category_id'];
	$object->modified_on = $category['modified_on'];
	$object->pwc_category_id = $category['pwc_category_id'];

	$update = 'pwc_category_id';

	drupal_write_record($table, $object, $update);

	drupal_set_message(t('Category has been Updated.'));
}

/**
 * Implementation of pwcs_category_delete()
 * Added by Rasel Kabir
 */
function pwcs_category_delete($pwc_category_id = NULL) {

	if ($pwc_category_id > 0) {
		db_query("DELETE FROM {pwcs_category} WHERE pwc_category_id = %d", $pwc_category_id);
		drupal_set_message(t('Category item deleted!'));
		drupal_goto('siteadmin/categories');
	} else {
		drupal_set_message(t('Category cant be deleted'), 'error');
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
function pwcs_cat_array($parent_category_id = NULL)
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
function pwcs_cat_array_selection_combo($parent=0, $spacer=' ') {

	$sql  = "SELECT pc.*";
	$sql .= " FROM {pwcs_category} pc";
	$sql .= " WHERE pc.parent_category_id = ".$parent;
	$sql .= " ORDER BY pc.weight ASC, pc.pwc_category_id";

	$num = pwcs_result_count($sql);
	$results = db_query($sql);

	$category_array = array();
	while($result = db_fetch_object($results)) {
		$category_array[$result->pwc_category_id] = $spacer.$result->category_title;
	    if(!(($result->pwc_category_id == 0) && ($result->parent_category_id == 0))){
		    $category_array = $category_array+pwcs_cat_array_selection_combo($result->pwc_category_id, '--'.$spacer);
	    }
	}

	return $category_array;
}


/**
 * PWCS category menu block for end user
 * Added by Rasel Kabir
 */
function pwcs_category_menu(){

	$output = '';
	$output .= pwcs_categories_tree(0);
	return $output;
}

/**
 * Recurssive category output.
 * Added by Rasel Kabir
 */
function pwcs_categories_tree($parent='0') {

	$sql = "SELECT pc.*, Count(pp.nid) AS product_amount";
	$sql .= " FROM {pwcs_category} pc";
	$sql .= " Left Join {pwcs_product} pp ON pc.pwc_category_id = pp.pwc_category_id";
	$sql .= " WHERE pc.parent_category_id = ".$parent;
	$sql .= " GROUP BY pc.pwc_category_id";
	$sql .= " ORDER BY pc.weight ASC, pc.category_title ASC, pc.category_id";

	$num = pwcs_result_count($sql);
	$results = db_query($sql);

	if($num==0) {
		return '';
	}
	//$spacer .= '- ';
	$output = '';
	$output .= '<ul class="pwcs-category-menu flyoutmenu">';

/*	if($parent == 0) {
		//PWCS_CATEGORY_DEFAULT ADDING
		$product_amount_under_no_category = db_result(db_query("SELECT Count(pp.nid) FROM {pwcs_product} pp WHERE pp.pwc_category_id = '".PWCS_CATEGORY_DEFAULT_VALUE."' GROUP BY pp.pwc_category_id"));
		if($product_amount_under_no_category == NULL)
			$product_amount_under_no_category = 0;

		$output .= '<li>';
		$output .= l( PWCS_CATEGORY_DEFAULT_NAME.'('.$product_amount_under_no_category.')', 'products/'.PWCS_CATEGORY_DEFAULT_VALUE);
		$output .='</li>';
	}*/

	while($result = db_fetch_object($results)) {

		if($num==0 || $result->parent_category_id=='0') {
		  //$spacer = '';
		}
		$output .= '<li>';
		//$output .= '<span>'.$spacer;
		if ($result->product_amount > 0)
			$output .= l( $result->category_title.' ('.$result->product_amount.')', 'products/'.$result->pwc_category_id, array('attributes' => array('class'=>'pwcs-cat-menu-item', 'id'=>'pwcs-cat-'.$result->pwc_category_id)));
		else
			$output .= l( $result->category_title.' ('.$result->product_amount.')', 'products/'.$result->pwc_category_id, array('attributes' => array('class'=>'pwcs-cat-menu-item no-product', 'id'=>'pwcs-cat-'.$result->pwc_category_id)));
		//$output .= '</span>';
	    if(!(($result->pwc_category_id == 0) && ($result->parent_category_id == 0))){
		    $output .= pwcs_categories_tree($result->pwc_category_id); //Recurrsion
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
function pwcs_get_categories_array($parent=0, $depth=1) {

	$sql  = "SELECT pc.*, count(pp.pwc_product_row_id) AS total_product ";
	$sql .= " FROM {pwcs_category} pc";
	$sql .= " LEFT JOIN {pwcs_product} pp  ON pp.pwc_category_id=pc.pwc_category_id";
	$sql .= " WHERE pc.parent_category_id = ".$parent;
	$sql .= " GROUP BY pc.pwc_category_id";
	$sql .= " ORDER BY pc.weight ASC, pc.category_title ASC, pc.category_id";


	$num = pwcs_result_count($sql);
	$results = db_query($sql);


	$category_array = array();
	while($result = db_fetch_object($results)) {
		$category_array[$result->pwc_category_id] = (array)$result;
		$category_array[$result->pwc_category_id]['depth'] = $depth;
		$category_array[$result->pwc_category_id]['is_editable'] = $result->is_editable;
		$category_array[$result->pwc_category_id]['products_no'] = $result->total_product;

		if ($parent==0)
			$category_array[$result->pwc_category_id]['parent'] = 1;
		else
			$category_array[$result->pwc_category_id]['parent'] = 0;

	    if(!(($result->pwc_category_id == 0) && ($result->parent_category_id == 0))){
		    $temp = pwcs_get_categories_array($result->pwc_category_id, $depth+1);
		    if(!empty($temp)) $category_array[$result->pwc_category_id]['child'] = $temp; //Recurrsion
	    }
	}

	return $category_array;
}

/**
 * Category list with drag and drop sorting [List as a form]
 * Added by Rasel Kabir 23 September 2010
 */
function pwcs_categorylist_sort_form() {

	$form = array();
	$form['#theme'] = 'pwcs_categorylist_sort_form';
	$category_array = pwcs_get_categories_array(0);
	$form = _pwcs_categorylist_sort_form($category_array);

	if (element_children($form)) {
		$form['submit'] = array(
			'#type' => 'submit',
			'#value' => t('Save Order'),
		);
	}
	else {
		$form['empty_menu'] = array('#value' => t('There are no category items yet.'));
	}

	return $form;
}


/**
 * Category list with drag and drop sorting [Building form in recurssive way]
 * Added by Rasel Kabir 23 September 2010
 */
function _pwcs_categorylist_sort_form($category_array) {

	static $form = array('#tree' => TRUE);
	foreach($category_array as $pwc_category_id => $category_item) {

		$form[$pwc_category_id]['#item']['depth'] 	  = $category_item['depth'];
		$form[$pwc_category_id]['title']			  = array('#value' => $category_item['category_title']);
		$form[$pwc_category_id]['weight']			  = array('#type' => 'weight', '#delta' => 50, '#default_value' => isset($form_state[$pwc_category_id]['weight']) ? $form_state[$pwc_category_id]['weight'] : $category_item['weight']);
		$form[$pwc_category_id]['pwc_category_id'] 	  = array('#type' => 'hidden', '#value' => $pwc_category_id );
		$form[$pwc_category_id]['parent_category_id'] = array('#type' => 'textfield', '#default_value' => isset($form_state[$pwc_category_id]['parent_category_id']) ? $form_state[$pwc_category_id]['parent_category_id'] : $category_item['parent_category_id'], '#size' => 6 );
		//$form[$pwc_category_id]['edit']			      = array('#value' => l(t('Edit'), 'siteadmin/categories/edit/'.$pwc_category_id));
		$form[$pwc_category_id]['products']			  = array('#value' => t($category_item['products_no']));


		//Only Editable Categories will have the Options
		if ($category_item['is_editable']==1)
		{
			if (($category_item['parent']) && $category_item['child'])
				$form[$pwc_category_id]['action']	= array('#value' => l(t('Add Sub Category'),'siteadmin/categories/addnew/-1/'.$pwc_category_id).t(' || ').l(t('Update Description'),'siteadmin/categories/edit/'.$pwc_category_id).t(' || ').t('<i>Delete</i>'));
			else
				$form[$pwc_category_id]['action']	= array('#value' => l(t('Add Sub Category'),'siteadmin/categories/addnew/-1/'.$pwc_category_id).t(' || ').l(t('Update Description'),'siteadmin/categories/edit/'.$pwc_category_id).t(' || ').l(t('Delete'),'siteadmin/categories/delete/'.$pwc_category_id));			
		}
		else
		{
			$form[$pwc_category_id]['action']	= array('#value' => l(t('Add Sub Category'),'siteadmin/categories/addnew/-1/'.$pwc_category_id).t(' || ').l(t('Update Description'),'siteadmin/categories/edit/'.$pwc_category_id).t(' || ').t('<i>Delete</i>'));
		}

		if($category_item['child']) {
			_pwcs_categorylist_sort_form($category_item['child']);
		}
	}
	return $form;
}

/**
 * Category list with drag and drop sorting [Table Theme]
 * Added by Rasel Kabir 24 September 2010
 */
function theme_pwcs_categorylist_sort_form($form) {

  drupal_add_tabledrag('categorylist_sort', 'match', 'parent', 'cat_parent_category_id', 'cat_parent_category_id', 'cat_pwc_category_id', TRUE, 100);
  drupal_add_tabledrag('categorylist_sort', 'order', 'sibling', 'cat_weight');

  $header = array(
    t('Description'),
    t('Weight'),
    t('Product Count'),
    t('Action'),
  );

  $rows = array();

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

      $row = array();
      $row[] = theme('indentation', $element['#item']['depth'] - 1) . drupal_render($element['title']);
      $row[] = drupal_render($element['weight']) . drupal_render($element['parent_category_id']) . drupal_render($element['pwc_category_id']);
	  $row[] = drupal_render($element['products']);
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
 * Category list with drag and drop sorting [After Save/Submit]
 * Added by Rasel Kabir 24 September 2010
 */
function pwcs_categorylist_sort_form_submit($form, $formid) {

	global $user;

	if($form['submit']['#type'] == 'submit') {
		foreach ($form['submit']['#post'] as $pwc_category_id=> $value){

			if ((is_numeric($value['pwc_category_id'])) && ($value['pwc_category_id']==0)){
				pwcs_process_noncorecat2($value['weight']);

			}
			if ((is_numeric($value['pwc_category_id'])) && ($value['pwc_category_id']>0)){
				$cat_sync = 0;
				$sql = "UPDATE {pwcs_category} SET weight = '". $value['weight']."', parent_category_id = '".$value['parent_category_id']."' WHERE pwc_category_id = ".$value['pwc_category_id']."";
				//drupal_set_message(t($sql));
				db_query($sql);
			}
		}//foreach ($form['submit']['#post'] as $pwc_category_id=> $value)


		drupal_set_message(t('Sorting Order for the category has been Saved Successfully.'));

		//sending al category information to sort them in core also.
		pwcs_sendCategoryOrder($user->merchant_enc_id, $user->site_api_key);

	} else {
		drupal_set_message(t('Sorting Order could not be Saved. May be nothing to Save.'));
	}//if-else($form['submit']['#type'] == 'submit')

}
function pwcs_process_noncorecat()
{
	$sql = "UPDATE {pwcs_category} SET parent_category_id=0 WHERE pwc_category_id = 0 AND is_editable = 0";
	$results = db_query($sql);
}
function pwcs_process_noncorecat2($weight=0)
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
function pwcs_category_details($pwc_category_id) {

	$category_array = array();

	if(!empty($pwc_category_id))
	{
		$sql  = "SELECT * FROM {pwcs_category} pc WHERE pc.pwc_category_id = ".$pwc_category_id;
		$results = db_query($sql);
		while($result = db_fetch_object($results)) {
			$category_array = (array)$result;
		}
	}
	return $category_array;
}
function pwcs_category_product_count($pwc_category_id) {
	$countrows = db_result(db_query("SELECT COUNT(*) FROM {pwcs_product} WHERE pwc_category_id='%d'",$pwc_category_id));
	return ($countrows > 0) ? $countrows : 0;
}
function pwcs_category_count() {
	$countrows = db_result(db_query("SELECT COUNT(*) FROM {pwcs_category}"));
	return ($countrows > 0) ? $countrows : 0;
}
function pwcs_product_count() {
	$countrows = db_result(db_query("SELECT COUNT(*) FROM {pwcs_product}"));
	return ($countrows > 0) ? $countrows : 0;
}
function pwcs_category_sort_request_to_core()
{
	$sql  = "SELECT * FROM {pwcs_category} WHERE is_editable = 1 ";
	$results = db_query($sql);
	$category_array = array();
	$category_xml = '<?xml version="1.0"?>
					 <pwccart>
                       <categoryorder>';
	while($result = db_fetch_object($results)) {
		$category_xml .= '
                         <categoryid>'.$result->pwc_category_id.'</categoryid>
                         <categoryparentid>'.$result->parent_category_id.'</categoryparentid>
		                 ';
	}
	$category_xml .= '   </categoryorder>
					 </pwccart>';
	return 	$category_xml;

}