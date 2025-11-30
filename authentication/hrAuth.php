<?php
session_start();
require_once 'config.php';

// TO AVOID JAVA SCRIPT SCRIPTING =========================================================
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: eror.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // DEPARTMENTS AND JOB TITLES AREA=====================================================

    //DEPARTMENT AUTH
    if(isset($_POST["deprtament_auth"]) && $_POST["deprtament_auth"] == "true"){
        $name = $_POST["department_name"] ?? '';
        $code = $_POST["department_code"] ?? '';
        $dept_auth_type = $_POST["dept_auth_type"];

        try {
            if($dept_auth_type == "add"){
                $query = "SELECT Department_name, Department_code FROM departments
                    WHERE Department_name = '$name' AND Department_code = '$code'";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $department_name = $result["Department_name"] ?? '';
                $department_code = $result["Department_code"] ?? '';
                
                if($department_name == $name){
                    header("Location: ../src/UI-HR/index.php?page=contents/dept_job&add&dept=exist");
                    exit;
                }
                if($department_code == $code){
                    header("Location: ../src/UI-HR/index.php?page=contents/dept_job&add&dept=exist");
                    exit;
                }

                $query = "INSERT INTO departments (Department_name, Department_code) VALUES (:Department_name, :Department_code);";
                $stmt = $pdo->prepare($query);
                $stmt->execute(['Department_name' => $name, 'Department_code' => $code]);
                
                header("Location: ../src/UI-HR/index.php?page=contents/dept_job&add&add_dept=success");
                $stmt = null;
                $stmt = null;
                
                exit;

            }else if($dept_auth_type == "edit"){
                echo 'No funtion Yet';
            }else if($dept_auth_type == "delete"){
                $department_id = $_POST["department_id"];
                $query = "DELETE FROM departments WHERE Department_id = '$department_id'";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                header("Location: ../src/UI-HR/index.php?page=contents/dept_job&add&delete_dept=success");
                $stmt = null;
                $stmt = null;
                
                exit;
            }   
            
        } catch (PDOException $e) {
            die("Query Failed: " . $e->getMessage());
        }
    }
    //JOB TITLE AUTH
    if(isset($_POST["jobTitle_auth"]) && $_POST["jobTitle_auth"] == "true"){
        $job = $_POST["jobTitle"] ?? '';
        $salary = $_POST["salary"] ?? '';
        $job_auth_type = $_POST["job_auth_type"];
        try {
            if($job_auth_type == "add"){
                 $query = "SELECT jobTitle FROM jobtitles WHERE jobTitle = :job";
                $stmt = $pdo->prepare($query);
                $stmt->execute(['job' => $job]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Check if any row was returned
                if($result !== false){
                    header("Location: ../src/UI-HR/index.php?page=contents/dept_job&job=exist");
                    exit;
                }
                
                $query = "INSERT INTO jobtitles (jobTitle, salary) VALUES (:jobTitle, :salary);";
                $stmt = $pdo->prepare($query);
                $stmt->execute(['jobTitle' => $job, 'salary' => $salary]);
                
                header("Location: ../src/UI-HR/index.php?page=contents/dept_job&add&add-jobTitle=success");
                $stmt = null;
                $pdo = null;
                exit;
            }else if($job_auth_type == "edit"){

            }else if($job_auth_type == "delete"){
                $jobTitles_id = $_POST["jobTitles_id"];
                $query = "DELETE FROM jobtitles WHERE jobTitles_id = '$jobTitles_id'";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                header("Location: ../src/UI-HR/index.php?page=contents/dept_job&delete_job=success");
                $pdo = null;
                $stmt = null;
                
                exit;
            }
            
           

        } catch (PDOException $e) {
            die("Query Failed: " . $e->getMessage());
        }
    }

    // CAREER PATH AUTH
    if(isset($_POST["careerPath_auth"]) && $_POST["careerPath_auth"] == "true"){
        $careerPath_auth_type = $_POST["careerPath_auth_type"] ?? 'walang laman';
        $jobTitle = $_POST["jobTitle"] ?? 'no data';
        // $new_salary = $_POST["new_salary"] ?? '1.00';
        $employee_id = $_POST["employee_id"] ?? 'no data';
        $currentJobTitle = $_POST["currentJobTitle"];
        $currentSalary = $_POST["currentSalary"];
        $admin_id = $_POST["admin_id"];

        $stmtNewSalary = $pdo->prepare("SELECT salary FROM jobTitles WHERE jobTitles_id = :jobTitles_id");
        $stmtNewSalary->execute([
            'jobTitles_id' => $jobTitle
        ]);
        $salaryNew = $stmtNewSalary->fetch(PDO::FETCH_ASSOC);
        $new_salary = $salaryNew["salary"];
        try {
            if($careerPath_auth_type == "Update"){
                $query = "INSERT INTO job_history (employee_id, job_from, job_to, current_salary, new_salary, job_status) 
                    VALUES ('$employee_id', '$currentJobTitle', '$jobTitle', '$currentSalary', '$new_salary', 'update')";
                $stmt = $pdo->prepare($query);
                $stmt->execute();

                $query = "UPDATE hr_data SET jobtitle_id = :jobtitle_id, salary = :salary WHERE employee_id = :employee_id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'salary' => $new_salary,
                    'employee_id' => $employee_id,
                    'jobtitle_id' => $jobTitle
                ]);
                if($admin_id){
                    header("Location: ../src/UI-Admin/index.php?page=contents/dept_job&update_employee=success");
                    $pdo = null;
                    $stmt = null;
                    exit;
                }else{
                    header("Location: ../src/UI-HR/index.php?page=contents/dept_job&update_employee=success");
                    $pdo = null;
                    $stmt = null;
                    exit;
                }
                
            }else if($careerPath_auth_type == "Promote"){
                $query = "INSERT INTO job_history (employee_id, job_from, job_to, current_salary, new_salary, job_status) 
                    VALUES ('$employee_id', '$currentJobTitle', '$jobTitle', '$currentSalary', '$new_salary', 'Promote')";
                $stmt = $pdo->prepare($query);
                $stmt->execute();

                $query = "UPDATE hr_data SET jobtitle_id = :jobtitle_id, salary = :salary WHERE employee_id = :employee_id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'salary' => $new_salary,
                    'employee_id' => $employee_id,
                    'jobtitle_id' => $jobTitle
                ]);
                if($admin_id){
                    header("Location: ../src/UI-Admin/index.php?page=contents/dept_job&promote_employee=success");
                    $pdo = null;
                    $stmt = null;
                    exit;
                }else{
                    header("Location: ../src/UI-HR/index.php?page=contents/dept_job&promote_employee=success");
                    $pdo = null;
                    $stmt = null;
                    exit;
                }
            }else if($careerPath_auth_type == "Demote"){
                $query = "INSERT INTO job_history (employee_id, job_from, job_to, current_salary, new_salary, job_status) 
                    VALUES ('$employee_id', '$currentJobTitle', '$jobTitle', '$currentSalary', '$new_salary', 'Demote')";
                $stmt = $pdo->prepare($query);
                $stmt->execute();

                $query = "UPDATE hr_data SET jobtitle_id = :jobtitle_id, salary = :salary WHERE employee_id = :employee_id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'salary' => $new_salary,
                    'employee_id' => $employee_id,
                    'jobtitle_id' => $jobTitle
                ]);
                if($admin_id){
                    header("Location: ../src/UI-Admin/index.php?page=contents/dept_job&demote_employee=success");
                    $pdo = null;
                    $stmt = null;
                    exit;
                }else{
                    header("Location: ../src/UI-HR/index.php?page=contents/dept_job&demote_employee=success");
                    $pdo = null;
                    $stmt = null;
                    exit;
                }
            }
        } catch (PDOException $e) {
            die("Query Failed: " . $e->getMessage());
        }
    }

    // RECRUITMENTS AREA=====================================================

    // EMPLOYEE AUTH
    if(isset($_POST["employee_auth"]) && $_POST["employee_auth"] == "true"){
        $employeeID = $_POST["employeeID"];
        $employee_auth_type = $_POST["employee_auth_type"];
        try {
            if($employee_auth_type == "approve"){
                 $query = "UPDATE employee_data SET user_request = 'Validated' WHERE employee_id = '$employeeID'";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                header("Location: ../src/UI-HR/index.php?page=contents/recruitment&employee-approved=success");
                $pdo = null;
                $stmt = null;
                        
                exit;
            }else if($employee_auth_type == "reject"){
                 $query = "UPDATE employee_data SET user_request = 'Rejected' WHERE employee_id = '$employeeID'";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                header("Location: ../src/UI-HR/index.php?page=contents/recruitment&employee-rejected=success");
                $pdo = null;
                $stmt = null;
                        
                exit;
            } 
        } catch (PDOException $e) {
            die("Query Failed: " . $e->getMessage());
        }
    }

    if(isset($_POST["update_notification"]) && $_POST["update_notification"] == "true"){
        $admin_id = $_POST["admin_id"] ?? '';
        if($admin_id){
            $stmt = $pdo->prepare("UPDATE notifications SET status = 'Inactive' WHERE type = 'ADMIN'");
            $stmt->execute();
            header("Location: ../src/UI-Admin/index.php?page=contents/leave");
            $pdo = null;
            $stmt = null;
        }else{
            $stmt = $pdo->prepare("UPDATE notifications SET status = 'Inactive' WHERE type = 'HR'");
            $stmt->execute();
            header("Location: ../src/UI-HR/index.php?page=contents/leave");
            $pdo = null;
            $stmt = null;
        }
        
    }

    

    unset($_SESSION['csrf_token']);
}