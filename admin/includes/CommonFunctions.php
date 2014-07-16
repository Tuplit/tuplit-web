<?php
ini_set('default_encoding','utf-8');
function admin_login_check()
{
    if(!isset($_SESSION['tuplit_admin_user_name'])){
		header('location:index.php');
		die();
    }
}

function encode($id) {
	return dechex(($id + 5) * 101);
}
function decode($id) {
	return hexdec($id)/101-5;
}

function merchant_reset_password_check()
{	
	$check = array('UID','Type');
	$valid = $id = $type = 0;
    if(!empty($_GET)){
		print_r($_GET);
		foreach($_GET as $key=>$val) {
			if(in_array($key,$check)) {
				if(empty($_GET[$key])) {
					$valid = 1;
					break;
				}
				else {	
					$valid = 0;	
					if($key == 'UID')
						$id = 1;
					if($key == 'Type')
						$type = 1;
				}
			}
			else {
				$valid = 1;
			}
		}
    }
	else 
		$valid = 1;
	echo $valid.'  '.$id.' '.$type;
	if($valid == 1 || $id == 0 || $type == 0) {
		header('location:404.php');
		die();
	}
}


function user_login_check(){
	if(!isset($_SESSION["tuplit_ses_user_id"]) || $_SESSION["tuplit_ses_user_id"] == '')
		{ header('location: home'); die();}
}
function getCurrPage()
{
	$page = substr(strrchr($_SERVER['REQUEST_URI'], '/'), 1);
	$page=explode('?',$page);
	if(is_array($page))
		$page=$page[0];
	return $page;
}
	
function displayText($text, $length) {
	if (strlen($text) > $length) return strip_tags(substr($text, 0, $length)).' ...'; else return $text;
}

/********************************************************
  * Function Name: escapeSpecialCharacters
  * Purpose: Escapes special characters in a string for use in an SQL statement
  * $data   - array or text
  *******************************************************/
function escapeSpecialCharacters($data)
{
	//$data = trim($data);
	if (get_magic_quotes_gpc())
		return $data; //No need to escape data if magic quotes is turned on
	$data = is_array($data) ? array_map('escapeSpecialCharacters', $data) : mysql_real_escape_string($data);
    return $data;
}
/********************************************************
  * Function Name: unEscapeSpecialCharacters
  * Purpose: UnEscapes special characters in a string for use in an SQL statement
  * $data   - array or text
  *******************************************************/
function unEscapeSpecialCharacters($data)
{
	$data = is_array($data) ? array_map('unEscapeSpecialCharacters', $data) :stripslashes($data);
    return $data;
}
function sendMail($mailContentArray,$type)
{
	error_reporting(0);
	if(is_array($mailContentArray))
	{
		$heardFrom		= 	'';
		$message		=	'';
		$from 	  		=   $mailContentArray['from'];
		$to   		    =   $mailContentArray['toemail'];
		$subject		= 	$mailContentArray['subject'];
		$sitelinkpath	=	ADMIN_SITE_PATH.'/webresources/mail_content/';		
		$filename       = 	ADMIN_ABS_PATH.'/webresources/mail_content/'.$mailContentArray['fileName'];
		$mailData 		= 	file_get_contents($filename);
		$filearray 		= 	explode('/',$mailContentArray['fileName']);
		$typearray 		= 	end($filearray);
		$typeextn 		= 	explode('.',$typearray);
		$sitepath		=	SITE_PATH;
		switch($type)
		{
			case 1:
				//User and merchant forgot password
				$mailData 			=	str_replace('{NAME}', $mailContentArray['name'], $mailData);
				$mailData 			=	str_replace('{LINK}',  $mailContentArray['link'], $mailData);
				$mailData 			=	str_replace('{SITEPATH}',  $sitepath, $mailData);
				$mailData 			=	str_replace('{SITE_MAIL_PATH}',  $sitelinkpath, $mailData);
				break;
			case 2:
				//User Registeration 
				$mailData 			=	str_replace('{NAME}', $mailContentArray['name'], $mailData);
				//$mailData 			=	str_replace('{USERNAME}', $mailContentArray['email'], $mailData);
				$mailData 			=	str_replace('{PASSWORD}',  $mailContentArray['password'], $mailData);
				$mailData 			=	str_replace('{EMAIL}',  $mailContentArray['email'], $mailData);
				$mailData 			=	str_replace('{SITE_MAIL_PATH}',  $sitelinkpath, $mailData);
				break;
			case 3:
				//Admin forgot password
				$mailData 			=	str_replace('{NAME}', $mailContentArray['name'], $mailData);
				$mailData			=	str_replace('{EMAIL}', $mailContentArray['toemail'], $mailData);
				$mailData 			=	str_replace('{PASSWORD}',  $mailContentArray['password'], $mailData);
				$mailData 			=	str_replace('{SITE_MAIL_PATH}',  $sitelinkpath, $mailData);
				break;
			case 4:
				//Merchant forgot password
				$mailData 			=	str_replace('{NAME}', $mailContentArray['name'], $mailData);
				//$mailData			=	str_replace('{USERNAME}', $mailContentArray['email'], $mailData);
				$mailData 			=	str_replace('{SITEPATH}',  $sitepath, $mailData);
				$mailData 			=	str_replace('{PASSWORD}',  $mailContentArray['password'], $mailData);
				$mailData 			=	str_replace('{SITE_MAIL_PATH}',  $sitelinkpath, $mailData);
				$mailData 			=	str_replace('{LINK}', $mailContentArray['link'], $mailData);
				break;
			case 5:
				//Merchant Registeration 
				$mailData 			=	str_replace('{NAME}', $mailContentArray['name'], $mailData);
				$mailData 			=	str_replace('{EMAIL}', $mailContentArray['toemail'], $mailData);
				$mailData 			=	str_replace('{PASSWORD}',  $mailContentArray['password'], $mailData);
				$mailData 			=	str_replace('{SITE_MAIL_PATH}',  $sitelinkpath, $mailData);
				break;
			case 6:
				//Admin Merchant Registeration
				$mailData			=	str_replace('{MERCHANT_EMAIL}', $mailContentArray['merchantEmail'], $mailData);
				$mailData 			=	str_replace('{MERCHANT_NAME}',  $mailContentArray['merchantName'], $mailData);
				$mailData 			=	str_replace('{SITEPATH}',  $sitepath, $mailData);
				$mailData 			=	str_replace('{SITE_MAIL_PATH}',  $sitelinkpath, $mailData);
				$mailData 			=	str_replace('{LINK}', $mailContentArray['link'], $mailData);
				break;
			case 7:
				//Merchant Approval 
				$mailData 			=	str_replace('{NAME}', $mailContentArray['name'], $mailData);				
				$mailData 			=	str_replace('{SITE_MAIL_PATH}',  $sitelinkpath, $mailData);
				break;
			case 8:
				//new order mail to user by merchant
				$mailData 			=	str_replace('{NAME}', $mailContentArray['name'], $mailData);				
				$mailData 			=	str_replace('{SITE_MAIL_PATH}',  $sitelinkpath, $mailData);
				$mailData 			=	str_replace('{NAME1}',  $mailContentArray['name1'], $mailData);
				$mailData 			=	str_replace('{ADDRESS}',  $mailContentArray['address'], $mailData);
				$mailData 			=	str_replace('{TOTAL}',  $mailContentArray['TotalPrice'], $mailData);
				
				$mailData 			=	str_replace('{TRANSACTIONID}',  $mailContentArray['TransactionId'], $mailData);
				$mailData 			=	str_replace('{ORDERID}',  $mailContentArray['orderId'], $mailData);
				$productData		=	'';
				foreach($mailContentArray['CartDetails'] as $val) {
				//echo "<pre>"; print_r($val);die();
					$productData	.=	'<tr>
											<td align="left" style="color:#010101;font-size:20px;font-family:Calibri;"><span style="color:#6b5f5f;font-size:12px;font-family:Calibri;">'.$val['ProductsQuantity'].'x</span>&nbsp;&nbsp;<b>&nbsp;&nbsp;'.$val['ItemName'].'</b></td> 
											<td align="right" style="color:#010101;font-size:20px;font-family:Calibri;"><b>$'.number_format((float)($val['DiscountPrice'] * $val['ProductsQuantity']), 2, '.', '').'&nbsp;&nbsp;</b></td></tr>';
				}
				$mailData 			=	str_replace('{PRODUCTSLIST}',  $productData, $mailData);
				$mailData 			=	str_replace('{CONTENT}',  $mailContentArray['content'], $mailData);
				$mailData 			=	str_replace('{BYNAME}',  $mailContentArray['byname'], $mailData);
				break;			
		}
		$mail_image 	= 	ADMIN_SITE_PATH.'/webresources/mail_content/';
		$mailData 		=	str_replace('{SITE_PATH}',$sitelinkpath ,$mailData);
		$mailData		=   str_replace('{YEAR}', date('Y'), $mailData);
		$headers  		= 	"MIME-Version: 1.0\n";
		$headers 		.= 	"Content-Transfer-Encoding: 8bit\n";
		$headers        .= 	"From: $from\r\n";
		$headers 		.= 	"Content-type: text/html\r\n";
		if ($_SERVER['HTTP_HOST'] == '172.21.4.104'){
			if($_SERVER['REMOTE_ADDR'] == '172.21.4.215' || $_SERVER['REMOTE_ADDR'] == '172.21.4.81'){
				echo $mailData;
				//die();
			}			
			//$sendmail = sendMailSes($from,$to,$subject,$mailData,'');
		}
		else {
			//echo $mail_data;
			mail($to,$subject,$mailData,$headers);
			//$sendmail = mailThroughAmazon($from,$to,$subject,$mailData,'','','','','','','','');
			//$sendmail = sendMailSes($from,$to,$subject,$mailData,'');
		}
	}
}

