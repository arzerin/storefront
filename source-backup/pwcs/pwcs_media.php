<?php
/* $Id: pwcs_media.php,v 1.0 2011/02/10 Rasel Kabir Shuvro Exp $ */

/**
 * Media Manager for Image
 * Added By Rasel Kabir 
 */
function pwcs_imce_page(){
	$options = array(); 
	return '<iframe src="' . url('imce', $options) . '" frameborder="0" style="border: none; width: 99%; height: 600px" class="imce-frame"></iframe>';
}

/**
 * Video Manager Page
 * Added By Rasel Kabir
 */
function pwcs_video_manager($filtered = NULL){

	$output = '';
	$output .= '<h2>'.t('Videos').'</h2>';
	
	$output .= '<div class="messages">';
	$output .= t('Goto').' '.l('Display Settings','siteadmin/sitesettings').' '.t('to choose icons and text for video links');
	$output .= '</div>';

	//$output .= '<div style="text-align: right;">'.l(t('Add New Video'),'siteadmin/media/video_operations/add', array('attributes' => array('class'=>'pwcsaddlink button', 'title'=>''))).'</div>';

	if($filtered != 'filtered') unset($_SESSION['pwcs_siteadmin_videos_filter']);
	$filter = array();
	if(isset($_SESSION['pwcs_siteadmin_videos_filter'])){
		$filter = $_SESSION['pwcs_siteadmin_videos_filter'];
	}
	$output .= drupal_get_form ('pwcs_siteadmin_videos_filter_form', $filter);
	$output .= pwcs_media_video_list($filter);
	return $output ;
}

/**
 * 
 * 
 */
function pwcs_video_manager_operations($op = 'add', $video_id=NULL){

	switch ($op)
	{
		case 'add':
			drupal_set_title(t('Add New Video'));
			return drupal_get_form ('pwcs_video_manager_addnew_form');
			//break;

		case 'edit':
			drupal_set_title(t('Edit Video'));
			return drupal_get_form ('pwcs_video_manager_addnew_form', $video_id);
			//break;

		case 'delete':
			pwcs_video_delete($video_id);
			drupal_goto('siteadmin/media/videos');
			//return '';
			//break;

		case 'formaction':
			if(!empty($_POST['video_id'])) {
				if(isset($_POST['delete-all'])) {
					$deleted_video_id = array();
					$not_deleted_video_id = array();
					foreach($_POST['video_id'] as $video_id){
						$return_array = pwcs_video_delete($video_id);
						if($return_array['status'] == 1) $deleted_video_id[] = $video_id;
						else $not_deleted_video_id[] = $video_id;
					}
					if(!empty($deleted_video_id))
						drupal_set_message(t('Selected videos ('.implode(' , ',$deleted_video_id).') are deleted!'));
					if(!empty($not_deleted_video_id))
						drupal_set_message(t('Selected videos ('.implode(' , ',$not_deleted_video_id).') cant be deleted!'),'error');
				}
			} else {
				drupal_set_message(t('No item has been selected!'),'error');
			}
			drupal_goto('siteadmin/media/videos');
			//return '';
			//break;
	}

}



/**
 * Implementation of siteadmin videos filter form
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_videos_filter_form($form_info = array(), $filter = NULL) {

	$weight=0;
	$form['#prefix'] = '<div id="pwcs_videos_list_filter" class="formitem_inline">';
	$form['#suffix'] = '</div>';
	
	//Title	
	$form['video_title'] = array(
		'#title' => t('Search Title'),
		'#type' => 'textfield',
		'#default_value' => $filter['video_title'],
		'#size' => 40,
		'#weight' => $weight++,
	);

	$form['filter'] = array(
		'#type' => 'submit',
		'#weight' => $weight++,
		'#default_value' => t('Search')
	);

	return $form;
}

/**
 * Siteadmin Videos Filter form submit
 * Added by Rasel Kabir
 */
function pwcs_siteadmin_videos_filter_form_submit($form_id, $form_values)
{
	global $user;
	if($form_values[submitted] == 1) {
		if($form_values['clicked_button']['#parents'][0] == 'filter') {
			$_SESSION['pwcs_siteadmin_videos_filter'] = $form_values['values'];
		} else {
			unset($_SESSION['pwcs_siteadmin_videos_filter']);
		}
	}
	$_REQUEST[destination] = 'siteadmin/media/videos/filtered';
}

