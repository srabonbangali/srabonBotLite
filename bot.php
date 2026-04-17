<?php
/**
 * SrabonBot Lite - Free Telegram Group Management Bot
 * Open Source Version - No API Keys Required
 * 
 * @author Srabon Hasan
 * @version 2.0 Lite
 * @license MIT
 */

// ══════════════════════════════════════════════════════════════════════════════
//  CONFIGURATION - EDIT THESE VALUES
// ══════════════════════════════════════════════════════════════════════════════

define('BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');  // Get from @BotFather
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN);
define('SUPER_ADMIN_ID', 123456789);  // Your Telegram User ID

// File paths for persistent storage
define('WARNINGS_FILE', 'warnings.json');
define('WELCOME_FILE', 'welcome.json');
define('FILTER_FILE', 'filters.json');
define('LOCKED_FILE', 'locked.json');
define('SETTINGS_FILE', 'settings.json');

// ══════════════════════════════════════════════════════════════════════════════
//  LOAD STORAGE DATA
// ══════════════════════════════════════════════════════════════════════════════

$warningsData = file_exists(WARNINGS_FILE) ? json_decode(file_get_contents(WARNINGS_FILE), true) : [];
$welcomeData  = file_exists(WELCOME_FILE)  ? json_decode(file_get_contents(WELCOME_FILE),  true) : [];
$filterData   = file_exists(FILTER_FILE)   ? json_decode(file_get_contents(FILTER_FILE),   true) : [];
$lockedData   = file_exists(LOCKED_FILE)   ? json_decode(file_get_contents(LOCKED_FILE),   true) : [];
$settings     = file_exists(SETTINGS_FILE) ? json_decode(file_get_contents(SETTINGS_FILE), true) : [];

// ══════════════════════════════════════════════════════════════════════════════
//  HELPER FUNCTIONS
// ══════════════════════════════════════════════════════════════════════════════

function isSuperAdmin($userId) {
    return $userId == SUPER_ADMIN_ID;
}

function sendMessage($chatId, $text, $extra = []) {
    $params = array_merge(['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML'], $extra);
    file_get_contents(API_URL . "/sendMessage?" . http_build_query($params));
}

function deleteMessage($chatId, $messageId) {
    file_get_contents(API_URL . "/deleteMessage?" . http_build_query([
        'chat_id' => $chatId, 'message_id' => $messageId
    ]));
}

function isAdmin($chatId, $userId) {
    global $telegramApi;
    if (isSuperAdmin($userId)) return true;
    
    $resp = json_decode(file_get_contents(API_URL . "/getChatMember?" . http_build_query([
        'chat_id' => $chatId, 'user_id' => $userId
    ])), true);
    $status = $resp['result']['status'] ?? '';
    return in_array($status, ['administrator', 'creator']);
}

function requireAdmin($chatId, $userId) {
    if (isSuperAdmin($userId)) return true;
    
    if (!isAdmin($chatId, $userId)) {
        sendMessage($chatId, "🚫 This command is for admins only.");
        return false;
    }
    return true;
}

function getTargetUser($message, $msgText) {
    if (!empty($message['reply_to_message'])) {
        $r = $message['reply_to_message']['from'];
        return [
            'id' => $r['id'],
            'name' => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
            'username' => $r['username'] ?? null
        ];
    }
    return null;
}

function kickMember($chatId, $userId) {
    file_get_contents(API_URL . "/banChatMember?" . http_build_query([
        'chat_id' => $chatId, 'user_id' => $userId, 'until_date' => time() + 45
    ]));
}

function banMember($chatId, $userId) {
    file_get_contents(API_URL . "/banChatMember?" . http_build_query([
        'chat_id' => $chatId, 'user_id' => $userId
    ]));
}

function unbanMember($chatId, $userId) {
    file_get_contents(API_URL . "/unbanChatMember?" . http_build_query([
        'chat_id' => $chatId, 'user_id' => $userId, 'only_if_banned' => true
    ]));
}

function restrictMember($chatId, $userId, $untilDate = 0) {
    $permissions = [
        'can_send_messages' => false,
        'can_send_media_messages' => false,
        'can_send_polls' => false,
        'can_send_other_messages' => false,
        'can_add_web_page_previews' => false
    ];
    
    $opts = ['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'chat_id' => $chatId, 'user_id' => $userId,
            'until_date' => $untilDate, 'permissions' => $permissions
        ])
    ]];
    file_get_contents(API_URL . "/restrictChatMember", false, stream_context_create($opts));
}

