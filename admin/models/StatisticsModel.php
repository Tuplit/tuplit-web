
<?php
class StatisticsModel extends Model
{
   	
   function getTotalRecordCount()
	{
		$result = $this->sqlCalcFoundRows();
        return $result;
	}	
	function getTopUsersList($fields,$condition = '')
	{	
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->orderTable} as o 
		left join users as u on (u.id	= o.fkUsersId ) 
		WHERE 1".$condition ;
		$result	=	$this->sqlQueryArray($sql);	
		//echo "Query==>".$sql; die();
		if(count($result) == 0) return false;
		else return $result;	
	}
	function getTopMerchantsList($fields,$condition = '')
	{
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->orderTable} as o 
		left join merchants as m on (m.id = o.fkMerchantsId)
		WHERE 1 ".$condition;
		$result	=	$this->sqlQueryArray($sql);	
		//echo "Query==>".$sql;
		if(count($result) == 0) return false;
		else return $result;	
	}
	function getRegisteredUsers($fields,$condition = '')
	{
		//and Status = 1
		$sql = "select ".$fields." from {$this->userTable}  
		WHERE 1 ".$condition;
		$result	=	$this->sqlQueryArray($sql);	
		//echo "Query==>".$sql;
		if(count($result) == 0) return false;
		else return $result;	
	}
	function getRegisteredMerchants($fields,$condition = '')
	{
		$sql = "select ".$fields." from {$this->merchantTable} 
		WHERE 1 ".$condition;
		$result	=	$this->sqlQueryArray($sql);	
		if(count($result) == 0) return false;
		else return $result;	
	}	
	function getOverallStatOrderList($condition = '')
	{
		$fields = "*,count(id) AS totalcount, sum(`TotalPrice`) AS Price";
		$sql = "select ".$fields." from {$this->orderTable} 
				WHERE 1 ".$condition;	
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}
	function getorderlistbydate($fields,$condition = '')
	{
		$sql = "select ".$fields." from {$this->orderTable} 
				WHERE 1 ".$condition;	
				//echo $sql; die();
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}
}
?>