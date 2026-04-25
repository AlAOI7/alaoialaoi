<?php
require_once 'config.php';
checkAuth();

// التحقق من صلاحيات المستخدم
if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'support') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = getUser($user_id);

// معالجة طلبات AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_conversations':
            echo json_encode(getConversations());
            exit;
            
        case 'get_messages':
            if (isset($_POST['conversation_id'])) {
                echo json_encode(getMessages($_POST['conversation_id']));
            }
            exit;
            
        case 'send_message':
            if (isset($_POST['conversation_id']) && isset($_POST['message'])) {
                echo json_encode(sendMessage($_POST['conversation_id'], $_POST['message']));
            }
            exit;
            
        case 'update_conversation_status':
            if (isset($_POST['conversation_id']) && isset($_POST['status'])) {
                echo json_encode(updateConversationStatus($_POST['conversation_id'], $_POST['status']));
            }
            exit;
            
        case 'get_all_users':
            echo json_encode(getAllUsersForChat());
            exit;
            
        case 'send_bulk_message':
            if (isset($_POST['users']) && isset($_POST['message'])) {
                echo json_encode(sendBulkMessage($_POST['users'], $_POST['message']));
            }
            exit;
            
        case 'create_conversation':
            if (isset($_POST['user_id'])) {
                echo json_encode(createConversation($_POST['user_id']));
            }
            exit;
    }
}

// دالات قاعدة البيانات
function getConversations() {
    global $conn;
    
    $query = "SELECT c.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
              FROM conversations c 
              JOIN users u ON c.user_id = u.id 
              ORDER BY c.last_message_time DESC";
    
    $result = $conn->query($query);
    $conversations = [];
    
    while ($row = $result->fetch_assoc()) {
        $conversations[] = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'user_name' => $row['user_name'],
            'user_email' => $row['user_email'],
            'user_phone' => $row['user_phone'],
            'user_initial' => mb_substr($row['user_name'], 0, 1),
            'title' => $row['title'],
            'status' => $row['status'],
            'unread_count' => $row['unread_count'],
            'last_message' => $row['last_message'],
            'last_message_time' => $row['last_message_time'],
            'created_at' => $row['created_at']
        ];
    }
    
    return $conversations;
}

function getMessages($conversation_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT m.*, u.name as sender_name, u.user_type as sender_role 
                           FROM messages m 
                           JOIN users u ON m.sender_id = u.id 
                           WHERE m.conversation_id = ? 
                           ORDER BY m.created_at ASC");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'text' => $row['message_text'],
            'time' => date('H:i', strtotime($row['created_at'])),
            'date' => date('Y-m-d', strtotime($row['created_at'])),
            'type' => $row['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received',
            'sender_name' => $row['sender_name'],
            'sender_role' => $row['sender_role']
        ];
    }
    
    // تحديث الرسائل كمقروءة
    markMessagesAsRead($conversation_id, $_SESSION['user_id']);
    
    return $messages;
}

function sendMessage($conversation_id, $message_text) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, message_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $conversation_id, $_SESSION['user_id'], $message_text);
    
    if ($stmt->execute()) {
        // تحديث آخر رسالة في المحادثة
        $update_stmt = $conn->prepare("UPDATE conversations SET last_message = ?, last_message_time = NOW(), status = 'active' WHERE id = ?");
        $update_stmt->bind_param("si", $message_text, $conversation_id);
        $update_stmt->execute();
        
        return ['success' => true, 'message_id' => $stmt->insert_id];
    }
    
    return ['success' => false];
}

function markMessagesAsRead($conversation_id, $user_id) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ?");
    $stmt->bind_param("ii", $conversation_id, $user_id);
    $stmt->execute();
    
    // تحديث عداد الرسائل غير المقروءة
    $update_stmt = $conn->prepare("UPDATE conversations SET unread_count = 0 WHERE id = ?");
    $update_stmt->bind_param("i", $conversation_id);
    $update_stmt->execute();
}

function updateConversationStatus($conversation_id, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE conversations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $conversation_id);
    
    return ['success' => $stmt->execute()];
}

