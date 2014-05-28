<?php
class ManagementController extends Controller
{
   function getTotalRecordCount()
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->getTotalRecordCount();
	}
	function getCategoryList($fields,$condition)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->getCategoryList($fields,$condition);
	}
	function updateCategoryDetails($update_string,$condition)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->updateCategoryDetails($update_string,$condition);
	}
	function selectCategoryDetails($field,$condition)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->selectCategoryDetails($field,$condition);
	}
	function selectWordDetails()
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->selectWordDetails();
	}
	function insertCategoryDetails($register_values)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->insertCategoryDetails($register_values);
	}
	function getCategoryDetails($fields,$condition)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->getCategoryDetails($fields,$condition);
	}
	function deleteCategoryReleatedEntries($delete_id)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->deleteCategoryReleatedEntries($delete_id);
	}
}
?>