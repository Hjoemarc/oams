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
            "CREATE TABLE IF NOT EXISTS departments(
                Department_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                Department_name VARCHAR(50) NOT NULL,
                Department_code VARCHAR(50) NOT NULL,
                addAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS programs (
                program_id INT AUTO_INCREMENT PRIMARY KEY,
                program_code VARCHAR(20) NOT NULL,
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
                gender VARCHAR(5),
                role_id INT NOT NULL,
                program_id INT,
                is_active ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Active',
                user_role ENUM('admin', 'QA', 'head', 'faculty') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (program_id) REFERENCES programs(program_id)
            )",
            //  ACCREDITATION FRAMEWORK TABLES =======================================
            "CREATE TABLE IF NOT EXISTS accreditation_areas (
                area_id INT AUTO_INCREMENT PRIMARY KEY,
                area_number INT NOT NULL,
                area_name VARCHAR(255) NOT NULL,
                description TEXT
            )",

            "CREATE TABLE IF NOT EXISTS accreditation_indicators (
                indicator_id INT AUTO_INCREMENT PRIMARY KEY,
                area_id INT NOT NULL,
                indicator_code VARCHAR(20) NOT NULL,
                indicator_description TEXT NOT NULL,
                required_evidence TEXT,
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
            // CORE DOCUMENT & WORKFLOW TABLES =======================================
            "CREATE TABLE IF NOT EXISTS assignments (
                assignment_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                indicator_id INT NOT NULL,
                program_id INT NOT NULL,
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
                uploaded_by INT NOT NULL,
                indicator_id INT NOT NULL,
                program_id INT NOT NULL,
                cycle_id INT NOT NULL,
                version INT DEFAULT 1,
                status ENUM('draft', 'submitted', 'under_review', 'needs_revision', 'approved') DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (uploaded_by) REFERENCES users(user_id),
                FOREIGN KEY (indicator_id) REFERENCES accreditation_indicators(indicator_id),
                FOREIGN KEY (program_id) REFERENCES programs(program_id),
                FOREIGN KEY (cycle_id) REFERENCES accreditation_cycles(cycle_id)
            )",

            "CREATE TABLE IF NOT EXISTS document_versions (
                version_id INT AUTO_INCREMENT PRIMARY KEY,
                document_id INT NOT NULL,
                version INT NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                uploaded_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (document_id) REFERENCES documents(document_id),
                FOREIGN KEY (uploaded_by) REFERENCES users(user_id)
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

            // NOTIFICATION & LOGGING TABLES =======================================
            "CREATE TABLE IF NOT EXISTS notifications (
                notification_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                related_document_id INT,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id),
                FOREIGN KEY (related_document_id) REFERENCES documents(document_id)
            )",

            "CREATE TABLE IF NOT EXISTS activity_logs (
                log_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                action VARCHAR(100) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id)
            )"
        ];

        foreach ($tableQueries as $sql) {
            $pdo->exec($sql);
        }
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count == 0) {
            $stmt = $pdo->prepare("INSERT INTO users (
                username,
                email,
                password,
                first_name,
                last_name
            ) VALUES (?, ?, ?, ?, ?)");

            $stmt->execute([
                'admin',                    
                'admin@email.com',                     
                password_hash('12345', PASSWORD_BCRYPT),             
                'Juan',                         
                'Dela Torre'
            ]);
        }

           
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            $csrf_token = $_SESSION['csrf_token'];

        return $pdo;

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Initialize on load
$pdo = db_connect();