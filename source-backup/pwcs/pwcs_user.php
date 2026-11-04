<?php
/* $Id: pwcs_user.php.php,v 1.0 2010/08/17 Rasel Kabir Exp $ */

define('SITEADMIN', 'siteadmin');
define('PHOTOGRAPHER', 'photographer');

/**
 * Implementation of hook_perm()
 * Added by Rasel Kabir
 */
function pwcs_user_perm()
{
	return array ('is siteadmin');
}

/**
 * Implementation of hook_user()
 * Added by Rasel Kabir
 */
function pwcs_user($op, &$edit, &$account, $category = NULL)
{ 
	global $user;

	switch ($op)
	{
		case 'view':
			$account->ws_user_details = pwcs_user_profile($account);
			break;
		case 'categories':
			break;

		case 'login':
			
			unset($_SESSION['pwcs_cart']);

			if ($user->uid == 1) {
				//drupal_goto('admin');
				$_REQUEST[destination] = 'admin';
			}
			else if (user_access('is siteadmin')) {
				$_REQUEST[destination] = 'siteadmin';

//				if(variable_get('pwcs_fetchMerchantData', 0) == 0){
//				
//					if($user->merchant_enc_id != NULL) {
//						pwcs_fetchMerchantData($user->merchant_enc_id);
//						variable_set('pwcs_fetchMerchantData', '1');
//				   		drupal_set_message(t('Data Pool Completed'));
//						$_REQUEST[destination] = 'user';
//					}
//					else{
//				   		drupal_set_message(t('Missing Merchant Id'), 'error');
//						$_REQUEST[destination] = 'user/'.$user->uid.'/edit';
//					}
//				} else {
//					drupal_set_message(t('You have previously pooled data!'));
//				}
			}
			else{
				//drupal_goto('<front>');
			}

			break;

		case 'load':

			break;

		case 'register':
				//drupal_set_title(t('Add Siteadmin'));
				//$form = pwcs_user_registration($edit,$account);
				//return $form;
			break;

		case 'form':
		
				if ($account->uid == $user->uid)
					drupal_set_title(t('Edit your information'));
				else
					drupal_set_title(t('Edit user'));

				if ($account->uid != 1)
					$form = pwcs_user_registration($edit,$account);

				return $form;
			//break;

		case 'insert':				 

				if ((user_access('is siteadmin')) && ($user->uid > 1)) {
					$edit['status'] = 1;
					user_save($account,$edit);
				}
			break;

		case 'update':		

			if(user_access('is siteadmin') && isset($edit['custom_roles']))
			{
				$edit['roles'][$edit['custom_roles']] = $edit['custom_roles'];
				unset($edit['custom_roles']);							
			} 
			break;	 

		case 'validate':

//			if(stristr($edit['name'], ' ') != FALSE)
//				form_set_error( 'name', 'Space not allowed in Username');
//
//			else if(strlen($edit['name']) < 4)
//				form_set_error( 'name', 'Username cant be less then 3 characters');
//
//			if(	(isset($edit['password']) && ( empty($edit['password']) || empty($edit['conf_password']) )) || isset($edit['pass']) && ( empty($edit['pass']) || empty($edit['conf_password']) ) )
//				form_set_error( 'password', 'Password required');
//			else
//			{
//				if((isset($edit['password']) && strlen($edit['password']) < 6) ||(isset($edit['pass']) && strlen($edit['pass']) < 6))
//					form_set_error( 'password', 'Password cant be less then 5 characters');
//	
//				if( ( isset($edit['password']) && ($edit['conf_password'] != $edit['password'])) || ( isset($edit['pass']) && ($edit['conf_password'] != $edit['pass']) ) )
//				{
//					form_set_error( 'conf_password', 'Password and confirm password not matched');
//					form_set_error( 'password', '');
//				}
//			}

		break;

	}
}

/**
 * @return 
 * @param $edit Object
 * Added by Rasel Kabir
 */
function pwcs_user_registration($edit=null, $account=null)
{		
	global $user;
	$form = array();
	$weight = 20;
	$input_size = 35;
	
	if(user_access('is siteadmin'))
	{ 
		$form['account']['merchant_enc_id'] = array(
			'#type' => 'textfield',
			'#title' =>	t('Merchant Id'),
			'#size' => $input_size,
			'#required' => TRUE,		
			'#default_value' => $edit['merchant_enc_id'],
			'#weight' => $weight++
		);
		
		$form['account']['site_api_key'] = array(
			'#type' => 'textfield',
			'#title' =>	t('Site Api Key'),
			'#size' => $input_size,
			'#required' => TRUE,		
			'#default_value' => $edit['site_api_key'],
			'#weight' => $weight++
		);
	
	}

	return $form ;
}

/**
 * User profile output
 * Added by Rasel Kabir
 */
function pwcs_user_profile($account=NULL) {
	
	$output = '';
	if ($account == NULL) { global $user; $account = $user; }
	
	drupal_set_title(t('Merchant Information'));

	$output .= '<div class="order_preview_customers_info" style="padding:0 10px; margin-bottom: 20px;">';
	$output .= '<div style="border-bottom:1px solid #ddd; padding:5px 0px;"><span style="font-weight:bold;">'.t('User name').': </span> <span>'.$account->name.'</span></div>';
	$output .= '<div style="border-bottom:1px solid #ddd; padding:5px 0px;"><span style="font-weight:bold;">'.t('Email').': </span> <span>'.$account->mail.'</span></div>';
	$output .= '<div style="border-bottom:1px solid #ddd; padding:5px 0px;"><span style="font-weight:bold;">'.t('Merchant Id').': </span> <span>'.$account->merchant_enc_id.'</span></div>';
	$output .= '</div>';
	
	return $output;
}
