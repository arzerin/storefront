<?php
/* $Id: pwcs_massupload.php,v 1.0 2011/01/03 A.R. ZERIUN Exp $ */

function pwcs_siteadmin_massupload($form_state) 
{

	$form = array('#tree' => TRUE);
 	$form['#attributes'] = array('enctype' => "multipart/form-data");
	$form['zip_file_name'] = array(
		'#type' => 'file',
		'#title' => t('Attach new file'), 
		'#size' => 40,
		'#title' => t('Upload any ZIP file(zipped with many images) only '), 		
	);
	$form['button'] = array(
		'#type' => 'submit',
		'#default_value' => 'Upload'
	);	
	return $form;
}
function pwcs_siteadmin_massupload_submit($form, $form_state)
{
	include_once(drupal_get_path('module', 'imce').'/inc/imce.page.inc');

	$upload_status_yes = '<div class="pwcs_quickview_yes"></div>';
	$upload_status_no = '<div class="pwcs_quickview_no"></div>';

	$output = '';
	// Basics
	$header = array(
					array('data' => t('Uploaded'), 'width'=>'5%'), 
					array('data' => t('File Name'), 'width'=>'50%'), 
					array('data' => t('File Type'), 'width'=>'20%')
					);
	$rows = array();
	
	
	$allowed_file_type = array("image/jpeg", "image/gif", "image/png", "image/x-ms-bmp"); 
  	$validators = array();
    $file = file_save_upload('zip_file_name', $validators, realpath(file_directory_path().'/'),FILE_EXISTS_REPLACE);
    
    
	if ($file->filemime != 'application/zip')
	{
		unlink(file_directory_path().'/'.$file->filename);
		return ;
	}    

	@db_query('DELETE FROM {files} WHERE filename="%s"',$file->filename);
	
	$file_upload_summary = array();
	
	$zip = zip_open(realpath(file_directory_path().'/'.$file->filename));
	
	if (!is_resource($zip)) 
	{
      drupal_set_message(t("Error for $file->filename"), 'error');
      unlink(file_directory_path().'/'.$file->filename);
      return;
    }	
	
 	if ($zip) 
 	{
 	  //echo "<br/>Dump here: ";
 	  $filewritten=0;
 	  $filecancelled=0;
      while ($zip_entry = zip_read($zip))
      {
        //echo "<br/>Dumpi: ";
 		//echo "Name:               " . zip_entry_name($zip_entry) . "\n";
        //echo "Actual Filesize:    " . zip_entry_filesize($zip_entry) . "\n";
        //echo "Compressed Size:    " . zip_entry_compressedsize($zip_entry) . "\n";
        //echo "Compression Method: " . zip_entry_compressionmethod($zip_entry) . "\n";

        			
        $filename = basename(zip_entry_name($zip_entry));
        //if (!is_file($filename))
        // echo "<br/>Here FP: ".is_file(realpath(file_directory_path()).'/imagelibrary/'.$zip_dir.basename($filename));
        $ext = drupal_strtolower(array_pop(explode('.', $filename)));

          // check file extension - this should be added on next release.
		  $fp = fopen(file_directory_path().'/imagelibrary/'.$zip_dir.basename($filename), "w+");
		  //echo "<br/>Here FP: ".$ext;
          if (zip_entry_open($zip, $zip_entry, "r")) {
              $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
              zip_entry_close($zip_entry);
          }
          fwrite($fp, $buf);
          fclose($fp);
          $file_obj = pwcs_save_file_files(file_directory_path().'/imagelibrary/'.$zip_dir.basename($filename));
          
          
          if (!in_array($file_obj->filemime, $allowed_file_type))
          {
			$file_upload_summary[] = array( FILENAME => $filename, 
                      					  TYPE => $file_obj->filemime,
                      					  UPLOADED => 'NO' 
                    					);
			 $filecancelled++;                    					
			 
			 
			 if ($ext==$filename) 
			 	$rows[] = array($upload_status_no, $filename, "Folder"); 
			 else if ($file_obj->filemime=='application/octet-stream') 
			 	$rows[] = array($upload_status_no, $filename, "Folder / Not a Valid Image File"); 	 
			 else if ($file_obj->filemime=='application/x-httpd-php') 
			 	$rows[] = array($upload_status_no, $filename, "PHP Script File"); 	 
			 else
			 	$rows[] = array($upload_status_no, $filename, $file_obj->filemime);                    					
			 	
          }            					
          else
          {
 			$file_upload_summary[] = array( FILENAME => $filename, 
                      					  TYPE => $file_obj->filemime,
                      					  UPLOADED => 'YES' 
                    					);
			$filewritten++;                    					      
			$rows[] = array($upload_status_yes, $filename, $file_obj->filemime);                             					                					
          }            					
	                    					
          if (!in_array($file_obj->filemime, $allowed_file_type)){
          	@unlink(realpath(file_directory_path()).'/imagelibrary/'.$zip_dir.basename($filename));
      	  }else{
      	  	imce_file_register($file_obj);
      	  /*	imagefield_create_admin_thumb(realpath(file_directory_path()).'/imagelibrary/'.$zip_dir.basename($filename),realpath(file_directory_path()).'/imagelibrary/'.$zip_dir.basename($filename).'_thumb');
      	  	*/
      	  }
      	  
      }
    zip_close($zip);
    }
    unlink(file_directory_path().'/'.$file->filename);
	@db_query('DELETE FROM {imce_files} WHERE filemime="application/zip"');
    

	$output .= theme('table', $header, $rows, array('width'=>'100%'));
	
    drupal_set_message(t('Total <b>'.$filewritten.'</b> Image(s) has been Uploaded Successfully.'), 'status');
	drupal_set_message($output, 'status');
	drupal_goto('siteadmin/massupload');	    
}
function pwcs_preview($path1,$path2=NULL)
{
	global $base_url;
	if (!empty($path2))
	$target_url =  $base_url.'/'.$path1.'/'.$path2;
	else
	$target_url =  $base_url.'/'.$path1;

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
	echo $response;	
}