<?php
class CommentModel extends Model
{
   function getCommentList($fields,$condition)
	{
		$limit_clause='';
		$sorting_clause = ' com.id desc';	
		if(isset($_SESSION['tuplit_sess_comment_user_name']) && $_SESSION['tuplit_sess_comment_user_name'] != '')
			$condition .= " and ( u.FirstName LIKE '%".$_SESSION['tuplit_sess_comment_user_name']."%' || u.LastName LIKE '%".$_SESSION['tuplit_sess_comment_user_name']."%' )";
		if(isset($_SESSION['tuplit_sess_comment_date']) && $_SESSION['tuplit_sess_comment_date'] != '')
			$condition .= " and date(com.CommentDate) = '".$_SESSION['tuplit_sess_comment_date']."'";	
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->commentTable} as com 
		left join users as u on  (u.id	= com.fkUsersId )
		left join merchants as m on (m.id = com.fkMerchantsId)
		WHERE 1".$condition."  ORDER BY ".$sorting_clause." ".$limit_clause;
		$result	=	$this->sqlQueryArray($sql);		
		if(count($result) == 0) return false;
		else return $result;	
	}
	
   function getTotalRecordCount()
	{
		$result = $this->sqlCalcFoundRows();
        return $result;
	}	
}
?>