function unrestrictMember($chatId, $userId) {
    $permissions = [
        'can_send_messages' => true,
        'can_send_media_messages' => true,
        'can_send_polls' => true,
        'can_send_other_messages' => true,
        'can_add_web_page_previews' => true
    ];
    
    $opts = ['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'chat_id' => $chatId, 'user_id' => $userId,
            'permissions' => $permissions
        ])
    ]];
    file_get_contents(API_URL . "/restrictChatMember", false, stream_context_create($opts));
}

function pinMessage($chatId, $messageId) {
    file_get_contents(API_URL . "/pinChatMessage?" . http_build_query([
        'chat_id' => $chatId, 'message_id' => $messageId
    ]));
}

function unpinMessage($chatId) {
    file_get_contents(API_URL . "/unpinAllChatMessages?" . http_build_query(['chat_id' => $chatId]));
}

function promoteAdmin($chatId, $userId) {
    $opts = ['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'chat_id' => $chatId, 'user_id' => $userId,
            'can_delete_messages' => true,
            'can_restrict_members' => true,
            'can_pin_messages' => true,
            'can_invite_users' => true
        ])
    ]];
    file_get_contents(API_URL . "/promoteChatMember", false, stream_context_create($opts));
}

function demoteAdmin($chatId, $userId) {
    $opts = ['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'chat_id' => $chatId, 'user_id' => $userId,
            'can_delete_messages' => false,
            'can_restrict_members' => false,
            'can_pin_messages' => false,
            'can_invite_users' => false
        ])
    ]];
    file_get_contents(API_URL . "/promoteChatMember", false, stream_context_create($opts));
}

// ══════════════════════════════════════════════════════════════════════════════
//  PROCESS INCOMING UPDATE
// ══════════════════════════════════════════════════════════════════════════════

$update = json_decode(file_get_contents("php://input"), true);
if (!$update) exit;

$message = $update["message"] ?? null;
$msgText = $message["text"] ?? '';
$chatId = $message["chat"]["id"] ?? null;
$chatType = $message["chat"]["type"] ?? 'private';
$fromId = $message["from"]["id"] ?? null;
$fromName = trim(($message["from"]["first_name"] ?? '') . ' ' . ($message["from"]["last_name"] ?? ''));
$username = $message["from"]["username"] ?? null;
$messageId = $message["message_id"] ?? null;
$newMembers = $message["new_chat_members"] ?? [];
$leftMember = $message["left_chat_member"] ?? null;

if (!$chatId) exit;

// ══════════════════════════════════════════════════════════════════════════════
//  AUTO-MODERATION: FILTER SYSTEM
// ══════════════════════════════════════════════════════════════════════════════

if ($msgText && !empty($filterData[$chatId])) {
    foreach ($filterData[$chatId] as $word) {
        if (stripos($msgText, $word) !== false && !isSuperAdmin($fromId)) {
            deleteMessage($chatId, $messageId);
            sendMessage($chatId, "🚫 Message removed: contains filtered word.");
            exit;
        }
    }
}

// ══════════════════════════════════════════════════════════════════════════════
//  AUTO-MODERATION: CHAT LOCK
// ══════════════════════════════════════════════════════════════════════════════

if (!empty($lockedData[$chatId]) && !isSuperAdmin($fromId)) {
    if (!isAdmin($chatId, $fromId)) {
        deleteMessage($chatId, $messageId);
        exit;
    }
}

// ══════════════════════════════════════════════════════════════════════════════
//  WELCOME & GOODBYE MESSAGES
// ══════════════════════════════════════════════════════════════════════════════

if (!empty($newMembers)) {
    foreach ($newMembers as $member) {
        if ($member['is_bot'] ?? false) continue;
        $name = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));
        $uname = $member['username'] ?? null;
        
        $custom = $welcomeData[$chatId]['welcome'] ?? null;
        if ($custom) {
            $text = str_replace(['{name}', '{username}'], [$name, $uname ? "@$uname" : $name], $custom);
        } else {
            $text = "👋 Welcome <b>$name</b>" . ($uname ? " (@$uname)" : "") . "!\nEnjoy your stay!";
        }
        sendMessage($chatId, $text);
    }
}

