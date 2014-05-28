<?php
class ProductModel extends Model
{
   function getProductList($fields,$condition)
	{
		$limit_clause='';
		$sorting_clause = ' p.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['item_sess_name']) && $_SESSION['item_sess_name'] != '')
			$condition .= " and p.ItemName  LIKE '%".$_SESSION['item_sess_name']."%' ";
		if(isset($_SESSION['item_sess_cost']) && $_SESSION['item_sess_cost'] != '')
			$condition .= " and p.Price LIKE '".$_SESSION['item_sess_cost']."%' ";
		if(isset($_SESSION['item_sess_mername']) && $_SESSION['item_sess_mername'] != '')
			$condition .= " and m.CompanyName LIKE '%".$_SESSION['item_sess_mername']."%' ";
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->productTable} p	left join merchants as m on m.id = p.fkMerchantsId
				WHERE 1".$condition." group by p.id ORDER BY ".$sorting_clause." ".$limit_clause;
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
	
	function selectProductDetail($id){
		$sql	 =	"select p.*, m.CompanyName from {$this->productTable} p left join merchants as m on m.id = p.fkMerchantsId where p.id=".$id;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		//echo "<pre>";   print_r($result);   echo "</pre>";		
		if($result) return $result;
		else false;
	}
		
}
?>