function sendMailSes($from,$to,$subject,$html_message,$text_msg=''){
	
	require_once('sdk.class.php');// Include the SDK
	
	$ses = new AmazonSES();//// Instantiate the  class
	//$region = $ses->set_region(REGION_US_W2);
	$result = $ses->send_email($from, array(
		        'ToAddresses' => array($to),
		    	), 
				array(
				        // Subject is required
				        'Subject' => array(
				            // Data is required
				            'Data' => $subject,
				            'Charset' => 'utf8',
				        ),
				        // Body is required
				        'Body' => array(
				            'Html' => array(
				                // Data is required
				                'Data' => $html_message,
				                'Charset' => 'utf8',
				            ),
				        ),
		    	)
			);
	//echo'<pre>';print_r($result);echo'</pre>';
}

function mailThroughAmazon($from,$to,$subject,$html_message='',$text_msg='',$file_text_path='',$file_html_path='',$http_url='',$replyto='',$cc='',$bcc='',$return_path=''){

	//require_once('ses.php');

	$ses = new SimpleEmailService('', '');//tuplit -server
		//$ses->verifyEmailAddress($to);

	$m = new SimpleEmailServiceMessage();
	$m->addTo($to);
	$m->setFrom($from);
	$m->setSubject($subject);
	if($text_msg != '' || $html_message != '') {
		$m->setMessageFromString($text_msg,$html_message);
	}
	else if($file_text_path != '' || $file_html_path != '') {
		$m->setMessageFromFile($file_text_path,$file_html_path);
	}
	else if($http_url != '') {
		$m->setMessageFromURL($http_url);
	}
	else {
		return 'Attempt to send mail with out message';
	}
	if($cc != '')
		$m->addCC($cc);
	if($bcc != '')
		$m->addBCC($bcc);
	if($return_path != '')
		$m->setReturnPath($return_path);
	if($replyto != '')
		$m->addReplyTo($replyto);
	//echo '<pre>';print_r($m);echo '</pre>';
	return $ses->sendEmail($m);
}

function destroyPagingControlsVariables() { //clear paging session variables
    unset($_SESSION['orderby']);
    unset($_SESSION['ordertype']);
    unset($_SESSION['curpage']);
    unset($_SESSION['perpage']);
    unset($_SESSION['paging']);
}
//set paging session variables
function setPagingControlValues($default_field,$per_page) {
	if(isset($_POST['per_page']))
		$_SESSION['perpage'] = $_POST['per_page'];
	elseif(!isset($_SESSION['perpage']))
		$_SESSION['perpage'] = $per_page;

	if(isset($_POST['cur_page']))
		$_SESSION['curpage'] = $_POST['cur_page'];
	elseif(!isset($_SESSION['curpage']))
		$_SESSION['curpage'] = 1;

	if(isset($_POST['order_by']))
		$_SESSION['orderby'] = $_POST['order_by'];
	elseif(!isset($_SESSION['orderby']))
		$_SESSION['orderby'] = $default_field;

	if(isset($_POST['order_type']))
		$_SESSION['ordertype'] = $_POST['order_type'];
	elseif(!isset($_SESSION['ordertype']))
		$_SESSION['ordertype'] = 'desc';
	if(isset($_POST['paging_change']) && $_POST['paging_change']!='')
         $_SESSION['curpage'] = $_POST['paging_change'];
}

// Displays the sort icons for the column headings
// Paramters => 	column : field in the database that is merged in the ORDER BY clause of the query
//					title  : column name to be displayed on the screen.
// Output	 =>		Returns as a Hyperlink with given column and field.
function SortColumn($column, $title)
{
	/*if($_SERVER['REMOTE_ADDR']=='172.21.4.95') {
		echo '===>'.__LINE__.'===>'.$column;
		echo '===>'.__LINE__.'===>'.$title;
		die();
	}*/
	$sort_type = 'ASC';
	$sort_image = 'fa fa-sort ';
	
	if (($_SESSION['orderby'] == $column) && ($_SESSION['ordertype'] == 'ASC')){  //asc
		$sort_type = 'DESC';
		$sort_image = 'fa fa-sort-alpha-asc ';
	}
	elseif (($_SESSION['orderby'] == $column) && ($_SESSION['ordertype'] == 'DESC')){ //desc
		$sort_type = 'ASC';
		$sort_image = 'fa fa-sort-alpha-desc ';
	}
	
	$alt_title = 'Sort by '.ucfirst(strtolower($title))." ".strtolower($sort_type);
	$sort_link = "<a href=\"#\" onclick=\"javascript:setPagingControlValues('".$column."','".$sort_type."',".$_SESSION['curpage'].");\" alt=\"".$alt_title."\" title=\"".$alt_title."\" >";
	//return $sort_link.''.$title.'</a>&nbsp;'.$sort_link.'</a>';//<img src="'.IMAGE_PATH . $sort_image.'" alt="" border="0">
	return $sort_link.''.$title.'</a>&nbsp;'.$sort_link.'&nbsp;<i class="'. $sort_image.'"></i></a>';
}
/* sort column for icon in title tag*/
function SortColumnIcon($column, $title,$titleName)
{
	/*if($_SERVER['REMOTE_ADDR']=='172.21.4.95') {
		echo '===>'.__LINE__.'===>'.$column;
		echo '===>'.__LINE__.'===>'.$title;
		die();
	}*/
	$sort_type = 'ASC';
	$sort_image = 'fa fa-sort fa-lg';
	
	if (($_SESSION['orderby'] == $column) && ($_SESSION['ordertype'] == 'ASC')){  //asc
		$sort_type = 'DESC';
		$sort_image = 'fa fa-sort-alpha-asc fa-lg';
	}
	elseif (($_SESSION['orderby'] == $column) && ($_SESSION['ordertype'] == 'DESC')){ //desc
		$sort_type = 'ASC';
		$sort_image = 'fa fa-sort-alpha-desc fa-lg';
	}
	
	$alt_title = 'Sort by '.ucfirst(strtolower($titleName))." ".strtolower($sort_type);
	$sort_link = "<a href=\"#\" onclick=\"javascript:setPagingControlValues('".$column."','".$sort_type."',".$_SESSION['curpage'].");\" alt=\"".$alt_title."\" title=\"".$alt_title."\" >";
	//return $sort_link.''.$title.'</a>&nbsp;'.$sort_link.'</a>';//<img src="'.IMAGE_PATH . $sort_image.'" alt="" border="0">
	return $sort_link.''.$title.'</a>&nbsp;'.$sort_link.'&nbsp;<i class="'. $sort_image.'"></i></a>';
}
function SortColumnAjax($column, $title,$functionName)
{
	$sort_type = 'ASC';
	$sort_image = 'no_sort.gif';
	if (($_SESSION['orderby'] == $column) && ($_SESSION['ordertype'] == 'ASC')){  //asc
		$sort_type = 'DESC';
		$sort_image = 'asc.gif';
	}
	elseif (($_SESSION['orderby'] == $column) && ($_SESSION['ordertype'] == 'DESC')){ //desc
		$sort_type = 'ASC';
		$sort_image = 'desc.gif';
	}
	$alt_title = 'Sort by '.ucfirst(strtolower($title))." ".strtolower($sort_type);
	$sort_link = "<a href=\"javascript:void(0);\" onclick=\"javascript:setPagingControlValuesAjax('".$column."','".$sort_type."',".$_SESSION['curpage']."); $functionName \" alt=\"".$alt_title."\" title=\"".$alt_title."\" >";
	//return $sort_link.''.$title.'</a>&nbsp;'.$sort_link.'</a>';//<img src="'.IMAGE_PATH . $sort_image.'" alt="" border="0">
	return $sort_link.''.$title.'</a>&nbsp;'.$sort_link.'<img src="'.ADMIN_IMAGE_PATH . $sort_image.'" alt="" border="0"></a>';
}