if (!empty($leftMember) && !($leftMember['is_bot'] ?? false)) {
    $name = trim(($leftMember['first_name'] ?? '') . ' ' . ($leftMember['last_name'] ?? ''));
    $custom = $welcomeData[$chatId]['goodbye'] ?? null;
    $text = $custom ? str_replace('{name}', $name, $custom) : "👋 Goodbye <b>$name</b>!";
    sendMessage($chatId, $text);
}

if (!$msgText) exit;

// ══════════════════════════════════════════════════════════════════════════════
//  COMMAND HANDLING
// ══════════════════════════════════════════════════════════════════════════════

// ── /start ────────────────────────────────────────────────────────────────────
if ($msgText === "/start") {
    if (isSuperAdmin($fromId)) {
        sendMessage($chatId, "👑 Welcome back, Master!\n\nI'm your group management bot.\nType /help to see commands.");
    } else {
        sendMessage($chatId, "🤖 <b>SrabonBot Lite</b>\n\nI help manage groups with:\n✅ Moderation tools\n✅ Auto-moderation\n✅ Welcome messages\n\nType /help for commands!");
    }
}

// ── /help ─────────────────────────────────────────────────────────────────────
elseif ($msgText === "/help") {
    $isGroupAdmin = isSuperAdmin($fromId) || (in_array($chatType, ['group', 'supergroup']) && isAdmin($chatId, $fromId));
    
    $text = "🤖 <b>SrabonBot Lite - Commands</b>\n\n";
    
    if ($isGroupAdmin) {
        $text .= "━━━ 👑 <b>Admin Commands</b> ━━━\n";
        $text .= "/mute [time] - Mute user (10m/2h/1d)\n";
        $text .= "/unmute - Unmute user\n";
        $text .= "/kick - Kick user\n";
        $text .= "/ban - Ban user\n";
        $text .= "/unban - Unban user\n";
        $text .= "/warn - Warn user (3 = kick)\n";
        $text .= "/unwarn - Remove warning\n";
        $text .= "/warnings - Check warnings\n";
        $text .= "/promote - Make admin\n";
        $text .= "/demote - Remove admin\n";
        $text .= "/pin - Pin message\n";
        $text .= "/unpin - Unpin all\n";
        $text .= "/del - Delete message\n";
        $text .= "/adminlist - List admins\n";
        $text .= "/info - User info\n";
        $text .= "/groupinfo - Group stats\n";
        $text .= "/setwelcome [msg] - Set welcome\n";
        $text .= "/setgoodbye [msg] - Set goodbye\n";
        $text .= "/filter [word] - Add filter\n";
        $text .= "/unfilter [word] - Remove filter\n";
        $text .= "/filters - List filters\n";
        $text .= "/lock - Lock chat\n";
        $text .= "/unlock - Unlock chat\n";
        $text .= "/resetwarns - Reset all warnings\n\n";
    }
    
    $text .= "━━━ 🌐 <b>Public Commands</b> ━━━\n";
    $text .= "/start - Bot info\n";
    $text .= "/help - This menu\n";
    $text .= "/stats - Bot statistics\n\n";
    
    $text .= "━━━━━━━━━━━━━━━━━━━━\n";
    $text .= "💎 <b>Premium Version Available!</b>\n";
    $text .= "🔗 https://srabon.net/srabonbot\n\n";
    $text .= "✨ <i>Custom name • Advanced features • 24/7 hosting • Priority support</i>";
    
    sendMessage($chatId, $text);
}

// ── /stats (Bot Statistics) ───────────────────────────────────────────────────
elseif ($msgText === "/stats") {
    $totalGroups = count($welcomeData);
    $totalFilters = array_sum(array_map('count', $filterData));
    $totalWarns = array_sum($warningsData);
    
    $text = "📊 <b>Bot Statistics</b>\n\n";
    $text .= "👥 Groups: $totalGroups\n";
    $text .= "🔤 Active Filters: $totalFilters\n";
    $text .= "⚠️ Total Warnings: $totalWarns\n";
    $text .= "🔒 Locked Chats: " . count(array_filter($lockedData)) . "\n\n";
    $text .= "🤖 Version: 2.0 Lite\n";
    $text .= "💡 Upgrade to Premium for more!";
    
    sendMessage($chatId, $text);
}

// ══════════════════════════════════════════════════════════════════════════════
//  ADMIN COMMANDS (require admin access)
// ══════════════════════════════════════════════════════════════════════════════