/**
 * List of all videos (siteadmin)
 * Added by Rasel Kabir
 */
function pwcs_media_video_list($filter = array())
{
	$pagetags = array(0=>'First', 1=>'Previous', 3=>'Next', 4=>'Last');
	$pagelimit = isset($filter['display_per_page'])? $filter['display_per_page'] : 15;
	$pageelement = 0;
	$pageparameters = array();
	$pagequantity = 5;

	$output	= '';
	$header = array(
		'<input type="checkbox" id="videos-check-all" class="form-checkbox checkbox_checkall" title="Select all rows in this table">',
		array('data' => t('Preview'), 'width'=>90),
		array('data' => t('Title'), 'field' => 'pmv.video_title', 'width'=>250),
		array('data' => t('URL'), 'field' => 'pmv.video_url'),
		array('data' => t('Action'), 'field' => 'pmv.modified', 'width'=>90),
	);

	$sql  = "SELECT * FROM {pwcs_media_video} pmv";
	$sql .= " WHERE 1=1";
	if(!empty($filter)) {
		if($filter['video_title'])
			$sql .= " AND pmv.video_title LIKE '%".$filter['video_title']."%'";
	}
	//$sql .= " ORDER BY pmv.modified DESC";

	$tablesort = tablesort_sql($header);
	$results = pager_query($sql . $tablesort, $pagelimit);
	
	$amount = pwcs_result_count($sql);
	$output .= '<div class="pwcs_table">';
	$rows = array();
	while($result = db_fetch_object($results))
	{
		$row = array();
		$node = node_load($result->nid);
		$row[] = '<input type="checkbox" id="video-'.$result->video_id.'" class="form-checkbox" name="video_id[]" value="'.$result->video_id.'">';
		
		$video_thumbnail = isset($result->video_thumb) ? $result->video_thumb : PWCS_VIDEO_DEFAULT_THUMBNAIL;
		$preview = '<a href="'.$result->video_url.'" rel="lightvideo['.$result->video_id.'|width:400px;]">';
		$preview .= theme('image', $video_thumbnail, '', t('Click Here To View The Video'), array('width'=>'auto'), FALSE);
		$preview .= '</a>';
		$row[] = $preview;

		$row[] = array('data' => '<b>'.$result->video_title.'</b><br/>'.$result->video_description);
		$row[] = array('data' => $result->video_url);

		// Action
		$row[] = l(t('Edit'),'siteadmin/media/video_operations/edit/'.$result->video_id, array('html'=>true, 'query'=>'destination=siteadmin/media/videos', 'attributes'=>array('class'=>'pwcseditlink','title'=>'')))
				.' | '
				.l(t('Delete'),'siteadmin/media/video_operations/delete/'.$result->video_id, array('html'=>true, 'query'=>'destination=siteadmin/media/videos', 'attributes'=>array('class'=>'pwcsdeletelink','title'=>'')));
		$rows[] = $row;
	}

	$output .= '<form id="siteadmin-video-operations-form" method="post" accept-charset="UTF-8" action="'.base_path().'siteadmin/media/video_operations/formaction">';
	$output .= '<div style="float:left; margin-right:10px; width:auto;"><input type="submit" class="form-submit" value="'.t('Delete All').'" id="delete-all" name="delete-all"></div>';
	$output .= '<div style="float:left; width:auto;">'.l(t('Add New Video'),'siteadmin/media/video_operations/add', array('attributes' => array('class'=>'pwcsaddlink button', 'title'=>''))).'</div>';
	$output .= '<div class="pwcs_video_list_count">'.t('Total <b>@amount</b> video(s) found',  array('@amount' => $amount)).'</div>';
	$output .= '<div style="clear:both;"></div>';
	$output .= theme('table', $header, $rows, array('width'=>'100%'));
	//$output .= '<input type="submit" class="form-submit" value="'.t('Delete All').'" id="delete-all" name="delete">';
	$output .= '</form>';

	$output .= theme_pager($pagetags, $pagelimit, $pageelement, $pageparameters, $pagequantity);
	$output .= '</div>';

	return $output;
}

