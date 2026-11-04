<?php
/* $Id: pwcs_category.php,v 1.0 2010/08/23 Rasel Kabir Shuvro Exp $ */

/**
 * Site Admin Option page theme
 * Added by A.R. ZERIN
 */

function pwcs_attr_and_group_add() {

	//drupal_set_message(t('Tips: '.l(t('Create '), 'siteadmin/attribute-group/new').l(t('Options Group '), 'siteadmin/attribute-group').', '.l(t('Add Option '), 'siteadmin/attributes/new').'under Group and assign '.l(t('added Option '), 'siteadmin/attributes').' to '.l(t('product '), 'siteadmin/products').' from Edit Product'), 'warning');

    $output = '';
	$output .= '<div class="pwcs-page-content-inner">';
	$output .= '<div id="pwcs_product_list" class="ws-content-list">';
	//$output .= pwcs_product_list();
	$output .= drupal_get_form('pwcs_attr_and_group_add_form');
	$output .= '</div>';
	$output .= '</div>';

	return $output;
}
function pwcs_attr_and_group_add_form($form_info = array(), $attr_group_id = NULL) {

    if($attr_group_id > 0)
    {
        $results=db_query("SELECT * FROM {pwcs_attr_groups} WHERE id=".$attr_group_id);

        while($result = db_fetch_object($results)) {
            $attr_group = (array) $result;
        }

        $form['attribute'] = array(
        '#title' => t(PWCS_ATTRIBUTE_GROUP_NAME.' Update'),
        '#type' => 'fieldset',
        '#description' => t(''),
        '#collapsible' => TRUE,
        '#collapsed' => FALSE
      );

      $form['attribute']['attr_group_id'] = array(
         '#type' => 'hidden',
         '#default_value'=>$attr_group_id,
      );

/*
      $form['attribute']['name'] = array(
        '#title' => t('Local Name'),
        '#type' => 'textfield',
        '#description' => t('This name is required for application only. <br/>example values: color, type, size etc.'),
        '#required' => TRUE,
        '#default_value'=>$attr_group['attr_group_name'],
      );
*/      
      $form['attribute']['public_name'] = array(
        '#title' => t(PWCS_ATTRIBUTE_GROUP_NAME),
        '#type' => 'textfield',
        '#description' => t('This is the name that Customer will see when they will go in the Store Product Details Page.<br/>Example values: Color, Type, Size'),
        '#required' => TRUE,
        '#default_value'=>$attr_group['attr_group_public_name'],
      );

      $form['attribute']['is_required'] = array(
		'#type' => 'checkbox',
		'#default_value' => $attr_group['is_required'],
		'#description' => t('Your User will have to select this '.PWCS_ATTRIBUTE_GROUP_NAME.' when they will add Product in the cart for which this '.PWCS_ATTRIBUTE_GROUP_NAME.' will be assigned.'),
		'#prefix' => "<div class=\"\"><b>Is Required</b>",
		'#suffix' => "</div>",    
      );

      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Update Attribute')
      );
    }
    else
    {
        $form['attribute'] = array(
	    '#title' => t('New '.PWCS_ATTRIBUTE_GROUP_NAME),
	    '#type' => 'fieldset',
	    '#description' => t(''),
	    '#collapsible' => TRUE,
	    '#collapsed' => FALSE
      );

  $form['attribute']['attr_group_id'] = array(
     '#type' => 'hidden',
     '#default_value'=>$attr_group_id,
  );
/*
  $form['attribute']['name'] = array(
    '#title' => t('Local Name'),
    '#type' => 'textfield',
    '#description' => t('This name is required for application. <br/>example values: color, type, size etc.'),
    '#required' => TRUE,

  );
*/  
  $form['attribute']['public_name'] = array(
    '#title' => t(PWCS_ATTRIBUTE_GROUP_NAME),
    '#type' => 'textfield',
    '#description' => t('This is the name that Customer will see when they will go in the Store Product Details Page.<br/>Example values: Color, Type, Size'),
    '#required' => TRUE,
  );
  $form['attribute']['is_required'] = array(
	'#type' => 'checkbox',
	'#default_value' => 0,
	'#description' => t('Your User will have to select this '.PWCS_ATTRIBUTE_GROUP_NAME.' when they will add Product in the cart for which this '.PWCS_ATTRIBUTE_GROUP_NAME.' will be assigned.'),
	'#prefix' => "<div class=\"\"><b>Is Required</b>",
	'#suffix' => "</div>",    
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create '.PWCS_ATTRIBUTE_GROUP_NAME.' Now')
  );
    }

  $form['#tree'] = TRUE;

  return $form;
}