// ── Mute System ───────────────────────────────────────────────────────────────
elseif (str_starts_with($msgText, "/mute") && $msgText !== "/unmute") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    if (!$target) {
        sendMessage($chatId, "❗ Reply to a user's message to mute them.");
        exit;
    }
    
    if (!isSuperAdmin($fromId) && isAdmin($chatId, $target['id'])) {
        sendMessage($chatId, "⛔ You cannot mute an admin.");
        exit;
    }
    
    // Parse duration (e.g., 10m, 2h, 1d)
    preg_match('/\/mute\s+(\d+)([mhd])?/i', $msgText, $matches);
    $until = 0;
    $durationText = "indefinitely";
    
    if (!empty($matches[1])) {
        $unit = $matches[2] ?? 'm';
        $multiplier = match($unit) {
            'h' => 3600,
            'd' => 86400,
            default => 60
        };
        $seconds = (int)$matches[1] * $multiplier;
        $until = time() + $seconds;
        $durationText = "for " . $seconds . " seconds";
    }
    
    restrictMember($chatId, $target['id'], $until);
    sendMessage($chatId, "🔇 <b>{$target['name']}</b> has been muted {$durationText}.");
}

elseif ($msgText === "/unmute") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    if (!$target) {
        sendMessage($chatId, "❗ Reply to a muted user's message.");
        exit;
    }
    
    unrestrictMember($chatId, $target['id']);
    sendMessage($chatId, "🔊 <b>{$target['name']}</b> has been unmuted.");
}

// ── Kick/Ban System ───────────────────────────────────────────────────────────
elseif ($msgText === "/kick") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    if (!$target) {
        sendMessage($chatId, "❗ Reply to a user's message to kick them.");
        exit;
    }
    
    if (!isSuperAdmin($fromId) && isAdmin($chatId, $target['id'])) {
        sendMessage($chatId, "⛔ You cannot kick an admin.");
        exit;
    }
    
    kickMember($chatId, $target['id']);
    sendMessage($chatId, "👢 <b>{$target['name']}</b> has been kicked.");
}

elseif ($msgText === "/ban") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    if (!$target) {
        sendMessage($chatId, "❗ Reply to a user's message to ban them.");
        exit;
    }
    
    if (!isSuperAdmin($fromId) && isAdmin($chatId, $target['id'])) {
        sendMessage($chatId, "⛔ You cannot ban an admin.");
        exit;
    }
    
    banMember($chatId, $target['id']);
    sendMessage($chatId, "🔨 <b>{$target['name']}</b> has been banned.");
}

elseif ($msgText === "/unban") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    if (!$target) {
        sendMessage($chatId, "❗ Reply to a message from the banned user.");
        exit;
    }
    
    unbanMember($chatId, $target['id']);
    sendMessage($chatId, "✅ <b>{$target['name']}</b> has been unbanned.");
}

// ── Warning System (3 strikes) ────────────────────────────────────────────────
elseif ($msgText === "/warn") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    if (!$target) {
        sendMessage($chatId, "❗ Reply to a user's message to warn them.");
        exit;
    }
    
    if (!isSuperAdmin($fromId) && isAdmin($chatId, $target['id'])) {
        sendMessage($chatId, "⛔ You cannot warn an admin.");
        exit;
    }
    
    $key = "{$chatId}_{$target['id']}";
    $warningsData[$key] = ($warningsData[$key] ?? 0) + 1;
    $count = $warningsData[$key];
    file_put_contents(WARNINGS_FILE, json_encode($warningsData));
    
    if ($count >= 3) {
        kickMember($chatId, $target['id']);
        unset($warningsData[$key]);
        file_put_contents(WARNINGS_FILE, json_encode($warningsData));
        sendMessage($chatId, "⚠️ <b>{$target['name']}</b> got 3 warnings and was kicked!");
    } else {
        sendMessage($chatId, "⚠️ <b>{$target['name']}</b> warned! ({$count}/3)");
    }
}

elseif ($msgText === "/unwarn") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    if (!$target) {
        sendMessage($chatId, "❗ Reply to a user's message.");
        exit;
    }
    
    $key = "{$chatId}_{$target['id']}";
    if (($warningsData[$key] ?? 0) > 0) {
        $warningsData[$key]--;
        file_put_contents(WARNINGS_FILE, json_encode($warningsData));
        sendMessage($chatId, "✅ Warning removed from <b>{$target['name']}</b>. Now {$warningsData[$key]}/3.");
    } else {
        sendMessage($chatId, "ℹ️ <b>{$target['name']}</b> has no warnings.");
    }
}

