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

        // Step 4: Define table structure - SIMPLIFIED VERSION
        // First, create basic tables without foreign keys
        $basicTables = [
            // 1. DEPARTMENTS (No dependencies)
            "CREATE TABLE IF NOT EXISTS departments (
                department_id INT AUTO_INCREMENT PRIMARY KEY,
                department_code VARCHAR(20) NOT NULL UNIQUE,
                department_name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",

            // 2. ACCREDITATION AREAS (No dependencies)
            "CREATE TABLE IF NOT EXISTS accreditation_areas (
                area_id INT AUTO_INCREMENT PRIMARY KEY,
                area_number VARCHAR(10) NOT NULL,
                area_name VARCHAR(255) NOT NULL,
                description TEXT
            )",

            // 3. ACCREDITATION LEVELS (No dependencies)
            "CREATE TABLE IF NOT EXISTS accreditation_levels (
                level_id INT AUTO_INCREMENT PRIMARY KEY,
                level_code VARCHAR(20) NOT NULL UNIQUE,
                level_name VARCHAR(100) NOT NULL,
                description TEXT,
                requirements TEXT,
                next_level_code VARCHAR(20),
                sort_order INT DEFAULT 0
            )",
        ];

        foreach ($basicTables as $sql) {
            $pdo->exec($sql);
        }

        // Create programs table (depends on departments)
        $pdo->exec("CREATE TABLE IF NOT EXISTS programs (
            program_id INT AUTO_INCREMENT PRIMARY KEY,
            program_code VARCHAR(20) NOT NULL UNIQUE,
            program_name VARCHAR(255) NOT NULL,
            department_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(department_id)
                ON DELETE CASCADE
        )");

        // Create users table (depends on departments and programs)
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
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
            program_id INT,
            is_active ENUM('Active','Inactive','Pending') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(department_id)
                ON DELETE SET NULL,
            FOREIGN KEY (program_id) REFERENCES programs(program_id)
                ON DELETE SET NULL
        )");

        // Create user info table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users_info (
            users_info_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            citizenship VARCHAR(50),
            civil_status VARCHAR(50),
            birthday DATE,
            religion VARCHAR(50),
            birth_place VARCHAR(50),
            contact VARCHAR(50),
            block VARCHAR(50),
            street VARCHAR(50),
            subdivision VARCHAR(50),
            barangay VARCHAR(50),
            city VARCHAR(50),
            province VARCHAR(50),
            zip_code VARCHAR(5),
            FOREIGN KEY (user_id) REFERENCES users(user_id)
                ON DELETE CASCADE
        )");

        // Create accreditation indicators (depends on accreditation_areas)
        $pdo->exec("CREATE TABLE IF NOT EXISTS accreditation_indicators (
            indicator_id INT AUTO_INCREMENT PRIMARY KEY,
            area_id INT NOT NULL,
            indicator_code VARCHAR(20) NOT NULL,
            indicator_description TEXT NOT NULL,
            FOREIGN KEY (area_id) REFERENCES accreditation_areas(area_id)
                ON DELETE CASCADE
        )");

        // Create accreditation cycles (depends on programs)
        $pdo->exec("CREATE TABLE IF NOT EXISTS accreditation_cycles (
            cycle_id INT AUTO_INCREMENT PRIMARY KEY,
            cycle_name VARCHAR(100) NOT NULL,
            academic_year VARCHAR(9) NOT NULL,
            program_id INT NOT NULL,
            status ENUM('upcoming','active','closed') DEFAULT 'upcoming',
            deadline DATE,
            visit_start DATE,
            visit_end DATE,
            accrediting_body VARCHAR(100) DEFAULT 'AACCUP',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (program_id) REFERENCES programs(program_id)
                ON DELETE CASCADE
        )");

        // Create program accreditation history
        $pdo->exec("CREATE TABLE IF NOT EXISTS program_accreditation (
            accreditation_id INT AUTO_INCREMENT PRIMARY KEY,
            program_id INT NOT NULL,
            cycle_id INT,
            accreditation_level VARCHAR(50) NOT NULL,
            survey_date DATE NOT NULL,
            accrediting_body VARCHAR(100) DEFAULT 'AACCUP',
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (program_id) REFERENCES programs(program_id)
                ON DELETE CASCADE,
            FOREIGN KEY (cycle_id) REFERENCES accreditation_cycles(cycle_id)
                ON DELETE SET NULL
        )");

        // Now create assignments table with all foreign keys
        $pdo->exec("CREATE TABLE IF NOT EXISTS assignments (
            assignment_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            indicator_id INT NOT NULL,
            program_id INT NOT NULL,
            cycle_id INT NOT NULL,
            status ENUM('not_started','in_progress','submitted','completed') 
                DEFAULT 'not_started',
            assigned_by INT NULL,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
                ON DELETE CASCADE,
            FOREIGN KEY (indicator_id) REFERENCES accreditation_indicators(indicator_id)
                ON DELETE CASCADE,
            FOREIGN KEY (program_id) REFERENCES programs(program_id)
                ON DELETE CASCADE,
            FOREIGN KEY (cycle_id) REFERENCES accreditation_cycles(cycle_id)
                ON DELETE CASCADE,
            FOREIGN KEY (assigned_by) REFERENCES users(user_id)
                ON DELETE SET NULL
        )");
        // Create documents table
        $pdo->exec("CREATE TABLE IF NOT EXISTS documents (
            document_id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size INT NOT NULL,
            assignment_id INT NOT NULL,
            uploaded_by INT NULL,
            indicator_id INT NOT NULL,
            program_id INT NOT NULL,
            cycle_id INT NOT NULL,
            description TEXT,
            document_type VARCHAR(50) DEFAULT 'other',
            is_archive TINYINT(1) DEFAULT 0,
            original_filename VARCHAR(255),
            status ENUM('draft','submitted','under_review','needs_revision','approved')
                DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP 
                DEFAULT CURRENT_TIMESTAMP 
                ON UPDATE CURRENT_TIMESTAMP,

            FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id)
                ON DELETE CASCADE,

            FOREIGN KEY (uploaded_by) REFERENCES users(user_id)
                ON DELETE SET NULL,

            FOREIGN KEY (indicator_id) REFERENCES accreditation_indicators(indicator_id)
                ON DELETE CASCADE,

            FOREIGN KEY (program_id) REFERENCES programs(program_id)
                ON DELETE CASCADE,

            FOREIGN KEY (cycle_id) REFERENCES accreditation_cycles(cycle_id)
                ON DELETE CASCADE
        )");


        // Create remaining tables
        $remainingTables = [
            // Reviews table
            "CREATE TABLE IF NOT EXISTS reviews (
                review_id INT AUTO_INCREMENT PRIMARY KEY,
                document_id INT NOT NULL,
                reviewer_id INT NULL,
                review_action ENUM('approved','needs_revision') NOT NULL,
                comments TEXT,
                deadline DATE,
                revision_reason TEXT,
                priority ENUM('normal','high','urgent') DEFAULT 'normal',
                reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (document_id) REFERENCES documents(document_id)
                    ON DELETE CASCADE,
                FOREIGN KEY (reviewer_id) REFERENCES users(user_id)
                    ON DELETE SET NULL
            )",


            // Compliance tracking
            "CREATE TABLE IF NOT EXISTS compliance_tracking (
                compliance_id INT AUTO_INCREMENT PRIMARY KEY,
                program_id INT NOT NULL,
                cycle_id INT NOT NULL,
                indicator_id INT NOT NULL,
                status ENUM('compliant','non_compliant','partial','not_assessed') 
                    DEFAULT 'not_assessed',
                compliance_score DECIMAL(5,2),
                reviewer_id INT,
                review_date DATE,
                comments TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (program_id) REFERENCES programs(program_id)
                    ON DELETE CASCADE,
                FOREIGN KEY (cycle_id) REFERENCES accreditation_cycles(cycle_id)
                    ON DELETE CASCADE,
                FOREIGN KEY (indicator_id) REFERENCES accreditation_indicators(indicator_id)
                    ON DELETE CASCADE,
                FOREIGN KEY (reviewer_id) REFERENCES users(user_id)
                    ON DELETE SET NULL
            )",

            // Notifications
            "CREATE TABLE IF NOT EXISTS notifications (
                notification_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id)
                    ON DELETE CASCADE
            )",

            // Login history
            "CREATE TABLE IF NOT EXISTS login_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                login_time DATETIME NOT NULL,
                logout_time DATETIME DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(user_id)
                    ON DELETE CASCADE
            )",
        ];

        foreach ($remainingTables as $sql) {
            $pdo->exec($sql);
        }

        // Step 5: Check if database is empty and insert sample data
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count == 0) {
            // Insert sample data
            insertSampleData($pdo);
        }

        return $pdo;

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine());
    }
}

