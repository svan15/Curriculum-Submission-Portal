CREATE DATABASE Ched;
USE Ched;

CREATE TABLE User (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('ched', 'school', 'admin') NOT NULL, -- plus curriculum user type
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ChedDetails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    middle_name VARCHAR(255),
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    position VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);

CREATE TABLE `tbl_heis` (
  `hei_id` int(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
  `user_id` INT UNIQUE NOT NULL,
  `hei_name` varchar(128) NOT NULL,
  `hei_type` varchar(32) NOT NULL,
  `semester_type` varchar(16) NOT NULL DEFAULT 'Regular',
  `street_barangay` varchar(64) NOT NULL,
  `municipality` varchar(64) NOT NULL,
  `province` varchar(64) NOT NULL,
  `zip_code` varchar(8) NOT NULL,
  `contact_no` varchar(64) DEFAULT NULL,
  `email_address` varchar(64) NOT NULL,
  `website` varchar(64) NOT NULL,
  `year_established` varchar(8) NOT NULL,
  `head` varchar(64) NOT NULL,
  `designation` varchar(32) NOT NULL,
  `calendar` varchar(16) NOT NULL,
  `land_area` varchar(32) NOT NULL,
  `x_coordinate` varchar(32) NOT NULL,
  `y_coordinate` varchar(32) NOT NULL,
  `registrar` varchar(128) DEFAULT NULL,
  `hei_logo` varchar(256) DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE Programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    hei_id INT NOT NULL, -- Foreign key linking to tbl_heis
    program_name VARCHAR(255) NOT NULL, -- Do not abbreviate
    major VARCHAR(255), -- Do not abbreviate
    learning_modality ENUM('Face to Face', 'Hybrid', 'Online') NOT NULL,
    academic_year_started YEAR NOT NULL,
    other_remarks TEXT,
    category VARCHAR(255),
    others VARCHAR(255),
    serial VARCHAR(255),
    year INT,
    FOREIGN KEY (hei_id) REFERENCES tbl_heis(hei_id) ON DELETE CASCADE
);

CREATE TABLE Graduates (
    graduate_id INT AUTO_INCREMENT PRIMARY KEY,
    hei_id INT NOT NULL, -- Foreign key linking to tbl_heis
    academic_year_started YEAR NOT NULL, -- not less than the current year
    semester_started ENUM('1st', '2nd','3rd', 'Summer') NOT NULL,
    academic_year_ended YEAR NOT NULL,
    semester_ended ENUM('1st', '2nd','3rd', 'Summer') NOT NULL,
    program_name VARCHAR(255) NOT NULL, -- Do not abbreviate
    major_name VARCHAR(255), -- Do not abbreviate
    student_id_no VARCHAR(50) NOT NULL UNIQUE,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    suffix VARCHAR(10),
    date_of_birth DATE NOT NULL,
    sex ENUM('M', 'F') NOT NULL,
    student_type ENUM('Foreign', 'Regular', 'Exchange', 'LEP', 'ETEEAP', 'DE', 'TNE', 'Others') NOT NULL,
    if_others VARCHAR(255) DEFAULT NULL,
    special_order_no VARCHAR(100) DEFAULT NULL,
    FOREIGN KEY (hei_id) REFERENCES tbl_heis(hei_id) ON DELETE CASCADE
);

CREATE TABLE Enrollment (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    hei_id INT NOT NULL, -- Foreign key linking to tbl_heis
    semester ENUM('1st', '2nd', '3rd', 'Summer') NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    program_level ENUM('Pre-Baccalaureate', 'Baccalaureate', 'Post Baccalaureate', 'Masteral', 'Doctorate') NOT NULL,
    program_name VARCHAR(255) NOT NULL, -- Do not abbreviate
    major_name VARCHAR(255), -- Do not abbreviate
    year_level ENUM('1 Old', '1 New', '2', '3', '4', '5', '6', '7') NOT NULL,
    student_id_no VARCHAR(50) NOT NULL UNIQUE,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    suffix VARCHAR(10),
    sex ENUM('M', 'F') NOT NULL,
    sector ENUM('Indigenous People', 'Solo Parent', 'PWD', 'Senior Citizen') DEFAULT NULL,
    sector_details VARCHAR(255) DEFAULT NULL, -- If sector is Indigenous People or PWD
    nationality VARCHAR(100) NOT NULL,
    student_type ENUM('Foreign', 'Regular', 'Exchange', 'LEP', 'ETEEAP', 'DE', 'TNE', 'Others') NOT NULL,
    if_others VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (hei_id) REFERENCES tbl_heis(hei_id) ON DELETE CASCADE
);

-- Separate table to store subjects and units for an enrollment
CREATE TABLE EnrolledSubjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    subject_code VARCHAR(50) NOT NULL,
    units INT NOT NULL,
    FOREIGN KEY (enrollment_id) REFERENCES Enrollment(student_id) ON DELETE CASCADE
);

-- CSP TABLES
CREATE TABLE IF NOT EXISTS curriculum_submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    hei_id INT NOT NULL,
    semester VARCHAR(50) NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hei_id) REFERENCES tbl_heis(hei_id) ON DELETE CASCADE
);

-- Curriculum entries (individual subjects)
CREATE TABLE IF NOT EXISTS curriculum_entries (
    entry_id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    subject_area VARCHAR(255) NOT NULL,
    category_no VARCHAR(50) NOT NULL,
    course_title VARCHAR(255) NOT NULL,
    lec_units DECIMAL(4,2) NOT NULL,
    lab_units DECIMAL(4,2) NOT NULL,
    total_units DECIMAL(4,2) NOT NULL,
    pre_requisites VARCHAR(255),
    FOREIGN KEY (submission_id) REFERENCES curriculum_submissions(submission_id) ON DELETE CASCADE
);

