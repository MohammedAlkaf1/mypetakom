-- USER TABLE
CREATE TABLE User (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('student', 'staff','admin') NOT NULL
);

-- STUDENT TABLE
CREATE TABLE Student (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    major VARCHAR(20),
    student_matric_id VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

-- STAFF TABLE
CREATE TABLE Staff (
    staff_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    position ENUM('Advisor', 'Admin') NOT NULL,
    staff_id_card VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

-- QR CODE TABLE
CREATE TABLE QRCode (
    qrcode_id INT PRIMARY KEY AUTO_INCREMENT,
    code_url TEXT
);

-- EVENT TABLE
CREATE TABLE Event (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    qrcode_id INT,
    title VARCHAR(200),
    description TEXT,
    location VARCHAR(200),
    event_start_date DATE,
    event_status ENUM('Upcoming', 'Postponed', 'Cancelled'),
    approval_letter TEXT,
    geolocation VARCHAR(100),
    added_by INT,
    FOREIGN KEY (qrcode_id) REFERENCES QRCode(qrcode_id),
    FOREIGN KEY (added_by) REFERENCES User(user_id)
);

-- MEMBERSHIP TABLE 
CREATE TABLE Membership (
    membership_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    status ENUM('approved', 'pending', 'not_approved') NOT NULL,
    approved_by INT,
    student_matric_card VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES User(user_id),
    FOREIGN KEY (approved_by) REFERENCES User(user_id)
);

-- COMMITTEE ROLE TABLE
CREATE TABLE Committee_Role (
    cr_id INT PRIMARY KEY AUTO_INCREMENT,
    cr_desc ENUM('Main Committee', 'Committee') NOT NULL
);

-- EVENT COMMITTEE TABLE
CREATE TABLE EventCommittee (
    committee_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    user_id INT,
    cr_id INT,
    FOREIGN KEY (event_id) REFERENCES Event(event_id),
    FOREIGN KEY (user_id) REFERENCES User(user_id),
    FOREIGN KEY (cr_id) REFERENCES Committee_Role(cr_id)
);

-- ATTENDANCE TABLE
CREATE TABLE Attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    qrcode_id INT,
    check_in_time DATETIME,
    location VARCHAR(200),
    attendance_status ENUM('Active', 'Deactive') NOT NULL,
    FOREIGN KEY (event_id) REFERENCES Event(event_id),
    FOREIGN KEY (qrcode_id) REFERENCES QRCode(qrcode_id)
);

-- ATTENDANCE SLOT TABLE
CREATE TABLE Attendance_Slot (
    attendance_slot_id INT PRIMARY KEY AUTO_INCREMENT,
    attendance_id INT,
    user_id INT,
    status ENUM('present', 'absent') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id),
    FOREIGN KEY (attendance_id) REFERENCES Attendance(attendance_id)
);

-- MERIT APPLICATION TABLE
CREATE TABLE Merit_Application (
    merit_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    event_level ENUM('International', 'National', 'State', 'District', 'UMPSA') NOT NULL,
    points_main_committee INT,
    points_committee INT,
    points_participant INT,
    status VARCHAR(50),
    applied_by INT,
    FOREIGN KEY (event_id) REFERENCES Event(event_id),
    FOREIGN KEY (applied_by) REFERENCES User(user_id)
);

-- VIEW AWARDED MERITS TABLE
CREATE TABLE View_Awarded_Merits (
    student_merit_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    merit_id INT,
    role VARCHAR(50),
    points_awarded INT,
    FOREIGN KEY (user_id) REFERENCES User(user_id),
    FOREIGN KEY (merit_id) REFERENCES Merit_Application(merit_id)
);

-- MERIT CLAIMS TABLE
CREATE TABLE merit_claims (
    claim_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    event_id INT,
    role_claimed ENUM('Main Committee','Committee','Participant') NOT NULL,
    justification TEXT,
    status VARCHAR(50),
    semester VARCHAR(10),
    academic_year VARCHAR(9),
    created_at DATETIME,
    updated_at DATETIME,
    official_letter_path TEXT,
    FOREIGN KEY (user_id) REFERENCES User(user_id),
    FOREIGN KEY (event_id) REFERENCES Event(event_id)
);

CREATE TABLE merit_point_rules (
    rule_id INT PRIMARY KEY AUTO_INCREMENT,
    event_level ENUM('International','National','State','District','UMPSA') NOT NULL,
    role ENUM('Main Committee','Committee','Participant') NOT NULL,
    points INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);