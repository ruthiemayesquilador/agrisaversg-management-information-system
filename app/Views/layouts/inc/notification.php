<?php
/**
 * Notification Dropdown Component
 * Displays user notifications from activity logs
 */

// Get current user ID
$currentUserId = session()->get('user_id');

// Initialize notification data
$unreadNotificationCount = 0;
$communityActivities = [];

if ($currentUserId) {
    try {
        // Use ActivityLogController to get activities
        $activityLogController = new ActivityLogController();
        $allActivities = $activityLogController->setCommunityActivitiesForView(30, 50);
        
        // Filter out current user's own actions from notifications
        if (is_array($allActivities)) {
            $communityActivities = array_filter($allActivities, function($activity) use ($currentUserId) {
                return !empty($activity['user_id']) && $activity['user_id'] != $currentUserId;
            });
            
            // Get last viewed timestamp from session (more reliable than cookies)
            $notificationLastViewed = session()->get('notification_last_viewed') ?: 0;
            
            // Count unread notifications (newer than last viewed)
            foreach ($communityActivities as $activity) {
                $createdAt = strtotime($activity['created_at'] ?? '');
                if ($createdAt > $notificationLastViewed) {
                    $unreadNotificationCount++;
                }
            }
            
            // Limit to 20 most recent after filtering
            $communityActivities = array_slice($communityActivities, 0, 20);
        }
    } catch (\Throwable $e) {
        log_message('error', 'Failed to load notifications: ' . $e->getMessage());
        $communityActivities = [];
    }
}
?>