function getAllUsersForChat() {
    global $conn;
    
    $query = "SELECT id, name, email, phone, user_type, status, 
              DATE_FORMAT(last_activity, '%Y-%m-%d %H:%i:%s') as last_activity,
              DATE_FORMAT(created_at, '%Y-%m-%d') as join_date
              FROM users 
              WHERE id != ? 
              ORDER BY name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?: 'غير متوفر',
            'type' => $row['user_type'] == 'admin' ? 'مدير' : 'مستخدم',
            'status' => $row['status'],
            'last_activity' => $row['last_activity'],
            'join_date' => $row['join_date'],
            'initial' => mb_substr($row['name'], 0, 1)
        ];
    }
    
    return $users;
}

function sendBulkMessage($user_ids, $message_text) {
    global $conn;
    
    $success_count = 0;
    $failed_count = 0;
    
    foreach ($user_ids as $user_id) {
        // التحقق من وجود محادثة مع المستخدم
        $check_stmt = $conn->prepare("SELECT id FROM conversations WHERE user_id = ? AND admin_id = ?");
        $check_stmt->bind_param("ii", $user_id, $_SESSION['user_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $conversation = $check_result->fetch_assoc();
            $conversation_id = $conversation['id'];
        } else {
            // إنشاء محادثة جديدة
            $create_stmt = $conn->prepare("INSERT INTO conversations (user_id, admin_id, title, status) VALUES (?, ?, ?, 'active')");
            $title = "رسالة جماعية";
            $create_stmt->bind_param("iis", $user_id, $_SESSION['user_id'], $title);
            if ($create_stmt->execute()) {
                $conversation_id = $create_stmt->insert_id;
            } else {
                $failed_count++;
                continue;
            }
        }
        
        // إرسال الرسالة
        $msg_stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, message_text) VALUES (?, ?, ?)");
        $msg_stmt->bind_param("iis", $conversation_id, $_SESSION['user_id'], $message_text);
        
        if ($msg_stmt->execute()) {
            // تحديث آخر رسالة
            $update_stmt = $conn->prepare("UPDATE conversations SET last_message = ?, last_message_time = NOW() WHERE id = ?");
            $update_stmt->bind_param("si", $message_text, $conversation_id);
            $update_stmt->execute();
            
            $success_count++;
        } else {
            $failed_count++;
        }
    }
    
    return [
        'success' => true,
        'sent' => $success_count,
        'failed' => $failed_count,
        'total' => count($user_ids)
    ];
}

