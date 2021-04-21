<?php
header('Content-Type: application/json');
require_once '../db_connect.php';


// Initilize variables
$username = "";
$password = "";



// Parameters
if (isset($_REQUEST["username"])) {
	$username = $_REQUEST['username'];
}

if (isset($_REQUEST["password"])) {
	$password = $_REQUEST['password'];
}

if (isset($_REQUEST["token"])) {
	$token = $_REQUEST['token'];
}



// Validate parameters
if ($username != "" && $password != "") { 

    $json_output = array();

    // Query
    $login_SQL = "SELECT Mobile_ID AS Authorisation FROM Mobile_Users WHERE Mobile_Username = '$username' AND Password = '$password'";
    $login_output = $conn->query($login_SQL);

    // Check for rows
    if(mysqli_num_rows($login_output) > 0){
       
        while ($row = mysqli_fetch_assoc($login_output)) {
            $json_output[] = $row; // add to array row
        }
        if ($token != ""){ // if token is found it will update token in this stage
            UpdateToken($username, $token, $conn);
        }

        echo json_encode($json_output);  // Output JSON format response 
        setHeader(200); // Status code OK

    }else{
        // No rows found
        setHeader(401);
    }

    
  
}else{
    setHeader(400); // Failed to provide parameters
}

function UpdateToken($username, $token, $conn){
    // Update notification token function
    $Token_Query = "UPDATE Mobile_Notification_Tokens SET Mobile_ID = (SELECT Mobile_ID FROM Mobile_Users WHERE Mobile_Username = '$username') , Last_Seen = NOW() WHERE  Notification_Token = '$token'";
	$Token_Query_Output = $conn->query($Token_Query);

}

function setHeader($status){
    // Update header
    header("Status: ".$status);
}


$conn->close();
?>


