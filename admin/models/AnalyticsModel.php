<?php
class AnalyticsModel extends Model
{
   function getAnalyticsList($fields,$condition)
	{
		
		$limit_clause='';
		$having_cond = ' Having 1 ';
		$sorting_clause = ' o.OrderDate desc';
		//if(!empty($_SESSION['ordertype']))
			//$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		
		if(isset($_SESSION['tuplit_sess_order_user_name']) && $_SESSION['tuplit_sess_order_user_name'] != '')
			$condition .= " and ( u.FirstName LIKE '%".$_SESSION['tuplit_sess_order_user_name']."%' || u.LastName LIKE '%".$_SESSION['tuplit_sess_order_user_name']."%' )";

		if(isset($_SESSION['tuplit_sess_order_visit']) && $_SESSION['tuplit_sess_order_visit'] != '')
			$having_cond .= " and count(o.id) = '".$_SESSION['tuplit_sess_order_visit']."' ";
			
		if(isset($_SESSION['tuplit_sess_order_total_spend']) && $_SESSION['tuplit_sess_order_total_spend'] != '')
			$having_cond .= " and SUM(o.TotalPrice) = '".$_SESSION['tuplit_sess_order_total_spend']."' ";
			
			
		$sql = "select SQL_CALC_FOUND_ROWS u.id as user_id, ".$fields." from {$this->userTable} as u	
				left join orders as o on  (u.id	= o.fkUsersId )
				WHERE 1".$condition." group by u.id ".$having_cond." ORDER BY ".$sorting_clause." ".$limit_clause;
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
	
	function getOrdersDetail($fields,$id,$order_by,$limit){
		$sql	 =	"select ".$fields." from {$this->orderTable} where fkUsersId=".$id." order by id ".$order_by."  ".$limit;
		//echo "<br/>======".$sql; die();
		$result = 	$this->sqlQueryArray($sql);
		if($result) return $result;
		else false;
	}	
}
?>