// Display paging control
//Input : no. of records and URL
function pagingControlLatest($total,$action='')
{
	//$counter		=	'';
	$per_page 		= $_SESSION['perpage'];
	$page 			= $_SESSION['curpage'];
	$pagination 	= ' <div class="col-sm-9 col-xs-12  mb_clr" align="center">';
	if ($action == '')
		$action = $_SERVER['SCRIPT_NAME'];
	?>
	<form name="paging" id="paging" method="post" action="<?php echo($action);?>"  >
		<input type="Hidden" value="<?php echo($_SESSION['curpage']);?>" name="cur_page" id="cur_page">
		<input type="Hidden" value="<?php echo($_SESSION['orderby']);?>" name="order_by" id="order_by">
		<input type="Hidden" value="<?php echo($_SESSION['ordertype']);?>" name="order_type" id="order_type">
		<?php
		if ($total > $per_page)
		{
        $adjacents = "2";

    	$page = ($page == 0 ? 1 : $page);
    	$start = ($page - 1) * $per_page;

		$firstPage = 1;

		$prev = ($page == 1)?1:$page - 1;

    	$prev = $page - 1;
    	$next = $page + 1;
        $lastpage = ceil($total/$per_page);
    	$lpm1 = $lastpage - 1;

    	if($lastpage > 1)
    	{
    		$pagination .= " <ul class='pagination'>";
                   // $pagination .= "<li class='details'>Page $page of $lastpage</li>";
			if ($page == 1)
			{
				$pagination.= "<li><span><i class='fa fa-angle-double-left fa-lg'></i></span></li>";
				$pagination.= "<li><span><i class='fa fa-angle-left fa-lg'></i></span></li>";
			}
			else
			{
				$pagination.= "<li><a class='' href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$firstPage);\" ><i class='fa fa-angle-double-left fa-lg'></i></a></li>";
				$pagination.= "<li><a class='' href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$prev);\" ><i class='fa fa-angle-left fa-lg'></i></a></li>";
			}

    		if ($lastpage < 7 + ($adjacents * 2))
    		{
    			for ($counter = 1; $counter <= $lastpage; $counter++)
    			{
    				if ($counter == $page)
    					$pagination.= "<li class='active'><span><i>$counter</i></span></li>";
    				else
    					$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter);\">$counter</a></li>";
    			}
    		}
    		elseif($lastpage > 5 + ($adjacents * 2))
    		{
    			if($page < 1 + ($adjacents * 2))
    			{
    				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li class='active'><span><i>$counter</i></span></li>";
    					else
    						$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter);\">$counter</a></li>";
    				}
    				$pagination.= "<li class='dot'><span>...</span></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lpm1);\">$lpm1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lastpage);\">$lastpage</a></li>";
    			}
    			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
    			{
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',1);\">1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',2);\">2</a></li>";
    				$pagination.= "<li class='dot'><span>...</span></li>";
    				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li><span><i>$counter</i></span></li>";
    					else
    						$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter);\">$counter</a></li>";
    				}
    				$pagination.= "<li class='dot'><span>..</span></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lpm1);\" >$lpm1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lastpage);\" >$lastpage</a></li>";
    			}
    			else
    			{
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',1);\" >1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',2);\" >2</a></li>";
    				$pagination.= "<li class='dot'><span>..</span></li>";
    				for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li><span><i>$counter</i></span></li>";
    					else
    						$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter);\" >$counter</a></li>";
    				}
    			}
    		}

    		if ($page < $counter - 1){
    			$pagination.= "<li><a class='' href='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$next);\" ><i class='fa fa-angle-right fa-lg'></i></a></li>";
                $pagination.= "<li><a class='' shref='javascript:void(0);' onclick=\"javascript:setPagingControlValues('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lastpage);\" ><i class='fa fa-angle-double-right fa-lg'></i></a></li>";
    		}else{
    			$pagination.= "<li><span><i class='fa fa-angle-right fa-lg'></i></span></li>";
                $pagination.= "<li><span><i class='fa fa-angle-double-right fa-lg'></i></span></li>";
            }
    		$pagination.= "</ul>";
    	}
	}?>
		<?php echo $pagination; ?>
		 </div>
		<?php $per_page_array =  eval(ADMIN_PER_PAGE_ARRAY);
		if($total > $per_page_array[0]){ ?>
			<div class="col-xs-12  col-sm-3 mb_clr pad">
			<span class="pull-right">
				<select name="per_page" id="per_page" onchange="setPerPage(this.value);">
				<?php foreach($per_page_array as $value){ ?>
					<option value="<?php echo($value);?>" <?php if($per_page == $value) { echo "selected='selected'"; } ?>><?php echo($value);?></option>
				<?php } ?>
				</select>
			</span>
			<span class="pull-right">Per page &nbsp;</span>
			 </div>
		<?php }?>
	</form>
<?php }
// Display paging control
//Input : no. of records and URL
function pagingControlLatestAjax($total,$functionName='')
{
	$per_page 		= $_SESSION['perpage'];
	$page 			= $_SESSION['curpage'];
	$pagination 	= '<br><table cellspacing="0" cellpadding="0" width="100%" border="0" align="center">
	<tr>
	<td align="center" width="90%" ><table cellspacing="0" cellpadding="0" border="0" align="center"><tr><td> ';
	?>
	<form name="pagingAjax" id="pagingAjax" method="post"   >
		<input type="Hidden" value="<?php echo($_SESSION['curpage']);?>" name="cur_page" id="cur_page">
		<input type="Hidden" value="<?php echo($_SESSION['orderby']);?>" name="order_by" id="order_by">
		<input type="Hidden" value="<?php echo($_SESSION['ordertype']);?>" name="order_type" id="order_type">
		<?php
		if ($total > $per_page)
		{
        $adjacents = "2";
    	$page = ($page == 0 ? 1 : $page);
    	$start = ($page - 1) * $per_page;

		$firstPage = 1;

		$prev = ($page == 1)?1:$page - 1;

    	$prev = $page - 1;
    	$next = $page + 1;
        $lastpage = ceil($total/$per_page);
    	$lpm1 = $lastpage - 1;

    	if($lastpage > 1)
    	{
    		$pagination .= "<ul class='pagination'>";
                    $pagination .= "<li class='details'>Page $page of $lastpage</li>";
			if ($page == 1)
			{
				$pagination.= "<li><a class='current'><i class='fa fa-angle-double-left fa-lg'></i></a></li>";
				$pagination.= "<li><a class='current'><i class='fa fa-angle-double-right fa-lg'></i></a></li>";
			}
			else
			{
				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$firstPage); $functionName \" ><<</a></li>";
				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$prev); $functionName \" ><</a></li>";
			}
    		if ($lastpage < 5 + ($adjacents * 2))
    		{
    			for ($counter = 1; $counter <= $lastpage; $counter++)
    			{
    				if ($counter == $page)
    					$pagination.= "<li><a class='current'>$counter</a></li>";
    				else
    					$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter); $functionName \">$counter</a></li>";
    			}
    		}
    		elseif($lastpage > 5 + ($adjacents * 2))
    		{
    			if($page < 1 + ($adjacents * 2))
    			{
    				for ($counter = 1; $counter < 2 + ($adjacents * 2); $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li><a class='current'>$counter</a></li>";
    					else
    						$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter); $functionName \">$counter</a></li>";
    				}
    				$pagination.= "<li class='dot'>...</li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lpm1); $functionName \">$lpm1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lastpage); $functionName \">$lastpage</a></li>";
    			}
    			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
    			{
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',1); $functionName \">1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',2); $functionName \">2</a></li>";
    				$pagination.= "<li class='dot'>...</li>";
    				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li><a class='current'>$counter</a></li>";
    					else
    						$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter); $functionName \">$counter</a></li>";
    				}
    				$pagination.= "<li class='dot'>..</li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lpm1); $functionName \" >$lpm1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lastpage); $functionName \" >$lastpage</a></li>";
    			}
    			else
    			{
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',1); $functionName \" >1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',2); $functionName \" >2</a></li>";
    				$pagination.= "<li class='dot'>..</li>";
    				for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li><a class='current'>$counter</a></li>";
    					else
    						$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter); $functionName \" >$counter</a></li>";
    				}
    			}
    		}
    		if ($page < $counter - 1){
    			$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$next); $functionName \" >></a></a></li>";
                $pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lastpage); $functionName \" >>></a></a></li>";
    		}else{
    			$pagination.= "<li><a class='current'>></a></a></li>";
                $pagination.= "<li><a class='current'>>></a></a></li>";
            }
    		$pagination.= "</ul>";
    	}
	}
		 echo $pagination; ?>
		 	</td></tr>
		 </table>
		</td>
		<?php  $per_page_array =  eval(ADMIN_PER_PAGE_ARRAY);
		if($total > $per_page_array[0]){ ?>
		<td  class="record">
		Per page 
		</td><td class="record" width="20%" style="padding-right:10px;">

			<select name="per_page" id="per_page" onchange="setPerPageAjax(this.value);<?php  echo $functionName; ?>" style="width:40px;">
			<?php foreach($per_page_array as $value){?>
				<option value="<?php echo($value);?>" <? if($per_page == $value) echo " selected='selected'"?>><?php echo($value);?></option>
			<?php }?>
			</select>
		</td>
		<?php }?>
		</tr>
		</table>
	</form>
<?php } 

