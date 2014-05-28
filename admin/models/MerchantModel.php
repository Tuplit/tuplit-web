<?php
class MerchantModel extends Model
{
   function getMerchantList($fields,$condition)
	{
		$limit_clause='';
		$sorting_clause = ' m.id desc';
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
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->merchantTable} as m	
				WHERE 1".$condition." group by m.id ORDER BY ".$sorting_clause." ".$limit_clause;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		//echo "<pre>";   print_r($result);   echo "</pre>";
		if(count($result) == 0) return false;
		else return $result;		
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
	
	function updateDetails($data,$icon,$img){
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
		if(!empty($data['Address']))
			$update_string .= " Address ='".$data['Address']."',";
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
			
		if(!empty($data['categorySelected'])) {
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
		
		$update_string = rtrim($update_string, ",");			
		$sql =	"update {$this->merchantTable}  set ".$update_string." where id=".$data['merchant_id'];
		//echo "<br/>======".$sql; die();
		$this->updateInto($sql);
		//die();	
	}	
	
	function getCategories() {
		$sql	 =	"select Id,CategoryName,CategoryIcon from {$this->categoryTable} where Status=1 ORDER BY CategoryName asc";
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
}
?>