<?php

// databaseb connection
require_once '../db_connect.php';

// initialization variables
$confidence = "";
$file_name = "";


// store variable from parameters

if (isset($_REQUEST["confidence"])) {
	$confidence = $_REQUEST['confidence'];
}

if (isset($_REQUEST["file_name"])) {
	$file_name = $_REQUEST['file_name'];
}

// check if variables are not empty - validation
if ($file_name != "" && $confidence != "") { 

    // SQL query to check if the file name already exists
    $sql_check = "SELECT * FROM Image_Analysation INNER JOIN Motion_Detection_Log ON Image_Analysation.Motion_ID = Motion_Detection_Log.Motion_ID WHERE Motion_Detection_Log.File_Name = '$file_name'";
  
    $query_check = $conn->query($sql_check);
    $row = mysqli_fetch_array($query_check);
    if (empty($row)) { // IF the filename does not exist then insert a new record with confidence, date and motion ID
        $insert_analyse = "INSERT INTO Image_Analysation (Confidence, Analysed_Date, Motion_ID) VALUES ('$confidence',NOW(),(SELECT Motion_ID FROM Motion_Detection_Log WHERE File_Name = '$file_name'))";
        if ($conn->query($insert_analyse) === TRUE) {
            // Insert Success.

          //Mysql Query to find all notification tokens based on file name
          $find_tokens = "SELECT Mobile_Notification_Tokens.Notification_Token, Mobile_Notification_Tokens.Operating_System FROM Mobile_Notification_Tokens INNER JOIN Mobile_Users ON Mobile_Notification_Tokens.Mobile_ID = Mobile_Users.Mobile_ID INNER JOIN Raspberry_Authentication ON Mobile_Users.Raspberry_ID = Raspberry_Authentication.Raspberry_ID INNER JOIN Motion_Detection_Log ON Motion_Detection_Log.Raspberry_ID = Raspberry_Authentication.Raspberry_ID WHERE Motion_Detection_Log.File_Name = '$file_name'";
          $tokens_output = $conn->query($find_tokens);
        
          // If any tokens are found
          if(mysqli_num_rows($tokens_output) > 0){

            // Initilize variables.
            $tokens= array();
            $tokens_os= array();
            $count_token = 0;
            
            // Store MySQL tokens output to variable
            while ($row = mysqli_fetch_assoc($tokens_output)) {
                                
               $tokens[$count_token] = $row['Notification_Token'];
               $tokens_os[$count_token] = $row['Operating_System'];
               
               $count_token++;
            }

            // If any tokens are stored then send a notification
            if ($count_token > 0){
                //Function to send notification
                sendPushMsg($tokens,$tokens_os,$confidence,date("d.m.Y H:i:s"));
                echo "Notification Sent";
            }else{
                echo "Error sending notifications";
            }

          }
            echo "OK";
        } else {
            // Record insert error
            echo "Error 01";
        }
    } else {
        // Record existing
        echo "Error 02";
    } 
}


function sendPushMsg($tokens,$tokens_os,$confidence,$current_datetime) {
	
    //Firebase ACESS KEY
	define('API_ACCESS_KEY','SECRET');

   


    // message for notification body
	$bodyText= "Human detected confidence ".$confidence." at ".$current_datetime ;
    //. $confidence + " at " . $current_datetime;

 echo $bodyText;
   
		
		$notification = [
            "title" => "Human Detection",
			"body" => $bodyText,
			 "sound" => "default"
        ];
        
        $extraNotificationData = [
        	"message" => $notification,
			"sound" => "default",

		];
		
		$fcmNotification;
		
        // device OS count
		$tAndroidCount=0;
		$tIOSCount=0;
		
        // tokens for individual OS
		$tokensAndroid= array();
		$tokensIOS= array();

        // Filter incomming tokens
        
		for ($i=0;$i<sizeof($tokens);$i++) {
			
			$token=$tokens[$i]; // assign current token
            
			$version=strToLower($tokens_os[$i]);
			
			if ($version=="" || strpos($version, 'android') !== false) { // check if it is Android token
				
              //  echo "Android found";
                // store android token and add count
				$tokensAndroid[$tAndroidCount]=$token;
				$tAndroidCount++;

			}
			else {
                // iOS token
           
				$tokensIOS[$tIOSCount]=$token;
				$tIOSCount++;
                             
        	}
         
        
        }

        //Set headers with Firebase acces key & content-type
        $headers = [
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        ];

    //Firebase FCM server
    $url = 'https://fcm.googleapis.com/fcm/send';
    

    // Send Android customised notification
    if ($tAndroidCount>0) {
    	
        $fcmNotification = [
        'registration_ids' => $tokensAndroid,
        'priority' => 'high',
        'notification' => $notification,
		'data' => $extraNotificationData
        ];
        
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL,$url);
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
    	$result = curl_exec($ch);
    	curl_close ($ch);
    }
    
    //Send iOS customised notification
   
    if ($tIOSCount>0) {
    	


        $fcmNotification = [
        'registration_ids' => $tokensIOS,
        'priority' => 'high',
        'notification' => $notification,
        'data' => $extraNotificationData
        ];
        
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL,$url);
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));

    	$result = curl_exec($ch);

    	curl_close ($ch);
    }
}

//Close Connection
$conn->close();
?>