function uploadImageToS3($image_path,$type,$image_name){
	error_reporting(0);
	/*echo "<br>==================>".$image_path;
	echo "<br>==================>".$type;
	echo "<br>==================>".$image_name;*/
	
	$image_upload_path = '';
	if($type == 1){ //user original
		$image_upload_path = 'users/'.$image_name;
	} else if($type == 2){ //user small thumb
		$image_upload_path = 'users/thumbnail/'.$image_name;
	} else if($type == 3){ //category original
		$image_upload_path = 'category/'.$image_name;
	} else if($type == 4){ //SliderImages //old 5
		$image_upload_path = 'sliderImages/'.$image_name;
	} else if($type == 5){ //SliderImages thumb//old 6
		$image_upload_path = 'sliderImages/thumbnail/'.$image_name;
	} else if($type == 6){ //merchants icon
		$image_upload_path = 'merchants/icons/'.$image_name;
	} else if($type == 7){ //merchants
		$image_upload_path = 'merchants/'.$image_name;
	} else if($type == 8){ //merchants
		$image_upload_path = 'products/'.$image_name;
	}
	require_once('sdk.class.php');// Include the SDK
	$s3 = new AmazonS3();//// Instantiate the AmazonS3 class
	
	$bucket = BUCKET_NAME;
	// Create our new bucket in the US-West region.
	$exists = $s3->if_bucket_exists($bucket);
	if(!$exists){
		$create_bucket_response = $s3->create_bucket($bucket, AmazonS3::REGION_US_W2);
	}
		$filename = $image_path;
		$s3->batch()->create_object($bucket, $image_upload_path, array(
					'fileUpload' => $filename,
					'contentType' => 'image/png',
					'acl' => AmazonS3::ACL_PUBLIC,
					 'headers' => array( // Custom $requestHeaders //meta headers
					           	 	"Cache-Control" => "max-age=315360000",
					            	"Expires" => gmdate("D, d M Y H:i:s T", strtotime("+5 years"))
								  )
					),
					array( // Custom $requestHeaders //meta headers
					           	 	"Cache-Control" => "max-age=315360000",
					            	"Expires" => gmdate("D, d M Y H:i:s T", strtotime("+5 years"))
								  )
					
			);
		$file_upload_response = $s3->batch()->send();
		//echo'<br>-------file---------<pre>';print_r($file_upload_response);echo'</pre>';
		
}

function image_exists($type,$image_name){
	return true;
	if($type == 1){ //user 
		$filename = 'users/'.$image_name;
	} else if($type == 2){ //user thumb
		$filename = 'users/thumbnail/'.$image_name;
	} else if($type == 3){ //category 
		$filename = 'category/'.$image_name;
	} else if($type == 4){ //sliderImages //old 5
		$filename = 'sliderImages/'.$image_name;
	} else if($type == 5){ //sliderImages thumb//old 6
		$filename = 'sliderImages/thumbnail/'.$image_name;
	}else if($type == 6){ //merchants icon
		$filename = 'merchants/icons/'.$image_name;
	} else if($type == 7){ //merchants
		$filename = 'merchants/'.$image_name;
	} else if($type == 8){ //merchants
		$filename = 'products/'.$image_name;
	}
	$bucket = BUCKET_NAME;
	require_once('sdk.class.php');// Include the SDK
	$s3 = new AmazonS3();// Instantiate the AmazonS3 class
	$s3->get_object_url($bucket, $filename);
	if ($info){
	 return true;
	}
	else{
		return false;
	}
}
function deleteImages($type,$image_name){
	if($type == 1){ //user 
		$filename = 'users/'.$image_name;
	} else if($type == 2){ //user thumb
		$filename = 'users/thumbnail/'.$image_name;
	} else if($type == 3){ //category thumb
		$filename = 'category/'.$image_name;
	} else if($type == 4){ //category thumb
		$filename = 'sliderImages/'.$image_name;
	} else if($type == 5){ //category thumb
		$filename = 'sliderImages/thumbnail/'.$image_name;
	} else if($type == 6){ //merchants icon
		$filename = 'merchants/icons/'.$image_name;
	} else if($type == 7){ //merchants
		$filename = 'merchants/'.$image_name;
	} else if($type == 8){ //merchants
		$filename = 'products/'.$image_name;
	}
	$bucket = BUCKET_NAME;
	
	require_once('sdk.class.php');// Include the SDK
	$s3 = new AmazonS3();// Instantiate the AmazonS3 class
	$info = $s3->delete_object($bucket, $filename);
	if ($info){
	 return true;
	}
	else{
		return false;
	}
}

function ipAddress(){
	/*if (!empty($_SERVER['HTTP_CLIENT_IP'])){
 		$ip_address=$_SERVER['HTTP_CLIENT_IP'];
	}elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
 		$ip_address=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
 		$ip_address=$_SERVER['REMOTE_ADDR'];
	}*/	
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
 		$ip_address=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
 		$ip_address=$_SERVER['REMOTE_ADDR'];
	}
	return $ip_address;
}
function sendingPNForAllUser($user_id,$message){
	if($_SERVER['REMOTE_ADDR']=='172.21.4.95') {
		echo '===>'.__LINE__.'===>'.$user_id.'<br />';
		echo '===>'.__LINE__.'===>'.$message.'<br />';
	}
}
function imagethumb_new($src, $des,$itype,$exn, $maxwidth, $hght) {
		ini_set('memory_limit', '512M');
		
		if(file_exists($src))
		   	$size=getimagesize($src);
		$mime_type	=	$size['mime'];
		$mime_type	= strtolower($mime_type);
		$itype	=	substr($mime_type,strpos($mime_type,'/')+1);
		if($mime_type	==	'image/gif') $exn	= 'gif';
		if( ($mime_type	==	'image/pjpeg') || ($mime_type	==	'image/jpg') || ($mime_type	==	'image/jpeg')) $exn	= 'jpg';
		//if($mime_type	==	'image/jpg') $exn	= 'jpg';
		//if($mime_type	==	'image/jpeg') $exn	= 'jpeg';
		if($mime_type	==	'image/png') $exn	= 'png';
		if($mime_type	==	'image/bmp') $exn	= 'bmp';
		
		$n_width=$size[0];
		$n_height=$size[1];
		$imagehw = GetImageSize($src);
	
		$imagewidth = $imagehw[0];
		$imageheight = $imagehw[1];
		$imgorig = $imagewidth;
		$n_width1 = $maxwidth;
		$n_height1 = $hght;
		if (($n_width - $n_width1) > ($n_height - $n_height1)) 
		{
		  //$imageprop=($n_width1*100)/$imagewidth;
		  //$imagevsize= ($imageheight*$imageprop)/100 ;
		  $imagewidth=$n_width1; 
		  $imageheight=($n_width1/$n_width)*$n_height;
		}else
		{		
			//$imageprop=($n_height1*100)/$imageheight;
		  	//$imagevsize= ($imageheight*$imageprop)/100 ;
		    $imagewidth=($n_height1/$n_height)*$n_width; 
		    $imageheight=$n_height1;	  
	
		}
		
		if($imagewidth > $n_width1){
			$imagewidth = $n_width1;
			$imageheight = round($imageheight / ($imagewidth/$n_width1));
		}
		
		if($n_width <= $n_width1 && $n_height <= $n_height1){
			$imagewidth = $n_width;
			$imageheight = $n_height;
		}
			
		
		//$n_width = $imagewidth;
		//$n_height = $imageheight;
		
		$destimg=imagecreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
		switch($exn)
		{
		case "jpg":	
		$srcimg=ImageCreateFromJPEG($src) or die("Problem In opening Source Image");
		$destimg=imagecreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
		break;
		case "jpeg":
		$srcimg=ImageCreateFromJPEG($src) or die("Problem In opening Source Image");
		$destimg=imagecreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
		break;
		case "pjpeg":
		$srcimg=ImageCreateFromJPEG($src) or die("Problem In opening Source Image");
		$destimg=imagecreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
		break;
		case "gif":
		$srcimg=ImageCreateFromGIF($src) or die("Problem In opening Source Image");
		$destimg=ImageCreate($n_width1,$n_height1) or die("Problem In Creating image");
		break;
		case "png":
		$srcimg=ImageCreateFromPNG($src) or die("Problem In opening Source Image");
		$destimg=imagecreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
		break;
		case "bmp":
		$srcimg =ImageCreateFromBMP($src) or die("Problem In opening Source Image");
		$destimg=imagecreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
		break;
		}
		
		$int = hexdec('ffffff');
		$arr = array("red" => 0xFF & ($int >> 0x10),
	               "green" => 0xFF & ($int >> 0x8),
	               "blue" => 0xFF & $int);
	
		//$black = ImageColorAllocate($image, $arr["red"], $arr["green"], $arr["blue"]); 
		$transparent = imagecolorallocate($destimg,  $arr["red"], $arr["green"], $arr["blue"]);
		
		for($x=0;$x<$n_width1;$x++) {
	           for($y=0;$y<$n_height1;$y++) {
	             imageSetPixel( $destimg, $x, $y, $transparent );
	           }
	         }
		$dest_x = (( $n_width1 / 2 ) - ( $imagewidth / 2 )); // centered
		$dest_y = (( $n_height1 / 2 ) - ( $imageheight / 2 )); // centered	
		ImageCopyresampled($destimg,$srcimg,$dest_x,$dest_y,0,0,$imagewidth,$imageheight,$n_width,$n_height) or die("Problem In resizing");
		//$destimg = $srcimg;
		if(($itype=="jpg")||($itype=="jpeg")||($itype=="pjpeg"))
		{	
			//header('Content-type:image/jpeg');
			
			imagejpeg($destimg, $des, 100); //ImageJPEG($destimg) or die('Problem In saving');
		}
		else
		if($itype=="gif")
		{
			//header('Content-type:image/gif');
			ImageGIF($destimg,$des) or die("Problem In saving"); //ImageGIF($destimg) or die('Problem In saving');
		}
		else
		if($itype=="png")
		{
			//header('Content-type:image/png');
			
			imagepng($destimg, $des, 9, PNG_NO_FILTER) or die("Problem In saving"); //ImagePNG($destimg) or die('Problem In saving');
		}
		else
		if($itype=="bmp")
		{
			//header('Content-type:image/png');
			imagebmp($destimg, $des, 9, BMP_NO_FILTER) or die("Problem In saving"); //ImagePNG($destimg) or die('Problem In saving');
		}
		imagedestroy($destimg);
	}  
	function checkImage($files,$type){
		$flag = 0;
		if (isset($files['tmp_name']) && $files['tmp_name'] != '') {
	   		$ImageArray = array('image/png','image/jpg','image/jpeg','image/gif');
			$dimension = getImageSize($files['tmp_name']);
			if(!in_array($files['type'],$ImageArray)){
				$flag = 1;
			}
			else if(!empty($files['error'])){
				$flag = 2;
			}
			else if($files['size'] > 2097152) {
				// 5242880 - 5MB
				$flag = 3;
			}
			else if($type ==1 && ($dimension[0] < '100' || $dimension[1] < '100')){
				$flag = 4;
			}
			else{
				$flag = 5;//success
			}
	   }
	   return $flag;
	}
