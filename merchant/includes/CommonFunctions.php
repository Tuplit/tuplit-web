<?php
ini_set('default_encoding','utf-8');
function merchant_login_check()
{
    if(!isset($_SESSION['merchantInfo']['AccessToken'])){
		header('location:Login');
		die();
    }
}
function user_login_check(){
	if(!isset($_SESSION["tuplit_ses_merchant_id"]) || $_SESSION["tuplit_ses_merchant_id"] == '')
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
	return $sort_link.''.$title.'</a>&nbsp;'.$sort_link.'<img src="'.MERCHANT_IMAGE_PATH . $sort_image.'" alt="" border="0"></a>';
}

// Display paging control
//Input : no. of records and URL
function pagingControlLatest($total,$action='')
{
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
		<?php $per_page_array =  eval(MERCHANT_PER_PAGE_ARRAY);
		if($total > $per_page_array[0]){ ?>
			<div class="col-xs-2">
			<span class="fright">
				<select name="per_page" id="per_page" onchange="setPerPage(this.value);">
				<?php foreach($per_page_array as $value){ ?>
					<option value="<?php echo($value);?>" <?php if($per_page == $value) { echo "selected='selected'"; } ?>><?php echo($value);?></option>
				<?php } ?>
				</select>
			</span>
			<span class="fright">Per page &nbsp;</span>
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
	
	//echo 'level 0 ---- '.$per_page.'*****'.$page.'----';
	$pagination 	= '<div class="col-sm-9 col-xs-12  mb_clr" align="center">';
	?>
	<form name="pagingAjax" id="pagingAjax" method="post" action="<?php echo($functionName);?>"  >
		<input type="Hidden" value="<?php echo($_SESSION['curpage']);?>" name="cur_page" id="cur_page">
		<input type="Hidden" value="<?php echo($_SESSION['orderby']);?>" name="order_by" id="order_by">
		<input type="Hidden" value="<?php echo($_SESSION['ordertype']);?>" name="order_type" id="order_type">
		<input type="Hidden" id="total_records" name="total_records" value="<?php echo $total;?>">
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
                    $pagination .= "<li class='details LH30'>Page $page of $lastpage &nbsp;&nbsp;</li>";
			if ($page == 1)
			{
				$pagination.= "<li><span><i class='fa fa-angle-double-left fa-lg'></i></span></li>";
				$pagination.= "<li><span><i class='fa fa-angle-left fa-lg'></i></span></li>";
			}
			else
			{
				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$firstPage); \" ><i class='fa fa-angle-double-left fa-lg'></i></a></li>";
				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$prev);\" ><i class='fa fa-angle-left fa-lg'></i></a></li>";
			}
    		if ($lastpage < 7 + ($adjacents * 2))
    		{
    			for ($counter = 1; $counter <= $lastpage; $counter++)
    			{
    				if ($counter == $page)
    					$pagination.= "<li class='active'><span><i>$counter</i></span></li>";
    				else
    					$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter);\">$counter</a></li>";
						//$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValues2($counter); $functionName \">$counter</a></li>";
					
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
							$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter);\">$counter</a></li>";

    				}
    				//$pagination.= "<li class='dot'>...</li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lpm1); \">$lpm1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lastpage);\">$lastpage</a></li>";
    			}
    			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
    			{
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',1);  \">1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',2);  \">2</a></li>";
    				//$pagination.= "<li class='dot'>...</li>";
    				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li><a class='current'>$counter</a></li>";
    					else
    						$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter);\">$counter</a></li>";
    				}
    				//$pagination.= "<li class='dot'>..</li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lpm1); \" >$lpm1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lastpage);\" >$lastpage</a></li>";
    			}
    			else
    			{
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',1); \" >1</a></li>";
    				$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',2);  \" >2</a></li>";
    				//$pagination.= "<li class='dot'>..</li>";
    				for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li><a class='current'>$counter</a></li>";
    					else
    						$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$counter); \" >$counter</a></li>";
    				}
    			}
    		}
    		if ($page < $counter - 1){
    			$pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$next); \" ><i class='fa fa-angle-right fa-lg'></i></a></li>";
                $pagination.= "<li><a href='javascript:void(0);' onclick=\"javascript:setPagingControlValuesAjax('".$_SESSION['orderby']."','".$_SESSION['ordertype']."',$lastpage); \" ><i class='fa fa-angle-double-right fa-lg'></i></a></li>";
    		}else{
    			$pagination.= "<li><a class='pre_arr'><i class='fa fa-angle-right fa-lg'></i></a></li>";
                $pagination.= "<li><a class='pre_arr_lt'><i class='fa fa-angle-double-right fa-lg'></i></a></li>";
            }
    		$pagination.= "</ul>";
    	}
	}
		 echo $pagination; ?>
		</div>
		<?php  $per_page_array =  eval(MERCHANT_PER_PAGE_ARRAY);
		if($total > $per_page_array[0]){ ?>
		<div class="col-xs-12  col-sm-3 mb_clr pad no-padding">
			<span class="pull-right">
			<select name="per_page" id="per_page" onchange="setPerPageAjax(this.value);" style="width:60px;">
			<?php foreach($per_page_array as $value){?>
				<option value="<?php echo($value);?>" <? if($per_page == $value) echo " selected='selected'"?>><?php echo($value);?></option>
			<?php }?>
			</select>
			</span>
			<div class="pull-right">Per page &nbsp;</div>
	 </div>

		<?php }?>
	</form>
