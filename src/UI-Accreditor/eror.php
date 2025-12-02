<?php
echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unauthorized Access</title>
</head>
<body class="bg-light">
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="text-center border p-4 rounded shadow bg-white">
            <h2 class="text-danger mb-3">Unauthorized Access</h2>
            <p class="text-muted mb-4">You are not authorized to view this page.</p>
            <a href="../index.php" class="btn btn-danger">
                Logout & Redirect
            </a>
        </div>
    </div>
</body>
</html>';
exit;
?>
