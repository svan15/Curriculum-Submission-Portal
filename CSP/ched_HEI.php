<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHED Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-container {
            overflow-x: auto; /* Add horizontal scrolling if needed */
        }
        th, td {
            white-space: nowrap; /* Prevent text from wrapping */
        }

        .student-table th {
            position: sticky;
            top: 0;
            background-color: #212529; /* Dark background for header */
            color: white; /* White text for header */
            z-index: 1; /* Ensure the header stays on top */
        }
        .content-container {
            margin-top: 80px;
        }
    </style>
</head>
<body class="bg-light p-4">
<header class="fixed-top w-100 bg-primary shadow-sm d-flex align-items-center p-2">
        <img src="img/CHED-LOGO_orig.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <img src="img/Bagong-Pilipinas.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <h3 class="text-white mb-0">Curriculum Submission Portal</h3>
        <a href="logout.php" class="btn btn-outline-light ms-auto">Logout</a>
    </header>
    <div class="container-fluid content-container">
        <h2 class="mb-4 text-center">HEI List</h2>

        <div class="table-container">
            <table class="table table-bordered table-striped student-table">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 90%;">School Name</th>
                    </tr>
                </thead>
                <tbody id="chedTableBody">
                    <tr>

                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch all HEIs from the database
    fetch('ched_get_heis.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tableBody = document.getElementById('chedTableBody');
            tableBody.innerHTML = ''; // Clear existing content
            
            // Sort HEIs alphabetically by name
            data.heis.sort((a, b) => a.hei_name.localeCompare(b.hei_name));
            
            // Populate table with sorted HEIs
            data.heis.forEach((hei, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td><a href="ched_dashboard.php?hei_id=${hei.hei_id}" style="color: black;">
                        ${hei.hei_name}
                    </a></td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            console.error('Failed to load HEIs:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>
</html>