elseif ($msgText === "/warnings") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    if (!$target) {
        sendMessage($chatId, "❗ Reply to a user's message.");
        exit;
    }
    
    $key = "{$chatId}_{$target['id']}";
    $count = $warningsData[$key] ?? 0;
    sendMessage($chatId, "⚠️ <b>{$target['name']}</b> has {$count}/3 warnings.");
}

// ── Admin Management ──────────────────────────────────────────────────────────
elseif ($msgText === "/promote") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    if (!$target) {
        sendMessage($chatId, "❗ Reply to a user's message to promote them.");
        exit;
    }
    
    promoteAdmin($chatId, $target['id']);
    sendMessage($chatId, "👑 <b>{$target['name']}</b> is now an admin!");
}

elseif ($msgText === "/demote") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    if (!$target) {
        sendMessage($chatId, "❗ Reply to an admin's message to demote them.");
        exit;
    }
    
    demoteAdmin($chatId, $target['id']);
    sendMessage($chatId, "🔽 <b>{$target['name']}</b> has been demoted.");
}

// ── Message Management ────────────────────────────────────────────────────────
elseif ($msgText === "/pin") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $replyId = $message['reply_to_message']['message_id'] ?? null;
    if (!$replyId) {
        sendMessage($chatId, "❗ Reply to a message to pin it.");
        exit;
    }
    
    pinMessage($chatId, $replyId);
    sendMessage($chatId, "📌 Message pinned!");
}

elseif ($msgText === "/unpin") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    unpinMessage($chatId);
    sendMessage($chatId, "📌 All messages unpinned.");
}

elseif ($msgText === "/del") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $replyId = $message['reply_to_message']['message_id'] ?? null;
    if (!$replyId) {
        sendMessage($chatId, "❗ Reply to a message to delete it.");
        exit;
    }
    
    deleteMessage($chatId, $replyId);
    deleteMessage($chatId, $messageId);
}

// ── Welcome/Goodbye Settings ──────────────────────────────────────────────────
elseif (str_starts_with($msgText, "/setwelcome")) {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $welcomeMsg = trim(substr($msgText, 12));
    if (empty($welcomeMsg)) {
        sendMessage($chatId, "❗ Usage: /setwelcome Welcome {name}!\nVariables: {name}, {username}");
        exit;
    }
    
    $welcomeData[$chatId]['welcome'] = $welcomeMsg;
    file_put_contents(WELCOME_FILE, json_encode($welcomeData));
    sendMessage($chatId, "✅ Welcome message saved!");
}

elseif (str_starts_with($msgText, "/setgoodbye")) {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $goodbyeMsg = trim(substr($msgText, 12));
    if (empty($goodbyeMsg)) {
        sendMessage($chatId, "❗ Usage: /setgoodbye Goodbye {name}!");
        exit;
    }
    
    $welcomeData[$chatId]['goodbye'] = $goodbyeMsg;
    file_put_contents(WELCOME_FILE, json_encode($welcomeData));
    sendMessage($chatId, "✅ Goodbye message saved!");
}

// ── Filter System ─────────────────────────────────────────────────────────────
elseif (str_starts_with($msgText, "/filter") && $msgText !== "/filters") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $word = strtolower(trim(substr($msgText, 8)));
    if (empty($word)) {
        sendMessage($chatId, "❗ Usage: /filter [word]");
        exit;
    }
    
    if (!isset($filterData[$chatId])) $filterData[$chatId] = [];
    if (!in_array($word, $filterData[$chatId])) {
        $filterData[$chatId][] = $word;
        file_put_contents(FILTER_FILE, json_encode($filterData));
        sendMessage($chatId, "✅ Filter added: <code>$word</code>");
    } else {
        sendMessage($chatId, "ℹ️ Word already filtered.");
    }
}

elseif (str_starts_with($msgText, "/unfilter")) {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $word = strtolower(trim(substr($msgText, 10)));
    if (empty($word)) {
        sendMessage($chatId, "❗ Usage: /unfilter [word]");
        exit;
    }
    
    if (isset($filterData[$chatId])) {
        $filterData[$chatId] = array_values(array_filter($filterData[$chatId], fn($w) => $w !== $word));
        file_put_contents(FILTER_FILE, json_encode($filterData));
        sendMessage($chatId, "✅ Filter removed: <code>$word</code>");
    }
}

