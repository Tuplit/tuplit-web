<?php
class UserController extends Controller
{
   function getTotalRecordCount()
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->getTotalRecordCount();
	}
	function getUserList($fields,$condition)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->getUserList($fields,$condition);
	}
	function updateUserDetails($update_string,$condition)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->updateUserDetails($update_string,$condition);
	}
	function selectUserDetails($field,$condition)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->selectUserDetails($field,$condition);
	}
	function selectWordDetails()
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->selectWordDetails();
	}
	function insertUserDetails($register_values)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->insertUserDetails($register_values);
	}
	function getUserDetails($fields,$condition)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->getUserDetails($fields,$condition);
	}
	function deleteUserReleatedEntries($delete_id)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->deleteUserReleatedEntries($delete_id);
	}
	function getSliderImageDetails($fields, $condition)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->getSliderImageDetails($fields, $condition);
	}
	function deleteSlider($delete_id)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->deleteSlider($delete_id);
	}
	function insertHomeSlide($k)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->insertHomeSlide($k);
	}
	function updateSliderDetails($update_string,$condition)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->updateSliderDetails($update_string,$condition);
	}
	function insertTutorialSlide($k)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->insertTutorialSlide($k);
	}
	function getUserListPN($fields,$condition)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->getUserListPN($fields,$condition);
	}
	function getDevicetoken($fields, $condition)
	{
		if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->getDevicetoken($fields,$condition);
	}
	function updateBadge($token)
	{
	if (!isset($this->UserModelObj))
			$this->loadModel('UserModel', 'UserModelObj');
		if ($this->UserModelObj)
			return $this->UserModelObj->updateBadge($token);
	}
}
?>