/**
* Validate the form.
*/
function pwcs_attr_and_group_add_form_validate($form, &$form_state) {

	if ($form_state['values']['attribute']['public_name'] == '') {
		// We notify the form API that this field has failed validation.
		//form_set_error('public_name',t('Please Provide Attribute Public Name'));
	}

   //$attr_name = $form_state['values']['attribute']['name'];
   $attr_public_name = $form_state['values']['attribute']['public_name'];
   $attr_group_id=$form_state['values']['attribute']['attr_group_id'];
   $is_required=$form_state['values']['attribute']['is_required'];

   if ((!empty($attr_public_name)))
   {
       if($attr_group_id > 0 || $attr_group_id!=NULL)
       {
           db_query("UPDATE {pwcs_attr_groups} SET `attr_group_public_name` = '%s',`is_required` = '%d' WHERE id = %d", $attr_public_name,$is_required, $attr_group_id );

           drupal_set_message(t(PWCS_ATTRIBUTE_GROUP_NAME.' has been Updated Successfully.'));
       }
       else
       {
	   		db_query("INSERT INTO {pwcs_attr_groups} (`attr_group_public_name`, `is_required`) VALUES ('%s','%d')",$attr_public_name,$is_required);
	   		drupal_set_message(t('Thanks for regisitering New '.PWCS_ATTRIBUTE_GROUP_NAME));
       }
	   drupal_goto('siteadmin/attribute-group');
   }
}
function pwcs_attr_add($attr_group_id='') {
	//drupal_set_message(t('Tips: '.l(t('Create '), 'siteadmin/attribute-group/new').l(t('Options Group '), 'siteadmin/attribute-group').', '.l(t('Add Option '), 'siteadmin/attributes/new').'under Group and assign '.l(t('added Option '), 'siteadmin/attributes').' to '.l(t('product '), 'siteadmin/products').' from Edit Product'), 'warning');
	$output = '';
	$output .= drupal_get_form('pwcs_attr_add_form',NULL,$attr_group_id);
	return $output;
}
function pwcs_attr_add_form($form_info = array(), $attr_id = NULL, $attr_group_id = NULL) {

  if ($attr_id > 0){

  	 $attr_group_id =0;
  	 $attr_name = '';

  	 $results=db_query("SELECT * FROM {pwcs_attributes} WHERE id = ".$attr_id);
   	  while($result = db_fetch_object($results)) {
            $attr_group_id = $result->group_id;
            $attr_name = $result->attr_name;;
        }
	  $form['attribute'] = array(
		'#title' => t(PWCS_ATTRIBUTE_NAME.' Update'),
		'#type' => 'fieldset',
		'#description' => t(''),
		'#collapsible' => TRUE,
		'#collapsed' => FALSE
	  );

	  $form['attribute']['name'] = array(
	    '#title' => t(PWCS_ATTRIBUTE_NAME.' Name'),
	    '#type' => 'textfield',
	  	'#value' => $attr_name,
	   	'#description' => t('ex. Blue, Red, XL, X, L, sleeveless, with sleeve etc based on '.PWCS_ATTRIBUTE_GROUP_NAME),
	    '#required' => TRUE,
	  );
  	  $options = pwcs_group_options();

	  $form['attribute']['group_id'] = array(
			'#title' => t(PWCS_ATTRIBUTE_GROUP_NAME),
			'#type' => 'select',
			'#description' => t(''),
	  		'#default_value' => $attr_group_id,
			'#options' => $options,
			'#multiple' => FALSE,
			'#weight' => 20,
		);
      $form['attribute']['attr_id'] = array(
         '#type' => 'hidden',
         '#default_value'=>$attr_id,
      );
	  $form['submit'] = array(
	    '#type' => 'submit',
	    '#value' => t('Save Update') //Please do not modify it, based on the value we have worked something
	  );

  }else
  {
	  $form['attribute'] = array(
		'#title' => t('New '.PWCS_ATTRIBUTE_NAME),
		'#type' => 'fieldset',
		'#description' => t(''),
		'#collapsible' => TRUE,
		'#collapsed' => FALSE
	  );

	  $form['attribute']['name'] = array(
	    '#title' => t(PWCS_ATTRIBUTE_NAME.' Name'),
	    '#type' => 'textfield',
	    '#description' => t('ex. Blue, Red, XL, X, L, sleeveless, with sleeve etc based on '.PWCS_ATTRIBUTE_GROUP_NAME),
	    '#required' => TRUE,
	  );


	  $options = pwcs_group_options();

	  $form['attribute']['group_id'] = array(
			'#title' => t(PWCS_ATTRIBUTE_GROUP_NAME),
			'#type' => 'select',
			'#description' => t(''),
			'#options' => $options,
	  		'#default_value' => $attr_group_id,
			'#multiple'=> FALSE,
			'#weight' => 20,
		);
      $form['attribute']['attr_id'] = array(
         '#type' => 'hidden',
         '#default_value'=>'0',
      );

	 $form['submit'] = array(
	    '#type' => 'submit',
	    '#value' => t('Save')
	  );


  }

  $form['#tree'] = TRUE;
  return $form;
}

