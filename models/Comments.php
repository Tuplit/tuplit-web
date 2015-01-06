<?php

/**
 * Description of Comments
 *
 * @author 
 */
use RedBean_Facade as R;
use Helpers\PasswordHelper as PasswordHelper;
use Enumerations\HttpStatusCode as HttpStatusCode;
use Enumerations\AccountType as AccountType;
use Enumerations\StatusType as StatusType;
use Enumerations\ErrorCodeType as ErrorCodeType;
use Exceptions\ApiException as ApiException;
use Valitron\Validator as Validator;
use Helpers\RedBeanHelper as RedBeanHelper;



class Comments extends RedBean_SimpleModel implements ModelBaseInterface {
	
	/**
    * @var int
    */
    public $start;	

    /**
    * Constructor
    */
    public function __construct() {

    }

	/**
    * Insert Comments
    */
    public function create(){ 
		/**
		* Get the bean
		*/
        $bean 			= 	$this->bean;
		
		// validate the parameters
        $this->validate();
		
		// validate create
        $this->validateCreate();		
		
    }	
	
	/**
	* Validate the model
	* @throws ApiException if the models fails to validate required fields
	*/
    public function validate()
    {
		$bean 	= 	$this->bean;
		$rules 	= 	[
						'required' => [
							['MerchantId'],['UsersId'],['CommentsText'],['Platform']
						],				
						'in' =>[
							['Platform',['0','1','2']]
						]
					];
        $v 		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the comments properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/*
	* validate create
	*/
	public function validateCreate(){
	
		/**
		* Get the bean
		*/
        $bean 			= 	$this->bean;
		
	}
	
	/**
	* Validate the user status
	*/
	public function validateUser($userId)
    {
		/**
		* Get the identity of the person requesting the details
		*/
        $user = R::findOne('users', 'id = ? and Status = ?', [$userId,StatusType::ActiveStatus]);
        if (!$user) {
            // the user was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::UserNotInActiveStatus);
        }
		return $user;
    }
	
	/**
	* Validate the merchant status
	*/
	public function validateMerchantId($merchantId)
    {
		/**
		* Get the identity of the merchant
		*/
        $merchant = R::findOne('merchants', 'id = ? and Status = ?', [$merchantId,StatusType::ActiveStatus]);
        if (!$merchant) {
            // the merchant was not found
            throw new ApiException("Merchant status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
        }
		return $merchant;
    }
	
	/**
	* @param  add comments to merchant
	*/
    public function CommentsProcess(){
		
		/**
		* Get the bean
		* @var $bean Comments
		*/
        $bean 	 		=  $this->bean;
		$userId 		=  $bean->UserId;
		$merchantId 	=  $bean->MerchantId;
		$CommentText 	=  $bean->CommentText;
		
		// validate the user
        $this->validateUser($userId);
		
		if($bean->Action == 1){
			// validate the Params
	        $this->validateComments();
			
			// validate the merchant
			$merchantValid	=	 $this->validateMerchantId($merchantId);
		}
		if($bean->Action == 1){
			if($merchantValid){
				$comments   				= R::dispense('comments');
				$comments->fkUsersId		= $userId;
				$comments->fkMerchantsId	= $merchantId;
				$comments->fkOrderId		= $bean->OrderId;
				$comments->Platform			= $bean->Platform;
				$comments->CommentsText		= $CommentText;
				$comments->CommentDate		= date('Y-m-d H:i:s');
				$comments->Status			= StatusType::ActiveStatus;
				$commentId 					= R::store($comments);
				return $commentId ;
			}
			else
			{
				throw new ApiException("Merchant is not valid", ErrorCodeType::MerchantsNotInActiveStatus);
			}
		}//if close for action 1
		else{
				$resultComments = R::findOne('comments', 'id = ?', array($bean->CommentId));
				if($resultComments && ($resultComments['fkUsersId'] == $userId )){					
					$comments	  = R::dispense('comments');
					$comments->id = $bean->CommentId;
					$sql			=	"delete from comments where id = ".$comments->id."";
					R::exec($sql);
				}
				else{
						// if no comments
			    		throw new ApiException("You are not allowed to delete this comments  -  ".$bean->CommentId."", ErrorCodeType::NotAllowToDeleteComments);
				}
		}
	}

	/**
	* Validate the comments
	*/
	public function validateComments()
    {
		$rules 	= 	[
						'required' => [
							['MerchantId'],['CommentText'],['OrderId']
						],
					];
		$bean 	= 	$this->bean;
        $v 		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the comment properties. Fill MerchantId,CommentText with correct values" , ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
	* @param List all comments
	*/
    public function commentsLists(){
		
		/**
		* Get the bean
		* @var $bean Comments
		*/
		$condition 	= 	'';
        $bean 	 	=  	$this->bean;
		$userId  	=  	$bean->UserId;
		$merchantId =  	$bean->MerchantId;
		$start  	=  	$bean->Start;
		$limit  	=  	$bean->Limit;
		$type	  	=  	$bean->Type; //1- user comments for merchant,else all comments in merchant
		
		// validate the modification
		if($type == 1){
			$this->validateUserId();
      		$this->validateUser($userId);
			$condition		.=	" and u.id	=	'".$userId ."' and u.Status = 1";
		}
		else {
			$this->validateMerchant();
			
		}
		if($merchantId  != ''){
			//Validate merchant status
			$merchantValid = $this->validateMerchantId($bean->MerchantId);
			$condition		.=	" and m.id	=	'".$merchantId ."'";
		}
		$commantsArray 	= 	array();
		
		$leftJoin	=	$groupBy	=	'';
		if($type == 3) {
			$fields 	= 	" c.id as CommentId,c.fkUsersId as UsersId, concat(u.FirstName,' ',u.LastName)as UserName,c.CommentsText, c.CommentDate, c.Platform,p.ItemName,p.Photo as ProductImage ";
			$leftJoin	=	" 	LEFT JOIN orders o ON (c.fkOrderId = o.id)
								LEFT JOIN carts ca ON (o.fkCartId = ca.CartId)
								LEFT JOIN products p ON (ca.fkProductsId = p.id && p.Status != 3)";
			$groupBy 	=	" group by c.id ";
		} else {
			$fields 	= 	"c.id as CommentId,CommentsText,c.Platform, c.CommentDate as CommentDate ,u.Photo as UserPhoto,
								u.id as UserId ,concat(u.FirstName,' ',u.LastName)as UserName,m.id as merchantId,m.CompanyName as MerchantName,m.Icon as MerchantIcon ";
		}
		
		$sql		= 	"SELECT SQL_CALC_FOUND_ROWS ".$fields."from comments as c
						left join users as u on (c.fkUsersId = u.id)
						left join merchants as m on (c.fkMerchantsId = m.id) ".$leftJoin."
						where 1 ".$condition." and u.Status = 1 and c.Status = 1 ".$groupBy." order by c.CommentDate desc limit $start,$limit";
		//echo $sql;
	   	$commentresult 	= 	R::getAll($sql);
		$totalRec 		=  	R::getAll('SELECT FOUND_ROWS() as count');
		$total 			= 	$totalRec[0]['count'];
		if(is_array($commentresult) && count($commentresult) > 0){
			foreach($commentresult as $key=>$value){
				if($type == 3) {
					
					if($value['ItemName'] !='')
						$value['ItemName'] 		= $value['ItemName'];
					else
						$value['ItemName'] 		= '';
					
					if($value['ProductImage'] !='')
						$value['ProductImage'] 	= 	PRODUCT_IMAGE_PATH.$value['ProductImage'];
					else
						$value['ProductImage'] 	= 	'';
				} else {
					if($value['MerchantIcon'] !='')
						$value['MerchantIcon'] 	= 	MERCHANT_ICONS_IMAGE_PATH.$value['MerchantIcon'];
					else
						$value['MerchantIcon'] 	= 	'';

					if($value['UserPhoto'] !='')
						$value['UserPhoto'] 	= 	USER_THUMB_IMAGE_PATH.$value['UserPhoto'];
					else
						$value['UserPhoto'] = '';					
				}
				$value['CommentsText'] 		= 	getCommentTextEmoji($bean->Platform,$value['CommentsText'],$value['Platform']);
				unset($value['Platform']);
				$commantsArray[$key] 		=	$value;
			}
			$commentsArray['Total'] 		= 	$total;
			$commentsArray['List'] 			= 	$commantsArray;
			return $commentsArray;
		}
		else{
			/** 
			* No reults found
			*/
			if($type != 1){
				throw new ApiException("No Comments found for this user ", ErrorCodeType::NoResultFound);
			}
		}
	}
	
	/**
	* Validate the merchant
	* @throws ApiException if the models fails to validate
	*/
	public function validateMerchant()
    {
		$bean 	= 	$this->bean;
	  	$rules 	= 	[
						'required' => [
							 ['MerchantId']
						],
					];
		
        $v		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the Merchant's properties. Fill MerchantId with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
	* Validate the merchant id
	* @throws ApiException if the models fails to validate
	*/
	public function validateUserId()
    {
		$bean 	= 	$this->bean;
	  	$rules 	= 	[
						'required' => [
							 ['UserId']
						],
					];
        $v 		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the users properties. Fill UserId with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
	* Get Merchants comments list
	*/
	public function getMerchantCommentList()
    {
		/**
		* Get the bean
		* @var $bean Comments
		*/
        $bean 	 		=  	$this->bean;
		$merchantId 	=  	$bean->MerchantId;
		$start  		=  	$bean->Start;
		$limit  		=  	$bean->Limit;
		$comments		=	$output = array();
		$this->validateMerchant();
		$commentSql 	= " SELECT SQL_CALC_FOUND_ROWS c.fkUsersId as UsersId,c.CommentsText,c.CommentDate,c.Platform,u.FirstName,u.LastName,u.Photo  from comments c 
							LEFT JOIN users u ON c.fkUsersId = u.id 
							where c.Status = 1 and u.Status = 1 and  c.fkMerchantsId=".$merchantId." order by c.CommentDate desc limit ".$start." ,".$limit;
		$comments 		= R::getAll($commentSql);
		$totalRec 		= R::getAll('SELECT FOUND_ROWS() as count ');
		$total 			= (integer)$totalRec[0]['count'];
		if($comments){
			foreach($comments as $key => $value){
				if($comments[$key]['Photo']  !='')
					$comments[$key]['Photo']  	= 	USER_THUMB_IMAGE_PATH.$value['Photo'];
				else
					$comments[$key]['Photo']  = '';
				$comments[$key]['CommentsText'] 	= getCommentTextEmoji($bean->Platform,$value['CommentsText'],$value['Platform']);
			}			
		}
		if(count($comments) >0 ) {
			$output['comments'] 		= $comments;
			$output['totalcomments'] 	= $total;
			return $output;
		} else 
			return $comments;
	}
	/**
	* @param List all comments for analytics
	*/
    public function productCommentsLists(){
		
		/**
		* Get the bean
		* @var $bean Comments
		*/
		$condition 	= 	$time_zone = '';
        $bean 	 	=  	$this->bean;
		$merchantId =  	$bean->MerchantId;
		$start  	=  	$bean->Start;
		$limit  	=  	$bean->Limit;
		// validate the modification
		if($merchantId  != ''){
			//Validate merchant status
			$merchantValid  = $this->validateMerchantId($merchantId);
		}
		$commantsArray 	= 	array();
		$dataType 		= 	$bean->DataType;
		if($bean->TimeZone)
		$time_zone 		= 	$bean->DataType;
		$curr_date 		= 	date('d-m-Y');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');
		if($dataType=='year') {
			$condition 	.=	 "  and DATE_FORMAT(CommentDate,'%Y') = ".$cur_year."";
		} else if($dataType=='month') {
			$condition .= 	"and DATE_FORMAT(CommentDate,'%m') = ".$cur_month." and DATE_FORMAT(CommentDate,'%Y') = ".$cur_year." ";
		} else if($dataType=='day') {
			$condition .= 	" and date(DATE_ADD(CommentDate,INTERVAL '".$time_zone."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'";
		} else if($dataType=='7days') {
			$condition 		.= 	"and (DATE_FORMAT(CommentDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(CommentDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')";
		}
		$leftJoin	=	$groupBy	=	'';
		$fields 	= 	" c.id as CommentId,c.fkUsersId as UsersId, u.FirstName,u.LastName,c.CommentsText, c.CommentDate, c.Platform ";
		$groupBy 	=	" group by c.id ";

		/*$sql		=	"SELECT SQL_CALC_FOUND_ROWS  ".$fields." from comments as c
							left join users as u on (c.fkUsersId = u.id)
							left join orders as o on (o.id = c.fkOrderId)
							left join (select * from carts ORDER BY RAND() ) as ca ON (ca.CartId = o.fkCartId)
							left join products  as  p ON (ca.fkProductsId = p.id)
							where 1 ".$condition."  and p.Status = 1 and c.fkOrderId != 0 and  c.fkMerchantsId = ".$merchantId." and   c.Status = 1  ".$groupBy."  order by c.CommentDate desc limit $start,$limit";*/
		$sql		=	"SELECT SQL_CALC_FOUND_ROWS  ".$fields." from comments as c
							left join users as u on (c.fkUsersId = u.id)
							where 1 ".$condition." and c.fkMerchantsId = ".$merchantId." and   c.Status = 1  ".$groupBy."  order by c.CommentDate desc limit $start,$limit";
		//echo"<br>===================>".$sql;
	   	$commentresult 	= 	R::getAll($sql);
		$totalRec 		=  	R::getAll('SELECT FOUND_ROWS() as count');
		$total 			= 	$totalRec[0]['count'];
		if(is_array($commentresult) && count($commentresult) > 0){
			foreach($commentresult as $key=>$value){
				$value['UserName'] 			= 	ucfirst($value['FirstName']).' '.$value['LastName'];
				$value['CommentsText'] 		= 	getCommentTextEmoji($bean->Platform,$value['CommentsText'],$value['Platform']);
				unset($value['Platform']);
				$commantsArray[$key] 		=	$value;
			}
			$commentsArray['Total'] 		= 	$total;
			$commentsArray['List'] 			= 	$commantsArray;
			return $commentsArray;
		}
		else{
			/** 
			* No reults found
			*/
				throw new ApiException("No Comments found", ErrorCodeType::NoResultFound);
		}
	}
}