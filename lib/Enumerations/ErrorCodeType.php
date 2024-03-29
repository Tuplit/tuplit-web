<?php

/**
 * Description of Account Types
 *
 * @author 
 */

namespace Enumerations;


class ErrorCodeType {
	const SomeFieldsMissing 				= 1000;
	const EmailAlreadyExists 				= 1001;
    const FbIdAlreadyExists 				= 1002;
	const GooglePlusIdAlreadyExists 		= 1003;
	const UserNameAlreadyExists 			= 1004;
	const NoAuthoriseToRequestForPassword 	= 1005;
	const ErrorInUpdateForgetPassword 		= 1006;
	const NoUserNameExists 					= 1007;
	const PincodeAlreadyExists 				= 1008;
	const NotAccessToDoProcess 				= 1009;
	const UserNotFound 						= 1010;
	const UserNotInActiveStatus 			= 1011;
	const userNotAllowedToDoProcess 		= 1012;
	const userNotAllowedToAddOrBlockOwn 	= 1013;
	const userNotAllowedToMessageOwn 		= 1014;
	const ErrorInProcessing 				= 1015;
	const ErrorInSaving 					= 1016;
	const ProblemInImage 					= 1017;
	const ProblemInVIdeo 					= 1018;
	const ProblemInAudio 					= 1019;
	const CellNumberAlreadyExists 			= 1020;
	const NoEmailExists	 					= 1021;
	const PasswordResetNotValid	 			= 1022;	
	
	const NoResultFound 					= 2000;
	const SomeFieldsRequired 				= 3000;
	
	const MerchantsNotInActiveStatus 		= 2001;
	const AlreadyFavoured 					= 2002;
	const FavouriteNotAllowed 				= 2003;
	const FavouriteTypeError 				= 2004;
	const UserFavouriteListError 			= 2005;
	const UserFavouriteListEmpty 			= 2006;
	const NotAllowToDeleteComments 			= 2007;
	const UserFriendsListError	  			= 2008;	
	const CheckBalanceError 				= 2009;
	const CheckLocationError 				= 2010;
	const SetPinError 						= 2011;	
	const UpdatePinError 					= 2012;
	const AlreadyInvited 					= 2013;
	const verifyPinError 					= 2014;
	const CategoryAlreadyExist 				= 2015;
	const CartIdAlreadyExist 				= 2016;
	const OrderAlreadyApproved 				= 2017;
	const OrderAlreadyRejected 				= 2018;
	const NotAllowed 						= 2019;
	const UserOnlyCanRequestProfile 		= 2020;
	const UpdateSettingsError 				= 2021;
	const ErrorInSettingTypeOrSettingAction = 2022;
	const SetPinValidateError 				= 2023;
	const PaymentAccountError 				= 2025;
	const PaymentError 						= 2026;
	const ErrorInCardRegistration 			= 2027;
	const ErrorInPaymentRegistration 		= 2028;
	const ErrorInCardDelete 				= 2029;
	const InvalidRefundUser 				= 2030;
	
	const AlreadyRefunded 					= 001401;	
	const ProductDiscountMust 				= 2223;
	const MerchantNotPublished 				= 2224;
	const AlreadyRegisteredUser 			= 2255;
	const InvitingOwnFriends 				= 2256;
}