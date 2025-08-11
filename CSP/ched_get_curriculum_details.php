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

// Check if submission_id parameter exists
if (!isset($_GET['submission_id']) || empty($_GET['submission_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Submission ID is required']);
    exit;
}

$submission_id = $_GET['submission_id'];

try {
    // First, get the submission details including HEI information
    $stmt = $pdo->prepare('
        SELECT 
            cs.submission_id,
            cs.hei_id,
            cs.semester,
            cs.submission_date,
            h.hei_name
        FROM curriculum_submissions cs
        JOIN tbl_heis h ON cs.hei_id = h.hei_id
        WHERE cs.submission_id = :submission_id
    ');
    $stmt->bindParam(':submission_id', $submission_id, PDO::PARAM_INT);
    $stmt->execute();
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Submission not found']);
        exit;
    }
    
    // Then, get all curriculum entries for this submission
    $stmt = $pdo->prepare('
        SELECT 
            entry_id,
            subject_area,
            category_no,
            course_title,
            lec_units,
            lab_units,
            total_units,
            pre_requisites
        FROM curriculum_entries
        WHERE submission_id = :submission_id
        ORDER BY entry_id
    ');
    $stmt->bindParam(':submission_id', $submission_id, PDO::PARAM_INT);
    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'submission' => $submission,
        'entries' => $entries,
        'hei_name' => $submission['hei_name']
    ]);
} catch (Exception $e) {
    // Return an error message
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
exit;
?>