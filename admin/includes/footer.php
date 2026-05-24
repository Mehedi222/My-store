<?php
// admin/includes/footer.php - Layout Footer for Admin Dashboard
?>
    </div> <!-- Close container fluid -->
</main> <!-- Close main content -->

<!-- MDBootstrap UI JS -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>

<!-- Shared main JS asset -->
<script src="../assets/js/main.js"></script>

<!-- Highlight active item script in Admin Sidebar -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const currentUrl = window.location.pathname;
    const pageName = currentUrl.substring(currentUrl.lastIndexOf('/') + 1) || 'index.php';
    
    const sidebarItems = document.querySelectorAll('.admin-sidebar .list-group-item');
    sidebarItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href === pageName) {
            item.classList.add('active');
            item.classList.add('fw-bold');
        } else {
            item.classList.remove('active');
        }
    });
});
</script>
</body>
</html>
