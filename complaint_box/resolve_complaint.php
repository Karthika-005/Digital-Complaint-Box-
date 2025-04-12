<?php
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['complaint_id'];
    $adminPhoto = $_FILES['admin_photo'];

    if ($adminPhoto['error'] === 0) {
        $filename = uniqid() . ".jpg";
        move_uploaded_file($adminPhoto['tmp_name'], "uploads/$filename");

        $sql = "UPDATE complaints 
                SET status = 'Resolved', admin_photo = '$filename' 
                WHERE id = $id";

        mysqli_query($conn, $sql);
    }
}

header("Location: admin.php");
exit();
?>
