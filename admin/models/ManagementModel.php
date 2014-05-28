<?php
class ManagementModel extends Model
{
   function getCategoryList($fields,$condition)
	{
		$limit_clause='';
		$sorting_clause = ' c.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['tuplit_sess_Category_name']) && $_SESSION['tuplit_sess_Category_name'] != '')
			$condition .= " and c.CategoryName LIKE '%".$_SESSION['tuplit_sess_Category_name']."%'";
		if(isset($_SESSION['tuplit_sess_Category_status']) && $_SESSION['tuplit_sess_Category_status'] != '')
			$condition .= " and c.Status = '".$_SESSION['tuplit_sess_Category_status']."' ";
		if(isset($_SESSION['tuplit_sess_Category_registerdate']) && $_SESSION['tuplit_sess_Category_registerdate'] != '')
			$condition .= " and date(c.DateCreated) = '".$_SESSION['tuplit_sess_Category_registerdate']."'";	
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->categoryTable} as c	
				WHERE 1".$condition." group by c.id ORDER BY ".$sorting_clause." ".$limit_clause;
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
	function updateCategoryDetails($update_string,$condition){
		$sql	 =	"update {$this->categoryTable}  set ".$update_string." where ".$condition;
		//echo "<br/>======".$sql;
		$this->updateInto($sql);
	}
	function selectCategoryDetails($field,$condition){
		$sql	 =	"select ".$field." from {$this->categoryTable} where ".$condition;
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}

	function insertCategoryDetails($register_values){
																 
		$sql	 =	"insert into  {$this->categoryTable}  set CategoryName	        = '".$register_values['CategoryName']."',
														  Status 			= 1,
														  DateCreated 		= '".date('Y-m-d H:i:s')."'";
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
	function getCategoryDetails($fields, $condition)
	{
		$sql	 =	"SELECT ".$fields." FROM {$this->categoryTable} WHERE ".$condition;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	function deleteCategoryReleatedEntries($delete_id)
	{
		$sql	 =	"update {$this->categoryTable}  set Status = 3 where id in (".$delete_id.")";
		$this->updateInto($sql);
	}
}
?>