function insertSampleData($pdo) {
    try {
        // 1. ADD DEPARTMENTS
        $stmt = $pdo->prepare("INSERT INTO departments (department_code, department_name) VALUES 
            ('CCS', 'College of Computer Studies'),
            ('COE', 'College of Engineering'),
            ('CBA', 'College of Business Administration'),
            ('CAS', 'College of Arts and Sciences'),
            ('CTE', 'College of Teacher Education')
        ");
        $stmt->execute();
        
        // Get all department IDs
        $deptStmt = $pdo->query("SELECT department_id, department_code FROM departments");
        $departments = [];
        while ($row = $deptStmt->fetch(PDO::FETCH_ASSOC)) {
            $departments[$row['department_code']] = $row['department_id'];
        }
        
        // 2. ADD PROGRAMS
        $programsData = [
            ['BSIT', 'Bachelor of Science in Information Technology', $departments['CCS']],
            ['BSCS', 'Bachelor of Science in Computer Science', $departments['CCS']],
            ['BSCpE', 'Bachelor of Science in Computer Engineering', $departments['COE']],
            ['BSEE', 'Bachelor of Science in Electrical Engineering', $departments['COE']],
            ['BSBA', 'Bachelor of Science in Business Administration', $departments['CBA']],
            ['BSPsy', 'Bachelor of Science in Psychology', $departments['CAS']],
            ['BSEd', 'Bachelor of Secondary Education', $departments['CTE']],
            ['BEEd', 'Bachelor of Elementary Education', $departments['CTE']]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO programs (program_code, program_name, department_id) VALUES (?, ?, ?)");
        foreach ($programsData as $program) {
            $stmt->execute($program);
        }
        
        // Get a program ID for sample cycles
        $programStmt = $pdo->query("SELECT program_id FROM programs WHERE program_code = 'BSIT'");
        $sampleProgramId = $programStmt->fetchColumn();
        
        // 3. ADD DEFAULT ADMIN USER
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
        
        // Get admin user ID
        $adminStmt = $pdo->query("SELECT user_id FROM users WHERE username = 'admin'");
        $adminId = $adminStmt->fetchColumn();
        
        // 4. ADD ACCREDITATION LEVELS
        $stmt = $pdo->prepare("INSERT INTO accreditation_levels (level_code, level_name, description, requirements, sort_order) VALUES 
            ('1', 'Level I - Candidate Status', 'Initial accreditation level for new programs', 
             '1. Compliance with minimum requirements\n2. Institutional commitment\n3. Qualified faculty\n4. Basic facilities', 1),
            ('2', 'Level II - Accredited', 'Program meets standards and is accredited',
             '1. Sustained compliance for 3 years\n2. Evidence of program effectiveness\n3. Qualified faculty with relevant degrees\n4. Adequate library and laboratory facilities', 2),
            ('3', 'Level III - Re-accredited', 'Program demonstrates excellence and continuous improvement',
             '1. Excellent performance in all areas\n2. Strong evidence of outcomes\n3. High faculty qualifications\n4. Outstanding facilities and resources', 3),
            ('4', 'Level IV - Center of Excellence/Development', 'Highest accreditation level recognizing program as benchmark',
             '1. National/International recognition\n2. Outstanding research output\n3. Exceptional faculty profile\n4. State-of-the-art facilities\n5. Significant community impact', 4),
            ('phase1', 'Phase 1 - Preliminary Survey', 'Initial assessment phase',
             '1. Document review\n2. Preliminary evaluation\n3. Compliance with basic standards', 0),
            ('phase2', 'Phase 2 - Formal Survey', 'Comprehensive evaluation phase',
             '1. On-site visit\n2. Full document review\n3. Stakeholder interviews\n4. Facilities inspection', 0.5)
        ");
        $stmt->execute();
        
        // 5. ADD ACCREDITATION AREAS
        $stmt = $pdo->prepare("INSERT INTO accreditation_areas (area_number, area_name, description) VALUES 
            ('I', 'Institutional Mission, Vision, and Goals', 'Clear and documented institutional direction and purpose.'),
            ('II', 'Faculty', 'Qualified, sufficient, and competent teaching personnel.'),
            ('III', 'Curriculum and Instruction', 'Relevant, updated, and effectively delivered academic programs.'),
            ('IV', 'Support to Students', 'Comprehensive student services and development programs.'),
            ('V', 'Research', 'Viable research program contributing to knowledge generation.'),
            ('VI', 'Extension and Community Involvement', 'Meaningful community engagement and outreach.'),
            ('VII', 'Library', 'Adequate learning resources and library services.'),
            ('VIII', 'Physical Plant and Facilities', 'Safe, adequate, and functional physical facilities.'),
            ('IX', 'Laboratories', 'Well-equipped and maintained laboratory facilities.'),
            ('X', 'Administration', 'Effective and efficient management and governance.')
        ");
        $stmt->execute();
        
        // Get area IDs
        $area_result = $pdo->query("SELECT area_id, area_number FROM accreditation_areas ORDER BY area_id");
        $areas = [];
        while ($row = $area_result->fetch(PDO::FETCH_ASSOC)) {
            $areas[$row['area_number']] = $row['area_id'];
        }
        
        // 6. ADD ACCREDITATION INDICATORS
        $stmt = $pdo->prepare("INSERT INTO accreditation_indicators (area_id, indicator_code, indicator_description) VALUES 
            (?, 'I.1', 'The institution has clear and documented Vision, Mission, and Goals (VMGO).'),
            (?, 'I.2', 'The VMGO are formulated and approved through participatory process.'),
            (?, 'I.3', 'The VMGO are disseminated to stakeholders.'),
            (?, 'I.4', 'Institutional activities are aligned with VMGO.'),
            
            (?, 'II.1', 'Faculty members possess appropriate academic qualifications.'),
            (?, 'II.2', 'There is a sufficient number of full-time faculty.'),
            (?, 'II.3', 'Faculty development programs are implemented.'),
            (?, 'II.4', 'Faculty performance is evaluated regularly.'),
            
            (?, 'III.1', 'The curriculum is aligned with program objectives.'),
            (?, 'III.2', 'Curriculum is reviewed and updated regularly.'),
            (?, 'III.3', 'Instructional materials are current and relevant.'),
            (?, 'III.4', 'Varied teaching strategies are employed.')
        ");
        
        $stmt->execute([
            $areas['I'], $areas['I'], $areas['I'], $areas['I'],
            $areas['II'], $areas['II'], $areas['II'], $areas['II'],
            $areas['III'], $areas['III'], $areas['III'], $areas['III']
        ]);
        
        // 7. ADD ACCREDITATION CYCLES
        $current_year = date('Y');
        $next_year = $current_year + 1;
        
        $stmt = $pdo->prepare("INSERT INTO accreditation_cycles 
            (cycle_name, academic_year, status, deadline, program_id, visit_start, visit_end) VALUES 
            (?, ?, 'active', ?, ?, ?, ?),
            (?, ?, 'upcoming', ?, ?, ?, ?),
            (?, ?, 'upcoming', ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            "AACCUP Preliminary Survey {$current_year}",
            "{$current_year}-" . ($current_year + 1),
            date('Y-m-d', strtotime('+3 months')),
            $sampleProgramId,
            date('Y-m-d', strtotime('+2 months')),
            date('Y-m-d', strtotime('+2 months +5 days')),
            
            "AACCUP Level I Visit {$next_year}", 
            "{$next_year}-" . ($next_year + 1),
            date('Y-m-d', strtotime('+9 months')),
            $sampleProgramId,
            date('Y-m-d', strtotime('+8 months')),
            date('Y-m-d', strtotime('+8 months +5 days')),
            
            "AACCUP Level II Visit " . ($next_year + 1),
            ($next_year + 1) . "-" . ($next_year + 2),
            date('Y-m-d', strtotime('+15 months')),
            $sampleProgramId,
            date('Y-m-d', strtotime('+14 months')),
            date('Y-m-d', strtotime('+14 months +5 days'))
        ]);
        
        // Get cycle ID
        $cycleStmt = $pdo->query("SELECT cycle_id FROM accreditation_cycles ORDER BY cycle_id LIMIT 1");
        $sampleCycleId = $cycleStmt->fetchColumn();
        
        // 8. ADD SAMPLE ACCREDITATION RECORDS
        $stmt = $pdo->prepare("INSERT INTO program_accreditation 
            (program_id, cycle_id, accreditation_level, survey_date, accrediting_body, remarks) VALUES 
            (?, ?, 'phase1', ?, 'AACCUP', 'Preliminary survey for BSIT program'),
            (?, ?, '1', ?, 'AACCUP', 'Achieved Level I accreditation')
        ");
        
        $stmt->execute([
            $sampleProgramId,
            $sampleCycleId,
            date('Y-m-d', strtotime('-6 months')),
            $sampleProgramId,
            $sampleCycleId,
            date('Y-m-d', strtotime('-3 months'))
        ]);
        
        // 9. ADD SAMPLE ASSIGNMENTS
        $indicatorStmt = $pdo->query("SELECT indicator_id FROM accreditation_indicators LIMIT 2");
        $indicators = $indicatorStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($indicators)) {
            $stmt = $pdo->prepare("INSERT INTO assignments 
                (user_id, indicator_id, program_id, cycle_id, assigned_by, status) VALUES 
                (?, ?, ?, ?, ?, 'in_progress'),
                (?, ?, ?, ?, ?, 'not_started')
            ");
            
            $stmt->execute([
                $adminId, $indicators[0], $sampleProgramId, $sampleCycleId, $adminId,
                $adminId, $indicators[1], $sampleProgramId, $sampleCycleId, $adminId
            ]);
        }
        
    } catch (PDOException $e) {
        die("Error inserting sample data: " . $e->getMessage());
    }
}

// Initialize on load
$pdo = db_connect();