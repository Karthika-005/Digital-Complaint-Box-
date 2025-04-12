<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "village_complaints";

// DB connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Login logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['Email'];
    $pass = $_POST['password'];
    $panchayat = $_POST['panchayat_name'];
    $role = $_POST['role'];

    // Secure query (prevent SQL injection)
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ? AND panchayat_name = ? AND role = ?");
    $stmt->bind_param("ssss", $email, $pass, $panchayat, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Store session variables
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['panchayat_name'] = $row['panchayat_name']; // ✅ Corrected this line

        // Redirect based on role
        if ($row['role'] === 'admin') {
            header("Location: admin.php");
        } elseif ($row['role'] === 'user') {
            header("Location: user.php");
        }
        exit();
    } else {
        echo "<script>alert('Invalid credentials. Please try again.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
