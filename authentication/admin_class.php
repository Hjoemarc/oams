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


    function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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
                    return json_encode([
                        'status' => 1,
                        'message' => 'Login successful.',
                        'redirect_url' => 'src/UI-Admin/index.php',
                        'user_role' => $employees['first_name'] . " " . $employees['last_name']
                    ]);
                } else {
                    return json_encode(['status' => 0, 'message' => 'Incorrect password.']);
                }
            }else if($employees['user_role'] === 'QA' && $employees["is_active"] === 'Active'){
                if (password_verify($password, $employees['password'])) {
                    $_SESSION['QAData'] = [
                        'first_name' => $employees['first_name'],
                        'last_name' => $employees['last_name'],
                        'user_role' => $employees['user_role'],
                        'user_id' => $employees['user_id']
                    ];
                    return json_encode([
                        'status' => 1,
                        'message' => 'Login successful.',
                        'redirect_url' => 'src/UI-QA/index.php',
                        'user_role' => $employees['first_name'] . " " . $employees['last_name']
                    ]);
                } else {
                    return json_encode(['status' => 0, 'message' => 'Incorrect password.']);
                }
            }else if($employees['user_role'] === 'head' && $employees["is_active"] === 'Active'){
                if (password_verify($password, $employees['password'])) {
                    $_SESSION['employeeData'] = [
                        'first_name' => $employees['first_name'],
                        'last_name' => $employees['last_name'],
                        'user_role' => $employees['user_role'],
                        'user_id' => $employees['user_id']
                    ];
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
                    return json_encode([
                        'status' => 1,
                        'message' => 'Login successful.',
                        'redirect_url' => 'src/UI-Accreditor/index.php',
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
    
    // ACCOUNT MANAGEMENT ========================================================
    function register_form(){
        try {
            // Get form data
            $lastName = $_POST["lastName"];
            $firstName = $_POST["firstName"];
            $middleName = $_POST["middleName"];
            $suffix = $_POST["suffix"];
            $gender = $_POST["gender"];
            $email = $_POST["email"];
            $username = $_POST["username"];
            $password = $_POST["password"];
            $cpassword = $_POST["cpassword"];
            $is_active = $_POST["is_active"];
            $user_role = $_POST["user_role"];
            
            // Validation checks
            if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password)) {
                return json_encode(['status' => 0, 'message' => 'All required fields must be filled.']);
            }
            
            if ($password !== $cpassword) {
                return json_encode(['status' => 0, 'message' => 'Passwords do not match.']);
            }
            
            if (strlen($password) < 6) {
                return json_encode(['status' => 0, 'message' => 'Password must be at least 6 characters long.']);
            }
            
            // Check if username already exists
            $stmt = $this->db->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->rowCount() > 0) {
                return json_encode(['status' => 0, 'message' => 'Username or email already exists.']);
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into database
            $stmt = $this->db->prepare("INSERT INTO users (
                username, email, password, first_name, last_name, middle_name, 
                suffix, gender, user_role, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $username,
                $email,
                $hashedPassword,
                $firstName,
                $lastName,
                $middleName,
                $suffix,
                $gender,
                $user_role,
                $is_active
            ]);
            
            return json_encode([
                'status' => 1, 
                'message' => 'Registration successful!.'
            ]);
            
        } catch (Exception $e) {
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
    
  
}