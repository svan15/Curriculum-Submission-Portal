<?php
// Start the session
session_start();

// Include database connection
require_once 'db_connect.php';

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on user type
    if ($_SESSION['user_type'] === 'ched') {
        header('Location: ched_HEI.php');
        exit;
    } else {
        header('Location: school_dashboard.php');
        exit;
    }
}

// Process the registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Get basic user data
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $user_type = $_POST['user_type'];
        
        // Check if username already exists
        $stmt = $pdo->prepare('SELECT * FROM User WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            throw new Exception('Username already exists');
        }
        
        // Insert into users table
        $stmt = $pdo->prepare('INSERT INTO User (username, password, user_type) VALUES (?, ?, ?)');
        $stmt->execute([$username, $password, $user_type]);
        $user_id = $pdo->lastInsertId();
        
        // Based on user type, insert into respective profile table
        if ($user_type === 'ched') {
            $first_name = $_POST['first_name'];
            $middle_name = $_POST['middle_name'] ?? '';
            $last_name = $_POST['last_name'];
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $position = $_POST['position'] ?? '';
            
            $stmt = $pdo->prepare('INSERT INTO ChedDetails (user_id, first_name, middle_name, last_name, email, phone, address, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $first_name, $middle_name, $last_name, $email, $phone, $address, $position]);
        } else if ($user_type === 'school') {
            $hei_name = $_POST['hei_name'];
            $hei_type = $_POST['hei_type'];
            $semester_type = $_POST['semester_type'];
            $year_established = $_POST['year_established'];
            $street_barangay = $_POST['street_barangay'];
            $municipality = $_POST['municipality'];
            $province = $_POST['province'];
            $zip_code = $_POST['zip_code'];
            $contact_no = $_POST['contact_no'] ?? '';
            $email_address = $_POST['email_address'];
            $website = $_POST['website'] ?? '';
            $year_established = $_POST['year_established'];
            $head = $_POST['head'];
            $designation = $_POST['designation'];
            $calendar = $_POST['calendar'];
            $land_area = $_POST['land_area'] ?? '';
            $x_coordinate = $_POST['x_coordinate'] ?? '';
            $y_coordinate = $_POST['y_coordinate'] ?? '';
            $registrar = $_POST['registrar'] ?? '';
            $hei_logo = $_POST['hei_logo'] ?? '';
            
            $stmt = $pdo->prepare('INSERT INTO tbl_heis (user_id, hei_name, hei_type, semester_type, street_barangay, municipality, province, zip_code, contact_no, email_address, website, year_established, head, designation, calendar, land_area, x_coordinate, y_coordinate, registrar, hei_logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $hei_name, $hei_type, $semester_type, $street_barangay, $municipality, $province, $zip_code, $contact_no, $email_address, $website, $year_established, $head, $designation, $calendar, $land_area, $x_coordinate, $y_coordinate, $registrar, $hei_logo]);

            $_SESSION['hei_name'] = $hei_name;
        }
        
        // Commit the transaction
        $pdo->commit();
        
        // Redirect to login page
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        // Rollback the transaction if something failed
        $pdo->rollBack();
        $error = 'Registration failed: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></head>
<body class="d-flex flex-column vh-100 bg-light">
    <header class="fixed-top w-100 bg-primary shadow-sm d-flex align-items-center p-2">
        <img src="img/CHED-LOGO_orig.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <img src="img/Bagong-Pilipinas.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <h3 class="text-white mb-0">Curriculum Submission Portal</h3>
    </header>
    
    <div class="container d-flex justify-content-center align-items-center flex-grow-1"style="margin-top: 70px;">
        <div class="card shadow rounded" style="width: 500px;">
            <div class="card-body p-4">
                <h2 class="text-center mb-4">Registration Form</h2>

                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger text-center"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <!-- Basic Information -->
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                        <input type="text" name="username" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">User Type</label>
                        <select name="user_type" class="form-select" required id="userType">
                            <option value="" selected disabled>Select a User Type</option>
                            <option value="ched">CHED</option>
                            <option value="school">School</option>
                        </select>
                    </div>

                    <!-- Name Fields -->
                    <div id="nameFields">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- CHED Fields -->
                    <div id="chedFields" class="d-none">
                        <hr class="my-3">
                        <h5 class="mb-3">CHED Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <input type="email" name="email" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <div class="input-group">
                                    <input type="text" name="phone" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <div class="input-group">
                                <input type="text" name="address" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <div class="input-group">
                                <input type="text" name="position" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- School Fields -->
                    <div id="schoolFields" class="d-none">
                        <hr class="my-3">
                        <h5 class="mb-3">Higher Education Institution Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">HEI Name</label>
                                <input type="text" name="hei_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">HEI Type</label>
                                <input type="text" name="hei_type" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Semester Type</label>
                                <input type="text" name="semester_type" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Year Established</label>
                                <input type="text" name="year_established" class="form-control" required>
                            </div>
                        </div>
                        
                        <h6 class="mb-2 mt-3">Address Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Street & Barangay</label>
                                <input type="text" name="street_barangay" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Municipality</label>
                                <input type="text" name="municipality" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Province</label>
                                <input type="text" name="province" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Zip Code</label>
                                <input type="text" name="zip_code" class="form-control" required>
                            </div>
                        </div>
                        
                        <h6 class="mb-2 mt-3">Contact Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact No</label>
                                <div class="input-group">
                                    <input type="text" name="contact_no" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <input type="email" name="email_address" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <div class="input-group">
                                <input type="text" name="website" class="form-control">
                            </div>

                            <label class="form-label">Year Established</label>
                            <div class="input-group">
                                <input type="text" name="year_established" class="form-control">
                            </div>
                        </div>
                        
                        <h6 class="mb-2 mt-3">Institution Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Head</label>
                                <input type="text" name="head" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Calendar</label>
                                <input type="text" name="calendar" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Land Area</label>
                                <input type="text" name="land_area" class="form-control">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">X Coordinate</label>
                                <input type="text" name="x_coordinate" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Y Coordinate</label>
                                <input type="text" name="y_coordinate" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Registrar</label>
                            <input type="text" name="registrar" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">HEI Logo</label>
                            <div class="input-group">
                                <input type="text" name="hei_logo" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                    <p class="mt-3 text-center">
                        <a href="index.php" class="text-primary text-decoration-none">Already have an account? Login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // DOM Elements
            const userTypeSelect = document.getElementById("userType");
            const chedFields = document.getElementById("chedFields");
            const schoolFields = document.getElementById("schoolFields");
            const nameFields = document.getElementById("nameFields");
            const passwordToggle = document.getElementById("togglePassword");
            const passwordInput = document.querySelector('input[name="password"]');
        
            // Password visibility toggle
            if (passwordToggle) {
                passwordToggle.addEventListener("click", function () {
                    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
                    passwordInput.setAttribute("type", type);
                    this.querySelector("i").classList.toggle("bi-eye");
                    this.querySelector("i").classList.toggle("bi-eye-slash");
                });
            }
        
            // Initially hide CHED and School fields
            chedFields.classList.add("d-none");
            schoolFields.classList.add("d-none");
        
            // Disable all inputs inside CHED, School, and Name sections
            document.querySelectorAll("#chedFields input, #chedFields select, #schoolFields input, #schoolFields select, #nameFields input").forEach(input => {
                input.setAttribute("disabled", "disabled");
            });
        
            // User type change handler
            userTypeSelect.addEventListener("change", function () {
                // Reset all fields to disabled state
                document.querySelectorAll("#chedFields input, #chedFields select, #schoolFields input, #schoolFields select, #nameFields input").forEach(input => {
                    input.setAttribute("disabled", "disabled");
                });
            
                if (this.value === "ched") {
                    // Show and enable CHED fields and Name fields
                    chedFields.classList.remove("d-none");
                    schoolFields.classList.add("d-none");
                    nameFields.classList.remove("d-none");
                
                    document.querySelectorAll("#chedFields input, #chedFields select, #nameFields input").forEach(input => {
                        input.removeAttribute("disabled");
                    });
                } else if (this.value === "school") {
                    // Show and enable School fields, hide Name fields
                    chedFields.classList.add("d-none");
                    schoolFields.classList.remove("d-none");
                    nameFields.classList.add("d-none");
                
                    document.querySelectorAll("#schoolFields input, #schoolFields select").forEach(input => {
                        input.removeAttribute("disabled");
                    });
                } else {
                    // No valid selection - show only Name fields
                    chedFields.classList.add("d-none");
                    schoolFields.classList.add("d-none");
                    nameFields.classList.remove("d-none");
                
                    // Only enable name fields if default option is somehow selected
                    document.querySelectorAll("#nameFields input").forEach(input => {
                        input.removeAttribute("disabled");
                    });
                }
            });
        
            // For any file upload buttons
            const browseButtons = document.querySelectorAll(".btn-outline-secondary");
            browseButtons.forEach(button => {
                if (button.textContent.trim() === "Browse") {
                    button.addEventListener("click", function() {
                        const inputField = this.previousElementSibling;
                        if (inputField) {
                            // Here you would normally trigger a file dialog
                            // Since we're just using a text field for the logo, we'll just focus it
                            inputField.focus();
                        }
                    });
                }
            });
        }); 
    </script>
</body>
</html>

