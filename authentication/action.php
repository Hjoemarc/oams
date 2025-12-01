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
if ($action === 'edit_area_form') {
	$validation = $crud->edit_area_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'delete_area_form') {
	$validation = $crud->delete_area_form();
	if ($validation) {
		echo $validation;
	}
}

if ($action === 'indicator_form') {
	$validation = $crud->indicator_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'edit_indicator_form') {
	$validation = $crud->edit_indicator_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'delete_indicator_form') {
	$validation = $crud->delete_indicator_form();
	if ($validation) {
		echo $validation;
	}
}

// Accreditation Cycles  =================================================
if ($action === 'cycle_form') {
	$validation = $crud->cycle_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'edit_cycle_form') {
	$validation = $crud->edit_cycle_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'delete_cycle_form') {
	$validation = $crud->delete_cycle_form();
	if ($validation) {
		echo $validation;
	}
}

// Document Management ==================================================
if ($action === 'document_form') {
	$validation = $crud->document_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'edit_document_form') {
	$validation = $crud->edit_document_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'delete_document_form') {
	$validation = $crud->delete_document_form();
	if ($validation) {
		echo $validation;
	}
}

// Compliance Tracking ===================================================
if ($action === 'add_compliance_form') {
	$validation = $crud->add_compliance_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'edit_compliance_form') {
	$validation = $crud->edit_compliance_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'delete_compliance_form') {
	$validation = $crud->delete_compliance_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'get_compliance_details') {
    $validation = $crud->get_compliance_details();
    if ($validation) {
        echo $validation;
    }
}

// Notifications ===================================================   
if ($action === 'notification_form') {
    $validation = $crud->notification_form();
    if ($validation) {
        echo $validation;
    }
}
if ($action === 'delete_notification_form') {
    $validation = $crud->delete_notification_form();
    if ($validation) {
        echo $validation;
    }
}
?>