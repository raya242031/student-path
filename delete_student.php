<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: view_students.php');
    exit;
}

$studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

if (!$studentId || $studentId < 1) {
    header('Location: view_students.php?error=invalid_id');
    exit;
}

try {
    // get student data to delete photo if exists
    $stmt = $pdo->prepare(
        'SELECT photo FROM students WHERE id = :id'
    );
    $stmt->execute(['id' => $studentId]);
    $student = $stmt->fetch();

    if (!$student) {
        header('Location: view_students.php?error=not_found');
        exit;
    }

    // delete student from database
    $stmt = $pdo->prepare(
        'DELETE FROM students WHERE id = :id'
    );
    $stmt->execute(['id' => $studentId]);

    // delte student photo if exists
    if (!empty($student['photo'])) {
        $photoPath = __DIR__ . '/uploads/' . basename($student['photo']);

        if (is_file($photoPath)) {
            unlink($photoPath);
        }
    }

    header('Location: view_students.php?deleted=1');
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    header('Location: view_students.php?error=delete_failed');
    exit;
}