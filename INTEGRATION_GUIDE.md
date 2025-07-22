# Integration Guide - Modernized HerMatchUp

## 🔄 How to Integrate New Libraries with Existing Files

This guide shows how to upgrade your existing PHP and HTML files to use the new modern architecture and libraries.

---

## 📄 Upgrading HTML Files

### Before (Old Structure):
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HerMatchUp</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- content -->
    <script src="script.js"></script>
</body>
</html>
```

### After (Modern Structure):
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HerMatchUp - Women in STEM</title>
    
    <!-- Modern CSS Bundle -->
    <link rel="stylesheet" href="dist/css/vendors.css">
    <link rel="stylesheet" href="dist/css/main.css">
    
    <!-- Keep existing custom styles for backwards compatibility -->
    <link rel="stylesheet" href="css/indexStyles.css">
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="HerMatchUp - Connecting women in STEM for mentorship and collaboration">
    <meta name="keywords" content="STEM, women, mentorship, technology, engineering, science">
</head>
<body>
    <!-- content -->
    
    <!-- Modern JavaScript Bundle -->
    <script src="dist/js/vendors.bundle.js"></script>
    <script src="dist/js/main.bundle.js"></script>
    
    <!-- Keep existing scripts for backwards compatibility -->
    <script src="script.js"></script>
</body>
</html>
```

---

## 🔧 Upgrading PHP Files

### Before (Old PHP Structure):
```php
<?php
session_start();
include 'db_connection.php';

// Basic error handling
if (!$conn) {
    die("Connection failed");
}

// Direct SQL queries
$query = "SELECT * FROM users WHERE id = " . $_GET['id'];
$result = mysqli_query($conn, $query);
?>
```

### After (Modern PHP Structure):
```php
<?php
// Load modern bootstrap
require_once 'bootstrap.php';

use App\Database\DatabaseConnection;
use App\Utils\Validator;
use App\Utils\Response;

// Modern authentication check
if (!isset($_SESSION['user_id'])) {
    Response::redirect('login.php');
}

try {
    // Modern database connection
    $db = DatabaseConnection::getInstance();
    
    // Input validation
    $userId = Validator::sanitizeInt($_GET['id'] ?? 0);
    if (!$userId) {
        Response::error('Invalid user ID', 400);
    }
    
    // Safe query with Doctrine DBAL
    $query = $db->createQueryBuilder()
        ->select('*')
        ->from('users')
        ->where('id = :id')
        ->setParameter('id', $userId);
    
    $user = $query->execute()->fetchAssociative();
    
    if (!$user) {
        Response::error('User not found', 404);
    }
    
} catch (Exception $e) {
    logError('Database error: ' . $e->getMessage());
    Response::error('An error occurred', 500);
}
?>
```

---

## 📱 Upgrading JavaScript Files

### Before (Old JavaScript):
```javascript
// Old vanilla JavaScript
function sendMessage() {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'send_message.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('messages').innerHTML += xhr.responseText;
        }
    };
    
    var message = document.getElementById('messageInput').value;
    xhr.send('message=' + encodeURIComponent(message));
}
```

### After (Modern JavaScript):
```javascript
// Modern JavaScript with Axios and SweetAlert2
async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message) {
        Swal.fire({
            icon: 'warning',
            title: 'Empty Message',
            text: 'Please enter a message before sending.'
        });
        return;
    }
    
    try {
        const response = await axios.post('/api/send-message.php', {
            message: message,
            conversation_id: currentConversationId
        });
        
        if (response.data.status === 'success') {
            // Add message to UI
            addMessageToChat(response.data.message);
            messageInput.value = '';
            
            // Success notification
            Swal.fire({
                icon: 'success',
                title: 'Message Sent!',
                timer: 1500,
                showConfirmButton: false
            });
        }
    } catch (error) {
        console.error('Failed to send message:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to send message. Please try again.'
        });
    }
}
```

---

## 🎨 Upgrading CSS with Bootstrap

### Before (Custom CSS):
```css
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.btn {
    background: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.form-group {
    margin-bottom: 20px;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
```

