<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid request.');
}

$user_id = $_POST['user_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("DELETE FROM users WHERE reseller_id = ?");
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare("DELETE FROM resellers WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    $pdo->commit();

    header('Location: reseller_management.php');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
