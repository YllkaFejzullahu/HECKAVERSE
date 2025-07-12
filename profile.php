<?php
session_start();

// Use session-stored data if available
if (isset($_SESSION['profile_data'])) {
    $profile = array_merge($profile ?? [], $_SESSION['profile_data']);
    unset($_SESSION['profile_data']);
}

require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user profile data
$stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

// Fetch user data from users table
$user_stmt = $conn->prepare("SELECT email, role FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Debugging - uncomment to see the data
// echo '<pre>';
// print_r($profile);
// print_r($user);
// echo '</pre>';
// exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HerMatchUp</title>
    <link rel="stylesheet" href="css/profileCss.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo" onclick="window.location.href='index.php'">
                <i class="fas fa-handshake"></i>
                <span>HerMatchUp</span>
            </div>
            <div class="nav-menu">
                <a href="swipe.php" class="nav-link">Find Matches</a>
                <a href="matches.html" class="nav-link">My Matches</a>
                <button class="btn-secondary" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i>
                Log Out
            </button>
            </div>
        </div>
    </nav>

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="profile-container">
            <div class="profile-header">
                <h1>My Profile</h1>
                <button class="btn-primary" id="edit-profile-btn">
                    <i class="fas fa-edit"></i>
                    Edit Profile
                </button>
            </div>

            <div class="profile-content">
                <div class="profile-main">
                    <div class="profile-card">
                        <div class="profile-image-section">
                            <div class="profile-avatar">
                                <div class="avatar-placeholder" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <i class="fas fa-user"></i>
                                </div>
                                <button class="change-photo-btn">
                                    <i class="fas fa-camera"></i>
                                </button>
                                <input type="file" id="profile-photo" accept="image/*" style="display: none;">
                            </div>
                            <div class="profile-basic-info">
                                <h2 id="profile-name"><?php echo htmlspecialchars($profile['full_name'] ?? 'New User'); ?></h2>
                                <p id="profile-title"><?php echo htmlspecialchars($profile['stem_field'] ?? 'STEM Professional'); ?></p>
                                <p id="profile-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($profile['location'] ?? 'Location not set'); ?>
                                </p>
                                <div class="profile-badges">
                                    <span class="badge <?php echo ($user['role'] === 'mentor') ? 'mentor-badge' : 'mentee-badge'; ?>">
                                        <?php echo ucfirst($user['role'] ?? 'user'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="profile-details">
                            <div class="detail-section">
                                <h3>About Me</h3>
                                <p id="profile-bio">
                                    <?php echo htmlspecialchars($profile['goals'] ?? 'Tell us about yourself and your goals'); ?>
                                </p>
                            </div>

                            <div class="detail-section">
                                <h3>STEM Fields & Expertise</h3>
                                <div class="specialization-tags" id="specialization-tags">
                                    <?php 
                                    if (!empty($profile['interests'])) {
                                        $interests = explode(',', $profile['interests']);
                                        foreach ($interests as $interest) {
                                            echo '<span class="tag">'.htmlspecialchars(trim($interest)).'</span>';
                                        }
                                    } else {
                                        echo '<span class="tag">Add your interests</span>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h3>Career Goals</h3>
                                <div class="goals-list">
                                    <div class="goal-item">
                                        <i class="fas fa-target"></i>
                                        <span><?php echo htmlspecialchars($profile['goals'] ?? 'Set your career goals'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h3>Personality & Communication</h3>
                                <div class="personality-grid">
                                    <div class="personality-item">
                                        <span class="personality-label">Communication Style</span>
                                        <span class="personality-value">
                                            <?php echo htmlspecialchars($profile['communication_style'] ?? 'Not specified'); ?>
                                        </span>
                                    </div>
                                    <div class="personality-item">
                                        <span class="personality-label">Personality Type</span>
                                        <span class="personality-value">
                                            <?php echo htmlspecialchars($profile['personality_traits'] ?? 'Not specified'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h3>Availability</h3>
                                <div class="preferences-grid">
                                    <div class="preference-item">
                                        <i class="fas fa-calendar"></i>
                                        <div>
                                            <span class="pref-label">Availability</span>
                                            <span class="pref-value">
                                                <?php echo htmlspecialchars($profile['availability'] ?? 'Not specified'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-sidebar">
                    <div class="sidebar-card">
                        <h3>Profile Completion</h3>
                        <?php
                        $completion = 0;
                        if (!empty($profile['full_name'])) $completion += 20;
                        if (!empty($profile['stem_field'])) $completion += 20;
                        if (!empty($profile['goals'])) $completion += 20;
                        if (!empty($profile['interests'])) $completion += 20;
                        if (!empty($profile['communication_style'])) $completion += 20;
                        ?>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $completion; ?>%"></div>
                        </div>
                        <p><?php echo $completion; ?>% Complete</p>
                    </div>

                    <div class="sidebar-card">
                        <h3>Contact Information</h3>
                        <div class="contact-info">
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Edit Profile Modal -->
    <div class="modal" id="edit-profile-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Profile</h2>
                <button class="close-btn" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-profile-form" action="update_profile.php" method="POST">
                    <div class="form-group">
                        <label for="edit-name">Full Name</label>
                        <input type="text" id="edit-name" name="full_name" value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-title">STEM Field</label>
                        <input type="text" id="edit-title" name="stem_field" value="<?php echo htmlspecialchars($profile['stem_field'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-location">Location</label>
                        <input type="text" id="edit-location" name="location" value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-bio">Goals</label>
                        <textarea id="edit-bio" name="goals" rows="4" required><?php echo htmlspecialchars($profile['goals'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-interests">Interests (comma separated)</label>
                        <textarea id="edit-interests" name="interests" rows="3"><?php echo htmlspecialchars($profile['interests'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-communication">Communication Style</label>
                        <select id="edit-communication" name="communication_style">
                            <option value="">Select style</option>
                            <option value="Friendly and Casual" <?php echo ($profile['communication_style'] ?? '') === 'Friendly and Casual' ? 'selected' : ''; ?>>Friendly & Casual</option>
                            <option value="Professional and Formal" <?php echo ($profile['communication_style'] ?? '') === 'Professional and Formal' ? 'selected' : ''; ?>>Professional & Formal</option>
                            <option value="Supportive and Empathetic" <?php echo ($profile['communication_style'] ?? '') === 'Supportive and Empathetic' ? 'selected' : ''; ?>>Supportive & Empathetic</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-personality">Personality Type</label>
                        <select id="edit-personality" name="personality_traits">
                            <option value="">Select type</option>
                            <option value="INTJ" <?php echo ($profile['personality_traits'] ?? '') === 'INTJ' ? 'selected' : ''; ?>>INTJ - The Architect</option>
                            <option value="INTP" <?php echo ($profile['personality_traits'] ?? '') === 'INTP' ? 'selected' : ''; ?>>INTP - The Logician</option>
                            <!-- Add other MBTI options as needed -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-availability">Availability</label>
                        <input type="text" id="edit-availability" name="availability" value="<?php echo htmlspecialchars($profile['availability'] ?? ''); ?>">
                    </div>
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                </form>
            </div>
            <form id="edit-profile-form" action="update_profile.php" method="POST">
    <!-- your form fields -->
    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

    <div class="modal-footer">
        <button class="btn-secondary" type="button" onclick="closeEditModal()">Cancel</button>
        <button class="btn-primary" type="submit">Save Changes</button>
    </div>
</form>

        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>