### After (Bootstrap + Custom):
```css
/* Use Bootstrap classes for base styling */
/* Custom overrides for brand-specific styling */

:root {
    --primary-color: #ff6b9d;
    --secondary-color: #667eea;
}

/* Override Bootstrap variables */
.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background-color: #e91e63;
    border-color: #e91e63;
}

/* Custom components */
.chat-message {
    @apply p-3 rounded-lg mb-2 max-w-xs;
}

.chat-message.sent {
    @apply bg-blue-500 text-white ml-auto;
}

.chat-message.received {
    @apply bg-gray-200 text-gray-800;
}
```

---

## 🔗 Step-by-Step Migration Process

### 1. **Backup Existing Files**
```bash
# Create backup
cp -r . ../hermatchup-backup/
```

### 2. **Install Dependencies**
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. **Build Assets**
```bash
# Build modern assets
npm run build
```

### 4. **Update File Headers**
Replace old includes with modern bootstrap:
```php
// Old
include 'db_connection.php';

// New
require_once 'bootstrap.php';
use App\Database\DatabaseConnection;
```

### 5. **Update HTML Templates**
Replace old CSS/JS includes with bundled versions:
```html
<!-- Old -->
<link rel="stylesheet" href="styles.css">
<script src="script.js"></script>

<!-- New -->
<link rel="stylesheet" href="dist/css/vendors.css">
<link rel="stylesheet" href="dist/css/main.css">
<script src="dist/js/vendors.bundle.js"></script>
<script src="dist/js/main.bundle.js"></script>
```

### 6. **Update JavaScript Calls**
Replace vanilla JavaScript with modern library calls:
```javascript
// Old
var xhr = new XMLHttpRequest();

// New
const response = await axios.post('/api/endpoint.php', data);
```

### 7. **Update PHP Database Calls**
Replace direct MySQL calls with Doctrine DBAL:
```php
// Old
$result = mysqli_query($conn, $query);

// New
$result = $db->createQueryBuilder()
    ->select('*')
    ->from('table')
    ->execute();
```

---

## 📋 Testing Checklist

After migration, verify:

- [ ] **Page Loading**: All pages load without errors
- [ ] **Styling**: Bootstrap components render correctly
- [ ] **JavaScript**: Modern functionality works (AJAX, notifications)
- [ ] **Database**: Queries execute properly with new connection
- [ ] **Forms**: Form validation and submission work
- [ ] **Responsive**: Mobile responsiveness with Bootstrap
- [ ] **Errors**: Error handling and logging function
- [ ] **Security**: Input validation and sanitization work

---

## 🚨 Common Issues and Solutions

### Issue 1: CSS Conflicts
**Problem**: Existing styles conflict with Bootstrap
**Solution**: Use CSS specificity or scope existing styles
```css
/* Scope existing styles */
.legacy-component {
    /* your existing styles */
}

/* Or use !important carefully */
.custom-button {
    background-color: #ff6b9d !important;
}
```

### Issue 2: JavaScript Errors
**Problem**: Old JavaScript conflicts with new libraries
**Solution**: Wrap in try-catch and check for dependencies
```javascript
if (typeof axios !== 'undefined') {
    // Use modern approach
    axios.post('/api/endpoint.php', data);
} else {
    // Fallback to old approach
    var xhr = new XMLHttpRequest();
}
```

### Issue 3: Database Connection Issues
**Problem**: Old connection methods don't work
**Solution**: Ensure proper environment configuration
```php
// Check if .env is loaded
if (!isset($_ENV['DB_HOST'])) {
    die('Environment not configured. Please set up .env file.');
}
```

---

## ⚡ Performance Tips

1. **Lazy Loading**: Load heavy components only when needed
2. **Caching**: Use proper cache headers for static assets
3. **Minification**: Use minified versions in production
4. **CDN**: Consider CDN for popular libraries
5. **Progressive Enhancement**: Ensure basic functionality without JavaScript

---

## 🔄 Gradual Migration Strategy

1. **Phase 1**: Update build system and dependencies
2. **Phase 2**: Migrate CSS to Bootstrap gradually
3. **Phase 3**: Update JavaScript to use modern libraries
4. **Phase 4**: Modernize PHP backend
5. **Phase 5**: Add new features with modern architecture

This approach allows you to maintain a working application throughout the migration process.