/**
 * Implementation of Video form
 * Added by Rasel Kabir
 */
function pwcs_video_manager_addnew_form($form_info = array(), $video_id = NULL) {

	$weight=0;

	$media_video = pwcs_video_manager_video_array($video_id);
	if ($video_id!=NULL) {
		$form['video_id'] = array(
			'#type' => 'hidden',
			'#value' => $video_id,
			'#weight' => $weight++,
		);
	}
	$form['video_title'] = array(
		'#type' => 'textfield',
		'#title' => t('Video Title'),
		'#description' => 'Title will be shown under the video.',
		'#default_value' => isset($media_video['video_title']) ? $media_video['video_title'] : '',
		'#weight' => $weight++,
	);
	$form['video_url'] = array(
		'#type' => 'textarea',
		'#title' => t('Video URL'),
		'#description' => 'The links need to be in one of the following formats:<br>
							<!--Google video: http://video.google.com/videoplay?docid=videoid<br>-->
							YouTube: youtube.com/watch?v=videoid<br>
							Metacafe: metacafe.com/watch/videoid/.....<br>
							Live Video : livevideo.com/video/videoid/...<br>
							<!--MySpace : myspace.com/index.cfm?fuseaction=vids.individual&videoid=videoid<br>-->
							<!--spike.com (formerly ifilm.com): spike.com/video/videoid<br>-->
							',
		'#default_value' => isset($media_video['video_url']) ? $media_video['video_url'] : '',
		'#rows' => '3',
		'#weight' => $weight++,
	);
	$form['video_description'] = array(
		'#type' => 'textarea',
		'#title' => t('Video Description'),
		'#description' => 'More details about your video.',
		'#default_value' => isset($media_video['video_description']) ? $media_video['video_description'] : '',
		'#weight' => $weight++,
	);

	$form['button_set']['#prefix']='<div style="margin:5px;">';
	$form['button_set']['#suffix']='<div style="clear:both;"></div></div>';
	$form['button_set']['#weight']=$weight++;
	
	$button_title = ($video_id==NULL)?t('Add Video'):t('Edit Video');
	$form['button_set']['button'] = array(
		'#type' => 'submit',
		'#default_value' => $button_title,
		'#weight' => $weight++,
		'#prefix'=>'<div style="float:left; width:auto; margin-right:10px;">',
		'#suffix'=>'</div>'
	);

	$back_button .= '<div style="float:left; width:auto;">'.l(t('Cancel'),'siteadmin/media/videos', array('attributes' => array('class'=>'button', 'title'=>''))).'</div>';
	$form['button_set']['back_button'] = array(
		'#type' => 'markup',
		'#value' => $back_button,
		'#weight' => $weight++,
	);
	return $form;
}


/**
 * Video form submit
 * Added by Rasel Kabir
 */
function pwcs_video_manager_addnew_form_submit($form_id, $form_values)
{
	global $user;

	if($form_values[submitted] == 1) {

		// Update Video
		if (isset ($form_values['clicked_button']['#post']['video_id']) &&  ($form_values['clicked_button']['#post']['video_id']!= NULL)) {

			$video['video_id']= $form_values['clicked_button']['#post']['video_id'];
			$video['video_title']= $form_values['clicked_button']['#post']['video_title'];
			$video['video_url']= $form_values['clicked_button']['#post']['video_url'];
			$video['video_description']= $form_values['clicked_button']['#post']['video_description'];

			if (db_result(db_query("SELECT `video_title` FROM {pwcs_media_video} WHERE `video_title` = '%s' AND `video_id` != %d", $video['video_title'], $video['video_id'])) == $video['video_title']) {
				drupal_set_message(t('Sorry').'! '.$video['video_title'].' '.t('is already added'), 'error');
			} else {
				$return_array = pwcs_video_edit($video);
				if($return_array['status'] == 1)
					drupal_set_message($return_array['message']);
				else 
					drupal_set_message($return_array['message'], 'error');
			}
		// Add New Video
		} else {
			$video['video_title']= $form_values['values']['video_title'];
			$video['video_url']= $form_values['values']['video_url'];
			$video['video_description']= $form_values['values']['video_description'];
			if (db_result(db_query("SELECT `video_title` FROM {pwcs_media_video} WHERE `video_title` = '%s'", $video['video_title'])) == $video['video_title']) {
				drupal_set_message(t('This video is already in your video'), 'error');
			} else {
				$return_array = pwcs_video_add($video);
				if($return_array['status'] == 1)
					drupal_set_message($return_array['message']);
				else 
					drupal_set_message($return_array['message'], 'error');
			}
		}
		if (user_access('is siteadmin')){
			$_REQUEST[destination] = 'siteadmin/media/videos';
		}
	}
	else {
		drupal_set_message(t('Opps! unfortunate error'), 'error');
	}
}

