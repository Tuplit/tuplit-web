<?php
class CurrencyModel extends Model
{
	function getCurrencyList() {
		$limit_clause	=	$condition	=	'';
		$sorting_clause = ' c.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['tuplit_sess_Location_code']) && $_SESSION['tuplit_sess_Location_code'] != '')
			$condition .= " and c.fkLocationId = '".$_SESSION['tuplit_sess_Location_code']."'";
		if(isset($_SESSION['tuplit_sess_Currency_code']) && $_SESSION['tuplit_sess_Currency_code'] != '')
			$condition .= " and c.Code  like '%".$_SESSION['tuplit_sess_Currency_code']."%'";
		if(isset($_SESSION['tuplit_sess_Currency_name']) && $_SESSION['tuplit_sess_Currency_name'] != '')
			$condition .= " and c.Currency  like '%".strtolower($_SESSION['tuplit_sess_Currency_name'])."%'";
		if(isset($_SESSION['tuplit_sess_Currency_status']) && $_SESSION['tuplit_sess_Currency_status'] != '')
			$condition .= " and c.Status  = ".$_SESSION['tuplit_sess_Currency_status'];
		if(isset($_SESSION['tuplit_sess_Currency_registerdate']) && $_SESSION['tuplit_sess_Currency_registerdate'] != '')
			$condition .= " and date(c.DateCreated)  = '".$_SESSION['tuplit_sess_Currency_registerdate']."'";		
		
		$sql	 	= 	"select SQL_CALC_FOUND_ROWS c . * , l.Location AS LocationName from {$this->currencyTable} c 
						LEFT JOIN {$this->locationTable} l ON ( c.fkLocationId = l.id )
						where 1 ".$condition." and c.Status in (1,2) and  l.Status = 1 ORDER BY ".$sorting_clause." ".$limit_clause;
		//echo $sql;
		$result		=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}
	
	function getLocationList() {
		$sql	 	= 	"select SQL_CALC_FOUND_ROWS * from {$this->locationTable} l where 1 and Status in (1) ORDER BY Location asc";
		$result		=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}

	function getTotalRecordCount() {
		$result = $this->sqlCalcFoundRows();
		return $result;
	}
	
	function getCurrencyDetails($id){
		$sql	 =	"select * from {$this->currencyTable} where id='".$id."'";
		$result	 =	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;
	}
	
	function insertCurrencyDetails($post_values){																 
		$sql	 =	"insert into  {$this->currencyTable}  set Code	    	= 	'".trim($post_values['CurrencyCode'])."',
															fkLocationId	=	'".$post_values['Location']."',
															Currency		=	'".strtolower(trim($post_values['CurrencyName']))."',
															Status			=	'".$post_values['Status']."',
														  	DateCreated 	=	'".date('Y-m-d H:i:s')."'";
		$this->result = $this->insertInto($sql);
		$insertId = $this->sqlInsertId();
		if($insertId) {
			$sql	 =	"update  {$this->locationTable}  set fkCurrencyId  	= 	'".$insertId."',
														DateModified 	=	'".date('Y-m-d H:i:s')."' 
														where id		=	'".$post_values['Location']."'";
			return $this->updateInto($sql);
		}
        return $insertId;
	}
	
	function updateCurrencyDetails($post_values){																 
		$sql	 =	"update  {$this->currencyTable}  set Code	    	= 	'".trim($post_values['CurrencyCode'])."',
														fkLocationId	=	'".$post_values['Location']."',
														Currency		=	'".strtolower(trim($post_values['CurrencyName']))."',
														Status			=	'".$post_values['Status']."',
														DateModified 	=	'".date('Y-m-d H:i:s')."' 
														where id		=	'".$post_values['Currency_id']."'";
		return $this->updateInto($sql);
	}	
	
	function deleteCurrency($ids){																 
		$sql	=	"update  {$this->currencyTable}  set Status			=	'3',
														DateModified 	=	'".date('Y-m-d H:i:s')."' 
														where id in (".$ids.")";
		$res	=	$this->updateInto($sql);
		if($res) {
			$sql	 =	"update  {$this->locationTable}  set fkCurrencyId  	= 	'',
														DateModified 	=	'".date('Y-m-d H:i:s')."' 
														where fkCurrencyId in (".$ids.")";
			return $this->updateInto($sql);
		}
	}
	function checkExist($post_values){	
		$condition	=	'';
		if(isset($post_values['Currency_id']) && !empty($post_values['Currency_id']))
			$condition	=	" and id != '".$post_values['Currency_id']."'";
		$sql	 =	"select * from {$this->currencyTable} where 1 and Status !=3 and (Code ='".trim($post_values['CurrencyCode'])."' || Currency='".strtolower(trim($post_values['CurrencyName']))."' || fkLocationId='".trim($post_values['Location'])."') ".$condition;
		$result	 =	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;
	}
	function updateDetails($update_string,$condition){
		$sql	 =	"update {$this->currencyTable}  set ".$update_string." where ".$condition;
		$this->updateInto($sql);
	}
	function getCurrencyArray($field,$condition)
	{
		$sql	 =	"select ".$field." from {$this->currencyTable} where ".$condition."";
		$result	 =	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;
	}
}
?>