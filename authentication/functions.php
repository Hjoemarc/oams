<?php
include "config.php";

$pdo = db_connect();

function initInstaller()
{
    $pdo = db_connect();

    try {
        // Check if admin exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_role = :user_role");
        $stmt->execute(['user_role' => 'admin']);
        $adminCount = $stmt->fetchColumn();

        // Get clean current path
        $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $installerPath = '/installation';

        if ($adminCount > 0) {
            if (strpos($currentPath, $installerPath) === 0) {
                header("Location: " . base_url() . "src/");
                exit;
            }
        } else {
            if (strpos($currentPath, $installerPath) !== 0) {
                header("Location: " . base_url() . "src/");
                exit;
            }
        }

    } catch (PDOException $e) {
        die("Installer check failed: " . $e->getMessage());
    }
    
    $pdo = null;
}



// function base_url()
// {
//     $pdo = db_connect();


//     if (isset($_SERVER['HTTPS'])) {
//         $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
//     } else {
//         $protocol = 'http';
//     }

//     $whitelist = array(
//         '127.0.0.1',
//         '::1'
//     );

//     if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
//         return $base_url = $protocol . "://" . $_SERVER['SERVER_NAME'] . '/zclient/';
//     }
//     return $base_url = $protocol . "://" . $_SERVER['SERVER_NAME'] . '/';

// }
function base_url()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $path = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'))[0];
    return $protocol . '://' . $_SERVER['SERVER_NAME'] . '/' . $path . '/';
}
// function base_url()
// {
//     $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
//     $host = $_SERVER['HTTP_HOST'];

//     $isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

//     if ($isLocal) {
//         return $protocol . '://' . $host . '/Zclient/';
//     } else {
//         return $protocol . '://' . $host . '/';
//     }
// }


function get_current_page()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];

    return $protocol . '://' . $host . $uri;
}

function render_styles()
{

    $styles = [
        // base_url() . 'assets/css/all.min.css',
        base_url() . 'assets/css/custom-bs.min.css',
        base_url() . 'assets/css/main.css',
        base_url() . 'assets/css/icons.min.css',
        base_url() . 'assets/css/morris.css',
        base_url() . 'assets/css/hr.css',
        base_url() . 'assets/css/graphs.css',
        base_url() . 'assets/css/navs.css',
        base_url() . 'assets/css/dataTables.min.css'
    ];

    foreach ($styles as $style) {
        echo '<link rel="stylesheet" href="' . $style . '">';
    }

}

function render_json()
{

    $json = [base_url() . '../templates/manifest.json'];

    foreach ($json as $jsons) {
        echo '<link rel="manifest" href="' . $jsons . '">';
    }

}

function render_scripts()
{

    $scripts = [
        base_url() . 'assets/js/jquery.min.js',
        base_url() . 'assets/js/plugins/perfect-scrollbar.min.js',
        base_url() . 'assets/js/smooth-scrollbar.min.js',
        base_url() . 'assets/js/sweetalert.min.js',
        base_url() . 'assets/js/all.min.js',
        base_url() . 'assets/js/bootstrap.min.js',
        base_url() . 'assets/js/custom-bs.js',
        base_url() . 'assets/js/main.js',
        base_url() . 'assets/js/hr-frontend.js',
        base_url() . 'assets/js/chart.js',
        base_url() . 'assets/js/raphael.min.js',
        base_url() . 'assets/js/morris.min.js',
        base_url() . 'assets/js/jquery-3.7.1.min.js',
        base_url() . 'assets/js/dataTables.min.js',
        base_url() . 'assets/js/custom_table.js'
    ];

    foreach ($scripts as $script) {
        echo '<script type="text/javascript" src="' . $script . '"></script>';
    }

}

function get_employee($key, $id)
{
    try {
        $pdo = db_connect();

        $stmt = $pdo->prepare("SELECT * FROM employee_data WHERE employee_id = ? ");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['' . $key . ''];
        }
        return '';

    } catch (PDOException $e) {
        error_log("Database error in get_option(): " . $e->getMessage());
        return '';
    }
}


