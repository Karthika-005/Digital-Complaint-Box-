<?php
$conn = new mysqli("localhost", "root", "", "village_complaints");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
