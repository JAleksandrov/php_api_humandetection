<?php
require_once '../db_connect.php';

// Initilize variable
$token = "";
$username = "";
$operating_system = "";

// Parameters
if (isset($_REQUEST["token"])) {
$token = $_REQUEST['token'];
}

if (isset($_REQUEST["username"])) {
$username = $_REQUEST['username'];
}

if (isset($_REQUEST["operating_system"])) {
$operating_system = $_REQUEST['operating_system'];
}


// Check if parameters are set
if ($token != "" && $operating_system != "") {
	
	$find_existing_tokens = "SELECT Token_ID AS FOUND_ROWS FROM Mobile_Notification_Tokens WHERE Notification_Token = '$token'";
    
  
    $existing_token_output = $conn->query($find_existing_tokens);
    
   
    // Check if token is found
    if(mysqli_num_rows($existing_token_output) > 0){
        // update if found
        $Token_Query = "UPDATE Mobile_Notification_Tokens SET Mobile_ID = (SELECT Mobile_ID FROM Mobile_Users WHERE Mobile_Username = '$username') , Last_Seen = NOW() WHERE  Notification_Token = '$token'";
	
    }else{
        // insert if not found
        $Token_Query = "INSERT INTO Mobile_Notification_Tokens (Notification_Token, Operating_System, Last_Seen, Mobile_ID) VALUES('$token', '$operating_system', NOW(), (SELECT Mobile_ID FROM Mobile_Users WHERE Mobile_Username = '$username'))";
    
    }

    // execute decision
    $Token_Query_Output = $conn->query($Token_Query);
}

$conn->close();
?>

