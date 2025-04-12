<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include("db.php");

// ✅ Get the admin's panchayat from session
$admin_panchayat = $_SESSION['panchayat_name'] ?? '';

$filter = $_GET['filter'] ?? '';

// ✅ Filter complaints based on panchayat
$sql = "SELECT * FROM complaints WHERE panchayat_name = ?";
$params = [$admin_panchayat];
$types = "s";

if (!empty($filter)) {
    $sql .= " AND category = ?";
    $params[] = $filter;
    $types .= "s";
}

$sql .= " ORDER BY date_submitted DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    body { background: #f0f4f8; padding: 30px; }
    .container { max-width: 1200px; margin: auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
    .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .top-bar h2 { color: #2c3e50; }
    .btn { padding: 10px 18px; background: #2980b9; color: white; border: none; border-radius: 8px; cursor: pointer; }
    .btn.logout { background: #e74c3c; }
    .filter-bar { margin-bottom: 20px; }
    .filter-bar select { padding: 8px; border-radius: 6px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
    th { background: #3498db; color: white; }
    td img { max-height: 60px; border-radius: 6px; cursor: pointer; }
    .modal { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center; }
    .modal img { max-width: 90%; max-height: 80vh; border-radius: 10px; }
    .modal-close { position: absolute; top: 20px; right: 30px; font-size: 30px; color: #fff; cursor: pointer; }
    form input[type="file"] { padding: 5px; }
    form button { margin-top: 5px; }
  </style>
</head>
<body>

<div class="container">
  <div class="top-bar">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h2>
    <a href="auth.html" class="btn logout">Logout</a>
  </div>

  <!-- Filter -->
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

  <!-- Table -->
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
      <th>Action</th>
    </tr>
    <?php $i = 1; while($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
      <td><?= $i++ ?></td>
      <td><?= htmlspecialchars($row['title']) ?></td>
      <td>
        <?php if (!empty($row['user_photo'])): ?>
          <img src="uploads/<?= htmlspecialchars($row['user_photo']) ?>" onclick="showImage(this.src)">
        <?php else: ?> No Image <?php endif; ?>
      </td>
      <td><?= htmlspecialchars($row['address']) ?></td>
      <td><?= htmlspecialchars($row['category']) ?></td>
      <td><?= htmlspecialchars($row['status']) ?></td>
      <td>
        <?php if (!empty($row['admin_photo'])): ?>
          <img src="uploads/<?= htmlspecialchars($row['admin_photo']) ?>" onclick="showImage(this.src)">
        <?php else: ?> Pending <?php endif; ?>
      </td>
      <td><?= htmlspecialchars($row['date_submitted']) ?></td>
      <td>
        <?php if ($row['status'] !== 'Resolved') { ?>
          <form action="resolve_complaint.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="complaint_id" value="<?= $row['id'] ?>">
            <input type="file" name="admin_photo" accept=".jpg" required>
            <button type="submit" class="btn">Mark Resolved</button>
          </form>
        <?php } else { ?>
          ✅ Resolved
        <?php } ?>
      </td>
    </tr>
    <?php } ?>
  </table>
</div>

<!-- Modal -->
<div id="imgModal" class="modal" onclick="hideImage()">
  <span class="modal-close" onclick="hideImage()">&times;</span>
  <img id="modalImage" src="">
</div>

<script>
function showImage(src) {
  const modal = document.getElementById("imgModal");
  const modalImg = document.getElementById("modalImage");
  modalImg.src = src;
  modal.style.display = "flex";
}
function hideImage() {
  document.getElementById("imgModal").style.display = "none";
}
</script>

</body>
</html>