elseif ($msgText === "/filters") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $list = $filterData[$chatId] ?? [];
    if (empty($list)) {
        sendMessage($chatId, "ℹ️ No active filters.");
    } else {
        sendMessage($chatId, "🔤 <b>Active Filters:</b>\n" . implode(", ", $list));
    }
}

// ── Lock/Unlock Chat ──────────────────────────────────────────────────────────
elseif ($msgText === "/lock") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $lockedData[$chatId] = true;
    file_put_contents(LOCKED_FILE, json_encode($lockedData));
    sendMessage($chatId, "🔒 Chat locked! Only admins can talk.");
}

elseif ($msgText === "/unlock") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $lockedData[$chatId] = false;
    file_put_contents(LOCKED_FILE, json_encode($lockedData));
    sendMessage($chatId, "🔓 Chat unlocked! Everyone can talk.");
}

// ── Reset Warnings ────────────────────────────────────────────────────────────
elseif ($msgText === "/resetwarns") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    foreach (array_keys($warningsData) as $key) {
        if (strpos($key, "{$chatId}_") === 0) {
            unset($warningsData[$key]);
        }
    }
    file_put_contents(WARNINGS_FILE, json_encode($warningsData));
    sendMessage($chatId, "✅ All warnings reset in this group.");
}

// ── Admin List ────────────────────────────────────────────────────────────────
elseif ($msgText === "/adminlist") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $resp = json_decode(file_get_contents(API_URL . "/getChatAdministrators?" . http_build_query(['chat_id' => $chatId])), true);
    $admins = $resp['result'] ?? [];
    
    if (empty($admins)) {
        sendMessage($chatId, "Couldn't fetch admin list.");
        exit;
    }
    
    $text = "👑 <b>Group Admins</b>\n\n";
    foreach ($admins as $a) {
        $name = trim(($a['user']['first_name'] ?? '') . ' ' . ($a['user']['last_name'] ?? ''));
        $badge = $a['status'] === 'creator' ? '🌟 Owner' : '🛡️ Admin';
        $isSA = isSuperAdmin($a['user']['id']) ? ' ⚡' : '';
        $text .= "$badge$isSA — <b>$name</b>\n";
    }
    sendMessage($chatId, $text);
}

// ── User Info ─────────────────────────────────────────────────────────────────
elseif ($msgText === "/info") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $target = getTargetUser($message, $msgText);
    $userId = $target ? $target['id'] : $fromId;
    
    $resp = json_decode(file_get_contents(API_URL . "/getChatMember?" . http_build_query([
        'chat_id' => $chatId, 'user_id' => $userId
    ])), true);
    $member = $resp['result'] ?? null;
    
    if (!$member) {
        sendMessage($chatId, "Couldn't fetch user info.");
        exit;
    }
    
    $u = $member['user'];
    $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
    $username = $u['username'] ?? 'None';
    $status = ucfirst($member['status']);
    $warns = $warningsData["{$chatId}_{$userId}"] ?? 0;
    $isSA = isSuperAdmin($userId) ? "\n⚡ Super Admin" : "";
    
    $text = "👤 <b>User Info</b>\n\n";
    $text .= "Name: $name\n";
    $text .= "Username: @$username\n";
    $text .= "ID: <code>$userId</code>\n";
    $text .= "Status: $status\n";
    $text .= "Warnings: $warns/3$isSA";
    
    sendMessage($chatId, $text);
}

// ── Group Info ────────────────────────────────────────────────────────────────
elseif ($msgText === "/groupinfo") {
    if (!requireAdmin($chatId, $fromId)) exit;
    
    $resp = json_decode(file_get_contents(API_URL . "/getChat?" . http_build_query(['chat_id' => $chatId])), true);
    $chat = $resp['result'] ?? null;
    
    if (!$chat) {
        sendMessage($chatId, "Couldn't fetch group info.");
        exit;
    }
    
    $memberCount = json_decode(file_get_contents(API_URL . "/getChatMemberCount?" . http_build_query(['chat_id' => $chatId])), true);
    
    $text = "📋 <b>Group Info</b>\n\n";
    $text .= "Name: {$chat['title']}\n";
    $text .= "ID: <code>$chatId</code>\n";
    $text .= "Type: " . ucfirst($chat['type']) . "\n";
    $text .= "Members: " . ($memberCount['result'] ?? '?') . "\n";
    $text .= "Description: " . ($chat['description'] ?? 'None');
    
    sendMessage($chatId, $text);
}
?>
