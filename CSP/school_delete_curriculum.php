<?php
session_start();
require_once 'db_connect.php';

// Function to safely return JSON and exit
function return_json($data, $status_code = 200) {
    header('Content-Type: application/json');
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

// Check if user is logged in as school
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    return_json(['success' => false, 'message' => 'Unauthorized access'], 403);
}

// Get the HEI ID from session
$hei_id = $_SESSION['hei_id'] ?? 0;

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return_json(['success' => false, 'message' => 'Invalid request method'], 405);
}

// Get and decode the JSON data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Validate submission ID
if (!isset($data['submission_id']) || !is_numeric($data['submission_id'])) {
    return_json(['success' => false, 'message' => 'Invalid submission ID'], 400);
}

$submission_id = $data['submission_id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Verify the submission belongs to this HEI
    $stmt = $pdo->prepare("SELECT submission_id FROM curriculum_submissions WHERE submission_id = ? AND hei_id = ?");
    $stmt->execute([$submission_id, $hei_id]);
    
    if ($stmt->rowCount() === 0) {
        return_json(['success' => false, 'message' => 'Submission not found or unauthorized'], 404);
    }
    
    // Delete entries first (should be handled by foreign key constraints, but let's be explicit)
    $stmt = $pdo->prepare("DELETE FROM curriculum_entries WHERE submission_id = ?");
    $stmt->execute([$submission_id]);
    
    // Then delete the submission
    $stmt = $pdo->prepare("DELETE FROM curriculum_submissions WHERE submission_id = ?");
    $stmt->execute([$submission_id]);
    
    // Commit transaction
    $pdo->commit();
    
    return_json(['success' => true, 'message' => 'Submission deleted successfully']);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log('Delete curriculum error: ' . $e->getMessage());
    return_json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}
?>