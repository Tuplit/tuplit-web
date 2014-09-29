<?php
class SubUserController extends Controller
{
    function getTotalRecordCount()
	{
		if (!isset($this->SubUserModelObj))
			$this->loadModel('SubUserModel', 'SubUserModelObj');
		if ($this->SubUserModelObj)
			return $this->SubUserModelObj->getTotalRecordCount();
	}
	function getSubUserList($fields,$condition)
	{
		if (!isset($this->SubUserModelObj))
			$this->loadModel('SubUserModel', 'SubUserModelObj');
		if ($this->SubUserModelObj)
			return $this->SubUserModelObj->getSubUserList($fields,$condition);
	}
}
?>