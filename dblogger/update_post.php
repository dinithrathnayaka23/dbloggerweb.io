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
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    if (!$post_id || !$title || !$content) {
        echo "Missing required fields!";
        exit;
    }

    // Update posts table
    $stmt = $conn->prepare("UPDATE posts SET title=?, content=?, updated_at=NOW() WHERE id=? AND user_id=?");
    $stmt->bind_param("ssii", $title, $content, $post_id, $user_id);
    if ($stmt->execute()) {
        // Handle uploaded images
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = 'uploads/';
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $filename = time() . '_' . basename($_FILES['images']['name'][$key]);
                if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                    $stmt_img = $conn->prepare("INSERT INTO post_images (post_id, user_id, filename, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt_img->bind_param("iis", $post_id, $user_id, $filename);
                    $stmt_img->execute();
                }
            }
        }
        echo "Blog updated successfully!";
    } else {
        echo "Failed to update blog!";
    }
} else {
    echo "Invalid request!";
}
?>