<?php } 


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
function subval_sort($a,$subkey,$type='') {
		//$type = '' for ascending 
		//$type = 1 for descending 
			$b  = $c  =	array();
			if(is_array($a) && count($a) > 0) {
				foreach($a as $k=>$v) {
					$b[$k] = $v[$subkey];
				}
			}
			if(is_array($b) && count($b) > 0) {
				if($type == 1)
					arsort($b);
				else
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
/*function displayConversationTime($postedDate,$time_zone='')
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
}*/

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
function sendNotificationAWS($message,$EndpointArn,$platform,$badge,$type,$processId,$userId,$unreadCount=0,$username){
	
	require_once __DIR__ . '/aws.phar';
	// Create a new Amazon SNS client
	$sns = Aws\Sns\SnsClient::factory(array(
	    'key'    => '',
	    'secret' => '',
	    'region' => 'us-west-2'
	));
	$badge = $badge + 1;
  if($EndpointArn  !=''){
	  try
	  {
	   if($platform == 2){
			$data = array(
			    'TargetArn' => $EndpointArn,
			    'MessageStructure' => 'json',
			    'Message' => json_encode(array(
			        'GCM' => json_encode(array(
			            'data' => array('message' => $message,
										'badge'=>(integer)$badge ,
										'sound' => 'default',
										'processId' => $processId,
										'type' => $type,
										'userId' => $userId ,
										'unreadMessage'=>$unreadCount,
										'userName'=>$username),
			        ))
			    ))
			 );
			$sns->publish($data);
		}
		else{
			if($_SERVER['SERVER_ADDR']=='172.21.4.104')
				$apns = 'APNS_SANDBOX';
		    else
		  		$apns = 'APNS';
			
			$data = array(
				    'TargetArn' => $EndpointArn,
				    'MessageStructure' => 'json',
				    'Message' => json_encode(array(
						$apns => json_encode(array(
				            'aps' => array('alert' => $message,
											'badge'=> (integer)$badge ,
											'sound' => 'default',
											'processId' => $processId,
											'type' => $type,
											'userId' => $userId ,
											'unreadMessage'=>$unreadCount,
											'userName'=>$username ),
				        ))
				    ))
				 );
			$sns->publish($data);
			
		}
	  return 1;
	  }
	  catch (Exception $e)
	  {
	  //  echo'<pre>';print_r($data);echo'</pre>';
		//echo"<br>";
		//print($EndpointArn . " - Failed: " . $e->getMessage() . "!\n");
		return 0;
	  }
  }
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
	if(!is_dir(ABS_PATH.'/logs')) {
		mkdir(ABS_PATH.'/logs',0777);
	}
	$log_dir	=	ABS_PATH.'/logs/'.date('mY');
	if(!is_dir($log_dir)) {
		mkdir($log_dir,0777);
	}
	$filename = ABS_PATH."/logs/".date('mY')."/".$txt_file;
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
	if($_SERVER['REMOTE_ADDR'] == '172.21.4.130'){
		//echo'<div style="width:200px"><pre>';print_r($response);echo'</pre></div>';
	}
	
	$response = json_decode($response, true);
	return $response; 
}
function setCookies($post)
{
      $expire = strtotime("+30 days");
	  setcookie('tuplit_merchant_email', ($post['Email']), $expire);
      setcookie('tuplit_merchant_password', encryption($post['Password']), $expire);
      setcookie('tuplit_merchant_logout', 'login', $expire);
}
function destroyCookies()
{
    setcookie("tuplit_merchant_email",'',time()-10);
    setcookie("tuplit_merchant_password",'',time()-10);
    setcookie("tuplit_merchant_logout",'',time()-10);
}
function encryption($str)
{
    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = $block - (strlen($str) % $block);
    $str .= str_repeat(chr($pad), $pad);
    return base64_encode(mcrypt_encrypt(MCRYPT_TripleDES,ENCODE_KEY , $str, MCRYPT_MODE_ECB));
}

function decryption($str)
{
	$str	=	base64_decode($str);
    $str = mcrypt_decrypt(MCRYPT_TripleDES, ENCODE_KEY , $str, MCRYPT_MODE_ECB);
    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = ord($str[($len = strlen($str)) - 1]);
    return substr($str, 0, strlen($str) - $pad);
}
function getLatLngFromAddress($address) {
	$address = str_replace(" ", "+", $address);
	$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false");//&region=$region	
	$json = json_decode($json);
	$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
	$long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
	if(isset($lat) && $lat !='' && isset($long) && $long !='') {
		return $lat.'###'.$long;
	} else {
		return 0;
	}
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
	$int = hexdec('FFFFFF');
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

/* Chart Functions Starts */
	/* To get inbetween dates from the given dates */
function createDateRangeArray($strDateFrom,$strDateTo)//date format should be ('Y-m-d')
{
	// takes two dates formatted as YYYY-MM-DD and creates an
    // inclusive array of the dates between the from and to dates.

    // could test validity of dates here but I'm already doing
    // that in the main script

    $aryRange=array();
    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));
    if ($iDateTo>=$iDateFrom)
    {
        array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
        while ($iDateFrom<$iDateTo)
        {
            $iDateFrom+=86400; // add 24 hours
            array_push($aryRange,date('Y-m-d',$iDateFrom));
        }
    }
    return $aryRange;
}
function getStringForDay($dataArray,$start_date='',$end_date='',$type='') {
	foreach($dataArray as $key => $value) {
			list($month,$day,$year) = explode('/',$value["day"]);
			$date_format = date('Y-m-d',strtotime($month.'/'.$day.'/'.$year));
			$data_content_array[$date_format] =$value["TotalPrice"];
		}
		if(isset($start_date) && $start_date=='' && isset($end_date) && $end_date=='') {
			$last_date =  date('m-t-Y');
			$curr_date = date('Y-m-d');
			$cur_month = date('m');
			$cur_year = date('Y');
			if($type == 1){
				$start_date = date('Y-m-d',strtotime("-7days"));
				$end_date = date('Y-m-d',strtotime($curr_date));
			}
			else {
				$start_date = date('Y-m-d',strtotime($cur_year.'-'.$cur_month.'-01'));
				$end_date = date('Y-m-d',strtotime($curr_date));
			}
		} 
		$date_differ_array = createDateRangeArray($start_date,$end_date);
		$total_count = count($date_differ_array);
		foreach($date_differ_array as $key => $value) {
			if($value!=$start_date && $value!=$end_date) {
				$exclude_mrgindate_differ_array[$key] = $value;
			}
		}
		if($type == 1){
			foreach($date_differ_array as $key => $value) {
				list($year,$month,$date) = explode('-',$value);
				$month_array[] = $month;
				if(array_key_exists($value,$data_content_array)) {
					$category_array[$value] = $data_content_array[$value];
				} else {
					$category_array[$value] = 0;
				}
			}
		}
		else if($total_count<=31) {
			foreach($date_differ_array as $key => $value) {
				list($year,$month,$date) = explode('-',$value);
				$month_array[] = $month;
				if(array_key_exists($value,$data_content_array)) {
					$category_array[$value] = $data_content_array[$value];
				} else {
					$category_array[$value] = 0;
				}
			}
		} else {
			$random_category_keys = array_rand($exclude_mrgindate_differ_array,28);
			$random_date_differ_array[] = $start_date;
			foreach($random_category_keys as $key => $value) {
				$random_date_differ_array[] = $date_differ_array[$value];
			}
			$random_date_differ_array[] = $end_date;
			foreach($random_date_differ_array as $key => $value) {
				list($year,$month,$date) = explode('-',$value);
				$month_array[] = $month;
				if(array_key_exists($value,$data_content_array)) {
					$category_array[$value] = $data_content_array[$value];
				} else {
					$category_array[$value] = 0;
				}
			}
		}
		$month_array = array_unique($month_array);
		foreach($category_array as $date => $value) {
			if(isset($month_array) && is_array($month_array) && count($month_array)==1) {
				list($year,$month,$day) = explode('-',$date);
				$x_labels[] = $day;
			} else { 
				$date_format = date('M d',strtotime($date));
				if($type == 1)
					$x_labels[] = "".$date_format."";
				else
					$x_labels[] = "'".$date_format."'";
			}
			$values[] = $value;
			
		}
		$x_labels_string = implode(',',$x_labels);
		$value_string = implode(',',$values);
		return $x_labels_string.'###'.$value_string;
		
		
}
function getStringForMonth($dataArray) {
	foreach($dataArray as $key => $value) {
		$month_arr[] = $value["month"];
		$value_arr[$value["month"]] = $value["TotalPrice"];
	}
	$month_arr = array_unique($month_arr);
	for($i=1;$i<=12;$i++) {
		if(in_array($i,$month_arr)) {
			$category_arr[$i] = $value_arr[$i];
		} else {
			$category_arr[$i] = 0;
		}
	}
	foreach($category_arr as $key => $value) {
		$values[] = $value;
	}
	$value_string = implode(',',$values);
	return $value_string;
}
function getStringForHour($dataArray) {
	foreach($dataArray as $key => $value) {
		$hour_arr[] = $value["hour"];
		$value_arr[$value["hour"]] = $value["TotalPrice"];
	}
	$hour_arr = array_unique($hour_arr);
	for($i=1;$i<=24;$i++) {
		if(in_array($i,$hour_arr)) {
			$category_arr[$i] = $value_arr[$i];
		} else {
			$category_arr[$i] = 0;
		}
	}
	foreach($category_arr as $key => $value) {
		$x_labels[] = $key;
		$values[] = $value;
	}
	$x_labels_string = implode(',',$x_labels);
	$value_string = implode(',',$values);
	return $x_labels_string.'###'.$value_string;
}
function GetBetween($var1="",$var2="",$pool){
	$temp1 = strpos($pool,$var1)+strlen($var1);
	$result = substr($pool,$temp1,strlen($pool));
	$dd=strpos($result,$var2);
	if($dd == 0){
	$dd = strlen($result);
	}
	return substr($result,0,$dd);
}
function getStringForDayTime($dataArray) {
//echo "<pre>"; print_r($dataArray ); echo "</pre>";
$morning_arr = $noon_arr = $evening_arr  = $value_morning_arr = array();
$value_morning_arr['Late Night'] = $value_morning_arr['Evening'] = $value_morning_arr['Afternoon'] =  $value_morning_arr['Morning']  = 0;
	foreach($dataArray as $key => $value) {
		if ($value["hour"] < 12) {
    		$morning_arr[] = $value["hour"];
			$value_morning_arr['Morning'] += $value["TotalPrice"];
		} else if ($value["hour"]  >= 12 && $value["hour"]  < 16) {
		    $noon_arr[] = $value["hour"];
			$value_morning_arr['Afternoon'] += $value["TotalPrice"];
		} else if($value["hour"] >= 16 && $value["hour"]  < 22) {
		   $evening_arr[] = $value["hour"];
		   $value_morning_arr['Evening'] += $value["TotalPrice"];
		}
		 else{
		   $night_arr[] = $value["hour"];
		   $value_morning_arr['Late Night'] += $value["TotalPrice"];
		}
		//$value_morning_arr[] = $value["TotalPrice"];
	}
	//echo "<pre>"; print_r( $value_morning_arr); echo "</pre>";
	foreach($value_morning_arr as $key => $value) {
		$x_labels[] = $key;
		$values[] 	= $value;
	}
	$x_labels_string 	= implode(',',$x_labels);
	$value_string 		= implode(',',$values);
	return $x_labels_string.'###'.$value_string;
}
/* Chart Function Ends */

function formOpeningHours($openinghours) {
	//echo "<pre>"; echo print_r($openinghours); echo "</pre>";
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

function time_ago($date,$granularity=2) {
    $date 	= strtotime($date);
    $retval	=	'';
	$difference = time() - $date;
    $periods = array('decade' => 315360000,
        'year' => 31536000,
        'month' => 2628000,
        'week' => 604800, 
        'day' => 86400,
        'hr' => 3600,
        'min' => 60,
        'sec' => 1);
    if ($difference < 5) { // less than 5 seconds ago, let's say "just now"
        $retval = "just now";
        return $retval;
    } else { 
        foreach ($periods as $key => $value) {
            if ($difference >= $value) {
                $time = floor($difference/$value);
                $difference %= $value;
                $retval .= ($retval ? ' ' : '').$time.' ';
                $retval .= (($time > 1) ? $key.'s' : $key);
                $granularity--;
            }
            if ($granularity == '0') { break; }
			if(!empty($retval))
				break;
        }
        return $retval.' ago';      
    }
}
function price_fomat($price_val){
	$price = number_format($price_val,2,'.',',');
	if(strstr($price,'$'))
		return $price;
	else
		return '$'.$price;
}
function getStringForDayProduct($dataArray,$start_date='',$end_date='',$type='') {
	usort( $dataArray, function( $a, $b) {  
	    if( $a['TotalOrders']== $b['TotalOrders']) 
	        return 0; 
	    return $a['TotalOrders'] < $b['TotalOrders'] ? 1 : -1; // Might need to switch 1 and -1
	});
	foreach($dataArray as $key => $value) {
	   if($key <= 9){
			$x_labels[] = $value['Name'];
			$y_labels[] = $value['TotalOrders'];
	   }
	}
	$x_labels_string = implode(',',$x_labels);
	$value_string = implode(',',$y_labels);
	return $x_labels_string.'###'.$value_string;
		
		
}

/*
function getCurrencyFromCountry($country_name)
{
	$url = "http://www.webservicex.net/country.asmx/GetCurrencyByCountry?CountryName=".$country_name;
	
	try{
		$page = file_get_contents($url);
		$service_xml = new SimpleXMLElement($page);
		$xml_object = simplexml_load_string($service_xml);
		if (isset($xml_object->Table)) {
			return $xml_object->Table;
		}
		else  {
			return 0;
		}
	}
	catch(Exception $e){
		return 0;
	}
	
}
*/
?>