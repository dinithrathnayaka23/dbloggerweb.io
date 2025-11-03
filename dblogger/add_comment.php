<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Not logged in');
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($post_id <= 0 || $comment === '') {
    http_response_code(400);
    exit('Invalid input');
}

// 1️⃣ Insert comment
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $comment);
$stmt->execute();
$comment_id = $conn->insert_id;

// 2️⃣ Fetch comment info to return
$get = $conn->query("
    SELECT c.comment, c.created_at, u.full_name, u.profile_image, u.id AS user_id
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = $comment_id
")->fetch_assoc();

$commenter_image = (!empty($get['profile_image']) && file_exists('uploads/' . $get['profile_image']))
    ? 'uploads/' . htmlspecialchars($get['profile_image'])
    : 'https://placehold.co/40x40';

// 3️⃣ Insert notification for post author
$post_author_id = $conn->query("SELECT user_id FROM posts WHERE id = $post_id")->fetch_assoc()['user_id'];

if ($post_author_id != $user_id) { // Don't notify yourself
    $msg = "commented on your post";
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, post_id, message) VALUES (?, ?, ?, ?)");
    $notif_stmt->bind_param("iiis", $post_author_id, $user_id, $post_id, $msg);
    $notif_stmt->execute();
}

// 4️⃣ Return HTML for immediate display
?>
<div class="comment bg-gray-700 p-4 rounded-xl flex items-start space-x-3 hover:bg-gray-600 transition">
    <img src="<?= $commenter_image ?>" class="w-10 h-10 rounded-full border border-cyan-400 object-cover" alt="User">
    <div>
        <p class="text-cyan-300 font-semibold"><?= htmlspecialchars($get['full_name']) ?></p>
        <p class="text-gray-200 mt-1"><?= nl2br(htmlspecialchars($get['comment'])) ?></p>
        <small class="text-gray-400 text-xs"><?= date("M j, g:i a", strtotime($get['created_at'])) ?></small>
    </div>
</div>
