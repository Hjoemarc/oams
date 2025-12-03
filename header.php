<?php
include 'authentication/functions.php';
include 'authentication/view.php';
$employees = getEmployees();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <title>OAMS-ZPPSU</title>
    <link href="https://fonts.googleapis.com/css2?family=Jomhuria&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php render_styles() ?>
    <script>
        var base_url = '<?php echo base_url() ?>';
    </script>
    <?php render_scripts() ?>
</head>

<body class="bg-light-300">

    