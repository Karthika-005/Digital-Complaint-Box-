<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "village_complaints"; // make sure this DB exists

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uname = $_POST["username"];
    $email = $_POST["email"];
    $panchayat = $_POST["panchayat_name"];
    $pass = $_POST["password"];
    $role = $_POST["role"];

    $sql = "INSERT INTO users (username, email, panchayat_name, password, role)
            VALUES ('$uname', '$email', '$panchayat', '$pass', '$role')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Registered Successfully! Redirecting to login...'); 
              window.location.href='auth.html';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