/**
 * Validaate Functions after the new Attribute Request comes
 *
 * @author A.R. ZERIN <a.r.zerin@gmail.com>
 * @version 1.0
 * @copyright A.R. ZERIN <a.r.zerin@gmail.com>
 * @package PWC Store
 * @link siteadmin/attributes/new
 * @example siteadmin/attributes/new
 * @access private
 * @param array [$form]
 * @param array [$form_state]
 * @param $form_state
 * @see  pwcs_attr_add_form
 * @usedby siteadmin/attributes/new
 *
 * @return NULL, nothing to return
 *
 */

function pwcs_attr_add_form_validate($form, &$form_state) {

/*
	if ($form_state['values']['attribute']['name'] == '') {
		//We notify the form API that this field has failed validation.
		//form_set_error('name',t('Please Provide Attribute Name'));
	}
*/	
	if (($form_state['values']['attribute']['group_id'] == '')||($form_state['values']['attribute']['group_id'] == '0')) {
		//We notify the form API that this field has failed validation.
		form_set_error('group_id',t('Please Select a Valid Attribute'));
	}

   $attr_name = $form_state['values']['attribute']['name'];
   $attr_group_id = $form_state['values']['attribute']['group_id'];
   $attr_id = $form_state['values']['attribute']['attr_id'];

   if ($form_state['values']['submit']=='Save Update')
   {
 		$attr_name = $form['submit']['#post']['attribute']['name'];
   		$attr_group_id =  $form['submit']['#post']['attribute']['group_id'];
   		$attr_id =  $form['submit']['#post']['attribute']['attr_id'];

		   db_query("UPDATE {pwcs_attributes} SET `group_id`='%d',`attr_name`='%s' WHERE id='%d'",
						$attr_group_id, $attr_name, $attr_id  );
		   drupal_set_message(t('Selected Option has been Updated Successfully.'));
		   drupal_goto('siteadmin/attributes');

   }else {
	   if ((!empty($attr_name)) && (!empty($attr_group_id)))
	   {
		   db_query("INSERT INTO {pwcs_attributes} (`group_id`,`attr_name`) VALUES ('%d', '%s')",
						$attr_group_id, $attr_name  );
		   drupal_set_message(t('New Option has been Saved Successfully.'));
		   drupal_goto('siteadmin/attributes');
	   }
   }
}
/**
 * Site Admin product page theme
 * Added by A.R. ZERIN
 */
function pwcs_siteadmin_groups_form($action=NULL, $nid=NULL) {

  $form = array();

  $form['groups']['#tree'] = TRUE;
  $position = 0;

  $groups_result = pwcs_get_attr_group_resultset();

	//	$row[] = l(t('Edit'),'siteadmin/attribute-group/action/edit/'.$result->id);
	//	$row[] = l(t('Delete'),'siteadmin/attribute-group/action/delete/'.$result->id);

	while($result = db_fetch_object($groups_result)) {

		$position = $position+1;
/*
	    $form['groups'][$result->id]['delete'] = array(
	      '#type' => 'checkbox',
	    );
*/	    
	    /*
	    $form['groups'][$result->id]['attr_group_name'] = array(
	      '#value' => $result->attr_group_name,
	    );
	    */
	    $form['groups'][$result->id]['attr_group_public_name'] = array(
	      '#value' => l(t($result->attr_group_public_name), 'siteadmin/attributes/'),
	    );
	    $form['groups'][$result->id]['is_required'] = array(
	      '#value' => ($result->is_required == 1) ? "YES" : "NO",
	    );
	    $form['groups'][$result->id]['total_attribute'] = array(
	      '#value' => $result->total_attribute,
	    );

	    $form['groups'][$result->id]['id'] = array(
	      '#type' => 'hidden',
	      '#value' => $result->id,
	    );

	    $form['groups'][$result->id]['position'] = array(
	      '#type' => 'textfield',
	      '#default_value' => $position,
	      '#size' => 3,
	      '#maxlenth' => 4,
	      '#attributes' => array('class' => 'macro-position'),
	    );
	    /*
	    $form['groups'][$result->id]['action'] = array(
	      '#value' => l(t('Add '.PWCS_ATTRIBUTE_NAME), 'siteadmin/attributes/new/' . $result->id).t(' || ').l(t('Rename'), 'siteadmin/attribute-group/action/edit/' . $result->id),
	    );
	    */
	    $form['groups'][$result->id]['action'] = array(
	      '#value' => l(t('Load Premium Web Cart '.PWCS_ATTRIBUTE_NAME.''), PWCS_PWC_AUTO_LOGIN_URL . PWCS_PWC_CORE_URL . 'products/attribute/groupList.html' , array('attributes' => 
				array('class'=>'pwcs-popup-iframe', 'title'=>'PREMIUMWEBCART Attributes'))).t(' || ').l(t('Attributes'), 'siteadmin/attributes/'),
	    );


	}

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save the Changes'),
  );
  return $form;
}
/**
 * Theme the display of the settings form as a table.
 */
