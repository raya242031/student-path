<?php

session_start();

require_once __DIR__ . '/includes/db.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'يرجى إدخال البريد الإلكتروني وكلمة المرور.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'يرجى إدخال بريد إلكتروني صحيح.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, name, email, password
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>تسجيل الدخول | Student Path</title>


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
                        <a class="nav-link active" href="login.php">تسجيل الدخول</a>
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
    <main class="main-content login-section">

        <div class="container py-5">

            <div class="row justify-content-center">

                <div class="col-12 col-sm-10 col-md-7 col-lg-5">

                    <div class="card login-card">

                        <div class="card-body p-4 p-md-5">

                            <!-- Logo and Title -->
                            <div class="text-center mb-4">

                                <img src="assets/img/logo.png" alt="Student Path" class="login-logo mb-3">

                                <h1 class="login-title fw-bold">
                                    تسجيل الدخول
                                </h1>

                                <p class="text-muted mb-0">
                                    مرحبًا بعودتك إلى Student Path
                                </p>

                            </div>

                            <?php if ($error !== ''): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_GET['logout'])): ?>
                                <div class="alert alert-success" role="alert">
                                    تم تسجيل الخروج بنجاح.
                                </div>
                            <?php endif; ?>

                            <!-- Login Form -->
                            <form action="login.php" method="post">

                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        البريد الإلكتروني
                                    </label>

                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="ادخل البريد الالكتروني" autocomplete="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        كلمة المرور
                                    </label>

                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="ادخل كلمة السر" autocomplete="current-password" dir="ltr" required>
                                </div>

                                <button type="submit" class="btn btn-main w-100 mt-2">
                                    تسجيل الدخول
                                </button>

                            </form>

                            <div class="text-center mt-4">

                                <p class="mb-0">
                                    ليس لديك حساب؟
                                    <a href="register.php" class="login-link">
                                        إنشاء حساب جديد
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