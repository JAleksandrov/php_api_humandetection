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

// Validate parameters
if ($username != "" && $password != "") { 

    $json_output = array();

    // Query
    $Fetch_Images_SQL ="SELECT 
    Image_Analysation.Analysed_ID, 
    Motion_Detection_Log.File_Name, 
    Image_Analysation.Confidence, 
    Image_Analysation.Analysed_Date 
    FROM Motion_Detection_Log 
    INNER JOIN Image_Analysation ON Image_Analysation.Motion_ID = Motion_Detection_Log.Motion_ID 
    INNER JOIN Raspberry_Authentication ON Raspberry_Authentication.Raspberry_ID = Motion_Detection_Log.Raspberry_ID 
    INNER JOIN Mobile_Users ON Mobile_Users.Raspberry_ID = Raspberry_Authentication.Raspberry_ID 
    WHERE Mobile_Users.Mobile_Username = '$username' AND Mobile_Users.Password = '$password' 
    ORDER BY Image_Analysation.Analysed_Date ASC";


    $Fetch_Images_Output = $conn->query($Fetch_Images_SQL);
    // If images found
    if(mysqli_num_rows($Fetch_Images_Output) > 0){
        
        while ($row = mysqli_fetch_assoc($Fetch_Images_Output)) {
            $json_output[] = $row; //Json output
        }
    
        echo json_encode($json_output);  // Output images found 
        setHeader(200); // status code OK

    }else{// No iamges found
        setHeader(400);
    }

    
  
}else{ // Wrong parameters
    setHeader(400);
}


function setHeader($status){ // Update header status code.
    header("Status: ".$status);
}


$conn->close();

?>