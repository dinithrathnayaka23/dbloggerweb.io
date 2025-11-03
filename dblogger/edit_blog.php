<?php
session_start();
require_once "db.php";

// ✅ Determine login state first
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
$user_name = "Guest User";
$user_email = "Not Logged In";
$profile_image = "https://placehold.co/40x40?text=User";
$user_pic = $profile_image;

// ✅ If logged in, get user info
if ($isLoggedIn) {
  $stmt_user = $conn->prepare("SELECT full_name, email, profile_image FROM users WHERE id = ?");
  $stmt_user->bind_param("i", $user_id);
  $stmt_user->execute();
  $user = $stmt_user->get_result()->fetch_assoc();

  $user_name = $user['full_name'] ?? 'User';
  $user_email = $user['email'] ?? '';

  if (!empty($user['profile_image'])) {
    $profile_image = 'uploads/' . basename($user['profile_image']);
    if (!file_exists($profile_image)) {
      $profile_image = 'https://placehold.co/40x40?text=User';
    }
  }

  $user_pic = $profile_image;
}

// ✅ Fetch user's posts only if logged in
$posts = [];
if ($isLoggedIn) {
  $stmt_posts = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
  $stmt_posts->bind_param("i", $user_id);
  $stmt_posts->execute();
  $res_posts = $stmt_posts->get_result();

  while ($row = $res_posts->fetch_assoc()) {
    $stmt_img = $conn->prepare("SELECT filename FROM post_images WHERE post_id = ? ORDER BY created_at ASC");
    $stmt_img->bind_param("i", $row['id']);
    $stmt_img->execute();
    $img_res = $stmt_img->get_result();
    $images = [];
    while ($img = $img_res->fetch_assoc()) {
      $images[] = $img['filename'];
    }
    $row['images'] = $images;
    $posts[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Blogs | D_BLOGGER</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="./favicon.jpg">
  <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

  <style>
    .ql-toolbar {
      background-color: #111827;
      border-color: #374151 !important;
      border-radius: 0.5rem 0.5rem 0 0;
    }

    .ql-container {
      border-color: #374151 !important;
      background-color: #1f2937;
      color: #e5e7eb;
      border-radius: 0 0 0.5rem 0.5rem;
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

    /* Notifications */
    .notification-dropdown {
      display: none;
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
        <h1 class="text-2xl md:text-2xl font-bold tracking-wide text-cyan-400">D_<span class="text-2xl text-white font-bold">BLOGGER</span></h1>
      </div>

      <div id="userSection" class="hidden md:flex items-center space-x-3">
        <?php if ($isLoggedIn): ?>
          <!-- Notification Bell -->
          <div class="relative mr-4 cursor-pointer" id="notifBell">
            <button id="notifBell" class="relative text-cyan-400 focus:outline-none">
              <i class="fa-solid fa-bell text-xl text-gray-200 hover:text-cyan-400 transition"></i>
              <?php
              $stmt_notif = $conn->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
              $stmt_notif->bind_param("i", $user_id);
              $stmt_notif->execute();
              $notif_result = $stmt_notif->get_result()->fetch_assoc();
              $unreadCount = $notif_result['unread_count'] ?? 0;
              if ($unreadCount > 0): ?>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 rounded-full">
                  <?= $unreadCount; ?>
                </span>
              <?php endif; ?>
            </button>

            <!-- Dropdown -->
            <div id="notifDropdown" class="notification-dropdown absolute right-0 mt-2 w-80 bg-gray-800 border border-gray-700 rounded-lg overflow-hidden z-50">
              <?php
              $stmt_list = $conn->prepare("SELECT n.id, n.message, n.is_read, n.created_at, u.full_name AS sender_name 
                                           FROM notifications n
                                           JOIN users u ON n.sender_id = u.id
                                           WHERE n.user_id = ?
                                           ORDER BY n.is_read ASC, n.created_at DESC
                                           LIMIT 10");
              $stmt_list->bind_param("i", $user_id);
              $stmt_list->execute();
              $notifications = $stmt_list->get_result()->fetch_all(MYSQLI_ASSOC);
              ?>
              <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notif): ?>
                  <div class="px-4 py-2 border-b border-gray-700 hover:bg-gray-700 cursor-pointer notif-item <?= $notif['is_read'] ? '' : 'unread' ?>" data-id="<?= $notif['id'] ?>">
                    <p class="text-sm text-gray-200"><strong><?= htmlspecialchars($notif['sender_name']); ?></strong> <?= htmlspecialchars($notif['message']); ?></p>
                    <span class="text-xs text-gray-400"><?= date('M d, Y H:i', strtotime($notif['created_at'])); ?></span>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="px-4 py-3 text-gray-400 text-center">No new notifications</div>
              <?php endif; ?>
            </div>
          </div>

          <!-- User info -->
          <img src="<?php echo htmlspecialchars($user_pic); ?>"
            class="w-9 h-9 rounded-full border border-cyan-400 object-cover"
            alt="Profile">

          <div class="flex flex-col leading-tight">
            <span class="text-sm font-medium text-gray-200"><?php echo htmlspecialchars($user_name); ?></span>
            <span class="text-xs text-gray-400"><?php echo htmlspecialchars($user_email); ?></span>
          </div>

        <?php else: ?>
          <button class="bg-cyan-500 hover:bg-cyan-600 text-gray-900 font-semibold px-4 py-2 rounded-lg transition"
            onclick="window.location.href='login.php'">
            Login
          </button>
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
        <a href="dashboard.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 transition"><i class="fa-solid fa-house"></i><span>Home</span></a>
        <a href="write_blog.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 transition"><i class="fa-solid fa-pen-nib"></i><span>Write Blogs</span></a>
        <a href="edit_blog.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg bg-cyan-600 text-white transition"><i class="fa-solid fa-edit"></i><span>Edit Blogs</span></a>
        <a href="profile.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-cyan-600 transition"><i class="fa-solid fa-user"></i><span>Profile</span></a>
        <?php if ($isLoggedIn): ?>
          <a href="logout.php" class="flex items-center space-x-3 py-2 px-4 rounded-lg hover:bg-red-600 transition">
            <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
          </a>
        <?php endif; ?>

      </nav>
    </aside>

    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-10"></div>

    <main class="flex-1 bg-gray-900 p-6 transition-all duration-300">
      <section class="max-w-5xl mx-auto">
        <h2 class="text-2xl font-bold text-cyan-400 mb-6">Manage Your Blogs</h2>

        <?php if ($isLoggedIn): ?>

          <!-- Blog List -->
          <div id="blogList" class="space-y-4">
            <?php foreach ($posts as $post): ?>
              <?php
              $safeContent = htmlspecialchars(json_encode($post['content']), ENT_QUOTES, 'UTF-8');
              $safeImages = htmlspecialchars(json_encode($post['images']), ENT_QUOTES, 'UTF-8');
              ?>
              <div class="bg-gray-800 border border-gray-700 rounded-lg p-5 flex flex-col md:flex-row justify-between items-start md:items-center"
                data-id="<?php echo $post['id']; ?>"
                data-title="<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>"
                data-content='<?php echo $safeContent; ?>'
                data-images='<?php echo $safeImages; ?>'>
                <div>
                  <h3 class="text-lg font-semibold text-cyan-400"><?php echo htmlspecialchars($post['title']); ?></h3>
                  <p class="text-gray-400 text-sm mt-1">Published on: <?php echo $post['published_at'] ? date('M d, Y', strtotime($post['published_at'])) : 'Draft'; ?></p>
                </div>
                <div class="mt-3 md:mt-0 space-x-2">
                  <button class="editBtn bg-cyan-500 hover:bg-cyan-600 text-gray-900 font-medium px-3 py-1.5 rounded-md"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
                  <button class="deleteBtn bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-md"><i class="fa-solid fa-trash"></i></button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Hidden Edit Section -->
          <div id="editSection" class="hidden mt-8">
            <h3 class="text-xl font-semibold text-cyan-400 mb-4">Edit Blog</h3>

            <div class="mb-5">
              <label class="block text-sm font-medium text-gray-300 mb-2">Blog Title</label>
              <input type="text" id="editTitle" class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-cyan-500" />
            </div>

            <div class="mb-5">
              <label class="block text-sm font-medium text-gray-300 mb-2">Blog Content</label>
              <div id="editEditor"></div>
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-300 mb-2">Update Images</label>
              <div class="border-2 border-dashed border-gray-600 p-6 rounded-lg text-center hover:border-cyan-500 transition">
                <input type="file" id="editImageUpload" multiple accept="image/*" class="hidden" />
                <label for="editImageUpload" class="cursor-pointer text-cyan-400 font-semibold"><i class="fa-solid fa-upload mr-2"></i> Upload New Images</label>
                <p class="text-gray-500 text-sm mt-2">Current images will appear below</p>
              </div>
              <div id="editImagePreview" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"></div>
            </div>

            <div class="flex justify-end space-x-4">
              <button id="cancelEdit" class="bg-gray-700 hover:bg-gray-600 px-5 py-2 rounded-lg text-gray-200">Cancel</button>
              <button id="updateBlog" class="bg-cyan-500 hover:bg-cyan-600 px-5 py-2 rounded-lg text-gray-900 font-semibold">Update Blog</button>
            </div>
          </div>

        <?php else: ?>

          <!-- Suggestion for not logged-in users -->
          <div class="bg-gray-800 border border-cyan-500 p-6 rounded-xl text-center mt-6">
            <p class="text-gray-300 text-lg mb-4">You must be logged in to edit blogs.</p>
            <a href="login.php" class="bg-cyan-500 hover:bg-cyan-600 text-gray-900 font-semibold px-5 py-2 rounded-lg transition">Login</a>
            <a href="signup.php" class="ml-3 border border-cyan-400 text-cyan-400 hover:bg-cyan-400 hover:text-gray-900 px-5 py-2 rounded-lg transition">Sign Up</a>
          </div>

        <?php endif; ?>

      </section>
    </main>
  </div>

  <script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const editSection = document.getElementById('editSection');
    const blogList = document.getElementById('blogList');
    const cancelEdit = document.getElementById('cancelEdit');
    const editImageUpload = document.getElementById('editImageUpload');
    const editImagePreview = document.getElementById('editImagePreview');

    menuToggle.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
    });
    overlay.addEventListener('click', () => {
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    });

    const editQuill = new Quill('#editEditor', {
      theme: 'snow',
      placeholder: 'Edit your blog content...',
      modules: {
        toolbar: [
          [{
            header: [1, 2, 3, false]
          }],
          ['bold', 'italic', 'underline', 'strike'],
          [{
            list: 'ordered'
          }, {
            list: 'bullet'
          }],
          ['link', 'image', 'code-block'],
          ['clean']
        ]
      }
    });

    // Edit Blog
    document.querySelectorAll('.editBtn').forEach(btn => {
      btn.addEventListener('click', () => {
        const postDiv = btn.closest('div[data-id]');
        const postId = postDiv.dataset.id;
        const title = postDiv.dataset.title;
        const content = JSON.parse(postDiv.dataset.content);
        const images = JSON.parse(postDiv.dataset.images);

        blogList.classList.add('hidden');
        editSection.classList.remove('hidden');
        document.getElementById('editTitle').value = title;
        editQuill.root.innerHTML = content;

        editImagePreview.innerHTML = '';
        images.forEach(img => {
          const imgEl = document.createElement('img');
          imgEl.src = 'uploads/' + img;
          imgEl.className = "w-full h-32 object-cover rounded-lg border border-gray-700 hover:border-cyan-500 transition";
          editImagePreview.appendChild(imgEl);
        });

        editSection.dataset.postId = postId;
      });
    });

    // Cancel Edit
    cancelEdit.addEventListener('click', () => {
      editSection.classList.add('hidden');
      blogList.classList.remove('hidden');
    });

    // Delete button
    document.querySelectorAll('.deleteBtn').forEach(btn => {
      btn.addEventListener('click', () => {
        if (confirm('Are you sure you want to delete this blog?')) {
          const postDiv = btn.closest('div[data-id]');
          const postId = postDiv.dataset.id;
          fetch('delete_post.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `post_id=${postId}`
          }).then(res => res.text()).then(resp => {
            alert(resp);
            postDiv.remove();
          });
        }
      });
    });

    // Image preview for updated images
    editImageUpload.addEventListener('change', function() {
      editImagePreview.innerHTML = '';
      Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = "w-full h-32 object-cover rounded-lg border border-gray-700 hover:border-cyan-500 transition";
          editImagePreview.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    });

    // Update Blog
    document.getElementById('updateBlog').addEventListener('click', () => {
      const postId = editSection.dataset.postId;
      const title = document.getElementById('editTitle').value;
      const content = editQuill.root.innerHTML;
      const files = editImageUpload.files;
      const formData = new FormData();
      formData.append('post_id', postId);
      formData.append('title', title);
      formData.append('content', content);
      Array.from(files).forEach(file => formData.append('images[]', file));

      fetch('update_post.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.text())
        .then(resp => {
          alert(resp);
          location.reload();
        });
    });

    // Notification Dropdown
    const notifBell = document.getElementById('notifBell');
    const notifDropdown = document.getElementById('notifDropdown');

    notifBell?.addEventListener('click', () => {
      notifDropdown.classList.toggle('active');
      if (notifDropdown.classList.contains('active')) {
        const unreadNotifs = [...notifDropdown.querySelectorAll('.notif-item.unread')];
        const ids = unreadNotifs.map(n => n.dataset.id);
        if (ids.length > 0) {
          fetch('mark_notifications_read.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
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

    document.addEventListener('click', (e) => {
      if (!notifDropdown.contains(e.target) && !notifBell.contains(e.target)) {
        notifDropdown.classList.remove('active');
      }
    });
  </script>
</body>

</html>