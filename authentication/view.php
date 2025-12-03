<?php
// session_start();
require_once 'config.php';

function getEmployees(){
    $pdo = db_connect();
    $user_id = $_GET["id"] ?? 'hakdok';
    $query = "SELECT programs.*, users.*, departments.* FROM users
    INNER JOIN programs ON users.program_id = programs.program_id
    INNER JOIN departments ON users.department_id = departments.department_id
    WHERE users.user_id = '$user_id'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $employee_data = $stmt->fetch(PDO::FETCH_ASSOC);
    return ['employee_data' => $employee_data];
}