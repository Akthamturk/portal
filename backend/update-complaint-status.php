<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin-complaints.php");
    exit();
}

require_once "db.php";

$complaint_id = filter_input(INPUT_POST, "complaint_id", FILTER_VALIDATE_INT);
$status = trim($_POST["status"] ?? "");
$allowed_statuses = ["قيد المراجعة", "قيد التنفيذ", "تم الحل"];

if (!$complaint_id || !in_array($status, $allowed_statuses, true)) {
    header("Location: ../admin-complaints.php");
    exit();
}

$stmt = $pdo->prepare("UPDATE complaints SET status = :status WHERE id = :id");
$stmt->execute([
    ":status" => $status,
    ":id" => $complaint_id,
]);

header("Location: ../admin-complaints.php");
exit();
