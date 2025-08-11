<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in as school
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

// Get the HEI ID from session
$hei_id = $_SESSION['hei_id'] ?? 0;

try {
    // Get all curriculum submissions for this HEI
    $stmt = $pdo->prepare("
        SELECT submission_id, semester, DATE_FORMAT(submission_date, '%M %d, %Y %h:%i %p') as submission_date
        FROM curriculum_submissions
        WHERE hei_id = ?
        ORDER BY submission_date DESC
    ");
    $stmt->execute([$hei_id]);
    
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($submissions);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([]);
}
?>