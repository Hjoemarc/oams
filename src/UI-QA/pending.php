<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <div class="bg-gradient-primary h-auto p-3 d-flex  align-items-center justify-content-between" style="background-image: linear-gradient(300deg,#E32126, #FF6F8F,#E32126);
    color: #fff;">
        <div class="col-md-7 d-flex">
            <img src="../../assets/image/system_logo/pueri-logo.png" class="me-2" style="height: auto; width: 50px;">
            <h2 class="m-0 d-flex align-items-center fw-bold">Zamboanga Puericulture Center</h2>
        </div>  
        <div class="col-md-3 d-flex justify-content-end">
            <a href="pds.php?employee_id=<?= $_SESSION['employeeData']['employee_id'] ?>"><button class="btn btn-info m-0 fw-bold me-3 text-white">PDS</button></a>
        </div>
    </div>
    <div class="d-flex flex-column justify-content-center align-items-center" style="width: 100vw; height: 85vh; overflow-hidden;">
        <strong>Waiting for admin approval</strong>
        <a href="../index.php"><button class="btn btn-danger px-5 m-0 fw-bold mt-3">Back to login</button></a>
    </div>  
</body>
</html>