function theme_pwcs_siteadmin_groups_form($form) {
  $headers = array(t(PWCS_ATTRIBUTE_GROUP_NAME), t('End User Selection Required'), t(PWCS_ATTRIBUTE_NAME.' Count'), t('Position'), t('Action'));
  $rows = array();
  foreach (element_children($form['groups']) as $mid) {
    $row = array();
    $row[] = drupal_render($form['groups'][$mid]['attr_group_public_name']);
    $row[] = drupal_render($form['groups'][$mid]['is_required']);
    $row[] = drupal_render($form['groups'][$mid]['total_attribute']);
    $row[] = drupal_render($form['groups'][$mid]['position']);
    $row[] = drupal_render($form['groups'][$mid]['action']);
    $rows[] = array('data' => $row, 'class' => 'draggable');
  }

  if (empty($rows)) {
    $rows[] = array(array('colspan' => 5, 'data' => t('There are currently no '.PWCS_ATTRIBUTE_NAME.'.')));
    //$rows[] = array(array('colspan' => 5, 'data' => l(t('Add the first one'), 'siteadmin/attribute-group/new')));
  }
  else {
    drupal_add_tabledrag('table-attr-group', 'order', 'sibling', 'macro-position');
    //$rows[] = array(array('colspan' => 5, 'data' => l(t('Add another'), 'siteadmin/attribute-group/new')));
  }


  $output = '';
  $output .= theme('table', $headers, $rows, array('id' => 'table-attr-group'));
  $output .= drupal_render($form);

  return $output;
}
/**
 * Submit handler for the pwcs_siteadmin_groups_form().
 */
function pwcs_siteadmin_groups_form_submit($form, $form_state) {

/*  foreach ($form_state['values']['groups'] as $group)
  {
    $gid = $group['id'];
    echo "<pre>";
    print_r($group);
    echo "</pre>";
    $sql="UPDATE  {pwcs_attr_groups} SET weight = '" . $group['position'] . "' WHERE id = ".$gid;
    echo $sql;

  }
  die();
*/
  $record_affected = 0;
  $record_deleted = 0;
  foreach ($form_state['values']['groups'] as $group)
  {
    $gid = $group['id'];

    $sql="UPDATE  {pwcs_attr_groups} SET weight = '".$group['position']."' WHERE id = ".$gid;
    if(db_query($sql))
    {
		$record_affected++;
    }
    if ($group['delete']=='1')
    {

        $sql="DELETE FROM {pwcs_attributes} WHERE group_id = ".$gid;
        if(db_query($sql))
        {
           $sql_sub="DELETE FROM {pwcs_attr_groups} WHERE id = ".$gid;
           if(db_query($sql_sub))
           {
             $record_deleted++;
           }
        }
    }
  }
  if ($record_affected > 0)
  	drupal_set_message(t(PWCS_ATTRIBUTE_NAME.' Order has been Saved Successfully.'));

  if ($record_deleted > 0)
  	drupal_set_message($record_deleted." ".PWCS_ATTRIBUTE_GROUP_NAME."(s) has been Deleted");

  drupal_goto('siteadmin/attribute-group');

}
function pwcs_siteadmin_attributes($action=NULL, $nid=NULL) {

	unset($_SESSION['messages']);
	//drupal_set_message(t('Tips: '.l(t('Create '), 'siteadmin/attribute-group/new').l(t('Options Group '), 'siteadmin/attribute-group').', '.l(t('Add Option '), 'siteadmin/attributes/new').'under Group and assign '.l(t('added Option '), 'siteadmin/attributes').' to '.l(t('product '), 'siteadmin/products').' from Edit Product'), 'warning');

	$output = '';
	$output .= '<div class="pwcs-page-content-inner">';
	$output .= '<div>'.l(t('Load Premium Web Cart '.PWCS_ATTRIBUTE_NAME.' Page'), PWCS_PWC_AUTO_LOGIN_URL . PWCS_PWC_CORE_URL . 'products/attribute/groupList.html' , array('attributes' => 
				array('class'=>'pwcs-popup-iframe', 'title'=>'PREMIUMWEBCART Attributes'))).'</div>';
	
	$sql  = "SELECT pag.*,count(patt.group_id) AS total_attribute FROM {pwcs_attr_groups} pag ";
	$sql .= "LEFT JOIN {pwcs_attributes} patt  ON pag.id = patt.group_id ";
	$sql .= "GROUP BY pag.id ";
	$sql .= "ORDER BY weight ASC";

	
	
	$results = db_query($sql);
	$a = 0;
	while($result = db_fetch_object($results))
	{
		
		$output .= drupal_get_form('pwcs_siteadmin_groupattribute_form',$result->id, $result->attr_group_public_name,$result->total_attribute);
		$a++;
	}
	if ($a==0) {
		$output .= drupal_get_form('pwcs_siteadmin_noattribute_form');
	}
	if ($a==1) {
		drupal_set_message(t('Notice: '.PWCS_ATTRIBUTE_GROUP_NAME.' along with '.PWCS_ATTRIBUTE_NAME.' have been displayed according to their weight.'), 'warning');
	}	
	$output .= '</div>';

	return $output;
}