<div class="user-notification">
    <div class="dropdown">
        <a class="dropdown-toggle no-arrow" href="#" role="button" data-toggle="dropdown" id="notificationDropdownToggle">
            <img src="<?= base_url('anitala icons/notificationIcon.png') ?>" alt="Notifications" width="36" height="36" style="vertical-align:middle;width:24px;height:24px;"> 
            <?php if ($unreadNotificationCount > 0): ?>
                <span class="badge badge-pill badge-danger notification-active" id="notificationBadge" 
                      style="position: absolute; top: -5px; right: -5px; font-size: 10px; min-width: 18px; height: 18px; line-height: 18px; padding: 0 5px; display: flex; align-items: center; justify-content: center;" 
                      aria-hidden="true">
                    <?= $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount ?>
                </span>
            <?php else: ?>
                <span class="badge notification-active" id="notificationBadge" aria-hidden="true" style="display: none;"></span>
            <?php endif; ?>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <div class="notification-list mx-h-350 customscroll">
                <ul>
                    <?php if (!empty($communityActivities) && is_array($communityActivities)): ?>
                        <?php foreach ($communityActivities as $activity): 
                            // Compute notification link
                            $link = '#';
                            $highlightId = null;
                            $hasExplicitTarget = !empty($activity['report_id']) || !empty($activity['plant_id']) || !empty($activity['plant_name']);
                            $activityType = $activity['activity_type'] ?? 'activity_logged';
                            
                            // Create highlight identifier
                            if ($activityType === 'plant_added') {
                                $action = $activity['action'] ?? '';
                                if (preg_match('/added a geotag for (.+)$/i', $action, $matches)) {
                                    $plantName = trim($matches[1]);
                                    $highlightId = 'activity-plant-' . urlencode($plantName);
                                } else {
                                    $highlightId = 'activity-' . $activity['id'];
                                }
                            } elseif (!empty($activity['plant_id'])) {
                                $highlightId = $activityType . '-' . $activity['plant_id'];
                            }

                            // Determine link based on activity type
                            $session = session();
                            $isAdmin = $session->get('is_admin') || in_array($session->get('user_id'), [2]);
                            
                            if ($activityType === 'report_submitted') {
                                if ($isAdmin && !empty($activity['report_id'])) {
                                    $link = base_url('admin/reports/view/' . $activity['report_id']);
                                } else {
                                    $rtype = $activity['report_reference_type'] ?? null;
                                    $rid = $activity['report_reference_id'] ?? null;
                                    if ($rtype === 'user' && $rid) {
                                        $link = base_url('profile/' . $rid);
                                    } elseif ($rtype === 'harvest' && $rid) {
                                        $link = base_url('user/harvest') . '?highlight=harvest-' . $rid;
                                    } elseif ($rtype === 'geotag' && $rid) {
                                        $link = base_url('user/geotag') . '?highlight=geotag-' . $rid;
                                    } elseif ($rtype === 'plant' && $rid) {
                                        $link = base_url('user/plants') . '?highlight=plant-' . $rid;
                                    }
                                }
                            } elseif (!empty($highlightId)) {
                                switch ($activityType) {
                                    case 'plant_added':
                                        $link = base_url('user/geotag/community') . '?highlight=' . $highlightId;
                                        break;
                                    case 'harvest_shared':
                                        $link = base_url('user/harvest') . '?highlight=' . $highlightId;
                                        break;
                                    case 'plant_identified':
                                        $link = base_url('user/geotag') . '?highlight=' . $highlightId;
                                        break;
                                    case 'plant_updated':
                                        $link = base_url('user/plants') . '?highlight=' . $highlightId;
                                        break;
                                    case 'warning_received':
                                        $link = base_url('profile/warnings');
                                        break;
                                    default:
                                        $link = base_url('user/plants') . '?highlight=' . $highlightId;
                                        break;
                                }
                            }
                            
                            // Determine image
                            $showPlantImage = !empty($activity['image']) && $hasExplicitTarget;
                            $isUserActivity = in_array($activityType, ['plant_added', 'harvest_shared', 'plant_identified', 'warning_received']);
                            
                            if ($showPlantImage) {
                                $imageUrl = base_url('uploads/plants/' . esc($activity['image']));
                            } elseif ($isUserActivity && !empty($activity['user_id'])) {
                                try {
                                    $userModel = new \App\Models\UserModel();
                                    $actorUser = $userModel->find($activity['user_id']);
                                    $imageUrl = $actorUser ? $userModel->getProfileImageUrl($actorUser) : base_url('anitala icons/userNoProfile.svg');
                                } catch (\Throwable $e) {
                                    $imageUrl = base_url('anitala icons/userNoProfile.svg');
                                }
                            } else {
                                $imageUrl = base_url('anitala icons/userNoProfile.svg');
                            }
                            
                            // Calculate relative time
                            $when = strtotime($activity['created_at'] ?? date('Y-m-d H:i:s'));
                            $diff = time() - $when;
                            if ($diff < 60) { $ago = $diff . 's'; }
                            elseif ($diff < 3600) { $ago = floor($diff/60) . 'm'; }
                            elseif ($diff < 86400) { $ago = floor($diff/3600) . 'h'; }
                            else { $ago = floor($diff/86400) . 'd'; }
                        ?>
                        <li>
                            <a href="<?= $link ?>">
                                <img src="<?= $imageUrl ?>" alt="Notification Image" />
                                
                                <?php 
                                // Don't show admin names - use generic "AniTala Team" or system messages
                                $displayName = 'Community Member';
                                $session = session();
                                $isAdmin = $session->get('is_admin') || in_array($session->get('user_id'), [2]);
                                
                                // For admin actions, don't show admin name to regular users
                                $isAdminAction = in_array($activityType, ['warning_received', 'mark_plant_verified', 'mark_plant_unverified', 'warn_user']);
                                
                                if (!$isAdminAction) {
                                    $displayName = esc($activity['actor_name'] ?? $activity['username'] ?? 'Community Member');
                                }
                                ?>
                                
                                <?php if ($activityType === 'report_submitted'): 
                                    $reporterLabel = $activity['actor_name'] ?? $activity['username'] ?? 'User';
                                    $refType = $activity['report_reference_type'] ?? null;
                                    $refId = $activity['report_reference_id'] ?? null;
                                    $reportedName = $activity['reported_name'] ?? $activity['reported_username'] ?? null;
                                    $plantName = $activity['plant_name'] ?? null;
                                    $rLabel = $activity['report_type'] ?? null;
                                    
                                    if ($refType === 'user') {
                                        $subjectLine = !empty($reportedName) ? esc($reportedName) : ('User #' . esc($refId));
                                        echo '<h3>' . esc($reporterLabel) . '</h3>';
                                        echo '<p>reported user ' . $subjectLine . ($rLabel ? ' as ' . esc($rLabel) : '') . ' <small class="text-muted">' . $ago . '</small></p>';
                                    } elseif ($refType === 'plant') {
                                        $subjectLine = !empty($plantName) ? esc($plantName) : ('Plant #' . esc($refId));
                                        echo '<h3>' . esc($reporterLabel) . '</h3>';
                                        echo '<p>reported plant ' . $subjectLine . ($rLabel ? ' as ' . esc($rLabel) : '') . ' <small class="text-muted">' . $ago . '</small></p>';
                                    } elseif ($refType === 'harvest') {
                                        $harvestName = $activity['harvest_name'] ?? ($activity['plant_name'] ?? null);
                                        $subjectLine = !empty($harvestName) ? esc($harvestName) : ('Harvest #' . esc($refId));
                                        echo '<h3>' . esc($reporterLabel) . '</h3>';
                                        echo '<p>reported harvest ' . $subjectLine . ($rLabel ? ' as ' . esc($rLabel) : '') . ' <small class="text-muted">' . $ago . '</small></p>';
                                    } elseif ($refType === 'review') {
                                        $reviewSnippet = esc($activity['details'] ?? $activity['action'] ?? 'review');
                                        echo '<h3>' . esc($reporterLabel) . '</h3>';
                                        echo '<p>reported review on ' . (!empty($plantName) ? esc($plantName) : 'a plant') . ' as ' . $reviewSnippet . ' <small class="text-muted">' . $ago . '</small></p>';
                                    } elseif ($refType === 'geotag') {
                                        $subjectLine = !empty($plantName) ? esc($plantName) : ('Geotag #' . esc($refId));
                                        echo '<h3>' . esc($reporterLabel) . '</h3>';
                                        echo '<p>reported plant identification of ' . $subjectLine . ($rLabel ? ' as ' . esc($rLabel) : '') . ' <small class="text-muted">' . $ago . '</small></p>';
                                    } else {
                                        $message = esc($activity['details'] ?? $activity['action'] ?? 'A report has been filed');
                                        echo '<h3>' . esc($reporterLabel) . '</h3>';
                                        echo '<p>' . $message . ' <small class="text-muted">' . $ago . '</small></p>';
                                    }
                                elseif ($activityType === 'warn_user' || $activityType === 'warning_received'):
                                    // User-friendly warning message without showing admin name
                                    $reason = $activity['details'] ?? 'violating community guidelines';
                                ?>
                                    <h3>⚠️ Community Warning</h3>
                                    <p>You received a warning for <?= esc($reason) ?>. Please review our community guidelines. <small class="text-muted"><?= $ago ?></small></p>
                                <?php elseif ($activityType === 'mark_plant_verified'):
                                    $plantLabel = $activity['plant_name'] ?? 'a plant';
                                ?>
                                    <h3>✅ Plant Verified</h3>
                                    <p>Your plant "<?= esc($plantLabel) ?>" has been verified by our team. <small class="text-muted"><?= $ago ?></small></p>
                                <?php elseif ($activityType === 'mark_plant_unverified'):
                                    $plantLabel = $activity['plant_name'] ?? 'a plant';
                                ?>
                                    <h3> Plant Needs Review</h3>
                                    <p>Your plant "<?= esc($plantLabel) ?>" requires additional verification. Please check the details. <small class="text-muted"><?= $ago ?></small></p>
                                <?php elseif ($activityType === 'plant_added'):
                                    $plantLabel = $activity['plant_name'] ?? null;
                                    if (empty($plantLabel)) {
                                        $action = $activity['action'] ?? '';
                                        if (preg_match('/added a geotag for (.+)$/i', $action, $matches)) {
                                            $plantLabel = trim($matches[1]);
                                        } else {
                                            $plantLabel = 'a plant';
                                        }
                                    }
                                ?>
                                    <h3><?= $displayName ?></h3>
                                    <p>added a new geotag for <?= esc($plantLabel) ?> <small class="text-muted"><?= $ago ?></small></p>
                                <?php elseif ($activityType === 'harvest_shared'): ?>
                                    <h3><?= $displayName ?></h3>
                                    <p>shared a harvest <small class="text-muted"><?= $ago ?></small></p>
                                <?php elseif ($activityType === 'plant_identified'): ?>
                                    <h3><?= $displayName ?></h3>
                                    <p>identified a plant <?= !empty($activity['plant_name']) ? 'as ' . esc($activity['plant_name']) : '' ?> <small class="text-muted"><?= $ago ?></small></p>
                                <?php else: ?>
                                    <h3><?= $displayName ?></h3>
                                    <p><?= esc($activity['action'] ?? 'New activity') ?> <small class="text-muted"><?= $ago ?></small></p>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>
                            <a href="#">
                                <img src="<?= base_url('backend/vendors/images/img.jpg') ?>" alt="No notifications" />
                                <h3 class="text-muted">No activity</h3>
                                <p class="text-muted small">You're all caught up</p>
                            </a>
                        </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
