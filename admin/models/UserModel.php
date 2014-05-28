<?php
class UserModel extends Model
{
   function getUserList($fields,$condition)
	{
		$limit_clause='';
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
			
		/*if(isset($_SESSION['tuplit_sess_spent']) && $_SESSION['tuplit_sess_spent'] != '')
			$condition .= " and u.Location LIKE '%".$_SESSION['tuplit_sess_spent']."%' ";
		if(isset($_SESSION['tuplit_sess_spent7']) && $_SESSION['tuplit_sess_spent7'] != '')
			$condition .= " and u.Location LIKE '%".$_SESSION['tuplit_sess_spent7']."%' ";*/
		if(isset($_SESSION['tuplit_sess_order']) && $_SESSION['tuplit_sess_order'] != '')
			$condition .= " and u.Oders >='".$_SESSION['tuplit_sess_order']."'";
			
			
		if(isset($_SESSION['tuplit_sess_user_registerdate']) && $_SESSION['tuplit_sess_user_registerdate'] != '')
			$condition .= " and date(u.DateCreated) = '".$_SESSION['tuplit_sess_user_registerdate']."'";	
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->userTable} as u	
				WHERE 1".$condition." group by u.id ORDER BY ".$sorting_clause." ".$limit_clause;
		//echo "<br/>======".$sql;
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
														  Platform			= 0,
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
}
?>