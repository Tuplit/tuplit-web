<?php
class ProductController extends Controller
{
    function getTotalRecordCount()
	{
		if (!isset($this->ProductModelObj))
			$this->loadModel('ProductModel', 'ProductModelObj');
		if ($this->ProductModelObj)
			return $this->ProductModelObj->getTotalRecordCount();
	}
	function getProductList($fields,$condition,$startLimit)
	{
		if (!isset($this->ProductModelObj))
			$this->loadModel('ProductModel', 'ProductModelObj');
		if ($this->ProductModelObj)
			return $this->ProductModelObj->getProductList($fields,$condition,$startLimit);
	}
	function selectProductDetail($id)
	{
		if (!isset($this->ProductModelObj))
			$this->loadModel('ProductModel', 'ProductModelObj');
		if ($this->ProductModelObj)
			return $this->ProductModelObj->selectProductDetail($id);
	}
	function updateProductDetails($update_string,$condition)
	{
		if (!isset($this->ProductModelObj))
			$this->loadModel('ProductModel', 'ProductModelObj');
		if ($this->ProductModelObj)
			return $this->ProductModelObj->updateProductDetails($update_string,$condition);
	}
	function insertProductDetails($post_values)
	{
		if (!isset($this->ProductModelObj))
			$this->loadModel('ProductModel', 'ProductModelObj');
		if ($this->ProductModelObj)
			return $this->ProductModelObj->insertProductDetails($post_values);
	}
	function selectProductDetails($field,$condition)
	{
		if (!isset($this->ProductModelObj))
			$this->loadModel('ProductModel', 'ProductModelObj');
		if ($this->ProductModelObj)
			return $this->ProductModelObj->selectProductDetails($field,$condition);
	}
	function getProductNamesList($id)
	{
		if (!isset($this->ProductModelObj))
			$this->loadModel('ProductModel', 'ProductModelObj');
		if ($this->ProductModelObj)
			return $this->ProductModelObj->getProductNamesList($id);
	}
}
?>