<?php
class MerchantController extends Controller
{
    function getTotalRecordCount()
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->getTotalRecordCount();
	}
	function getMerchantNotApproved()
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->getMerchantNotApproved();
	}
	function getMerchantList($fields,$condition)
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->getMerchantList($fields,$condition);
	}
	
	function deleteMerchantEntries($id)
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->deleteMerchantEntries($id);
	}
	function approveMerchant($id)
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->approveMerchant($id);
	}
	function selectMerchantDetail($id)
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->selectMerchantDetail($id);
	}
	
	function updateDetails($data,$icon,$img)
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->updateDetails($data,$icon,$img);
	}
	
	function updateShoppingHours($data)
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->updateShoppingHours($data);
	}
	
	function selectOpeningHoursDetail($merchantId)
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->selectOpeningHoursDetail($merchantId);
	}
	
	function getCategories()
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->getCategories();
	}
	
	function selectCategoryDetail($id,$cid)
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->selectCategoryDetail($id,$cid);
	}
	function selectMerchantCategory($id)
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->selectMerchantCategory($id);
	}
	function selectMerchantDetails($field,$condition)
	{
		if (!isset($this->MerchantModelObj))
			$this->loadModel('MerchantModel', 'MerchantModelObj');
		if ($this->MerchantModelObj)
			return $this->MerchantModelObj->selectMerchantDetails($field,$condition);
	}	
}
?>