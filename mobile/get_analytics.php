<?php
header('Content-Type: application/json');
require_once '../db_connect.php';



// Variable Initilization
$username = "";
$password = "";



// Parameters
if (isset($_REQUEST["username"])) {
	$username = $_REQUEST['username'];
}

if (isset($_REQUEST["password"])) {
	$password = $_REQUEST['password'];
}


// Validation if parameters were used
if ($username != "" && $password != "") { 

    $json_output = array(); // initilize json  output

    // Query
    $Fetch_Analytics_SQL = "SELECT 
    count(*) AS Analysed_Images, 
    DATE_FORMAT(Analysed_Date, '%d/%m/%Y') AS Analysed_Date
    FROM Image_Analysation 
    INNER JOIN Motion_Detection_Log ON Motion_Detection_Log.Motion_ID = Image_Analysation.Motion_ID 
    INNER JOIN Raspberry_Authentication ON Raspberry_Authentication.Raspberry_ID = Motion_Detection_Log.Raspberry_ID 
    INNER JOIN Mobile_Users ON Mobile_Users.Raspberry_ID = Raspberry_Authentication.Raspberry_ID 
    WHERE Mobile_Users.Mobile_Username = '$username' AND Mobile_Users.Password = '$password' 
    AND MONTH(Image_Analysation.Analysed_Date) = MONTH(CURRENT_DATE()) 
    AND YEAR(Image_Analysation.Analysed_Date) = YEAR(CURRENT_DATE()) 
    GROUP BY DATE(Image_Analysation.Analysed_Date)";


    $Fetch_Analytics_Output = $conn->query($Fetch_Analytics_SQL);
    // Check if row was found
    if(mysqli_num_rows($Fetch_Analytics_Output) > 0){
        
        while ($row = mysqli_fetch_assoc($Fetch_Analytics_Output)) {
            $json_output[] = $row; // adds to array row
        }
    
        echo json_encode($json_output);  // output in json format
        setHeader(200); // update header status code

    }else{
        // No record found
        setHeader(401);
    }

    
  
}else{ // Parameters incorrect
    setHeader(400);
}


function setHeader($status){    // updates status code for header
    header("Status: ".$status);
}


$conn->close();

?>