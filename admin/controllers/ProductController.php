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
	function getProductList($fields,$condition)
	{
		if (!isset($this->ProductModelObj))
			$this->loadModel('ProductModel', 'ProductModelObj');
		if ($this->ProductModelObj)
			return $this->ProductModelObj->getProductList($fields,$condition);
	}
	function selectProductDetail($id)
	{
		if (!isset($this->ProductModelObj))
			$this->loadModel('ProductModel', 'ProductModelObj');
		if ($this->ProductModelObj)
			return $this->ProductModelObj->selectProductDetail($id);
	}
}
?>