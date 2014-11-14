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
	function selectProductCategoryDetails($field,$condition)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->selectProductCategoryDetails($field,$condition);
	}
	function updateProductCategoryDetails($update_string,$condition)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->updateProductCategoryDetails($update_string,$condition);
	}
	function insertProductCategoryDetails($post_values)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->insertProductCategoryDetails($post_values);
	}
	function getProductCategoryList($fields,$condition,$join_condition)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->getProductCategoryList($fields,$condition,$join_condition);
	}
	function getCategoriesList($fields,$condition)
	{
		if (!isset($this->ManagementModelObj))
			$this->loadModel('ManagementModel', 'ManagementModelObj');
		if ($this->ManagementModelObj)
			return $this->ManagementModelObj->getCategoriesList($fields,$condition);
	}
}
?>