/**
 * FORM that generates Attributes List Group based
 *
 * @todo Attributes Order Save Option should be implemented
 *       to sort them in Product Details Page
 *
 * @author A.R. ZERIN <a.r.zerin@gmail.com>
 * @version 1.0
 * @copyright
 * @package PWC Store
 * @link http://project_url/siteadmin/attributes
 * @example http://www.pwcstores.com/store/siteadmin/attributes
 * @access public
 * @param array $form_info
 * @param integer $attr_group_id DEFAULT VALUE 0
 * @param string $attr_group_name The Attribute Group Name for the Legend / Fieldset Caption
 * @see  pwcs_siteadmin_attributes Get called from Here
 *
 * @usedby pwcs_siteadmin_attributes
 *
 * @last-modified on 26 October, 2010 by A.R. ZERIN <a.r.zerin@gmail.com>
 *
 * @return $form , Form Array
 *
 */

function pwcs_form_attributes_list($form_info, $attr_group_id=0,  $attr_group_name='') {

  $form['attribute'] = array(
	'#title' => t(PWCS_ATTRIBUTE_GROUP_NAME.' : '.$attr_group_name),
	'#type' => 'fieldset',
	'#description' => t(''),
	'#collapsible' => TRUE,
	'#collapsed' => FALSE
  );

  $form['attribute']['target'] = array(
		'#type'   => 'markup',
		'#prefix' => '<div id="target">',
		'#value'  => t(''.pwcs_attr_list2($attr_group_id)),
		'#suffix' => '</div>',
   );

// NEXT PHASE TASK
/*  $form['attribute']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Order')
  );
*/
	return $form;
}

/**
 * List of all attribute groups (siteadmin)
 * Added by A.R. ZERIN
 */
function pwcs_attr_list2($attr_group_id=0)
{
	$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
	$pagelimit = 15;
	$pageelement = 0;
	$pageparameters = array();
	$pagequantity = 5;

	$output	= '';

	$sql  = 'SELECT pa.id, pa.attr_name AS attribute_name, pag.attr_group_public_name AS group_name ';
	$sql .= 'FROM {pwcs_attr_groups} pag LEFT JOIN {pwcs_attributes} pa ';
	$sql .= 'ON pag.id = pa.group_id WHERE pa.id IS NOT NULL AND pag.id = '.$attr_group_id .' ORDER BY pa.weight ASC, pag.attr_group_public_name';

	$header = array(
		t('Attribute Name'),
		t('Attribute Group'),
		//t('Edit'),
		t('Delete')
	);

	$rows = array();

	$results = db_query($sql);
	//$results = pager_query($sql, $pagelimit);

	$output .= '<div class="pwcs_table">';

	while($result = db_fetch_object($results))
	{
		$row = array();
		$row[] = $result->attribute_name;
		$row[] = $result->group_name;
		//$row[] = l(t('Edit'),'');
		$row[] = l(t('Delete'),'siteadmin/attributes/action/delete/'.$result->id);
		$rows[] = $row;
	}
	if (empty($rows)) {
	    $rows[] = array(array('colspan' => 3, 'data' => t('There are currently no attributes for this Group.')));
	}
	else {
	    drupal_add_tabledrag('macromaker-macros', 'order', 'sibling', 'macro-position');
	}
	$output .= theme('table', $header, $rows, array('id' => 'macromaker-macros','width'=>'100%'));
	$output .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);

	$output .= '</div>';

	return $output;
}


/**
 * List of all attribute groups (siteadmin)
 * Added by A.R. ZERIN
 */
function pwcs_attr_list($userid=NULL)
{
	$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
	$pagelimit = 15;
	$pageelement = 0;
	$pageparameters = array();
	$pagequantity = 5;

	$output	= '';

	$sql  = 'SELECT pa.id, pa.attr_name AS attribute_name, pag.attr_group_public_name AS group_name ';
	$sql .= 'FROM {pwcs_attr_groups} pag LEFT JOIN {pwcs_attributes} pa ';
	$sql .= 'ON pag.id = pa.group_id WHERE pa.id IS NOT NULL ORDER BY pa.weight ASC, pag.attr_group_public_name';

	$header = array(
		t('Option'),
		t('Attribute'),
		t('Edit'),
		t('Delete')
	);

	$rows = array();

	$results = db_query($sql);
	//$results = pager_query($sql, $pagelimit);

	$output .= '<div class="pwcs_table">';

	while($result = db_fetch_object($results))
	{
		$row = array();
		$row[] = $result->attribute_name;
		$row[] = $result->group_name;
		$row[] = l(t('Edit'),'');
		$row[] = l(t('Delete'),'');
		$rows[] = $row;
	}
	$output .= theme('table', $header, $rows, array('width'=>'100%'));
	$output .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);

	$output .= '</div>';

	return $output;
}

