<?php
// Start the session
session_start();

// Include database connection
require_once 'db_connect.php';

// Check if the user is logged in and is a CHED user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'ched') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if HEI ID is provided
if (!isset($_GET['hei_id']) || empty($_GET['hei_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No HEI ID provided']);
    exit;
}

$heiId = $_GET['hei_id'];

try {
    // Get HEI information
    $heiStmt = $pdo->prepare('SELECT hei_name FROM tbl_heis WHERE hei_id = ?');
    $heiStmt->execute([$heiId]);
    $heiInfo = $heiStmt->fetch();
    
    if (!$heiInfo) {
        throw new Exception('HEI not found');
    }
    
    // Get all curriculum submissions for this HEI
    $stmt = $pdo->prepare('
        SELECT s.submission_id, s.semester, s.submission_date, h.hei_name
        FROM curriculum_submissions s
        JOIN tbl_heis h ON s.hei_id = h.hei_id
        WHERE s.hei_id = ?
        ORDER BY s.submission_date DESC
    ');
    $stmt->execute([$heiId]);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the submissions as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'submissions' => $submissions
    ]);
} catch (Exception $e) {
    // Return an error message
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
exit;
?>