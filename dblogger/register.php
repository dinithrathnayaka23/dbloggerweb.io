<?php
require_once "db.php";
session_start(); // ✅ Needed for session handling

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ✅ Input validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match");
    }

    // ✅ Hash password to protect
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // ✅ Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        die("Email already registered");
    }
    $stmt->close();

    // ✅ Insert new user
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, is_active) VALUES (?, ?, ?, 1)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sss", $full_name, $email, $password_hash);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['full_name'] = $full_name;
        header("Location: dashboard.php");
        exit;
    } else {
        die("Error creating account: " . $conn->error);
    }

    $stmt->close();
    $conn->close();
}
?>
