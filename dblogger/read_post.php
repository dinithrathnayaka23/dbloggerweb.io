<?php
session_start();
require_once "db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid post ID.");
}

$post_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'] ?? null;

// ✅ Fetch logged-in user info (for navbar)
$logged_name = "Guest";
$logged_pic = "https://placehold.co/40x40";
$logged_email = "Not logged in";
$unread_notifications = 0;
$notifications = [];

if ($user_id) {
  $stmt_user = $conn->prepare("SELECT full_name,email,profile_image FROM users WHERE id = ?");
  $stmt_user->bind_param("i", $user_id);
  $stmt_user->execute();
  $user_row = $stmt_user->get_result()->fetch_assoc();
  if ($user_row) {
    $logged_name = $user_row['full_name'];
    $logged_email = $user_row['email'];
    $logged_pic = (!empty($user_row['profile_image']) && file_exists('uploads/' . $user_row['profile_image']))
      ? 'uploads/' . $user_row['profile_image']
      : 'https://placehold.co/40x40';
  }

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

// ✅ Fetch post with author info
$stmt = $conn->prepare("SELECT p.*, u.full_name AS author_name, u.profile_image AS author_image 
                        FROM posts p
                        JOIN users u ON p.user_id = u.id
                        WHERE p.id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
  die("Post not found.");
}

// ✅ Author info
$author_name = $post['author_name'];
$author_pic = (!empty($post['author_image']) && file_exists('uploads/' . $post['author_image']))
  ? 'uploads/' . $post['author_image']
  : 'https://placehold.co/40x40';

// ✅ Fetch post images
$stmt_img = $conn->prepare("SELECT filename, alt_text FROM post_images WHERE post_id = ?");
$stmt_img->bind_param("i", $post_id);
$stmt_img->execute();
$images = $stmt_img->get_result()->fetch_all(MYSQLI_ASSOC);

// ✅ Likes (users not logged in can only see count)
$likes_count = $conn->query("SELECT COUNT(*) AS c FROM likes WHERE post_id = $post_id")->fetch_assoc()['c'];
$user_liked = false;
if ($user_id) {
  $check_like = $conn->query("SELECT * FROM likes WHERE user_id = $user_id AND post_id = $post_id");
  $user_liked = $check_like->num_rows > 0;
}

// ✅ Comments
$comments = $conn->query("SELECT c.*, u.full_name, u.profile_image 
                          FROM comments c
                          JOIN users u ON c.user_id = u.id 
                          WHERE post_id = $post_id
                          ORDER BY c.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($post['title']) ?> | D_BLOGGER</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
<link rel="icon" type="image/x-icon" href="./favicon.jpg">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
.glow { box-shadow: 0 0 20px rgba(34, 211, 238, 0.3); }
.glow-hover:hover { box-shadow: 0 0 30px rgba(34, 211, 238, 0.5); }
.notif-badge { position:absolute; top:-5px; right:-5px; background:red; color:white; font-size:0.7rem; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
.notif-dropdown { position:absolute; right:0; margin-top:0.5rem; background:#1f2937; border:1px solid #374151; border-radius:0.5rem; width:300px; display:none; z-index:50; }
.notif-item { padding:0.5rem 1rem; border-bottom:1px solid #374151; cursor:pointer; }
.notif-item:last-child { border-bottom:none; }
.notif-item:hover { background:#374151; }
.notif-time { font-size:0.7rem; color:#9ca3af; }
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
    <h1 class="text-2xl font-bold tracking-wide text-cyan-400">
      D_<span class="text-2xl text-white font-bold">BLOGGER</span>
    </h1>
  </div>
  <div class="flex items-center space-x-3 relative">
    <?php if ($user_id): ?>
      <!-- Notification Bell -->
      <div class="relative mr-4 cursor-pointer" id="notifBell">
        <i class="fa-solid fa-bell text-xl text-gray-200 hover:text-cyan-400 transition"></i>
        <?php if($unread_notifications > 0): ?>
          <div class="notif-badge"><?= $unread_notifications ?></div>
        <?php endif; ?>
        <div id="notifDropdown" class="notif-dropdown">
          <?php if(count($notifications) > 0): ?>
            <?php foreach($notifications as $notif): ?>
              <div class="notif-item" onclick="window.location.href='notifications.php'">
                <div><?= htmlspecialchars($notif['message']) ?></div>
                <div class="notif-time"><?= date('M d, Y H:i', strtotime($notif['created_at'])) ?></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="notif-item">No notifications</div>
          <?php endif; ?>
        </div>
      </div>

      <img src="<?= htmlspecialchars($logged_pic) ?>" class="w-9 h-9 rounded-full border border-cyan-400 object-cover" alt="Profile">
      <div class="hidden md:flex flex-col leading-tight">
        <span class="text-sm font-medium text-gray-200"><?= htmlspecialchars($logged_name) ?></span>
        <span class="text-xs text-gray-400"><?= htmlspecialchars($logged_email ?? '') ?></span>
      </div>
    <?php else: ?>
      <button class="bg-cyan-500 hover:bg-cyan-600 text-gray-900 font-semibold px-4 py-2 rounded-lg transition" onclick="window.location.href='login.php'">Login</button>
      <button class="border border-cyan-400 text-cyan-400 hover:bg-cyan-400 hover:text-gray-900 px-4 py-2 rounded-lg transition"
            onclick="window.location.href='signup.php'">
            Sign Up
      </button>
    <?php endif; ?>
  </div>

  <button id="menuToggle" class="md:hidden text-cyan-400 focus:outline-none">
    <i class="fa-solid fa-bars text-2xl"></i>
  </button>
</div>
</header>

<div class="flex flex-1 pt-16">
<!-- Sidebar -->
<aside id="sidebar" class="fixed md:relative bg-gray-800 w-64 h-screen md:h-auto p-5 space-y-2 flex-shrink-0 z-20 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
  <nav class="space-y-2 text-gray-300">
    <a href="dashboard.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 hover:text-white transition"><i class="fa-solid fa-house"></i><span>Home</span></a>
    <a href="write_blog.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 hover:text-white transition"><i class="fa-solid fa-pen-nib"></i><span>Write Blogs</span></a>
    <a href="edit_blog.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 hover:text-white transition"><i class="fa-solid fa-edit"></i><span>Edit Blogs</span></a>
    <a href="profile.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 hover:text-white transition"><i class="fa-solid fa-user"></i><span>Profile</span></a>
    <?php if ($user_id): ?>
          <a href="logout.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-red-600 transition">
            <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
          </a>
    <?php endif; ?>
  </nav>
</aside>

<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-10"></div>

<main class="flex-1 bg-gray-900 p-6 transition-all duration-300">
<section class="max-w-4xl mx-auto space-y-8">

<!-- Post Card -->
<div class="bg-gray-800 p-6 rounded-2xl shadow-lg glow-hover">
  <!-- Author Info -->
  <div class="flex items-center mb-5">
    <img src="<?= htmlspecialchars($author_pic) ?>" class="w-12 h-12 rounded-full border border-cyan-400 mr-4" alt="Author">
    <div>
      <p class="text-cyan-300 font-semibold text-lg"><?= htmlspecialchars($author_name) ?></p>
      <p class="text-gray-400 text-sm"><?= date("M d, Y H:i", strtotime($post['published_at'])) ?></p>
    </div>
  </div>

  <!-- Post Title -->
  <h1 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-5"><?= htmlspecialchars($post['title']) ?></h1>

  <!-- Post Images -->
  <?php if (!empty($images)): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-5">
      <?php foreach ($images as $img): ?>
        <img src="uploads/<?= htmlspecialchars($img['filename']) ?>" alt="<?= htmlspecialchars($img['alt_text']) ?>" class="w-full h-72 md:h-96 object-cover rounded-xl">
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Post Content -->
  <div class="text-gray-300 text-lg leading-relaxed prose prose-invert max-w-full">
    <?= $post['content'] ?>
  </div>

  <!-- Likes -->
  <div class="mt-6 flex items-center space-x-2">
    <?php if ($user_id): ?>
      <button id="likeBtn" class="text-gray-300 hover:text-cyan-400 transition text-2xl focus:outline-none">
        <i class="fa-regular fa-heart <?= $user_liked ? 'fa-solid text-cyan-400' : '' ?>"></i>
      </button>
    <?php else: ?>
      <i class="fa-regular fa-heart text-gray-400 text-2xl"></i>
    <?php endif; ?>
    <span id="likeCount" class="text-gray-300 font-medium text-lg"><?= $likes_count ?></span>
  </div>
</div>

<!-- Add Comment Form -->
<div class="mt-6">
<?php if ($user_id): ?>
  <form id="commentForm" class="flex flex-col space-y-3">
    <textarea id="commentText" name="comment" rows="3"
      class="bg-gray-800 text-gray-200 rounded-xl p-3 border border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 resize-none"
      placeholder="Write a comment..." required></textarea>
    <button type="submit"
      class="self-end bg-cyan-500 hover:bg-cyan-600 text-gray-900 font-semibold px-5 py-2 rounded-lg transition">
      Post Comment
    </button>
  </form>
<?php else: ?>
  <div class="text-center">
    <p class="text-gray-400 mb-3">You must be logged in to comment.</p>
    <button onclick="window.location.href='login.php'"
      class="bg-cyan-500 hover:bg-cyan-600 text-gray-900 font-semibold px-5 py-2 rounded-lg transition">
      Login
    </button>
  </div>
<?php endif; ?>
</div>

<!-- Comments Section -->
<div id="commentsContainer" class="space-y-4 max-h-96 overflow-y-auto mt-6">
<?php while ($c = $comments->fetch_assoc()): ?>
<?php
$commenter_image = !empty($c['profile_image']) && file_exists('uploads/' . $c['profile_image'])
  ? 'uploads/' . htmlspecialchars($c['profile_image'])
  : 'https://placehold.co/40x40';
?>
<div class="bg-gray-800 p-4 rounded-xl flex space-x-3 items-start">
  <img src="<?= $commenter_image ?>" class="w-10 h-10 rounded-full border border-cyan-400 object-cover mt-1" alt="User">
  <div class="flex-1">
    <p class="text-cyan-300 font-semibold"><?= htmlspecialchars($c['full_name']) ?></p>
    <p class="text-gray-300"><?= htmlspecialchars($c['comment']) ?></p>
    <span class="text-gray-500 text-xs"><?= date("M d, Y H:i", strtotime($c['created_at'])) ?></span>
  </div>
</div>
<?php endwhile; ?>
</div>

</section>
</main>
</div>

<script>
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const notifBell = document.getElementById('notifBell');
const notifDropdown = document.getElementById('notifDropdown');

menuToggle.addEventListener('click', () => {
  sidebar.classList.toggle('-translate-x-full');
  overlay.classList.toggle('hidden');
});
overlay.addEventListener('click', () => {
  sidebar.classList.add('-translate-x-full');
  overlay.classList.add('hidden');
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

// Likes
$('#likeBtn').click(function() {
  $.post('toggle_like.php', { post_id: <?= $post_id ?> }, function(data) {
    const res = JSON.parse(data);
    $('#likeCount').text(res.likes);
    const heart = $('#likeBtn i');
    if (res.liked) { heart.removeClass('fa-regular').addClass('fa-solid text-cyan-400'); }
    else { heart.removeClass('fa-solid text-cyan-400').addClass('fa-regular'); }
  });
});

// Comments
$('#commentForm').submit(function(e) {
  e.preventDefault();
  var commentText = $('#commentText').val().trim();
  if (commentText === '') return;
  $.post('add_comment.php', { post_id: <?= $post_id ?>, comment: commentText }, function(data) {
    $('#commentsContainer').prepend(data);
    $('#commentText').val('');
  });
});
</script>
</body>
</html>