function checkVideo($files){
	if (isset($files['tmp_name']) && $files['tmp_name'] != '') {
		//echo "<br>==================>".$files['size'];
		$VideoArray = array('image/mp4');
		if(!in_array($files['type'],$VideoArray)){
				$flag = 1;
		}
		else if(!empty($files['error'])){
			$flag = 2;
		}
		else if($files['size'] > 5242880 ) {
			$flag = 3;
		}
		else{
				$flag = 5;//success
	    }
	}
}
function subval_sort($a,$subkey) {
			$b  = $c  =	array();
			if(is_array($a) && count($a) > 0) {
				foreach($a as $k=>$v) {
					$b[$k] = $v[$subkey];
				}
			}
			if(is_array($b) && count($b) > 0) {
				asort($b);
				foreach($b as $key=>$val) {
					$c[]   = $a[$key];
				}
			}
			return $c;
}
function sorting($a,$subkey) {

			$b  = $c  =	array();
			if(is_array($a) && count($a) > 0) {
				foreach($a as $k=>$v) {
					$b[$k] = $v->$subkey;
				}
			}
			if(is_array($b) && count($b) > 0) {
				asort($b);
				foreach($b as $key=>$val) {
					$c[]   = $a[$key];
				}
			}
			return $c;
}
function getTimeZone(){
	$key="9dcde915a1a065fbaf14165f00fcc0461b8d0a6b43889614e8acdb8343e2cf15";
	if ($_SERVER['HTTP_HOST'] == '172.21.4.104'){
		$ip = '27.124.58.84';
	}
	else{
		//$ip = $_SERVER['REMOTE_ADDR']; //
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
		else
			$ip = $_SERVER['REMOTE_ADDR']; 
	}
	$url = "http://api.ipinfodb.com/v3/ip-city/?key=$key&ip=$ip&format=xml";

	$xml = simplexml_load_file($url);
	foreach($xml->children() as $child)
  	{
 	 //echo $child->getName() . ": " . $child . "<br />";
	 if($child->getName() == 'timeZone')
	 	return $child ;
 	}
}

function convertIntocheckinGmtSite($meet_time, $time_zone=''){
	 if($time_zone=='') {
	 	if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
			 $time_zone = getTimeZone();
			 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);
		} else {
			$time_zone = $_SESSION['tuplit_ses_from_timeZone'];
		}
	 }
	 if(substr($time_zone,0,1) == '-') {
	 	$multiplier = -1;
		$time_zone = substr($time_zone,1);
	 }
	 else
	 	$multiplier = 1;
	$offset_array = explode(':',$time_zone);
	$hour= $offset_array[0];
	$minutes= $offset_array[1];
	$offset = $multiplier*(($hour*60*60)+($minutes*60));
	if($meet_time != '') {
		$date = strtotime(gmdate($meet_time))+$offset;
	}
	else{
		$date = strtotime(gmdate('Y-m-d H:i:s'))+$offset;
	}
	//echo '<pre>';print_r($date);echo '</pre>';
	return date('Y-m-d H:i:s',$date);
}
/********************************************************
* Function : displayDate
********************************************************/
function displayDate($postedDate,$time_zone='')
{
	//$endDate = '2012-07-24 14:00:01';
	if($postedDate != '')
	{
		$db_date	=	date('d',strtotime($postedDate));
		$db_month	=	date('M',strtotime($postedDate));
		$db_year	=	date('Y',strtotime($postedDate));
		$db_hour	=	date('h',strtotime($postedDate));
		$db_min		=	date('i',strtotime($postedDate));
		$db_sec		=	date('s',strtotime($postedDate));
		$meridian	=	date('a',strtotime($postedDate));


		if($time_zone != '') {
			if(substr($time_zone,0,1) == '-') {
				$multiplier = -1;
				$time_zone = substr($time_zone,1);
			}
			else
				$multiplier = 1;
			$offset_array 	= explode(':',$time_zone);
			$hour			= $offset_array[0];
			$minutes		= $offset_array[1];
			$offset 		= $multiplier*(($hour*60*60)+($minutes*60));

			$c_date 		= strtotime(date('Y-m-d H:i:s'))+$offset;
			$cur_date		= date('d',$c_date);
			$cur_month		= date('M',$c_date);
			$cur_year		= date('Y',$c_date);
		}
		else {
			$cur_date	=	date('d');
			$cur_month	=	date('M');
			$cur_year	=	date('Y');
			$c_date 		= strtotime(date('Y-m-d H:i:s'));
		}
		$posted_week = date('W',strtotime($postedDate));
		$current_week = date('W',$c_date);
		if( ($db_year==$cur_year) && ($db_month==$cur_month) )
		{
			if($db_date==$cur_date)
				$time =	$db_hour.':'.$db_min.' '.$meridian;
			else if( ($db_date+1)==$cur_date)
				$time =	'Yesterday '.$db_hour.':'.$db_min.' '.$meridian;
			else if($posted_week == $current_week) {
				$week_day = date('l',strtotime($postedDate));
				$time =	$week_day.' '.$db_hour.':'.$db_min.' '.$meridian;
			}
			else
				//$time =	$db_date. ' '.$db_month.' '.$db_hour.':'.$db_min.' '.$meridian;
				$time =	$db_month. ' '.$db_date.' '.$db_hour.':'.$db_min.' '.$meridian;
		}
		else if($db_year==$cur_year)
			$time =	$db_month. ' '.$db_date.' '.$db_hour.':'.$db_min.' '.$meridian;
		else
			//$time =	$db_date. ' '.$db_month.' '.$db_year.' '.$db_hour.':'.$db_min.' '.$meridian;
			$time =	$db_month. ' '.$db_date.' '.$db_year.' '.$db_hour.':'.$db_min.' '.$meridian;
	}
	else
		$time = 'Null';
	return $time;
}
/********************************************************
* Function : displayDate
********************************************************/
function displayConversationTime($postedDate,$time_zone='')
{
	//$endDate = '2012-07-24 14:00:01';
	if($postedDate != '')
	{
		$db_date	=	date('d',strtotime($postedDate));
		$db_month	=	date('M',strtotime($postedDate));
		$db_year	=	date('Y',strtotime($postedDate));
		$db_hour	=	date('h',strtotime($postedDate));
		$db_min		=	date('i',strtotime($postedDate));
		$db_sec		=	date('s',strtotime($postedDate));
		$meridian	=	date('a',strtotime($postedDate));


		if($time_zone != '') {
			if(substr($time_zone,0,1) == '-') {
				$multiplier = -1;
				$time_zone = substr($time_zone,1);
			}
			else
				$multiplier = 1;
			$offset_array 	= explode(':',$time_zone);
			$hour			= $offset_array[0];
			$minutes		= $offset_array[1];
			$offset 		= $multiplier*(($hour*60*60)+($minutes*60));
			$c_date 		= strtotime(date('Y-m-d H:i:s'))+$offset;
			$cur_date		= date('d',$c_date);
			$cur_month		= date('M',$c_date);
			$cur_year		= date('Y',$c_date);
		}
		else {
			$cur_date	=	date('d');
			$cur_month	=	date('M');
			$cur_year	=	date('Y');
		}
		if( ($db_year==$cur_year) && ($db_month==$cur_month) )
		{
			if($db_date==$cur_date)
				$time =	$db_hour.':'.$db_min.' '.$meridian;
			else if( ($db_date+1)==$cur_date)
				$time =	'Yesterday ';
			else
				//$time =	$db_date. ' '.$db_month.' '.$db_hour.':'.$db_min.' '.$meridian;
				$time =	$db_month. ' '.$db_date;
		}
		else if($db_year==$cur_year)
			$time =	$db_month. ' '.$db_date;
		else
			//$time =	$db_date. ' '.$db_month.' '.$db_year.' '.$db_hour.':'.$db_min.' '.$meridian;
			$time =	$db_month. ' '.$db_date.' '.$db_year;
	}
	else
		$time = 'Null';
	return $time;
}

