<?php
header('Content-Type: application/json');
/* header('x-powered-by : PHP/8.0.30'); */
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';

include 'admin_class.php';

$crud = new Action();


if ($action === 'login') {

	$login = $crud->login();

	if ($login) {
		echo $login;
	}
}

if ($action === 'logout') {
	$logout = $crud->logout();

	if ($logout) {
		echo $logout;
	}
}

if ($action === 'forget_account_form') {
	$validation = $crud->forget_account_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'changePassword_form') {
	$validation = $crud->changePassword_form();
	if ($validation) {
		echo $validation;
	}
}

if ($action === 'changePassword_verification_form') {
	$validation = $crud->changePassword_verification_form();
	if ($validation) {
		echo $validation;
	}
}
// ACCOUNT MANAGEMENT ===========================================
if ($action === 'register_form') {
	$validation = $crud->register_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'rejection_form') {
	$validation = $crud->rejection_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'approval_form') {
	$validation = $crud->approval_form();
	if ($validation) {
		echo $validation;
	}
}

// DEPARTMENTS AND PROGRAMS ==================================== 
if ($action === 'department_form') {
	$validation = $crud->department_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'edit_department') {
	$validation = $crud->edit_department();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'delete_department') {
	$validation = $crud->delete_department();
	if ($validation) {
		echo $validation;
	}
}

if ($action === 'programs_form') {
	$validation = $crud->programs_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'update_programs') {
	$validation = $crud->update_programs();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'delete_program') {
	$validation = $crud->delete_program();
	if ($validation) {
		echo $validation;
	}
}

// Accreditation Framework ====================================
if ($action === 'area_form') {
	$validation = $crud->area_form();
	if ($validation) {
		echo $validation;
	}
}
?>