/**
 * return all the Groups List as Array so that Drupal Form can render it
 *
 * @author A.R. ZERIN <a.r.zerin@gmail.com>
 * @version 1.0
 * @copyright A.R. ZERIN <a.r.zerin@gmail.com>
 * @package PWC Store
 * @link NO DIRECT LINK
 * @access private
 * @param No Parameter is required
 * @see  pwcs_attr_add_form
 * @usedby pwcs_attr_add_form
 * @return array, Arrau of Attribute Group List
 *
 */

function pwcs_group_options()
{
	$sql  = "SELECT * FROM {pwcs_attr_groups} ";
	$sql .= "ORDER BY id";

    $results = db_query($sql);
	$options = array();
	$options[] = '--- Select '.PWCS_ATTRIBUTE_GROUP_NAME.' ----';
	while($result = db_fetch_object($results))
	{
		$options[$result->id] = check_plain($result->attr_group_public_name);
	}
	return $options;
}

function pwcs_siteadmin_attributegroup($action=null,$attr_group_id='')
{
    if($action=="delete")
    {
        $sql="DELETE FROM {pwcs_product_attributes} WHERE attr_group_id=".$attr_group_id;
        if(db_query($sql))
        {
            $sql="DELETE FROM {pwcs_attributes} WHERE group_id=".$attr_group_id;
            if(db_query($sql))
            {
                $sql="DELETE FROM {pwcs_attr_groups} WHERE id=".$attr_group_id;
                if(db_query($sql))
                {
                drupal_set_message(PWCS_ATTRIBUTE_GROUP_NAME." has been deleted");
                }
                else
                {
                    drupal_set_message(PWCS_ATTRIBUTE_GROUP_NAME." cannot be deleted");
                }

            }


        }
    drupal_goto('siteadmin/attribute-group/list');

    }
    if($action=="edit")
    {
        if($attr_group_id!=NULL){
            return drupal_get_form('pwcs_attr_and_group_add_form',$attr_group_id);
        }
    }

}

function pwcs_siteadmin_attributesaction($action=null,$attr_id='')
{
    if($action=="delete")
    {
/*      $sql="DELETE FROM {pwcs_product_attributes} WHERE attr_id=".$attr_id;
        if(db_query($sql))
        {
*/
    	    $sql="DELETE FROM {pwcs_attributes} WHERE id=".$attr_id;
            if(db_query($sql))
            {
                drupal_set_message(PWCS_ATTRIBUTE_NAME." has been deleted");
            }
            else
            {
                drupal_set_message(PWCS_ATTRIBUTE_NAME." cannot be deleted");
            }
        /*}*/
    }
    if($action=="edit")
    {
    	if($attr_id!=NULL){
           // return drupal_get_form('pwcs_attr_update_form',$attr_id);
            return drupal_get_form('pwcs_attr_add_form',$attr_id);
        }

    	die();
    }

    drupal_goto('siteadmin/attributes');

}
function pwcs_siteadmin_product_attributes_action($action=null,$attr_id='',$node_id)
{
    if($action=="delete")
    {
        $sql="UPDATE {pwcs_product_attributes} SET is_deleted = '1' WHERE nId='".$node_id."' AND attributes_id = ".$attr_id;
        if(db_query($sql))
        {
                drupal_set_message("Selected ".PWCS_ATTRIBUTE_NAME." for the Product has been deleted");
        }
        else
        {
                drupal_set_message("Selected ".PWCS_ATTRIBUTE_NAME." for the Product cannot be deleted");
        }
    }
    drupal_goto('node/'.$node_id.'/edit');
}
function pwcs_product_attr_edit($node_id='',$group_name='') {
	//drupal_set_message(t('Tips: '.l(t('Create '), 'siteadmin/attribute-group/new').l(t('Options Group '), 'siteadmin/attribute-group').', '.l(t('Add Option '), 'siteadmin/attributes/new').'under Group and assign '.l(t('added Option '), 'siteadmin/attributes').' to '.l(t('product '), 'siteadmin/products').' from Edit Product'), 'warning');
	$output = '';
	$output .= drupal_get_form('pwcs_product_attr_edit_form');
	return $output;
}

