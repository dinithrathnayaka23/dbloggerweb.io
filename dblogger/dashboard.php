<?php
session_start();
require_once "db.php";

$isLoggedIn = isset($_SESSION['user_id']);
$user_name = 'Guest User';
$user_pic = '';
$user_email = 'Not Logged In';

if ($isLoggedIn) {
  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT full_name,email,profile_image FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $user_name = $user['full_name'] ?? 'User';
  $user_email = $user['email'] ?? 'User';
  $user_pic = isset($user['profile_image']) && file_exists('uploads/' . $user['profile_image'])
    ? 'uploads/' . $user['profile_image']
    : 'https://placehold.co/40x40';
}

//Fetch notifications for logged-in user
$notifications = [];
if ($isLoggedIn) {
  $stmt_notif = $conn->prepare("SELECT n.id, n.message, n.is_read, n.created_at, u.full_name AS sender_name 
                                FROM notifications n
                                JOIN users u ON n.sender_id = u.id
                                WHERE n.user_id = ?
                                ORDER BY n.is_read ASC, n.created_at DESC
                                LIMIT 10");
  $stmt_notif->bind_param("i", $user_id);
  $stmt_notif->execute();
  $notifications = $stmt_notif->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch posts
$posts = [];
$query = "SELECT p.id, p.title, p.content, p.published_at, u.full_name
          FROM posts p
          JOIN users u ON p.user_id = u.id
          WHERE p.is_published = 1
          ORDER BY p.published_at DESC";

$result = $conn->query($query);
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $stmt_img = $conn->prepare("SELECT filename FROM post_images WHERE post_id = ? ORDER BY created_at ASC LIMIT 1");
    $stmt_img->bind_param("i", $row['id']);
    $stmt_img->execute();
    $img_result = $stmt_img->get_result()->fetch_assoc();
    if ($img_result && !empty($img_result['filename'])) {
      $imagePath = 'uploads/' . $img_result['filename'];
      $row['image'] = file_exists($imagePath) ? $imagePath : 'https://placehold.co/600x300/111/eee?text=Post+Image';
    } else {
      $row['image'] = 'https://placehold.co/600x300/111/eee?text=Post+Image';
    }
    $posts[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>D_BLOGGER Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link rel="icon" type="image/x-icon" href="./favicon.jpg">
  <style>
    .glow {
      box-shadow: 0 0 20px rgba(34, 211, 238, 0.3);
    }

    .glow-hover:hover {
      box-shadow: 0 0 30px rgba(34, 211, 238, 0.5);
    }

    * {
      transition: all 0.2s ease-in-out;
    }

    @media (max-width: 768px) {
      main {
        margin-left: 0 !important;
      }
    }

    body {
      overflow-x: hidden;
    }

    #sidebar {
      overflow-y: auto;
    }

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
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center relative">
      <div class="flex items-center space-x-2">
        <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-lg flex items-center justify-center glow">
          <span class="text-gray-900 font-bold text-xl">D</span>
        </div>
        <h1 class="text-2xl font-bold tracking-wide text-cyan-400">D_<span class="text-2xl font-bold text-white">BLOGGER</span></h1>
      </div>

      <div class="flex items-center space-x-4">
        <?php if ($isLoggedIn): ?>
          <!-- Notification Bell -->
          <div class="relative mr-4 cursor-pointer" id="notifBell">
            <button id="notifBell" class="relative text-cyan-400 focus:outline-none">
              <i class="fa-solid fa-bell text-xl text-gray-200 hover:text-cyan-400 transition"></i>
              <?php
              $unreadCount = count(array_filter($notifications, fn($n) => $n['is_read'] == 0));
              if ($unreadCount > 0): ?>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 rounded-full">
                  <?= $unreadCount; ?>
                </span>
              <?php endif; ?>
            </button>

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
        <?php endif; ?>

        <div id="userSection" class="hidden md:flex items-center space-x-3">
          <?php if ($isLoggedIn): ?>
            <img src="<?= htmlspecialchars($user_pic); ?>" class="w-9 h-9 rounded-full border border-cyan-400 object-cover" alt="Profile">
            <div class="flex flex-col leading-tight">
              <span class="text-sm font-medium text-gray-200"><?= htmlspecialchars($user_name); ?></span>
              <span class="text-xs text-gray-400"><?= htmlspecialchars($user_email); ?></span>
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
    </div>
  </header>

  <div class="flex flex-1 pt-16">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed md:relative bg-gray-800 w-64 top-0 bottom-0 min-h-screen flex flex-col p-5 space-y-2 flex-shrink-0 z-20 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto">
      <nav class="space-y-2 text-gray-300">
        <a href="dashboard.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg bg-cyan-600 text-white transition">
          <i class="fa-solid fa-house"></i><span>Home</span>
        </a>
        <a href="write_blog.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 hover:text-white transition">
          <i class="fa-solid fa-pen-nib"></i><span>Write Blogs</span>
        </a>
        <a href="edit_blog.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 hover:text-white transition">
          <i class="fa-solid fa-edit"></i><span>Edit Blogs</span>
        </a>
        <a href="profile.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 hover:text-white transition">
          <i class="fa-solid fa-user"></i><span>Profile</span>
        </a>
        <?php if ($isLoggedIn): ?>
          <a href="logout.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-red-600 hover:text-white transition">
            <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
          </a>
        <?php endif; ?>
      </nav>
    </aside>

    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-10 md:hidden"></div>

    <!-- Main Content -->
    <main class="flex-1 bg-gray-900 p-6 transition-all duration-300">
      <section id="homeSection" class="w-full max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start mb-6 gap-4">
          <h2 class="text-2xl font-bold text-cyan-400">Home</h2>
          <div class="relative w-full md:w-64">
            <input type="text" id="searchInput" placeholder="Search posts..."
              class="bg-gray-800 border border-gray-700 text-gray-200 rounded-lg pl-10 pr-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-cyan-500">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-500"></i>
          </div>
        </div>

        <!-- Post Grid -->
        <div id="blogGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
              <?php
              $imgSrc = htmlspecialchars($post['image']);
              // Guests can now read blogs too
              $postLink = "read_post.php?id=" . $post['id'];
              ?>
              <article class="blog-item bg-gray-800 p-4 rounded-xl shadow-lg hover:shadow-cyan-500/30 transition cursor-pointer w-full"
                data-title="<?= strtolower($post['title']); ?>"
                data-author="<?= strtolower($post['full_name']); ?>"
                onclick="window.location.href='<?= $postLink; ?>'">
                <div class="w-full h-48 mb-3 overflow-hidden rounded-lg">
                  <img src="<?= $imgSrc; ?>" alt="blog_image" class="w-full h-full object-cover">
                </div>
                <h3 class="text-lg font-semibold text-cyan-300 mb-2"><?= htmlspecialchars($post['title']); ?></h3>
                <p class="text-gray-400 text-sm mb-2">by <?= htmlspecialchars($post['full_name']); ?> • <?= date('M d, Y', strtotime($post['published_at'])); ?></p>
                <p class="text-gray-300 text-sm leading-relaxed"><?= substr(strip_tags($post['content']), 0, 100) . '...'; ?></p>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-gray-400">No posts published yet.</p>
          <?php endif; ?>
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

    menuToggle?.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
    });
    overlay?.addEventListener('click', () => {
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    });

    // Toggle notification dropdown
    notifBell?.addEventListener('click', () => {
      notifDropdown.classList.toggle('active');

      // Mark visible notifications as read
      if (notifDropdown.classList.contains('active')) {
        const unreadNotifs = [...notifDropdown.querySelectorAll('.notif-item.unread')];
        const ids = unreadNotifs.map(n => n.dataset.id);
        if (ids.length > 0) {
          fetch('mark_notifications_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'notif_ids[]=' + ids.join('&notif_ids[]=')
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              const badge = notifBell.querySelector('span');
              if (badge) badge.remove();
              unreadNotifs.forEach(n => {
                n.classList.remove('unread');
              });
            }
          });
        }
      }
    });

    // Close dropdown if clicked outside
    document.addEventListener('click', (e) => {
      if (!notifDropdown.contains(e.target) && !notifBell.contains(e.target)) {
        notifDropdown.classList.remove('active');
      }
    });

    // Live search
    const searchInput = document.getElementById('searchInput');
    const blogItems = document.querySelectorAll('.blog-item');
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase();
      blogItems.forEach(item => {
        const title = item.getAttribute('data-title');
        const author = item.getAttribute('data-author');
        item.style.display = (title.includes(query) || author.includes(query)) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
