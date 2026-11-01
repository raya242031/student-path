<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function sendJsonResponse($success, $message, $extra = [])
{
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        array_merge([
            'success' => $success,
            'message' => $message
        ], $extra),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');


    if ($name === '' || $email === '' || $phone === '') {
        $error = 'يرجى تعبئة جميع الحقول المطلوبة.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'يرجى إدخال بريد إلكتروني صحيح.';
    } elseif (!preg_match('/^[0-9]{1,15}$/', $phone)) {
        $error = 'رقم الهاتف يجب أن يحتوي على أرقام فقط، وبحد أقصى 15 رقمًا.';
    }

    if ($error !== '') {
        if ($isAjax) {
            sendJsonResponse(false, $error);
        }
    } else {

        $photoName = null;
        $uploadedPath = null;

        try {
            // Image Verification
            if (
                isset($_FILES['photo']) &&
                $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('تعذر رفع الصورة.');
                }

                if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                    throw new RuntimeException(
                        'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.'
                    );
                }

                $imageInfo = getimagesize($_FILES['photo']['tmp_name']);

                if ($imageInfo === false) {
                    throw new RuntimeException('الملف المحدد ليس صورة صحيحة.');
                }

                $allowedTypes = [
                    IMAGETYPE_JPEG => 'jpg',
                    IMAGETYPE_PNG => 'png',
                    IMAGETYPE_WEBP => 'webp'
                ];

                $imageType = $imageInfo[2];

                if (!isset($allowedTypes[$imageType])) {
                    throw new RuntimeException(
                        'يسمح بصور JPG وPNG وWEBP فقط.'
                    );
                }

                $photoName = bin2hex(random_bytes(16))
                    . '.' . $allowedTypes[$imageType];

                $uploadedPath = __DIR__ . '/uploads/' . $photoName;

                if (
                    !move_uploaded_file(
                        $_FILES['photo']['tmp_name'],
                        $uploadedPath
                    )
                ) {
                    throw new RuntimeException('تعذر حفظ الصورة.');
                }
            }

            // save data
            $sql = "INSERT INTO students (name, email, phone, photo)
                    VALUES (:name, :email, :phone, :photo)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'photo' => $photoName
            ]);

            if ($isAjax) {
                sendJsonResponse(
                    true,
                    'تمت إضافة الطالب بنجاح.',
                    ['redirect' => 'view_students.php?success=1']
                );
            }

            header('Location: view_students.php?success=1');
            exit;

        } catch (PDOException $e) {
            if ($uploadedPath !== null && is_file($uploadedPath)) {
                unlink($uploadedPath);
            }

            $mysqlErrorCode = isset($e->errorInfo[1])
                ? (int) $e->errorInfo[1]
                : 0;

            if ($mysqlErrorCode === 1062) {
                $error = 'هذا البريد الإلكتروني مسجل مسبقًا.';
            } else {
                error_log($e->getMessage());
                $error = 'حدث خطأ أثناء حفظ الطالب. حاول مرة أخرى.';
            }

            if ($isAjax) {
                sendJsonResponse(false, $error);
            }

        } catch (RuntimeException $e) {
            if ($uploadedPath !== null && is_file($uploadedPath)) {
                unlink($uploadedPath);
            }

            $error = $e->getMessage();

            if ($isAjax) {
                sendJsonResponse(false, $error);
            }
        }

    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة طالب جديد | Student Path</title>

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
    <main class="main-content add-student-section py-5">
        <div class="container">

            <div class="add-student-heading mb-4">
                <h1 class="add-student-title mb-2">إضافة طالب جديد</h1>
                <p class="add-student-text mb-0">
                    أدخل بيانات الطالب التالية لإضافته إلى قائمة الطلاب.
                </p>
            </div>



            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>


            <div class="card add-student-card mx-auto">
                <div class="card-body p-4 p-md-5">

                    <form action="add_student.php" method="post" enctype="multipart/form-data">

                        <div id="formMessage" class="mb-3" role="alert"></div>

                        <div class="row g-4">

                            <!-- Student Name -->
                            <div class="col-12 col-md-6">
                                <label for="studentName" class="form-label">
                                    اسم الطالب
                                </label>

                                <input type="text" class="form-control" id="studentName" name="name"
                                    placeholder="أدخل اسم الطالب الكامل" required>
                            </div>

                            <!-- Email -->
                            <div class="col-12 col-md-6">
                                <label for="studentEmail" class="form-label">
                                    البريد الإلكتروني
                                </label>

                                <input type="email" class="form-control" id="studentEmail" name="email"
                                    placeholder="أدخل البريد الإلكتروني" dir="ltr" required>
                            </div>

                            <!-- Phone -->
                            <div class="col-12 col-md-6">
                                <label for="studentPhone" class="form-label">
                                    رقم الهاتف
                                </label>

                                <input type="tel" id="studentPhone" name="phone" class="form-control"
                                    inputmode="numeric" pattern="[0-9]{1,15}" maxlength="15" required>
                            </div>

                            <!-- Photo -->
                            <div class="col-12 col-md-6">
                                <label for="studentPhoto" class="form-label">
                                    صورة الطالب
                                    <span class="optional-text">(اختياري)</span>
                                </label>

                                <input type="file" class="form-control" id="studentPhoto" name="photo" accept="image/*">

                                <div class="form-text">
                                    يمكنك اختيار صورة بصيغة صورة شائعة.
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="col-12">
                                <div class="add-student-actions">

                                    <button type="submit" class="btn btn-main">
                                        إضافة الطالب
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