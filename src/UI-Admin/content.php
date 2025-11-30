<div id="view-panel" 
     class="container-fluid table-responsive w-100" 
     style="height: calc(100vh - 100px); overflow-y: auto;">
    <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
    <?php include $page . '.php' ?>
</div>
