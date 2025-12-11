<?php 
// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread notifications
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
$stmtCount->execute(['user_id' => $user_id]);
$unreadCount = $stmtCount->fetchColumn();

// Count all notifications
$totalCount = count($notifications);
?>
<body>
   <section>
      <div class="mb-4">
         <div class="mx-2">
            <h4><i class="fa fa-tv me-2"></i>Accreditor Dashboard</h4>
            <small class="text-muted">Overview and Notifications</small>
         </div>
      </div>

      <!-- Notification System -->
      <div class="row">
         <div class="col-md-12">
            <!-- Notification Bell with Dropdown -->
            <div class="dropdown position-relative d-inline-block">
               <button class="btn btn-primary position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-bell"></i>
                  <?php if($unreadCount > 0): ?>
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                     <?= $unreadCount ?>
                     <span class="visually-hidden">unread notifications</span>
                  </span>
                  <?php endif; ?>
               </button>
               
               <!-- Dropdown Menu -->
               <ul class="dropdown-menu dropdown-menu-end p-0 shadow" style="width: 400px; max-height: 500px; overflow-y: auto;">
                  <li class="dropdown-header bg-light border-bottom">
                     <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Notifications</h6>
                        <?php if($totalCount > 0): ?>
                        <button class="btn btn-sm btn-outline-secondary" onclick="markAllAsRead()">Mark all as read</button>
                        <?php endif; ?>
                     </div>
                  </li>
                  
                  <?php if($totalCount == 0): ?>
                  <li class="text-center py-4">
                     <i class="fa-regular fa-bell-slash fa-2x text-muted mb-2"></i>
                     <p class="text-muted mb-0">No notifications yet</p>
                  </li>
                  <?php else: ?>
                     <?php foreach($notifications as $noti): 
                        $isRead = $noti['is_read'] == 1;
                        $timeAgo = timeAgo($noti['created_at']);
                     ?>
                     <li class="notification-item <?= !$isRead ? 'bg-light' : '' ?> border-bottom">
                        <a href="#" class="dropdown-item p-3" onclick="markAsRead(<?= $noti['notification_id'] ?>, this)">
                           <div class="d-flex justify-content-between align-items-start mb-1">
                              <h6 class="mb-0 <?= !$isRead ? 'fw-bold' : '' ?>"><?= htmlspecialchars($noti['title']) ?></h6>
                              <small class="text-muted"><?= $timeAgo ?></small>
                           </div>
                           <p class="mb-0 text-muted small"><?= htmlspecialchars($noti['message']) ?></p>
                           <?php if(!$isRead): ?>
                           <span class="badge bg-primary badge-pill">New</span>
                           <?php endif; ?>
                        </a>
                     </li>
                     <?php endforeach; ?>
                  <?php endif; ?>
                  
               </ul>
            </div>
            
            <!-- Quick Stats -->
            <div class="mt-4">
               <div class="row g-3">
                  <div class="col-md-3">
                     <div class="card border-start border-primary border-3">
                        <div class="card-body">
                           <div class="d-flex justify-content-between">
                              <div>
                                 <h6 class="text-muted mb-1">Total</h6>
                                 <h4 class="mb-0"><?= $totalCount ?></h4>
                              </div>
                              <div class="avatar-md">
                                 <i class="fa-regular fa-bell fa-2x text-danger"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <div class="col-md-3">
                     <div class="card border-start border-warning border-3">
                        <div class="card-body">
                           <div class="d-flex justify-content-between">
                              <div>
                                 <h6 class="text-muted mb-1">Unread</h6>
                                 <h4 class="mb-0"><?= $unreadCount ?></h4>
                              </div>
                              <div class="avatar-md">
                                 <i class="fa-solid fa-envelope fa-2x text-dark"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <div class="col-md-3">
                     <div class="card border-start border-success border-3">
                        <div class="card-body">
                           <div class="d-flex justify-content-between">
                              <div>
                                 <h6 class="text-dark mb-1">Read</h6>
                                 <h4 class="mb-0"><?= $totalCount - $unreadCount ?></h4>
                              </div>
                              <div class="avatar-md">
                                 <i class="fa-regular fa-envelope-open fa-2x text-success"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
</body>

<script>
// Mark notification as read
function markAsRead(notificationId, element) {
    $.ajax({
        url: 'mark_notification_read.php',
        method: 'POST',
        data: { notification_id: notificationId },
        success: function(response) {
            if (response.status === 1) {
                // Update UI
                $(element).closest('.notification-item').removeClass('bg-light');
                $(element).find('h6').removeClass('fw-bold');
                $(element).find('.badge').remove();
                
                // Update badge count
                updateNotificationBadge();
            }
        }
    });
}

// Mark all notifications as read
function markAllAsRead() {
    $.ajax({
        url: base_url + "/authentication/action.php?action=mark_all_as_read", // REMOVED SEMICOLON HERE
        method: 'POST',
        data: { user_id: <?= $user_id ?> },
        dataType: 'json', // ADD THIS for proper JSON parsing
        success: function(response) {
            console.log("Mark all response:", response); // Debug log
            if (response.status === 1) {
                // Show success message
                Swal.fire({
                    title: 'Success!',
                    text: 'All notifications marked as read',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    showConfirmButton: false
                });
                
                // Update all notifications
                $('.notification-item').removeClass('bg-light');
                $('.notification-item h6').removeClass('fw-bold');
                $('.notification-item .badge').remove();
                
                // Update badge count
                $('#notificationDropdown .badge').remove();
                
                // Hide the "Mark all as read" button
                $('.dropdown-header button').hide();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'Failed to mark notifications as read',
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
            Swal.fire({
                title: 'Connection Error',
                text: 'Please check your connection and try again.',
                icon: 'error',
                toast: true,
                position: 'top-end',
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
}
// Update notification badge
function updateNotificationBadge() {
    $.ajax({
        url: base_url + "/authentication/action.php?action=unread_count",
        method: 'POST',
        data: { user_id: <?= $user_id ?> },
        success: function(response) {
            const unreadCount = response.unread_count || 0;
            const badgeElement = $('#notificationDropdown .badge');
            
            if (unreadCount > 0) {
                if (badgeElement.length) {
                    badgeElement.text(unreadCount);
                } else {
                    $('#notificationDropdown').append(`
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            ${unreadCount}
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    `);
                }
            } else {
                badgeElement.remove();
            }
        }
    });
}

// Auto-refresh notifications every 30 seconds
setInterval(updateNotificationBadge, 30000);
</script>

<style>
.notification-item {
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.dropdown-menu {
    max-height: 500px;
    overflow-y: auto;
    transform: translateX(23rem) !important;
}

.avatar-md {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background-color: rgba(13, 110, 253, 0.1);
}

.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}
</style>

<?php
// Helper function to show time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' min ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M d, Y', $time);
    }
}