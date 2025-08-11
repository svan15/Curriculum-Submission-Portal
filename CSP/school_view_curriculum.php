<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    header('Location: index.php');
    exit;
}

$hei_id = $_SESSION['hei_id'] ?? 0;
$hei_name = $_SESSION['hei_name'] ?? 'Unknown School';

// Check if submission ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: school_dashboard.php');
    exit;
}

$submission_id = $_GET['id'];

// Fetch submission details
try {
    $stmt = $pdo->prepare("
        SELECT submission_id, semester, DATE_FORMAT(submission_date, '%M %d, %Y %h:%i %p') as formatted_date
        FROM curriculum_submissions
        WHERE submission_id = ? AND hei_id = ?
    ");
    $stmt->execute([$submission_id, $hei_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        header('Location: school_dashboard.php');
        exit;
    }
    
    // Fetch all entries for this submission
    $stmt = $pdo->prepare("
        SELECT entry_id, subject_area, category_no, course_title, lec_units, lab_units, total_units, pre_requisites
        FROM curriculum_entries
        WHERE submission_id = ?
        ORDER BY entry_id ASC
    ");
    $stmt->execute([$submission_id]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize entries by semester
    $semesters = [];
    foreach ($entries as $entry) {
        // Extract semester name from subject_area (format: "Semester: Subject")
        $parts = explode(": ", $entry['subject_area'], 2);
        $semester = $parts[0];
        if (count($parts) > 1) {
            $entry['subject_area'] = $parts[1]; // Remove semester prefix from subject area
        }
        
        if (!isset($semesters[$semester])) {
            $semesters[$semester] = [];
        }
        
        $semesters[$semester][] = $entry;
    }
    
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Curriculum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .content-container {
            margin-top: 80px;
        }
    </style>
</head>
<body class="bg-light">
    <header class="fixed-top w-100 bg-primary shadow-sm d-flex align-items-center p-2">
        <img src="img/CHED-LOGO_orig.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <img src="img/Bagong-Pilipinas.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <h3 class="text-white mb-0">Curriculum Submission Portal</h3>
        <a href="logout.php" class="btn btn-outline-light ms-auto">Logout</a>
    </header>
    
    <div class="container content-container">
        <div class="text-center mb-4">
            <h2 class="mb-3">Curriculum Details</h2>
        </div>

                <!-- Buttons -->
        <div class="d-flex justify-content-between mb-3">
            <a href="school_dashboard.php" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fa fa-print"></i> Print
            </button>
        </div>

        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Submission Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Institution:</strong> <?= htmlspecialchars($hei_name) ?></p>
                        <p><strong>Submission Date:</strong> <?= htmlspecialchars($submission['formatted_date']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Submission ID:</strong> <?= htmlspecialchars($submission_id) ?></p>
                        <p><strong>File Name:</strong> <?= htmlspecialchars($submission['semester']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php foreach ($semesters as $semesterName => $semesterEntries): ?>
        <div class="semester-container">
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><?= htmlspecialchars($semesterName) ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Subject Area</th>
                                    <th>Category</th>
                                    <th>Course Title</th>
                                    <th>Lec Units</th>
                                    <th>Lab Units</th>
                                    <th>Total Units</th>
                                    <th>Pre-requisites</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($semesterEntries as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($entry['subject_area']) ?></td>
                                    <td><?= htmlspecialchars($entry['category_no']) ?></td>
                                    <td><?= htmlspecialchars($entry['course_title']) ?></td>
                                    <td><?= htmlspecialchars($entry['lec_units']) ?></td>
                                    <td><?= htmlspecialchars($entry['lab_units']) ?></td>
                                    <td><?= htmlspecialchars($entry['total_units']) ?></td>
                                    <td><?= htmlspecialchars($entry['pre_requisites']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="">
                                    <td colspan="3" class="text-end"><strong>Total Units:</strong></td>
                                    <td>
                                        <?= number_format(array_sum(array_column($semesterEntries, 'lec_units')), 2) ?>
                                    </td>
                                    <td>
                                        <?= number_format(array_sum(array_column($semesterEntries, 'lab_units')), 2) ?>
                                    </td>
                                    <td>
                                        <?= number_format(array_sum(array_column($semesterEntries, 'total_units')), 2) ?>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>