<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHED Dashboard</title>
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
        <p class="text-center fs-2" id="heiName">Loading HEI information...</p>
        
        <div class="back-link mb-3"> <a href="ched_HEI.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to HEI List</a> </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 90%;">Date Submitted</th>
                    </tr>
                </thead>
                <tbody id="submissionsTableBody"></tbody>
            </table>
            <div id="noData" class="text-center p-4 fst-italic text-secondary" style="display: none;">
                No curriculum submissions found for this HEI.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get HEI ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const heiId = urlParams.get('hei_id');
        
        if (!heiId) {
            alert('No HEI selected');
            window.location.href = 'ched_HEI.php';
            return;
        }
        
        // Fetch curriculum submissions for the selected HEI
        fetch(`ched_get_curriculum_submissions.php?hei_id=${heiId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update HEI name if available
                if (data.submissions.length > 0) {
                    document.getElementById('heiName').textContent = data.submissions[0].hei_name;
                }
                
                const tableBody = document.getElementById('submissionsTableBody');
                tableBody.innerHTML = ''; // Clear existing content
                
                if (data.submissions.length === 0) {
                    // Show no data message if there are no submissions
                    document.getElementById('noData').style.display = 'block';
                } else {
                    // Hide no data message
                    document.getElementById('noData').style.display = 'none';
                    
                    // Populate table with submissions
                    data.submissions.forEach((submission, index) => {
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
                                        <a href="ched_view_curriculum.php?submission_id=${submission.submission_id}" class="btn btn-sm btn-primary view-btn">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                            </td>
                        `;
                        
                        tableBody.appendChild(row);
                    });
                }
            } else {
                console.error('Failed to load submissions:', data.message);
                alert('Failed to load curriculum submissions. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading curriculum submissions.');
        });
    });
    </script>
</body>
</html>