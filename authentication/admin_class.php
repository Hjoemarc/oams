<?php
session_start();
require_once __DIR__ . '/../assets/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Action
{
    private $db;

    public function __construct()
    {

        include 'config.php';

        if (!isset($pdo)) {
            die("Database not connected.");
        }
        $this->db = $pdo;
    }

    function __destruct()
    {
        $this->db = null;
    }

    // ===============================================================================================
    // login without verification code !!!!!!!!!! Uncomment to use!!!!!
    function login()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $_SESSION["username"] = $username;
        $_SESSION["password"] = $password;

        if (empty($username) || empty($password)) {
            return json_encode(['status' => 0, 'message' => 'Username and password are required.']);
        }

        try {
            // ==== Employee Login ====
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $employees = $stmt->fetch();



            if ($employees['user_role'] === 'admin' && $employees["is_active"] === 'Active') {

                if (password_verify($password, $employees['password'])) {
                    $_SESSION['adminData'] = [
                        'first_name' => $employees['first_name'],
                        'last_name' => $employees['last_name'],
                        'user_role' => $employees['user_role'],
                        'user_id' => $employees['user_id']
                    ];
                    $admin_id = $employees['user_id'];
                    $stmtHistory = $this->db->prepare("INSERT INTO login_history (user_id, login_time) VALUES ('$admin_id', NOW())");
                    $stmtHistory->execute();
                    return json_encode([
                        'status' => 1,
                        'message' => 'Login successful.',
                        'redirect_url' => 'src/UI-Admin/index.php',
                        'user_role' => $employees['first_name'] . " " . $employees['last_name']
                    ]);
                } else {
                    return json_encode(['status' => 0, 'message' => 'Incorrect password.']);
                }
            }else if($employees['user_role'] === 'qa' && $employees["is_active"] === 'Active'){
                if (password_verify($password, $employees['password'])) {
                    $_SESSION['QAData'] = [
                        'first_name' => $employees['first_name'],
                        'last_name' => $employees['last_name'],
                        'user_role' => $employees['user_role'],
                        'user_id' => $employees['user_id']
                    ];
                    $user_id = $employees['user_id'];
                    $stmtHistory = $this->db->prepare("INSERT INTO login_history (user_id, login_time) VALUES ('$user_id', NOW())");
                    $stmtHistory->execute();
                    return json_encode([
                        'status' => 1,
                        'message' => 'Login successful.',
                        'redirect_url' => 'src/UI-QA/index.php',
                        'user_role' => $employees['first_name'] . " " . $employees['last_name']
                    ]);
                } else {
                    return json_encode(['status' => 0, 'message' => 'Incorrect password.']);
                }
            }else if($employees['user_role'] === 'accreditor' && $employees["is_active"] === 'Active'){
                if (password_verify($password, $employees['password'])) {
                    $_SESSION['accreditorData'] = [
                        'first_name' => $employees['first_name'],
                        'last_name' => $employees['last_name'],
                        'user_role' => $employees['user_role'],
                        'user_id' => $employees['user_id']
                    ];
                    $user_id = $employees['user_id'];
                    $stmtHistory = $this->db->prepare("INSERT INTO login_history (user_id, login_time) VALUES ('$user_id', NOW())");
                    $stmtHistory->execute();
                    return json_encode([
                        'status' => 1,
                        'message' => 'Login successful.',
                        'redirect_url' => 'src/UI-Accreditor/index.php',
                        'user_role' => $employees['first_name'] . " " . $employees['last_name']
                    ]);
                } else {
                    return json_encode(['status' => 0, 'message' => 'Incorrect password.']);
                }
            }else if($employees['user_role'] === 'head' && $employees["is_active"] === 'Active'){
                if (password_verify($password, $employees['password'])) {
                    $_SESSION['headData'] = [
                        'first_name' => $employees['first_name'],
                        'last_name' => $employees['last_name'],
                        'user_role' => $employees['user_role'],
                        'user_id' => $employees['user_id']
                    ];
                    $user_id = $employees['user_id'];
                    $stmtHistory = $this->db->prepare("INSERT INTO login_history (user_id, login_time) VALUES ('$user_id', NOW())");
                    $stmtHistory->execute();
                    return json_encode([
                        'status' => 1,
                        'message' => 'Login successful.',
                        'redirect_url' => 'src/UI-Head/index.php',
                        'user_role' => $employees['first_name'] . " " . $employees['last_name']
                    ]);
                } else {
                    return json_encode(['status' => 0, 'message' => 'Incorrect password.']);
                }
            }

            if ($employees['user_role'] === 'faculty' && $employees["is_active"] === 'Active') {

                if (password_verify($password, $employees['password'])) {
                    $_SESSION['facultyData'] = [
                         'first_name' => $employees['first_name'],
                        'last_name' => $employees['last_name'],
                        'user_role' => $employees['user_role'],
                        'user_id' => $employees['user_id']
                    ];
                    $user_id = $employees['user_id'];
                    $stmtHistory = $this->db->prepare("INSERT INTO login_history (user_id, login_time) VALUES ('$user_id', NOW())");
                    $stmtHistory->execute();
                    return json_encode([
                        'status' => 1,
                        'message' => 'Login successful.',
                        'redirect_url' => 'src/UI-Faculty/index.php',
                        'user_role' => $employees['first_name'] . " " . $employees['last_name']
                    ]);
                } else {
                    return json_encode(['status' => 0, 'message' => 'Incorrect password.']);
                }
            }else if ($employees["is_active"] === 'Inactive') {

                if (password_verify($password, $employees['password'])) {
                    $_SESSION['inactiveData'] = [
                        'first_name' => $employees['first_name'],
                        'last_name' => $employees['last_name'],
                        'user_role' => $employees['user_role'],
                        'user_id' => $employees['user_id']
                    ];
                    return json_encode([
                        'status' => 1,
                        'message' => 'Login successful.',
                        'redirect_url' => 'src/inactive.php',
                        'user_role' => $employees['first_name'] . " " . $employees['last_name']
                    ]);
                } else {
                    return json_encode(['status' => 0, 'message' => 'Incorrect password.']);
                }
            }else if ($employees["is_active"] === 'Pending') {

                if (password_verify($password, $employees['password'])) {
                    $_SESSION['pendingData'] = [
                        'first_name' => $employees['first_name'],
                        'last_name' => $employees['last_name'],
                        'user_role' => $employees['user_role'],
                        'user_id' => $employees['user_id']
                    ];
                    return json_encode([
                        'status' => 1,
                        'message' => 'Login successful.',
                        'redirect_url' => 'src/pending.php',
                        'user_role' => $employees['first_name'] . " " . $employees['last_name']
                    ]);
                } else {
                    return json_encode(['status' => 0, 'message' => 'Incorrect password.']);
                }
            }

            return json_encode([
                'status' => 0,
                'message' => 'User not found. Please check your username/email.'
            ]);
        } catch (Exception $e) {
            return json_encode(['status' => 0, 'message' => 'Database error. Please try again later.']);
        }
    }
    // ===============================================================================================

    function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // ✅ Get employee ID before destroying the session
        $user_id = null;

        if (!empty($_SESSION['QAData']['user_id'])) {
            $user_id = $_SESSION['QAData']['user_id'];
        } elseif (!empty($_SESSION['headData']['user_id'])) {
            $user_id = $_SESSION['headData']['user_id'];
        } elseif (!empty($_SESSION['facultyData']['user_id'])) {
            $user_id = $_SESSION['facultyData']['user_id'];
        } elseif (!empty($_SESSION['adminData']['user_id'])) {
            $user_id = $_SESSION['adminData']['user_id'];
        }

        // ✅ Update logout_time only if we got an employee_id
        if ($user_id !== null) {
            $sql = "UPDATE login_history 
                    SET logout_time = NOW() 
                    WHERE user_id = :user_id 
                    AND logout_time IS NULL 
                    ORDER BY login_time DESC 
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
        }
        // ✅ Now clear and destroy the session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_unset();
        session_destroy();

        return json_encode([
            'status' => 1,
            'message' => 'Logout successful.',
            'redirect_url' => './src/index.php'
        ]);
    }

    private function getComplianceId($program_id, $cycle_id, $indicator_id) {
        // Check if already exists
        $checkStmt = $this->db->prepare("
            SELECT compliance_id FROM compliance_tracking 
            WHERE program_id = :program_id 
            AND cycle_id = :cycle_id 
            AND indicator_id = :indicator_id
        ");
        $checkStmt->execute([
            ':program_id' => $program_id,
            ':cycle_id' => $cycle_id,
            ':indicator_id' => $indicator_id
        ]);
        
        if ($checkStmt->rowCount() > 0) {
            // Already exists, return the existing ID
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            return $row['compliance_id'];
        } else {
            // Insert new record
            $stmt = $this->db->prepare("
                INSERT INTO compliance_tracking 
                (program_id, cycle_id, indicator_id, status, created_at)
                VALUES (:program_id, :cycle_id, :indicator_id, 'not_assessed', NOW())
            ");
            
            $stmt->execute([
                ':program_id' => $program_id,
                ':cycle_id' => $cycle_id,
                ':indicator_id' => $indicator_id
            ]);
            
            // Return the new ID
            return $this->db->lastInsertId();
        }
    }
    // Helper method for fallback if getComplianceId doesn't exist
    private function createOrGetComplianceId($program_id, $cycle_id, $indicator_id) {
        // Check if already exists
        $checkStmt = $this->db->prepare("
            SELECT compliance_id FROM compliance_tracking 
            WHERE program_id = :program_id 
            AND cycle_id = :cycle_id 
            AND indicator_id = :indicator_id
        ");
        $checkStmt->execute([
            ':program_id' => $program_id,
            ':cycle_id' => $cycle_id,
            ':indicator_id' => $indicator_id
        ]);
        
        if ($checkStmt->rowCount() > 0) {
            // Already exists, return the existing ID
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            return $row['compliance_id'];
        } else {
            // Insert new record
            $stmt = $this->db->prepare("
                INSERT INTO compliance_tracking 
                (program_id, cycle_id, indicator_id, status, created_at)
                VALUES (:program_id, :cycle_id, :indicator_id, 'not_assessed', NOW())
            ");
            
            $stmt->execute([
                ':program_id' => $program_id,
                ':cycle_id' => $cycle_id,
                ':indicator_id' => $indicator_id
            ]);
            
            // Return the new ID
            return $this->db->lastInsertId();
        }
    }
    

   
    
    // ---------- Forget Account Verifications and change  password code
    function changePassword_form(){
        try {
            $current_password = $_POST["current_password"] ?? '';
            $new_pass = $_POST["new_pass"] ?? '';
            $confirm_pass = $_POST["confirm_pass"] ?? '';
            
            // Validate match
            if ($new_pass !== $confirm_pass) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Password not match. Please try again.'
                ]);
            }

            // Determine employee id
            $employee_id = $_SESSION["employee_id"] ?? $_POST["employee_id"] ?? null;

            if ($employee_id === null) {
                $admin_id = $_POST["admin_id"];
                $stmt = $this->db->prepare("SELECT admin_password FROM admin WHERE admin_id = :admin_id");
                $stmt->execute(['admin_id' => $admin_id]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$admin) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Employee does not exist.'
                    ]);
                }
                // Verify old password
                if (!password_verify($current_password, $admin["admin_password"])) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Current password incorrect.'
                    ]);
                }

                // Create new hash
                $hash = password_hash($new_pass, PASSWORD_BCRYPT);

                // FIXED: Added WHERE clause
                $stmt = $this->db->prepare("UPDATE admin SET admin_password = :admin_password WHERE admin_id = :admin_id");
                $stmt->execute([
                    'admin_password' => $hash,
                    'admin_id' => $admin_id
                ]);
            }else if($employee_id !== null){
                $stmt = $this->db->prepare("SELECT password FROM employee_data WHERE employee_id = :employee_id");
                $stmt->execute(['employee_id' => $employee_id]);
                $employee = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$employee) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Employee does not exist.'
                    ]);
                }

                // Verify old password
                if (!password_verify($current_password, $employee["password"])) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Current password incorrect.'
                    ]);
                }

                // Create new hash
                $hash = password_hash($new_pass, PASSWORD_BCRYPT);

                // FIXED: Added WHERE clause
                $stmt = $this->db->prepare("UPDATE employee_data SET password = :password WHERE employee_id = :employee_id");
                $stmt->execute([
                    'password' => $hash,
                    'employee_id' => $employee_id
                ]);
            }
            return json_encode([
                'status' => 1,
                'message' => 'Password changed successfully!'
            ]);

        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function forget_account_form()
    {
        try {
            $username = $_POST['username'] ?? '';
            
            // Use proper prepared statements
            $stmt = $this->db->prepare("SELECT username, email, firstname, lastname, employee_id FROM employee_data WHERE username = :username OR email = :username");
            $stmt->execute([':username' => $username]);
            $userExists = $stmt->fetch(PDO::FETCH_ASSOC);

            if($userExists){
                $verification = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                // Use correct array keys (lowercase as in SQL query)
                $email = $userExists["email"];
                $firstName = $userExists["firstname"]; // lowercase
                $lastName = $userExists["lastname"];   // lowercase
                $employee_id = $userExists["employee_id"];

                $_SESSION["email"] = $email;
                $_SESSION["employee_id"] = $employee_id;
                $_SESSION["email_verification"] = $verification;

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'pagotaisidromarcojean123@gmail.com';
                    $mail->Password = 'piji jfrn znjb eaey';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('pagotaisidromarcojean123@gmail.com', 'HR OFFICE');
                    $mail->addAddress($email, $firstName . ' ' . $lastName);

                    $mail->isHTML(true);
                    $mail->Subject = 'Verify Your Account';
                    $mail->Body = "
                        <h3>Hello " . htmlspecialchars($firstName) . "!</h3>
                        <p>Welcome to Zamboanga Puericulture Center Org. No. 144.</p>
                        <p>Your verification code is:</p>
                        <h2 style='color:#2e6c80;'>" . htmlspecialchars($verification) . "</h2>
                        <p>This code will expire in 10 minutes.</p>
                    ";
                    $mail->AltBody = 'Hello ' . $firstName . ', your verification code is: ' . $verification;

                    $mail->send();

                    return json_encode([
                        'status' => 1,
                        'message' => 'Verification code sent successfully! Please check your email.'
                    ]);
                } catch (Exception $e) {
                    return json_encode([
                        'status' => 2,
                        'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo
                    ]);
                }
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'Username or email not exist'
                ]);
            }
        } catch (Exception $e) {
            return json_encode(['status' => 2, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
    function changePassword_verification_form(){
        try {
            
            $verification_code = $_POST["verification_code"] ?? '';
            
            // Check if session variables exist
            if (!isset($_SESSION["email_verification"])) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Verification session expired or not found. Please request a new code.'
                ]);
            }
            
            $stored_verification = $_SESSION["email_verification"];
            
            if ($verification_code !== $stored_verification) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Incorrect verification code! Please try again.'
                ]);
            } else {
                // Mark verification as completed
                $_SESSION["email_verified"] = true;
                
                return json_encode([
                    'status' => 1,
                    'message' => 'Verification successful!'
                ]);
            }
            
        } catch (Exception $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    // ACCOUNT MANAGEMENT ========================================================
    function register_form(){
        try {
            // Get form data
            $lastName = $_POST["lastName"] ?? '';
            $firstName = $_POST["firstName"] ?? '';
            $middleName = $_POST["middleName"] ?? '';
            $department_id = $_POST["department_id"] ?? null;
            $program_id = $_POST["program_id"] ?? null;
            $suffix = $_POST["suffix"] ?? '';
            $email = $_POST["email"] ?? '';
            $username = $_POST["username"] ?? '';
            $password = $_POST["password"] ?? '';
            $cpassword = $_POST["cpassword"] ?? '';
            $is_active = $_POST["is_active"] ?? 'Active';
            $user_role = $_POST["user_role"] ?? 'faculty';
            
            // Validation checks
            $required = ['firstName', 'lastName', 'email', 'username', 'password'];
            foreach ($required as $field) {
                if (empty($$field)) {
                    return json_encode(['status' => 0, 'message' => 'All required fields must be filled.']);
                }
            }
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return json_encode(['status' => 0, 'message' => 'Invalid email format.']);
            }
            
            if ($password !== $cpassword) {
                return json_encode(['status' => 0, 'message' => 'Passwords do not match.']);
            }
            
            if (strlen($password) < 6) {
                return json_encode(['status' => 0, 'message' => 'Password must be at least 6 characters long.']);
            }
            
            // Check if username or email already exists
            $stmt = $this->db->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->rowCount() > 0) {
                return json_encode(['status' => 0, 'message' => 'Username or email already exists.']);
            }
            
            // Validate user_role
            $allowed_roles = ['admin', 'qa', 'head', 'faculty', 'accreditor'];
            if (!in_array($user_role, $allowed_roles)) {
                $user_role = 'faculty'; // Default to faculty
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into database
            $stmt = $this->db->prepare("INSERT INTO users (
                username, email, password, first_name, last_name, middle_name, 
                suffix, user_role, is_active, department_id, program_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $result = $stmt->execute([
                $username,
                $email,
                $hashedPassword,
                $firstName,
                $lastName,
                $middleName,
                $suffix,
                $user_role,
                $is_active,
                $department_id,
                $program_id
            ]);
            
            if ($result) {
                return json_encode([
                    'status' => 1, 
                    'message' => 'Registration successful!'
                ]);
            } else {
                return json_encode([
                    'status' => 0, 
                    'message' => 'Registration failed. Please try again.'
                ]);
            }
            
        } catch (Exception $e) {
            // Log the error for debugging
            error_log("Registration error: " . $e->getMessage());
            
            return json_encode([
                'status' => 0, 
                'message' => 'Registration failed. Please try again later.'
            ]);
        }
    }
    function rejection_form()
    {
        $user_id = htmlspecialchars($_POST["user_id"] ?? '');

        // Validate input
        if (empty($user_id)) {
            return json_encode([
                'status' => 0,
                'message' => 'user ID is required.'
            ]);
        }

        try {
            // Update employee status to Inactive
            $stmt = $this->db->prepare("UPDATE users SET is_active = 'Inactive' WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            return json_encode([
                'status' => 1,
                'message' => 'Account rejected successfully!'
            ]);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
     function approval_form()
    {
        $user_id = htmlspecialchars($_POST["user_id"] ?? '');

        // Validate input
        if (empty($user_id)) {
            return json_encode([
                'status' => 0,
                'message' => 'user ID is required.'
            ]);
        }

        try {
            // Update employee status to Inactive
            $stmt = $this->db->prepare("UPDATE users SET is_active = 'Active' WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            return json_encode([
                'status' => 1,
                'message' => 'Account Approved successfully!'
            ]);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    
    // DEPARTMENTS AND PROGRAMS ======================================
    function department_form()
    {
        $department_name = htmlspecialchars(trim($_POST["department_name"]));
        $department_code = htmlspecialchars(trim($_POST["department_code"]));
        // Validation code remains the same...

        try {
            // Check if username exists using prepared statement
            $stmt = $this->db->prepare("SELECT department_name FROM departments WHERE department_name = ?");
            $stmt->execute([$department_name]);
            $department_nameTaken = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($department_nameTaken) {
                return json_encode([
                    'status' => 0,
                    'message' => 'department_name ' . $department_nameTaken["department_name"] . ' already taken please try another department_name'
                ]);
            }

            $stmt = $this->db->prepare("INSERT INTO departments (department_name, department_code) VALUES ('$department_name', '$department_code')");
            $stmt->execute();

            return json_encode([
                'status' => 1,
                'message' => 'department created successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function edit_department()
    {
        $department_id = htmlspecialchars($_POST["department_id"] ?? '');
        $department_name = htmlspecialchars($_POST["department_name"] ?? '');
        $department_code = htmlspecialchars($_POST["department_code"] ?? '');

        try {

            $stmt = $this->db->prepare("UPDATE departments SET Department_name = :Department_name, Department_code = :Department_code WHERE Department_id = :Department_id ");
            $stmt->execute(['Department_name' => $department_name, 'Department_code' => $department_code, 'Department_id' => $department_id]);

            return json_encode([
                'status' => 1,
                'message' => 'Department Edited successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function delete_department(){
        try {
           $department_id = $_POST["department_id"];
           $stmt = $this->db->prepare("DELETE FROM departments WHERE Department_id = :Department_id");
           $stmt->execute([
            'Department_id' => $department_id
           ]);

           return json_encode([
                'status' => 1,
                'message' => 'department Deleted successfully!'
            ]);
       } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

    function programs_form(){
        try {
            $program_name = $_POST["program_name"];
            $program_code = $_POST["program_code"];
            $department_id = $_POST["department_id"];

            $stmt = $this->db->prepare("INSERT INTO programs (program_name, program_code, department_id)
                VALUES (:program_name, :program_code, :department_id)");
            $stmt->execute([
                'program_name' => $program_name,
                'program_code' => $program_code,
                'department_id' => $department_id
            ]);

             return json_encode([
                'status' => 1,
                'message' => 'program created successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function update_programs(){
        try{
            $program_id = $_POST["program_id"];
            $program_name = $_POST["program_name"];
            $program_code = $_POST["program_code"];
            $department_id = $_POST["department_id"];

            $stmt = $this->db->prepare("UPDATE programs SET program_name = :program_name,
                program_code = :program_code, department_id = :department_id WHERE program_id = :program_id");
            $stmt->execute([
                'program_id' => $program_id,
                'program_name' => $program_name,
                'program_code' => $program_code,
                'department_id' => $department_id
            ]);

             return json_encode([
                'status' => 1,
                'message' => 'program updated successfully!'
            ]);

        } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                return json_encode([
                    'status' => 0,
                    'message' => 'An error occurred. Please try again later.'
                ]);
            }  
    }
    function delete_program(){
        try {
           $program_id = $_POST["program_id"];
           $stmt = $this->db->prepare("DELETE FROM programs WHERE program_id = :program_id");
           $stmt->execute([
            'program_id' => $program_id
           ]);

           return json_encode([
                'status' => 1,
                'message' => 'Program Deleted successfully!'
            ]);
       } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

    // Accreditation Framework ================================
    function area_form(){
        try {
            $area_number = $_POST["area_number"];
            $area_name = $_POST["area_name"];
            $description = $_POST["description"] ?? '';

            $stmt = $this->db->prepare("INSERT INTO accreditation_areas (area_number, area_name, description)
                VALUES (:area_number, :area_name, :description)");
            $stmt->execute([
                'area_number' => $area_number,
                'area_name' => $area_name,
                'description' => $description
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Area created successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function edit_area_form(){
        try{
            $area_id = $_POST["area_id"];
            $editAreaNumber = $_POST["area_number"];
            $editAreaName = $_POST["area_name"];
            $editAreaDescription = $_POST["description"];

            $stmt = $this->db->prepare("UPDATE accreditation_areas SET area_number = :area_number,
                area_name = :area_name, description = :description WHERE area_id = :area_id");
            $stmt->execute([
                'area_id' => $area_id,
                'area_number' => $editAreaNumber,
                'area_name' => $editAreaName,
                'description' => $editAreaDescription
            ]);

             return json_encode([
                'status' => 1,
                'message' => 'area updated successfully!'
            ]);

        } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                return json_encode([
                    'status' => 0,
                    'message' => 'An error occurred. Please try again later.'
                ]);
            }  
    }
    function delete_area_form(){
        try {
           $area_id = $_POST["area_id"];
           $stmt = $this->db->prepare("DELETE FROM accreditation_areas WHERE area_id = :area_id");
           $stmt->execute([
            'area_id' => $area_id
           ]);

           return json_encode([
                'status' => 1,
                'message' => 'Area Deleted successfully!'
            ]);
       } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

    function indicator_form() {
        try {
            // Clean values
            $parameter_id = $_POST["parameter_id"] ?? NULL;
            $parent_indicator_id = $_POST["parent_indicator_id"] ?? NULL;
            $indicator_code = trim($_POST["indicator_code"] ?? '');
            $indicator_description = trim($_POST["indicator_description"] ?? '');
            $indicator_level = $_POST["indicator_level"] ?? 'I';
            $sort_order = (int)($_POST["sort_order"] ?? 0);

            // Convert empty strings to NULL explicitly
            if ($parameter_id === "" || $parameter_id === "0") {
                $parameter_id = NULL;
            }

            if ($parent_indicator_id === "" || $parent_indicator_id === "0") {
                $parent_indicator_id = NULL;
            }

            // If indicator_level = 1 → force NO parent
            if ($indicator_level == 1) {
                $parent_indicator_id = NULL;
            }

            // Validate fields
            if (empty($indicator_code) || empty($indicator_description)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'All fields are required.'
                ]);
            }

            // Prepare insert
            $stmt = $this->db->prepare("
                INSERT INTO accreditation_indicators 
                (parameter_id, parent_indicator_id, indicator_code, indicator_description, indicator_level, sort_order)
                VALUES 
                (:parameter_id, :parent_indicator_id, :indicator_code, :indicator_description, :indicator_level, :sort_order)
            ");

            $stmt->execute([
                'parameter_id' => $parameter_id,
                'parent_indicator_id' => $parent_indicator_id,
                'indicator_code' => $indicator_code,
                'indicator_description' => $indicator_description,
                'indicator_level' => $indicator_level,
                'sort_order' => $sort_order
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Indicator added successfully!'
            ]);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());

            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }



    function edit_indicator_form() {
        try {
            // Collect values
            $indicator_id = $_POST["indicator_id"] ?? '';
            $parameter_id = $_POST["parameter_id"] ?? NULL;
            $parent_indicator_id = $_POST["parent_indicator_id"] ?? NULL;
            $indicator_code = trim($_POST["indicator_code"] ?? '');
            $indicator_description = trim($_POST["indicator_description"] ?? '');
            $indicator_level = (int)($_POST["indicator_level"] ?? 1);
            $sort_order = (int)($_POST["sort_order"] ?? 0);

            // Convert empty values to NULL
            if ($parameter_id === "" || $parameter_id === "0") {
                $parameter_id = NULL;
            }

            if ($parent_indicator_id === "" || $parent_indicator_id === "0") {
                $parent_indicator_id = NULL;
            }

            // If level 1 → MUST HAVE NO PARENT
            if ($indicator_level == 1) {
                $parent_indicator_id = NULL;
            }

            // Cannot be its own parent
            if ($parent_indicator_id == $indicator_id) {
                return json_encode([
                    'status' => 0,
                    'message' => 'An indicator cannot be its own parent.'
                ]);
            }

            // Validate required fields
            if (empty($indicator_id) || empty($indicator_code) || empty($indicator_description)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'All required fields must be filled.'
                ]);
            }

            // Update query (includes ALL important fields)
            $stmt = $this->db->prepare("
                UPDATE accreditation_indicators 
                SET 
                    parameter_id = :parameter_id,
                    parent_indicator_id = :parent_indicator_id,
                    indicator_code = :indicator_code,
                    indicator_description = :indicator_description,
                    indicator_level = :indicator_level,
                    sort_order = :sort_order
                WHERE indicator_id = :indicator_id
            ");

            $stmt->execute([
                'indicator_id' => $indicator_id,
                'parameter_id' => $parameter_id,
                'parent_indicator_id' => $parent_indicator_id,
                'indicator_code' => $indicator_code,
                'indicator_description' => $indicator_description,
                'indicator_level' => $indicator_level,
                'sort_order' => $sort_order
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Indicator updated successfully!'
            ]);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

    function delete_indicator_form(){
        try {
           $indicator_id = $_POST["indicator_id"];
           $stmt = $this->db->prepare("DELETE FROM accreditation_indicators WHERE indicator_id = :indicator_id");
           $stmt->execute([
            'indicator_id' => $indicator_id
           ]);

           return json_encode([
                'status' => 1,
                'message' => 'Indicators Deleted successfully!'
            ]);
       } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

// Accreditation Cycles  =================================================
    function cycle_form() {
        try {
            $cycle_name = $_POST["cycle_name"] ?? '';
            $academic_year = $_POST["academic_year"] ?? '';
            $program_id = $_POST["program_id"] ?? '';
            $status = $_POST["status"] ?? 'upcoming';
            $deadline = $_POST["deadline"] ?? null;

            if (empty($cycle_name) || empty($academic_year) || empty($program_id)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Cycle name, program and academic year are required.'
                ]);
            }

            // Validate academic year format (e.g., 2024-2025)
            if (!preg_match('/^\d{4}-\d{4}$/', $academic_year)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Academic year must be in format YYYY-YYYY (e.g., 2024-2025).'
                ]);
            }

            // Validate status
            $allowed_statuses = ['upcoming', 'active', 'closed'];
            if (!in_array($status, $allowed_statuses)) {
                $status = 'upcoming';
            }

            // Validate deadline if provided
            if (!empty($deadline)) {
                $deadline_date = DateTime::createFromFormat('Y-m-d', $deadline);
                if (!$deadline_date || $deadline_date->format('Y-m-d') !== $deadline) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Invalid deadline format. Use YYYY-MM-DD.'
                    ]);
                }
            }

            $stmt = $this->db->prepare("
                INSERT INTO accreditation_cycles 
                    (cycle_name, academic_year, program_id, status, deadline)
                VALUES 
                    (:cycle_name, :academic_year, :program_id, :status, :deadline)
            ");

            $stmt->execute([
                ':cycle_name' => $cycle_name,
                ':academic_year' => $academic_year,
                ':program_id' => $program_id,
                ':status' => $status,
                ':deadline' => $deadline ?: null
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Accreditation cycle added successfully!'
            ]);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function edit_cycle_form() {
        try {
            $cycle_id = $_POST["cycle_id"] ?? '';
            $cycle_name = $_POST["cycle_name"] ?? '';
            $academic_year = $_POST["academic_year"] ?? '';
            $program_id = $_POST["program_id"] ?? '';
            $status = $_POST["status"] ?? 'upcoming';
            $deadline = $_POST["deadline"] ?? null;

            if (empty($cycle_id) || empty($cycle_name) || empty($academic_year)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Cycle ID, name, and academic year are required.'
                ]);
            }

            // Validate academic year format (e.g., 2024-2025)
            if (!preg_match('/^\d{4}-\d{4}$/', $academic_year)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Academic year must be in format YYYY-YYYY (e.g., 2024-2025).'
                ]);
            }

            // Validate status
            $allowed_statuses = ['upcoming', 'active', 'closed'];
            if (!in_array($status, $allowed_statuses)) {
                $status = 'upcoming';
            }

            // Validate deadline if provided
            if (!empty($deadline)) {
                $deadline_date = DateTime::createFromFormat('Y-m-d', $deadline);
                if (!$deadline_date || $deadline_date->format('Y-m-d') !== $deadline) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Invalid deadline format. Use YYYY-MM-DD.'
                    ]);
                }
            }

            $stmt = $this->db->prepare(" 
                UPDATE accreditation_cycles
                SET cycle_name = :cycle_name,
                    academic_year = :academic_year,
                    program_id = :program_id,
                    status = :status,
                    deadline = :deadline
                WHERE cycle_id = :cycle_id
            ");

            $stmt->execute([
                ':cycle_id' => $cycle_id,
                ':cycle_name' => $cycle_name,
                ':academic_year' => $academic_year,
                ':program_id' => $program_id,
                ':status' => $status,
                ':deadline' => $deadline ?: null
            ]);

            if ($stmt->rowCount() === 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'No changes made or cycle not found.'
                ]);
            }

            return json_encode([
                'status' => 1,
                'message' => 'Accreditation cycle updated successfully!'
            ]);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function delete_cycle_form() {
        try {
            $cycle_id = $_POST["cycle_id"] ?? '';

            if (empty($cycle_id)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid request. Missing cycle ID.'
                ]);
            }

            // First, check if cycle exists and can be deleted
            $check_stmt = $this->db->prepare("
                SELECT status FROM accreditation_cycles 
                WHERE cycle_id = :cycle_id
            ");
            $check_stmt->execute([':cycle_id' => $cycle_id]);
            $cycle = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cycle) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Accreditation cycle not found.'
                ]);
            }

            // Optional: Prevent deletion of active cycles
            if ($cycle['status'] === 'active') {
                return json_encode([
                    'status' => 0,
                    'message' => 'Cannot delete an strong active accreditation cycle. Please close it first.'
                ]);
            }

            $stmt = $this->db->prepare("
                DELETE FROM accreditation_cycles 
                WHERE cycle_id = :cycle_id
            ");

            $stmt->execute([':cycle_id' => $cycle_id]);

            if ($stmt->rowCount() === 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Accreditation cycle not found or already deleted.'
                ]);
            }

            return json_encode([
                'status' => 1,
                'message' => 'Accreditation cycle deleted successfully!'
            ]);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            
            // Check for foreign key constraint violation
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Cannot delete this cycle because it has associated criteria or data. Please delete related data first.'
                ]);
            }
            
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function activate_cycle_form(){
        try {
            $cycle_id = $_POST["cycle_id"];

            $stmt = $this->db->prepare("UPDATE accreditation_cycles SET status = 'active' WHERE cycle_id = :cycle_id");
            $stmt->execute([
                'cycle_id' => $cycle_id
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Cycle avticated successfully'
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occured: ' . $e->getMessage()
            ]);
        }
    }
    function close_cycle_form(){
        try {
            $cycle_id = $_POST["cycle_id"];

            $stmt = $this->db->prepare("UPDATE accreditation_cycles SET status = 'closed' WHERE cycle_id = :cycle_id");
            $stmt->execute([
                'cycle_id' => $cycle_id
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Cycle closed successfully'
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occured: ' . $e->getMessage()
            ]);
        }
    }

// Document Management ==================================================
    function document_form() {
        try {
            // Simple validation
            $indicator_id = $_POST["indicator_id"] ?? '';
            $program_id = $_POST["program_id"] ?? '';
            $cycle_id = $_POST["cycle_id"] ?? '';
            $uploaded_by = $_POST['user_id'] ?? 0;
            $description = $_POST["description"] ?? '';

            if (empty($indicator_id) || empty($program_id) || empty($cycle_id) || empty($uploaded_by)) {
                return json_encode(['status' => 0, 'message' => 'Missing required fields']);
            }

            if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
                return json_encode(['status' => 0, 'message' => 'Please select a file']);
            }

            $getHead = $this->db->prepare("SELECT assigned_by FROM assignments
            WHERE indicator_id = :indicator_id AND program_id = :program_id");
            $getHead->execute([
                'program_id' => $program_id,
                'indicator_id' => $indicator_id
            ]);
            $getDataHead = $getHead->fetch(PDO::FETCH_ASSOC);
            $user_id = $getDataHead["assigned_by"];

            $file = $_FILES['document_file'];
            
            // Extended file validation
            $allowed = ['pdf', 'doc', 'docx', 'zip'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                return json_encode(['status' => 0, 'message' => 'Only PDF, DOC, DOCX, and ZIP files allowed']);
            }

            // Adjust file size limit
            $max_size = ($ext === 'zip') ? 50 * 1024 * 1024 : 5 * 1024 * 1024;
            if ($file['size'] > $max_size) {
                $max_mb = ($ext === 'zip') ? 50 : 5;
                return json_encode(['status' => 0, 'message' => "File too large (max {$max_mb}MB)"]);
            }

            // Create directory
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate unique filename
            $original_filename = $file['name'];
            $safe_filename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '_', $original_filename);
            $filepath = $upload_dir . $safe_filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return json_encode(['status' => 0, 'message' => 'Failed to upload file']);
            }

            // Get or create assignment
            $assignmentStmt = $this->db->prepare("
                SELECT assignment_id FROM assignments 
                WHERE user_id = ? AND indicator_id = ? AND program_id = ? AND cycle_id = ?
            ");
            $assignmentStmt->execute([$uploaded_by, $indicator_id, $program_id, $cycle_id]);
            $assignment = $assignmentStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$assignment) {
                $assigned_by = $_SESSION['adminData']['user_id'] ?? 
                            $_SESSION['QAData']['user_id'] ?? 
                            $_SESSION['employeeData']['user_id'] ?? 
                            $uploaded_by;
                
                $create = $this->db->prepare("
                    INSERT INTO assignments (user_id, indicator_id, program_id, cycle_id, assigned_by, status)
                    VALUES (?, ?, ?, ?, ?, 'in_progress')
                ");
                $create->execute([$uploaded_by, $indicator_id, $program_id, $cycle_id, $assigned_by]);
                $assignment_id = $this->db->lastInsertId();
            } else {
                $assignment_id = $assignment['assignment_id'];
            }

            // Set is_archive flag
            $is_archive = ($ext === 'zip') ? 1 : 0;

            // Insert document
            $stmt = $this->db->prepare("
                INSERT INTO documents 
                (filename, original_filename, file_path, file_size, assignment_id, uploaded_by, 
                indicator_id, program_id, cycle_id, description, status, is_archive)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', ?)
            ");
            
            $stmt->execute([
                $safe_filename,
                $original_filename,
                $filepath,
                $file['size'],
                $assignment_id,
                $uploaded_by,
                $indicator_id,
                $program_id,
                $cycle_id,
                $description,
                $is_archive
            ]);

            $stmtNotifi = $this->db->prepare("INSERT INTO notifications (user_id, title, message) VALUES
                (:user_id, 'Document Uploaded', 'A faculty Uploaded a document, you can now assign a reviewer!')");
            $stmtNotifi->execute([
                'user_id' => $user_id
            ]);

            $document_id = $this->db->lastInsertId();

            // Update assignment to submitted
            $update = $this->db->prepare("UPDATE assignments SET status = 'submitted' WHERE assignment_id = ?");
            $update->execute([$assignment_id]);

            $message = ($ext === 'zip') ? 
                'ZIP file uploaded successfully!' : 
                'Document uploaded successfully!';

            return json_encode([
                'status' => 1,
                'message' => $message,
                'document_id' => $document_id,
                'is_archive' => $is_archive
            ]);

        } catch (PDOException $e) {
            // Clean up file on error
            if (isset($filepath) && file_exists($filepath)) {
                unlink($filepath);
            }
            return json_encode(['status' => 0, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            if (isset($filepath) && file_exists($filepath)) {
                unlink($filepath);
            }
            return json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    function edit_document_form(){
        $document_id = $_POST['document_id'] ?? null;
        $indicator_id = $_POST['indicator_id'] ?? null;
        $program_id = $_POST['program_id'] ?? null;
        $cycle_id = $_POST['cycle_id'] ?? null;
        $status = $_POST['status'] ?? 'draft';

        if (!$document_id) {
            return json_encode([
                'status' => 0,
                'message' => 'Missing document ID.'
            ]);
        }

        try {
            // Get existing document info
            $stmt = $this->db->prepare("SELECT * FROM documents WHERE document_id = ?");
            $stmt->execute([$document_id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$doc) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Document not found.'
                ]);
            }

            $newFilename = $doc['filename']; // keep existing if no upload
            $newFilePath = $doc['file_path'];
            $newFileSize = $doc['file_size'];
            $newOriginalFilename = $doc['original_filename'];

            // If user selected a new file
            if (!empty($_FILES['document_file']['name'])) {

                $uploadDir = "uploads/";

                // Create folder if not exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $originalName = $_FILES['document_file']['name'];
                $tmpName = $_FILES['document_file']['tmp_name'];
                $size = $_FILES['document_file']['size'];

                // Generate new unique filename
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $generatedName = uniqid('doc_') . '.' . $ext;
                $fullPath = $uploadDir . $generatedName;

                // Upload file
                if (!move_uploaded_file($tmpName, $fullPath)) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Failed to upload new file.'
                    ]);
                }

                // Delete old file
                if (file_exists($doc['file_path'])) {
                    unlink($doc['file_path']);
                }

                // Update values
                $newFilename = $generatedName;
                $newFilePath = $fullPath;
                $newFileSize = $size;
                $newOriginalFilename = $originalName;
            }

            // Update database record
            $stmt = $this->db->prepare("
                UPDATE documents SET 
                    filename = ?, 
                    file_path = ?, 
                    file_size = ?, 
                    indicator_id = ?, 
                    program_id = ?, 
                    cycle_id = ?, 
                    original_filename = ?, 
                    status = ?, 
                    updated_at = NOW()
                WHERE document_id = ?
            ");

            $stmt->execute([
                $newFilename,
                $newFilePath,
                $newFileSize,
                $indicator_id,
                $program_id,
                $cycle_id,
                $newOriginalFilename,
                $status,
                $document_id
            ]);

            $reviewStmt = $this->db->prepare("SELECT r.reviewer_id FROM reviews r
                WHERE r.document_id = :document_id AND r.reviewer_id IS NOT NULL
                LIMIT 1");
            $reviewStmt->execute([
                'document_id' => $document_id
            ]);
            $getData = $reviewStmt->fetch(PDO::FETCH_ASSOC);

            if ($getData && isset($getData["reviewer_id"])) {
                $user_id = $getData["reviewer_id"];
                
                $notifStmt = $this->db->prepare("
                    INSERT INTO notifications 
                    (user_id, title, message) 
                    VALUES (:user_id, 'Document have been Updated!', 'Faculty Updated a document.')
                ");
                $notifStmt->execute([':user_id' => $user_id]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'No reviewer found for document ID: ' . $document_id
                ]);
            }

            return json_encode([
                'status' => 1,
                'message' => 'Document updated successfully!'
            ]);

        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    function delete_document_form() {
        try {
            $document_id  = $_POST["document_id"];

            $stmt = $this->db->prepare("SELECT * FROM reviews WHERE document_id = :document_id");
            $stmt->execute([
                'document_id' => $document_id
            ]);
            $reviewerExist = $stmt->fetch(PDO::FETCH_ASSOC);

            if($reviewerExist){
                return json_encode([
                    'status' => 0,
                    'message' => 'Document are being reviewed cannot delete'
                ]);
            }
            
            $documentStmt = $this->db->prepare("DELETE FROM documents WHERE document_id = :document_id");
            $documentStmt->execute([
                'document_id' => $document_id
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Documment deleted successfully'
            ]);
        } catch (PDOExecption $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occured: ' . $e->getMessage()
            ]);
        }
    }
    function approve_document_form() {
        try {
            $document_id = $_POST["document_id"];
            $review_id = $_POST["review_id"];

            $stmt = $this->db->prepare("SELECT uploaded_by FROM documents WHERE document_id = :document_id");
            $stmt->execute([
                'document_id' => $document_id
            ]);
            $uploaded_by = $stmt->fetch(PDO::FETCH_ASSOC);
            $uploader_id = $uploaded_by["uploaded_by"];

            // Update document status
            $stmtdocumemt = $this->db->prepare("UPDATE documents SET status = 'approved' WHERE document_id = :document_id");
            $stmtdocumemt->execute(['document_id' => $document_id]);
            
            // Update review action
            $stmtreview = $this->db->prepare("UPDATE reviews SET review_action = 'approved' WHERE review_id = :review_id");
            $stmtreview->execute(['review_id' => $review_id]);

            // Get document info for compliance tracking
            $stmt = $this->db->prepare("
                SELECT d.program_id, d.cycle_id, d.indicator_id, 
                    d.status, r.reviewer_id, r.reviewed_at
                FROM documents d
                LEFT JOIN reviews r ON d.document_id = r.document_id
                WHERE d.document_id = :document_id
            ");
            $stmt->execute(['document_id' => $document_id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $complianceId = null;
            if ($doc) {
                // Determine compliance status
                $complianceStatus = 'compliant'; // Since we just approved it
                
                // Get or create compliance record (using helper function)
                $complianceId = $this->getComplianceId(
                    $doc['program_id'],
                    $doc['cycle_id'], 
                    $doc['indicator_id']
                );
                
                // Update compliance tracking
                $stmt = $this->db->prepare("
                    UPDATE compliance_tracking 
                    SET status = :status, 
                        reviewer_id = :reviewer_id,
                        review_date = DATE(:reviewed_at)
                    WHERE compliance_id = :compliance_id
                ");
                
                $stmt->execute([
                    'status' => $complianceStatus,
                    'reviewer_id' => $doc['reviewer_id'],
                    'reviewed_at' => $doc['reviewed_at'],
                    'compliance_id' => $complianceId
                ]);
            }
             $notifStmt = $this->db->prepare("
                            INSERT INTO notifications 
                            (user_id, title, message) 
                            VALUES (:user_id, 'Document have been Approved!', 'Your submitted document meets the requirements.')
                        ");
                        $notifStmt->execute([':user_id' => $uploader_id]);

            return json_encode([
                'status' => 1,
                'message' => 'Document approved successfully',
                'compliance_id' => $complianceId
            ]);
            
        } catch (PDOException $e) { 
            return json_encode([
                'status' => 0, 
                'message' => 'An error occurred: ' . $e->getMessage() 
            ]);
        }
    }
    function revise_document_form() {
        try {
            $document_id = $_POST["document_id"];
            $review_id = $_POST["review_id"];
            $revision_comments = $_POST["comments"] ?? '';
            $revision_reason = $_POST["revision_reason"] ?? ''; // Optional: specific reason

            // Update document status
            $stmtdocumemt = $this->db->prepare("UPDATE documents SET status = 'needs_revision' WHERE document_id = :document_id");
            $stmtdocumemt->execute(['document_id' => $document_id]);
            
            // Update review with reason and comments
            $stmtreview = $this->db->prepare("
                UPDATE reviews 
                SET review_action = 'needs_revision', 
                    comments = :comments,
                    revision_reason = :revision_reason,
                    reviewed_at = NOW()
                WHERE review_id = :review_id
            ");
            $stmtreview->execute([
                'review_id' => $review_id,
                'comments' => $revision_comments,
                'revision_reason' => $revision_reason
            ]);

            // Get document info for compliance tracking
            $stmt = $this->db->prepare("
                SELECT d.program_id, d.cycle_id, d.indicator_id, 
                    d.status, r.reviewer_id, r.reviewed_at
                FROM documents d
                LEFT JOIN reviews r ON d.document_id = r.document_id
                WHERE d.document_id = :document_id
            ");
            $stmt->execute(['document_id' => $document_id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $complianceId = null;
            if ($doc) {
                // Compliance status for needs_revision is 'partial'
                $complianceStatus = 'partial';
                
                // Get or create compliance record using the helper method
                if (method_exists($this, 'getComplianceId')) {
                    $complianceId = $this->getComplianceId(
                        $doc['program_id'],
                        $doc['cycle_id'], 
                        $doc['indicator_id']
                    );
                } else {
                    // Fallback: direct implementation
                    $complianceId = $this->createOrGetComplianceId(
                        $doc['program_id'],
                        $doc['cycle_id'], 
                        $doc['indicator_id']
                    );
                }
                
                // Update compliance tracking
                $stmt = $this->db->prepare("
                    UPDATE compliance_tracking 
                    SET status = :status, 
                        reviewer_id = :reviewer_id,
                        review_date = DATE(:reviewed_at),
                        comments = :comments
                    WHERE compliance_id = :compliance_id
                ");
                
                $stmt->execute([
                    'status' => $complianceStatus,
                    'reviewer_id' => $doc['reviewer_id'],
                    'reviewed_at' => $doc['reviewed_at'],
                    'comments' => $revision_comments . ($revision_reason ? "\n\nRevision Reason: " . $revision_reason : ''),
                    'compliance_id' => $complianceId
                ]);
                
                // Optional: Send notification to faculty that document needs revision
                try {
                    // Get faculty user_id from the document
                    $facultyStmt = $this->db->prepare("
                        SELECT uploaded_by 
                        FROM documents 
                        WHERE document_id = :document_id
                    ");
                    $facultyStmt->execute(['document_id' => $document_id]);
                    $faculty = $facultyStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($faculty && isset($faculty['uploaded_by'])) {
                        $notifStmt = $this->db->prepare("
                            INSERT INTO notifications 
                            (user_id, title, message, created_at) 
                            VALUES (:user_id, 'Document Needs Revision', 'Your submitted document requires revisions. Please check the comments.', NOW())
                        ");
                        $notifStmt->execute([':user_id' => $faculty['uploaded_by']]);
                    }
                } catch (Exception $e) {
                    // Continue even if notification fails
                    error_log("Notification error in revise_document_form: " . $e->getMessage());
                }
            }
            
            return json_encode([
                'status' => 1,
                'message' => 'Document marked for revision successfully',
                'compliance_id' => $complianceId,
                'document_id' => $document_id,
                'status_text' => 'needs_revision'
            ]);
            
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0, 
                'message' => 'An error occurred: ' . $e->getMessage(),
                'error_details' => 'Error in revise_document_form function'
            ]);
        }
    }

// Compliance Tracking ===================================================
    function add_compliance_form() {
        try {
            // Get form data
            $program_id = $_POST["program_id"] ?? '';
            $cycle_id = $_POST["cycle_id"] ?? '';
            $indicator_id = $_POST["indicator_id"] ?? '';
            $criteria_id = $_POST["criteria_id"] ?? null; // Optional
            $status = $_POST["status"] ?? 'not_assessed';
            $compliance_score = $_POST["compliance_score"] ?? null;
            $evidence_count = $_POST["evidence_count"] ?? 0;
            $reviewer_id = $_POST["reviewer_id"] ?? null;
            $review_date = $_POST["review_date"] ?? null;
            $comments = $_POST["comments"] ?? '';
            $current_user_id = $_POST["user_id"];

            // Validate required fields
            if (empty($program_id) || empty($cycle_id) || empty($indicator_id)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Program, accreditation cycle, and indicator are required.'
                ]);
            }

            // Validate program exists
            $checkProgram = $this->db->prepare("SELECT program_id FROM programs WHERE program_id = ?");
            $checkProgram->execute([$program_id]);
            if (!$checkProgram->fetch()) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Selected program does not exist.'
                ]);
            }

            // Validate cycle exists
            $checkCycle = $this->db->prepare("SELECT cycle_id FROM accreditation_cycles WHERE cycle_id = ?");
            $checkCycle->execute([$cycle_id]);
            if (!$checkCycle->fetch()) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Selected accreditation cycle does not exist.'
                ]);
            }

            // Validate indicator exists
            $checkIndicator = $this->db->prepare("SELECT indicator_id FROM accreditation_indicators WHERE indicator_id = ?");
            $checkIndicator->execute([$indicator_id]);
            if (!$checkIndicator->fetch()) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Selected indicator does not exist.'
                ]);
            }

            // Validate criteria if provided
            if (!empty($criteria_id)) {
                $checkCriteria = $this->db->prepare("SELECT criteria_id FROM accreditation_criteria WHERE criteria_id = ?");
                $checkCriteria->execute([$criteria_id]);
                if (!$checkCriteria->fetch()) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Selected criteria does not exist.'
                    ]);
                }
            }

            // Validate status
            $allowed_statuses = ['compliant', 'non_compliant', 'partial', 'not_assessed'];
            if (!in_array($status, $allowed_statuses)) {
                $status = 'not_assessed';
            }

            // Validate compliance score (0-100)
            if ($compliance_score !== null) {
                $compliance_score = floatval($compliance_score);
                if ($compliance_score < 0 || $compliance_score > 100) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Compliance score must be between 0 and 100.'
                    ]);
                }
            }

            // Validate evidence count
            $evidence_count = intval($evidence_count);
            if ($evidence_count < 0) {
                $evidence_count = 0;
            }

            // Validate reviewer if provided
            if (!empty($reviewer_id)) {
                $checkReviewer = $this->db->prepare("SELECT user_id FROM users WHERE user_id = ?");
                $checkReviewer->execute([$reviewer_id]);
                if (!$checkReviewer->fetch()) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Selected reviewer does not exist.'
                    ]);
                }
            }

            // Validate review date if provided
            if (!empty($review_date)) {
                $review_date_obj = DateTime::createFromFormat('Y-m-d', $review_date);
                if (!$review_date_obj || $review_date_obj->format('Y-m-d') !== $review_date) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Invalid review date format. Use YYYY-MM-DD.'
                    ]);
                }
            }

            // Check if compliance record already exists for this combination
            $checkExisting = $this->db->prepare("
                SELECT compliance_id 
                FROM compliance_tracking 
                WHERE program_id = ? 
                AND cycle_id = ? 
                AND indicator_id = ?
                AND (criteria_id = ? OR (? IS NULL AND criteria_id IS NULL))
            ");
            $checkExisting->execute([$program_id, $cycle_id, $indicator_id, $criteria_id, $criteria_id]);
            
            if ($checkExisting->fetch()) {
                return json_encode([
                    'status' => 0,
                    'message' => 'A compliance record already exists for this program, cycle, and indicator combination. Please update the existing record instead.'
                ]);
            }

            // Insert into compliance_tracking
            $stmt = $this->db->prepare("
                INSERT INTO compliance_tracking 
                    (program_id, cycle_id, indicator_id, criteria_id, status, 
                    compliance_score, evidence_count, reviewer_id, review_date, comments)
                VALUES 
                    (:program_id, :cycle_id, :indicator_id, :criteria_id, :status,
                    :compliance_score, :evidence_count, :reviewer_id, :review_date, :comments)
            ");

            $stmt->execute([
                ':program_id' => $program_id,
                ':cycle_id' => $cycle_id,
                ':indicator_id' => $indicator_id,
                ':criteria_id' => $criteria_id,
                ':status' => $status,
                ':compliance_score' => $compliance_score,
                ':evidence_count' => $evidence_count,
                ':reviewer_id' => $reviewer_id,
                ':review_date' => $review_date,
                ':comments' => $comments
            ]);

            $compliance_id = $this->db->lastInsertId();

            // Optional: Log the action
            $logStmt = $this->db->prepare("
                INSERT INTO activity_logs 
                    (user_id, action, description, ip_address, user_agent)
                VALUES 
                    (:user_id, :action, :description, :ip_address, :user_agent)
            ");

            // Get IP address
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }

            // Get user agent
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $logStmt->execute([
                ':user_id' => $current_user_id,
                ':action' => 'add_compliance',
                ':description' => "Added compliance record #{$compliance_id} for program:{$program_id}, indicator:{$indicator_id}, status:{$status}",
                ':ip_address' => $ip_address,
                ':user_agent' => $user_agent
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Compliance assessment added successfully!',
                'compliance_id' => $compliance_id
            ]);

        } catch (PDOException $e) {
            error_log("Database error in add_compliance_form: " . $e->getMessage());
            
            // Check for foreign key constraint violation
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid reference. Please check the selected program, cycle, or indicator.'
                ]);
            }
            
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function edit_compliance_form() {
        try {
            // Get form data
            $compliance_id = $_POST["compliance_id"] ?? '';
            $program_id = $_POST["program_id"] ?? '';
            $cycle_id = $_POST["cycle_id"] ?? '';
            $indicator_id = $_POST["indicator_id"] ?? '';
            $criteria_id = $_POST["criteria_id"] ?? null;
            $status = $_POST["status"] ?? 'not_assessed';
            $compliance_score = $_POST["compliance_score"] ?? null;
            $evidence_count = $_POST["evidence_count"] ?? 0;
            $reviewer_id = $_POST["reviewer_id"] ?? null;
            $review_date = $_POST["review_date"] ?? null;
            $comments = $_POST["comments"] ?? '';
            $current_user_id = $_POST["user_id"];

            // Validate required fields
            if (empty($compliance_id) || empty($program_id) || empty($cycle_id) || empty($indicator_id)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'All required fields must be filled.'
                ]);
            }

            // Check if compliance record exists
            $checkExists = $this->db->prepare("SELECT compliance_id FROM compliance_tracking WHERE compliance_id = ?");
            $checkExists->execute([$compliance_id]);
            if (!$checkExists->fetch()) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Compliance record not found.'
                ]);
            }

            // Validate program exists
            $checkProgram = $this->db->prepare("SELECT program_id FROM programs WHERE program_id = ?");
            $checkProgram->execute([$program_id]);
            if (!$checkProgram->fetch()) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Selected program does not exist.'
                ]);
            }

            // Validate cycle exists
            $checkCycle = $this->db->prepare("SELECT cycle_id FROM accreditation_cycles WHERE cycle_id = ?");
            $checkCycle->execute([$cycle_id]);
            if (!$checkCycle->fetch()) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Selected accreditation cycle does not exist.'
                ]);
            }

            // Validate indicator exists
            $checkIndicator = $this->db->prepare("SELECT indicator_id FROM accreditation_indicators WHERE indicator_id = ?");
            $checkIndicator->execute([$indicator_id]);
            if (!$checkIndicator->fetch()) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Selected indicator does not exist.'
                ]);
            }

            // Validate criteria if provided
            if (!empty($criteria_id)) {
                $checkCriteria = $this->db->prepare("SELECT criteria_id FROM accreditation_criteria WHERE criteria_id = ?");
                $checkCriteria->execute([$criteria_id]);
                if (!$checkCriteria->fetch()) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Selected criteria does not exist.'
                    ]);
                }
            }

            // Validate status
            $allowed_statuses = ['compliant', 'non_compliant', 'partial', 'not_assessed'];
            if (!in_array($status, $allowed_statuses)) {
                $status = 'not_assessed';
            }

            // Validate compliance score (0-100)
            if ($compliance_score !== null && $compliance_score !== '') {
                $compliance_score = floatval($compliance_score);
                if ($compliance_score < 0 || $compliance_score > 100) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Compliance score must be between 0 and 100.'
                    ]);
                }
            } else {
                $compliance_score = null;
            }

            // Validate evidence count
            $evidence_count = intval($evidence_count);
            if ($evidence_count < 0) {
                $evidence_count = 0;
            }

            // Validate reviewer if provided
            if (!empty($reviewer_id)) {
                $checkReviewer = $this->db->prepare("SELECT user_id FROM users WHERE user_id = ?");
                $checkReviewer->execute([$reviewer_id]);
                if (!$checkReviewer->fetch()) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Selected reviewer does not exist.'
                    ]);
                }
            }

            // Validate review date if provided
            if (!empty($review_date)) {
                $review_date_obj = DateTime::createFromFormat('Y-m-d', $review_date);
                if (!$review_date_obj || $review_date_obj->format('Y-m-d') !== $review_date) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Invalid review date format. Use YYYY-MM-DD.'
                    ]);
                }
            }

            // Check for duplicate compliance record (excluding current one)
            $checkDuplicate = $this->db->prepare("
                SELECT compliance_id 
                FROM compliance_tracking 
                WHERE program_id = ? 
                AND cycle_id = ? 
                AND indicator_id = ?
                AND (criteria_id = ? OR (? IS NULL AND criteria_id IS NULL))
                AND compliance_id != ?
            ");
            $checkDuplicate->execute([$program_id, $cycle_id, $indicator_id, $criteria_id, $criteria_id, $compliance_id]);
            
            if ($checkDuplicate->fetch()) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Another compliance record already exists for this program, cycle, and indicator combination.'
                ]);
            }

            // Update the compliance record
            $stmt = $this->db->prepare("
                UPDATE compliance_tracking 
                SET program_id = :program_id,
                    cycle_id = :cycle_id,
                    indicator_id = :indicator_id,
                    criteria_id = :criteria_id,
                    status = :status,
                    compliance_score = :compliance_score,
                    evidence_count = :evidence_count,
                    reviewer_id = :reviewer_id,
                    review_date = :review_date,
                    comments = :comments
                WHERE compliance_id = :compliance_id
            ");

            $stmt->execute([
                ':compliance_id' => $compliance_id,
                ':program_id' => $program_id,
                ':cycle_id' => $cycle_id,
                ':indicator_id' => $indicator_id,
                ':criteria_id' => $criteria_id,
                ':status' => $status,
                ':compliance_score' => $compliance_score,
                ':evidence_count' => $evidence_count,
                ':reviewer_id' => $reviewer_id,
                ':review_date' => $review_date,
                ':comments' => $comments
            ]);

            if ($stmt->rowCount() === 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'No changes were made.'
                ]);
            }

            // Log the action
            if ($current_user_id > 0) {
                $logStmt = $this->db->prepare("
                    INSERT INTO activity_logs 
                        (user_id, action, description, ip_address, user_agent)
                    VALUES 
                        (:user_id, :action, :description, :ip_address, :user_agent)
                ");
                
                // Get IP address
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
                
                // Get user agent
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                
                $logStmt->execute([
                    ':user_id' => $current_user_id,
                    ':action' => 'edit_compliance',
                    ':description' => "Updated compliance record #{$compliance_id} - Program:{$program_id}, Indicator:{$indicator_id}, Status:{$status}, Score:{$compliance_score}",
                    ':ip_address' => $ip_address,
                    ':user_agent' => $user_agent
                ]);
            }

            return json_encode([
                'status' => 1,
                'message' => 'Compliance assessment updated successfully!',
                'compliance_id' => $compliance_id
            ]);

        } catch (PDOException $e) {
            error_log("Database error in edit_compliance_form: " . $e->getMessage());
            
            // Check for foreign key constraint violation
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid reference. Please check the selected program, cycle, or indicator.'
                ]);
            }
            
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function delete_compliance_form() {
        try {
            $compliance_id = $_POST["compliance_id"] ?? '';
            
            // Get current user for logging
            $current_user_id = $_POST['user_id'] ?? 0;

            // Validate required fields
            if (empty($compliance_id)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid request. Missing compliance ID.'
                ]);
            }

            // Get compliance details before deletion (for logging and verification)
            $getDetails = $this->db->prepare("
                SELECT 
                    ct.program_id,
                    ct.indicator_id,
                    ct.status,
                    ct.compliance_score,
                    p.program_code,
                    p.program_name,
                    ai.indicator_code
                FROM compliance_tracking ct
                JOIN programs p ON ct.program_id = p.program_id
                JOIN accreditation_indicators ai ON ct.indicator_id = ai.indicator_id
                WHERE ct.compliance_id = ?
            ");
            $getDetails->execute([$compliance_id]);
            $details = $getDetails->fetch(PDO::FETCH_ASSOC);

            if (!$details) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Compliance record not found.'
                ]);
            }

            // Optional: Check permissions (e.g., only admin or original reviewer can delete)
            // $checkPermission = $this->db->prepare("
            //     SELECT user_role FROM users WHERE user_id = ?
            // ");
            // $checkPermission->execute([$current_user_id]);
            // $user = $checkPermission->fetch(PDO::FETCH_ASSOC);
            // 
            // if ($user['user_role'] !== 'admin') {
            //     return json_encode([
            //         'status' => 0,
            //         'message' => 'You do not have permission to delete compliance records.'
            //     ]);
            // }

            // Delete the compliance record
            $stmt = $this->db->prepare("DELETE FROM compliance_tracking WHERE compliance_id = ?");
            $stmt->execute([$compliance_id]);

            if ($stmt->rowCount() === 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Compliance record not found or already deleted.'
                ]);
            }

            // Log the action
            if ($current_user_id > 0) {
                $logStmt = $this->db->prepare("
                    INSERT INTO activity_logs 
                        (user_id, action, description, ip_address, user_agent)
                    VALUES 
                        (:user_id, :action, :description, :ip_address, :user_agent)
                ");
                
                // Get IP address
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
                
                // Get user agent
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                
                $logDescription = sprintf(
                    "Deleted compliance record #%s - Program: %s (%s), Indicator: %s, Status: %s, Score: %s",
                    $compliance_id,
                    $details['program_code'],
                    $details['program_name'],
                    $details['indicator_code'],
                    $details['status'],
                    $details['compliance_score'] ?? 'N/A'
                );
                
                $logStmt->execute([
                    ':user_id' => $current_user_id,
                    ':action' => 'delete_compliance',
                    ':description' => $logDescription,
                    ':ip_address' => $ip_address,
                    ':user_agent' => $user_agent
                ]);
            }

            return json_encode([
                'status' => 1,
                'message' => 'Compliance assessment deleted successfully!',
                'deleted_id' => $compliance_id
            ]);

        } catch (PDOException $e) {
            error_log("Database error in delete_compliance_form: " . $e->getMessage());
            
            // Check for foreign key constraint violation
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Cannot delete this compliance record because it is referenced by other data.'
                ]);
            }
            
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred while deleting the compliance record. Please try again later.'
            ]);
        }
    }
    function get_compliance_details() {
        try {
            $compliance_id = $_POST["compliance_id"] ?? $_GET["compliance_id"] ?? '';
            
            if (empty($compliance_id)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Compliance ID is required.'
                ]);
            }

            $stmt = $this->db->prepare("
                SELECT 
                    ct.*,
                    p.program_code,
                    p.program_name,
                    ac.cycle_name,
                    ac.academic_year,
                    ai.indicator_code,
                    ai.indicator_description,
                    aa.area_number,
                    aa.area_name,
                    aa.description as area_description,
                    cr.criteria_number,
                    cr.criteria_description,
                    cr.required_evidence,
                    u.first_name as reviewer_first,
                    u.last_name as reviewer_last,
                    u.email as reviewer_email,
                    u.user_role as reviewer_role,
                    -- Get creator info if you have created_by field
                    uc.first_name as created_first,
                    uc.last_name as created_last,
                    -- Get evidence documents count
                    (SELECT COUNT(*) FROM documents d 
                    WHERE d.program_id = ct.program_id 
                    AND d.indicator_id = ct.indicator_id 
                    AND d.cycle_id = ct.cycle_id) as total_documents
                FROM compliance_tracking ct
                JOIN programs p ON ct.program_id = p.program_id
                JOIN accreditation_cycles ac ON ct.cycle_id = ac.cycle_id
                JOIN accreditation_indicators ai ON ct.indicator_id = ai.indicator_id
                JOIN accreditation_areas aa ON ai.area_id = aa.area_id
                LEFT JOIN accreditation_criteria cr ON ct.criteria_id = cr.criteria_id
                LEFT JOIN users u ON ct.reviewer_id = u.user_id
                LEFT JOIN users uc ON ct.created_by = uc.user_id
                WHERE ct.compliance_id = ?
            ");
            
            $stmt->execute([$compliance_id]);
            $compliance = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$compliance) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Compliance record not found.'
                ]);
            }

            // Format dates
            $compliance['review_date_formatted'] = $compliance['review_date'] ? 
                date('F j, Y', strtotime($compliance['review_date'])) : 'Not reviewed';
            
            $compliance['created_at_formatted'] = date('F j, Y g:i A', strtotime($compliance['created_at']));
            $compliance['updated_at_formatted'] = date('F j, Y g:i A', strtotime($compliance['updated_at']));

            // Get status badge class
            $statusClass = '';
            switch($compliance['status']) {
                case 'compliant': $statusClass = 'success'; break;
                case 'partial': $statusClass = 'warning'; break;
                case 'non_compliant': $statusClass = 'danger'; break;
                case 'not_assessed': $statusClass = 'secondary'; break;
            }
            $compliance['status_class'] = $statusClass;
            $compliance['status_display'] = ucfirst(str_replace('_', ' ', $compliance['status']));

            // Format score
            $compliance['score_display'] = $compliance['compliance_score'] ? 
                number_format($compliance['compliance_score'], 1) . '%' : 'Not scored';

            return json_encode([
                'status' => 1,
                'data' => $compliance
            ]);

        } catch (PDOException $e) {
            error_log("Database error in get_compliance_details: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred while fetching compliance details.'
            ]);
        }
    }