function createConversation($user_id) {
    global $conn;
    
    // التحقق من وجود محادثة مسبقاً
    $check_stmt = $conn->prepare("SELECT id FROM conversations WHERE user_id = ? AND admin_id = ?");
    $check_stmt->bind_param("ii", $user_id, $_SESSION['user_id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $conversation = $check_result->fetch_assoc();
        return ['success' => true, 'conversation_id' => $conversation['id'], 'existing' => true];
    }
    
    // إنشاء محادثة جديدة
    $user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    
    $title = "محادثة مع " . $user['name'];
    $stmt = $conn->prepare("INSERT INTO conversations (user_id, admin_id, title, status) VALUES (?, ?, ?, 'active')");
    $stmt->bind_param("iis", $user_id, $_SESSION['user_id'], $title);
    
    if ($stmt->execute()) {
        return ['success' => true, 'conversation_id' => $stmt->insert_id, 'existing' => false];
    }
    
    return ['success' => false];
}

// جلب الإحصائيات
function getSupportStats() {
    global $conn;
    
    $stats = [
        'total_conversations' => 0,
        'active_conversations' => 0,
        'pending_conversations' => 0,
        'unread_messages' => 0,
        'total_users' => 0,
        'online_users' => 0
    ];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM conversations");
    $stats['total_conversations'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as active FROM conversations WHERE status = 'active'");
    $stats['active_conversations'] = $result->fetch_assoc()['active'];
    
    $result = $conn->query("SELECT COUNT(*) as pending FROM conversations WHERE status = 'pending'");
    $stats['pending_conversations'] = $result->fetch_assoc()['pending'];
    
    $result = $conn->query("SELECT SUM(unread_count) as unread FROM conversations");
    $stats['unread_messages'] = $result->fetch_assoc()['unread'] ?: 0;
    
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'user'");
    $stats['total_users'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as online FROM users WHERE status = 'active' AND user_type = 'user'");
    $stats['online_users'] = $result->fetch_assoc()['online'];
    
    return $stats;
}

$stats = getSupportStats();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الدعم الفني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <?php include 'header.php'; ?>

            <!-- محتوى الصفحة -->
            <div class="page-content">
                <div class="page-title">
                    <h2>نظام الدردشة والدعم الفني</h2>
                    <div class="date" id="currentDate"><?php echo date('Y-m-d'); ?></div>
                </div>

                <!-- إحصائيات سريعة -->
                <div class="quick-stats">
                    <div class="stat-card">
                        <div class="stat-icon stat-1">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_conversations']; ?></h3>
                            <p>إجمالي المحادثات</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-2">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['online_users']; ?></h3>
                            <p>المستخدمين النشطين</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['pending_conversations']; ?></h3>
                            <p>محادثات معلقة</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-4">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p>إجمالي العملاء</p>
                        </div>
                    </div>
                </div>

                <!-- شاشة الدعم الفني -->
                <div class="support-container">
                    <!-- لوحة المحادثات -->
                    <div class="conversations-panel">
                        <div class="conversations-header">
                            <h3>المحادثات النشطة</h3>
                            <div class="search-box">
                                <input type="text" id="searchConversations" placeholder="ابحث في المحادثات...">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="filters">
                                <button class="filter-btn active" data-filter="all">الكل</button>
                                <button class="filter-btn" data-filter="unread">غير مقروء</button>
                                <button class="filter-btn" data-filter="pending">معلقة</button>
                                <button class="filter-btn" data-filter="closed">مغلقة</button>
                            </div>
                        </div>
                        <div class="conversations-list" id="conversationsList">
                            <!-- سيتم تعبئة المحادثات ديناميكياً -->
                        </div>
                    </div>

                    <!-- لوحة المحادثة -->
                    <div class="chat-panel" id="chatPanel">
                        <div class="no-chat-selected" id="noChatSelected">
                            <i class="fas fa-comments"></i>
                            <h3>اختر محادثة لبدء الدردشة</h3>
                            <p>اختر محادثة من القائمة أو ابدأ محادثة جديدة مع عميل</p>
                        </div>
                        
                        <div class="chat-header" id="chatHeader" style="display: none;">
                            <div class="chat-user">
                                <div class="chat-user-avatar" id="chatUserAvatar">أ</div>
                                <div class="chat-user-info">
                                    <h4 id="chatUserName">أحمد محمد</h4>
                                    <p id="chatUserStatus">الحالة: نشط</p>
                                </div>
                            </div>
                            <div class="chat-status">
                                <div class="status-dot" id="statusDot"></div>
                                <span id="statusText">نشط</span>
                            </div>
                            <div class="chat-actions">
                                <button class="chat-action-btn" id="closeChatBtn" title="إغلاق المحادثة">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button class="chat-action-btn" id="userInfoBtn" title="معلومات المستخدم">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="chat-messages" id="chatMessages" style="display: none;">
                            <!-- سيتم تعبئة الرسائل ديناميكياً -->
                        </div>
                        
                        <div class="chat-input" id="chatInput" style="display: none;">
                            <div class="chat-input-actions">
                                <button class="chat-input-btn" title="إرفاق ملف">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button class="chat-input-btn" title="إرسال صورة">
                                    <i class="fas fa-image"></i>
                                </button>
                            </div>
                            <input type="text" id="messageInput" placeholder="اكتب رسالتك هنا..." autocomplete="off">
                            <button class="chat-input-btn send-btn" id="sendMessageBtn" title="إرسال">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>

                    <!-- لوحة المستخدمين -->
                    <div class="users-panel">
                        <div class="users-header">
                            <h3>قائمة العملاء</h3>
                            <button class="bulk-message-btn" id="bulkMessageBtn">
                                <i class="fas fa-bullhorn"></i>
                                رسالة جماعية
                            </button>
                        </div>
                        <div class="users-list" id="usersList">
                            <!-- سيتم تعبئة المستخدمين ديناميكياً -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة الرسائل الجماعية -->
    <div class="modal" id="bulkMessageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>إرسال رسالة جماعية</h3>
                <button class="close-btn" id="closeBulkModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="selected-users" id="selectedUsersList">
                    <!-- المستخدمين المحددين سيظهرون هنا -->
                </div>
                <div class="form-group">
                    <label for="bulkMessage">الرسالة</label>
                    <textarea class="form-control" id="bulkMessage" placeholder="اكتب الرسالة التي تريد إرسالها لجميع المستخدمين المحددين..."></textarea>
                </div>
                <div class="form-group">
                    <label>عدد المستخدمين المحددين: <span id="selectedCount">0</span></label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelBulkBtn">إلغاء</button>
                <button class="btn btn-success" id="sendBulkBtn">إرسال الرسالة</button>
            </div>
        </div>
    </div>

    <script>
        // عناصر DOM
        const toggleSidebar = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const conversationsList = document.getElementById('conversationsList');
        const searchConversations = document.getElementById('searchConversations');
        const filterButtons = document.querySelectorAll('.filter-btn');
        const chatPanel = document.getElementById('chatPanel');
        const noChatSelected = document.getElementById('noChatSelected');
        const chatHeader = document.getElementById('chatHeader');
        const chatMessages = document.getElementById('chatMessages');
        const chatInput = document.getElementById('chatInput');
        const chatUserAvatar = document.getElementById('chatUserAvatar');
        const chatUserName = document.getElementById('chatUserName');
        const chatUserStatus = document.getElementById('chatUserStatus');
        const statusDot = document.getElementById('statusDot');
        const statusText = document.getElementById('statusText');
        const messageInput = document.getElementById('messageInput');
        const sendMessageBtn = document.getElementById('sendMessageBtn');
        const notificationCount = document.getElementById('notificationCount');
        const closeChatBtn = document.getElementById('closeChatBtn');
        const userInfoBtn = document.getElementById('userInfoBtn');
        const bulkMessageBtn = document.getElementById('bulkMessageBtn');
        const usersList = document.getElementById('usersList');
        const bulkMessageModal = document.getElementById('bulkMessageModal');
        const closeBulkModal = document.getElementById('closeBulkModal');
        const cancelBulkBtn = document.getElementById('cancelBulkBtn');
        const sendBulkBtn = document.getElementById('sendBulkBtn');
        const bulkMessage = document.getElementById('bulkMessage');
        const selectedUsersList = document.getElementById('selectedUsersList');
        const selectedCount = document.getElementById('selectedCount');

        // المتغيرات العامة
        let currentFilter = 'all';
        let currentConversationId = null;
        let currentUser = null;
        let allConversations = [];
        let allUsers = [];
        let selectedUsers = new Set();
        let refreshInterval;

        // تهيئة التطبيق
        document.addEventListener('DOMContentLoaded', function() {
            loadConversations();
            loadUsers();
            addEventListeners();
            
            // تحديث المحادثات والمستخدمين كل 10 ثواني
            refreshInterval = setInterval(() => {
                loadConversations();
                loadUsers();
            }, 10000);
        });

        // تحميل المحادثات من الخادم
        async function loadConversations() {
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_conversations'
                });
                
                const conversations = await response.json();
                allConversations = conversations;
                renderConversations();
                updateNotificationCount();
            } catch (error) {
                console.error('Error loading conversations:', error);
            }
        }

        // تحميل المستخدمين من الخادم
        async function loadUsers() {
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_all_users'
                });
                
                const users = await response.json();
                allUsers = users;
                renderUsers();
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        // عرض المحادثات
        function renderConversations() {
            conversationsList.innerHTML = '';
            
            let conversationsToShow = [...allConversations];
            
            // تطبيق الفلتر
            if (currentFilter === 'unread') {
                conversationsToShow = conversationsToShow.filter(conv => conv.unread_count > 0);
            } else if (currentFilter === 'pending') {
                conversationsToShow = conversationsToShow.filter(conv => conv.status === 'pending');
            } else if (currentFilter === 'closed') {
                conversationsToShow = conversationsToShow.filter(conv => conv.status === 'closed');
            }
            
            // تطبيق البحث
            const searchTerm = searchConversations.value.toLowerCase();
            if (searchTerm) {
                conversationsToShow = conversationsToShow.filter(conv => 
                    conv.user_name.toLowerCase().includes(searchTerm) ||
                    (conv.last_message && conv.last_message.toLowerCase().includes(searchTerm))
                );
            }
            
            if (conversationsToShow.length === 0) {
                conversationsList.innerHTML = '<div class="no-conversations" style="text-align: center; padding: 20px; color: var(--gray);">لا توجد محادثات</div>';
                return;
            }
            
            conversationsToShow.forEach(conversation => {
                const conversationItem = document.createElement('div');
                conversationItem.className = 'conversation-item';
                if (conversation.id === currentConversationId) {
                    conversationItem.classList.add('active');
                }
                
                const lastMessageTime = formatTime(conversation.last_message_time);
                
                conversationItem.innerHTML = `
                    <div class="conversation-avatar">${conversation.user_initial}</div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <div class="conversation-name">${conversation.user_name}</div>
                            <div class="conversation-time">${lastMessageTime}</div>
                        </div>
                        <div class="conversation-preview">
                            <div class="conversation-message" title="${conversation.last_message || 'لا توجد رسائل'}">
                                ${conversation.last_message || 'لا توجد رسائل'}
                            </div>
                            ${conversation.unread_count > 0 ? `<div class="unread-badge" title="${conversation.unread_count} رسالة غير مقروءة">${conversation.unread_count}</div>` : ''}
                        </div>
                    </div>
                `;
                
                conversationItem.addEventListener('click', () => {
                    selectConversation(conversation.id);
                });
                
                conversationsList.appendChild(conversationItem);
            });
        }

        // عرض المستخدمين
        function renderUsers() {
            usersList.innerHTML = '';
            
            if (allUsers.length === 0) {
                usersList.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--gray);">لا يوجد عملاء</div>';
                return;
            }
            
            allUsers.forEach(user => {
                const userItem = document.createElement('div');
                userItem.className = 'user-item';
                if (selectedUsers.has(user.id)) {
                    userItem.classList.add('selected');
                }
                
                userItem.innerHTML = `
                    <div class="user-avatar-small">${user.initial}</div>
                    <div class="user-details">
                        <h4>${user.name}</h4>
                        <p>${user.email}</p>
                        <div class="user-phone">
                            <i class="fas fa-phone"></i>
                            <span>${user.phone}</span>
                        </div>
                    </div>
                    <input type="checkbox" class="user-checkbox" data-user-id="${user.id}" ${selectedUsers.has(user.id) ? 'checked' : ''}>
                `;
                
                // عند النقر على المستخدم
                userItem.addEventListener('click', (e) => {
                    if (!e.target.classList.contains('user-checkbox')) {
                        startConversationWithUser(user);
                    }
                });
                
                // عند تحديد/إلغاء تحديد المربع
                const checkbox = userItem.querySelector('.user-checkbox');
                checkbox.addEventListener('change', (e) => {
                    e.stopPropagation();
                    if (checkbox.checked) {
                        selectedUsers.add(user.id);
                        userItem.classList.add('selected');
                    } else {
                        selectedUsers.delete(user.id);
                        userItem.classList.remove('selected');
                    }
                    updateSelectedCount();
                });
                
                usersList.appendChild(userItem);
            });
            
            updateSelectedCount();
        }

        // تحديث عدد المستخدمين المحددين
        function updateSelectedCount() {
            selectedCount.textContent = selectedUsers.size;
            
            // تحديث قائمة المستخدمين المحددين في النافذة المنبثقة
            selectedUsersList.innerHTML = '';
            if (selectedUsers.size > 0) {
                selectedUsers.forEach(userId => {
                    const user = allUsers.find(u => u.id == userId);
                    if (user) {
                        const tag = document.createElement('span');
                        tag.className = 'selected-user-tag';
                        tag.innerHTML = `${user.name} <i class="fas fa-times" onclick="removeSelectedUser(${userId})" style="margin-right: 5px; cursor: pointer;"></i>`;
                        selectedUsersList.appendChild(tag);
                    }
                });
            } else {
                selectedUsersList.innerHTML = '<div style="text-align: center; color: var(--gray);">لم يتم تحديد أي مستخدمين</div>';
            }
        }

        // إزالة مستخدم من القائمة المحددة
        window.removeSelectedUser = function(userId) {
            selectedUsers.delete(userId);
            renderUsers();
        };

        // بدء محادثة جديدة مع مستخدم
        async function startConversationWithUser(user) {
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=create_conversation&user_id=${user.id}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // تحديث واجهة المحادثة
                    noChatSelected.style.display = 'none';
                    chatHeader.style.display = 'flex';
                    chatMessages.style.display = 'flex';
                    chatInput.style.display = 'flex';
                    
                    // تحديث رأس المحادثة
                    chatUserAvatar.textContent = user.initial;
                    chatUserName.textContent = user.name;
                    chatUserStatus.textContent = `الهاتف: ${user.phone}`;
                    chatUserStatus.title = `البريد: ${user.email}`;
                    
                    // تحديث حالة المحادثة
                    updateStatusDisplay('active');
                    
                    // تحميل الرسائل إذا كانت محادثة موجودة
                    if (result.existing) {
                        await loadMessages(result.conversation_id);
                    } else {
                        chatMessages.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--gray);">ابدأ المحادثة بإرسال رسالة...</div>';
                    }
                    
                    currentConversationId = result.conversation_id;
                    currentUser = user;
                    
                    // إعادة تحميل المحادثات
                    loadConversations();
                }
            } catch (error) {
                console.error('Error starting conversation:', error);
            }
        }

        // تحديث عدد الإشعارات
        function updateNotificationCount() {
            const unreadCount = allConversations.reduce((total, conv) => total + conv.unread_count, 0);
            notificationCount.textContent = unreadCount;
        }

        // تحديد محادثة
        async function selectConversation(conversationId) {
            currentConversationId = conversationId;
            const conversation = allConversations.find(conv => conv.id === conversationId);
            
            if (!conversation) return;
            
            // تحديث واجهة المحادثة
            noChatSelected.style.display = 'none';
            chatHeader.style.display = 'flex';
            chatMessages.style.display = 'flex';
            chatInput.style.display = 'flex';
            
            // تحديث رأس المحادثة
            chatUserAvatar.textContent = conversation.user_initial;
            chatUserName.textContent = conversation.user_name;
            chatUserStatus.textContent = `الهاتف: ${conversation.user_phone || 'غير متوفر'}`;
            chatUserStatus.title = `البريد: ${conversation.user_email}`;
            
            // تحديث حالة المحادثة
            updateStatusDisplay(conversation.status);
            
            // تحميل الرسائل
            await loadMessages(conversationId);
            
            // إعادة تحميل قائمة المحادثات لتحديد النشطة
            renderConversations();
            
            // العثور على المستخدم في قائمة المستخدمين
            currentUser = allUsers.find(u => u.id == conversation.user_id);
        }

        // تحميل الرسائل من الخادم
        async function loadMessages(conversationId) {
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_messages&conversation_id=${conversationId}`
                });
                
                const messages = await response.json();
                renderMessages(messages);
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        // عرض الرسائل
        function renderMessages(messages) {
            chatMessages.innerHTML = '';
            
            let currentDate = '';
            
            messages.forEach(message => {
                // إضافة تاريخ جديد إذا تغير
                if (message.date !== currentDate) {
                    currentDate = message.date;
                    const dateHeader = document.createElement('div');
                    dateHeader.className = 'date-header';
                    dateHeader.style.textAlign = 'center';
                    dateHeader.style.margin = '10px 0';
                    dateHeader.style.color = 'var(--gray)';
                    dateHeader.style.fontSize = '12px';
                    dateHeader.textContent = formatDate(message.date);
                    chatMessages.appendChild(dateHeader);
                }
                
                const messageElement = document.createElement('div');
                messageElement.className = `message ${message.type}`;
                messageElement.innerHTML = `
                    <div class="message-text">${message.text}</div>
                    <div class="message-time">${message.time}</div>
                `;
                chatMessages.appendChild(messageElement);
            });
            
            // التمرير لأسفل لعرض أحدث الرسائل
            setTimeout(() => {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, 100);
        }

        // إرسال رسالة جديدة
        async function sendMessage() {
            const messageText = messageInput.value.trim();
            if (messageText === '' || !currentConversationId) return;
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=send_message&conversation_id=${currentConversationId}&message=${encodeURIComponent(messageText)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // إضافة الرسالة للواجهة
                    const now = new Date();
                    const timeString = formatTime(now);
                    
                    const messageElement = document.createElement('div');
                    messageElement.className = 'message sent';
                    messageElement.innerHTML = `
                        <div class="message-text">${messageText}</div>
                        <div class="message-time">${timeString}</div>
                    `;
                    
                    chatMessages.appendChild(messageElement);
                    messageInput.value = '';
                    
                    // التمرير لأسفل
                    setTimeout(() => {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }, 100);
                    
                    // إعادة تحميل المحادثات
                    loadConversations();
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        }

        // إرسال رسالة جماعية
        async function sendBulkMessage() {
            const messageText = bulkMessage.value.trim();
            if (messageText === '' || selectedUsers.size === 0) {
                alert('يرجى تحديد مستخدمين وكتابة رسالة');
                return;
            }
            
            if (!confirm(`هل تريد إرسال هذه الرسالة إلى ${selectedUsers.size} مستخدم؟`)) {
                return;
            }
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=send_bulk_message&users=${JSON.stringify([...selectedUsers])}&message=${encodeURIComponent(messageText)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(`✅ تم إرسال الرسالة إلى ${result.sent} مستخدم`);
                    bulkMessageModal.style.display = 'none';
                    bulkMessage.value = '';
                    selectedUsers.clear();
                    renderUsers();
                }
            } catch (error) {
                console.error('Error sending bulk message:', error);
                alert('حدث خطأ أثناء إرسال الرسائل');
            }
        }

        // تحديث حالة المحادثة
        async function updateConversationStatus(status) {
            if (!currentConversationId) return;
            
            try {
                await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_conversation_status&conversation_id=${currentConversationId}&status=${status}`
                });
                
                loadConversations();
            } catch (error) {
                console.error('Error updating conversation status:', error);
            }
        }

        // وظائف مساعدة
        function getStatusText(status) {
            const statusMap = {
                'active': 'نشط',
                'pending': 'معلق',
                'closed': 'مغلق'
            };
            return statusMap[status] || status;
        }

        function updateStatusDisplay(status) {
            const statusMap = {
                'active': { color: 'var(--success)', text: 'نشط' },
                'pending': { color: 'var(--warning)', text: 'معلق' },
                'closed': { color: 'var(--gray)', text: 'مغلق' }
            };
            
            const statusInfo = statusMap[status] || statusMap['pending'];
            statusDot.style.backgroundColor = statusInfo.color;
            statusText.textContent = statusInfo.text;
        }

        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('ar-SA', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const today = new Date();
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            
            if (date.toDateString() === today.toDateString()) {
                return 'اليوم';
            } else if (date.toDateString() === yesterday.toDateString()) {
                return 'أمس';
            } else {
                return date.toLocaleDateString('ar-SA');
            }
        }

        // البحث في المحادثات
        function searchConversationsHandler() {
            renderConversations();
        }

        // إضافة مستمعي الأحداث
        function addEventListeners() {
            // تبديل الشريط الجانبي
            toggleSidebar.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('expanded');
            });

            // فلتر المحادثات
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    currentFilter = button.getAttribute('data-filter');
                    renderConversations();
                });
            });

            // البحث في المحادثات
            searchConversations.addEventListener('input', searchConversationsHandler);

            // إرسال رسالة
            sendMessageBtn.addEventListener('click', sendMessage);
            
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });

            // إغلاق المحادثة
            closeChatBtn.addEventListener('click', () => {
                if (confirm('هل تريد إغلاق هذه المحادثة؟')) {
                    updateConversationStatus('closed');
                }
            });

            // معلومات المستخدم
            userInfoBtn.addEventListener('click', () => {
                if (currentUser) {
                    alert(`
                        معلومات المستخدم:
                        الاسم: ${currentUser.name}
                        البريد: ${currentUser.email}
                        الهاتف: ${currentUser.phone}
                        نوع الحساب: ${currentUser.type}
                        تاريخ التسجيل: ${currentUser.join_date}
                        آخر نشاط: ${currentUser.last_activity || 'غير متوفر'}
                    `);
                }
            });

            // رسالة جماعية
            bulkMessageBtn.addEventListener('click', () => {
                if (selectedUsers.size === 0) {
                    alert('يرجى تحديد مستخدمين أولاً من القائمة على اليمين');
                    return;
                }
                bulkMessageModal.style.display = 'flex';
            });

            // إغلاق نافذة الرسائل الجماعية
            closeBulkModal.addEventListener('click', () => {
                bulkMessageModal.style.display = 'none';
            });

            cancelBulkBtn.addEventListener('click', () => {
                bulkMessageModal.style.display = 'none';
            });

            // إرسال الرسائل الجماعية
            sendBulkBtn.addEventListener('click', sendBulkMessage);
            
            // إغلاق النافذة المنبثقة عند النقر خارجها
            window.addEventListener('click', (e) => {
                if (e.target === bulkMessageModal) {
                    bulkMessageModal.style.display = 'none';
                }
            });
        }

        // تنظيف عند إغلاق الصفحة
        window.addEventListener('beforeunload', () => {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });
    </script>
</body>
</html>