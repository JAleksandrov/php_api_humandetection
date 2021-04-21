<?php
$servername = "localhost";
$username = "api";
$password = "CIiidlPMjGEd9Gxf";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 

mysqli_select_db($conn,"human_detection");
?>