// Notifications ===================================================   
    function notification_form(){
        try {
            $user_id = $_POST["user_id"];
            $type = $_POST["notification_type"];
            $title = $_POST["title"];
            $message = $_POST["message"];
            $urgent = $_POST["urgent"] ?? '';

            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, title, message, urgent) 
                VALUES (:user_id, :type, :title, :message, :urgent)");
            $stmt->execute([
                'user_id' => $user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'urgent' => $urgent
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Announcement, Announced successfully!'
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occured: ' . $e->getMessage()
            ]);
        }
    }
    function delete_notification_form(){
        try {
            $notification_id = $_POST["notification_id"];
            $stmt = $this->db->prepare("DELETE FROM notifications WHERE notification_id = :notification_id");
            $stmt->execute([
                'notification_id' => $notification_id
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Announcement deleted successfully!'
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0, 
                'message' => 'An error occured: ' . $e->getMessage() 
            ]);
        }
    }

// Assignments ========================================================
    function get_faculty() {
        try {
            if (!isset($_POST['program_id']) || empty($_POST['program_id'])) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Program ID is required'
                ]);
            }
            
            $program_id = $_POST['program_id'];
            
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.first_name, u.last_name, u.user_role, 
                    p.program_name, d.department_name
                FROM users u
                INNER JOIN programs p ON u.program_id = p.program_id
                LEFT JOIN departments d ON u.department_id = d.department_id
                WHERE u.user_role IN ('faculty') 
                AND u.is_active = 'Active'
                AND u.program_id = :program_id
                ORDER BY u.last_name, u.first_name
            ");
            $stmt->execute([
                'program_id' => $program_id
            ]);
            $faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return json_encode([
                'status' => 1,
                'message' => 'Faculty retrieved successfully',
                'data' => $faculty
            ]);
            
        } catch(PDOException $e) {
            return json_encode([
                'status' => 0, 
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    function get_parameters_by_area() {
        try {
            
            if (!isset($_POST['area_id']) || empty($_POST['area_id'])) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Area ID is required'
                ]);
            }
            
            $area_id = $_POST['area_id'];
            
            $stmt = $this->db->prepare("
                SELECT parameter_id, parameter_code, parameter_name 
                FROM accreditation_parameters 
                WHERE area_id = ? 
                ORDER BY parameter_code
            ");
            $stmt->execute([$area_id]);
            $parameters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return json_encode([
                'status' => 1,
                'message' => 'Parameters retrieved successfully',
                'data' => $parameters
            ]);
            
        } catch(PDOException $e) {
            return json_encode([
                'status' => 0, 
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    function get_indicators_by_parameter() {
        try {
            
            if (!isset($_POST['parameter_id']) || empty($_POST['parameter_id'])) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Parameter ID is required'
                ]);
            }
            
            $parameter_id = $_POST['parameter_id'];
            
            $stmt = $this->db->prepare("
                SELECT indicator_id, indicator_code, indicator_description 
                FROM accreditation_indicators 
                WHERE parameter_id = ? 
                ORDER BY indicator_code
            ");
            $stmt->execute([$parameter_id]);
            $indicators = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return json_encode([
                'status' => 1,
                'message' => 'Indicators retrieved successfully',
                'data' => $indicators
            ]);
            
        } catch(PDOException $e) {
            return json_encode([
                'status' => 0, 
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
   function assignment_form() {
        try {
            $cycle_id = $_POST['cycle_id'] ?? '';
            $user_id = $_POST['user_id'] ?? '';
            $faculty_id = $_POST['faculty_id'] ?? '';
            $program_id = $_POST['program_id'] ?? '';
            $status = $_POST['status'] ?? 'not_started';
            
            // DEBUG: Log received data
            error_log("assignment_form POST data: " . print_r($_POST, true));
            
            // Handle indicator_ids - check both formats
            $indicator_ids = [];
            
            if (isset($_POST['indicator_ids']) && is_array($_POST['indicator_ids'])) {
                // FORMAT 1: Array from checkboxes (name="indicator_ids[]")
                $indicator_ids = array_filter(array_map('trim', $_POST['indicator_ids']));
                error_log("Format 1: Array from checkboxes - " . implode(',', $indicator_ids));
                
            } elseif (isset($_POST['indicator_ids']) && is_string($_POST['indicator_ids'])) {
                // FORMAT 2: String from hidden field (name="indicator_ids")
                $indicator_ids_str = trim($_POST['indicator_ids']);
                if (!empty($indicator_ids_str)) {
                    $indicator_ids = array_filter(array_map('trim', explode(',', $indicator_ids_str)));
                }
                error_log("Format 2: String from hidden field - " . $indicator_ids_str);
                
            } elseif (isset($_POST['indicators']) && is_array($_POST['indicators'])) {
                // FORMAT 3: Old format for backward compatibility (name="indicators[]")
                $indicator_ids = array_filter(array_map('trim', $_POST['indicators']));
                error_log("Format 3: Old array format - " . implode(',', $indicator_ids));
            }
            
            // DEBUG: Log processed data
            error_log("assignment_form processed: cycle_id=$cycle_id, faculty_id=$faculty_id, program_id=$program_id, indicator_count=" . count($indicator_ids));
            
            // Validate required fields
            if (empty($cycle_id) || empty($faculty_id) || empty($program_id)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Cycle, Faculty, and Program are required fields'
                ]);
            }
            
            if (empty($indicator_ids)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Please select at least one indicator'
                ]);
            }
            
            // FIXED: Check if the specified cycle exists and is active (lowercase 'active')
            $stmt = $this->db->prepare("
                SELECT * FROM accreditation_cycles c
                INNER JOIN programs p ON c.program_id = p.program_id
                WHERE c.cycle_id = :cycle_id 
                AND p.program_id = :program_id 
                AND c.status = 'active'
            ");
            $stmt->execute([
                ':cycle_id' => $cycle_id,
                ':program_id' => $program_id
            ]);
            $cycleData = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$cycleData){
                // Get the actual status of this cycle for better error message
                $checkStmt = $this->db->prepare("SELECT status FROM accreditation_cycles 
                                                WHERE cycle_id = :cycle_id 
                                                AND program_id = :program_id 
                                                LIMIT 1");
                $checkStmt->execute([
                    ':cycle_id' => $cycle_id,
                    ':program_id' => $program_id
                ]);
                $actualCycle = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                // if($actualCycle){
                //     return json_encode([
                //         'status' => 0,
                //         'message' => 'Cycle is currently ' . $actualCycle['status'] . '. Please select an active cycle!'
                //     ]);
                // } else {
                //     return json_encode([
                //         'status' => 0,
                //         'message' => 'Cycle not found for this program.'
                //     ]);
                // }
            }

            $assigned_count = 0;
            $assignment_ids = [];
            $errors = [];
            
            // Create a separate assignment for EACH indicator
            foreach ($indicator_ids as $indicator_id) {
                // Validate indicator exists
                $indicatorCheck = $this->db->prepare("SELECT indicator_id FROM accreditation_indicators WHERE indicator_id = ?");
                $indicatorCheck->execute([$indicator_id]);
                if (!$indicatorCheck->fetch()) {
                    $errors[] = "Invalid indicator ID: $indicator_id";
                    continue;
                }
                
                // Check if assignment already exists for this combination
                $checkStmt = $this->db->prepare("
                    SELECT assignment_id 
                    FROM assignments 
                    WHERE user_id = :faculty_id 
                    AND indicator_id = :indicator_id
                    AND program_id = :program_id 
                    AND cycle_id = :cycle_id
                    LIMIT 1
                ");
                $checkStmt->execute([
                    ':faculty_id' => $faculty_id,
                    ':indicator_id' => $indicator_id,
                    ':program_id' => $program_id,
                    ':cycle_id' => $cycle_id
                ]);
                
                if ($checkStmt->rowCount() > 0) {
                    // Assignment already exists for this indicator
                    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
                    $errors[] = "Indicator ID $indicator_id already has an assignment";
                    continue;
                }
                
                // Create NEW assignment for this indicator
                $stmt = $this->db->prepare("
                    INSERT INTO assignments 
                    (user_id, indicator_id, program_id, status, cycle_id, assigned_by) 
                    VALUES (:user_id, :indicator_id, :program_id, :status, :cycle_id, :assigned_by)
                ");
                
                $result = $stmt->execute([
                    ':user_id' => $faculty_id,
                    ':indicator_id' => $indicator_id,
                    ':program_id' => $program_id,
                    ':status' => $status,
                    ':cycle_id' => $cycle_id,
                    ':assigned_by' => $user_id
                ]);
                
                if ($result) {
                    $assignment_id = $this->db->lastInsertId();
                    $assignment_ids[] = $assignment_id;
                    $assigned_count++;
                    error_log("Created assignment $assignment_id for indicator $indicator_id");
                } else {
                    $errors[] = "Failed to create assignment for indicator $indicator_id";
                }
            }
            
            if ($assigned_count === 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'No assignments created. ' . implode(' ', $errors)
                ]);
            }

            $stmtNotifi = $this->db->prepare("INSERT INTO notifications (user_id, title, message) VALUES
                (:user_id, 'New Assignment', 'You are assigned to: Review and submit documents for specific accreditation indicators.')");
            $stmtNotifi->execute([
                'user_id' => $faculty_id
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Successfully created ' . $assigned_count . ' assignment(s)' . 
                            (count($errors) > 0 ? '. Note: ' . implode(' ', $errors) : ''),
                'assignment_ids' => $assignment_ids,
                'assigned_count' => $assigned_count
            ]);
            
        } catch (PDOException $e) {
            error_log("assignment_form PDOException: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
    function update_assignment() {
        try {
            $assignment_id = $_POST["assignment_id"] ?? '';
            $faculty_id = $_POST["faculty_id"] ?? '';
            $indicator_id = $_POST["indicator_id"] ?? '';
            $program_id = $_POST["program_id"] ?? '';
            $status = $_POST["status"] ?? 'not_started';
            $cycle_id = $_POST["cycle_id"] ?? '';
            
            // Validate required fields
            if (empty($assignment_id) || empty($faculty_id) || empty($indicator_id) || empty($program_id) || empty($cycle_id)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'All required fields must be filled'
                ]);
            }
            
            // Check if assignment exists
            $checkStmt = $this->db->prepare("SELECT assignment_id FROM assignments WHERE assignment_id = :assignment_id");
            $checkStmt->execute([':assignment_id' => $assignment_id]);
            
            if ($checkStmt->rowCount() === 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Assignment not found'
                ]);
            }
            
            // Update assignment
            $stmt = $this->db->prepare("
                UPDATE assignments 
                SET user_id = :faculty_id,
                    indicator_id = :indicator_id,
                    program_id = :program_id,
                    status = :status
                WHERE assignment_id = :assignment_id
            ");
            
            $stmt->execute([
                ':faculty_id' => $faculty_id,
                ':indicator_id' => $indicator_id,
                ':program_id' => $program_id,
                ':status' => $status,
                ':assignment_id' => $assignment_id
            ]);
            
            return json_encode([
                'status' => 1,
                'message' => 'Assignment updated successfully'
            ]);
            
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
    function delete_assignment_form(){
        try {
            // Get assignment ID from POST
            $assignment_id = $_POST["assignment_id"] ?? '';
            
            // Validate assignment ID
            if (empty($assignment_id)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Assignment ID is required'
                ]);
            }
            
            // Check if assignment exists
            $checkStmt = $this->db->prepare("SELECT assignment_id FROM assignments WHERE assignment_id = :assignment_id");
            $checkStmt->execute([':assignment_id' => $assignment_id]);
            
            if ($checkStmt->rowCount() === 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Assignment not found'
                ]);
            }
            
            // Optional: Check if assignment has any submissions/documents before deleting
            // This prevents accidental deletion of assignments with progress
            $progressStmt = $this->db->prepare("
                SELECT status 
                FROM assignments 
                WHERE assignment_id = :assignment_id 
                AND status IN ('in_progress', 'submitted', 'completed')
            ");
            $progressStmt->execute([':assignment_id' => $assignment_id]);
            
            if ($progressStmt->rowCount() > 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Cannot delete assignment with existing progress. Please update status instead.'
                ]);
            }
            
            $user_idStmt = $this->db->prepare("SELECT user_id FROM assignments WHERE assignment_id = :assignment_id");
            $user_idStmt->execute([':assignment_id' => $assignment_id]);
            $getUser_id = $user_idStmt->fetch(PDO::FETCH_ASSOC);
            $user_id = $getUser_id["user_id"];

            // Delete the assignment
            $stmt = $this->db->prepare("DELETE FROM assignments WHERE assignment_id = :assignment_id");
            $stmt->execute([':assignment_id' => $assignment_id]);
            
            try {
                $notifStmt = $this->db->prepare("DELETE FROM notifications WHERE user_id = :user_id");
                $notifStmt->execute([
                    'user_id' => $user_id
                ]);
            } catch (Exception $e) {
                // Continue even if notification deletion fails
                error_log("Notification cleanup error: " . $e->getMessage());
            }

            return json_encode([
                'status' => 1,
                'message' => 'Assignment deleted successfully'
            ]);
            
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function assign_reviewer_form(){
        try {
            // Get form data from POST
            $document_id = $_POST["document_id"] ?? '';
            $reviewer_id = $_POST["reviewer_id"] ?? '';
            $faculty_id = $_POST["faculty_id"] ?? '';
            $deadline = $_POST["deadline"] ?? '';
            $priority = $_POST["priority"] ?? 'normal';
            
            // Validate required fields
            if (empty($document_id) || empty($reviewer_id)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Document and Reviewer are required'
                ]);
            }
            
            // Validate document exists
            $checkDocStmt = $this->db->prepare("SELECT document_id FROM documents WHERE document_id = :document_id");
            $checkDocStmt->execute([':document_id' => $document_id]);
            
            if ($checkDocStmt->rowCount() === 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Document not found'
                ]);
            }
            
            // Validate reviewer exists
            $checkRevStmt = $this->db->prepare("SELECT user_id FROM users WHERE user_id = :reviewer_id");
            $checkRevStmt->execute([':reviewer_id' => $reviewer_id]);
            
            if ($checkRevStmt->rowCount() === 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Reviewer not found'
                ]);
            }
            
            // Validate date if provided
            if (!empty($deadline)) {
                $date_parts = explode('-', $deadline);
                if (count($date_parts) != 3 || !checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Invalid date format'
                    ]);
                }
            }
            
            // Insert into reviews table
            $stmt = $this->db->prepare("
                INSERT INTO reviews (document_id, reviewer_id, deadline, priority, review_action) 
                VALUES (:document_id, :reviewer_id, :deadline, :priority, 'needs_revision')
            ");
            
            // Convert empty deadline to NULL
            $deadline_db = empty($deadline) ? null : $deadline;
            
            $stmt->execute([
                ':document_id' => $document_id,
                ':reviewer_id' => $reviewer_id,
                ':deadline' => $deadline_db,
                ':priority' => $priority
            ]);

            $stmtNotification = $this->db->prepare("INSERT INTO notifications (user_id, title, message)
            VALUES (:user_id, 'Review Document', 'You have assign to review a document. please view it at Document managament')");
            $stmtNotification->execute([
                'user_id' => $reviewer_id
            ]);
            
            return json_encode([
                'status' => 1,
                'message' => 'Reviewer assigned successfully',
                'review_id' => $this->db->lastInsertId()
            ]);
            
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            return json_encode([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    function get_parent_indicators() {
        try {
            // Check if indicator_id is provided
            if (!isset($_POST['indicator_id']) || empty($_POST['indicator_id'])) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Indicator ID is required'
                ]);
                return;
            }
            
            $indicator_id = $_POST['indicator_id'];
            
            // Get sub-indicators where the selected indicator is the parent
            $stmt = $this->db->prepare("SELECT * FROM accreditation_indicators WHERE parent_indicator_id = :parent_indicator_id ORDER BY indicator_code");
            $stmt->execute(['parent_indicator_id' => $indicator_id]);
            $subIndicators = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 1,
                'data' => $subIndicators,
                'count' => count($subIndicators)
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 0,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

// user_profile =======================================================
    function profile_update() {
        try {
            $user_id = $_POST["user_id"];
            $citizenship = $_POST["citizenship"];
            $civil_Status = $_POST["civil_Status"];
            $birthday = $_POST["birthday"];
            $religion = $_POST["religion"];
            $birth_place = $_POST["birth_place"];
            $contact = $_POST["contact"];
            $block = $_POST["block"];
            $subdivision = $_POST["subdivision"];
            $barangay = $_POST["barangay"];
            $city = $_POST["city"];
            $province = $_POST["province"];
            $zip_code = $_POST["zip_code"];

            // 1. Check if user has an existing record
            $check = $this->db->prepare("SELECT COUNT(*) FROM users_info WHERE user_id = :user_id");
            $check->execute([':user_id' => $user_id]);
            $exists = $check->fetchColumn();

            // If record exists, UPDATE
            if ($exists > 0) {

                $stmt = $this->db->prepare("
                    UPDATE users_info SET
                        citizenship = :citizenship,
                        civil_Status = :civil_Status,
                        birthday = :birthday,
                        religion = :religion,
                        birth_place = :birth_place,
                        contact = :contact,
                        block = :block,
                        subdivision = :subdivision,
                        barangay = :barangay,
                        city = :city,
                        province = :province,
                        zip_code = :zip_code
                    WHERE user_id = :user_id
                ");

            } else {
                // If no record, INSERT new row
                $stmt = $this->db->prepare("
                    INSERT INTO users_info (
                        user_id, citizenship, civil_Status, birthday, religion, birth_place,
                        contact, block, subdivision, barangay, city, province, zip_code
                    ) VALUES (
                        :user_id, :citizenship, :civil_Status, :birthday, :religion, :birth_place,
                        :contact, :block, :subdivision, :barangay, :city, :province, :zip_code
                    )
                ");
            }

            // Bind all values
            $stmt->execute([
                ':user_id' => $user_id,
                ':citizenship' => $citizenship,
                ':civil_Status' => $civil_Status,
                ':birthday' => $birthday,
                ':religion' => $religion,
                ':birth_place' => $birth_place,
                ':contact' => $contact,
                ':block' => $block,
                ':subdivision' => $subdivision,
                ':barangay' => $barangay,
                ':city' => $city,
                ':province' => $province,
                ':zip_code' => $zip_code
            ]);

            return json_encode([
                'status' => 1,
                'message' => $exists > 0 ? 'Profile updated successfully' : 'Profile created successfully'
            ]);

        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

// Program Accreditation Status =======================================
    function GetSummaryCounts() {
        try {
            // Test database connection first
            $testQuery = $this->db->query("SELECT 1 as test");
            if (!$testQuery) {
                throw new Exception("Database connection failed");
            }

            // Initialize summary array - only what frontend needs
            $summary = [
                'level4' => 0,
                'level3' => 0,
                'level2' => 0,
                'level1' => 0
            ];

            // Get counts from program_levels table (this is where accreditation_level is stored)
            $query = "
                SELECT 
                    accreditation_level,
                    COUNT(*) as count
                FROM program_levels
                WHERE accreditation_level IS NOT NULL
                AND accreditation_level != ''
                GROUP BY accreditation_level
            ";

            $stmt = $this->db->query($query);
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $level = $row['accreditation_level'] ?? '';
                    $count = (int)($row['count'] ?? 0);
                    
                    // Map your ENUM values to the frontend expected format
                    if ($level === 'IV' || $level === 'IV(Phase 2)') {
                        $summary['level4'] += $count;
                    } elseif ($level === 'III' || $level === 'III(Phase 2)') {
                        $summary['level3'] += $count;
                    } elseif ($level === 'II') {
                        $summary['level2'] += $count;
                    } elseif ($level === 'I') {
                        $summary['level1'] += $count;
                    }
                }
            }

            // Return JSON response
            return json_encode([
                'status' => 1,
                'data' => $summary,
                'message' => 'Summary counts retrieved successfully'
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            // Return error in JSON format
            return json_encode([
                'status' => 0,
                'data' => null,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    function add_accreditation_form(){
        try {
            // Get POST data
            $program_id = isset($_POST['program_id']) ? $_POST['program_id'] : 
                        (isset($_POST['program_id_select']) ? $_POST['program_id_select'] : null);
            
            if (!$program_id) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Program ID is required'
                ]);
            }
            
            $accreditation_level = $_POST['accreditation_level'] ?? '';
            $survey_date = $_POST['survey_date'] ?? '';
            $accrediting_body = $_POST['accrediting_body'] ?? 'AACCUP';
            $remarks = $_POST['remarks'] ?? '';
            $cycle_id = $_POST['cycle_id'];
            
            // Validate required fields
            if (empty($accreditation_level) || empty($survey_date)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Accreditation level and survey date are required'
                ]);
            }
            
            // Validate program exists and get department info
            $programCheck = $this->db->prepare("
                SELECT p.program_name, p.department_id, d.department_name 
                FROM programs p
                LEFT JOIN departments d ON p.department_id = d.department_id
                WHERE p.program_id = ?
            ");
            $programCheck->execute([$program_id]);
            $program = $programCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$program) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Program not found'
                ]);
            }
            
            // 1. Insert accreditation record (without accreditation_level)
            $stmt = $this->db->prepare("
                INSERT INTO program_accreditation 
                (program_id, cycle_id, survey_date, accrediting_body, remarks)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $program_id,
                $cycle_id,
                $survey_date,
                $accrediting_body,
                $remarks
            ]);
            
            // 2. Update or insert into program_levels table
            $checkLevelStmt = $this->db->prepare("
                SELECT level_id FROM program_levels WHERE program_id = ?
            ");
            $checkLevelStmt->execute([$program_id]);
            $existingLevel = $checkLevelStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingLevel) {
                // Update existing record
                $updateStmt = $this->db->prepare("
                    UPDATE program_levels 
                    SET accreditation_level = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE program_id = ?
                ");
                $updateStmt->execute([$accreditation_level, $program_id]);
            } else {
                // Insert new record
                $insertStmt = $this->db->prepare("
                    INSERT INTO program_levels (program_id, accreditation_level)
                    VALUES (?, ?)
                ");
                $insertStmt->execute([$program_id, $accreditation_level]);
            }
            
            // 3. Check if department exists, insert if not
            $checkDeptStmt = $this->db->prepare("
                SELECT department_id FROM departments WHERE department_id = ?
            ");
            $checkDeptStmt->execute([$program['department_id']]);
            $existingDept = $checkDeptStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingDept && $program['department_id']) {
                // Insert department if it doesn't exist (this shouldn't happen if foreign keys work)
                $insertDeptStmt = $this->db->prepare("
                    INSERT INTO departments (department_id, department_code, department_name)
                    VALUES (?, ?, ?)
                ");
                // You need to get department_code somehow - adjust as needed
                $dept_code = 'DEPT_' . $program['department_id'];
                $insertDeptStmt->execute([
                    $program['department_id'],
                    $dept_code,
                    $program['department_name'] ?? 'Unknown Department'
                ]);
            }
            
            return json_encode([
                'status' => 1,
                'message' => 'Accreditation record added successfully for ' . $program['program_name'] . 
                            ' and accreditation level updated to ' . $accreditation_level
            ]);
            
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function viewProgramHistory(){
        try {
            $program_id = $_POST['program_id'] ?? null;
            
            if (!$program_id) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Program ID is required'
                ]);
            }
            
            // Get program details
            $programStmt = $this->db->prepare("
                SELECT p.*, d.department_name 
                FROM programs p 
                JOIN departments d ON p.department_id = d.department_id 
                WHERE p.program_id = ?
            ");
            $programStmt->execute([$program_id]);
            $program = $programStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$program) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Program not found'
                ]);
            }
            
            // Get accreditation history
            $historyStmt = $this->db->prepare("
                SELECT * 
                FROM program_accreditation 
                WHERE program_id = ? 
                ORDER BY survey_date DESC
            ");
            $historyStmt->execute([$program_id]);
            $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get current level from accreditation_levels table for better display
            $currentLevelInfo = [];
            if (!empty($history)) {
                $latestAccreditation = $history[0];
                $levelStmt = $this->db->prepare("
                    SELECT level_name, description 
                    FROM accreditation_levels 
                    WHERE level_code = ? OR level_name LIKE ?
                ");
                $levelStmt->execute([
                    $latestAccreditation['accreditation_level'],
                    '%' . $latestAccreditation['accreditation_level'] . '%'
                ]);
                $currentLevelInfo = $levelStmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return json_encode([
                'status' => 1,
                'program_name' => $program['program_name'],
                'department_name' => $program['department_name'],
                'current_level' => $currentLevelInfo ? $currentLevelInfo['level_name'] : 
                                  (!empty($history) ? $history[0]['accreditation_level'] : 'Not Accredited'),
                'current_level_desc' => $currentLevelInfo['description'] ?? '',
                'history' => $history,
                'message' => 'Program history retrieved successfully'
            ]);
            
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function viewNextLevelInfo(){
        try {
            $program_id = $_POST['program_id'] ?? null;
            $current_level = $_POST['current_level'] ?? null;
            
            if (!$program_id) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Program ID is required'
                ]);
                return;
            }
            
            // Get program details
            $programStmt = $this->db->prepare("
                SELECT p.*, d.department_name 
                FROM programs p 
                JOIN departments d ON p.department_id = d.department_id 
                WHERE p.program_id = ?
            ");
            $programStmt->execute([$program_id]);
            $program = $programStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$program) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Program not found'
                ]);
                return;
            }
            
            $response = [
                'status' => 1,
                'program_name' => $program['program_name'],
                'department_name' => $program['department_name'],
                'program_code' => $program['program_code'],
                'message' => 'Next level information retrieved successfully'
            ];
            
            // If current level is provided
            if ($current_level && $current_level !== 'not_accredited') {
                // Try to get level info from accreditation_levels table
                $levelStmt = $this->db->prepare("
                    SELECT * FROM accreditation_levels 
                    WHERE level_code = ? OR level_name LIKE ?
                    ORDER BY sort_order
                ");
                $levelStmt->execute([$current_level, '%' . $current_level . '%']);
                $currentLevelInfo = $levelStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($currentLevelInfo) {
                    $response['current_level'] = $currentLevelInfo['level_name'];
                    $response['current_level_desc'] = $currentLevelInfo['description'] ?? '';
                    
                    // Get next level
                    $nextLevelStmt = $this->db->prepare("
                        SELECT * FROM accreditation_levels 
                        WHERE sort_order > ? 
                        ORDER BY sort_order ASC 
                        LIMIT 1
                    ");
                    $nextLevelStmt->execute([$currentLevelInfo['sort_order']]);
                    $nextLevel = $nextLevelStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($nextLevel) {
                        $response['next_level'] = [
                            'level_name' => $nextLevel['level_name'],
                            'level_code' => $nextLevel['level_code'],
                            'description' => $nextLevel['description'] ?? '',
                            'requirements' => $nextLevel['requirements'] ?? '',
                            'sort_order' => $nextLevel['sort_order']
                        ];
                    } else {
                        $response['next_level'] = null;
                    }
                } else {
                    // Current level not in accreditation_levels table
                    $response['current_level'] = $current_level;
                    $response['current_level_desc'] = '';
                    
                    // Get first level as next level
                    $firstLevelStmt = $this->db->query("SELECT * FROM accreditation_levels ORDER BY sort_order LIMIT 1");
                    $firstLevel = $firstLevelStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($firstLevel) {
                        $response['next_level'] = [
                            'level_name' => $firstLevel['level_name'],
                            'level_code' => $firstLevel['level_code'],
                            'description' => $firstLevel['description'] ?? '',
                            'requirements' => $firstLevel['requirements'] ?? '',
                            'sort_order' => $firstLevel['sort_order']
                        ];
                    }
                }
            } else {
                // No current level or not accredited
                $response['current_level'] = 'Not Accredited';
                $response['current_level_desc'] = '';
                
                // Get first level as next level
                $firstLevelStmt = $this->db->query("SELECT * FROM accreditation_levels ORDER BY sort_order LIMIT 1");
                $firstLevel = $firstLevelStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($firstLevel) {
                    $response['next_level'] = [
                        'level_name' => $firstLevel['level_name'],
                        'level_code' => $firstLevel['level_code'],
                        'description' => $firstLevel['description'] ?? '',
                        'requirements' => $firstLevel['requirements'] ?? '',
                        'sort_order' => $firstLevel['sort_order']
                    ];
                }
            }
            
            echo json_encode($response);
            
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    // Additional helper method to get all programs for dropdown
    function getProgramsList(){
        try {
            $stmt = $this->db->query("
                SELECT p.program_id, p.program_code, p.program_name, d.department_name
                FROM programs p
                JOIN departments d ON p.department_id = d.department_id
                ORDER BY d.department_name, p.program_name
            ");
            $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return json_encode([
                'status' => 1,
                'data' => $programs,
                'message' => 'Programs list retrieved successfully'
            ]);
            
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    // Method to get accreditation levels for dropdown
    function getAccreditationLevels(){
        try {
            $stmt = $this->db->query("SELECT * FROM accreditation_levels ORDER BY sort_order");
            $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return json_encode([
                'status' => 1,
                'data' => $levels,
                'message' => 'Accreditation levels retrieved successfully'
            ]);
            
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

// Parameters ===============================================================
    function parameter_form(){
        try {
            $area_id = $_POST["area_id"];
            $parameter_code = $_POST["parameter_code"];
            $parameter_name = $_POST["parameter_name"];

            $stmtParameter = $this->db->prepare("INSERT INTO accreditation_parameters (area_id, parameter_code, parameter_name) VALUES 
            (:area_id, :parameter_code, :parameter_name)");
            $stmtParameter->execute([
                'area_id' => $area_id,
                'parameter_code' => $parameter_code,
                'parameter_name' => $parameter_name
            ]);


            return json_encode([
                'status' => 1,
                'message' => 'Parameter added Successfully!'
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occured: ' . $e->getMessage()
            ]);
        }
    }

// View sub indicators ====================================================================|
    function get_sub_indicators_by_assignment() {
        try {
            $pdo = db_connect();
            
            if (!isset($_POST['assignment_id']) || empty($_POST['assignment_id'])) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Assignment ID is required'
                ]);
            }
            
            $assignment_id = $_POST['assignment_id'];
            
            // Get the main indicator ID from the assignment
            $mainStmt = $this->db->prepare("SELECT indicator_id FROM assignments WHERE assignment_id = ?");
            $mainStmt->execute([$assignment_id]);
            $main_assignment = $mainStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$main_assignment) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Assignment not found'
                ]);
            }
            
            $main_indicator_id = $main_assignment['indicator_id'];
            
            // Method 1: Get sub-indicators from assignment_sub_indicators table
            $stmt = $this->db->prepare("
                SELECT 
                    asi.assignment_sub_id,
                    asi.status,
                    asi.assigned_at,
                    asi.updated_at,
                    ai.indicator_id,
                    ai.indicator_code,
                    ai.indicator_description
                FROM assignment_sub_indicators asi
                INNER JOIN accreditation_indicators ai ON asi.parent_indicator_id = ai.indicator_id
                WHERE asi.assignment_id = ?
                ORDER BY ai.indicator_code
            ");
            $stmt->execute([$assignment_id]);
            $sub_indicators = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no results from assignment_sub_indicators, try Method 2: 
            // Get all indicators that have this as parent from accreditation_indicators
            if (empty($sub_indicators)) {
                $stmt = $this->db->prepare("
                    SELECT 
                        NULL as assignment_sub_id,
                        'not_started' as status,
                        NULL as assigned_at,
                        NULL as updated_at,
                        ai.indicator_id,
                        ai.indicator_code,
                        ai.indicator_description
                    FROM accreditation_indicators ai
                    WHERE ai.parent_indicator_id = ?
                    ORDER BY ai.indicator_code
                ");
                $stmt->execute([$main_indicator_id]);
                $sub_indicators = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return json_encode([
                'status' => 1,
                'message' => 'Sub indicators retrieved successfully',
                'data' => $sub_indicators,
                'main_indicator_id' => $main_indicator_id
            ]);
            
        } catch(PDOException $e) {
            return json_encode([
                'status' => 0, 
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
     public function get_assignment_details() {
        try {
            $assignment_id = $_POST['assignment_id'] ?? '';
            
            if (empty($assignment_id)) {
                return json_encode(['status' => 0, 'message' => 'Assignment ID is required']);
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    a.*,
                    ai.indicator_code,
                    ai.indicator_description,
                    ap.parameter_name,
                    ap.parameter_code,
                    aa.area_number,
                    aa.area_name,
                    ac.cycle_name,
                    ac.academic_year,
                    p.program_code,
                    p.program_name,
                    uf.first_name as faculty_firstname,
                    uf.last_name as faculty_lastname,
                    ua.first_name as assigned_by_firstname,
                    ua.last_name as assigned_by_lastname,
                    a.assigned_at
                FROM assignments a
                LEFT JOIN accreditation_indicators ai ON a.indicator_id = ai.indicator_id
                LEFT JOIN accreditation_parameters ap ON ai.parameter_id = ap.parameter_id
                LEFT JOIN accreditation_areas aa ON ap.area_id = aa.area_id
                LEFT JOIN accreditation_cycles ac ON a.cycle_id = ac.cycle_id
                LEFT JOIN programs p ON a.program_id = p.program_id
                LEFT JOIN users uf ON a.user_id = uf.user_id
                LEFT JOIN users ua ON a.assigned_by = ua.user_id
                WHERE a.assignment_id = :assignment_id
            ");
            
            $stmt->execute([':assignment_id' => $assignment_id]);
            
            if ($stmt->rowCount() > 0) {
                $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
                return json_encode([
                    'status' => 1,
                    'message' => 'Assignment details retrieved successfully',
                    'data' => $assignment
                ]);
            } else {
                return json_encode(['status' => 0, 'message' => 'Assignment not found']);
            }
            
        } catch (PDOException $e) {
            return json_encode(['status' => 0, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    function document_feedback() {
        try {
            // Validate required fields
            if (!isset($_POST["user_id"]) || empty($_POST["user_id"])) {
                return json_encode([
                    'status' => 0,
                    'message' => 'User ID is required'
                ]);
            }
            
            if (!isset($_POST["document_id"]) || empty($_POST["document_id"])) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Document ID is required'
                ]);
            }
            
            if (!isset($_POST["feedback"]) || empty(trim($_POST["feedback"]))) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Feedback cannot be empty'
                ]);
            }
            
            $user_id = (int)$_POST["user_id"];
            $document_id = (int)$_POST["document_id"];
            $feedback = trim($_POST["feedback"]);
            
            // Validate document exists
            $checkStmt = $this->db->prepare("SELECT document_id FROM documents WHERE document_id = ?");
            $checkStmt->execute([$document_id]);
            
            if ($checkStmt->rowCount() === 0) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Document not found'
                ]);
            }
            
            $stmt = $this->db->prepare("INSERT INTO document_feedbacks (user_id, document_id, feedback) VALUES (:user_id, :document_id, :feedback)");
            $stmt->execute([
                'user_id' => $user_id,
                'document_id' => $document_id,
                'feedback' => $feedback
            ]);
            
            $feedback_id = $this->db->lastInsertId();
            
            $stmtNotifi = $this->db->prepare("INSERT INTO notifications
                (user_id, title, message) VALUES
                (:user_id, 'Accreditor comment', :message)");
            $stmtNotifi->execute([
                'user_id' => $user_id,
                'message' => $feedback
            ]);


            return json_encode([
                'status' => 1,
                'message' => 'Feedback sent successfully!',
                'feedback_id' => $feedback_id
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    // Function to get document comments
    public function get_document_comments() {
        try {
            // Debug: Check what data is received
            error_log("get_document_comments called with POST: " . print_r($_POST, true));
            
            if (!isset($_POST['document_id']) || empty($_POST['document_id'])) {
                error_log("Document ID missing");
                return json_encode(['status' => 0, 'message' => 'Document ID is required']);
            }
            
            $document_id = $_POST['document_id'];
            error_log("Looking for comments for document ID: " . $document_id);
            
            // Test if document exists
            $checkStmt = $this->db->prepare("SELECT COUNT(*) as count FROM documents WHERE document_id = ?");
            $checkStmt->execute([$document_id]);
            $docCheck = $checkStmt->fetch(PDO::FETCH_ASSOC);
            error_log("Document exists: " . $docCheck['count']);
            
            if ($docCheck['count'] == 0) {
                return json_encode(['status' => 0, 'message' => 'Document not found']);
            }
            
            // Get QA/Reviewer comments
            $stmt = $this->db->prepare("
                SELECT 
                    r.*,
                    CONCAT(u.first_name, ' ', u.last_name) as reviewer_name,
                    u.user_role
                FROM reviews r
                LEFT JOIN users u ON r.reviewer_id = u.user_id
                WHERE r.document_id = ?
                ORDER BY r.reviewed_at DESC
            ");
            $stmt->execute([$document_id]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($reviews) . " QA/review comments");
            
            // Get Accreditor comments
            $stmt = $this->db->prepare("
                SELECT 
                    df.*,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    u.user_role
                FROM document_feedbacks df
                INNER JOIN users u ON df.user_id = u.user_id
                WHERE df.document_id = ?
                ORDER BY df.created_at DESC
            ");
            $stmt->execute([$document_id]);
            $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($feedbacks) . " accreditor comments");
            

            return json_encode([
                'status' => 1,
                'message' => 'Comments retrieved successfully',
                'data' => [
                    'reviews' => $reviews,
                    'feedbacks' => $feedbacks
                ]
            ]);
            
        } catch(PDOException $e) {
            error_log("Database error in get_document_comments: " . $e->getMessage());
            return json_encode([
                'status' => 0, 
                'message' => 'Database error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch(Exception $e) {
            error_log("General error in get_document_comments: " . $e->getMessage());
            return json_encode([
                'status' => 0, 
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    function mark_all_as_read() {
    try {
            if (!isset($_POST["user_id"]) || empty($_POST["user_id"])) {
                return json_encode([
                    'status' => 0,
                    'message' => 'User ID is required'
                ]);
            }
            
            $user_id = (int)$_POST["user_id"];
            
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
            $stmt->execute([
                'user_id' => $user_id
            ]);
            
            $affectedRows = $stmt->rowCount();

            return json_encode([
                'status' => 1,
                'message' => $affectedRows . ' notification(s) marked as read',
                'affected_rows' => $affectedRows
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function unread_count(){
        try {
            $user_id = $_POST["user_id"];

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND user_id = :user_id");
            $stmt->execute([
                'user_id' => $user_id
            ]);
            $dataCount = $stmt->fetchColumn();
            
            return json_encode([
                'status' => 1,
                'data' => $dataCount
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occured: ' . $e->getMessage()
            ]);
        }
    }
    
    function getCyclesByPorgram(){
        if (!isset($_POST['program_id']) || empty($_POST['program_id'])) {
        echo json_encode(['status' => 0, 'message' => 'Program ID is required']);
            exit;
        }
        
        $program_id = (int)$_POST['program_id'];
        try {
        $stmt = $this->db->prepare("
            SELECT cycle_id, cycle_name, academic_year 
            FROM accreditation_cycles 
            WHERE program_id = :program_id 
            ORDER BY academic_year DESC
        ");
        $stmt->execute([':program_id' => $program_id]);
        $cycles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 1,
            'cycles' => $cycles
        ]);
        } catch (PDOExcedption $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occured: ' . $e->getMessage()
            ]);
        }
    }

}   