/********************************************************
* Function : Emoji Comment Text
********************************************************/
function getCommentTextEmoji($plateform,$comments,$from=''){
		error_reporting(0);
		//$plateform = 'android';
		require_once("emoji.php");
		if($plateform == 'ios' ) 
		{
			 //$datas = base64_encode(emoji_unified_to_softbank($comments)); 
			 $datas = $comments; 
		}
		else if($plateform == 'android' ) 
		{
			  if($from == '1'){
			 	//new function
				 $convertedComments = emoji_unified_to_html(base64_decode($comments));
				 preg_match_all('/<span class="emoji (.*?)"><\/span>/', $convertedComments, $matches);
				// echo'<pre>';print_r($matches);echo'</pre>';
					if(count($matches)>1) {
						$data = $convertedComments;
						foreach($matches as $key => $value) {
							if($key==0) {
								foreach($value as $subKey => $subValue) {
									$data = str_replace($subValue,'%*%'.$matches[1][$subKey].'%*%',$data);
								}
							}
						}
					} else {
						$data = preg_replace("/<span[^>]+\>/i", $matches[1][0], $htmlContent);
					}
					if(!strstr($convertedComments,'class="emoji')){
						$data = $convertedComments;
					}
					
					$datas = base64_encode($data);
			 }
			 else{
			 	//$datas = base64_encode(emoji_unified_to_google($comments)); 
				$datas = $comments; 
			}
		}
		else{
			// $comments_data = $comments;
			 if($from == '1' ) 
			{
				 $data_caption = base64_decode($comments);
				 $datasComment       = emoji_softbank_to_unified($data_caption);
			}
			else if($from == '2' ) 
			{
				 $data_caption = base64_decode($comments);
				 $datasComment = emoji_google_to_unified($data_caption);
			}
			else{
				  $datasComment = base64_decode($comments);
				  return  $datasComment;
			}
			if( preg_match('/(mac)/i', $_SERVER['HTTP_USER_AGENT'])) { 	
				 //$datas = emoji_html_to_unified(emoji_unified_to_html($comments));
				 $datas = emoji_unified_to_html($datasComment);
			}
			else{
				$datas = emoji_unified_to_html($datasComment);
			}
			
			$datas = preg_replace('/[^(\x20-\x7F)]*/','', $datas);
		}
	return $datas;
}

