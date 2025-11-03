<?php
session_start();
require_once "db.php";

// ✅ Determine if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

// Default guest values
$user_name = "Guest";
$user_pic = "https://placehold.co/40x40";
$user_email = "Not logged in";
$msg = "";

// ✅ If logged in, fetch user info
if ($isLoggedIn) {
  $stmt = $conn->prepare("SELECT full_name,email,profile_image FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();

  $user_name = $user['full_name'] ?? ($_SESSION['full_name'] ?? 'User');
  $user_email = $user['email'] ?? ($_SESSION['email'] ?? 'User');
  $user_pic = isset($user['profile_image']) && file_exists('uploads/' . $user['profile_image'])
    ? 'uploads/' . $user['profile_image']
    : ($_SESSION['profile_image'] ?? 'https://placehold.co/40x40');
}

// ✅ Fetch notifications for logged-in user
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

// ✅ Handle blog submission only if logged in
if ($isLoggedIn && $_SERVER["REQUEST_METHOD"] === "POST") {
  $title = $_POST['title'];
  $content = $_POST['content'];
  $status = $_POST['status'];

  $is_published = ($status === "published") ? 1 : 0;
  $published_at = $is_published ? date("Y-m-d H:i:s") : null;
  $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title));

  $stmt = $conn->prepare("INSERT INTO posts (user_id, title, slug, content, published_at, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
  $stmt->bind_param("issssi", $user_id, $title, $slug, $content, $published_at, $is_published);

  if ($stmt->execute()) {
    $post_id = $stmt->insert_id;

    // ✅ Handle image uploads
    if (!empty($_FILES['images']['name'][0])) {
      $uploadDir = "uploads/";
      if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

      foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        $filename = time() . "_" . basename($_FILES['images']['name'][$key]);
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($tmpName, $targetPath)) {
          $alt_text = "";
          $stmt_img = $conn->prepare("INSERT INTO post_images (post_id, user_id, filename, alt_text, created_at) VALUES (?, ?, ?, ?, NOW())");
          $stmt_img->bind_param("iiss", $post_id, $user_id, $filename, $alt_text);
          $stmt_img->execute();
        }
      }
    }

    $msg = $is_published ? "Blog published successfully!" : "Draft saved successfully!";
  } else {
    $msg = "Error: " . $conn->error;
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Write Blog | D_BLOGGER</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="./favicon.jpg">
  <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
  <style>
    #editor {
      height: 300px;
      background-color: #1f2937;
      color: white;
      border-radius: .5rem;
      padding: 1rem;
    }

    .ql-toolbar {
      background-color: #111827;
      border-color: #374151 !important;
      border-radius: .5rem .5rem 0 0;
    }

    .ql-container {
      border-color: #374151 !important;
    }

    .ql-editor {
      color: #e5e7eb;
      min-height: 250px;
    }

    .ql-snow .ql-stroke {
      stroke: #9ca3af;
    }

    .ql-snow .ql-fill {
      fill: #9ca3af;
    }

    .ql-snow .ql-picker {
      color: #9ca3af;
    }

    .glow {
      box-shadow: 0 0 20px rgba(34, 211, 238, 0.3);
    }

    .glow-hover:hover {
      box-shadow: 0 0 30px rgba(34, 211, 238, 0.5);
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
        <h1 class="text-2xl md:text-2xl font-bold tracking-wide text-cyan-400">
          D_<span class="text-2xl text-white font-bold">BLOGGER</span>
        </h1>
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
    <aside id="sidebar" class="fixed md:relative bg-gray-800 w-64 h-screen md:h-auto p-5 space-y-2 flex-shrink-0 z-20 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
      <nav class="space-y-2 text-gray-300">
        <a href="dashboard.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 hover:text-white transition">
          <i class="fa-solid fa-house"></i><span>Home</span>
        </a>
        <a href="write_blog.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg bg-cyan-600 text-white transition">
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

    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-10"></div>

    <main class="flex-1 bg-gray-900 p-6 transition-all duration-300">
      <section id="writeSection" class="max-w-4xl mx-auto">

        <?php if ($msg): ?>
          <div class="bg-gray-800 border border-cyan-500 text-cyan-300 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($msg); ?>
          </div>
        <?php endif; ?>

        <h2 class="text-2xl font-bold text-cyan-400 mb-6">Write a New Blog</h2>

        <?php if (!$isLoggedIn): ?>
          <div class="bg-gray-800 border border-cyan-500 text-center text-gray-300 px-6 py-8 rounded-lg">
            <p class="mb-4 text-lg">You must be logged in to write blogs.</p>
            <button onclick="window.location.href='login.php'" class="bg-cyan-500 hover:bg-cyan-600 text-gray-900 font-semibold px-5 py-2 rounded-lg transition">Login</button>
            <button onclick="window.location.href='signup.php'" class="ml-3 border border-cyan-400 text-cyan-400 hover:bg-cyan-400 hover:text-gray-900 px-5 py-2 rounded-lg transition">Sign Up</button>
          </div>
        <?php else: ?>

        <form method="POST" enctype="multipart/form-data">
          <div class="mb-5">
            <label class="block text-sm font-medium text-gray-300 mb-2">Blog Title</label>
            <input type="text" name="title" placeholder="Enter your blog title..."
              class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
          </div>

          <div class="mb-5">
            <label class="block text-sm font-medium text-gray-300 mb-2">Blog Content</label>
            <div id="editor"></div>
            <input type="hidden" name="content" id="hiddenContent">
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Add Images</label>
            <div class="border-2 border-dashed border-gray-600 p-6 rounded-lg text-center hover:border-cyan-500 transition relative">
              <input type="file" id="imageUpload" name="images[]" multiple accept="image/*" class="hidden">
              <label for="imageUpload" class="cursor-pointer text-cyan-400 font-semibold">
                <i class="fa-solid fa-upload mr-2"></i> Upload Images
              </label>
              <div id="imagePreview" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"></div>
              <p class="text-gray-500 text-sm mt-2">You can select multiple images</p>
            </div>
          </div>

          <div class="flex flex-wrap justify-end gap-4">
            <button type="submit" name="status" value="draft" class="bg-gray-700 hover:bg-gray-600 px-5 py-2 rounded-lg text-gray-200 font-medium transition">
              Save as Draft
            </button>
            <button type="submit" name="status" value="published" class="bg-cyan-500 hover:bg-cyan-600 px-5 py-2 rounded-lg text-gray-900 font-semibold transition">
              Publish Blog
            </button>
          </div>
        </form>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const imageUpload = document.getElementById('imageUpload');
    const imagePreview = document.getElementById('imagePreview');
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

    // Preview images
    imageUpload?.addEventListener('change', function() {
      imagePreview.innerHTML = '';
      Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = "w-full h-32 object-cover rounded-lg border border-gray-700 hover:border-cyan-500 transition";
          imagePreview.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    });

    // Quill editor
    const quill = new Quill('#editor', {
      theme: 'snow',
      modules: {
        toolbar: [
          [{ header: [1, 2, 3, false] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          ['link', 'image', 'code-block'],
          ['clean']
        ]
      }
    });

    // Save Quill content to hidden input
    document.querySelector("form")?.addEventListener("submit", () => {
      document.getElementById("hiddenContent").value = quill.root.innerHTML;
    });

    // Notification Dropdown Toggle
    notifBell?.addEventListener('click', () => {
      notifDropdown.classList.toggle('active');
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
              unreadNotifs.forEach(n => n.classList.remove('unread'));
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
  </script>
</body>

</html>