function verify_init($id)
{
    try {
        $pdo = db_connect();

        $stmt = $pdo->prepare("SELECT employee_status FROM employee_data WHERE employee_id = ?");
        $stmt->execute([$id]);

        $status = $stmt->fetchColumn(); // returns just the employee_status value

        if ($status === 'pending') {
            $stmt2 = $pdo->prepare("SELECT employee_no FROM employee_data WHERE employee_id = ?");
            $stmt2->execute([$id]);
            $employeeNo = $stmt2->fetchColumn();

            $_SESSION['email_queue'] = $employeeNo;
            header('Location: ../../authentication/verification_form.php');
            exit;
        }

    } catch (PDOException $e) {
        error_log("Database error in verify_init(): " . $e->getMessage());
        return '';
    }
}


function get_family_data($key, $id)
{
    try {
        $pdo = db_connect();

        $stmt = $pdo->prepare("SELECT * FROM family_data WHERE employee_id = ? ");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['' . $key . ''] ?? 'No data';
        }
        return '';

    } catch (PDOException $e) {
        error_log("Database error in get_option(): " . $e->getMessage());
        return '';
    }
}

function get_employee_count($user_role)
{
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM employee_data WHERE user_role = ?");
        $stmt->execute([$user_role]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;

    } catch (PDOException $e) {
        error_log("Database error in get_employee_count(): " . $e->getMessage());
        return 0;
    }
}

function get_total()
{
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM employee_data");
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;

    } catch (PDOException $e) {
        error_log("Database error in get_employee_count(): " . $e->getMessage());
        return 0;
    }
}

function get_countDepartment($key)
{
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM employee_data WHERE department = ?");
        $stmt->execute([$key]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;

    } catch (PDOException $e) {
        error_log("Database error in get_employee_count(): " . $e->getMessage());
        return 0;
    }
}

function get_countStatus($key)
{
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM employee_data WHERE employee_status = ?");
        $stmt->execute([$key]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['total'] : 0;

    } catch (PDOException $e) {
        error_log("Database error in get_employee_count(): " . $e->getMessage());
        return 0;
    }
}

function get_annual($key, $employee_id)
{
    try {
        $pdo = db_connect();

        // Safely allow only specific aggregate queries
        $allowed_keys = [
            'SUM(emp.annual_salary)',
            'SUM(pd.payslip_net_pay)',
            'SUM(e.current_pay)',
            'SUM(d.deduction_current)',
            '(SUM(e.hour_pay * e.rate_pay) + SUM(e.current_pay)) - SUM(d.deduction_current)',
            'SUM(e.hour_pay * e.rate_pay)',
            'SUM(e.current_pay)',
            '(SUM(e.hour_pay * e.rate_pay) + SUM(e.current_pay))'
        ];

        if (!in_array($key, $allowed_keys)) {
            return 0;  // Prevent unsafe SQL execution
        }

        /* NET PAY */
        if($key == '(SUM(e.hour_pay * e.rate_pay) + SUM(e.current_pay)) - SUM(d.deduction_current)'){
             $query = 'SELECT 
           (SUM(e.hour_pay * e.rate_pay) + SUM(e.current_pay)) - SUM(d.deduction_current) AS result
            FROM payslip_data pd
            INNER JOIN earning_data e ON pd.earning_id = e.earning_id
            INNER JOIN deduction_data d ON pd.deduction_id = d.deduction_id
            INNER JOIN employee_data emp ON pd.employee_id = emp.employee_id
            WHERE emp.employee_id = ?';
        }

        /* GROSS NET PAY */
        if ($key == '(SUM(e.hour_pay * e.rate_pay) + SUM(e.current_pay))') {
            $query = "
            SELECT $key AS result
            FROM earning_data e
            WHERE e.employee_id = ?
        ";
        }
        /* TOTAL DEDUCTION */
        if ($key == 'SUM(d.deduction_current)') {
            $query = "
            SELECT $key AS result
            FROM deduction_data d
            WHERE d.employee_id = ?
        ";


        }

        $stmt = $pdo->prepare($query);
        $stmt->execute([$employee_id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($row['result']) ? (float) $row['result'] : 0;

    } catch (PDOException $e) {
        error_log("Error in get_annual(): " . $e->getMessage());
        return 0;
    }
}









?>