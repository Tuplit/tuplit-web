<?php

/**
 * Description of Model_Comments
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



class Model_Comments extends RedBean_SimpleModel{


    /**
     * Identifier
     * @var int
     */
    public $id;

     /**
     * When the record was created
     * @var int
     */
    public $DateCreated;

    /**
     * When the record was last updated
     * @var int
     */
    public $DateModified;
	
	/**
     * @var int
     */
    public $profileUserId;
	
	/**
     * @var int
     */
    public $Platform;
	
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
         * @var $bean Model_Comments
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
		$rules = [
	            'required' => [
	                ['MerchantId'],['CommentText']
	            ],
	        ];
		$bean = $this->bean;
        $v = new Validator($this->bean);
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
         * @var $bean Model_Comments
         */
		$condition = '';
        $bean 	 	=  $this->bean;
		$userId  	=  $bean->UserId;
		$merchantId =  $bean->MerchantId;
		$start  	=  $bean->Start;
		$limit  	=  $bean->Limit;
		$type	  	=  $bean->Type;//1- user comments for merchant,else all comments in merchant
		// validate the modification
		if($type == 1){
			$this->validateUserId();
      		$this->validateUser($userId);
			$condition		.=	" and u.id	=	'".$userId ."'";
		}
		else {
			$this->validateMerchant();
			
		}
		if($merchantId  != ''){
			//Validate merchant status
			$merchantValid = $this->validateMerchantId($bean->MerchantId);
			$condition		.=	" and m.id	=	'".$merchantId ."'";
		}
		$commantsArray = array();
		$sql		   = "SELECT SQL_CALC_FOUND_ROWS c.id as CommentId,CommentsText,c.Platform, c.CommentDate as CommentDate ,u.Photo as UserPhoto,
						 u.id as UserId ,concat(u.FirstName,' ',u.LastName)as UserName,m.id as merchantId,m.CompanyName as MerchantName,m.Icon as MerchantIcon
						from comments as c
						left join users as u on (u.id = c.fkUsersId)
						left join merchants as m on (m.id = c.fkMerchantsId)
						where 1 ".$condition." and c.Status = 1 and u.Status = 1 order by c.id desc limit $start,$limit";
	   	$commentresult = R::getAll($sql);
		$totalRec =  R::getAll('SELECT FOUND_ROWS() as count');
		$total = (integer)$totalRec[0]['count'];
		if(is_array($commentresult) && count($commentresult) > 0){
			foreach($commentresult as $key=>$value){
				
				if($value['MerchantIcon'] !='')
					$value['MerchantIcon'] = MERCHANT_ICONS_IMAGE_PATH.$value['MerchantIcon'];
				else
					$value['MerchantIcon'] = '';

				if($value['UserPhoto'] !='')
					$value['UserPhoto'] = USER_THUMB_IMAGE_PATH.$value['UserPhoto'];
				else
					$value['UserPhoto'] = '';
				$value['CommentsText'] = getCommentTextEmoji($bean->Platform,$value['CommentsText'],$value['Platform']);
				unset($value['Platform']);
				$commantsArray[$key] = $value;
			}
			$commentsArray['Total'] = $total;
			$commentsArray['List'] = $commantsArray;
			return $commentsArray;
		}
		else{
			/** 
			* No reults found
			*/
			throw new ApiException("No Comments found for this user ", ErrorCodeType::NoResultFound);
		}
	}
	 /**
     * Validate the merchant
     * @throws ApiException if the models fails to validate
     */
	public function validateMerchant()
    {
		$bean = $this->bean;
	  	$rules = [
            'required' => [
                 ['MerchantId']
            ],
			
        ];
		
        $v = new Validator($this->bean);
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
		$bean = $this->bean;
	  	$rules = [
            'required' => [
                 ['UserId']
            ],
			
        ];
		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the users properties. Fill UserId with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
}