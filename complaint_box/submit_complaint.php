<?php
session_start();
include("db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $panchayat = $_POST['panchayat_name'];

    $user_photo = '';
    if (isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] == 0) {
        $ext = pathinfo($_FILES['user_photo']['name'], PATHINFO_EXTENSION);
        $user_photo = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['user_photo']['tmp_name'], "uploads/" . $user_photo);
    }

    $sql = "INSERT INTO complaints (title, description, category, address, username, panchayat_name, user_photo, status, date_submitted)
            VALUES ('$title', '$description', '$category', '$address', '$username', '$panchayat', '$user_photo', 'Pending', NOW())";

    if (mysqli_query($conn, $sql)) {
        header("Location: user.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: user.php");
}
?>
