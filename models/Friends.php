<?php

/**
 * Description of Friends
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

//Require needed models
require_once	'Orders.php';

class Friends extends RedBean_SimpleModel implements ModelBaseInterface {

	/**
	* Constructor
	*/
    public function __construct() {

    }
	
	/**
	* Insert friends
	*/
    public function create(){ 
		/**
		* Get the bean
		* @var $bean friends
		*/
        $bean 	= 	$this->bean;
		
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
							['UsersId'],['FriendUsersId']
						]
					];
        $v 		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the friends properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/*
	* validate create
	*/
	public function validateCreate(){
	
		/**
		* Get the bean
		* @var $bean friends
		*/
        $bean 	= 	$this->bean;
		
	}
	
	/**
	* Create an user account
	* Validation for email,fbId,GooglePlusId
	*/
    public function checkInviteFriends(){ // Tuplit user creating
		/**
		* Get the bean
		* @var $bean Friends
		*/
        $bean 						= 	$this->bean;
        $bean->DateCreated 			= 	date('Y-m-d H:i:s');
        $bean->DateModified 		= 	$bean->DateCreated;
		$response					=  	array();
		if($bean->GoogleFriends){
			$fbids = '';
			foreach($bean->GoogleFriends as $key=>$value){
				$fbids .= $value.',';
			}
			if($fbids != ''){
				$fbids = trim($fbids,',');
				$sql = "select OtherUserUniquevalue from invites where OtherUserUniquevalue in (".$fbids.") and inviteType = 1 and fkUsersId = ".$bean->UserId;
				$inviteResult = R::getAll($sql);
			}
			
			$invitedIdArray = array();
			if($inviteResult){
				foreach($inviteResult as $key=>$val){
					$invitedIdArray[$key] = $val['OtherUserUniquevalue'];
				}
			}
			foreach($bean->GoogleFriends as $fkey=>$value){
				$response['GoogleFriends'][$fkey]['id'] = $value;
				if(in_array($value,$invitedIdArray))
					$response['GoogleFriends'][$fkey]['AlreadyInvited'] = 1;
				else
					$response['GoogleFriends'][$fkey]['AlreadyInvited'] = 0;
			}
		}
		if($bean->ContactFriends){
			$contactids = '';
			foreach($bean->ContactFriends as $key=>$value){
				$contactids .= addslashes($value).',';
			}
			if($contactids != ''){
				$contactids 			= trim($contactids,',');
				$contactids				=	"'".str_replace(",","','",$contactids)."'";
				$sql 					= "select OtherUserUniquevalue from invites where OtherUserUniquevalue in (".$contactids.") and inviteType = 2 and fkUsersId = ".$bean->UserId;
				$inviteContactResult 	= R::getAll($sql);
			}
			
			$invitedContactArray = array();
			if($inviteContactResult){
				foreach($inviteContactResult as $key=>$val){
					$invitedContactArray[$key] = $val['OtherUserUniquevalue'];
				}
			}
			foreach($bean->ContactFriends as $ckey=>$value){
				$response['ContactFriends'][$ckey]['id'] = $value;
				if(in_array($value,$invitedContactArray))
					$response['ContactFriends'][$ckey]['AlreadyInvited'] = 1;
				else
					$response['ContactFriends'][$ckey]['AlreadyInvited'] = 0;
			}
		}
		return $response;
    }
	
	/**
	* Get user friend list
	*/
    public function usersFriendsList($type=''){ // Tuplit user friendlist
		/**
		* Get the bean
		* @var $bean Friends
		*/
        $bean 	= 	$this->bean;
		$start	=	0;
		$end	=	10;
		if(isset($bean->Start))
			$start				=	$bean->Start;
		
		$friendsId	=	$search = '';
		$friendsIdArray	=	$friendsArray	= $friendsListArray	= 	$orderIdsArray	=	$orderDetails	=	array();		
		
		$condition			=	'';
		if(isset($bean->Search) && !empty($bean->Search)){
			$search			=	$bean->Search;
			$condition		.=	" and (FirstName like '%".trim($search)."%' || LastName like '%".trim($search)."%') ";
		}
		if($bean->UserId != '') {
			$userId			=	$bean->UserId;
			$sql 			= 	"SELECT  *  FROM friends 
									where  (fkUsersId = ".$userId." or fkFriendsId = ".$userId.") and Status = 1 ";
	   		$friends		= 	R::getAll($sql);			
			if($friends){
				foreach($friends as $val) {
					if($val['fkFriendsId'] != $userId) {
						if(!in_array($val['fkFriendsId'],$friendsIdArray))							
							$friendsIdArray[]	=	$val['fkFriendsId'];
					}
					if($val['fkUsersId'] != $userId) {
						if(!in_array($val['fkUsersId'],$friendsIdArray))						
							$friendsIdArray[]	=	$val['fkUsersId'];
					}		
				}
				$friendsId	=	implode(',',$friendsIdArray);
			}
			$fri_con = '';
			if($type == 1){	
				$condition .= " and WalletId != '' and OrderStatus = 1";
				$orderlimit = ' ORDER BY orderid desc limit 0, 3';
			} else {
				$fri_con	= " and OrderStatus = 1";
				$orderlimit = "ORDER BY u.FirstName asc limit $start, $end";
			}
			
			if(!empty($friendsId) )
				$condition		.= " and u.id IN (".$friendsId.")";	
			else {
				if($type == '')
					throw new ApiException("Friends not found" ,  ErrorCodeType::UserFriendsListError);
			}
			if(!empty($friendsId) ){
				$sql 	=  	"SELECT SQL_CALC_FOUND_ROWS u.id,u.FirstName,u.LastName,u.Photo,u.Email,u.FBId,u.GooglePlusId,max( o.id ) AS orderid, o.fkMerchantsId AS merchantId FROM users u 
						LEFT JOIN orders o ON ( u.id = o.fkUsersId ".$fri_con.")
						where  u.id != ".$userId." and u.Status = 1 ".$condition."  GROUP BY u.id ".$orderlimit;
				$friendsArray		=  	R::getAll($sql);
			}
			$totalRec 			=  	R::getAll('SELECT FOUND_ROWS() as count');
			$total 				= 	(integer)$totalRec[0]['count'];
			$listedCount		=  	count($friendsArray);
			if($friendsArray){
				$orderIds = '';
				foreach($friendsArray as $key => $value){
					if(!empty($value['orderid'])){
						if(!in_array($value['orderid'],$orderIdsArray))
							$orderIdsArray[]	=	$value['orderid'];
					}								
				}
				if(count($orderIdsArray) > 0)
					$orderIds	=	implode(',',$orderIdsArray);
				if(!empty($orderIds)) {
					$sql1 =  "SELECT m.id as MerchantId,m.CompanyName,o.id as OrderId from orders o
								left join merchants m on (o.fkMerchantsId = m.id)
								where o.id in (".$orderIds.") and o.OrderStatus = 1";
					$temp =  R::getAll($sql1);
					if($temp) {
						foreach($temp as $tval) 
							$orderDetails[$tval['OrderId']]	=	$tval['CompanyName'];
					}
				}
				foreach($friendsArray as $key => $value){
					$user_image_path 			= 	MERCHANT_SITE_IMAGE_PATH.'no_user.jpeg';
					if(isset($value['Photo']) && $value['Photo'] != '')
						$user_image_path 		= 	USER_IMAGE_PATH.$value['Photo'];
					$value['Photo'] 			= 	$user_image_path;
					
					$value['CompanyName']		=	'';
					if(!empty($value['orderid']) && isset($orderDetails[$value['orderid']]))
						$value['CompanyName']	=	$orderDetails[$value['orderid']];
						
					unset($value['orderid']);
					unset($value['merchantId']);
					$friendsListArray[]			= 	$value;
				}			
			}
			if((count($friendsListArray) == 0) &&  $type == '') {
				// No friends found for this user
				throw new ApiException("Friends not found" ,  ErrorCodeType::UserFriendsListError);
			} else {
				$usersFriendsList['result'] 		= 	$friendsListArray;
				$usersFriendsList['totalCount']   	= 	$total;
				$usersFriendsList['listedCount']   	= 	count($friendsListArray);
				return $usersFriendsList;
			}		
		}
	}
	
	/**
	* @param  user invitation
	*/
    public function inviteFriend(){
	
		/**
		* Get the bean
		* @var $bean Friends
		*/
		$bean 				= 	$this->bean;
		$userId				=	$bean->UserId;
		$fbId				=	$bean->GoogleId;
		$inviteType			=	$bean->InviteType;
		$cellNumber			=	$bean->CellNumber;
		$email				=	$bean->Email;

		if($fbId != ''){
			$uniqueValue	=	$fbId;
			$inviteType		=	 1;
		}
		else if($cellNumber != ''){
			$uniqueValue	=	$cellNumber;
			$inviteType 	= 	2;
		}
		else if($email != ''){
			$uniqueValue	=	$email;
			$inviteType 	= 	2;
		}
		//validate user
		$this->validateUser($userId);
		
		//validate FbId and invite type
		//if($bean->GoogleId == '' && $bean->CellNumber == '')
		$this->validateParams();
			
		$this->validateInvite();
		
		$invite 						= 	R::dispense('invites');
		$invite->fkUsersId 				=	$userId;
		$invite->inviteType 			=	$inviteType;	
		$invite->OtherUserUniquevalue  	=   $uniqueValue;	
		$inviteId 						=  	R::store($invite);
		
		if($inviteType == 1)
			$userprofile = R::findOne('users', 'GooglePlusId = ? and Status = ?', array($uniqueValue,1));
		else if($inviteType == 2)
			$userprofile = R::findOne('users', 'Email = ? and Status = ?', array($uniqueValue,1));
		if($userprofile) {	
			$sql	=	"select id,Status from friends where 1 and ((fkUsersId =".$userId." and fkFriendsId=".$userprofile['id']." ) or (fkUsersId =".$userprofile['id']." and fkFriendsId=".$userId." ))";
			$friends =	R::getAll($sql);
			if($friends) {
				if($friends[0]['Status'] == 1) {
					throw new ApiException("You're already friend with this user. Awesome!", ErrorCodeType::InvitingOwnFriends);
				} else {
					$sql = "update friends set Status=1 where id=".$friends[0]['id'];
					R::exec($sql);
				}
			}	else {
				$insfriends 				= 	R::dispense('friends');
				$insfriends->fkUsersId 		=	$userId;
				$insfriends->fkFriendsId 	=	$userprofile['id'];	
				$insfriends->Status  		=  	1;	
				$insfriends->DateCreated	=   date('Y-m-d H:i:s');
				$friendId 					=  	R::store($insfriends);
			}
			//throw new ApiException("User you invited is already registered in Tuplit and added as your friend", ErrorCodeType::AlreadyRegisteredUser);
			return 1;
		}
		return $inviteId;
	}
	
	/**
	* validate FbId and invite type
	* @throws ApiException if the models fails to validate
	*/
	public function validateParams()
    {
		$bean 		= 	$this->bean;
		if($bean->GoogleId == '' && $bean->Email == '' && $bean->CellNumber == ''){
			$rules 	= 	[
							'required' => [
								 ['CellNumber'],['Email'],['GoogleId']
							],
							
						];
			$v 			= 	new Validator($this->bean);
			$v->rules($rules);
			if (!$v->validate()) {
				$errors = $v->errors();
				// the action was not found
				throw new ApiException("Please check the invite properties. Fill GoogleId,CellNumber,Email with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
			}
		}
        
    }
	
	/**
	* Validate the modification
	*/
	public function validateUser($userId,$type='')
    {
		/**
		* Get the identity of the person requesting the details
		*/
		$requestedBy = R::findOne('users', 'id = ? and Status = ?', [$userId,StatusType::ActiveStatus]);
        if (!$requestedBy) {
            // the User was not found
			if($type == 1)
				throw new ApiException("User you requested was not in active state", ErrorCodeType::UserNotInActiveStatus);
			else
				throw new ApiException("Your status is not in active state", ErrorCodeType::UserNotInActiveStatus);
        }
    }
	
	
	public function validateInvite(){
		/**
		* Get the bean
		* @var $bean Friends
		*/
        $bean = $this->bean;
		if($bean->GoogleId != '')
	        $existingAccount = R::findOne('invites', 'OtherUserUniquevalue = ? and fkUsersId = ?  and inviteType = 1', array($bean->GoogleId,$bean->UserId));
		else if($bean->CellNumber != '')
	        $existingAccount = R::findOne('invites', 'OtherUserUniquevalue = ? and fkUsersId = ? and inviteType = 2', array($bean->CellNumber,$bean->UserId));
		else if($bean->Email != '')
	        $existingAccount = R::findOne('invites', 'OtherUserUniquevalue = ? and fkUsersId = ? and inviteType = 2', array($bean->Email,$bean->UserId));
        if ($existingAccount) {	
            // an account with that FacebookId or CellNumber already exists in the system - don't create invite
            throw new ApiException("Already invitation sent", ErrorCodeType::AlreadyInvited);
		}
	}
	
	/**
	* @param makeInviteFriends function makes app users as friends if he/she invited you already
	*/
    public function makeInviteFriends(){
	
		//params
		$condition			=	'';
		$friendsArray 		=	Array();
		$i					=	0;
		
		//getting bean data's
		$bean 				= 	$this->bean;
		$userId				=	$bean->UserId;		
		
		//making friends condition
		if(!empty($bean->GooglePlusId))
			$condition		.=	",'".$bean->GooglePlusId."'";
		if(!empty($bean->Email))
			$condition		.=	",'".addslashes($bean->Email)."'";
		
		if($condition != '' && $userId != ''){
			$condition		=	trim($condition,',');			
			$sql 			= 	"select fkUsersId as friendsId from invites where OtherUserUniquevalue in (".$condition.")";
			$inviteResult 	= 	R::getAll($sql);
			if(isset($inviteResult) && count($inviteResult) > 0){
				foreach($inviteResult as $value)
					$friendsArray[]	=	$value['friendsId'];
				
				//friendsIds unique
				$friendsArray =	array_unique($friendsArray);

				//friends process
				foreach($friendsArray as $value){
					$friends[] 						= 	R::dispense('friends');
					$friends[$i]->fkUsersId 		=	$userId;
					$friends[$i]->fkFriendsId 		=	$value;	
					$friends[$i]->Status  			=   1;	
					$friends[$i]->DateCreated		=   date('Y-m-d H:i:s');
					$i++;
				}
				if($i > 0)
					R::storeAll($friends);
			}
		}
	}
	
	/**
    * Add Friend
    */
	public function AddFriend(){
		
		/**
		* Get the bean
		*/
		$bean 	= 	$this->bean;
		$this->validateUser($bean->ToUserId,1);
		$friends	=	R::getAll("SELECT id,Status from friends where 1 and ((fkUsersId = '".$bean->UserId."' and fkFriendsId='".$bean->ToUserId."') or (fkUsersId = '".$bean->ToUserId."' and fkFriendsId='".$bean->UserId."'))");
		if($friends) {
			if($friends[0]['Status'] == 1) {
				throw new ApiException("You're already friend with this user. Awesome!" ,  ErrorCodeType::InvitingOwnFriends);			
			} else {
				$sql	=	"update friends set Status = 1 where id = ".$friends[0]['id'];
				$friendId 	=	R::exec($sql);
				return $friendId;
			}		
		} else {
			$friends 				= 	R::dispense('friends');
			$friends->fkUsersId		=	$bean->UserId;
			$friends->fkFriendsId	=	$bean->ToUserId;
			$friends->Status		=	1;
			$friends->DateCreated	=	date('Y-m-d H:i:s');
			// save the bean to the friends table
			$friendId 				= 	R::store($friends);
			return $friendId;
		}
	}
}
