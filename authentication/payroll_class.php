<?php
session_start();


class Payroll
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


    function test()
    {
        return json_encode(["message" => "Payroll class is working!"]);
    }

    function biometric_check()
    {

        return json_encode(["message" => "Biometric check executed."]);
    }

    function payroll_process()
    {
        return json_encode(["message" => "Payroll process executed."]);
    }

    function get_payroll_data()
    {
        try {
            $stmt = $this->db->prepare("
            SELECT 
                h.hr_data_id,
                h.created_at,
                h.updated_at,
                h.employee_id,
                h.employeeID,
                e.firstname,
                e.middlename,
                e.lastname,
                j.jobTitle AS position,
                h.gross_pay,
                h.deduction_pay,
                h.net_pay
            FROM hr_data h
            JOIN employee_data e ON h.employee_id = e.employee_id
            JOIN jobTitles j ON h.jobtitle_id = j.jobTitles_id
        ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                "status" => 1,
                "data" => $results
            ]);
        } catch (PDOException $e) {
            return json_encode([
                "status" => 0,
                "data" => [],
                "error" => $e->getMessage()
            ]);
        }
    }

    function get_employee_details()
    {
        $hr_data_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        try {

            $result = [];
            $stmt = $this->db->prepare("
            SELECT 
               *
            FROM hr_data h
            JOIN employee_data e ON h.employee_id = e.employee_id
            JOIN jobTitles j ON h.jobtitle_id = j.jobTitles_id
            JOIN departments d ON h.Department_id = d.Department_id
            WHERE h.employee_id = ?  ");

            $stmt->execute([$hr_data_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare(" SELECT * FROM deduction_data WHERE employee_id = ? ");
            $stmt->execute([$hr_data_id]);
            $deductions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($deductions as &$deduction) {
                $deduction['current_pay'] = ($deduction['deduction_current'] ?? 0);
            }
            $result['deductions'] = $deductions;

            $stmt = $this->db->prepare(" SELECT * FROM earning_data WHERE employee_id = ? ");
            $stmt->execute([$hr_data_id]);
            $earnings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($earnings as &$earning) {
                $earning['current_pay'] = ($earning['hour_pay'] ?? 0) * ($earning['rate_pay'] ?? 0);

            }

            $result['earnings'] = $earnings;

            
            $result['totalEarningPay'] = array_sum(array_column($earnings, 'current_pay'));
            $result['totalDeductionPay'] = array_sum(array_column($deductions, 'current_pay'));
            $result['gross_pay'] = ($result['salary'] ?? 0) + $result['totalEarningPay'];
            $result['net_pay'] = ($result['gross_pay'] ?? 0) - $result['totalDeductionPay'];

            if ($result) {
                $stmt = $this->db->prepare("UPDATE hr_data SET gross_pay = ?, deduction_pay = ?, net_pay = ? WHERE employee_id = ?");
                $stmt->execute([
                    $result['gross_pay'],
                    $result['totalDeductionPay'],
                    $result['net_pay'],
                    $hr_data_id
                ]);

                return json_encode([
                    "status" => 1,
                    "data" => $result
                ]);
            } else {
                return json_encode([
                    "status" => 0,
                    "data" => [],
                    "error" => "Record not found"
                ]);
            }

        } catch (PDOException $e) {
            return json_encode([
                "status" => 0,
                "data" => [],
                "error" => $e->getMessage()
            ]);
        }
    }





}