function pwcs_product_attr_edit_form() {

 		$form['product_attribute']['attrib_new_tab'] = array(
			'#title' => t('Update Product Option'),
			'#type' => 'fieldset',
			'#description' => t(''),
			'#collapsible' => FALSE,
			'#collapsed' => FALSE
		);
		$form['product_attribute']['attrib_new_tab']['product_attribute_edit_form'] = array(
			'#type' => 'hidden',
			'#default_value' => 1,
		);
		// END TAB product_attributes

		// START TAB Image selection
		$product_attribute_array = pwcs_get_product_attributes();

    foreach($product_attribute_array as $key=>$val)
    {
        foreach($val as $key2=>$val2)
        {
          $form['product_attribute']['attrib_new_tab'][$key] = array(
            '#type' => 'select',
            '#options' => $val2,
            '#title' => t('Select '.$key2),
            '#weight' => $weight++,
			'#description' => t('Select <strong>'.$key2.'</strong> Options If Applicable for this Product'),
          );
        }
    }
		$form['product_attribute']['attrib_new_tab']['quantity'] = array(
		  '#type' => 'textfield',
		  '#title' => t('Inventory Current Quantity'),
		  '#default_value' => 0,
		  '#attributes' => array('class' => 'small_text_field'),
		  '#maxlength' => 8,
		  '#description' => t('Number of Product with your above selected options that is available in your store.'),
		  '#weight' => $weight++,
		);
		$price_inc_desc= array(2=>'No Change', 0=>'Increase', 1=>'Decrease' );
		$form['product_attribute']['attrib_new_tab']['price_inc_desc'] = array(
			'#type' => 'select',
			'#options' => $price_inc_desc,
			'#title' => t('If Price will be Increased or Decreased for the above selected Option'),
			'#description' => t('If you select <strong>Increase</strong> then Price will be added with Regular Price when user will add this options product in the cart. If you select <strong>Decrease</strong> then Price will be deducted from Regular Price when user will add this options product in the cart. If you select <strong>No Change</strong> then Price will be same as  Regular Price when user will add this options product in the cart.'),
			'#weight' => $weight++,
		);
		$form['product_attribute']['attrib_new_tab']['inc_desc_amount'] = array(
			'#type' => 'textfield',
			'#title' => t('Price that will be Increased or Decreased for this Option'),
			'#attributes' => array('class' => 'small_text_field'),
			'#default_value' => '0.00',
		    '#maxlength' => 14,
			'#description' => t('The amount of price that will be added with Regular Price.'),
			'#weight' => $weight++,
		);

		$form['product_attribute']['attrib_new_tab']['submit'] = array(
			'#type' => 'submit',
			'#id'   => 'edit-submit',
			'#value' => t('Save'),
			'#weight' => $weight++,
		);
  $form['#tree'] = TRUE;
  return $form;
}
function pwcs_get_attr_group_resultset()
{
	$sql  = "SELECT pag.*,count(patt.group_id) AS total_attribute FROM {pwcs_attr_groups} pag ";
	$sql .= "LEFT JOIN {pwcs_attributes} patt  ON pag.id = patt.group_id ";
	$sql .= "GROUP BY pag.id ";
	$sql .= "ORDER BY weight ASC";

	$results = db_query($sql);
	return $results;
}

