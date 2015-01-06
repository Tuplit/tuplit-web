<?php
require_once __DIR__ . '/aws.phar';
//require 'vendor/autoload.php';
use Doctrine\Common\Cache\FilesystemCache;
use Guzzle\Cache\DoctrineCacheAdapter;
// Create a cache adapter that stores data on the filesystem
$cacheAdapter = new DoctrineCacheAdapter(new FilesystemCache('/tmp/cache'));
// Provide a credentials.cache to cache credentials to the file system
$sns = Aws\Sns\SnsClient::factory(array(
	'credentials.cache' => $cacheAdapter,
	'region' => REGION
));
$badge = $badge + 1;

  if($EndpointArn  !=''){
	  try
	  {
	   if($platform == 2){
			$pushdata	=	array('message' => $message,
								'badge'=>(integer)$badge ,										
								'processId' => $processId,
								'type' => $type,
								'userId' => $userId,
								'merchantId' => $merchantId,
								'merchantName' => $merchantName,
								'notes' => $notes,
								'balance' => $userbalance );
			if($sound == 1)
				$pushdata['sound'] = $sound;
		
			$data = array(
			    'TargetArn' => $EndpointArn,
			    'MessageStructure' => 'json',
			    'Message' => json_encode(array(
			        'GCM' => json_encode(array(
			            'data' => $pushdata,
			        ))
			    ))
			 );
			$sns->publish($data);
		}
		else{
			if($_SERVER['SERVER_ADDR']=='172.21.4.104')
				$apns = 'APNS_SANDBOX';
		    else
		  		$apns = 'APNS';
				
			$pushdata	=	array('alert' => $message,
								'badge'=>(integer)$badge ,										
								'processId' => $processId,
								'type' => $type,
								'merchantId' => $merchantId,
								'merchantName' => $merchantName,
								'notes' => $notes,
								'OrderAmount' => $orderAmount );
			if($sound == 1)
				$pushdata['sound'] = $sound;
			
			$data = array(
				    'TargetArn' => $EndpointArn,
				    'MessageStructure' => 'json',
				    'Message' => json_encode(array(
						$apns => json_encode(array(
				            'aps' => $pushdata,
				        ))
				    ))
				 );
			$sns->publish($data);
		}
	  return 1;
	  }
	  catch (Exception $e)
	  {
	 //  echo'<pre>';print_r($data);echo'</pre>';
		//echo"<br>";
		//print($EndpointArn . " - Failed: " . $e->getMessage() . "!\n");
		return 0;
	  }
  }
?>