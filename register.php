<?php

require_once __DIR__ . '/includes/db.php';

$error = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $error = 'يرجى تعبئة جميع الحقول.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'يرجى إدخال بريد إلكتروني صحيح.';
    } elseif (strlen($password) < 8) {
        $error = 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.';
    } elseif ($password !== $confirmPassword) {
        $error = 'كلمتا المرور غير متطابقتين.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password)
                 VALUES (:name, :email, :password)'
            );

            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            header('Location: login.php?registered=1');
            exit;

        } catch (PDOException $e) {
            $mysqlErrorCode = isset($e->errorInfo[1])
                ? (int) $e->errorInfo[1]
                : 0;

            if ($mysqlErrorCode === 1062) {
                $error = 'هذا البريد الإلكتروني مسجل مسبقًا.';
            } else {
                error_log($e->getMessage());
                $error = 'حدث خطأ أثناء إنشاء الحساب. حاول مرة أخرى.';
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

    <title>إنشاء حساب | Student Path</title>


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
                        <a class="nav-link" href="view_students.php">
                            الطلاب
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="login.php">تسجيل الدخول</a>
                    </li>

                    <li class="nav-item active">
                        <a class="nav-link active" href="register.php">
                            إنشاء حساب
                        </a>
                    </li>

                </ul>
            </div>

        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content register-section">

        <div class="container py-5">

            <div class="row justify-content-center">

                <div class="col-12 col-sm-10 col-md-7 col-lg-5">

                    <div class="card register-card">

                        <div class="card-body p-4 p-md-5">

                            <!-- Logo and Title -->
                            <div class="text-center mb-4">

                                <img src="assets/img/logo.png" alt="Student Path" class="register-logo mb-3">

                                <h1 class="register-title fw-bold">
                                    إنشاء حساب جديد
                                </h1>

                                <p class="text-muted mb-0">
                                    أنشئ حسابك للبدء باستخدام Student Path
                                </p>

                            </div>

                            <?php if ($error !== ''): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php endif; ?>

                            <!-- Register Form -->
                            <form action="#" method="post">

                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        الاسم الكامل
                                    </label>

                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="أدخل اسمك الكامل" autocomplete="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        البريد الإلكتروني
                                    </label>

                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="أدخل بريدك الإلكتروني" autocomplete="email" dir="rtl" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        كلمة المرور
                                    </label>

                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="أنشئ كلمة مرور" autocomplete="new-password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        تأكيد كلمة المرور
                                    </label>

                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" placeholder="أعد إدخال كلمة المرور"
                                        autocomplete="new-password" required>
                                </div>

                                <button type="submit" class="btn btn-main w-100 mt-2">
                                    إنشاء الحساب
                                </button>

                            </form>

                            <div class="text-center mt-4">

                                <p class="mb-0">
                                    لديك حساب بالفعل؟
                                    <a href="login.php" class="register-link">
                                        تسجيل الدخول
                                    </a>
                                </p>

                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container text-center">
            <p class="mb-0">
                جميع الحقوق محفوظة &copy; 2026 - Student Path
            </p>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <script src="assets/js/main.js"></script>

</body>

</html>