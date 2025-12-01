<?php
$user_id = $admin_id;
//  Fetch user notifications
$notificationsStmt = $pdo->prepare("
    SELECT 
        n.*,
        d.filename,
        d.document_id,
        u.first_name as sender_first,
        u.last_name as sender_last,
        p.program_code,
        p.program_name
    FROM notifications n
    LEFT JOIN users u ON n.user_id = u.user_id
    LEFT JOIN documents d ON n.related_document_id = d.document_id
    LEFT JOIN programs p ON d.program_id = p.program_id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");

$notificationsStmt->execute([$user_id]);
$notifications = $notificationsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$totalNotifications = count($notifications);
$unreadCount = 0;
$todayCount = 0;
$weekCount = 0;

$today = new DateTime();
$weekAgo = (new DateTime())->modify('-7 days');

foreach ($notifications as $notification) {
    if (!$notification['is_read']) {
        $unreadCount++;
    }
    
    $createdAt = new DateTime($notification['created_at']);
    if ($createdAt->format('Y-m-d') === $today->format('Y-m-d')) {
        $todayCount++;
    }
    
    if ($createdAt >= $weekAgo) {
        $weekCount++;
    }
}

// For admin: fetch all users for sending notifications
$allUsers = [];
$usersByRole = [];

?>

<!-- Notifications Dashboard -->
<section class="notifications-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4><i class="fas fa-bell me-2"></i>Notifications Center</h4>
            <small class="text-muted">System announcements and broadcast messages</small>
        </div>
        <div class="d-flex gap-2">
            <?php if ($totalNotifications > 0): ?>
            <form method="POST" style="display: inline;">
                <button type="submit" name="mark_all_read" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-check-double me-1"></i> Mark All Read
                </button>
            </form>
            <form method="POST" style="display: inline;">
                <button type="submit" name="clear_all" class="btn btn-outline-danger btn-sm" 
                        onclick="return confirm('Are you sure you want to clear all notifications?');">
                    <i class="fas fa-trash me-1"></i> Clear All
                </button>
            </form>
            <?php endif; ?>
            
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                <i class="fas fa-bullhorn me-1"></i> Send Announcement
            </button>
        </div>
    </div>

    <!-- Notification Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white rounded-1">
                <div class="card-body text-center">
                    <h5 class="card-title">Unread</h5>
                    <h1 class="display-4 fw-bold"><?= $unreadCount ?></h1>
                    <p class="card-text mb-0">of <?= $totalNotifications ?> total</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white rounded-1">
                <div class="card-body text-center">
                    <h5 class="card-title">Today</h5>
                    <h1 class="display-4 fw-bold"><?= $todayCount ?></h1>
                    <p class="card-text mb-0">new notifications</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white rounded-1">
                <div class="card-body text-center">
                    <h5 class="card-title">This Week</h5>
                    <h1 class="display-4 fw-bold"><?= $weekCount ?></h1>
                    <p class="card-text mb-0">notifications</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white rounded-1">
                <div class="card-body text-center">
                    <h5 class="card-title">History</h5>
                    <h1 class="display-4 fw-bold"><?= $totalNotifications ?></h1>
                    <p class="card-text mb-0">total notifications</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>My Notifications
                <?php if ($unreadCount > 0): ?>
                <span class="badge bg-danger ms-2"><?= $unreadCount ?> unread</span>
                <?php endif; ?>
            </h5>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" style="width: auto;" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="unread">Unread Only</option>
                    <option value="read">Read Only</option>
                </select>
                <select class="form-select form-select-sm" style="width: auto;" id="filterType">
                    <option value="">All Types</option>
                    <option value="system">System</option>
                    <option value="document">Document</option>
                    <option value="review">Review</option>
                    <option value="deadline">Deadline</option>
                    <option value="announcement">Announcement</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="notificationsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Status</th>
                            <th width="25%">Notification</th>
                            <th width="15%">Related To</th>
                            <th width="15%">Type</th>
                            <th width="15%">Date & Time</th>
                            <th width="25%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 0;
                        foreach ($notifications as $notification): 
                            $counter++;
                            $isUnread = !$notification['is_read'];
                            $notificationType = $notification['notification_type'] ?? 'system';
                            
                            // Determine icon and color based on type
                            $icon = 'fas fa-bell';
                            $bgColor = '';
                            $typeBadge = '';
                            
                            switch ($notificationType) {
                                case 'document':
                                    $icon = 'fas fa-file';
                                    $bgColor = $isUnread ? 'bg-info' : 'bg-light';
                                    $typeBadge = 'badge bg-info';
                                    break;
                                case 'review':
                                    $icon = 'fas fa-clipboard-check';
                                    $bgColor = $isUnread ? 'bg-warning' : 'bg-light';
                                    $typeBadge = 'badge bg-warning';
                                    break;
                                case 'deadline':
                                    $icon = 'fas fa-calendar-times';
                                    $bgColor = $isUnread ? 'bg-danger' : 'bg-light';
                                    $typeBadge = 'badge bg-danger';
                                    break;
                                case 'announcement':
                                    $icon = 'fas fa-bullhorn';
                                    $bgColor = $isUnread ? 'bg-success' : 'bg-light';
                                    $typeBadge = 'badge bg-success';
                                    break;
                                default:
                                    $icon = 'fas fa-info-circle';
                                    $bgColor = $isUnread ? 'bg-primary' : 'bg-light';
                                    $typeBadge = 'badge bg-primary';
                            }
                            
                            // Format date
                            $createdAt = new DateTime($notification['created_at']);
                            $now = new DateTime();
                            $interval = $now->diff($createdAt);
                            
                            if ($interval->days == 0) {
                                $timeAgo = $createdAt->format('h:i A');
                                if ($interval->h < 1) {
                                    $timeAgo = $interval->i . ' min ago';
                                } else {
                                    $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                                }
                            } elseif ($interval->days == 1) {
                                $timeAgo = 'Yesterday';
                            } elseif ($interval->days < 7) {
                                $timeAgo = $interval->days . ' days ago';
                            } else {
                                $timeAgo = $createdAt->format('M d, Y');
                            }
                        ?>
                        <tr class="<?= $isUnread ? 'table-row-unread fw-bold' : 'table-row-read' ?>" 
                            data-status="<?= $isUnread ? 'unread' : 'read' ?>" 
                            data-type="<?= $notificationType ?>">
                            <td class="text-center">
                                <?php if ($isUnread): ?>
                                <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-title="Unread">
                                    <i class="fas fa-circle"></i>
                                </span>
                                <?php else: ?>
                                <span class="badge bg-success" data-bs-toggle="tooltip" data-bs-title="Read">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i class="<?= $icon ?> fa-lg text-<?= $notificationType ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($notification['title']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars(substr($notification['message'], 0, 80)) ?>...</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($notification['document_id']): ?>
                                <div class="small">
                                    <i class="fas fa-file-pdf text-danger me-1"></i>
                                    <?= htmlspecialchars($notification['filename']) ?>
                                    <?php if ($notification['program_code']): ?>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($notification['program_code']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">System Message</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="<?= $typeBadge ?>">
                                    <?= ucfirst($notificationType) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    <div><?= $createdAt->format('M d, Y') ?></div>
                                    <div class="text-muted"><?= $timeAgo ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary view-notification-btn"
                                            data-notification-id="<?= $notification['notification_id'] ?>"
                                            data-title="<?= htmlspecialchars($notification['title']) ?>"
                                            data-message="<?= htmlspecialchars($notification['message']) ?>"
                                            data-type="<?= $notificationType ?>"
                                            data-created-at="<?= $notification['created_at'] ?>"
                                            data-is-read="<?= $notification['is_read'] ?>"
                                            data-document-id="<?= $notification['document_id'] ?>"
                                            data-filename="<?= htmlspecialchars($notification['filename'] ?? '') ?>"
                                            data-program="<?= htmlspecialchars($notification['program_code'] ?? '') ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <?php if ($isUnread): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="notification_id" value="<?= $notification['notification_id'] ?>">
                                        <button type="submit" name="mark_as_read" class="btn btn-outline-success">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                        <input type="hidden" name="notification_id" value="<?= $notification['notification_id'] ?>">
                                        <button type="submit" name="delete_notification" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($notifications)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No notifications yet</h5>
                                    <p class="text-muted">You're all caught up! New notifications will appear here.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- View Notification Modal -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1" aria-labelledby="viewNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title text-white" id="viewNotificationModalLabel">Notification Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4 id="notificationTitle"></h4>
                        <div class="d-flex align-items-center mt-2">
                            <span id="notificationTypeBadge" class="badge me-2"></span>
                            <small class="text-muted" id="notificationDateTime"></small>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <form method="POST" id="markReadForm" class="d-inline">
                            <input type="hidden" name="notification_id" id="modalNotificationId">
                            <button type="submit" name="mark_as_read" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> Mark as Read
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h6>Message:</h6>
                        <div id="notificationMessage" class="mb-4"></div>
                        
                        <div id="relatedDocumentInfo" style="display: none;">
                            <hr>
                            <h6>Related Document:</h6>
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                                    <div>
                                        <strong id="documentFileName"></strong>
                                        <div id="documentProgram" class="small"></div>
                                        <button class="btn btn-sm btn-outline-primary mt-2" id="viewDocumentBtn">
                                            <i class="fas fa-external-link-alt me-1"></i> View Document
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <form method="POST" class="d-inline" 
                      onsubmit="return confirm('Are you sure you want to delete this notification?');">
                    <input type="hidden" name="notification_id" id="deleteNotificationId">
                    <button type="submit" name="delete_notification" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sendNotificationModal" tabindex="-1" aria-labelledby="sendNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title text-white" id="sendNotificationModalLabel">Send System Announcement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Notification Type</label>
                        <select name="notification_type" class="form-select" required>
                            <option value="announcement">Announcement</option>
                            <option value="system">System Update</option>
                            <option value="deadline">Deadline Reminder</option>
                            <option value="review">Review Update</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Target Audience</label>
                        <select name="target_users" id="targetUsersSelect" class="form-select" required>
                            <option value="all">All Users</option>
                            <option value="role_based">Specific Role</option>
                            <option value="specific">Specific Users</option>
                        </select>
                    </div>
                    
                    <!-- Role Selection (hidden by default) -->
                    <div class="col-md-6" id="roleSelection" style="display: none;">
                        <label class="form-label">Select Role</label>
                        <select name="user_role" class="form-select">
                            <option value="">Select Role</option>
                            <option value="admin">Administrators</option>
                            <option value="program_coordinator">Program Coordinators</option>
                            <option value="reviewer">Reviewers</option>
                            <option value="faculty">Faculty</option>
                            <option value="student">Students</option>
                        </select>
                    </div>
                    
                    <!-- User Selection (hidden by default) -->
                    <div class="col-12" id="userSelection" style="display: none;">
                        <label class="form-label">Select Users</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div class="row">
                                <?php foreach ($allUsers as $user): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input user-checkbox" type="checkbox" 
                                               name="specific_users[]" value="<?= $user['user_id'] ?>" 
                                               id="user_<?= $user['user_id'] ?>">
                                        <label class="form-check-label" for="user_<?= $user['user_id'] ?>">
                                            <?= htmlspecialchars($user['full_name']) ?>
                                            <small class="text-muted">(<?= $user['user_role'] ?>)</small>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" placeholder="Enter notification title" required>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="5" placeholder="Enter notification message..." required></textarea>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="urgent" id="urgentCheck">
                            <label class="form-check-label text-danger fw-bold" for="urgentCheck">
                                <i class="fas fa-exclamation-triangle me-1"></i> Mark as Urgent
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="send_notification" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i> Send Notification
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.table-row-unread {
    background-color: rgba(13, 110, 253, 0.05) !important;
    border-left: 4px solid #0d6efd;
}

.table-row-unread:hover {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.table-row-read {
    opacity: 0.8;
}

.notification-badge {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}

.bg-unread { background-color: #dc3545; }
.bg-document { background-color: #17a2b8; }
.bg-review { background-color: #ffc107; }
.bg-deadline { background-color: #dc3545; }
.bg-announcement { background-color: #28a745; }
.bg-system { background-color: #6f42c1; }

.text-document { color: #17a2b8; }
.text-review { color: #ffc107; }
.text-deadline { color: #dc3545; }
.text-announcement { color: #28a745; }
.text-system { color: #6f42c1; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Filter functionality
    const filterStatus = document.getElementById('filterStatus');
    const filterType = document.getElementById('filterType');

    function filterNotifications() {
        const statusFilter = filterStatus.value;
        const typeFilter = filterType.value;
        const rows = document.querySelectorAll('#notificationsTable tbody tr');

        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            const type = row.getAttribute('data-type');

            let showRow = true;

            if (statusFilter && status !== statusFilter) {
                showRow = false;
            }

            if (typeFilter && type !== typeFilter) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });
    }

    if (filterStatus) filterStatus.addEventListener('change', filterNotifications);
    if (filterType) filterType.addEventListener('change', filterNotifications);

    // View notification button
    document.querySelectorAll('.view-notification-btn').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.getAttribute('data-notification-id');
            const title = this.getAttribute('data-title');
            const message = this.getAttribute('data-message');
            const type = this.getAttribute('data-type');
            const createdAt = new Date(this.getAttribute('data-created-at'));
            const isRead = this.getAttribute('data-is-read') === '1';
            const documentId = this.getAttribute('data-document-id');
            const filename = this.getAttribute('data-filename');
            const program = this.getAttribute('data-program');
            
            // Set modal content
            document.getElementById('notificationTitle').textContent = title;
            document.getElementById('notificationMessage').textContent = message;
            
            // Set type badge
            const typeBadge = document.getElementById('notificationTypeBadge');
            typeBadge.className = 'badge ' + getBadgeClass(type);
            typeBadge.textContent = type.charAt(0).toUpperCase() + type.slice(1);
            
            // Set date/time
            document.getElementById('notificationDateTime').textContent = 
                createdAt.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            
            // Set IDs for forms
            document.getElementById('modalNotificationId').value = notificationId;
            document.getElementById('deleteNotificationId').value = notificationId;
            
            // Show/hide mark as read button
            const markReadForm = document.getElementById('markReadForm');
            markReadForm.style.display = isRead ? 'none' : 'inline';
            
            // Show/hide related document info
            const relatedDocInfo = document.getElementById('relatedDocumentInfo');
            const viewDocBtn = document.getElementById('viewDocumentBtn');
            
            if (documentId && filename) {
                document.getElementById('documentFileName').textContent = filename;
                if (program) {
                    document.getElementById('documentProgram').textContent = 'Program: ' + program;
                }
                
                // Set up view document button
                viewDocBtn.onclick = function() {
                    window.open('../documents/view.php?id=' + documentId, '_blank');
                };
                
                relatedDocInfo.style.display = 'block';
            } else {
                relatedDocInfo.style.display = 'none';
            }
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewNotificationModal'));
            modal.show();
        });
    });

    // Target audience selection logic (admin only)
    const targetUsersSelect = document.getElementById('targetUsersSelect');
    const roleSelection = document.getElementById('roleSelection');
    const userSelection = document.getElementById('userSelection');
    
    if (targetUsersSelect) {
        targetUsersSelect.addEventListener('change', function() {
            const value = this.value;
            
            // Hide all
            roleSelection.style.display = 'none';
            userSelection.style.display = 'none';
            
            // Show relevant selection
            if (value === 'role_based') {
                roleSelection.style.display = 'block';
            } else if (value === 'specific') {
                userSelection.style.display = 'block';
            }
        });
    }

    // Select all users checkbox (admin only)
    const selectAllCheckbox = document.getElementById('selectAllUsers');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Auto-mark as read when viewing notification
    document.getElementById('viewNotificationModal').addEventListener('show.bs.modal', function(event) {
        // This could be enhanced to auto-mark as read via AJAX
    });
});

function getBadgeClass(type) {
    switch(type) {
        case 'document': return 'bg-info';
        case 'review': return 'bg-warning';
        case 'deadline': return 'bg-danger';
        case 'announcement': return 'bg-success';
        default: return 'bg-primary';
    }
}

// Real-time notification check (optional)
function checkNewNotifications() {
    fetch('../api/check_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.unread_count > 0) {
                // Update badge
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.textContent = data.unread_count;
                }
                
                // Show desktop notification if allowed
                if (Notification.permission === "granted" && data.unread_count > 0) {
                    new Notification("New Notifications", {
                        body: `You have ${data.unread_count} new notification${data.unread_count > 1 ? 's' : ''}`,
                        icon: '../assets/images/logo.png'
                    });
                }
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}

// Request notification permission
if ("Notification" in window && Notification.permission === "default") {
    Notification.requestPermission();
}

// Check for new notifications every 30 seconds
setInterval(checkNewNotifications, 30000);
</script>