<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

include("db.php");

$username = $_SESSION['username'];
$panchayat = $_SESSION['panchayat_name'] ?? '';
$filter = $_GET['filter'] ?? '';

// Fetch complaints only for user's panchayat
$sql = "SELECT * FROM complaints WHERE panchayat_name = '$panchayat'";
if (!empty($filter)) {
    $sql .= " AND category = '$filter'";
}
$sql .= " ORDER BY date_submitted DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Panel - <?php echo htmlspecialchars($username); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    /* [Styles same as before, keeping everything modern and neat] */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    body { background: #e7f0fd; padding: 30px; color: #333; }
    .container { max-width: 1200px; margin: auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .top-bar h2 { color: #2c3e50; font-weight: 600; }
    .btn { padding: 10px 20px; background: #2980b9; color: #fff; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; transition: 0.3s; }
    .btn:hover { background: #216b9b; }
    .logout { background: #e74c3c; }
    .form-container { display: none; background: #f4faff; padding: 25px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #dce8f5; }
    .form-container input, .form-container select, .form-container textarea {
      width: 100%; margin: 10px 0; padding: 12px; border-radius: 8px; border: 1px solid #ccc; font-size: 15px;
    }
    .form-container textarea { height: 100px; resize: vertical; }
    .filter-bar { margin-bottom: 20px; }
    .filter-bar select { padding: 8px; border-radius: 6px; border: 1px solid #ccc; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; text-align: center; border: 1px solid #e0e0e0; }
    th { background: #3498db; color: #fff; }
    td img { border-radius: 8px; max-height: 60px; cursor: pointer; transition: 0.2s; }
    td img:hover { transform: scale(1.05); }
    .modal { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center; }
    .modal img { max-width: 90%; max-height: 80vh; border-radius: 10px; }
    .modal-close { position: absolute; top: 20px; right: 30px; font-size: 30px; color: #fff; cursor: pointer; }
  </style>
</head>
<body>

<div class="container">
  <div class="top-bar">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?> 👋</h2>
    <div>
      <button class="btn" onclick="toggleForm()">+ Add Complaint</button>
      <a href="auth.html" class="btn logout">Logout</a>
    </div>
  </div>

  <!-- Complaint Form -->
  <div class="form-container" id="complaintForm">
    <form action="submit_complaint.php" method="POST" enctype="multipart/form-data">
      <input type="text" name="title" placeholder="Complaint Title" required>
      <textarea name="description" placeholder="Complaint Description" required></textarea>
      <select name="category" required>
        <option value="">Select Category</option>
        <option value="Water">Water</option>
        <option value="Road">Road</option>
        <option value="Streetlight">Streetlight</option>
        <option value="Sanitation">Sanitation</option>
      </select>
      <input type="text" name="address" placeholder="Your Address" required>
      <input type="file" name="user_photo" accept=".jpg">
      <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
      <input type="hidden" name="panchayat_name" value="<?php echo htmlspecialchars($panchayat); ?>">
      <button type="submit" class="btn">Submit</button>
    </form>
  </div>

  <!-- Filter Dropdown -->
  <div class="filter-bar">
    <form method="GET">
      <label><strong>Filter by Category:</strong></label>
      <select name="filter" onchange="this.form.submit()">
        <option value="">-- All --</option>
        <option value="Water" <?php if($filter=="Water") echo "selected"; ?>>Water</option>
        <option value="Road" <?php if($filter=="Road") echo "selected"; ?>>Road</option>
        <option value="Streetlight" <?php if($filter=="Streetlight") echo "selected"; ?>>Streetlight</option>
        <option value="Sanitation" <?php if($filter=="Sanitation") echo "selected"; ?>>Sanitation</option>
      </select>
    </form>
  </div>

  <!-- Complaints Table -->
  <table>
    <tr>
      <th>#</th>
      <th>Title</th>
      <th>User Image</th>
      <th>Address</th>
      <th>Category</th>
      <th>Status</th>
      <th>Admin Image</th>
      <th>Date</th>
    </tr>
    <?php $count = 1; while($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
      <td><?= $count++ ?></td>
      <td><?= htmlspecialchars($row['title']) ?></td>
      <td>
        <?php if (!empty($row['user_photo'])): ?>
          <img src="uploads/<?= htmlspecialchars($row['user_photo']) ?>" onclick="showImage(this.src)">
        <?php else: ?>
          No Image
        <?php endif; ?>
      </td>
      <td><?= htmlspecialchars($row['address']) ?></td>
      <td><?= htmlspecialchars($row['category']) ?></td>
      <td><?= htmlspecialchars($row['status']) ?></td>
      <td>
        <?php if (!empty($row['admin_photo'])): ?>
          <img src="uploads/<?= htmlspecialchars($row['admin_photo']) ?>" onclick="showImage(this.src)">
        <?php else: ?>
          No Image
        <?php endif; ?>
      </td>
      <td><?= htmlspecialchars($row['date_submitted']) ?></td>
    </tr>
    <?php } ?>
  </table>
</div>

<!-- Modal for Image -->
<div id="imgModal" class="modal" onclick="hideImage()">
  <span class="modal-close" onclick="hideImage()">&times;</span>
  <img id="modalImage" src="">
</div>

<script>
function toggleForm() {
  const form = document.getElementById("complaintForm");
  form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
}

function showImage(src) {
  const modal = document.getElementById("imgModal");
  const modalImg = document.getElementById("modalImage");
  modalImg.src = src;
  modal.style.display = "flex";
}

function hideImage() {
  const modal = document.getElementById("imgModal");
  modal.style.display = "none";
}
</script>

</body>
</html>
