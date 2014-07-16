<?php
class Model extends Database
{
    /*Table Name*/
    var $adminTable              			=   "admins";
	var $userTable               			=   "users";
	var $wordsTable                 		=   "words";
	var $staticpagesTable           		=   "staticpages";
	var $oauthClientEndpointsTable  		=   "oauth_client_endpoints";
	var $oauthClientEndpointsParamsTable	=	"oauth_client_endpoints_params";
	var $oauthSessionAccessTokensTable		=	"oauth_session_access_tokens"; 
	var $oauthSessionTable					=	"oauth_sessions";
	var $oauthClientsTable 					=	"oauth_clients";
	var $logTable               			=   "logs";
	var $categoryTable             			=   "categories";
	var $SliderImageTable             		=   "sliderimages";
	var $merchantTable             			=   "merchants";
	var $productTable             			=   "products";
	var $merchantcategoriesTable            =   "merchantcategories";
	var $productcategoriesTable            	=   "productcategories";
	var $merchantshoppingTable           	=   "merchantshoppinghours";
	var $orderTable			            	=   "orders";
	var $cartTable			            	=   "carts";
	var $commentTable			            =   "comments";
	var $devicetokenTable					=	"devicetokens";
	var $webContentTable					=	"webcontent";
	var $transferTable					=	"transfer";
    /*Table Name*/
	function Model()
	{
		global $globalDbManager;
		$this->dbConnect = $globalDbManager->dbConnect;
	}
}?>