<?php
// إعداد الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=localhost;dbname=wesal;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("فشل الاتصال: " . $e->getMessage());
}

// التحقق من رقم الحالة
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : die("رقم الحالة غير صالح.");

// جلب بيانات الحالة
$stmt = $pdo->prepare("SELECT * FROM help_requests WHERE id = ?");
$stmt->execute([$id]);
$case = $stmt->fetch();
if (!$case) die("لم يتم العثور على الحالة.");

// تحويل بيانات JSON إلى مصفوفات
$extraDetails = json_decode($case['extra_details'], true) ?? [];
$proofFiles = json_decode($case['proof_files'], true) ?? [];
$donors = json_decode($case['donors'], true) ?? [];
$progress = min(100, ($case['collected_amount'] / $case['target_amount']) * 100);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($case['title']) ?> - تفاصيل الحالة</title>
  <link rel="stylesheet" href="css/help-details.css">
</head>
<body>

  <div class="details-container">
    <div class="title">
      <a href="availabeHelp.htm"><i class="fa-solid fa-arrow-right"></i></a>
      تفاصيل الحالة
    </div>

    <div class="details-header">
      <?php if ($case['urgent']): ?>
        <span class="badge urgent">حالة طارئة</span>
      <?php endif; ?>
      <img src="<?= htmlspecialchars($case['image_url']) ?>" alt="صورة الحالة">
    </div>

    <div class="title-row">
      <h2><?= htmlspecialchars($case['title']) ?></h2>
      <div class="meta total">
        <?= number_format($case['collected_amount'], 2) ?> / 
        <?= number_format($case['target_amount'], 2) ?> ريال
      </div>
    </div>

    <div class="meta"><?= htmlspecialchars($case['category']) ?></div>
    <div class="meta date"><?= htmlspecialchars($case['submission_date']) ?></div>

    <div class="progress-bar">
      <?php
        $progress = min(100, ($case['collected_amount'] / $case['target_amount']) * 100);
      ?>
      <div class="progress-bar-fill" style="width: <?= $progress ?>%;"></div>
    </div>

    <p class="description"><?= nl2br(htmlspecialchars($case['description'])) ?></p>

    <p class="text">كل مساهمة، مهما كانت بسيطة، تقرّب الحالة من هدفها وتمنحها فرصة لحياة أفضل.</p>

    <!-- تفاصيل إضافية -->
    <?php if (!empty($extraDetails)): ?>
      <div class="extra-details">
        <h4>تفاصيل إضافية:</h4>
        <ul>
          <?php foreach ($extraDetails as $item): ?>
            <li><?= htmlspecialchars($item) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- ملفات إثبات -->
    <?php if (!empty($proofFiles)): ?>
      <div class="attachment">
        <h3>ملفات الإثبات</h3>
        <p>تم إرفاق المستندات التالية:</p>
        <ul>
          <?php foreach ($proofFiles as $file): ?>
            <li><a href="uploads/<?= htmlspecialchars($file) ?>" target="_blank"><?= htmlspecialchars($file) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- تبرع الآن -->
    <div class="btn-text">
      <p>ساهم الآن، فكل لحظة تأخير قد تُحدث فرقًا كبيرًا.</p>
      <a href="payment.php?id=<?= $case['id'] ?>" class="btn-donate">تبرع الآن</a>
    </div>

    <!-- مشاركة الحالة -->
    <div class="share-section">
      <p class="share-title">شارك الحالة:</p>
      <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>" target="_blank">فيسبوك</a> |
      <a href="https://wa.me/?text=<?= urlencode($_SERVER['REQUEST_URI']) ?>" target="_blank">واتساب</a>
    </div>

    <!-- قائمة المتبرعين -->
    <div class="donors">
      <h4>آخر المساهمين:</h4>
      <ul>
        <?php if (!empty($donors)): ?>
          <?php foreach ($donors as $donor): ?>
            <li><?= htmlspecialchars($donor) ?></li>
          <?php endforeach; ?>
        <?php else: ?>
          <li>لم يتم التبرع بعد.</li>
        <?php endif; ?>
      </ul>
    </div>

  </div>

</body>
</html>
