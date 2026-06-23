<?php

session_start();

require_once "backend/db.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: admin-complaints.php");
    exit();
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$error = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = :username LIMIT 1");
    $stmt->execute([":username" => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin["password"])) {
        session_regenerate_id(true);
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_username"] = $admin["username"];

        header("Location: admin-complaints.php");
        exit();
    }

    $error = "اسم المستخدم أو كلمة المرور غير صحيحة";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="تسجيل دخول إدارة شكاوى بوابة بلدية قباطية الإلكترونية.">
  <title>تسجيل دخول الإدارة | بوابة بلدية قباطية</title>
  <style>
    :root {
      --navy: #272343;
      --white: #ffffff;
      --soft: #e3f6f5;
      --cyan: #bae8e8;
      --ink: #1f2937;
      --muted: #64748b;
      --line: rgba(39, 35, 67, 0.12);
      --shadow: 0 20px 55px rgba(39, 35, 67, 0.12);
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      direction: rtl;
      font-family: "Tahoma", "Arial", sans-serif;
      color: var(--ink);
      background: linear-gradient(180deg, var(--soft) 0%, #f8fbfb 42%, var(--white) 100%);
      display: grid;
      place-items: center;
      padding: 24px;
    }

    .login-card {
      width: min(440px, 100%);
      padding: 30px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: var(--shadow);
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
      color: var(--navy);
      font-weight: 800;
    }

    .brand img {
      width: 46px;
      height: 46px;
      object-fit: contain;
    }

    .section-label {
      display: inline-block;
      color: #0f766e;
      font-size: 0.9rem;
      font-weight: 800;
      margin-bottom: 8px;
    }

    h1 {
      margin: 0 0 8px;
      color: var(--navy);
      font-size: 1.8rem;
      letter-spacing: 0;
    }

    .login-copy {
      margin: 0 0 24px;
      color: var(--muted);
      line-height: 1.8;
    }

    label {
      display: grid;
      gap: 8px;
      margin-bottom: 16px;
      color: var(--navy);
      font-weight: 700;
    }

    input {
      width: 100%;
      min-height: 48px;
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 0 14px;
      color: var(--ink);
      background: var(--white);
      font: inherit;
      outline: none;
    }

    input:focus {
      border-color: #0f766e;
      box-shadow: 0 0 0 4px rgba(186, 232, 232, 0.55);
    }

    .error-message {
      margin: 0 0 16px;
      padding: 12px 14px;
      border-radius: 8px;
      color: #991b1b;
      background: #fee2e2;
      font-weight: 700;
    }

    .login-button {
      width: 100%;
      min-height: 50px;
      border: 0;
      border-radius: 8px;
      color: var(--white);
      background: var(--navy);
      font: inherit;
      font-weight: 800;
      cursor: pointer;
    }

    .back-link {
      display: flex;
      justify-content: center;
      margin-top: 18px;
      color: #0f766e;
      font-weight: 800;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <main class="login-card">
    <div class="brand">
      <img src="images/q.png" alt="شعار بلدية قباطية">
      <span>بوابة بلدية قباطية الإلكترونية</span>
    </div>

    <span class="section-label">دخول الإدارة</span>
    <h1>تسجيل الدخول</h1>
    <p class="login-copy">يرجى إدخال بيانات المدير للوصول إلى لوحة متابعة الشكاوى.</p>

    <?php if ($error): ?>
      <p class="error-message"><?php echo e($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="admin-login.php">
      <label>
        <span>اسم المستخدم</span>
        <input type="text" name="username" value="<?php echo e($username); ?>" autocomplete="username" required>
      </label>

      <label>
        <span>كلمة المرور</span>
        <input type="password" name="password" autocomplete="current-password" required>
      </label>

      <button class="login-button" type="submit">دخول</button>
    </form>

    <a class="back-link" href="index.php">العودة إلى البوابة</a>
  </main>
</body>
</html>
