<?php
class UserModel extends Model
{
   function getUserList($fields,$condition)
	{
		$limit_clause	=	$join	=	$joincon	=	$lcondition	=	'';
		$sorting_clause = ' u.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['tuplit_sess_user_platform']) && $_SESSION['tuplit_sess_user_platform'] != '')
			$condition .= " and u.Platform  = ".$_SESSION['tuplit_sess_user_platform'];
		if(isset($_SESSION['tuplit_sess_user_name']) && $_SESSION['tuplit_sess_user_name'] != '')
			$condition .= " and ( u.FirstName LIKE '%".$_SESSION['tuplit_sess_user_name']."%' || u.LastName LIKE '%".$_SESSION['tuplit_sess_user_name']."%' )";
		if(isset($_SESSION['tuplit_sess_email']) && $_SESSION['tuplit_sess_email'] != '')
			$condition .= " and u.Email LIKE '".$_SESSION['tuplit_sess_email']."%' ";
		if(isset($_SESSION['tuplit_sess_user_status']) && $_SESSION['tuplit_sess_user_status'] != '')
			$condition .= " and u.Status = '".$_SESSION['tuplit_sess_user_status']."' ";
		if(isset($_SESSION['tuplit_sess_country']) && $_SESSION['tuplit_sess_country'] != '')
			$condition .= " and u.Country LIKE '%".$_SESSION['tuplit_sess_country']."%' ";
		if(isset($_SESSION['tuplit_sess_location']) && $_SESSION['tuplit_sess_location'] != '')
			$condition .= " and u.Location LIKE '%".$_SESSION['tuplit_sess_location']."%' ";
		
		if(isset($_SESSION['tuplit_sess_spent7']) && $_SESSION['tuplit_sess_spent7'] != '') {
			$fields	.=	' , sum(o.TotalPrice) as TotalOrderAmount ';
			if(empty($join))
				$join	.=	' left join orders o on (u.id = o.fkUsersId) ';
			if(empty($joincon))
				$joincon.= 	" HAVING sum(o.TotalPrice) >= '".$_SESSION['tuplit_sess_spent7']."'";
			else
				$joincon.= 	" and sum(o.TotalPrice) >= '".$_SESSION['tuplit_sess_spent7']."'";
			if(empty($lcondition))
				$lcondition	.=	" and o.OrderStatus = 1 and o.TransactionId !='' and date(o.OrderDate) >= '2014-07-31'";
		}
		
		if(isset($_SESSION['tuplit_sess_spent']) && $_SESSION['tuplit_sess_spent'] != '') {
			$fields	.=	' , sum(o.TotalPrice) as TotalOrderAmount ';
			if(empty($join))
				$join	.=	' left join orders o on (u.id = o.fkUsersId) ';			
			if(empty($joincon))
				$joincon.= 	" HAVING sum(o.TotalPrice) >= '".$_SESSION['tuplit_sess_spent']."'";
			else
				$joincon.= 	" and sum(o.TotalPrice) >= '".$_SESSION['tuplit_sess_spent']."'";
			if(empty($lcondition))
				$lcondition	.=	" and o.OrderStatus = 1 and o.TransactionId !='' ";
		}
		
		if(isset($_SESSION['tuplit_sess_order']) && $_SESSION['tuplit_sess_order'] != '') {
			$fields	.=	', count(o.id) as TotalOrders ';
			if(empty($join))
				$join	.=	' left join orders o on (u.id = o.fkUsersId) ';			
			if(empty($joincon))
				$joincon.= 	" HAVING count(o.id) ='".$_SESSION['tuplit_sess_order']."'";
			else
				$joincon.= 	" and count(o.id) ='".$_SESSION['tuplit_sess_order']."'";
			if(empty($lcondition))
				$lcondition	.=	' and o.OrderStatus = 1 ';
		}			
			
		if(isset($_SESSION['tuplit_sess_user_registerdate']) && $_SESSION['tuplit_sess_user_registerdate'] != '')
			$condition .= " and date(u.DateCreated) = '".$_SESSION['tuplit_sess_user_registerdate']."'";	
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->userTable} as u	".$join."
				WHERE 1".$condition." ".$lcondition." group by u.id ".$joincon." ORDER BY ".$sorting_clause." ".$limit_clause;
				
				
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;
	}
   function getTotalRecordCount()
	{
		$result = $this->sqlCalcFoundRows();
        return $result;
	}
	function updateUserDetails($update_string,$condition){
		$sql	 =	"update {$this->userTable}  set ".$update_string." where ".$condition;
		//echo "<br/>======".$sql;
		$this->updateInto($sql);
	}
	function selectUserDetails($field,$condition){
		$sql	 =	"select ".$field." from {$this->userTable} where ".$condition;
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}

	function insertUserDetails($register_values){
																  //UserName 			= '".$register_values['UserName']."',
		$sql	 =	"insert into  {$this->userTable}  set FirstName	        = '".$register_values['FirstName']."',
														  LastName	        = '".$register_values['LastName']."',

														  Email 			= '".$register_values['Email']."',
														  FBId				= '".$register_values['FbId']."',
														  GooglePlusId 		= '".$register_values['GooglePlusId']."',
														  Location	 		= '".$register_values['Location']."',
														  Country	 		= '".$register_values['Country']."',
														  ZipCode	 		= '".$register_values['ZipCode']."',
														  PinCode	 		= '".$register_values['PinCode']."',
														  CellNumber	 	= '".$register_values['CellNumber']."',
														  IpAddress 		= '".$register_values['ipaddress']."',
														  Status 			= 1,
														  PushNotification	= 1,
														  SendCredit		= 1,
														  RecieveCredit		= 1,
														  BuySomething		= 1,
														  DealsOffers		= 1,
														  Sounds			= 1,
														  Passcode			= 1,
														  PaymentPreference	= 1,
														  RememberMe		= 1,
														  Platform			= 0,
														  Gender 			= '".$register_values['Gender']."',
														  DOB 				= '".$register_values['DOB']."',
														  Age 				= '".$register_values['Age']."',
														  DateCreated 		= '".date('Y-m-d H:i:s')."',
														  DateModified		= '".date('Y-m-d H:i:s')."'";
		$this->result = $this->insertInto($sql);
		$insertId = $this->sqlInsertId();
        return $insertId;
	}
	function selectWordDetails(){
		$sql	 =	"select * from {$this->wordsTable} where 1 order by rand() limit 1 ";
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	function getUserDetails($fields, $condition)
	{
		$sql	 =	"SELECT ".$fields." FROM {$this->userTable} WHERE ".$condition;
		echo "================>".$sql."</br>";
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	function deleteUserReleatedEntries($delete_id)
	{
		$sql	 =	"update {$this->userTable}  set Status = 3 where id in (".$delete_id.")";
		$this->updateInto($sql);
	}
	function getSliderImageDetails($fields, $condition)
	{
		$sql	 =	"SELECT ".$fields." FROM {$this->SliderImageTable} WHERE ".$condition;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	function deleteSlider($delete_id)
	{
		$sql	 =	"update {$this->SliderImageTable}  set Status = 2 where id in (".$delete_id.")";
		$this->updateInto($sql);
	}
	function insertHomeSlide($k)
	{
		$sql	 =	"INSERT INTO {$this->SliderImageTable}  set  `Order`	    = '".$k."',
																 SliderType		=	1,
																 Status			=	1";
		$this->result = $this->insertInto($sql);
		$insertId = $this->sqlInsertId();
        return $insertId;
	}
	function updateSliderDetails($update_string,$condition){
		$sql	 =	"update {$this->SliderImageTable}  set ".$update_string." where ".$condition;
		//echo "<br/>======".$sql;
		$this->updateInto($sql);
	}
	function insertTutorialSlide($k)
	{
		$sql	 =	"INSERT INTO {$this->SliderImageTable}  set  `Order`	    = '".$k."',
																 SliderType		=	2,
																 Status			=	1";
		$this->result = $this->insertInto($sql);
		$insertId = $this->sqlInsertId();
        return $insertId;
	}
	function getUserListPN($fields,$condition)
	{
		$limit_clause='';
		$sorting_clause = ' id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['tuplit_sess_user_platform']) && $_SESSION['tuplit_sess_user_platform'] != '')
			$condition .= " and u.Platform  = ".$_SESSION['tuplit_sess_user_platform'];
		if(isset($_SESSION['tuplit_sess_user_name']) && $_SESSION['tuplit_sess_user_name'] != '')
			$condition .= " and ( u.FirstName LIKE '%".$_SESSION['tuplit_sess_user_name']."%' || u.LastName LIKE '%".$_SESSION['tuplit_sess_user_name']."%' )";
		if(isset($_SESSION['tuplit_sess_email']) && $_SESSION['tuplit_sess_email'] != '')
			$condition .= " and u.Email LIKE '".$_SESSION['tuplit_sess_email']."%' ";
		if(isset($_SESSION['tuplit_sess_user_status']) && $_SESSION['tuplit_sess_user_status'] != '')
			$condition .= " and u.Status = '".$_SESSION['tuplit_sess_user_status']."' ";
		if(isset($_SESSION['tuplit_sess_country']) && $_SESSION['tuplit_sess_country'] != '')
			$condition .= " and u.Country LIKE '%".$_SESSION['tuplit_sess_country']."%' ";
		if(isset($_SESSION['tuplit_sess_location']) && $_SESSION['tuplit_sess_location'] != '')
			$condition .= " and u.Location LIKE '%".$_SESSION['tuplit_sess_location']."%' ";
		
		$sql = "select ".$fields." from {$this->userTable} as u
				WHERE 1 ".$condition." ORDER BY ".$sorting_clause." ";
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;
	}
	function getDevicetoken($fields, $condition){
		$sql	 =	"select ".$fields." from {$this->devicetokenTable} where ".$condition;
		$result = 	$this->sqlQueryArray($sql);
		if($result) return $result;
			else false;
	}
	function updateBadge($token){
		$sql	 =	"update {$this->devicetokenTable} set BadgeCount = BadgeCount + 1 where DeviceToken = '".$token."'";
		$result = 	$this->updateInto($sql);
	}
	/*-------Customers------*/
	function getCustomerList($fields,$condition,$limit)
	{
		$limit_clause	=	$join	=	$joincon	=	$lcondition	=	'';
		if(isset($limit) && !empty($limit)){
			$limit_clause	= "limit ".$limit.",30";
		}else{
			$limit_clause	= "limit 0,30";
		}
		$sorting_clause = ' u.id desc';
		$sql 	= "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->userTable} as u	
				WHERE 1".$condition." ORDER BY ".$sorting_clause." ".$limit_clause;
		$result	=	$this->sqlQueryArray($sql);
		//echo $sql;
		if(count($result) == 0) return false;
		else return $result;
	}
	function getCustomerTransaction($field,$condition)
	{
		$sql 	= 	"SELECT  count(o.id) as TotalOrders,SUM(TotalPrice) as TotalPrice ".$field." from orders as o 
					LEFT JOIN users as u on (u.id = o.fkUsersId)
					where 1 and  o.OrderStatus IN (1) ".$condition;	
		//echo '-->'. $sql .'<br>';
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;	
	}
	function getTransactionDetails($fields,$condition)
	{
		$sql 	= 	"SELECT ".$fields." FROM `orders` as o
					LEFT JOIN merchants as m on (m.id = o.fkMerchantsId)
					where 1 and  o.OrderStatus IN (1) ".$condition." group by fkMerchantsId
					order by Total desc limit 0,5";	
		//echo '-->'. $sql .'<br>';
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;	
	}
	function getUserDemographics($fields,$condition)
	{
		$search_condition = $left_join	= '';
		
		if(isset($_SESSION['loc_mer_name']) && $_SESSION['loc_mer_name'] != '')
			$search_condition .= " and o.fkMerchantsId  = ".$_SESSION['loc_mer_name'];
		if(isset($_SESSION['loc_mer_category']) && $_SESSION['loc_mer_category'] != ''){
			$left_join		   = "left join merchantcategories as mc on (mc.fkMerchantId = o.fkMerchantsId )";
			$search_condition .= " and mc.fkCategoriesId  = ".$_SESSION['loc_mer_category'];
		}
		if(isset($_SESSION['loc_mer_city']) && $_SESSION['loc_mer_city'] != '')
			$search_condition .= " and m.City LIKE '".$_SESSION['loc_mer_city']."%' ";
		if(isset($_SESSION['loc_mer_price']) && $_SESSION['loc_mer_price'] != '')
			$search_condition .= " and o.TotalPrice = '".$_SESSION['loc_mer_price']."' ";
		$sql 	= 	"Select ".$fields." from users as u
					left join `orders` as o ON (o.fkUsersId = u.id and  o.OrderStatus IN (1))
					left join merchants as m on (m.id = o.fkMerchantsId)
					".$left_join."
					where 1 ".$search_condition." ".$condition." ";
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;	
	}
}
?>