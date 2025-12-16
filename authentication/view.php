<?php
session_start();
require_once 'config.php';

function getEmployees(){
    $pdo = db_connect();
    if($_SESSION['facultyData']['user_id'] ?? ''){
        $user_id = $_SESSION['facultyData']['user_id'] ?? '';
    }
    else if($_SESSION['QAData']['user_id'] ?? '') {
        $user_id = $_SESSION['QAData']['user_id'] ?? '';
    }
    else if($_SESSION['headData']['user_id'] ?? '') {
        $user_id = $_SESSION['headData']['user_id'] ?? '';
    }else if($_SESSION['department_headData']['user_id'] ?? '') {
        $user_id = $_SESSION['department_headData']['user_id'] ?? '';
    }else if($_SESSION['accreditorData']['user_id'] ?? '') {
        $user_id = $_SESSION['accreditorData']['user_id'] ?? '';
    }else{
        $user_id = $_GET["id"] ?? '';   
    }
    $query = "SELECT programs.*, users.*, departments.*, users_info.* FROM users
    INNER JOIN programs ON users.program_id = programs.program_id
    INNER JOIN departments ON users.department_id = departments.department_id
    LEFT JOIN users_info ON users.user_id = users_info.user_id
    WHERE users.user_id = '$user_id'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $employee_data = $stmt->fetch(PDO::FETCH_ASSOC);
    return ['employee_data' => $employee_data];
}