/********************************************************
* Function : Emoji Comment Text
********************************************************/
function addCommentTextEmoji($plateform,$comments){
		//error_reporting(E_ALL);
		/*require_once("emoji.php");
		if($plateform == 'ios' ) 
		{
			 $data_caption = base64_decode($comments);
			 $datas       = emoji_softbank_to_unified($data_caption);
		}
		else if($plateform == 'android' ) 
		{
			 $data_caption = base64_decode($comments);
			 $datas = emoji_google_to_unified($data_caption);
		}
		else{
			$datas = $comments;
		}*/
	if($plateform == 'web' ){
		$datas = base64_encode($comments);
	}
	else
		$datas = $comments;
	return $datas;
}
function html_entities ( $string )
{
     return str_replace ( array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' , '&mdash;', '&nbsp;'), array ( '&', '"', "'", '<', '>' ,'--', ' '), $string );
	 // return str_replace ( array ('&', '"', "'", '<', '>' ,'--'), array ('&amp;', '&quot;', '&apos;' , '&lt;' , '&gt;' , '&mdash;'), $string );
}
function logEntry($event,$txt_file){
	$log_dir	=	'C:/wamp/www/tuplit/logs';
	if(!is_dir($log_dir)) {
		mkdir($log_dir,0777);
	}
	$filename = "C:/wamp/www/tuplit/logs".$txt_file;
	$f		  = fopen($filename, 'a' );			// open the log file for writing and put the pointer at the end of the file
	fwrite ($f, $event . chr(13) . chr(10) );
	fclose($f);
}
function getImageMimeType($imagedata)
{
  $imagemimetypes = array( 
    "jpeg" => "FFD8", 
    "png" => "89504E470D0A1A0A", 
    "gif" => "474946",
    "bmp" => "424D"
  );

  foreach ($imagemimetypes as $mime => $hexbytes)
  {
    $bytes = getBytesFromHexString($hexbytes);
    if (substr($imagedata, 0, strlen($bytes)) == $bytes)
      return $mime;
  }

  return NULL;
}
function getBytesFromHexString($hexdata)
{
  for($count = 0; $count < strlen($hexdata); $count+=2)
    $bytes[] = chr(hexdec(substr($hexdata, $count, 2)));

  return implode($bytes);
}
/* Create endpointARN for push notification */
function createEndpointARNAWS($PlatformApplicationArn,$Token,$CustomUserData){
	error_reporting(E_ALL);
	$endpoint = require("sns-create.php");
	return $endpoint;die();
}
function sendNotificationAWS($message,$EndpointArn,$platform,$badge,$type,$processId,$userId){
	error_reporting(E_ALL);
	$endpoint = require("sns-send.php");
	return $endpoint;die();
}
function dateValidation($date){
	$result = 0;
	$date  = explode('/', $date);
	if (count($date) == 3) {
		if($date[0] != '' && $date[1] != '' && $date[2] != ''){
			if($date[2] >= '1983'){
			    if (checkdate($date[0], $date[1], $date[2]))
				    $result = 1;
			   	else 
			       $result = 0;
			}
			else 
		       $result = 0;
		}
		else {
		    $result = 0;
		}
	} 
	else {
	    $result = 0;
	}	
	return $result;
}
function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

function logEntryProcess($event,$txt_file){
	if(!is_dir(ABS_PATH.'/admin/logs')) {
		mkdir(ABS_PATH.'/admin/logs',0777);
	}
	$log_dir	=	ABS_PATH.'/admin/logs/'.date('mY');
	if(!is_dir($log_dir)) {
		mkdir($log_dir,0777);
	}
	$filename = ABS_PATH."/admin/logs/".date('mY')."/".$txt_file;
	$f		  = fopen($filename, 'a' );			// open the log file for writing and points to end of file
	fwrite ($f, $event . chr(13) . chr(10) );
	fclose($f);
}
function getTimeDifference($given_date){
	$current_date_val = strtotime(date('Y-m-d H:i:s'));
	$given_time_val = strtotime($given_date);
	$diff = $current_date_val - $given_time_val;
	$hours=floor($diff/(60*60));

    $mins=floor(($diff-($hours*60*60))/(60));

    $secs=floor(($diff-(($hours*60*60)+($mins*60))));
    if($hours == 24){
		$timing = 'yesterday';
	}
	else if($hours > 24){
		$days = round($hours /24 );
		$timing = $days.' day ago';
		if($days > 30){
			$month = floor($days /30 );
			$timing = $month.' month ago';
			if($month > 12){
				$year = floor($month /12 );
				$timing = $year.' yrs ago';
			}
		}
	}
	else if($hours != '0'){
		$timing = $hours.' h';
	}
	else if($hours == '0' && $mins !='0'){
		$timing = $mins.' m';
	}
	else if($hours == '0' && $mins =='0' && $secs != '0'){
		$timing = $secs.' s';
	}
	return $timing;
}
function convertToArrayNames($vars){
	$last = strrchr($vars,",");
	$last_ = str_replace(","," & ",$last);
	$names_val = str_replace("$last","$last_",$vars);
	$names_val = str_replace(",",", ",$names_val);
	
	return $names_val;
}
/********************************************************
* Function : displayDate
********************************************************/
function displayConversationDateTime($postedDate,$time_zone='')
{
	//$endDate = '2012-07-24 14:00:01';
	if($postedDate != '')
	{
		$db_date	=	date('d',strtotime($postedDate));
		$db_month	=	date('m',strtotime($postedDate));
		$db_year	=	date('Y',strtotime($postedDate));
		$db_hour	=	date('h',strtotime($postedDate));
		$db_min		=	date('i',strtotime($postedDate));
		$db_sec		=	date('s',strtotime($postedDate));
		$meridian	=	date('a',strtotime($postedDate));


		if($time_zone != '') {
			if(substr($time_zone,0,1) == '-') {
				$multiplier = -1;
				$time_zone = substr($time_zone,1);
			}
			else
				$multiplier = 1;
			$offset_array 	= explode(':',$time_zone);
			$hour			= $offset_array[0];
			$minutes		= $offset_array[1];
			$offset 		= $multiplier*(($hour*60*60)+($minutes*60));
			$c_date 		= strtotime(date('Y-m-d H:i:s'))+$offset;
			$cur_date		= date('d',$c_date);
			$cur_month		= date('m',$c_date);
			$cur_year		= date('Y',$c_date);
		}
		else {
			$cur_date	=	date('d');
			$cur_month	=	date('m');
			$cur_year	=	date('Y');
		}
		$time =	$db_month. '/'.$db_date.'/'.$db_year.'&nbsp;&nbsp;'.$db_hour.':'.$db_min.' '.$meridian;
	}
	else
		$time = 'Null';
	return $time;
}

/********************************************************
* Function : displayDate
********************************************************/
function displayConversationDateTimeForLog($postedDate,$time_zone='')
{
	//$endDate = '2012-07-24 14:00:01';
	if($postedDate != '')
	{
		$db_date	=	date('d',strtotime($postedDate));
		$db_month	=	date('m',strtotime($postedDate));
		$db_year	=	date('Y',strtotime($postedDate));
		$db_hour	=	date('h',strtotime($postedDate));
		$db_min		=	date('i',strtotime($postedDate));
		$db_sec		=	date('s',strtotime($postedDate));
		$meridian	=	date('a',strtotime($postedDate));


		if($time_zone != '') {
			if(substr($time_zone,0,1) == '-') {
				$multiplier = -1;
				$time_zone = substr($time_zone,1);
			}
			else
				$multiplier = 1;
			$offset_array 	= explode(':',$time_zone);
			$hour			= $offset_array[0];
			$minutes		= $offset_array[1];
			$offset 		= $multiplier*(($hour*60*60)+($minutes*60));
			$c_date 		= strtotime(date('Y-m-d H:i:s'))+$offset;
			$cur_date		= date('d',$c_date);
			$cur_month		= date('m',$c_date);
			$cur_year		= date('Y',$c_date);
		}
		else {
			$cur_date	=	date('d');
			$cur_month	=	date('m');
			$cur_year	=	date('Y');
		}
		$time =	$db_month. '/'.$db_date.'/'.$db_year.'&nbsp;&nbsp;'.$db_hour.':'.$db_min.':'.$db_sec.' '.$meridian;
	}
	else
		$time = 'Null';
	return $time;
}
function getImagesFromGoogle($serach_term){


//you may use "r" instead of "rb"
$variablee = fopen(GOOGLE_IMAGE_URL.$serach_term, "rb");  
return stream_get_contents($variablee);  
}
/* To Check folder is empty or not */
function checkFolderIsEmptyOrNot( $folderName ){
    $files = array ();
    if ( $handle = opendir ( $folderName ) ) {
        while ( false !== ( $file = readdir ( $handle ) ) ) {
            if ( $file != "." && $file != ".." ) {
                $files [] = $file;
            }
        }
        closedir ( $handle );
    }
    return ( count ( $files ) > 0 ) ?  TRUE: FALSE;
}
function remoteFileExists($url) {
    $curl = curl_init($url);

    //don't fetch the actual page, you only want to check the connection is ok
    curl_setopt($curl, CURLOPT_NOBODY, true);

    //do request
    $result = curl_exec($curl);

    $ret = false;

    //if request did not fail
    if ($result !== false) {
        //if request was ok, check response code
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  

        if ($statusCode == 200) {
            $ret = true;   
        }
    }

    curl_close($curl);

    return $ret;
}

function curlRequest($url, $method, $data = null, $access_token = '')
{
//echo '<pre>'; print_r($data); exit;
	$handle = curl_init();
	curl_setopt($handle, CURLOPT_URL, $url);
	if ($access_token != '') {
		# headers and data (this is API dependent, some uses XML)
		if ($method == 'PUT') {
		$headers = array(
						'Accept: application/json',
						'Content-Type: application/json',
						'Authorization: '.$access_token,
						);
		} else {
			$headers = array(
						'Authorization: '.$access_token
						);
		}
		curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
	} 
	
	
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)"); 
	curl_setopt($handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
	
	switch($method) {
		case 'GET':
		break;
		case 'POST':
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
		break;
		case 'PUT':
		curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
		break;
		case 'DELETE':
		curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
		break;
	}
	$response = curl_exec($handle);
	if($_SERVER['REMOTE_ADDR'] == '172.21.4.215'){
		//echo'<div style="width:200px"><pre>';print_r($response);echo'</pre></div>';
	}
	
	$response = json_decode($response, true);
	return $response; 
}

function getLatLngFromAddress($address) {
	$address = str_replace(" ", "+", $address);
	$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false");//&region=$region	
	$json = json_decode($json);
	if(!empty( $json->{'results'}))  {
		$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
		$long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
		if(isset($lat) && $lat !='' && isset($long) && $long !='') {
			return $lat.'###'.$long;
		} else {
			return 0;
		}
	} else {
		return 0;
	}
}

function displayOpeningHoursString($String,$type){
	$outputArray = array();
	$openingHours = array();
	$closed ='';
	global $admin_days_array;
	if(!empty($String)&& preg_match("/##/i", $String) && $type == 1){
		$days = explode('***',$String);
		$i=0;
		foreach($days as $key=>$val) {			
			$times = explode('##',$val);
			if($times[1] == 0) {
				if(empty($closed))
					$closed .= 'Closed '.$admin_days_array[$key];
				else
					$closed .= ', '.$admin_days_array[$key];
			}
			if($times[1] == 1) {
				$temp = $admin_days_array[$key].' : ';
				$temp1 = 0;
				if($times[2] >= 12) {
					if($times[2] == 12) {
						$temp .= $times[2].' PM';
					}
					else {
						$temp1 = $times[2] - 12;
						if($temp1 < 0)
							$temp1 = $temp1 * (-1);
						$temp .= $temp1.' PM';
					}
				}
				else {
					if($times[2] == '00')
						$temp .= '12 AM';
					else
						$temp .= $times[2].' AM';
				}
				$temp .=' - ';
				$temp1 = 0;
				if($times[3] >= 12) {
					if($times[3] == 12) {
						$temp .= $times[3].' PM';
					}
					else {
						$temp1 = $times[3] - 12;
						if($temp1 < 0)
							$temp1 = $temp1 * (-1);
						$temp .= $temp1.' PM';
					}
				}
				else {
					if($times[3] == '00')
						$temp .= '12 AM';
					else
						$temp .= $times[3].' AM';
				}								
				$openingHours[$i] = $temp;
				$i++;				
			}
		}	
		$outputArray[0] = $openingHours;
		$outputArray[1] = $closed;
		return $outputArray;
	}	
}

function getOpeningHoursString($data,$from,$to,$set){
	$openingHours = '';	
	for($i = 0; $i < 7;$i++){
		$tempset = $set.'_'.$i;
		$tempfrom = $from.'_'.$i;
		$tempto = $to.'_'.$i;
		if(isset($data[$tempset]) && $data[$tempset] == '1'){
			$openingHours .=$i.'##1##'.$data[$tempfrom].'##'.$data[$tempto].'***';			
		}
		else
			$openingHours .=$i.'##0##00##00***';
	}
	$openingHours = rtrim($openingHours, "***");
	return $openingHours;
}

function editOpeningHoursString($String){
	$outputArray = array();	
	global $admin_days_array;
	if(!empty($String) && preg_match("/##/i", $String)){
		$days = explode('***',$String);
		$i=0;
		foreach($days as $key=>$val) {			
			$times = explode('##',$val);
			$outputArray[$i] = $times;
			$i++;
		}
	}
	return $outputArray;
}

function openingHoursString($openHoursArray,$from) {	
	$temp = (array)$openHoursArray;
	$openHoursArray = array();
	foreach($temp as $key=>$val) {
		$openHoursArray[$key] = (array)$val;
	}	
	$openHour	= array();
	global $admin_days_array;
	if(count($openHoursArray) == 0)
		$openHour['Closed'] = "Monday to Sunday : Closed";
	else if($openHoursArray[0]['DateType'] == 1)
		$openHour['Open'][0] = "Monday to Sunday : ".$openHoursArray[0]['Start']." to ".$openHoursArray[0]['End'];	
	else {
		$open = array();
		if($from == 0)
			$close = 'Closed ';
		else
			$close = '';
		foreach($openHoursArray as $key=>$val) {
			if(!empty($openHoursArray[$key]['Start']) && !empty($openHoursArray[$key]['End'])) {
				$open[] = $admin_days_array[$key].' : '.$openHoursArray[$key]['Start']." to ".$openHoursArray[$key]['End'];	
			}
			else {
				if(empty($close))
					$close = $admin_days_array[$key];
				else if($close == 'Closed ')	
					$close .= $admin_days_array[$key];
				else
					$close .= ", ".$admin_days_array[$key];
			}
		}
		$openHour['Open'] = $open;
		$openHour['Closed'] = $close;
	}
	return $openHour;
}


function price_fomat($price_val){
	$price = number_format($price_val,2,'.',',');
	if(strstr($price,'$'))
		return $price;
	else
		return '$'.$price;
}

function imagethumb_addbg($src,$des,$itype,$exn,$maxwidth,$hght)
{
	ini_set('memory_limit', '100M');
	if(file_exists($src))
	   	$size=getimagesize($src);
	$mime_type	=	$size['mime'];
	$itype	=	substr($mime_type,strpos($mime_type,'/')+1);
	if($mime_type	==	'image/gif') $exn	= 'gif';
	if($mime_type	==	'image/pjpeg') $exn	= 'pjpeg';
	if($mime_type	==	'image/jpg') $exn	= 'jpg';
	if($mime_type	==	'image/jpeg') $exn	= 'jpeg';
	if($mime_type	==	'image/png') $exn	= 'png';
	$n_width=$size[0];
	$n_height=$size[1];
	$imagehw = GetImageSize($src);
	$imagewidth = $imagehw[0];
	$imageheight = $imagehw[1];
	$imgorig = $imagewidth;
	$n_width1 = $maxwidth;
	$n_height1 = $hght;
	if (($n_width - $n_width1) > ($n_height - $n_height1))
	{
	  //$imageprop=($n_width1*100)/$imagewidth;
	  //$imagevsize= ($imageheight*$imageprop)/100 ;
	 	$imagewidth=$n_width1;
	    $imageheight=($n_width1/$n_width)*$n_height;
	}else
	{
		//$imageprop=($n_height1*100)/$imageheight;
	  	//$imagevsize= ($imageheight*$imageprop)/100 ;
	    $imagewidth=($n_height1/$n_height)*$n_width;
	    $imageheight=$n_height1;
	}
	if($imagewidth > $n_width1){
		$imagewidth = $n_width1;
		$imageheight = round($imageheight / ($imagewidth/$n_width1));
	}
	if($n_width <= $n_width1 && $n_height <= $n_height1){
		$imagewidth = $n_width;
		$imageheight = $n_height;
	}
	$destimg=ImageCreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
	switch($exn)
	{
	case "jpg":
		$srcimg=ImageCreateFromJPEG($src) or die("Problem In opening Source Image");
		$destimg=ImageCreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
	break;
	case "jpeg":
		$srcimg=ImageCreateFromJPEG($src) or die("Problem In opening Source Image");
		$destimg=ImageCreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
	break;
	case "pjpeg":
		$srcimg=ImageCreateFromJPEG($src) or die("Problem In opening Source Image");
		$destimg=ImageCreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
	break;
	case "gif":
		$srcimg=ImageCreateFromGIF($src) or die("Problem In opening Source Image");
		$destimg=ImageCreate($n_width1,$n_height1) or die("Problem In Creating image");
	break;
	case "png":
		$srcimg=ImageCreateFromPNG($src) or die("Problem In opening Source Image");
		$destimg=ImageCreatetruecolor($n_width1,$n_height1) or die("Problem In Creating image");
	break;
	}
	//$int = hexdec('c19383');
	//$int =  repeat-x scroll 0 top #BD8E7C;
	$int = hexdec('ffffff');
	$arr = array("red" => 0xFF & ($int >> 0x10),
               "green" => 0xFF & ($int >> 0x8),
               "blue" => 0xFF & $int);
	//$black = ImageColorAllocate($image, $arr["red"], $arr["green"], $arr["blue"]);
	$transparent = imagecolorallocate($destimg,  $arr["red"], $arr["green"], $arr["blue"]);
	for($x=0;$x<$n_width1;$x++) {
           for($y=0;$y<$n_height1;$y++) {
             imageSetPixel( $destimg, $x, $y, $transparent );
           }
         }
	$dest_x = (( $n_width1 / 2 ) - ( $imagewidth / 2 )); // centered
	$dest_y = (( $n_height1 / 2 ) - ( $imageheight / 2 )); // centered
	ImageCopyresampled($destimg,$srcimg,$dest_x,$dest_y,0,0,$imagewidth,$imageheight,$n_width,$n_height) or die("Problem In resizing");
	if(($itype=="jpeg")||($itype=="jpg")||($itype=="pjpeg"))
	{
		//ImageJPEG($destimg,$des) or die("Problem In saving");
		imagejpeg($destimg,$des);
	}
	else
	if($itype=="gif")
	{
		ImageGIF($destimg,$des) or die("Problem In saving");
	}
	else
	if($itype=="png")
	{
		ImagePNG($destimg,$des) or die("Problem In saving");
	}
	imagedestroy($destimg);
}

function formOpeningHours($openinghours) {
	$newopeninghours	=	array();
	foreach($openinghours as $key=>$value) {
		$value	=	(array)$value;
		//From time
		$timeampm = $ampm	=	$hr	=	$min	=	$hrmin	=	'';
		$time	=	$splittime	=	$fromtime	=	array();
		
		$timeampm		=	$value['Start'];
		if(!empty($timeampm)) {
			$time					=	explode(" ", $timeampm);
			$hrmin					=	$time[0];
			$ampm					=	$time[1];
			$splittime				=	explode(":", $hrmin);
			$hr						=	$splittime[0];
			$min					=	$splittime[1];
			$fromtime['fromTime']	=	$value['Start'];
			$fromtime['hr']			=	$hr;
			$fromtime['min']		=	$min;
			$fromtime['ampm']		=	$ampm;
		}
		//to time			
		$timeampm = $ampm	=	$hr	=	$min	=	$hrmin	=	'';
		$time	=	$splittime	=	$toTime	=	array();
		
		$timeampm		=	$value['End'];
		if(!empty($timeampm)) {
			$time				=	explode(" ", $timeampm);
			$hrmin				=	$time[0];
			$ampm				=	$time[1];
			$splittime			=	explode(":", $hrmin);
			$hr					=	$splittime[0];
			$min				=	$splittime[1];
			$toTime['toTime']	=	$value['End'];
			$toTime['hr']		=	$hr;
			$toTime['min']		=	$min;
			$toTime['ampm']		=	$ampm;
		}
		$newopeninghours[$key]['id'] 			= $value['id'];
		$newopeninghours[$key]['fkMerchantId'] 	= $value['fkMerchantId'];
		$newopeninghours[$key]['OpeningDay'] 	= $value['OpeningDay'];
		$newopeninghours[$key]['Start'] 		= $fromtime;
		$newopeninghours[$key]['End'] 			= $toTime;
		$newopeninghours[$key]['DateType'] 		= $value['DateType'];
		$newopeninghours[$key]['DateCreated'] 	= $value['DateCreated'];
	}
	return	$newopeninghours;
}

function openingHoursStringupdated($openHoursArray){
	$temp = (array)$openHoursArray;
	$openHoursArray = array();
	foreach($temp as $key=>$val) {
		$openHoursArray[$key] = (array)$val;
	}	
	$openHour	= array();
	global $admin_days_array;
	if(count($openHoursArray) == 0)
		$openHour['Closed'] = "Mon to Sun : Closed";
	else if($openHoursArray[0]['DateType'] == 1) {
		$from	=	openHourExplode($openHoursArray[0]['Start']);
		$to		=	openHourExplode($openHoursArray[0]['End']);		
		$openHour['Open'][0] = "Mon to Sun : ".$from." - ".$to;
	}
	else {
		$opening = array();		
		$newopenHoursArray	=	array();
		
		//Converting to 24 hours
		foreach($openHoursArray as $key=>$val) {
			if(!empty($val['Start']) && !empty($val['End'])) {
				$from						=	openHourExplode($val['Start']);
				$to							=	openHourExplode($val['End']);
				$newopenHoursArray[$key]	=	$from.' - '.$to;
			} else {
				$newopenHoursArray[$key]	=	'';
			}
		}
		
		//forming closed days		
		$closed	=	'';
		foreach($newopenHoursArray as $key1=>$val1) {
			if(empty($val1)) {
				$day		=	substr($admin_days_array[$key1],0,3);
				if(empty($closed))
					$closed 	=	$day;
				else
					$closed 	.=	', '.$day;
				unset($newopenHoursArray[$key1]);
			}			
		}		
		$openHour['Closed'] = $closed;
		
		//Forming open days
		$sameopen			=	array();
		foreach($newopenHoursArray as $key2=>$val2) {
			if(!in_array($admin_days_array[$key2],$sameopen)) {
				$string			=	$val2;
				$searchArray	=	$newopenHoursArray;
				$open			=	'';
				foreach($searchArray as $key3=>$val3) {
					if(($val2==$val3)) {
						$day		=	substr($admin_days_array[$key3],0,3);
						if(empty($open))
							$open	=	$day;
						else
							$open	.=	", ".$day;
						unset($newopenHoursArray[$key3]);
						$sameopen[]	=	$admin_days_array[$key3];
					}				
				}
				if(empty($open))
					$opening[]	=	substr($admin_days_array[$key2],0,3).' : '.$val2;
				else
					$opening[]	=	$open.' : '.$val2;
				unset($newopenHoursArray[$key3]);
			}
		}	
		$openHour['Open'] = $opening;
	}
	//echo "<pre>"; echo print_r($openHour); echo "</pre>";
	return $openHour;
}
function openHourExplode($openHours) {
	$time	=	$splittime	=	array();
	$time			=	explode(" ", $openHours);
	$hrmin			=	$time[0];
	$ampm			=	$time[1];
	$splittime		=	explode(":", $hrmin);
	$hr				=	$splittime[0];
	$min			=	$splittime[1];
	if($ampm == 'PM') {
		$hr			=	$hr + 12;
	}
	if(strlen($hr) == 1){
		$hr			=	(string)$hr;
		$hr			=	'0'.$hr;
	}
	$outtime		=	$hr.":".$min;
	return $outtime;
}
function miles2kms($miles) {
	$ratio = 1.609344;
	$kms = $miles * $ratio;
	return $kms;
} 

function msort($array, $key, $sort_flags = SORT_ASC) {
    if (is_array($array) && count($array) > 0) {
        if (!empty($key)) {
            $mapping = array();
            foreach ($array as $k => $v) {
                $sort_key = '';
                if (!is_array($key)) {
                    $sort_key = $v[$key];
                } else {
                    // @TODO This should be fixed, now it will be sorted as string
                    foreach ($key as $key_key) {
                        $sort_key .= $v[$key_key];
                    }
                    $sort_flags = SORT_STRING;
                }
                $mapping[$k] = $sort_key;
            }
            asort($mapping, $sort_flags);
            $sorted = array();
            foreach ($mapping as $k => $v) {
                $sorted[] = $array[$k];
            }
            return $sorted;
        }
    }
    return $array;
}

?>