<!-- new -->
<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    header('Location: index.php');
    exit;
}

var_dump($_SESSION['hei_name']);

$hei_id = $_SESSION['hei_id'] ?? 0;
$hei_name = $_SESSION['hei_name'] ?? 'Unknown School';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .content-container { margin-top: 80px; }
        .student-table th { position: sticky; top: 0; background-color: #212529; color: white; z-index: 1; }
    </style>
</head>
<body class="bg-light p-4">
    <header class="fixed-top w-100 bg-primary shadow-sm d-flex align-items-center p-2">
        <img src="img/CHED-LOGO_orig.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <img src="img/Bagong-Pilipinas.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <h3 class="text-white mb-0">Curriculum Submission Portal</h3>
        <a href="logout.php" class="btn btn-outline-light ms-auto">Logout</a>
    </header>
    
    <div class="container mt-5 pt-4">
        <h4 class="mb-2 text-center">Submitted Forms</h4>
        <p class="text-center fs-2"><?= htmlspecialchars($hei_name) ?></p>
        
        <div class="d-flex justify-content-end mb-3">
            <button id="addProgramBtn" class="btn btn-outline-primary"><i class="fa fa-plus"></i> Fillout Form</button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 90%;">Program</th>
                    </tr>
                </thead>
                <tbody id="programTableBody"></tbody>
            </table>
        </div>
    </div>

    <!-- Fillout Form Modal -->
    <div class="modal fade" id="filloutFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Fillout Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="curriculumForm">
                <div class="mb-3">
                    <label for="curriculum-title" class="form-label fw-bold">Program <span class="text-danger">*</span></label>
                    <input type="text" class="form-control required-field" id="curriculum-title" name="curriculum_title" placeholder="e.g. BS Computer Science Curriculum" required>
                    <div class="form-text">Enter a descriptive title for this curriculum</div>
                </div>
                        <div id="semesterContainers">
                            <!-- First semester container will be added by JS -->
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-3" id="addSemester">+ Add Semester</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveCurriculum">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filloutFormModal = new bootstrap.Modal(document.getElementById('filloutFormModal'));
    const addProgramBtn = document.getElementById('addProgramBtn');
    const semesterContainers = document.getElementById('semesterContainers');
    const addSemesterBtn = document.getElementById('addSemester');
    const saveCurriculumBtn = document.getElementById('saveCurriculum');
    
    // Initialize variables
    let semesterCount = 0;
    let entryCounts = {};
    
    // Load submitted curriculum
    loadSubmittedCurriculum();
    
    // Open modal when add program button is clicked
    addProgramBtn.addEventListener('click', function() {
        // Reset form
        semesterContainers.innerHTML = '';
        semesterCount = 0;
        entryCounts = {};
        
        // Add first semester by default
        addSemester();
        
        filloutFormModal.show();
    });
    
    // Add semester button click handler
    addSemesterBtn.addEventListener('click', addSemester);
    
    // Save curriculum button click handler
    saveCurriculumBtn.addEventListener('click', saveCurriculum);
    
    // Add listener for dynamic entry buttons
    document.addEventListener('click', e => {
        if (e.target.classList.contains('add-entry') || e.target.closest('.add-entry')) {
            const button = e.target.classList.contains('add-entry') ? e.target : e.target.closest('.add-entry');
            addCurriculumEntry(button.getAttribute('data-semester-index'));
        }
    });
    
    // Calculate total units when lec or lab units change
    document.addEventListener("input", e => {
        if (e.target.classList.contains("lec-units") || e.target.classList.contains("lab-units")) {
            const entryDiv = e.target.closest(".entry");
            if (entryDiv) {
                const lecUnits = parseFloat(entryDiv.querySelector(".lec-units").value) || 0;
                const labUnits = parseFloat(entryDiv.querySelector(".lab-units").value) || 0;
                entryDiv.querySelector(".total-units").value = lecUnits + labUnits;
            }
        }
        
        // Remove invalid class when user starts typing
        if (e.target.classList.contains("required-field")) {
            e.target.classList.remove("is-invalid");
        }
    });
    
    // Function to add a new semester
    function addSemester() {
        if (semesterCount >= 3) {
            alert("You can only add up to 3 semesters.");
            return;
        }
        
        semesterCount++;
        entryCounts[semesterCount] = 0;
        
        const semesterHtml = `
            <div class="semester-container mb-4" data-semester-index="${semesterCount}">
                <div class="border p-3 bg-light rounded">
                    <div class="mb-3">
                        <label for="semester-${semesterCount}" class="form-label fw-bold">Semester</label>
                        <select class="form-select" id="semester-${semesterCount}" name="semester[]" required>
                            <option value="1st">1st Trimester</option>
                            <option value="2nd">2nd Trimester</option>
                            <option value="3rd">3rd Trimester</option>
                        </select>
                    </div>
                    <div class="curriculum-entries"></div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-success btn-sm add-entry" data-semester-index="${semesterCount}">+ Add Curriculum Entry</button>
                    </div>
                </div>
            </div>`;
        
        semesterContainers.insertAdjacentHTML("beforeend", semesterHtml);
        addCurriculumEntry(semesterCount);
    }
    
    // Function to add a new curriculum entry to a semester
    function addCurriculumEntry(semesterIndex) {
        const entryCount = entryCounts[semesterIndex] + 1;
        
        if (entryCount > 10) {
            alert("You can only add up to 10 curriculum entries per semester.");
            return;
        }
        
        entryCounts[semesterIndex]++;
        
        const entryHtml = `
            <div class="border p-3 mt-2 entry" id="entry-${semesterIndex}-${entryCount}">
                <div class="d-flex justify-content-between align-items-center">
                    <h6>Curriculum Entry ${entryCount}</h6>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(${semesterIndex}, ${entryCount})">Remove</button>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Subject Area <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required-field" name="subject_area[${semesterIndex}][]" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category No. <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required-field" name="category_no[${semesterIndex}][]" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Course Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required-field" name="course_title[${semesterIndex}][]" required>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-2">
                        <label class="form-label">Lec Units <span class="text-danger">*</span></label>
                        <input type="number" class="form-control lec-units required-field" name="lec_units[${semesterIndex}][]" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Lab Units <span class="text-danger">*</span></label>
                        <input type="number" class="form-control lab-units required-field" name="lab_units[${semesterIndex}][]" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Total Units</label>
                        <input type="number" class="form-control total-units" name="total_units[${semesterIndex}][]" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pre-requisites</label>
                        <input type="text" class="form-control" name="pre_requisites[${semesterIndex}][]">
                    </div>
                </div>
            </div>`;
        
        document.querySelector(`.semester-container[data-semester-index="${semesterIndex}"] .curriculum-entries`).insertAdjacentHTML("beforeend", entryHtml);
    }
    
// Function to save curriculum
function saveCurriculum() {
    // Validate form
    if (document.querySelectorAll('.entry').length === 0) {
        alert("Please add at least one curriculum entry.");
        return false;
    }
    
    const form = document.getElementById("curriculumForm");
    let isValid = true;
    
    // Validate semester selections
    form.querySelectorAll('select[name="semester[]"]').forEach(select => {
        if (!select.value) {
            select.classList.add("is-invalid");
            if (isValid) {
                select.focus();
                isValid = false;
            }
        } else {
            select.classList.remove("is-invalid");
        }
    });
    
    if (!isValid) {
        alert("Please select all semester values.");
        return false;
    }
    
    // Validate required fields
    form.querySelectorAll('.required-field').forEach(field => {
        if (!field.value.trim()) {
            field.classList.add("is-invalid");
            if (isValid) {
                field.focus();
                isValid = false;
            }
        } else {
            field.classList.remove("is-invalid");
        }
    });
    
    if (!isValid) {
        alert("Please fill out all required fields marked with *");
        return false;
    }
    
    // Show loading indicator
    const saveButton = document.getElementById('saveCurriculum');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    saveButton.disabled = true;
    
    // Format data properly for backend
    const formData = new FormData(form);
    const jsonData = {
        curriculum_title: document.getElementById('curriculum-title').value,
        semesters: []
    };
    
    // Process each semester container
    document.querySelectorAll('.semester-container').forEach(semesterContainer => {
        const semesterIndex = semesterContainer.getAttribute('data-semester-index');
        const select = semesterContainer.querySelector(`#semester-${semesterIndex}`);
        
        const semesterEntries = [];
        semesterContainer.querySelectorAll('.entry').forEach(entry => {
            semesterEntries.push({
                subject_area: entry.querySelector('input[name^="subject_area"]').value,
                category_no: entry.querySelector('input[name^="category_no"]').value,
                course_title: entry.querySelector('input[name^="course_title"]').value,
                lec_units: entry.querySelector('input[name^="lec_units"]').value,
                lab_units: entry.querySelector('input[name^="lab_units"]').value,
                total_units: entry.querySelector('input[name^="total_units"]').value,
                pre_requisites: entry.querySelector('input[name^="pre_requisites"]').value || ''
            });
        });
        
        if (semesterEntries.length > 0) {
            jsonData.semesters.push({
                semester: select.value,
                entries: semesterEntries
            });
        }
    });
    
    // Send data to server
    fetch('school_save_curriculum.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(jsonData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        // Reset button
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
        
        if (data.success) {
            alert('Curriculum saved successfully!');
            filloutFormModal.hide();
            loadSubmittedCurriculum();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
            console.error('Server error details:', data);
        }
    })
    .catch(error => {
        // Reset button
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
        
        console.error('Error:', error);
        alert('An error occurred while saving the curriculum: ' + error.message);
    });
}
    
    // Function to load submitted curriculum
    function loadSubmittedCurriculum() {
        fetch('school_get_curriculum_submissions.php')
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById('programTableBody');
                tableBody.innerHTML = '';
                
                if (data.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="2" class="text-center">No submissions found.</td>';
                    tableBody.appendChild(row);
                    return;
                }
                
                data.forEach((submission, index) => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold">${submission.semester}</span><br>
                                    <small class="text-muted">Submitted: ${submission.submission_date}</small>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-primary view-btn" data-id="${submission.submission_id}">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="${submission.submission_id}">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    `;
                    
                    tableBody.appendChild(row);
                    
                    // Add event listeners for view and delete buttons
                    row.querySelector('.view-btn').addEventListener('click', function() {
                        window.location.href = `school_view_curriculum.php?id=${submission.submission_id}`;
                    });
                    
                    row.querySelector('.delete-btn').addEventListener('click', function() {
                        if (confirm('Are you sure you want to delete this submission?')) {
                            deleteCurriculumSubmission(submission.submission_id);
                        }
                    });
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while loading the curriculum submissions.');
            });
    }
    
    // Function to delete curriculum submission
    function deleteCurriculumSubmission(submissionId) {
        fetch('school_delete_curriculum.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ submission_id: submissionId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Submission deleted successfully!');
                loadSubmittedCurriculum();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the submission.');
        });
    }
});

// Global function for removing entries
function removeEntry(semesterIndex, entryId) {
    const entryElement = document.getElementById(`entry-${semesterIndex}-${entryId}`);
    if (entryElement) entryElement.remove();
}
</script>
</html>