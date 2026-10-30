<?php
/**
 * messages.php — Customer Feedback & Contact Inquiries Inbox
 */
require_once __DIR__ . '/../config/config.php';

require_admin();

$adminTitle = 'Customer Messages';

// 1. Handle Toggle Read Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_read') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect(asset_url('backend/admin/messages.php'));
    }

    $msgId = filter_var($_POST['message_id'] ?? 0, FILTER_VALIDATE_INT);
    if ($msgId > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE `messages` SET `is_read` = NOT `is_read` WHERE `message_id` = ?");
            $stmt->execute([$msgId]);
            set_flash('success', 'Message status updated.');
        } catch (PDOException $e) {
            error_log('Toggle message read error: ' . $e->getMessage());
        }
    }
    redirect(asset_url('backend/admin/messages.php'));
}

// 2. Handle Delete Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_message') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect(asset_url('backend/admin/messages.php'));
    }

    $msgId = filter_var($_POST['message_id'] ?? 0, FILTER_VALIDATE_INT);
    if ($msgId > 0) {
        try {
            $delStmt = $pdo->prepare("DELETE FROM `messages` WHERE `message_id` = ?");
            $delStmt->execute([$msgId]);
            set_flash('success', 'Message deleted.');
        } catch (PDOException $e) {
            error_log('Delete message error: ' . $e->getMessage());
            set_flash('error', 'Failed to delete message.');
        }
    }
    redirect(asset_url('backend/admin/messages.php'));
}

// Fetch all messages
try {
    $messages = $pdo->query("SELECT * FROM `messages` ORDER BY `date` DESC")->fetchAll();
} catch (PDOException $e) {
    error_log('Fetch messages error: ' . $e->getMessage());
    $messages = [];
}

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">Inquiries & Feedback (<?= count($messages) ?>)</h3>
    </div>

    <div class="admin-table-wrap">
        <?php if (empty($messages)): ?>
            <div style="padding: 3.5rem; text-align: center; color: #64748b;">
                <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 0.75rem; color: #cbd5e1;"></i>
                <p>No messages received yet.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Status</th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Message Content</th>
                        <th>Received On</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr style="<?= !$msg['is_read'] ? 'background-color: #f8faff; font-weight: 500;' : '' ?>">
                            <td>
                                <?php if ($msg['is_read']): ?>
                                    <span style="color: #94a3b8;" title="Read"><i class="fas fa-envelope-open"></i></span>
                                <?php else: ?>
                                    <span style="color: #2563eb;" title="Unread"><i class="fas fa-envelope"></i></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a;"><?= e($msg['user_name']) ?></div>
                                <div style="font-size: 0.775rem; color: #64748b;">
                                    <a href="mailto:<?= e($msg['email']) ?>" style="color: #64748b;"><?= e($msg['email']) ?></a>
                                </div>
                            </td>
                            <td style="font-weight: 600; color: #0f172a; max-width: 180px;">
                                <?= e($msg['subject']) ?>
                            </td>
                            <td style="color: #475569; font-size: 0.885rem; max-width: 380px; line-height: 1.4;">
                                <?= nl2br(e($msg['message'])) ?>
                            </td>
                            <td style="font-size: 0.825rem; color: #64748b; white-space: nowrap;">
                                <?= date('d M Y, h:i A', strtotime($msg['date'])) ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <!-- Toggle Read -->
                                    <form action="<?= asset_url('backend/admin/messages.php') ?>" method="POST" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle_read">
                                        <input type="hidden" name="message_id" value="<?= (int)$msg['message_id'] ?>">
                                        <button type="submit" class="action-btn" title="<?= $msg['is_read'] ? 'Mark as Unread' : 'Mark as Read' ?>">
                                            <i class="fas <?= $msg['is_read'] ? 'fa-envelope' : 'fa-envelope-open' ?>"></i>
                                        </button>
                                    </form>

                                    <!-- Delete Message -->
                                    <form action="<?= asset_url('backend/admin/messages.php') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Delete message from \'<?= e(addslashes($msg['user_name'])) ?>\'?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_message">
                                        <input type="hidden" name="message_id" value="<?= (int)$msg['message_id'] ?>">
                                        <button type="submit" class="action-btn action-btn-danger" title="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

