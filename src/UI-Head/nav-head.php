<?php
if (!isset($_SESSION['headData'])) {
  include 'eror.php';
  exit;
}
$user_id = $_SESSION['headData']['user_id'];
$getEmployee = $employees["employee_data"];

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
$stmtCount->execute(['user_id' => $user_id]);
$unreadCount = $stmtCount->fetchColumn();

// Count all notifications
$totalCount = count($notifications);
?>
<style>
@media(max-width:768px) {
    .image-header {
        width: 40px !important;
        height: auto !important;
    }
}
</style>

<div class="media-logout-adjust bg-gradient-primary justify-content-between d-flex ">
    <div class="col-md-2 col-1 align-items-center justify-content-center burger-ka-saken" style="display: none;">
        <i class="fa-solid fw-solid fa-bars"></i>
    </div>
    <div class="mx-3 d-flex align-items-center justify-content-center col-md-6 col-7 no-media-margin">
        <img src="../../assets/image/system_logo/zppsu-logo.png" class="image-header me-2">
        <h4 class="w-100 text-white m-0 system-title">Online Accreditation Management System for Zamboanga Peninsula Polytechnic State University </h4>
    </div>
    <div class="justify-content-end p-4 mx-2 d-flex col-md-6 col-5 media-header-padding no-media-margin">
        <div class="col-md-4 col-9 d-flex justify-content-end align-items-center">
            <div class="dropdown position-relative d-inline-block">
                <button class="btn btn-primary position-relative m-0" type="button" id="notificationDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bell"></i>
                    <?php if($unreadCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $unreadCount ?>
                        <span class="visually-hidden">unread notifications</span>
                    </span>
                    <?php endif; ?>
                </button>

                <!-- Dropdown Menu -->
                <ul class="dropdown-menu dropdown-menu-end p-0 shadow"
                    style="width: 400px; max-height: 500px; overflow-y: auto;">
                    <li class="dropdown-header bg-light border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Notifications</h6>
                            <?php if($totalCount > 0): ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="markAllAsRead()">Mark all as
                                read</button>
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
                        <a href="#" class="dropdown-item p-3"
                            onclick="markAsRead(<?= $noti['notification_id'] ?>, this)">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0 <?= !$isRead ? 'fw-bold' : '' ?>">
                                    <?= htmlspecialchars($noti['title']) ?></h6>
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
        </div>

        <button type="button" id="logoutBtn" class="col-md-2 col-3 button-location-media">
            <i class="fas fa-sign-out-alt text-black ms-1"></i>
        </button>
        <div class="logoutDomain col-md-3 col-8 h-auto  shadow rounded-1 flex-column border" id="logoutDomain"
            style="display:none; background-color: #fff !important;">
            <div class="header-logout bg-danger p-3 d-flex align-items-start justify-content-start w-100 rounded-top">
                <strong class="text-white">Logout Confirmation</strong>
            </div>
            <div class="body-logout py-4 px-4">
                <span class="fw-bold text-muted"> Are you sure you want to logout?</span>
            </div>
            <div class="footer-logout w-100 d-flex align-items-end justify-content-end gap-3 pb-3 pe-3 pt-2"
                style="border-top: solid .5px #0e0e0e4f !important;">
                <button class="m-0 btn btn-danger" id="logout" class="logout" style="cursor: pointer;">
                    yes
                </button>
                <button class="m-0 btn btn-dark" type="button">Cancel</button>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    $('html').css('scroll-behavior', 'smooth');
});

$(document).ready(function() {
    const sidebar = $('.navbarHide');
    const burger = $('.burger-ka-saken i');

    if (!$('.sidebar-overlay').length) {
        $('body').append('<div class="sidebar-overlay"></div>');
    }

    $('.burger-ka-saken').on('click', function() {
        sidebar.toggleClass('navbarShow');
        $('.sidebar-overlay').toggleClass('active');
        burger.toggleClass('fa-times');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.navbarHide, .burger-ka-saken').length) {
            sidebar.removeClass('navbarShow');
            $('.sidebar-overlay').removeClass('active');
            burger.removeClass('fa-times');
        }
    });

    sidebar.on('click', function(e) {
        e.stopPropagation();
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutDomain = document.getElementById('logoutDomain');
    const cancelBtn = logoutDomain.querySelector('.btn-dark');
    const confirmLogoutBtn = document.getElementById('logout');

    // Show/hide logout confirmation popup
    logoutBtn.addEventListener('click', function(e) {
        e.stopPropagation();

        positionLogoutPopup();

        if (logoutDomain.style.display === 'none' || !logoutDomain.style.display) {
            logoutDomain.style.opacity = '0';
            logoutDomain.style.display = 'flex';
            logoutDomain.style.transform = 'translateY(-10px)';

            setTimeout(() => {
                logoutDomain.style.opacity = '1';
                logoutDomain.style.transform = 'translateY(0)';
            }, 10);
        } else {
            logoutDomain.style.opacity = '0';
            logoutDomain.style.transform = 'translateY(-10px)';

            setTimeout(() => {
                logoutDomain.style.display = 'none';
            }, 5000);
        }
    });

    // Position the popup relative to the logout button
    function positionLogoutPopup() {
        const btnRect = logoutBtn.getBoundingClientRect();
        const domainWidth = logoutDomain.offsetWidth;

        logoutDomain.style.position = 'fixed';

        if (btnRect.left + domainWidth > window.innerWidth) {
            logoutDomain.style.left = `${btnRect.left - domainWidth + btnRect.width}px`;
        } else {
            logoutDomain.style.left = `${btnRect.left}px`;
        }

        logoutDomain.style.top = `${btnRect.bottom + 5}px`;
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        if (logoutDomain.style.display !== 'none') {
            positionLogoutPopup();
        }
    });

    // Cancel button closes the popup
    cancelBtn.addEventListener('click', closeLogoutPopup);

    // Logout button handler - using AJAX
    confirmLogoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const $this = $(this);

        $this.html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out...'
            );
        $this.prop('disabled', true);

        $.ajax({
            url: base_url + "authentication/action.php?action=logout",
            method: "POST",
            dataType: "json",
            success: function(response) {
                if (response.status == 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.href = base_url + (response.redirect_url ||
                            "index.php");
                    });
                } else {
                    showError(response.message);
                    $this.text("Yes");
                    $this.prop('disabled', false);
                }
            },
            error: function() {
                console.error("AJAX error");
                $this.text("Yes");
                $this.prop('disabled', false);
            }
        });
    });

    // Close popup function
    function closeLogoutPopup() {
        logoutDomain.style.opacity = '0';
        logoutDomain.style.transform = 'translateY(-10px)';

        setTimeout(() => {
            logoutDomain.style.display = 'none';
        }, 200);
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!logoutDomain.contains(e.target) && e.target !== logoutBtn) {
            closeLogoutPopup();
        }
    });

    // Close when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && logoutDomain.style.display !== 'none') {
            closeLogoutPopup();
        }
    });
});
// Mark all notifications as read
function markAllAsRead() {
    $.ajax({
        url: base_url + "/authentication/action.php?action=mark_all_as_read", // REMOVED SEMICOLON HERE
        method: 'POST',
        data: {
            user_id: <?= $user_id ?>
        },
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
        data: {
            user_id: <?= $user_id ?>
        },
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
</script>
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
?>
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