<div class="user-notification">
    <div class="dropdown">
        <?php
        // Get unread message count for the current user, only count messages newer than last viewed
        $unreadMessageCount = 0;
        $currentUserId = session()->get('user_id');
        $chatLastViewed = session()->get('chat_last_viewed') ?: 0;
        if ($currentUserId) {
            try {
                $db = \Config\Database::connect();
                $builder = $db->table('messages')
                    ->where('receiver_id', $currentUserId)
                    ->where('is_read', 0);
                if ($chatLastViewed > 0) {
                    $builder = $builder->where('created_at >', date('Y-m-d H:i:s', $chatLastViewed));
                }
                $unreadMessageCount = $builder->countAllResults();
            } catch (\Throwable $e) {
                log_message('error', 'Failed to get unread message count: ' . $e->getMessage());
            }
        }
        ?>
        <a href="<?= base_url('user/chat') ?>" class="dropdown-toggle no-arrow" aria-label="Chat with the community" id="chatDropdownToggle">
            <img src="<?= base_url('anitala icons/messageIcon.png') ?>" alt="Chat" width="36" height="36" class="header-icon align-middle" style="width:24px;height:24px;">
            <?php if ($unreadMessageCount > 0): ?>
                <span class="badge badge-pill badge-danger chat-active" id="chatBadge" style="position: absolute; top: -5px; right: -5px; font-size: 10px; min-width: 18px; height: 18px; line-height: 18px; padding: 0 5px; display: flex; align-items: center; justify-content: center; text-align: center;" aria-hidden="true"><?= $unreadMessageCount > 99 ? '99+' : $unreadMessageCount ?></span>
            <?php else: ?>
                <span class="badge chat-active" id="chatBadge" aria-hidden="true" style="display: none;"></span>
            <?php endif; ?>
        </a>
    </div>
</div>