function pwcs_siteadmin_groupattribute_form($form_info, $attr_group_id=0,  $attr_group_name='', $attrgroup_count=0) {

  $form = array();
  $form['attribute'] = array(
	'#title' => t('<b>'.PWCS_ATTRIBUTE_GROUP_NAME.'</b> : '.$attr_group_name.' ('.$attrgroup_count.')'),
	'#type' => 'fieldset',
	'#description' => t(''),
	'#collapsible' => TRUE,
	'#collapsed' => FALSE
  );

  $form['attribute']['target'] = array(
		'#type'   => 'markup',
		'#prefix' => '<div id="target">',
		'#value'  => t(''.drupal_get_form('pwcs_siteadmin_att_groups_form',$attr_group_id, $attr_group_name)),
		'#suffix' => '</div>',
  );

  $form['attribute']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );


  return $form;
}
function pwcs_siteadmin_groupattribute_form_submit($form, $form_state) {

  $record_affected = 0;
  $record_deleted = 0;

  foreach ($form['#post']['groups'] as $group)
  {
  	$gid = $group['id'];

    $sql="UPDATE  {pwcs_attributes} SET weight = '".$group['position']."' WHERE id = ".$gid;
    if(db_query($sql))
    {
		$record_affected++;
    }
  }

  foreach ($form['#post']['groups'] as $group)
  {
  	$gid = $group['id'];
	if ($group['delete']=='1')
    $sql="DELETE FROM {pwcs_attributes} WHERE id = ".$gid;
    if(db_query($sql))
    {
		$record_deleted++;
    }

  }

  if ($record_affected > 0)
  drupal_set_message(t(PWCS_ATTRIBUTE_NAME.' has been Saved.'));

  if ($record_deleted > 0)
  drupal_set_message(t(PWCS_ATTRIBUTE_NAME.' has been Deleted.'));

  drupal_goto('siteadmin/attributes');
}
function pwcs_siteadmin_att_groups_form($form_info, $attr_group_id=0,  $attr_group_name='') {

  $form = array();

  $form['groups']['#tree'] = TRUE;
  $position = 0;

	$sql  = 'SELECT pa.id, pa.attr_name AS attribute_name, pag.attr_group_public_name AS group_name ';
	$sql .= 'FROM {pwcs_attr_groups} pag LEFT JOIN {pwcs_attributes} pa ';
	$sql .= 'ON pag.id = pa.group_id WHERE pa.id IS NOT NULL AND pag.id = '.$attr_group_id .' ORDER BY pag.weight ASC, pa.weight ASC, pag.attr_group_public_name';


  $groups_result = db_query($sql);

	//	$row[] = l(t('Edit'),'siteadmin/attribute-group/action/edit/'.$result->id);
	//	$row[] = l(t('Delete'),'siteadmin/attribute-group/action/delete/'.$result->id);

	    $form['groupsattrid'] = array(
	      '#type' => 'hidden',
	      '#value' => $attr_group_id,
	    );
	while($result = db_fetch_object($groups_result)) {

		$position = $position+1;

/*
	    $form['groups'][$result->id]['delete'] = array(
	      '#type' => 'checkbox',
	    );
*/	    
	    $form['groups'][$result->id]['attribute_name'] = array(
	      '#value' => $result->attribute_name,
	    );
	    $form['groups'][$result->id]['id'] = array(
	      '#type' => 'hidden',
	      '#value' => $result->id,
	    );
	    $form['groups'][$result->id]['gid'] = array(
	      '#type' => 'hidden',
	      '#value' => $attr_group_id,
	    );

	    $form['groups'][$result->id]['position'] = array(
	      '#type' => 'textfield',
	      '#default_value' => $position,
	      '#size' => 3,
	      '#maxlenth' => 4,
	      '#attributes' => array('class' => 'macro-position'),
	    );
	    $form['groups'][$result->id]['edit'] = array(
	      '#value' => l(t('edit'), PWCS_PWC_AUTO_LOGIN_URL . PWCS_PWC_CORE_URL . 'products/attribute/groupList.html' , array('attributes' => 
				array('class'=>'pwcs-popup-iframe', 'title'=>'PREMIUMWEBCART Attributes'))),
	    );
	}
  return $form;
}
function theme_pwcs_siteadmin_att_groups_form($form) {
 
  $rows = array();
  $a = 0;
  $group_id = '';
  $group_id= $form['groupsattrid']['#value'];

  if (is_array(element_children($form['groups'])))
  foreach (element_children($form['groups']) as $mid) {
    $row = array();
    $row[] = drupal_render($form['groups'][$mid]['attribute_name']);
    $row[] = drupal_render($form['groups'][$mid]['position']);
    $row[] = drupal_render($form['groups'][$mid]['edit']);
    //$row[] = drupal_render($form['groups'][$mid]['delete']);
    $rows[] = array('data' => $row, 'class' => 'draggable');
    $a = $mid;
    $group_id = $form['groups'][$mid]['gid']['#value'];
  }
  if (empty($rows)) {
   $headers = array(t(PWCS_ATTRIBUTE_NAME));
  }else{
   $headers = array(t(PWCS_ATTRIBUTE_NAME),  t('Position'), t('Edit'));
  }
  if (empty($rows)) {
    $rows[] = array(array('colspan' => 2, 'data' => t('There are currently no '.PWCS_ATTRIBUTE_NAME.' for this '.PWCS_ATTRIBUTE_GROUP_NAME.'.')));
    //$rows[] = array(array('colspan' => 3, 'data' => l(t('Add '.PWCS_ATTRIBUTE_NAME.''), 'siteadmin/attributes/new/'.$group_id)));
  }
  else {
    drupal_add_tabledrag('macromaker-macros'.$a, 'order', 'sibling', 'macro-position');
    //$rows[] = array(array('colspan' => 3, 'data' => l(t('Add another '.PWCS_ATTRIBUTE_NAME), 'siteadmin/attributes/new/'.$group_id)));
  }



  $output = '';
  $output .= theme('table', $headers, $rows, array('id' => 'macromaker-macros'.$a));
  $output .= drupal_render($form);

  return $output;
}
function pwcs_siteadmin_noattribute_form($form_info) {

  $form = array();

  $form['attribute'] = array(
	'#title' => t('<b>No Attribute has been Found.</b>'),
	'#type' => 'fieldset',
	'#description' => t(''),
	'#collapsible' => FALSE,
	'#collapsed' => FALSE
  );

  $form['attribute']['target'] = array(
		'#type'   => 'markup',
		'#prefix' => '<div id="target">',
		'#value'  => t('Please '.l(t('add an Attribute '), 'siteadmin/attribute-group/new').' first.'),
		'#suffix' => '</div>',
  );
  return $form;
}
