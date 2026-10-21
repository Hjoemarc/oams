<?php
header('Content-Type: application/json');
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';

include 'payroll_class.php';

$crud = new Payroll();

switch ($action) {

    case 'test':
        $installer = $crud->test();
        if ($installer) {
            echo $installer;
        }
        break;

    case 'biometric_check':
        $installer = $crud->biometric_check();
        if ($installer) {
            echo $installer;
        }
        break;
    case 'get_payroll_data':
        $installer = $crud->get_payroll_data();
        if ($installer) {
            echo $installer;
        }
        break;
    case 'get_employee_details':
        $installer = $crud->get_employee_details();
        if ($installer) {
            echo $installer;
        }
        break;


    // optional default
    default:
        echo "Invalid action";
        break;
}



?>