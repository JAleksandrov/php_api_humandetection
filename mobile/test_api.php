<?php
header('Content-Type: application/json');
require_once '../db_connect.php';


$user = "";

if (isset($_REQUEST["user"])) {
	$user = $_REQUEST['user'];
}


if ($user != "") { 

    $json_output = array();

    $sql_check = "SELECT Motion_Detection_Log.File_Name, Image_Analysation.Confidence FROM Image_Analysation INNER JOIN Motion_Detection_Log ON Motion_Detection_Log.Motion_ID = Image_Analysation.Motion_ID INNER JOIN Raspberry_Authentication ON Raspberry_Authentication.Raspberry_ID = Motion_Detection_Log.Raspberry_ID INNER JOIN Mobile_Users ON Mobile_Users.Raspberry_ID = Raspberry_Authentication.Raspberry_ID WHERE Mobile_Users.Mobile_Username = '$user'";
    $query_check = $conn->query($sql_check);
  
    while ($row = mysqli_fetch_assoc($query_check)) {
        $json_output[] = $row;
    }

    echo json_encode($json_output);   
  
}


$conn->close();
?>


