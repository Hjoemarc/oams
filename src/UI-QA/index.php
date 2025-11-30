<?php include "../../header.php"; ?>

<?php include 'nav-head.php'; ?>

<title><?php echo get_option('system_title'); ?></title>
<div class="d-flex d-justify-between body">
    <?php include 'nav-sidebar.php'; ?>
    <div class="mt-3 w-100 h-auto">
        <?php include 'content.php'; ?>
    </div>
</div>
<?php include '../../footer.php' ?>