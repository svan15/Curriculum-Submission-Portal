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

try {
    // Prepare the query to get all HEIs
    $stmt = $pdo->prepare('
        SELECT h.hei_id, h.user_id, h.hei_name
        FROM tbl_heis h
        JOIN User u ON h.user_id = u.id
        WHERE u.user_type = "school"
        ORDER BY h.hei_name
    ');
    $stmt->execute();
    $heis = $stmt->fetchAll();
    
    // Return the HEIs as JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'heis' => $heis]);
} catch (Exception $e) {
    // Return an error message
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
exit;
?>