<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin-login.php");
    exit();
}

require_once "backend/db.php";

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$stats_stmt = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'قيد المراجعة' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'تم الحل' THEN 1 ELSE 0 END) AS resolved
    FROM complaints
");
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC) ?: ["total" => 0, "pending" => 0, "resolved" => 0];

$complaints_stmt = $pdo->query("
    SELECT id, citizen_name, phone, area, complaint_type, description, image_path, status, created_at
    FROM complaints
    ORDER BY created_at DESC, id DESC
");
$complaints = $complaints_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="لوحة متابعة شكاوى بوابة بلدية قباطية الإلكترونية.">
  <title>إدارة الشكاوى | بوابة بلدية قباطية</title>
  <link rel="stylesheet" href="admin-login.css">
</head>
<body>
  <header class="admin-header">
    <nav class="admin-nav" aria-label="التنقل الإداري">
      <a class="brand" href="index.php" aria-label="بوابة بلدية قباطية">
        <img src="images/q.png" alt="شعار بلدية قباطية">
        <span>بوابة بلدية قباطية الإلكترونية</span>
      </a>
      <div class="nav-actions">
        <a class="back-link" href="index.php">العودة إلى البوابة</a>
        <a class="logout-link" href="admin-logout.php">تسجيل الخروج</a>
      </div>
    </nav>
  </header>

  <main class="admin-main">
    <section class="page-heading" aria-labelledby="adminTitle">
      <div>
        <span class="section-label">إدارة الشكاوى</span>
        <h1 id="adminTitle">متابعة شكاوى المواطنين</h1>
        <p>تعرض هذه الصفحة الشكاوى التي تصل من نموذج البوابة مع بيانات المواطن والصورة المرفقة وحالة المعالجة.</p>
      </div>
    </section>

    <section class="stats-grid" aria-label="إحصائيات الشكاوى">
      <article class="stat-card">
        <span>إجمالي الشكاوى</span>
        <strong><?php echo e($stats["total"] ?? 0); ?></strong>
      </article>
      <article class="stat-card">
        <span>قيد المراجعة</span>
        <strong><?php echo e($stats["pending"] ?? 0); ?></strong>
      </article>
      <article class="stat-card">
        <span>تم الحل</span>
        <strong><?php echo e($stats["resolved"] ?? 0); ?></strong>
      </article>
    </section>

    <section class="table-shell" aria-label="جدول الشكاوى">
      <?php if ($complaints): ?>
        <table>
          <thead>
            <tr>
              <th>رقم الشكوى</th>
              <th>اسم المواطن</th>
              <th>رقم الهاتف</th>
              <th>المنطقة</th>
              <th>نوع الشكوى</th>
              <th>الوصف</th>
              <th>الصورة</th>
              <th>الحالة</th>
              <th>التاريخ</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($complaints as $complaint): ?>
              <tr>
                <td><?php echo e($complaint["id"]); ?></td>
                <td><?php echo e($complaint["citizen_name"]); ?></td>
                <td><?php echo e($complaint["phone"]); ?></td>
                <td><?php echo e($complaint["area"]); ?></td>
                <td><?php echo e($complaint["complaint_type"]); ?></td>
                <td class="description-cell"><?php echo nl2br(e($complaint["description"])); ?></td>
                <td>
                  <?php if (!empty($complaint["image_path"])): ?>
                    <a href="<?php echo e($complaint["image_path"]); ?>" target="_blank" rel="noopener">
                      <img class="complaint-image" src="<?php echo e($complaint["image_path"]); ?>" alt="صورة الشكوى رقم <?php echo e($complaint["id"]); ?>">
                    </a>
                  <?php else: ?>
                    <span class="empty-value">لا يوجد</span>
                  <?php endif; ?>
                </td>
                <td><span class="status-pill"><?php echo e($complaint["status"] ?: "قيد المراجعة"); ?></span></td>
                <td><?php echo e($complaint["created_at"]); ?></td>
                <td class="action-cell">
                  <div class="status-actions" aria-label="إجراءات الشكوى رقم <?php echo e($complaint["id"]); ?>">
                    <form action="backend/update-complaint-status.php" method="POST" class="status-form">
                      <input type="hidden" name="complaint_id" value="<?php echo e($complaint["id"]); ?>">
                      <input type="hidden" name="status" value="قيد التنفيذ">
                      <button type="submit" class="status-btn progress-btn">قيد التنفيذ</button>
                    </form>
                    <form action="backend/update-complaint-status.php" method="POST" class="status-form">
                      <input type="hidden" name="complaint_id" value="<?php echo e($complaint["id"]); ?>">
                      <input type="hidden" name="status" value="تم الحل">
                      <button type="submit" class="status-btn done-btn">إنهاء المهمة</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="empty-state">لا توجد شكاوى مسجلة حتى الآن.</div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
