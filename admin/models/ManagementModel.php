<?php
class ManagementModel extends Model
{
   function getCategoryList($fields,$condition)
	{
		$limit_clause= " ";
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
			$condition .= " and date(c.DateCreated) >= '".$_SESSION['tuplit_sess_Category_registerdate']."'";	
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->categoryTable} as c	
				WHERE 1".$condition." group by c.id ORDER BY ".$sorting_clause." ".$limit_clause;
		$result	=	$this->sqlQueryArray($sql);
		//echo "<br/>======".$sql;
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
	function selectProductCategoryDetails($field,$condition){
		$sql	 =	"select ".$field." from {$this->productcategoriesTable} where ".$condition;
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	function updateProductCategoryDetails($update_string,$condition){
		$sql	 =	"update {$this->productcategoriesTable}  set ".$update_string." where ".$condition;
		//echo "<br/>======".$sql;
		$this->updateInto($sql);
	}
	function insertProductCategoryDetails($post_values){
																 
		$sql	 =	"insert into  {$this->productcategoriesTable}  set 
							CategoryName	= '".$post_values['CategoryName']."',
							Status 			= 1,
							fkMerchantId	=	0,
							DateCreated 	= '".date('Y-m-d H:i:s')."'";
		$this->result = $this->insertInto($sql);
		$insertId = $this->sqlInsertId();
        return $insertId;
	}
	function getProductCategoryList($fields,$condition,$join_condition)
	{
		$limit_clause='';
		$sorting_clause = ' pc.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['tuplit_sess_product_category_name']) && $_SESSION['tuplit_sess_product_category_name'] != '')
			$condition .= " and pc.CategoryName LIKE '%".trim($_SESSION['tuplit_sess_product_category_name'])."%'";
		if(isset($_SESSION['tuplit_sess_product_category_status']) && $_SESSION['tuplit_sess_product_category_status'] != '')
			$condition .= " and pc.Status = '".$_SESSION['tuplit_sess_product_category_status']."' ";
		if(isset($_SESSION['tuplit_sess_product_category_registerdate']) && $_SESSION['tuplit_sess_product_category_registerdate'] != '')
			$condition .= " and date(pc.DateCreated) = '".$_SESSION['tuplit_sess_product_category_registerdate']."'";	
		if(isset($_SESSION['tuplit_sess_product_category_merchant']) && $_SESSION['tuplit_sess_product_category_merchant'] != '')
			$condition .= " and pc.fkMerchantId = '".$_SESSION['tuplit_sess_product_category_merchant']."' ";
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->productcategoriesTable} as pc	".$join_condition."
				WHERE 1".$condition." group by pc.id ORDER BY ".$sorting_clause." ".$limit_clause;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;
	}
	 function getCategoriesList($fields,$condition)
	{
		$limit_clause= " ";
		$sorting_clause = ' ProductsCount desc';
		if(isset($_SESSION['startlimit']))
			$limit_clause .= ' LIMIT '.$_SESSION['startlimit']. ', 3';
		else{
			$limit_clause .= ' LIMIT 0,3';
		}
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields.",c.id as CategoryId FROM categories as c 
				left join merchantcategories as mc on (c.id = mc.fkCategoriesId)
				left join merchants as m on (m.id = mc.fkMerchantId and m.Status = 1) 
				left join products as p on (m.id = p.fkMerchantsId and p.Status = 1 ) 
				WHERE 1".$condition." group by c.id ORDER BY ".$sorting_clause." ".$limit_clause;
		$result	=	$this->sqlQueryArray($sql);
		//echo "<br/>======".$sql;
		if(count($result) == 0) return false;
		else return $result;
	}
	
}
?>