<?php

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}

$citizen_name = trim($_POST["citizen_name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$area = trim($_POST["area"] ?? "غير محدد");
$complaint_type = trim($_POST["complaint_type"] ?? "");
$description = trim($_POST["description"] ?? "");

if ($citizen_name === "" || $phone === "" || $complaint_type === "" || $description === "") {
    http_response_code(400);
    die("يرجى تعبئة جميع الحقول المطلوبة");
}

$image_path = null;

if (isset($_FILES["complaint_image"]) && $_FILES["complaint_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES["complaint_image"]["error"] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        die("تعذر رفع الصورة");
    }

    $original_name = basename($_FILES["complaint_image"]["name"]);
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $allowed_types = [
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "png" => "image/png",
        "webp" => "image/webp",
    ];

    if (!array_key_exists($extension, $allowed_types)) {
        http_response_code(400);
        die("نوع الصورة غير مسموح");
    }

    $image_info = getimagesize($_FILES["complaint_image"]["tmp_name"]);

    if ($image_info === false || $image_info["mime"] !== $allowed_types[$extension]) {
        http_response_code(400);
        die("نوع الصورة غير مسموح");
    }

    $upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "complaints";

    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
        http_response_code(500);
        die("تعذر إنشاء مجلد رفع الصور");
    }

    $new_name = bin2hex(random_bytes(16)) . "." . $extension;
    $target_file = $upload_dir . DIRECTORY_SEPARATOR . $new_name;

    if (!move_uploaded_file($_FILES["complaint_image"]["tmp_name"], $target_file)) {
        http_response_code(500);
        die("تعذر حفظ الصورة");
    }

    $image_path = "uploads/complaints/" . $new_name;
}

$sql = "INSERT INTO complaints
        (citizen_name, phone, area, complaint_type, description, image_path)
        VALUES
        (:citizen_name, :phone, :area, :complaint_type, :description, :image_path)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":citizen_name" => $citizen_name,
    ":phone" => $phone,
    ":area" => $area,
    ":complaint_type" => $complaint_type,
    ":description" => $description,
    ":image_path" => $image_path,
]);

header("Location: ../index.php?complaint=success");
exit();