/**
 * Implementation of pwcs_video_add()
 * Added by Rasel Kabir
 */
function pwcs_video_add($video) {
	$result = db_query(
		"INSERT INTO {pwcs_media_video} (`video_title`, `video_url`,`video_description`,`created`, `modified`) VALUES ('%s', '%s', '%s', %d, %d)",
		$video['video_title'], $video['video_url'], $video['video_description'], time(), time()
	);
	if ($result == 1) {
		return array('status'=>1, 'message'=>t('New video added!'), 'insert_id'=>db_last_insert_id('pwcs_media_video', 'video_id'));
	} else {
		drupal_set_message(t('Video Cannot be Added!'), 'error');
		return array('status'=>0, 'message'=>t('New video cant be added!'));
	}
}

/**
 * Implementation of pwcs_video_edit()
 * Added by Rasel Kabir
 */
function pwcs_video_edit($video) {
	$result = db_query("UPDATE {pwcs_media_video}
		SET `video_title`='%s', `video_url`='%s', `video_description`='%s', `modified`=%d WHERE video_id = %d",
		$video['video_title'], $video['video_url'], $video['video_description'], time(), $video['video_id']
	);
	if ($result == 1) {
		return array('status'=>1, 'message'=>t('Video updated!'), 'video_id'=>$video['video_id']);
	} else {
		return array('status'=>0, 'message'=>t('Video cant be updated!'));
	}
}

/**
 * Implementation of pwcs_video_delete()
 * Added by Rasel Kabir
 */
function pwcs_video_delete($video_id) {
	$result = db_query("DELETE FROM {pwcs_media_video} WHERE video_id = %d", $video_id);
	if ($result == 1) {
		return array('status'=>1, 'message'=>t('Video deleted!'));
	} else {
		return array('status'=>0, 'message'=>t('Video cant be deleted!'));
	}
}


/**
 * Get All values of a video_id in an array
 * Added By Rasel Kabir
 */
function pwcs_video_manager_video_array($video_id = NULL) {

	if ((isset($video_id)) && ($video_id>0)) {
		$results = db_query("SELECT * FROM {pwcs_media_video} WHERE `video_id` = %d", $video_id);
		while($result = db_fetch_object($results)) {
			$media_video = (array) $result;
		}
		return $media_video;
	} else {
		return NULL;
	}
}

/**
 * Get an array of all videos for options
 * Added By Rasel Kabir
 */
function pwcs_get_videos_for_options(){
	$results = db_query("SELECT * FROM {pwcs_media_video} WHERE video_status = 1");
	while($result = db_fetch_object($results)) {
		$video_play_icon = variable_get('pwcs_product_video_icon', '');
		if($video_play_icon == '') $video_play_icon = PWCS_MODULE_IMAGE.'video_play_icon.png';
		$preview = '<a href="'.$result->video_url.'" rel="lightvideo['.$result->video_id.'|width:400px;]" title="'.t('Click Here To Preview The Video').'">';
		$preview .= theme('image', $video_play_icon, '', '', array('width'=>'auto', 'style'=>'vertical-align: text-bottom;'), FALSE);
		$preview .= '</a>';
		$media_video[$result->video_id] = $preview.' '.$result->video_title.' (<em>'.$result->video_url.'</em>)';
	}
	return $media_video;
}

