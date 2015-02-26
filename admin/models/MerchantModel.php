<?php
class MerchantModel extends Model
{
   function getMerchantList($fields,$condition)
	{
		$limit_clause = ' ';
		$sorting_clause = 'm.id desc';
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
		
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields.", c.id as commentId from {$this->merchantTable} as m	".$join. " 
				left join comments c on (m.id = c.fkMerchantsId and c.Status = 1) 
				WHERE 1".$condition." AND UserType = 1 group by m.id ORDER BY ".$sorting_clause." ".$limit_clause;
		$result	=	$this->sqlQueryArray($sql);
		//echo "=====================>".$sql;
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
	
	function approveMerchant($id,$status) {
		$sql	 =	"update {$this->merchantTable}  set Status ='".$status."' where id in (".$id.")";
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
	
	function getProductsScold($id){
		//$sql	 =	"select * from {$this->merchantTable} where id=".$id;
		$sql	 =	"SELECT count(o.id) as TotalItems FROM {$this->orderTable} o  where o.OrderStatus=1 and  o.fkMerchantsId='".$id."'";
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
		$sql	 =	"select count(id) as total from {$this->merchantTable} where Status=0 and UserType = 1";
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	
	function updateDetails($data,$icon,$img){
	
		$update_string = '';
		if(!empty($data['Password']))
			$PassWord	=	sha1($data['Password'].ENCRYPTSALT);
		if(!empty($data['FirstName']))
			$update_string .= " FirstName ='".$data['FirstName']."',";
		if(!empty($data['LastName']))
			$update_string .= " LastName ='".$data['LastName']."',";
		if(!empty($data['Password']))
			$update_string	.=	" Password = '".$PassWord."', ";
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
		if(!empty($data['BusinessName']))
			$update_string .= " BusinessName ='".$data['BusinessName']."',";
		if(!empty($data['BusinessType']))
			$update_string .= " BusinessType ='".$data['BusinessType']."',";
		if(!empty($data['CompanyNumber']))
			$update_string .= " RegisterCompanyNumber ='".$data['CompanyNumber']."',";
		if(!empty($data['Country']))
			$update_string .= " Country ='".$data['Country']."',";
		if(!empty($data['Postcode']))
			$update_string .= " PostCode ='".$data['Postcode']."',";
		if(!empty($data['Currency']))
			$update_string .= " Currency ='".$data['Currency']."',";
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
		if(!empty($data['Products_List'])) {
			$update_string .= " DiscountType ='1', DiscountProductId ='";
			foreach($data['Products_List'] as $val) {
				if($data['Products_List'][0] == 'all') {
					$update_string .= "all";
					break;
				}
				else {
					$update_string .= $val.",";
				}
			}
			$update_string = rtrim($update_string, ",");
			$update_string .= "',";
		} else {
			$update_string .= " DiscountType='0', DiscountProductId='',";
		}
		if(!empty($data['min_price']) && !empty($data['max_price']))
			$update_string .= " PriceRange ='".$data['min_price'].",".$data['max_price']."',";			
		if(!empty($data['categorySelected'])) {
			$sqldel = "delete from {$this->merchantcategoriesTable} where fkCategoriesId not in (".$data['categorySelected'].") and fkMerchantId = '".$data['merchant_id']."'";
			$this->updateInto($sqldel);
			
			$category = explode(',',$data['categorySelected']);	
			foreach($category as $val) {
				$res = array();
				$res = $this->selectCategoryDetail($data['merchant_id'],$val);
				if(count($res) == 0) {
					$sql1 =	"insert into {$this->merchantcategoriesTable}(fkMerchantId,fkCategoriesId,DateCreated) values('".$data['merchant_id']."','".$val."','".date('Y-m-d H:i:s')."')";
					//echo $sql1;
					$this->updateInto($sql1);
				}
			}
		}			
		$update_string .= " DateModified ='".date('Y-m-d H:i:s')."',IpAddress = '".$data['ipaddress']."'";
		$update_string = rtrim($update_string, ",");	
		$sql =	"update {$this->merchantTable}  set ".$update_string." where id=".$data['merchant_id'];
		$this->updateInto($sql);
		//echo $sql;
	}
	function updateShoppingHours($data){		
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
				//echo $sql;
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
					//echo $sql;
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
										echo '-->'. $sql.'<br>';
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
		$sql	 =	"select ".$field." from {$this->merchantTable} where UserType = 1 and  ".$condition;
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
	
	function merchantOrders(){
		$sql	 =	"SELECT o.`fkMerchantsId` , m.CompanyName, count( o.id ) AS TotalOrders	FROM `orders` o
						LEFT JOIN merchants m ON ( o.`fkMerchantsId` = m.id )
						WHERE 1	AND m.Status =1	GROUP BY `fkMerchantsId` order by m.CompanyName asc";
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	
	function insertDetails($data){
		$insert_string = '';
		if(!empty($data['Password']))
			$PassWord	=	sha1($data['Password'].ENCRYPTSALT);
		if(!empty($data['FirstName']))
			$insert_string .= " FirstName ='".$data['FirstName']."',";
		if(!empty($data['LastName']))
			$insert_string .= " LastName ='".$data['LastName']."',";
		if(!empty($data['Email']))
			$insert_string .= " Email ='".$data['Email']."',";
		if(!empty($data['CompanyName']))
			$insert_string .= " CompanyName ='".$data['CompanyName']."',";
		if(!empty($data['PhoneNumber']))
			$insert_string .= " PhoneNumber ='".$data['PhoneNumber']."',";
		if(!empty($data['BusinessName']))
			$insert_string .= " BusinessName ='".$data['BusinessName']."',";
		if(!empty($data['BusinessType']))
			$insert_string .= " BusinessType ='".$data['BusinessType']."',";
		if(!empty($data['Address'])){
			$insert_string .= " Address ='".$data['Address']."',";
			$latlong = getLatLngFromAddress($data['Address']) ;
				$latlngArray = explode('###',$latlong);
			if(isset($latlngArray) && is_array($latlngArray) && count($latlngArray) > 0){
				if(isset($latlngArray[0]))
					$insert_string .= " Latitude  =	'". trim($latlngArray[0])."',";
				if(isset($latlngArray[1]))
					$insert_string .= " Longitude =	'".trim($latlngArray[1])."',"; 
			}
		}
		if(!empty($data['CompanyNumber']))
			$insert_string .= " RegisterCompanyNumber ='".$data['CompanyNumber']."',";
		if(!empty($data['Country']))
			$insert_string .= " Country ='".$data['Country']."',";
		if(!empty($data['Postcode']))
			$insert_string .= " PostCode ='".$data['Postcode']."',";
		if(!empty($data['Password']))
			$insert_string .= " Password ='".$PassWord."',";
		if(!empty($data['Currency']))
			$insert_string .= " Currency ='".$data['Currency']."',";
		if(!empty($data['ReferedBy']))
			$insert_string .= " HowHeared 	='".$data['ReferedBy']."',";
			$insert_string .= "IpAddress    = '".$data['ipaddress']."',";
			$insert_string .= " DateCreated = '".date('Y-m-d H:i:s')."',DateModified = '".date('Y-m-d H:i:s')."',
								BrowserDetails	= '".$_SERVER['HTTP_USER_AGENT']."',Status = 0";
		$insert_string = rtrim($insert_string, ",");			
		$sql =	"insert into {$this->merchantTable}  set ".$insert_string."";
		$this->result = $this->insertInto($sql);
		$insertId = $this->sqlInsertId();
		if(isset($insertId) && $insertId != '') {	
			for($i=0;$i<=6;$i++) {
				$sql = "insert into {$this->merchantshoppingTable} set 
								fkMerchantId 	= '".$insertId."',
								OpeningDay 		= '".$i."',
								Start 			= '',
								End 			= '',
								DateType 		= '0',
								DateCreated 	= '".date('Y-m-d H:i:s')."'";
				$this->result = $this->insertInto($sql);
			}
		}
        return $insertId;
	}
	
	function getMerchantImagesList($fields,$condition,$searchtxt)
	{
		if(isset($_SESSION['startlimit']) && $_SESSION['startlimit'] != ''){
			$limit_clause = ' LIMIT '.($_SESSION['startlimit']). ', 30';
		}else{
			$limit_clause='LIMIT 0,30'; 
		}
		$sorting_clause = 'm.id asc';
		if(empty($searchtxt)) {
			$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->merchantTable} as m	
					WHERE 1 and UserType=1 ".$condition.' order by m.id asc '.$limit_clause;
		} else {
			$sql = "SELECT SQL_CALC_FOUND_ROWS ".$fields." FROM products p 
					left join merchants m on (p.fkMerchantsId = m.id)
					WHERE 1 and p.ItemName like '%".$searchtxt."%' and p.Status = 1 ".$condition." group by m.id";
		}
		//echo $sql;
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else {	
			return $result;
		}
	}
	function getMerchantLocation($fields,$condition,$join){
		$sorting_clause = 'm.id desc';
		$leftjoin = '';
		if($join == 'category'){
			$leftjoin .= " left join {$this->merchantcategoriesTable} as mca on (mca.fkMerchantId = m.id)";
			$leftjoin .= " left join categories as c on (c.id = mca.fkCategoriesId)"; //
			
		}	
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->merchantTable} as m	".$leftjoin. "
				WHERE 1".$condition." AND m.UserType = 1 group by m.id ORDER BY ".$sorting_clause;
		
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else {	
			return $result;
		}
	}
		
}
?>