<?php
class AnalyticsModel extends Model
{
   function getAnalyticsList($fields,$condition,$show)
	{
		
		$limit_clause='';
		$having_condition = '';
		$sorting_clause = '	o.TotalPrice desc, o.OrderDate desc';
		
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['tuplit_sess_order_user_name']) && $_SESSION['tuplit_sess_order_user_name']  != '' )
			$condition  		.= 	'  and (u.FirstName LIKE "%'.$_SESSION['tuplit_sess_order_user_name'].'%" or u.LastName LIKE "%'.$_SESSION['tuplit_sess_order_user_name'].'%")';
		if(isset($_SESSION['tuplit_sess_merchant_id']) && $_SESSION['tuplit_sess_merchant_id']  != '' )
			$condition  		.= 	' and o.fkMerchantsId = '.$_SESSION['tuplit_sess_merchant_id'].'';
		if(isset($_SESSION['tuplit_sess_DateFrom']) && $_SESSION['tuplit_sess_DateFrom']  != '' )
			$condition  		.= 	" and date(o.OrderDate) = '".date('Y-m-d',strtotime($_SESSION['tuplit_sess_DateFrom']))."'";
		if((isset($_SESSION['tuplit_sess_order_visit']) && $_SESSION['tuplit_sess_order_visit']  != '' ) && (isset($_SESSION['tuplit_sess_order_total_spend']) && $_SESSION['tuplit_sess_order_total_spend']  != '' ))
			$having_condition  	.= 	' Having TotalOrders = '.$_SESSION['tuplit_sess_order_visit'].'  and  TotalPrice = '.$_SESSION['tuplit_sess_order_total_spend'].' ';
		else if(isset($_SESSION['tuplit_sess_order_visit']) && $_SESSION['tuplit_sess_order_visit']  != '' )
			$having_condition  	.= 	' Having TotalOrders = '.$_SESSION['tuplit_sess_order_visit'].' ';
		else if(isset($_SESSION['tuplit_sess_order_total_spend']) && $_SESSION['tuplit_sess_order_total_spend']  != '' )
			$having_condition  	.= 	' Having TotalPrice = '.$_SESSION['tuplit_sess_order_total_spend'].' ';	
		if($show == 1){
			$sorting_clause = ' o.TotalPrice desc,o.OrderDate desc';	
			$limit_clause = ' LIMIT 0,5';
		}
		/*$sql = "select SQL_CALC_FOUND_ROWS u.id as user_id, ".$fields." from {$this->userTable} as u	
				left join orders as o on  (u.id	= o.fkUsersId )
				WHERE 1".$condition." group by u.id ".$having_cond." ORDER BY ".$sorting_clause." ".$limit_clause;*/
				
		$sql = "SELECT SQL_CALC_FOUND_ROWS u.id as userId,u.FirstName,u.LastName,u.Photo,MAX(o.OrderDate) as LastVisit,
							MIN(o.OrderDate) as FirstVisit,COUNT(o.id) as TotalOrders,SUM(TotalPrice) as TotalPrice,c.id as Comments from orders as o 
							LEFT JOIN users as u ON(u.id = o.fkUsersId) 
							Left JOIN comments as c	on(u.id	= c.fkUsersId)
							where 1  and o.OrderStatus IN(0,1,2) and u.Status =1 ".$condition."
							GROUP BY u.id ".$having_condition." order by ".$sorting_clause."  ".$limit_clause."";
		//echo "<br/><br/><br/>======".$sql;		
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
	function getCustomerReport($fields,$condition,$having,$limit,$type,$left_join)
	{
		if($type == 1) {
			if($limit != '' ){
				$limit_clause = 'limit '.$limit.',10';
			}else{
				$limit_clause = 'limit 0,10';
			}
		}else if($type == 2) {
			$limit_clause = 'limit 0,3';
		}
		$sorting_clause 	= '	o.id desc';
		$sql 	= "SELECT SQL_CALC_FOUND_ROWS u.id as userId,u.FirstName,u.LastName,u.Photo,MAX(o.OrderDate) as LastVisit,
							MIN(o.OrderDate) as FirstVisit,COUNT(distinct o.id) as TotalOrders,SUM(TotalPrice) as TotalPrice,m.City from orders as o 
							LEFT JOIN users as u ON(u.id = o.fkUsersId)
							LEFT JOIN merchants as m ON (m.id = o.fkMerchantsId) ".$left_join."
							where 1  and o.OrderStatus IN(0,1,2) and u.Status =1 ".$condition."
							GROUP BY u.id ".$having." order by ".$sorting_clause."  ".$limit_clause."";
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;
	}
	function getLocationReport($fields,$condition,$having){
		$sql	 =	"select ".$fields." from {$this->orderTable} as o
					left join merchants as m on (m.id = o.fkMerchantsId)
					left join merchantcategories as mc on (m.id = mc.fkMerchantId) 
					left join categories as c on (c.id = mc.fkCategoriesId)
					where 1 ".$condition." and m.City != ''
					group by m.City ".$having."
					order by TotalPrice desc
					limit 0,3 ";
		//echo "<br/>======".$sql;
		$result 	= 	$this->sqlQueryArray($sql);
		if($result) return $result;
		else false;
	}
}
?>