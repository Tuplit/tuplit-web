<?php
class CommentController extends Controller
{
   function getTotalRecordCount()
	{
		if (!isset($this->CommentModelObj))
			$this->loadModel('CommentModel', 'CommentModelObj');
		if ($this->CommentModelObj)
			return $this->CommentModelObj->getTotalRecordCount();
	}
	function getCommentList($fields,$condition)
	{
		if (!isset($this->CommentModelObj))
			$this->loadModel('CommentModel', 'CommentModelObj');
		if ($this->CommentModelObj)
			return $this->CommentModelObj->getCommentList($fields,$condition);
	}
	
}
?>