</div>
</div>

<script>
// Mark notifications as viewed when dropdown is clicked
document.addEventListener('DOMContentLoaded', function() {
    const notificationToggle = document.querySelector('[data-toggle="dropdown"]');
    const notificationBadge = document.getElementById('notificationBadge');
    
    if (notificationToggle) {
        notificationToggle.addEventListener('click', function() {
            // Mark notifications as viewed via AJAX
            fetch('<?= base_url('activity/mark-viewed') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide the notification badge
                    if (notificationBadge) {
                        notificationBadge.style.display = 'none';
                        notificationBadge.textContent = '';
                    }
                }
            })
            .catch(error => {
                console.error('Error marking notifications as viewed:', error);
            });
        });
    }
});
</script><script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle notification dropdown click - mark as viewed
    const notificationDropdown = document.getElementById('notificationDropdownToggle');
    const notificationBadge = document.getElementById('notificationBadge');
    
    if (notificationDropdown && notificationBadge) {
        notificationDropdown.addEventListener('click', function() {
            // Mark notifications as viewed via AJAX
            fetch('<?= base_url('activity-log/mark-notifications-viewed') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      // Hide badge after brief delay
                      setTimeout(function() {
                          notificationBadge.style.display = 'none';
                          notificationBadge.textContent = '0';
                      }, 300);
                  }
              }).catch(error => {
                  console.error('Error marking notifications as viewed:', error);
              });
        });
    }
});
</script>
