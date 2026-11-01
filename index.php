<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Path | المسار الطلابي</title>


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
                        <a class="nav-link active" href="index.php">الرئيسية</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="view_students.php">
                            الطلاب
                        </a>
                    </li>


                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <span class="nav-link">
                            مرحبًا، <?= htmlspecialchars(
                                    $_SESSION['user_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-register ms-lg-2" href="logout.php">
                            تسجيل الخروج
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">تسجيل الدخول</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-register ms-lg-2" href="register.php">
                            إنشاء حساب
                        </a>
                    </li>
                    <?php endif; ?>


                </ul>
            </div>

        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">

        <!-- Welcome Section -->
        <section class="hero-section">
            <div class="container">

                <div class="row align-items-center g-4">

                    <div class="col-lg-7 text-center text-lg-start">

                        <h1 class="hero-title fw-bold">
                            رحلتك الطلابية،
                            <span>أصبحت أسهل.</span>
                        </h1>

                        <p class="hero-text mt-3 mb-4">
                            يساعدك Student Path على تنظيم معلومات الطلاب
                            وإدارتها بسهولة من مكان واحد.
                        </p>

                        <a href="view_students.php" class="btn btn-main ms-2 mb-2">
                            عرض الطلاب
                        </a>

                        <a href="login.php" class="btn btn-outline-main mb-2">
                            ابدأ الآن
                        </a>

                    </div>

                    <div class="col-lg-5 text-center">

                        <img src="assets/img/hero.jpeg" alt="Student Path - إدارة معلومات الطلاب"
                            class="hero-image img-fluid">

                    </div>

                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container">

                <div class="text-center mb-5">

                    <h2 class="section-title fw-bold">
                        ماذا يمكنك أن تفعل؟
                    </h2>

                    <p class="section-text">
                        كل ما تحتاج إليه لإدارة سجلات الطلاب بسهولة.
                    </p>

                </div>

                <div class="row g-4">

                    <div class="col-md-4">
                        <div class="card feature-card h-100">

                            <div class="card-body text-center p-4">

                                <h5 class="card-title fw-bold">
                                    سجلات الطلاب
                                </h5>

                                <p class="card-text">
                                    عرض معلومات الطلاب وإدارتها
                                    ضمن قائمة منظمة.
                                </p>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card feature-card h-100">

                            <div class="card-body text-center p-4">

                                <h5 class="card-title fw-bold">
                                    البحث السهل
                                </h5>

                                <p class="card-text">
                                    الوصول إلى سجلات الطلاب بسرعة
                                    باستخدام خيارات البحث المتاحة.
                                </p>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card feature-card h-100">

                            <div class="card-body text-center p-4">

                                <h5 class="card-title fw-bold">
                                    إدارة بسيطة
                                </h5>

                                <p class="card-text">
                                    إضافة سجلات الطلاب وتعديلها
                                    وحذفها من خلال واجهة بسيطة.
                                </p>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

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