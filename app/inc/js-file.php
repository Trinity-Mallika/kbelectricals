 <script src="assets/bootstrap/bootstrap.bundle.min.js"></script>
 <script src="assets/js/jquery.min.js"></script>
 <script src="assets/js/sweetalert.js"></script>
 <script src="assets/js/swiper.js"></script>
 <script src="assets/js/fancybox.js"></script>
 <script src="assets/js/daterange-movement.js"></script>
 <script src="assets/choosen-select/chosen.jquery.min.js"></script>
 <script src="assets/js/commonfun.js"></script>

 <script>
     function loadNotifications() {

         $.ajax({
             url: 'ajax/get_notifications.php',
             type: 'POST',
             dataType: 'json',
             data: {
                 companyid: '<?= $_SESSION['companyid'] ?? 0 ?>',
                 loginid: <?= (isset($_SESSION['salesuserid'])) ? $_SESSION['salesuserid'] : 0; ?>
             },
             success: function(res) {

                 let html = '';
                 let count = res.count;

                 if (count > 0) {

                     $('#notificationCount')
                         .text(count > 99 ? '99+' : count)
                         .show();

                     $.each(res.data, function(i, row) {

                         html += `
<li>
    <a class="dropdown-item py-2">
        <div class="d-flex justify-content-between">
            <strong>${row.title}</strong>
            <small class="text-primary">${row.message}</small>
        </div>
        <small class="text-muted text-truncate d-block">
            ${row.remark || 'Follow-up Reminder'}
        </small>
    </a>
</li>`;
                     });

                 } else {

                     $('#notificationCount').hide();

                     html = `
            <li>
                <span class="dropdown-item-text text-muted">
                    No Notifications
                </span>
            </li>`;
                 }

                 $('#notificationList').html(html);
             }
         });
     }

     $(document).ready(function() {

         loadNotifications();

         setInterval(function() {
             loadNotifications();
         }, 30000); // every 30 sec
     });

     function refreshPage() {
         location.reload();
     }
 </script>