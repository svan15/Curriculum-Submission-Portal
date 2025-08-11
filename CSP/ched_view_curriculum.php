<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum Details</title>
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
            <a href="#" id="backButton" class="btn btn-secondary">
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
                        <p><strong>Institution:</strong> <span id="heiName">Loading...</span></p>
                        <p><strong>Submission Date:</strong> <span id="submissionDate">Loading...</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Submission ID:</strong> <span id="submissionId">Loading...</span></p>
                        <p><strong>File Name:</strong> <span id="semester">Loading...</span></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="semestersContainer">
            <!-- Semester sections will be dynamically inserted here -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get submission_id from URL
        const urlParams = new URLSearchParams(window.location.search);
        const submissionId = urlParams.get('submission_id');
        let heiId = null;
        
        if (!submissionId) {
            alert('No curriculum submission selected');
            window.location.href = 'ched_HEI.php';
            return;
        }
        
        // Set submission ID in the page
        document.getElementById('submissionId').textContent = submissionId;
        
        // Set up back button
        document.getElementById('backButton').addEventListener('click', function(e) {
            e.preventDefault();
            if (heiId) {
                window.location.href = `ched_dashboard.php?hei_id=${heiId}`;
            } else {
                window.location.href = 'ched_HEI.php';
            }
        });
        
        // Fetch curriculum details
        fetch(`ched_get_curriculum_details.php?submission_id=${submissionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update submission info
                document.getElementById('heiName').textContent = data.hei_name;
                document.getElementById('semester').textContent = data.submission.semester;
                
                // Format the date nicely
                const submissionDate = new Date(data.submission.submission_date);
                const options = { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: true
                };
                document.getElementById('submissionDate').textContent = submissionDate.toLocaleDateString('en-US', options);
                
                // Save hei_id for back button
                heiId = data.submission.hei_id;
                
                if (data.entries.length === 0) {
                    // Show message if there are no entries
                    const container = document.getElementById('semestersContainer');
                    container.innerHTML = '<div class="alert alert-info">No curriculum entries found.</div>';
                } else {
                    // Group entries by semester
                    const semesters = groupBySemester(data.entries);
                    
                    // Render each semester section
                    renderSemesters(semesters);
                }
            } else {
                console.error('Failed to load curriculum details:', data.message);
                alert('Failed to load curriculum details. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading curriculum details.');
        });
        
        // Function to group entries by semester
        function groupBySemester(entries) {
            const semesters = {};
            
            entries.forEach(entry => {
                // Extract semester name from subject_area (format: "Semester: Subject")
                const parts = entry.subject_area.split(": ", 2);
                const semester = parts[0];
                
                if (parts.length > 1) {
                    entry.subject_area = parts[1]; // Remove semester prefix
                }
                
                if (!semesters[semester]) {
                    semesters[semester] = [];
                }
                
                semesters[semester].push(entry);
            });
            
            return semesters;
        }
        
        // Function to render semester sections
        function renderSemesters(semesters) {
            const container = document.getElementById('semestersContainer');
            container.innerHTML = ''; // Clear container
            
            // For each semester, create a card with a table
            Object.keys(semesters).forEach(semesterName => {
                const semesterEntries = semesters[semesterName];
                
                // Create semester section
                const semesterSection = document.createElement('div');
                semesterSection.className = 'semester-container';
                semesterSection.innerHTML = `
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">${semesterName}</h5>
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
                                    <tbody id="tbody-${semesterName.replace(/\s+/g, '-')}">
                                        <!-- Entries will be added here -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total Units:</strong></td>
                                            <td id="lec-total-${semesterName.replace(/\s+/g, '-')}">0.00</td>
                                            <td id="lab-total-${semesterName.replace(/\s+/g, '-')}">0.00</td>
                                            <td id="total-units-${semesterName.replace(/\s+/g, '-')}">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
                
                container.appendChild(semesterSection);
                
                // Get the table body for this semester
                const tableId = `tbody-${semesterName.replace(/\s+/g, '-')}`;
                const tableBody = document.getElementById(tableId);
                
                // Calculate totals
                let lecTotal = 0;
                let labTotal = 0;
                let unitsTotal = 0;
                
                // Add entries to the table
                semesterEntries.forEach(entry => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${entry.subject_area}</td>
                        <td>${entry.category_no}</td>
                        <td>${entry.course_title}</td>
                        <td>${entry.lec_units}</td>
                        <td>${entry.lab_units}</td>
                        <td>${entry.total_units}</td>
                        <td>${entry.pre_requisites || 'None'}</td>
                    `;
                    tableBody.appendChild(row);
                    
                    // Update totals
                    lecTotal += parseFloat(entry.lec_units) || 0;
                    labTotal += parseFloat(entry.lab_units) || 0;
                    unitsTotal += parseFloat(entry.total_units) || 0;
                });
                
                // Update total units in the footer
                document.getElementById(`lec-total-${semesterName.replace(/\s+/g, '-')}`).textContent = lecTotal.toFixed(2);
                document.getElementById(`lab-total-${semesterName.replace(/\s+/g, '-')}`).textContent = labTotal.toFixed(2);
                document.getElementById(`total-units-${semesterName.replace(/\s+/g, '-')}`).textContent = unitsTotal.toFixed(2);
            });
        }
    });
    </script>
</body>
</html>