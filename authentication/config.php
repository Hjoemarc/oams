<?php
function db_connect()
{
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'zppsu_accreditation_db';

    try {
        // Step 1: Initial connection to MySQL (no DB yet)
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Step 2: Create the database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

        // Step 3: Reconnect using the newly created database
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Step 4: Define table structure
       $tableQueries = [
            // ==================== BASIC ORGANIZATION ====================
            "CREATE TABLE IF NOT EXISTS departments (
                department_id INT AUTO_INCREMENT PRIMARY KEY,
                department_code VARCHAR(20) NOT NULL UNIQUE,  -- CAS, COE, CBA
                department_name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",

            "CREATE TABLE IF NOT EXISTS programs (
                program_id INT AUTO_INCREMENT PRIMARY KEY,
                program_code VARCHAR(20) NOT NULL UNIQUE,     -- BSIT, BSCS, BSEE
                program_name VARCHAR(255) NOT NULL,
                department_id INT NOT NULL,
                FOREIGN KEY (department_id) REFERENCES departments(department_id)
            )",

            "CREATE TABLE IF NOT EXISTS users (
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                middle_name VARCHAR(50),
                suffix VARCHAR(5),
                user_role ENUM('admin', 'qa', 'head', 'faculty') NOT NULL,
                department_id INT,
                is_active ENUM('Active', 'Inactive') DEFAULT 'Active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (department_id) REFERENCES departments(department_id)
            )",

            // ==================== SIMPLE ACCREDITATION FRAMEWORK ====================
            "CREATE TABLE IF NOT EXISTS accreditation_areas (
                area_id INT AUTO_INCREMENT PRIMARY KEY,
                area_number VARCHAR(10) NOT NULL,      
                area_name VARCHAR(255) NOT NULL,
                description TEXT
            )",

            "CREATE TABLE IF NOT EXISTS accreditation_indicators (
                indicator_id INT AUTO_INCREMENT PRIMARY KEY,
                area_id INT NOT NULL,
                indicator_code VARCHAR(20) NOT NULL,   
                indicator_description TEXT NOT NULL,
                FOREIGN KEY (area_id) REFERENCES accreditation_areas(area_id)
            )",

            "CREATE TABLE IF NOT EXISTS accreditation_cycles (
                cycle_id INT AUTO_INCREMENT PRIMARY KEY,
                cycle_name VARCHAR(100) NOT NULL,     
                academic_year VARCHAR(9) NOT NULL,
                status ENUM('upcoming', 'active', 'closed') DEFAULT 'upcoming',
                deadline DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",

            // ==================== CORE WORKFLOW ====================
            "CREATE TABLE IF NOT EXISTS assignments (
                assignment_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                indicator_id INT NOT NULL,
                program_id INT NOT NULL,
                status ENUM('not_started', 'in_progress', 'submitted', 'completed') DEFAULT 'not_started',
                cycle_id INT NOT NULL,
                assigned_by INT NOT NULL,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id),
                FOREIGN KEY (indicator_id) REFERENCES accreditation_indicators(indicator_id),
                FOREIGN KEY (program_id) REFERENCES programs(program_id),
                FOREIGN KEY (cycle_id) REFERENCES accreditation_cycles(cycle_id),
                FOREIGN KEY (assigned_by) REFERENCES users(user_id)
            )",

            "CREATE TABLE IF NOT EXISTS documents (
                document_id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size INT NOT NULL,
                assignment_id INT NOT NULL,
                uploaded_by INT NOT NULL,
                indicator_id INT NOT NULL,
                program_id INT NOT NULL,
                cycle_id INT NOT NULL,
                description TEXT,
                document_type VARCHAR(50) DEFAULT 'other',
                is_archive TINYINT(1) DEFAULT 0,
                original_filename VARCHAR(255),
                status ENUM('draft', 'submitted', 'under_review', 'needs_revision', 'approved') DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id),
                FOREIGN KEY (uploaded_by) REFERENCES users(user_id),
                FOREIGN KEY (indicator_id) REFERENCES accreditation_indicators(indicator_id),
                FOREIGN KEY (program_id) REFERENCES programs(program_id),
                FOREIGN KEY (cycle_id) REFERENCES accreditation_cycles(cycle_id)
            )",

            "CREATE TABLE IF NOT EXISTS reviews (
                review_id INT AUTO_INCREMENT PRIMARY KEY,
                document_id INT NOT NULL,
                reviewer_id INT NOT NULL,
                review_action ENUM('approved', 'needs_revision') NOT NULL,
                comments TEXT,
                reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (document_id) REFERENCES documents(document_id),
                FOREIGN KEY (reviewer_id) REFERENCES users(user_id)
            )",

            "CREATE TABLE IF NOT EXISTS compliance_tracking (
                compliance_id INT AUTO_INCREMENT PRIMARY KEY,
                program_id INT NOT NULL,
                cycle_id INT NOT NULL,
                indicator_id INT NOT NULL,
                status ENUM('compliant', 'non_compliant', 'partial', 'not_assessed') DEFAULT 'not_assessed',
                compliance_score DECIMAL(5,2),
                reviewer_id INT,
                review_date DATE,
                comments TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (program_id) REFERENCES programs(program_id),
                FOREIGN KEY (cycle_id) REFERENCES accreditation_cycles(cycle_id),
                FOREIGN KEY (indicator_id) REFERENCES accreditation_indicators(indicator_id),
                FOREIGN KEY (reviewer_id) REFERENCES users(user_id)
            )",

            // ==================== OPTIONAL (Can add later) ====================
            "CREATE TABLE IF NOT EXISTS notifications (
                notification_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id)
            )"
        ];

        foreach ($tableQueries as $sql) {
            $pdo->exec($sql);
        }

        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count == 0) {
            // 1. ADD DEFAULT ADMIN USER
            $stmt = $pdo->prepare("INSERT INTO users (
                username, email, password, first_name, last_name, user_role, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                'admin',                    
                'admin@zppsu.edu.ph',                     
                password_hash('admin123', PASSWORD_BCRYPT),             
                'Juan',                         
                'Dela Cruz',
                'admin',
                'Active'
            ]);
            
            // ==================== ADD SAMPLE ACCREDITATION DATA ====================
            
            // 2. ADD ACCREDITATION AREAS (AACCUP Standards)
            $stmt = $pdo->prepare("INSERT INTO accreditation_areas (area_number, area_name, description) VALUES 
                ('I', 'Institutional Mission, Vision, and Goals', 'Clear and documented institutional direction and purpose.'),
                ('II', 'Faculty', 'Qualified, sufficient, and competent teaching personnel.'),
                ('III', 'Curriculum and Instruction', 'Relevant, updated, and effectively delivered academic programs.'),
                ('IV', 'Support to Students', 'Comprehensive student services and development programs.'),
                ('V', 'Research', 'Viable research program contributing to knowledge generation.')
            ");
            $stmt->execute();
            
            // Get the inserted area IDs
            $area_result = $pdo->query("SELECT area_id, area_number FROM accreditation_areas ORDER BY area_id");
            $areas = [];
            while ($row = $area_result->fetch(PDO::FETCH_ASSOC)) {
                $areas[$row['area_number']] = $row['area_id'];
            }
            
            // 3. ADD ACCREDITATION INDICATORS (Specific Requirements)
            $stmt = $pdo->prepare("INSERT INTO accreditation_indicators (area_id, indicator_code, indicator_description) VALUES 
                -- AREA I: Mission, Vision, Goals
                (?, 'I.1', 'The institution has clear and documented Vision, Mission, and Goals (VMGO).'),
                (?, 'I.2', 'The VMGO are formulated and approved through participatory process.'),
                (?, 'I.3', 'The VMGO are disseminated to stakeholders.'),
                (?, 'I.4', 'Institutional activities are aligned with VMGO.'),
                
                -- AREA II: Faculty
                (?, 'II.1', 'Faculty members possess appropriate academic qualifications.'),
                (?, 'II.2', 'There is a sufficient number of full-time faculty.'),
                (?, 'II.3', 'Faculty development programs are implemented.'),
                (?, 'II.4', 'Faculty performance is evaluated regularly.'),
                
                -- AREA III: Curriculum
                (?, 'III.1', 'The curriculum is aligned with program objectives.'),
                (?, 'III.2', 'Curriculum is reviewed and updated regularly.'),
                (?, 'III.3', 'Instructional materials are current and relevant.'),
                (?, 'III.4', 'Varied teaching strategies are employed.')
            ");
            
            // Execute with area IDs
            $stmt->execute([
                $areas['I'], $areas['I'], $areas['I'], $areas['I'],  // Area I indicators
                $areas['II'], $areas['II'], $areas['II'], $areas['II'], // Area II indicators  
                $areas['III'], $areas['III'], $areas['III'], $areas['III']  // Area III indicators
            ]);
            
            // 4. ADD ACCREDITATION CYCLES (Timelines)
            $current_year = date('Y');
            $next_year = $current_year + 1;
            
            $stmt = $pdo->prepare("INSERT INTO accreditation_cycles 
                (cycle_name, academic_year, status, deadline) VALUES 
                (?, ?, 'active', ?),
                (?, ?, 'upcoming', ?),
                (?, ?, 'upcoming', ?)
            ");
            
            $stmt->execute([
                // Current active cycle
                "AACCUP Preliminary Survey {$current_year}",
                "{$current_year}-" . ($current_year + 1),
                date('Y-m-d', strtotime('+3 months')),
                
                // Upcoming cycle 1
                "AACCUP Level I Visit {$next_year}", 
                "{$next_year}-" . ($next_year + 1),
                date('Y-m-d', strtotime('+9 months')),
                
                // Upcoming cycle 2  
                "AACCUP Level II Visit " . ($next_year + 1),
                ($next_year + 1) . "-" . ($next_year + 2),
                date('Y-m-d', strtotime('+15 months'))
            ]);
            
            // 5. ADD ONE SAMPLE DEPARTMENT AND PROGRAM (for testing)
            $stmt = $pdo->prepare("INSERT INTO departments (department_code, department_name) VALUES 
                ('CCS', 'College of Computer Studies')
            ");
            $stmt->execute();
            $dept_id = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("INSERT INTO programs (program_code, program_name, department_id) VALUES 
                ('BSIT', 'Bachelor of Science in Information Technology', ?)
            ");
            $stmt->execute([$dept_id]);
            
        }

           
        return $pdo;

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Initialize on load
$pdo = db_connect();