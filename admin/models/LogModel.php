<?php
class LogModel extends Model
{
   function getTotalRecordCount()
	{
		$result = $this->sqlCalcFoundRows();
        return $result;
	}
	function logtrackDetails($where)
	{
		$limit_clause = '';
		$sorting_clause = ' l.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		
		if(isset($_SESSION['sess_logtrack_from_date']) && $_SESSION['sess_logtrack_from_date'] != ''	&&	isset($_SESSION['sess_logtrack_to_date']) && $_SESSION['sess_logtrack_to_date'] != ''){
			$where .= " AND ((date(l.start_time) >=  '".date('Y-m-d',strtotime($_SESSION['sess_logtrack_from_date']))."' and date(l.end_time) <= '".date('Y-m-d',strtotime($_SESSION['sess_logtrack_to_date']))."') ) ";
		}
		else if(isset($_SESSION['sess_logtrack_from_date']) && $_SESSION['sess_logtrack_from_date'] != '')
			$where .= " AND date(l.start_time) >=  '".date('Y-m-d',strtotime($_SESSION['sess_logtrack_from_date']))."'";
		else if(isset($_SESSION['sess_logtrack_to_date']) && $_SESSION['sess_logtrack_to_date'] != '')
			$where .= " AND date(l.end_time) <=  '".date('Y-m-d',strtotime($_SESSION['sess_logtrack_to_date']))."'";
		
		if(isset($_SESSION['sess_logtrack_searchIP']) && $_SESSION['sess_logtrack_searchIP'] != '')
			$where .= " and l.ip_address LIKE '%".$_SESSION['sess_logtrack_searchIP']."%' ";
		if(isset($_SESSION['sess_logtrack_searchUrl']) && $_SESSION['sess_logtrack_searchUrl'] != '')
			$where .= " and l.url LIKE '%".$_SESSION['sess_logtrack_searchUrl']."%' ";
		if(isset($_SESSION['sess_logtrack_log_method']) && $_SESSION['sess_logtrack_log_method'] != ''){
			$where .= " and l.method = '".$_SESSION['sess_logtrack_log_method']."' ";
		}
		if(isset($_SESSION['sess_logtrack_searchResponse']) && $_SESSION['sess_logtrack_searchResponse'] != '')
			$where .= " and l.response LIKE '%".$_SESSION['sess_logtrack_searchResponse']."%' ";
		/*$sql	=	"SELECT SQL_CALC_FOUND_ROWS l.id as logId,l.*
					FROM {$this->logTable} as l 
					WHERE 1 ".$where." ORDER BY ".$sorting_clause.$limit_clause;
		
		$sql	=	"SELECT SQL_CALC_FOUND_ROWS l.*,u.*,ac.device_type
					FROM {$this->logTable} as l 
					left JOIN {$this->oauthSessionAccessTokensTable} as atk on(atk.access_token = l.user ) 
					LEFT JOIN {$this->oauthSessionTable} as ses on ( ses.id = atk.session_id ) 
					LEFT JOIN {$this->userTable} as u on (u.id = ses.owner_id) 
					LEFT JOIN {$this->oauthClientsTable} as ac on(ac.id=ses.client_id) 
					WHERE 1 ".$where." ORDER BY ".$sorting_clause.$limit_clause;
		//echo "<br>============>".$sql;
		$result = 	$this->sqlQueryArray($sql);
		if (count($result) == 0) return false;
		return $result;*/
		
		if(isset($_SESSION['sess_logtrack_searchUserName']) && $_SESSION['sess_logtrack_searchUserName'] != ''){			
			$sql	=	"SELECT l.*,u.*,ac.device_type
						FROM {$this->logTable} as l 
						left JOIN {$this->oauthSessionAccessTokensTable} as atk on(atk.access_token = l.user ) 
						LEFT JOIN {$this->oauthSessionTable} as ses on ( ses.id = atk.session_id ) 
						LEFT JOIN {$this->userTable} as u on (u.id = ses.owner_id) 
						LEFT JOIN {$this->oauthClientsTable} as ac on(ac.id=ses.client_id) 
						WHERE 1 ".$where." ORDER BY ".$sorting_clause.$limit_clause;
		}else{
			$sql	=	"SELECT l.id as sortId,l.* FROM {$this->logTable} as l 
					 WHERE 1 ".$where." ORDER BY ".$sorting_clause.$limit_clause;
		}
		$result = 	$this->sqlQueryArray($sql);
		
		if (count($result) == 0) {
			$resultData['result'] = '';
			$resultData['total'] = 0;
			return $resultData;
			//return false;
		}
		else{
			if(isset($_SESSION['sess_logtrack_searchUserName']) && $_SESSION['sess_logtrack_searchUserName'] != ''){			
				$sql 	= "SELECT count(*) as total_count
							FROM {$this->logTable} as l 
							left JOIN {$this->oauthSessionAccessTokensTable} as atk on(atk.access_token = l.user ) 
							LEFT JOIN {$this->oauthSessionTable} as ses on ( ses.id = atk.session_id ) 
							LEFT JOIN {$this->userTable} as u on (u.id = ses.owner_id) 
							LEFT JOIN {$this->oauthClientsTable} as ac on(ac.id=ses.client_id) 
							WHERE 1 ".$where;	
			}else{	
				$sql	=	"SELECT count(id) as total_count FROM {$this->logTable} as l 
							WHERE 1 ".$where."";
			}
		
			$total 		= 	$this->sqlQueryArray($sql);
			$resultData['result'] 	= $result;
			$resultData['total'] 	= $total[0]->total_count;
			return $resultData;
		}	
	}
	function logUsersDetails($fields,$logUserTokens){
		$sql	=	"	SELECT ".$fields." FROM {$this->oauthSessionAccessTokensTable} as atk  
						LEFT JOIN {$this->oauthSessionTable} as ses on ( ses.id = atk.session_id ) 
						LEFT JOIN {$this->userTable} as u on (u.id = ses.owner_id) 
						LEFT JOIN {$this->oauthClientsTable} as ac on(ac.id=ses.client_id)
						WHERE atk.access_token IN(".$logUserTokens.") ";
		//echo "<br>============>".$sql;
		$result = 	$this->sqlQueryArray($sql);
		if (count($result) == 0) return false;
		return $result;
	}
	function selectUserDetails($field,$condition){
		$sql	 =	"select ".$field." from {$this->userTable} as u 
					LEFT JOIN {$this->oauthSessionTable} as ses on ( u.id = ses.owner_id ) 
					LEFT JOIN {$this->oauthSessionAccessTokensTable} as atk  ON(ses.id=atk.session_id)
					where ".$condition;	
		//echo "<br>============>".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	
	function MangoPayTracking($fields,$condition)
	{
		$limit_clause = ' ';
		$sorting_clause = 'ml.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		
		if(isset($_SESSION['sess_mangopay_from_date']) && $_SESSION['sess_mangopay_from_date'] != ''	&&	isset($_SESSION['sess_mangopay_to_date']) && $_SESSION['sess_mangopay_to_date'] != ''){
			$condition .= " AND ((date(ml.start_time) >=  '".date('Y-m-d',strtotime($_SESSION['sess_mangopay_from_date']))."' and date(ml.end_time) <= '".date('Y-m-d',strtotime($_SESSION['sess_mangopay_to_date']))."') ) ";
		}
		else if(isset($_SESSION['sess_mangopay_from_date']) && $_SESSION['sess_mangopay_from_date'] != '')
			$condition .= " AND date(ml.start_time) >=  '".date('Y-m-d',strtotime($_SESSION['sess_mangopay_from_date']))."'";
		else if(isset($_SESSION['sess_mangopay_to_date']) && $_SESSION['sess_mangopay_to_date'] != '')
			$condition .= " AND date(ml.end_time) <=  '".date('Y-m-d',strtotime($_SESSION['sess_mangopay_to_date']))."'";
		
		if(isset($_SESSION['sess_mangopay_searchIP']) && $_SESSION['sess_mangopay_searchIP'] != '')
			$condition .= " and ml.ip_address LIKE '%".$_SESSION['sess_mangopay_searchIP']."%' ";
		if(isset($_SESSION['sess_mangopay_searchUrl']) && $_SESSION['sess_mangopay_searchUrl'] != '')
			$condition .= " and ml.url LIKE '%".$_SESSION['sess_mangopay_searchUrl']."%' ";
		if(isset($_SESSION['sess_mangopay_log_method']) && $_SESSION['sess_mangopay_log_method'] != ''){
			$condition .= " and ml.method = '".$_SESSION['sess_mangopay_log_method']."' ";
		}
		if(isset($_SESSION['sess_mangopay_searchResponse']) && $_SESSION['sess_mangopay_searchResponse'] != '')
			$condition .= " and ml.response LIKE '%".$_SESSION['sess_mangopay_searchResponse']."%' ";
		if(isset($_SESSION['sess_mangopay_searchUserName']) && $_SESSION['sess_mangopay_searchUserName'] != '')
			$condition .= " and (u.FirstName LIKE '%".$_SESSION['sess_mangopay_searchUserName']."%' ||	u.LastName LIKE '%".$_SESSION['sess_mangopay_searchUserName']."%' || CONCAT( u.FirstName,  ' ', u.LastName ) LIKE '%".$_SESSION['sess_mangopay_searchUserName']."%')";
		if(isset($_SESSION['sess_mangopay_searchMerchantName']) && $_SESSION['sess_mangopay_searchMerchantName'] != '')
			$condition .= " and (m.FirstName LIKE '%".$_SESSION['sess_mangopay_searchMerchantName']."%' ||	m.LastName LIKE '%".$_SESSION['sess_mangopay_searchMerchantName']."%' || CONCAT( m.FirstName,  ' ', m.LastName ) LIKE '%".$_SESSION['sess_mangopay_searchMerchantName']."%')";
		
		$sql 	= "select SQL_CALC_FOUND_ROWS ".$fields." from mangopaylogs as ml
					LEFT JOIN {$this->userTable} as u on (u.id = ml.fkUsersId)
					LEFT JOIN {$this->merchantTable} as  m on (m.id = ml.fkMerchantsId)
					WHERE 1".$condition." ORDER BY ".$sorting_clause." ".$limit_clause;
		$result		=	$this->sqlQueryArray($sql);
		//echo "=========QRY============>".$sql;
		if(count($result) == 0) return false;
		else {	
			return $result;
		}
	}
}
?>