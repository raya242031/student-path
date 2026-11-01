<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// search engine
$search = trim($_GET['search'] ?? '');

// pagination
$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));

// students count for pagination after search
$countSql = "SELECT COUNT(*) FROM students
             WHERE name LIKE :search_name
             OR email LIKE :search_email
             OR phone LIKE :search_phone";

$countStmt = $pdo->prepare($countSql);

$countStmt->execute([
    'search_name' => "%$search%",
    'search_email' => "%$search%",
    'search_phone' => "%$search%"
]);

$totalStudents = (int) $countStmt->fetchColumn();

$totalPages = (int) ceil($totalStudents / $perPage);
$page = min($page, max(1, $totalPages));
$offset = ($page - 1) * $perPage;

// get students data from database with search and pagination
$sql = "SELECT id, name, email, phone, photo
        FROM students
        WHERE name LIKE :search_name
        OR email LIKE :search_email
        OR phone LIKE :search_phone
        ORDER BY id DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(':search_name', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':search_email', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':search_phone', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$students = $stmt->fetchAll();

?>



<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الطلاب | Student Path</title>

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
    <main class="main-content students-section py-5">
        <div class="container">

            <!-- Page Heading -->
            <div class="d-flex flex-column flex-md-row justify-content-between
                        align-items-md-center gap-3 mb-4">

                <div>
                    <h1 class="students-title mb-2">قائمة الطلاب</h1>
                    <p class="students-text mb-0">
                        عرض بيانات الطلاب وإدارة سجلاتهم بسهولة.
                    </p>
                </div>

                <a href="add_student.php" class="btn btn-main">
                    + إضافة طالب جديد
                </a>

            </div>


            <!-- Search -->
            <div class="card students-card students-search-card mb-4">
                <div class="card-body">

                    <form action="view_students.php" method="get">
                        <div class="students-search-row">
                            <div class="students-search-input">
                                <label for="search" class="form-label">
                                    البحث عن طالب
                                </label>
                                <input type="search" class="form-control" id="search" name="search"
                                    value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="ابحث بالاسم أو البريد الإلكتروني أو رقم الهاتف">
                            </div>

                            <button type="submit" class="btn btn-main students-search-button">
                                بحث
                            </button>
                        </div>
                    </form>

                </div>
            </div>


            <!-- Students Table -->
            <div class="card students-card">
                <div class="card-body">

                    <div class="d-flex justify-content-between
                                align-items-center flex-wrap gap-2 mb-3">
                        <h2 class="students-subtitle mb-0">بيانات الطلاب</h2>
                        <span class="students-count">
                            عدد الطلاب: <?= $totalStudents ?>
                        </span>
                    </div>


                    <div class="table-responsive">

                        <?php if (isset($_GET['success']) && $_GET['success'] === '1'): ?>
                            <div class="alert alert-success" role="alert">
                                تمت إضافة الطالب بنجاح.
                            </div>
                        <?php endif; ?>

                        <table class="table students-table align-middle mb-0">

                            <colgroup>
                                <col class="student-number-column">
                                <col class="student-name-column">
                                <col class="student-email-column">
                                <col class="student-phone-column">
                                <col class="student-actions-column">
                            </colgroup>

                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">اسم الطالب</th>
                                    <th scope="col">البريد الإلكتروني</th>
                                    <th scope="col">رقم الهاتف</th>
                                    <th scope="col">الإجراءات</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (count($students) > 0): ?>
                                    <?php foreach ($students as $index => $student): ?>
                                        <tr>
                                            <td><?= $offset + $index + 1 ?></td>

                                            <td>
                                                <?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>

                                            <td dir="ltr">
                                                <?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>

                                            <td dir="ltr">
                                                <?= htmlspecialchars($student['phone'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>

                                            <td>
                                                <div class="student-actions">
                                                    <a href="edit_student.php?id=<?= (int) $student['id'] ?>"
                                                        class="btn btn-sm btn-outline-main">
                                                        تعديل
                                                    </a>

                                                    <form action="delete_student.php" method="POST" class="d-inline"
                                                        onsubmit="return confirm('هل أنت متأكد من حذف هذا الطالب؟ لا يمكن التراجع عن هذا الإجراء.');">

                                                        <input type="hidden" name="student_id"
                                                            value="<?= (int) $student['id'] ?>">

                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            حذف
                                                        </button>
                                                    </form>

                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            لا توجد نتائج مطابقة.
                                        </td>
                                    </tr>
                                <?php endif; ?>

                            </tbody>

                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav class="mt-4" aria-label="التنقل بين صفحات الطلاب">
                        <ul class="pagination justify-content-center mb-0">


                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            ?>

                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?search=<?= urlencode($search) ?>&page=<?= max(1, $page - 1) ?>">
                                    السابق
                                </a>
                            </li>

                            <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $p ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?search=<?= urlencode($search) ?>&page=<?= min($totalPages, $page + 1) ?>">
                                    التالي
                                </a>
                            </li>


                        </ul>
                    </nav>

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