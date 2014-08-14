<?php
class CurrencyController extends Controller
{
    function getTotalRecordCount()
	{
		if (!isset($this->CurrencyModelObj))
			$this->loadModel('CurrencyModel', 'CurrencyModelObj');
		if ($this->CurrencyModelObj)
			return $this->CurrencyModelObj->getTotalRecordCount();
	}
	function getCurrencyList()
	{
		if (!isset($this->CurrencyModelObj))
			$this->loadModel('CurrencyModel', 'CurrencyModelObj');
		if ($this->CurrencyModelObj)
			return $this->CurrencyModelObj->getCurrencyList();
	}
	function getLocationList()
	{
		if (!isset($this->CurrencyModelObj))
			$this->loadModel('CurrencyModel', 'CurrencyModelObj');
		if ($this->CurrencyModelObj)
			return $this->CurrencyModelObj->getLocationList();
	}
	function getCurrencyDetails($id)
	{
		if (!isset($this->CurrencyModelObj))
			$this->loadModel('CurrencyModel', 'CurrencyModelObj');
		if ($this->CurrencyModelObj)
			return $this->CurrencyModelObj->getCurrencyDetails($id);
	}
	function insertCurrencyDetails($post_values)
	{
		if (!isset($this->CurrencyModelObj))
			$this->loadModel('CurrencyModel', 'CurrencyModelObj');
		if ($this->CurrencyModelObj)
			return $this->CurrencyModelObj->insertCurrencyDetails($post_values);
	}
	function updateCurrencyDetails($post_values)
	{
		if (!isset($this->CurrencyModelObj))
			$this->loadModel('CurrencyModel', 'CurrencyModelObj');
		if ($this->CurrencyModelObj)
			return $this->CurrencyModelObj->updateCurrencyDetails($post_values);
	}
	function deleteCurrency($ids)
	{
		if (!isset($this->CurrencyModelObj))
			$this->loadModel('CurrencyModel', 'CurrencyModelObj');
		if ($this->CurrencyModelObj)
			return $this->CurrencyModelObj->deleteCurrency($ids);
	}	
	function checkExist($post_values)
	{
		if (!isset($this->CurrencyModelObj))
			$this->loadModel('CurrencyModel', 'CurrencyModelObj');
		if ($this->CurrencyModelObj)
			return $this->CurrencyModelObj->checkExist($post_values);
	}	
	function updateDetails($update_string,$condition)
	{
		if (!isset($this->CurrencyModelObj))
			$this->loadModel('CurrencyModel', 'CurrencyModelObj');
		if ($this->CurrencyModelObj)
			return $this->CurrencyModelObj->updateDetails($update_string,$condition);
	}	
	function getCurrencyArray($field,$condition)
	{
		if (!isset($this->CurrencyModelObj))
			$this->loadModel('CurrencyModel', 'CurrencyModelObj');
		if ($this->CurrencyModelObj)
			return $this->CurrencyModelObj->getCurrencyArray($field,$condition);
	}	
}
?>