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
			$data = array(
			    'TargetArn' => $EndpointArn,
			    'MessageStructure' => 'json',
			    'Message' => json_encode(array(
			        'GCM' => json_encode(array(
			            'data' => array('message' => $message,
										'badge'=>(integer)$badge ,
										'sound' => 'default',
										'processId' => $processId,
										'type' => $type,
										'userId' => $userId ,
										'unreadMessage'=>$unreadCount),
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
			
			$data = array(
				    'TargetArn' => $EndpointArn,
				    'MessageStructure' => 'json',
				    'Message' => json_encode(array(
						$apns => json_encode(array(
				            'aps' => array('alert' => $message,
											'badge'=> (integer)$badge ,
											'sound' => 'default',
											'processId' => $processId,
											'type' => $type,
											'userId' => $userId ,
											'unreadMessage'=>$unreadCount),
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
		print($EndpointArn . " - Failed: " . $e->getMessage() . "!\n");
		return 0;
	  }
  }
?>