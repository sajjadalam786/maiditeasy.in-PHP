<?php
// Admin Panel: Contact Settings Management
// Fixed Admin Password: maiditeasy2026
session_start();

$admin_password_hash = '$2y$10$7rXm7/H7iBvhOQO37eQ93.B6qJ4g6m4z5hL1v4m8N9l0P1Q2R3S4T'; // hash for maiditeasy2026
$target_password = 'maiditeasy2026';

$error = '';
$success = '';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header("Location: contact.php");
    exit;
}

// Handle Login
if (isset($_POST['login_submit'])) {
    $input_pass = isset($_POST['password']) ? trim($_POST['password']) : '';
    if ($input_pass === $target_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = 'Invalid password. Please try again.';
    }
}

// Check Authentication
$is_authenticated = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

$data_file = __DIR__ . '/../includes/contact-data.json';

// Handle Settings Update
if ($is_authenticated && isset($_POST['save_settings'])) {
    $new_data = [
        'whatsapp_raw'  => trim($_POST['whatsapp_raw']),
        'whatsapp_text' => trim($_POST['whatsapp_text']),
        'phone_raw'     => trim($_POST['phone_raw']),
        'phone_text'    => trim($_POST['phone_text']),
        'contact_email' => trim($_POST['contact_email'])
    ];
    
    if (file_put_contents($data_file, json_encode($new_data, JSON_PRETTY_PRINT))) {
        $success = 'Contact settings updated successfully across the entire site!';
    } else {
        $error = 'Failed to save settings. Please check directory permissions.';
    }
}

// Load current values
$current_data = [
    'whatsapp_raw'  => '919866769832',
    'whatsapp_text' => '+91 9866769832',
    'phone_raw'     => '9866769832',
    'phone_text'    => '+91 98667 69832',
    'contact_email' => 'maiditeasy21@gmail.com'
];

if (file_exists($data_file)) {
    $decoded = json_decode(file_get_contents($data_file), true);
    if (is_array($decoded)) {
        $current_data = array_merge($current_data, $decoded);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Contact Settings | Maid It Easy</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #f4f6f9; color: #1e293b; padding: 30px 15px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .admin-card { background: #ffffff; width: 100%; max-width: 650px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
        .admin-header { background: #121139; color: #ffffff; padding: 35px 30px; text-align: center; position: relative; }
        .badge-admin { background: #ffc107; color: #000; font-weight: 800; font-size: 11px; padding: 6px 14px; border-radius: 6px; letter-spacing: 0.5px; text-transform: uppercase; display: inline-block; position: absolute; top: 25px; left: 30px; }
        .btn-logout { background: #dc3545; color: #fff; font-weight: 700; font-size: 12px; padding: 7px 16px; border-radius: 8px; text-decoration: none; position: absolute; top: 25px; right: 30px; transition: 0.2s; }
        .btn-logout:hover { background: #bb2d3b; }
        .admin-header h1 { font-size: 26px; font-weight: 800; margin-top: 15px; margin-bottom: 8px; }
        .admin-header p { font-size: 13px; color: #cbd5e1; font-weight: 500; }
        .admin-body { padding: 35px 30px; }
        .form-group { margin-bottom: 22px; }
        .form-label { font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 8px; display: block; }
        .form-control { width: 100%; height: 50px; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 10px 16px; font-size: 14px; font-weight: 600; color: #0f172a; transition: all 0.2s; outline: none; background: #fff; }
        .form-control:focus { border-color: #121139; box-shadow: 0 0 0 3px rgba(18,17,57,0.1); }
        .help-text { font-size: 12px; color: #64748b; margin-top: 6px; }
        .code-tag { color: #e83e8c; font-family: monospace; font-weight: 600; background: #fdf2f8; padding: 2px 5px; border-radius: 4px; }
        .hr-divider { border: 0; height: 1px; background: #e2e8f0; margin: 25px 0; }
        .btn-save { width: 100%; height: 52px; background: #121139; color: #ffffff; border: none; border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-save:hover { background: #1e1b4b; transform: translateY(-1px); }
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 20px; }
        .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>

<div class="admin-card">
    <?php if (!$is_authenticated): ?>
        <!-- Login Form -->
        <div class="admin-header">
            <span class="badge-admin">ADMIN LOGIN</span>
            <h1 style="margin-top: 25px;">Protected Admin Area</h1>
            <p>Enter the master password to access contact settings</p>
        </div>
        <div class="admin-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="contact.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Admin Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password " required autofocus>
                </div>
                <button type="submit" name="login_submit" class="btn-save">Unlock Dashboard</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Contact Settings Form -->
        <div class="admin-header">
            <span class="badge-admin">ADMIN PANEL</span>
            <a href="contact.php?action=logout" class="btn-logout">Logout</a>
            <h1>Website Contact Settings</h1>
            <p>Dynamically update WhatsApp, Phone Call numbers & Email across the site</p>
        </div>
        
        <div class="admin-body">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="contact.php" method="POST">
                <div class="form-group">
                    <label class="form-label">WhatsApp Number (Raw / Direct Link)</label>
                    <input type="text" name="whatsapp_raw" class="form-control" value="<?php echo htmlspecialchars($current_data['whatsapp_raw']); ?>" required>
                    <div class="help-text">Used for <span class="code-tag">wa.me/NUMBER</span> or <span class="code-tag">phone=NUMBER</span> links (include country code without +).</div>
                </div>

                <div class="form-group">
                    <label class="form-label">WhatsApp Number (Display Text)</label>
                    <input type="text" name="whatsapp_text" class="form-control" value="<?php echo htmlspecialchars($current_data['whatsapp_text']); ?>" required>
                </div>

                <hr class="hr-divider">

                <div class="form-group">
                    <label class="form-label">Call Phone Number (Raw / Tel Link)</label>
                    <input type="text" name="phone_raw" class="form-control" value="<?php echo htmlspecialchars($current_data['phone_raw']); ?>" required>
                    <div class="help-text">Used for <span class="code-tag">tel:NUMBER</span> click-to-call links.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Call Phone Number (Display Text)</label>
                    <input type="text" name="phone_text" class="form-control" value="<?php echo htmlspecialchars($current_data['phone_text']); ?>" required>
                </div>

                <hr class="hr-divider">

                <div class="form-group">
                    <label class="form-label">Contact Email Address</label>
                    <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($current_data['contact_email']); ?>" required>
                    <div class="help-text">Used for <span class="code-tag">mailto:EMAIL</span> links and footer/header contact info.</div>
                </div>

                <button type="submit" name="save_settings" class="btn-save">Save & Apply Changes Everywhere</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
