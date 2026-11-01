<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';

$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
}

if (!$studentId || $studentId < 1) {
    exit('رقم الطالب غير صحيح.');
}

$stmt = $pdo->prepare(
    'SELECT id, name, email, phone, photo
     FROM students
     WHERE id = :id'
);
$stmt->execute(['id' => $studentId]);
$student = $stmt->fetch();

if (!$student) {
    exit('الطالب غير موجود.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $email === '' || $phone === '') {
        $error = 'يرجى تعبئة جميع الحقول المطلوبة.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'يرجى إدخال بريد إلكتروني صحيح.';
    } else {

        try {
            $photoName = $student['photo'];
            $uploadedPath = null;

            // if a user uploaded a new photo
            if (
                isset($_FILES['photo']) &&
                $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('تعذر رفع الصورة.');
                }

                if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                    throw new RuntimeException('حجم الصورة يجب ألا يتجاوز 2 ميجابايت.');
                }

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($_FILES['photo']['tmp_name']);

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                if (!isset($allowedTypes[$mimeType])) {
                    throw new RuntimeException('يسمح فقط بصور JPG وPNG وWEBP.');
                }

                $uploadDirectory = __DIR__ . '/uploads/';

                if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
                    throw new RuntimeException('مجلد الصور غير جاهز لحفظ الملفات.');
                }

                $photoName = bin2hex(random_bytes(16))
                    . '.' . $allowedTypes[$mimeType];

                $uploadedPath = $uploadDirectory . $photoName;

                if (
                    !move_uploaded_file(
                        $_FILES['photo']['tmp_name'],
                        $uploadedPath
                    )
                ) {
                    throw new RuntimeException('تعذر حفظ الصورة.');
                }
            }

            // update student data in the database
            $stmt = $pdo->prepare(
                'UPDATE students
         SET name = :name,
             email = :email,
             phone = :phone,
             photo = :photo
         WHERE id = :id'
            );

            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'photo' => $photoName,
                'id' => $studentId
            ]);

            // delete old photo if a new one was uploaded
            if (
                $uploadedPath !== null &&
                !empty($student['photo'])
            ) {
                $oldPhotoPath = __DIR__ . '/uploads/' . basename($student['photo']);

                if (is_file($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            header('Location: view_students.php?updated=1');
            exit;

        } catch (PDOException $e) {
            if ($uploadedPath !== null && is_file($uploadedPath)) {
                unlink($uploadedPath);
            }

            $mysqlErrorCode = isset($e->errorInfo[1])
                ? (int) $e->errorInfo[1]
                : 0;

            if ($mysqlErrorCode === 1062) {
                $error = 'هذا البريد الإلكتروني مسجل لطالب آخر.';
            } else {
                error_log($e->getMessage());
                $error = 'حدث خطأ أثناء تحديث بيانات الطالب.';
            }

        } catch (RuntimeException $e) {
            if (isset($uploadedPath) && $uploadedPath !== null && is_file($uploadedPath)) {
                unlink($uploadedPath);
            }

            $error = $e->getMessage();
        }

    }

    // let the value in input if there is an error
    $student['name'] = $name;
    $student['email'] = $email;
    $student['phone'] = $phone;
}
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل بيانات الطالب | Student Path</title>

    <link rel="icon" href="assets/img/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-navy">
        <div class="container">

            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/logo.png" alt="Student Path" width="45" height="45" class="logo me-2">
                <span>Student Path</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="فتح القائمة">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link" href="index.php">الرئيسية</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active" href="view_students.php">
                            الطلاب
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="login.php">تسجيل الدخول</a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-register ms-lg-2" href="register.php">
                            إنشاء حساب
                        </a>
                    </li>

                </ul>
            </div>

        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content edit-student-section py-5">
        <div class="container">

            <div class="edit-student-heading mb-4">
                <h1 class="edit-student-title mb-2">تعديل بيانات الطالب</h1>
                <p class="edit-student-text mb-0">
                    يمكنك تحديث بيانات الطالب ثم حفظ التغييرات.
                </p>
            </div>

            <div class="card edit-student-card mx-auto">
                <div class="card-body p-4 p-md-5">

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form action="#" method="post" enctype="multipart/form-data">

                        <input type="hidden" name="student_id" value="<?= (int) $student['id'] ?>">

                        <div class="row g-4">

                            <!-- Student Name -->
                            <div class="col-12 col-md-6">
                                <label for="studentName" class="form-label">
                                    اسم الطالب
                                </label>

                                <input type="text" class="form-control" id="studentName" name="name"
                                    value="<?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="أدخل اسم الطالب الكامل" required>
                            </div>

                            <!-- Email -->
                            <div class="col-12 col-md-6">
                                <label for="studentEmail" class="form-label">
                                    البريد الإلكتروني
                                </label>

                                <input type="email" class="form-control" id="studentEmail" name="email"
                                    value="<?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="أدخل البريد الإلكتروني" dir="ltr" required>
                            </div>

                            <!-- Phone -->
                            <div class="col-12 col-md-6">
                                <label for="studentPhone" class="form-label">
                                    رقم الهاتف
                                </label>

                                <input type="tel" class="form-control" id="studentPhone" name="phone"
                                    value="<?= htmlspecialchars($student['phone'], ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="أدخل رقم الهاتف" dir="ltr" required>
                            </div>

                            <!-- Photo -->
                            <div class="col-12 col-md-6">
                                <label for="studentPhoto" class="form-label">
                                    تغيير صورة الطالب
                                    <span class="optional-text">(اختياري)</span>
                                </label>

                                <input type="file" class="form-control" id="studentPhoto" name="photo" accept="image/*">

                                <?php if (!empty($student['photo'])): ?>
                                    <div class="mt-3">
                                        <p class="form-text">الصورة الحالية:</p>
                                        <img src="uploads/<?= htmlspecialchars(basename($student['photo']), ENT_QUOTES, 'UTF-8') ?>"
                                            alt="صورة الطالب" width="100" height="100"
                                            style="object-fit: cover; border-radius: 8px;">
                                    </div>
                                <?php endif; ?>

                                <div class="form-text">
                                    اترك الحقل فارغًا للاحتفاظ بالصورة الحالية.
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="col-12">
                                <div class="edit-student-actions">

                                    <button type="submit" class="btn btn-main">
                                        حفظ التعديلات
                                    </button>

                                    <a href="view_students.php" class="btn btn-outline-main">
                                        إلغاء
                                    </a>

                                </div>
                            </div>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer text-center">
        <div class="container">
            <p class="mb-0">
                جميع الحقوق محفوظة © 2026 - Student Path
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

</body>

</html>