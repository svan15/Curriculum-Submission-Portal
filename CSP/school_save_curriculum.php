<?php
// Disable error display (errors will still be logged but not sent to output)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to catch any accidental output
ob_start();

session_start();

// Function to safely return JSON and exit
function return_json($data, $status_code = 200) {
    // Clear any previous output that might corrupt our JSON
    if (ob_get_length()) ob_clean();
    
    // Set proper content type and status code
    header('Content-Type: application/json');
    http_response_code($status_code);
    
    // Output the JSON and exit
    echo json_encode($data);
    exit;
}

// Include database connection after defining return_json function
// This way if there's an error in connection, we can handle it properly
try {
    require_once 'db_connect.php';
} catch (Exception $e) {
    error_log('Database connection error: ' . $e->getMessage());
    return_json([
        'success' => false,
        'message' => 'Database connection error. Please try again later.'
    ], 500);
}

// Check if user is logged in as school
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    return_json(['success' => false, 'message' => 'Unauthorized access'], 403);
}

// Get the HEI ID from session
$hei_id = $_SESSION['hei_id'] ?? 0;

// Check if HEI ID is valid
if ($hei_id <= 0) {
    return_json(['success' => false, 'message' => 'Invalid HEI ID: ' . $hei_id], 400);
}

// Get and decode the JSON data
$jsonData = file_get_contents('php://input');

// Validate that we received JSON data
if (empty($jsonData)) {
    return_json(['success' => false, 'message' => 'No data received'], 400);
}

// Try to decode the JSON
$data = json_decode($jsonData, true);

// Check if JSON decoding failed
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    return_json([
        'success' => false, 
        'message' => 'Invalid JSON format: ' . json_last_error_msg()
    ], 400);
}

// Log the received data for debugging
error_log('Received data: ' . $jsonData);

if (!$data || !isset($data['semesters']) || empty($data['semesters'])) {
    return_json(['success' => false, 'message' => 'Invalid data format: missing semesters array'], 400);
}

// Verify that the required tables exist
try {
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'curriculum_submissions'");
    if ($tableCheck->rowCount() === 0) {
        return_json([
            'success' => false,
            'message' => 'Database table "curriculum_submissions" does not exist'
        ], 500);
    }
    
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'curriculum_entries'");
    if ($tableCheck->rowCount() === 0) {
        return_json([
            'success' => false,
            'message' => 'Database table "curriculum_entries" does not exist'
        ], 500);
    }
} catch (PDOException $e) {
    error_log('Table check error: ' . $e->getMessage());
    return_json([
        'success' => false,
        'message' => 'Database structure error. Please contact support.'
    ], 500);
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Create a single submission for all semesters
    // Use the first semester as the name for the whole submission
    $submissionName = isset($data['curriculum_title']) && !empty($data['curriculum_title']) 
    ? $data['curriculum_title'] 
    : $data['semesters'][0]['semester'] . " to " . end($data['semesters'])['semester'];
    
    // Create a new curriculum submission
    $stmt = $pdo->prepare("
        INSERT INTO curriculum_submissions (hei_id, semester)
        VALUES (?, ?)
    ");
    $stmt->execute([$hei_id, $submissionName]);
    
    // Get the submission ID for this group of semesters
    $submission_id = $pdo->lastInsertId();
    
    // Process each semester and add them to the same submission
    foreach ($data['semesters'] as $semesterIndex => $semester) {
        // Validate semester data
        if (!isset($semester['semester']) || trim($semester['semester']) === '') {
            throw new Exception("Semester " . ($semesterIndex + 1) . " is missing semester name");
        }
        
        if (!isset($semester['entries']) || empty($semester['entries'])) {
            throw new Exception("Semester " . ($semesterIndex + 1) . " has no entries");
        }
        
        // Insert all entries for this semester into the same submission_id
        foreach ($semester['entries'] as $entryIndex => $entry) {
            // Validate entry data
            if (!isset($entry['subject_area']) || !isset($entry['category_no']) || !isset($entry['course_title'])) {
                throw new Exception("Entry " . ($entryIndex + 1) . " in semester " . ($semesterIndex + 1) . " is missing required fields");
            }
            
            // Add semester name to the subject area to distinguish between semesters
            $subjectArea = $semester['semester'] . ": " . $entry['subject_area'];
            
            $stmt = $pdo->prepare("
                INSERT INTO curriculum_entries (
                    submission_id, subject_area, category_no, course_title, 
                    lec_units, lab_units, total_units, pre_requisites
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $submission_id,
                $subjectArea,
                $entry['category_no'],
                $entry['course_title'],
                floatval($entry['lec_units']),
                floatval($entry['lab_units']),
                floatval($entry['total_units']),
                $entry['pre_requisites'] ?? ''
            ]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    return_json(['success' => true, 'message' => 'Curriculum saved successfully']);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log the error
    error_log('Database error in school_save_curriculum.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    return_json([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'details' => 'Check server logs for more information'
    ], 500);
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log the error
    error_log('Validation error in school_save_curriculum.php: ' . $e->getMessage());
    
    return_json([
        'success' => false, 
        'message' => $e->getMessage()
    ], 400);
}
?>