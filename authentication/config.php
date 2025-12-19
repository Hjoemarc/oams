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

        // Step 4: Define table structure - FIXED ORDER
        // First, create basic tables without foreign keys
        $basicTables = [
            // 1. DEPARTMENTS (No dependencies)
            "CREATE TABLE IF NOT EXISTS departments (
                department_id INT AUTO_INCREMENT PRIMARY KEY,
                department_code VARCHAR(20) NOT NULL UNIQUE,
                department_name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB",

            // 2. ACCREDITATION AREAS (No dependencies)
            "CREATE TABLE IF NOT EXISTS accreditation_areas (
                area_id INT AUTO_INCREMENT PRIMARY KEY,
                area_number VARCHAR(10) NOT NULL,
                area_name VARCHAR(255) NOT NULL,
                description TEXT
            ) ENGINE=InnoDB",

            // 3. ACCREDITATION LEVELS (No dependencies) - FIXED ENUM
            "CREATE TABLE IF NOT EXISTS accreditation_levels (
                level_id INT AUTO_INCREMENT PRIMARY KEY,
                level_code VARCHAR(20) NOT NULL UNIQUE,
                level_name VARCHAR(100) NOT NULL,
                description TEXT,
                requirements TEXT,
                next_level_code VARCHAR(20),
                sort_order INT DEFAULT 0
            ) ENGINE=InnoDB",
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
        ) ENGINE=InnoDB");

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
            user_role ENUM('admin', 'qa', 'head', 'department_head', 'faculty', 'accreditor') NOT NULL,
            department_id INT,
            program_id INT,
            is_active ENUM('Active','Inactive','Pending') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(department_id)
                ON DELETE SET NULL,
            FOREIGN KEY (program_id) REFERENCES programs(program_id)
                ON DELETE SET NULL
        ) ENGINE=InnoDB");

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
        ) ENGINE=InnoDB");

        // Create accreditation parameters (depends on accreditation_areas)
        $pdo->exec("CREATE TABLE IF NOT EXISTS accreditation_parameters (
            parameter_id INT AUTO_INCREMENT PRIMARY KEY,
            area_id INT NOT NULL,
            parameter_code VARCHAR(20),
            parameter_name VARCHAR(50),
            FOREIGN KEY (area_id) REFERENCES accreditation_areas(area_id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB");

        // Create accreditation indicators (depends on accreditation_parameters)
        $pdo->exec("CREATE TABLE IF NOT EXISTS accreditation_indicators (
            indicator_id INT AUTO_INCREMENT PRIMARY KEY,
            parameter_id INT,
            parent_indicator_id INT NULL,
            indicator_code VARCHAR(20) NOT NULL,
            indicator_description TEXT NOT NULL,
            indicator_level ENUM('I', 'II', 'III', 'IV', 'III(Phase 2)', 'IV(Phase 2)') DEFAULT 'I',
            sort_order INT DEFAULT 0,
            FOREIGN KEY (parameter_id) REFERENCES accreditation_parameters(parameter_id)
                ON DELETE CASCADE,
            FOREIGN KEY (parent_indicator_id) REFERENCES accreditation_indicators(indicator_id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB");

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
        ) ENGINE=InnoDB");
        $pdo->exec("CREATE TABLE IF NOT EXISTS accreditor_cycles (
            cyclaccreditor_cycles_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            cycle_id INT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
                ON DELETE CASCADE,
            FOREIGN KEY (cycle_id) REFERENCES accreditation_cycles(cycle_id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB");

        // Create program_accreditation table - FIXED: Added accreditation_level column
        $pdo->exec("CREATE TABLE IF NOT EXISTS program_accreditation (
            accreditation_id INT AUTO_INCREMENT PRIMARY KEY,
            program_id INT NOT NULL,
            cycle_id INT,
            survey_date DATE NOT NULL,
            accrediting_body VARCHAR(100) DEFAULT 'AACCUP',
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (program_id) REFERENCES programs(program_id)
                ON DELETE CASCADE,
            FOREIGN KEY (cycle_id) REFERENCES accreditation_cycles(cycle_id)
                ON DELETE SET NULL
        ) ENGINE=InnoDB");

        // Create program_levels table (optional - for tracking current level)
        $pdo->exec("CREATE TABLE IF NOT EXISTS program_levels (
            level_id INT AUTO_INCREMENT PRIMARY KEY,
            program_id INT NOT NULL,
            accreditation_level ENUM('I', 'II', 'III', 'IV', 'III(Phase 2)', 'IV(Phase 2)') DEFAULT 'I',
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (program_id) REFERENCES programs(program_id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB");

        // Now create assignments table with all foreign keys
        $pdo->exec("CREATE TABLE IF NOT EXISTS assignments (
            assignment_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            indicator_id INT NOT NULL,
            program_id INT NOT NULL,
            cycle_id INT,
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
        ) ENGINE=InnoDB");

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
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS document_feedbacks (
            document_feedbacks_id INT AUTO_INCREMENT PRIMARY KEY,
            document_id INT NOT NULL,
            user_id INT NOT NULL,
            feedback TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP 
                DEFAULT CURRENT_TIMESTAMP 
                ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (document_id) REFERENCES documents(document_id)
                ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB");

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
            ) ENGINE=InnoDB",

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
            ) ENGINE=InnoDB",

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
            ) ENGINE=InnoDB",

            // Login history
            "CREATE TABLE IF NOT EXISTS login_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                login_time DATETIME NOT NULL,
                logout_time DATETIME DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(user_id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB",
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
            ('CICS', 'College of Information and Computing Sciences'),
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
            ['BSIT', 'Bachelor of Science in Information Technology', $departments['CICS']],
            ['BSCS', 'Bachelor of Science in Computer Science', $departments['CICS']],
            ['BTLED', 'Bachelor of Technology and Livelihood Education', $departments['CTE']],
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
        
        // 4. ADD ACCREDITATION LEVELS - FIXED to match your ENUM format
        $stmt = $pdo->prepare("INSERT INTO accreditation_levels (level_code, level_name, description, requirements, sort_order) VALUES 
            ('I', 'Level I - Candidate Status', 'Initial accreditation level for new programs', 
            '1. Compliance with minimum requirements\n2. Institutional commitment\n3. Qualified faculty\n4. Basic facilities', 1),
            ('II', 'Level II - Accredited', 'Program meets standards and is accredited',
            '1. Sustained compliance for 3 years\n2. Evidence of program effectiveness\n3. Qualified faculty with relevant degrees\n4. Adequate library and laboratory facilities', 2),
            ('III', 'Level III - Re-accredited', 'Program demonstrates excellence and continuous improvement',
            '1. Excellent performance in all areas\n2. Strong evidence of outcomes\n3. High faculty qualifications\n4. Outstanding facilities and resources', 3),
            ('IV', 'Level IV - Center of Excellence/Development', 'Highest accreditation level recognizing program as benchmark',
            '1. National/International recognition\n2. Outstanding research output\n3. Exceptional faculty profile\n4. State-of-the-art facilities\n5. Significant community impact', 4),
            ('III(Phase 2)', 'Level III (Phase 2)', 'Advanced Level III accreditation',
            '1. Enhanced compliance with standards\n2. Advanced program outcomes\n3. Superior faculty qualifications\n4. Excellent facilities and resources', 5),
            ('IV(Phase 2)', 'Level IV (Phase 2)', 'Advanced Level IV accreditation',
            '1. World-class recognition\n2. Exceptional research output\n3. Distinguished faculty profile\n4. State-of-the-art facilities\n5. Significant national impact', 6)
        ");
        $stmt->execute();
        
        // 5. ADD ACCREDITATION AREAS
        $stmt = $pdo->prepare("INSERT INTO accreditation_areas (area_number, area_name, description) VALUES 
            ('I', 'VISION, MISSION, GOALS AND OBJECTIVES', 'Clear and documented institutional direction and purpose.'),
            ('II', 'FACULTY', 'Qualified, sufficient, and competent teaching personnel.'),
            ('III', 'CURRICULUM AND INSTRUCTION', 'Relevant, updated, and effectively delivered academic programs.'),
            ('IV', 'SUPPORT TO STUDENTS', 'Comprehensive student services and development programs.'),
            ('V', 'RESEARCH', 'Viable research program contributing to knowledge generation.'),
            ('VI', 'EXTENSION AND COMMUNITY INVOLVEMENT', 'Meaningful community engagement and outreach.'),
            ('VII', 'LIBRARY', 'Adequate learning resources and library services.'),
            ('VIII', 'PHYSICAL PLANT AND FACILITIES', 'Safe, adequate, and functional physical facilities.'),
            ('IX', 'Laboratories', 'Well-equipped and maintained laboratory facilities.'),
            ('X', 'ADMINISTRATION', 'Effective and efficient management and governance.')
        ");
        $stmt->execute();

        // Get area IDs
        $area_result = $pdo->query("SELECT area_id, area_number FROM accreditation_areas ORDER BY area_id");
        $areas = [];
        while ($row = $area_result->fetch(PDO::FETCH_ASSOC)) {
            $areas[$row['area_number']] = $row['area_id'];
        }

        // 6. ADD ACCREDITATION PARAMETERS for all areas
        $stmt = $pdo->prepare("INSERT INTO accreditation_parameters (area_id, parameter_code, parameter_name) VALUES 
            -- AREA I
            (?, 'A', 'Statement of Vision, Mission, Goals and Objectives'),
            (?, 'B', 'Dissemination and Acceptability'),
            
            -- AREA II
            (?, 'A', 'Academic Qualifications and Professional Experience'),
            (?, 'B', 'Recruitment, Selection and Orientation'),
            (?, 'C', 'Faculty Adequacy and Loading'),
            (?, 'D', 'Rank and Tenure'),
            (?, 'E', 'Faculty Development'),
            (?, 'F', 'Professional Performance and Scholarly Works'),
            (?, 'G', 'Salaries, Fringe Benefits and Incentives'),
            (?, 'H', 'Professionalism'),
            
            -- AREA III
            (?, 'A', 'Curriculum and Program of Studies'),
            (?, 'B', 'Instructional Processes, Methodologies and Learning Enhancement Opportunities'),
            (?, 'C', 'Assessment and Academic Performance'),
            (?, 'D', 'Management of Learning'),
            (?, 'E', 'Graduation Requirements'),
            (?, 'F', 'Administrative Support for Effective Instruction'),
            
            -- AREA IV
            (?, 'A', 'Student Services Program (SSP)'),
            (?, 'B', 'Student Welfare'),
            (?, 'C', 'Student Development'),
            (?, 'D', 'Institutional Student Programs and Services'),
            (?, 'E', 'Research Monitoring and Evaluation'),
            
            -- AREA V
            (?, 'A', 'Priorities and Relevance'),
            (?, 'B', 'Funding and Other Resources'),
            (?, 'C', 'Implementation, Monitoring, Evaluation and Utilization of Research Results/Outputs'),
            (?, 'D', 'Publication and Dissemination'),
            
            -- AREA VI
            (?, 'A', 'Priorities and Relevance'),
            (?, 'B', 'Planning Implementation, Monitoring and Evaluation'),
            (?, 'C', 'Funding and Other Resources'),
            (?, 'D', 'Community Involvement and Participation'),
            
            -- AREA VII
            (?, 'A', 'Administration'),
            (?, 'B', 'Administrative staff'),
            (?, 'C', 'Collection Development, Organization and Preservation'),
            (?, 'D', 'Services and Utilization'),
            (?, 'E', 'Physical Set-up and facilities'),
            (?, 'F', 'Financial Support'),
            (?, 'G', 'Linkages'),
            
            -- AREA VIII
            (?, 'A', 'Campus'),
            (?, 'B', 'Buildings'),
            (?, 'C', 'Classroom'),
            (?, 'D', 'Offices, Staff and Function Rooms'),
            (?, 'E', 'Assembly, Athletic and Sports Facilities'),
            (?, 'F', 'Medical and Dental Clinic'),
            (?, 'G', 'Student Center'),
            (?, 'H', 'Food Services/Canteen'),
            (?, 'I', 'Accreditation Center'),
            (?, 'J', 'Housing'),
            
            -- AREA IX
            (?, 'A', 'Laboratories, Shops And Facilities'),
            (?, 'B', 'Equipment, Supplies And Materials'),
            (?, 'C', 'Maintenance'),
            (?, 'D', 'Special Provisions'),
            
            -- AREA X
            (?, 'A', 'Organization'),
            (?, 'B', 'Academic Administration'),
            (?, 'C', 'Student Administration'),
            (?, 'D', 'Financial Management'),
            (?, 'E', 'Supply Management'),
            (?, 'F', 'Records Management'),
            (?, 'G', 'Institutional Planning and Development'),
            (?, 'H', 'Performance of Administrative Personnel')
        ");

        // Prepare the parameter values in order
        $param_values = [];
        foreach (['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'] as $area_num) {
            switch($area_num) {
                case 'I': $param_count = 2; break;
                case 'II': $param_count = 8; break;
                case 'III': $param_count = 6; break;
                case 'IV': $param_count = 5; break;
                case 'V': $param_count = 4; break;
                case 'VI': $param_count = 4; break;
                case 'VII': $param_count = 7; break;
                case 'VIII': $param_count = 10; break;
                case 'IX': $param_count = 4; break;
                case 'X': $param_count = 8; break;
            }
            
            for($i = 0; $i < $param_count; $i++) {
                $param_values[] = $areas[$area_num];
            }
        }

        $stmt->execute($param_values);

        // Get all parameter IDs
        $param_result = $pdo->query("
            SELECT p.parameter_id, p.parameter_code, a.area_number 
            FROM accreditation_parameters p 
            JOIN accreditation_areas a ON p.area_id = a.area_id 
            ORDER BY a.area_number, p.parameter_code
        ");

        $parameters_by_area = [];
        $all_parameters = [];
        while ($row = $param_result->fetch(PDO::FETCH_ASSOC)) {
            $parameters_by_area[$row['area_number']][] = $row;
            $all_parameters[] = $row;
        }

        // 7. ADD ACCREDITATION INDICATORS for all parameters
        $indicator_stmt = $pdo->prepare("INSERT INTO accreditation_indicators (parameter_id, indicator_code, indicator_description, indicator_level, sort_order) VALUES (?, ?, ?, '1', ?)");

        // Function to generate indicator descriptions
        function generateIndicatorDescription($area_name, $param_name, $type) {
            $descriptions = [
                1 => "SYSTEM – INPUTS AND PROCESSES for {$param_name} in {$area_name}",
                2 => "IMPLEMENTATION of {$param_name} in {$area_name}",
                3 => "OUTCOME/S of {$param_name} in {$area_name}"
            ];
            return $descriptions[$type];
        }

        // Area names mapping
        $area_names = [
            'I' => 'VISION, MISSION, GOALS AND OBJECTIVES',
            'II' => 'FACULTY',
            'III' => 'CURRICULUM AND INSTRUCTION',
            'IV' => 'SUPPORT TO STUDENTS',
            'V' => 'RESEARCH',
            'VI' => 'EXTENSION AND COMMUNITY INVOLVEMENT',
            'VII' => 'LIBRARY',
            'VIII' => 'PHYSICAL PLANT AND FACILITIES',
            'IX' => 'Laboratories',
            'X' => 'ADMINISTRATION'
        ];

        // Insert indicators for each parameter
        foreach ($all_parameters as $param) {
            $area_num = $param['area_number'];
            $param_code = $param['parameter_code'];
            $area_name = $area_names[$area_num];
            
            // Get parameter name
            $param_name_stmt = $pdo->prepare("SELECT parameter_name FROM accreditation_parameters WHERE parameter_id = ?");
            $param_name_stmt->execute([$param['parameter_id']]);
            $param_name = $param_name_stmt->fetchColumn();
            
            // Insert 3 indicators for each parameter (System-Inputs-Processes, Implementation, Outcomes)
            for ($i = 1; $i <= 3; $i++) {
                $indicator_code = "{$area_num}.{$param_code}.{$i}";
                $description = generateIndicatorDescription($area_name, $param_name, $i);
                
                $indicator_stmt->execute([
                    $param['parameter_id'],
                    $indicator_code,
                    $description,
                    $i
                ]);
            }
        }
        $indicator_stmt_lvl2 = $pdo->prepare("
            INSERT INTO accreditation_indicators
            (parameter_id, indicator_code, indicator_description, indicator_level, sort_order)
            VALUES (?, ?, ?, 'II', ?)
        ");

        // Reuse your function and area names
        foreach ($all_parameters as $param) {
            $area_num = $param['area_number'];

            // Only Areas II to IX
            if (!in_array($area_num, ['II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX'])) {
                continue;
            }

            $param_code = $param['parameter_code'];
            $area_name = $area_names[$area_num];

            // Get parameter name
            $param_name_stmt = $pdo->prepare("
                SELECT parameter_name
                FROM accreditation_parameters
                WHERE parameter_id = ?
            ");
            $param_name_stmt->execute([$param['parameter_id']]);
            $param_name = $param_name_stmt->fetchColumn();

            // Insert 3 indicators (System, Implementation, Outcome)
            for ($i = 1; $i <= 3; $i++) {
                $indicator_code = "{$area_num}.{$param_code}.{$i}";
                $description = generateIndicatorDescription($area_name, $param_name, $i);

                $indicator_stmt_lvl2->execute([
                    $param['parameter_id'],
                    $indicator_code,
                    $description,
                    $i
                ]);
            }
        }


        // 8. ADD SAMPLE ACCREDITATION CYCLES
        $current_year = date('Y');
        $next_year = $current_year + 1;

        $cycle_stmt = $pdo->prepare("INSERT INTO accreditation_cycles 
            (cycle_name, academic_year, status, deadline, program_id, visit_start, visit_end) VALUES 
            (?, ?, 'active', ?, ?, ?, ?),
            (?, ?, 'upcoming', ?, ?, ?, ?),
            (?, ?, 'upcoming', ?, ?, ?, ?)
        ");

        $cycle_stmt->execute([
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

        // 9. ADD SAMPLE ACCREDITATION RECORDS - FIXED: Removed accreditation_level from program_accreditation
        $accreditation_stmt = $pdo->prepare("INSERT INTO program_accreditation 
            (program_id, cycle_id, survey_date, accrediting_body, remarks) VALUES 
            (?, ?, ?, 'AACCUP', 'Preliminary survey for BSIT program'),
            (?, ?, ?, 'AACCUP', 'Achieved Level I accreditation')
        ");

        $accreditation_stmt->execute([
            $sampleProgramId,
            $sampleCycleId,
            date('Y-m-d', strtotime('-6 months')),
            $sampleProgramId,
            $sampleCycleId,
            date('Y-m-d', strtotime('-3 months'))
        ]);

        // 9b. ADD SAMPLE PROGRAM LEVELS (since program_accreditation doesn't have accreditation_level)
        $program_levels_stmt = $pdo->prepare("INSERT INTO program_levels 
            (program_id, accreditation_level) VALUES 
            (?, 'I'),
            (?, 'II'),
            (?, 'III'),
            (?, 'I')
        ");

        $program_levels_stmt->execute([
            $sampleProgramId, // BSIT - Level I
            2, // Assuming program_id 2 exists - Level II
            3, // Assuming program_id 3 exists - Level III
            4  // Assuming program_id 4 exists - Level I
        ]);

        // 10. ADD SAMPLE ASSIGNMENTS
        // Get some indicator IDs
        $indicatorStmt = $pdo->query("SELECT indicator_id FROM accreditation_indicators LIMIT 2");
        $indicators = $indicatorStmt->fetchAll(PDO::FETCH_COLUMN);

                
            } catch (PDOException $e) {
                die("Error inserting sample data: " . $e->getMessage());
            }
        }

// Initialize on load
$pdo = db_connect();
?>