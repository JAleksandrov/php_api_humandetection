<?php
require_once '../db_connect.php';

$uid = "";
$password = "";
$filename = 'img';

$folder_file_path = "../../motion_detector/uploads/"; // Directory where to upload files.
	
if (isset($_REQUEST["uid"])) {
	$uid = $_REQUEST['uid'];
}

if (isset($_REQUEST["password"])) {
	$password = $_REQUEST['password'];
}

if ($uid != "" && $password != "") { // Checking whether UID is specified in the POST request

	if ($_FILES[$filename]) { //Checking whether image is specified in the POST request
    
        $unix_time = time();
		$file_name= $unix_time.'_'.$uid.'.jpg';  // File name: 1612278554_0001.jpg (unix_uid_.jpg)
		$file_path = $folder_file_path.$file_name; // Combining directory path with file name.

		$insert_motion = "INSERT INTO Motion_Detection_Log (File_Name, Created_Date, Raspberry_ID) VALUES ('$file_name',NOW(),(SELECT Raspberry_ID FROM Raspberry_Authentication WHERE Raspberry_User_ID = '$uid' AND Password = '$password'))";

		if ($conn->query($insert_motion) === TRUE) {
		
			if (move_uploaded_file($_FILES[$filename]['tmp_name'], $file_path)) {
				//Saved Sucessfully	
				echo "OK";								    
			} 
			else{
				 // Saving not sucessful
				 echo "Error 01";	
		   	}   
		} else {
		echo "Error 02";
		}
    }
    else {
        // File is empty
		echo "File Empty";	
    }
}

$conn->close();
?>



