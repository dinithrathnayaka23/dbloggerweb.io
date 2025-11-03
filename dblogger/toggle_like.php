<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id'] ?? 0);
if ($post_id <= 0) exit;

$check_like = $conn->query("SELECT * FROM likes WHERE user_id = $user_id AND post_id = $post_id");

if ($check_like->num_rows > 0) {
    // Unlike
    $conn->query("DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id");
    $liked = false;
} else {
    // Like
    $conn->query("INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)");
    $liked = true;

    // Notify post author
    $post_author_id = $conn->query("SELECT user_id FROM posts WHERE id = $post_id")->fetch_assoc()['user_id'];
    if ($post_author_id != $user_id) {
        $msg = "liked your post";
        $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, post_id, message) VALUES (?, ?, ?, ?)");
        $notif_stmt->bind_param("iiis", $post_author_id, $user_id, $post_id, $msg);
        $notif_stmt->execute();
    }
}

// Return total likes
$likes_count = $conn->query("SELECT COUNT(*) AS c FROM likes WHERE post_id = $post_id")->fetch_assoc()['c'];

echo json_encode(['liked' => $liked, 'likes' => $likes_count]);
