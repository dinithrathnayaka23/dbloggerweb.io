<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in!";
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'] ?? '';

    if (!$post_id) {
        echo "Invalid post ID!";
        exit;
    }

    // Delete associated images from server
    $stmt_imgs = $conn->prepare("SELECT filename FROM post_images WHERE post_id=?");
    $stmt_imgs->bind_param("i", $post_id);
    $stmt_imgs->execute();
    $res_imgs = $stmt_imgs->get_result();
    while ($img = $res_imgs->fetch_assoc()) {
        $filePath = 'uploads/' . $img['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Delete images from post_images table
    $stmt_del_imgs = $conn->prepare("DELETE FROM post_images WHERE post_id=?");
    $stmt_del_imgs->bind_param("i", $post_id);
    $stmt_del_imgs->execute();

    // Delete post
    $stmt_del_post = $conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
    $stmt_del_post->bind_param("ii", $post_id, $user_id);
    if ($stmt_del_post->execute()) {
        echo "Blog deleted successfully!";
    } else {
        echo "Failed to delete blog!";
    }
} else {
    echo "Invalid request!";
}
?>
