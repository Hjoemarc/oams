<?php
header('Content-Type: application/json; charset=utf-8');
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
if ($action === 'activate_cycle_form') {
	$validation = $crud->activate_cycle_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'close_cycle_form') {
	$validation = $crud->close_cycle_form();
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
if ($action === 'approve_document_form') {
	$validation = $crud->approve_document_form();
	if ($validation) {
		echo $validation;
	}
}
if ($action === 'revise_document_form') {
	$validation = $crud->revise_document_form();
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

// Assignment ======================================================
if ($action === 'get_faculty') {
    $validation = $crud->get_faculty();
    if ($validation) {
        echo $validation;
    }
}
if ($action === 'get_parameters_by_area') {
    $validation = $crud->get_parameters_by_area();
    if ($validation) {
        echo $validation;
    }
}
if ($action === 'get_indicators_by_parameter') {
    $validation = $crud->get_indicators_by_parameter();
    if ($validation) {
        echo $validation;
    }
}
if ($action === 'assignment_form') {
    $validation = $crud->assignment_form();
    if ($validation) {
        echo $validation;
    }
}
if ($action === 'update_assignment') {
    $result = $crud->update_assignment();
    echo $result;
}
if ($action === 'delete_assignment_form') {
    $result = $crud->delete_assignment_form();
    echo $result;
}
if ($action === 'assign_reviewer_form') {
    $result = $crud->assign_reviewer_form();
    echo $result;
}
if ($action === 'get_parent_indicators') {
    $result = $crud->get_parent_indicators();
    echo $result;
}



// user_profile =======================================================
if ($action === 'profile_update') {
    $result = $crud->profile_update();
    echo $result;
}

// Program Accreditation Status =======================================
if ($action === 'GetSummaryCounts') {
    $result = $crud->GetSummaryCounts();
    echo $result;
    exit;
}

if ($action === 'add_accreditation_form') {
    $result = $crud->add_accreditation_form();
    echo $result;
    exit;
}

if ($action === 'viewProgramHistory') {
    $result = $crud->viewProgramHistory();
    echo $result;
    exit;
}

if ($action === 'viewNextLevelInfo') {
    $result = $crud->viewNextLevelInfo();
    echo $result;
    exit;
}


// Parameter =================================================
if ($action === 'parameter_form') {
    $result = $crud->parameter_form();
    echo $result;
    exit;
}

// view sub indicators ===============================================
if ($action === 'get_sub_indicators_by_assignment') {
    echo get_sub_indicators_by_assignment();
    exit();
}

if ($action === 'get_assignment_details') {
    $result = $crud->get_assignment_details();
    echo $result;
    exit;
}

// docuemnt feedbacks ===================================================
if ($action === 'document_feedback') {
    $result = $crud->document_feedback();
    echo $result;
    exit;
}
if ($action === 'get_document_comments') {
    $result = $crud->get_document_comments();
    echo $result;
    exit;
}
if ($action === 'mark_all_as_read') {
    $result = $crud->mark_all_as_read();
    echo $result;
    exit;
}
if ($action === 'unread_count') {
    $result = $crud->unread_count();
    echo $result;
    exit;
}
if ($action === 'getCyclesByPorgram') {
    $result = $crud->getCyclesByPorgram();
    echo $result;
    exit;
}
?>