<?php
class MerchantModel extends Model
{
   function getMerchantList($fields,$condition)
	{
		$limit_clause='';
		$sorting_clause = ' m.id desc';
		$join = '';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['mer_sess_name']) && $_SESSION['mer_sess_name'] != '')
			$condition .= " and ( m.FirstName LIKE '%".$_SESSION['mer_sess_name']."%' || m.LastName LIKE '%".$_SESSION['mer_sess_name']."%' )";
		if(isset($_SESSION['mer_sess_email']) && $_SESSION['mer_sess_email'] != '')
			$condition .= " and m.Email LIKE '".$_SESSION['mer_sess_email']."%' ";
		if(isset($_SESSION['mer_sess_company']) && $_SESSION['mer_sess_company'] != '')
			$condition .= " and m.CompanyName LIKE '%".$_SESSION['mer_sess_company']."%' ";
		if(isset($_SESSION['mer_sess_location']) && $_SESSION['mer_sess_location'] != '')
			$condition .= " and m.Address LIKE '%".$_SESSION['mer_sess_location']."%' ";
		if(isset($_SESSION['mer_sess_status']) && $_SESSION['mer_sess_status'] != '')
			$condition .= " and m.Status =".$_SESSION['mer_sess_status']." ";
		if(isset($_SESSION['mer_sess_Category']) && $_SESSION['mer_sess_Category'] != '') {
			$join = " left join {$this->merchantcategoriesTable} as mca on (mca.fkCategoriesId =".$_SESSION['mer_sess_Category'].")";
			$condition .= " and mca.fkMerchantId = m.id ";
		}
		
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->merchantTable} as m	".$join." 
				WHERE 1".$condition." group by m.id ORDER BY ".$sorting_clause." ".$limit_clause;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		//echo "<pre>";   print_r($result);   echo "</pre>";
		if(count($result) == 0) return false;
		else {	
			return $result;
		}
	}

   function getTotalRecordCount() {
		$result = $this->sqlCalcFoundRows();
		return $result;
	}

	function deleteMerchantEntries($delete_id) {
		$sql	 =	"update {$this->merchantTable}  set Status ='3' , Icon='', Image='' where id in (".$delete_id.")";
		//echo "<br>".$sql;
		$this->updateInto($sql);
	}	
	
	function approveMerchant($id) {
		$sql	 =	"update {$this->merchantTable}  set Status ='1' where id in (".$id.")";
		//echo "<br>".$sql;
		$this->updateInto($sql);
	}
	
	function selectMerchantDetail($id){
		$sql	 =	"select * from {$this->merchantTable} where id=".$id;
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	
	function selectCategoryDetail($id,$cid){
		$sql	 =	"select * from {$this->merchantcategoriesTable} where fkMerchantId=".$id." and fkCategoriesId=".$cid;
		//echo "<br/>======".$sql; die();
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	
	function selectMerchantCategory($id){
		$sql	 =	"select group_concat(fkCategoriesId) as cat_id from {$this->merchantcategoriesTable} where fkMerchantId=".$id." group by fkMerchantId";
		//$sql	 =	"select fkCategoriesId from {$this->merchantcategoriesTable} where fkMerchantId=".$id;
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	
	function getMerchantNotApproved(){
		$sql	 =	"select count(id) as total from {$this->merchantTable} where Status=0";
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	
	function updateDetails($data,$icon,$img,$openhours){
	
		$update_string = '';
		if(!empty($data['FirstName']))
			$update_string .= " FirstName ='".$data['FirstName']."',";
		if(!empty($data['LastName']))
			$update_string .= " LastName ='".$data['LastName']."',";
		if(!empty($data['Email']))
			$update_string .= " Email ='".$data['Email']."',";
		if(!empty($data['CompanyName']))
			$update_string .= " CompanyName ='".$data['CompanyName']."',";
		if(!empty($data['PhoneNumber']))
			$update_string .= " PhoneNumber ='".$data['PhoneNumber']."',";
		if(!empty($data['WebsiteUrl']))
			$update_string .= " WebsiteUrl ='".$data['WebsiteUrl']."',";
		if(!empty($data['Location']))
			$update_string .= " Location ='".$data['Location']."',";
		$update_string .= " ItemsSold ='".$data['ItemsSold']."',";
		if(!empty($data['Address'])){
			$update_string .= " Address ='".$data['Address']."',";
			$latlong = getLatLngFromAddress($data['Address']) ;
				$latlngArray = explode('###',$latlong);
			if(isset($latlngArray) && is_array($latlngArray) && count($latlngArray) > 0){
				if(isset($latlngArray[0]))
					$update_string .= " Latitude  =	'". trim($latlngArray[0])."',";
				if(isset($latlngArray[1]))
					$update_string .= " Longitude =	'".trim($latlngArray[1])."',"; 
			}
		}
		if(!empty($icon))
			$update_string .= " Icon ='".$icon."',";
		if(!empty($img))
			$update_string .= " Image ='".$img."',";
		if(!empty($data['Description']))
			$update_string .= " Description ='".$data['Description']."',";
		if(!empty($data['ShortDescription']))
			$update_string .= " ShortDescription ='".$data['ShortDescription']."',";
		if(!empty($data['DiscountTier']))
			$update_string .= " DiscountTier ='".$data['DiscountTier']."',";
		if(!empty($data['min_price']) && !empty($data['max_price']))
			$update_string .= " PriceRange ='".$data['min_price'].",".$data['max_price']."',";			
		if(!empty($openhours))
			$update_string .= " OpeningHours ='".$openhours."',";
		if(!empty($data['min_price']) && !empty($data['max_price']))
			$update_string .= " PriceRange ='".$data['min_price'].",".$data['max_price']."',";
		if(!empty($data['categorySelected'])) {
		    //echo $data['categorySelected'];
			$sqldel = "delete from {$this->merchantcategoriesTable} where fkCategoriesId not in (".$data['categorySelected'].") and fkMerchantId = '".$data['merchant_id']."'";
			//echo "========>".$sqldel;
			$this->updateInto($sqldel);
			
			$category = explode(',',$data['categorySelected']);	
			foreach($category as $val) {
				$res = array();
				$res = $this->selectCategoryDetail($data['merchant_id'],$val);
				if(count($res) == 0) {
					$sql1 =	"insert into {$this->merchantcategoriesTable}(fkMerchantId,fkCategoriesId,DateCreated) values('".$data['merchant_id']."','".$val."','".date('Y-m-d H:i:s')."')";
					//echo "<br/>======".$sql1; 
					$this->updateInto($sql1);
				}
			}
		}			
		//echo'<pre>';print_r($update_string);echo'</pre>';die();
		$update_string = rtrim($update_string, ",");			
		$sql =	"update {$this->merchantTable}  set ".$update_string." where id=".$data['merchant_id'];
		//echo "<br/>======".$sql; die();
		$this->updateInto($sql);
		//die();	
	}
	function updateShoppingHours($data){		
		
		//echo'<pre>';print_r($data);echo'</pre>';
		//DIE();
		if(isset($data['merchant_id']) && $data['merchant_id'] !=''){
			if(isset($data['samehours']) && $data['samehours'] == 'on') {
				$sql = "update {$this->merchantshoppingTable} set 
									fkMerchantId 	= '".$data['merchant_id']."',
									OpeningDay 		= '0',
									Start 			= '".strtoupper($data["from1_0"])."',
									End 			= '".strtoupper($data["to1_0"])."',
									DateType 		= '1',
									DateCreated 	= '".date('Y-m-d H:i:s')."'
									where id 		= '".strtoupper($data["id_0"])."'
									";
				$this->updateInto($sql);
				for($i=1;$i<=6;$i++) {
					$sql = "update {$this->merchantshoppingTable} set 
									fkMerchantId 	= '".$data['merchant_id']."',
									OpeningDay 		= '".$i."',
									Start 			= '',
									End 			= '',
									DateType 		= '0',
									DateCreated 	= '".date('Y-m-d H:i:s')."'
									where id 		= '".strtoupper($data["id_".$i.""])."'
									";
					$this->updateInto($sql);
				}
			}
			else{
				for($i=0;$i<=6;$i++) {
					$sql = "update {$this->merchantshoppingTable} set 
										fkMerchantId 	= '".$data['merchant_id']."',
										OpeningDay 		= '".$i."',
										Start 			= '".strtoupper($data["from1_".$i.""])."',
										End 			= '".strtoupper($data["to1_".$i.""])."',
										DateType 		= '0',
										DateCreated 	= '".date('Y-m-d H:i:s')."'
										where id 		= '".strtoupper($data["id_".$i.""])."'
										";
					$this->updateInto($sql);
				}
			}
		}
		else{
			if(isset($data['samehours']) && $data['samehours'] == 'on') {
				$sql = "insert into {$this->merchantshoppingTable} set 
									fkMerchantId 	= '".$data['merchant_id']."',
									OpeningDay 		= '0',
									Start 			= '".strtoupper($data["from1_0"])."',
									End 			= '".strtoupper($data["to1_0"])."',
									DateType 		= '1',
									DateCreated 	= '".date('Y-m-d H:i:s')."'
									where id 		= '".strtoupper($data["id_0"])."'
									";
				$this->updateInto($sql);
				for($i=1;$i<=6;$i++) {
					$sql = "insert into {$this->merchantshoppingTable} set 
									fkMerchantId 	= '".$data['merchant_id']."',
									OpeningDay 		= '".$i."',
									Start 			= '',
									End 			= '',
									DateType 		= '0',
									DateCreated 	= '".date('Y-m-d H:i:s')."'
									where id 		= '".strtoupper($data["id_".$i.""])."'
									";
					$this->updateInto($sql);
				}
			}
			else{
				for($i=0;$i<=6;$i++) {
					$sql = "insert into {$this->merchantshoppingTable} set 
										fkMerchantId 	= '".$data['merchant_id']."',
										OpeningDay 		= '".$i."',
										Start 			= '".strtoupper($data["from1_".$i.""])."',
										End 			= '".strtoupper($data["to1_".$i.""])."',
										DateType 		= '0',
										DateCreated 	= '".date('Y-m-d H:i:s')."'
										";
					$this->updateInto($sql);
				}
			}
		}
		//die();
		/*$sql	 =	"select * from {$this->merchantshoppingTable} where fkMerchantId =".$data['merchant_id'];		
		$result = 	$this->sqlQueryArray($sql);
		if($result) {			
			$sql1	= "delete from {$this->merchantshoppingTable} where fkMerchantId=".$data['merchant_id'];
			//echo $sql1."<br>";
			$this->updateInto($sql1);			
			if(isset($data['samehours']) && $data['samehours'] == 'on') {
				$sql2	= "insert into {$this->merchantshoppingTable}(fkMerchantId,OpeningDay,Start,End,DateType,DateCreated) values('".$data['merchant_id']."','0','".strtoupper($data['from1_0'])."','".strtoupper($data['to1_0'])."','1','".date('Y-m-d H:i:s')."')";
				//echo $sql2."<br>";
				$this->updateInto($sql2);
			} else {
				for($i=0;$i<=6;$i++) {
					$sql2	= "insert into {$this->merchantshoppingTable}(fkMerchantId,OpeningDay,Start,End,DateType,DateCreated) values('".$data['merchant_id']."','".$i."','".strtoupper($data["from1_".$i.""])."','".strtoupper($data["to1_".$i.""])."','0','".date('Y-m-d H:i:s')."')";
					//echo $sql2."<br>";
					$this->updateInto($sql2);
				}
			}
		} else {
			if(isset($data['samehours']) && $data['samehours'] == 'on') {				
				$sql2	= "insert into {$this->merchantshoppingTable}(fkMerchantId,OpeningDay,Start,End,DateType,DateCreated) values('".$data['merchant_id']."','0','".strtoupper($data['from1_0'])."','".strtoupper($data['to1_0'])."','1','".date('Y-m-d H:i:s')."')";
				//echo $sql2."<br>";
				$this->updateInto($sql2);
			} else {
				for($i=0;$i<=6;$i++) {
					$sql2	= "insert into {$this->merchantshoppingTable}(fkMerchantId,OpeningDay,Start,End,DateType,DateCreated) values('".$data['merchant_id']."','".$i."','".strtoupper($data["from1_".$i.""])."','".strtoupper($data["to1_".$i.""])."','0','".date('Y-m-d H:i:s')."')";
					//echo $sql2."<br>";
					$this->updateInto($sql2);
				}
			}
		}	*/	
	}	
	
	function getCategories() {
		$sql	 =	"select Id,CategoryName,CategoryIcon from {$this->categoryTable} where Status=1 ORDER BY CategoryName asc";
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	function selectMerchantDetails($field,$condition){
		$sql	 =	"select ".$field." from {$this->merchantTable} where ".$condition;
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	
	function selectOpeningHoursDetail($merchantId){
		$sql	 =	"select * from {$this->merchantshoppingTable} where fkMerchantId=".$merchantId;
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
}
?>