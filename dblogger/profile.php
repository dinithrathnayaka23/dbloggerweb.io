<?php
session_start();
require_once "db.php";

// Check login
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

// Default guest values
$user_name = "Guest User";
$user_email = "Not logged in";
$user_pic = "https://placehold.co/80x80";
$unread_notifications = 0;
$notifications = [];

// ✅ If logged in, fetch user info
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT full_name, email, profile_image FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    $user_name = $user['full_name'] ?? "User";
    $user_email = $user['email'] ?? "No email found";
    $user_pic = (!empty($user['profile_image']) && file_exists('uploads/' . $user['profile_image']))
        ? 'uploads/' . $user['profile_image']
        : "https://placehold.co/80x80";

    // ✅ Fetch unread notifications count & latest 5 notifications
    $notif_stmt = $conn->prepare("SELECT id, message, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $notif_stmt->bind_param("i", $user_id);
    $notif_stmt->execute();
    $notif_res = $notif_stmt->get_result();

    while ($notif = $notif_res->fetch_assoc()) {
        $notifications[] = $notif;
        if ($notif['is_read'] == 0) $unread_notifications++;
    }
}

// ✅ Handle profile image upload
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];

    if ($file['error'] === 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid('profile_') . '.' . $ext;
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $targetPath = $targetDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $update = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $update->bind_param("si", $newName, $user_id);
            $update->execute();

            $_SESSION['profile_image'] = $newName;
            header("Location: profile.php?success=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile | D_BLOGGER</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="./favicon.jpg">
<style>
    .profile-img { width:120px; height:120px; object-fit:cover; border-radius:9999px; border:3px solid #06b6d4; }
    .glow { box-shadow: 0 0 20px rgba(34,211,238,0.3); }
    .glow-hover:hover { box-shadow: 0 0 30px rgba(34,211,238,0.5); }
    /* Notification Dropdown */
    .notification-dropdown {
      display: none;
      position: absolute;
      top: 48px;
      right: 0;
      width: 300px;
      background-color: #1f2937;
      border: 1px solid #374151;
      border-radius: 10px;
      overflow: hidden;
      z-index: 50;
    }

    .notification-dropdown.active {
      display: block;
    }

    .notif-item.unread p {
      font-weight: bold;
    }
</style>
</head>
<body class="bg-gray-900 text-gray-100 flex flex-col min-h-screen">

<!-- Header -->
<header class="bg-gray-800 shadow-md w-full fixed top-0 left-0 z-30">
  <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-lg flex items-center justify-center glow">
        <span class="text-gray-900 font-bold text-xl">D</span>
      </div>
      <h1 class="text-2xl font-bold tracking-wide text-cyan-400">D_<span class="text-white">BLOGGER</span></h1>
    </div>

    <div class="hidden md:flex items-center space-x-3 relative">
      <?php if ($isLoggedIn): ?>
        <!-- Notification bell -->
        <div class="relative mr-4 cursor-pointer" id="notifBell">
          <i class="fa-solid fa-bell text-xl text-gray-200 hover:text-cyan-400 transition"></i>
          <?php if($unread_notifications > 0): ?>
            <div class="notif-badge"><?php echo $unread_notifications; ?></div>
          <?php endif; ?>
          <!-- Dropdown -->
          <div id="notifDropdown" class="notification-dropdown">
              <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notif): ?>
                  <div class="px-4 py-2 border-b border-gray-700 hover:bg-gray-700 cursor-pointer notif-item <?= $notif['is_read'] ? '' : 'unread' ?>"
                       data-id="<?= $notif['id'] ?>">
                    <p class="text-sm text-gray-200">
                      <strong><?= htmlspecialchars($notif['sender_name']); ?></strong>
                      <?= htmlspecialchars($notif['message']); ?>
                    </p>
                    <span class="text-xs text-gray-400"><?= date('M d, Y H:i', strtotime($notif['created_at'])); ?></span>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="px-4 py-3 text-gray-400 text-center">No new notifications</div>
              <?php endif; ?>
            </div>
        </div>

        <img id="topProfileImg" src="<?php echo htmlspecialchars($user_pic); ?>" class="w-9 h-9 rounded-full border border-cyan-400 object-cover" alt="Profile">
        <div class="flex flex-col leading-tight">
          <span class="text-sm font-medium text-gray-200"><?php echo htmlspecialchars($user_name); ?></span>
          <span class="text-xs text-gray-400"><?php echo htmlspecialchars($user_email); ?></span>
        </div>
      <?php else: ?>
        <button class="bg-cyan-500 hover:bg-cyan-600 text-gray-900 font-semibold px-4 py-2 rounded-lg transition" onclick="window.location.href='login.php'">Login</button>
        <button class="border border-cyan-400 text-cyan-400 hover:bg-cyan-400 hover:text-gray-900 px-4 py-2 rounded-lg transition" onclick="window.location.href='signup.php'">Sign Up</button>
      <?php endif; ?>
    </div>

    <button id="menuToggle" class="md:hidden text-cyan-400 focus:outline-none">
      <i class="fa-solid fa-bars text-2xl"></i>
    </button>
  </div>
</header>

<!-- Main -->
<div class="flex flex-1 pt-16">

  <!-- Sidebar -->
  <aside id="sidebar" class="fixed md:relative bg-gray-800 w-64 min-h-screen md:h-auto p-5 space-y-2 flex-shrink-0 z-20 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <nav class="space-y-2 text-gray-300">
      <a href="dashboard.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 transition"><i class="fa-solid fa-house"></i><span>Home</span></a>
      <a href="write_blog.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 transition"><i class="fa-solid fa-pen-nib"></i><span>Write Blogs</span></a>
      <a href="edit_blog.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 transition"><i class="fa-solid fa-edit"></i><span>Edit Blogs</span></a>
      <a href="profile.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg bg-cyan-600 text-white transition"><i class="fa-solid fa-user"></i><span>Profile</span></a>
      <?php if ($isLoggedIn): ?>
          <a href="logout.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-red-600 transition">
            <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
          </a>
      <?php endif; ?>
    </nav>
  </aside>

  <!-- Overlay -->
  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-10"></div>

  <!-- Main Content -->
  <main class="flex-1 bg-gray-900 p-6 transition-all duration-300">
    <section class="max-w-3xl mx-auto">
      <h2 class="text-2xl font-bold text-cyan-400 mb-6">Your Profile</h2>

      <!-- Profile Picture Form -->
      <form action="" method="POST" enctype="multipart/form-data" class="flex flex-col items-center mb-8">
        <img id="profileImg" src="<?php echo htmlspecialchars($user_pic); ?>" class="profile-img mb-4" alt="Profile Picture">
        <label for="profileUpload" class="cursor-pointer text-cyan-400 font-semibold flex items-center space-x-2">
          <i class="fa-solid fa-upload"></i><span>Change Profile Picture</span>
        </label>
        <input type="file" id="profileUpload" name="profile_image" accept="image/*" class="hidden">
        <button type="submit" class="mt-5 bg-cyan-500 hover:bg-cyan-600 px-6 py-2 rounded-lg text-gray-900 font-semibold">Save Changes</button>
      </form>

      <!-- User Info -->
      <div class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Full Name</label>
          <input type="text" value="<?php echo htmlspecialchars($user_name); ?>" class="w-full bg-gray-800 border border-gray-700 text-gray-400 rounded-lg px-4 py-3 cursor-not-allowed" readonly />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
          <input type="email" value="<?php echo htmlspecialchars($user_email); ?>" class="w-full bg-gray-800 border border-gray-700 text-gray-400 rounded-lg px-4 py-3 cursor-not-allowed" readonly />
        </div>
      </div>

      <?php if (isset($_GET['success'])): ?>
        <p class="text-green-400 mt-6">✅ Profile picture updated successfully!</p>
      <?php endif; ?>
    </section>
  </main>
</div>

<script>
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const profileUpload = document.getElementById('profileUpload');
const profileImg = document.getElementById('profileImg');
const topProfileImg = document.getElementById('topProfileImg');
const notifBell = document.getElementById('notifBell');
const notifDropdown = document.getElementById('notifDropdown');

// Sidebar toggle
menuToggle.addEventListener('click', () => {
  sidebar.classList.toggle('-translate-x-full');
  overlay.classList.toggle('hidden');
});
overlay.addEventListener('click', () => {
  sidebar.classList.add('-translate-x-full');
  overlay.classList.add('hidden');
});

// Profile image preview
profileUpload.addEventListener('change', function() {
  const file = this.files[0];
  if(file){
    const reader = new FileReader();
    reader.onload = function(e){
      profileImg.src = e.target.result;
      if(topProfileImg) topProfileImg.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
});

// Notification dropdown toggle
if(notifBell){
  notifBell.addEventListener('click', () => {
    notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
  });
  window.addEventListener('click', function(e){
    if(!notifBell.contains(e.target)){
      notifDropdown.style.display = 'none';
    }
  });
}
</script>
</body>
</html>
