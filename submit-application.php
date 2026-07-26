<?php
function get_env_var($key, $default = '') {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) {
        $env_file = __DIR__ . '/config.env';
    }
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                if (trim($name) == $key && !empty(trim($value))) {
                    return trim($value);
                }
            }
        }
    }
    if ($key === 'RECIPIENT_EMAIL') {
        return 'wholesalehouse2016@gmail.com, maiditeasy999@gmail.com';
    }
    return $default;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $phone = strip_tags(trim($_POST["phone"]));
    $alternate_phone = strip_tags(trim($_POST["alternate_phone"]));
    $city = isset($_POST["city"]) ? strip_tags(trim($_POST["city"])) : '';
    $age = strip_tags(trim($_POST["age"]));
    $gender = strip_tags(trim($_POST["gender"]));
    $role = strip_tags(trim($_POST["role"]));
    $experience = strip_tags(trim($_POST["experience"]));
    $salary = strip_tags(trim($_POST["salary"]));
    $work_type = strip_tags(trim($_POST["work_type"]));
    $location = strip_tags(trim($_POST["location"]));
    $message = strip_tags(trim($_POST["message"]));
    
    if (empty($name) || empty($phone) || empty($role) || empty($location)) {
        header("Location: pages/career.php");
        exit;
    }
    
    // 1. Send Email Notification
    $recipient_str = get_env_var('RECIPIENT_EMAIL', 'wholesalehouse2016@gmail.com, maiditeasy999@gmail.com');
    $recipients = array_filter(array_map('trim', explode(',', $recipient_str)));
    
    $subject = "New Job Application: " . $role . " - " . $name;
    
    $email_body = "<html><body style='font-family: Arial, sans-serif; color: #333;'>";
    $email_body .= "<h2 style='color:#0e0035; border-bottom: 2px solid #ff890c; padding-bottom: 8px;'>New Job Application</h2>";
    $email_body .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse; width:100%; max-width:600px; border-color: #ddd;'>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold; width:35%;'>Full Name:</td><td>" . htmlspecialchars($name) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Phone:</td><td>" . htmlspecialchars($phone) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Alternate Phone:</td><td>" . htmlspecialchars($alternate_phone) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>City:</td><td>" . htmlspecialchars($city) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Age:</td><td>" . htmlspecialchars($age) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Gender:</td><td>" . htmlspecialchars($gender) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Role Applied For:</td><td>" . htmlspecialchars($role) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Experience:</td><td>" . htmlspecialchars($experience) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Expected Salary:</td><td>" . htmlspecialchars($salary) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Work Type:</td><td>" . htmlspecialchars($work_type) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Preferred Location:</td><td>" . htmlspecialchars($location) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Remarks:</td><td>" . nl2br(htmlspecialchars($message)) . "</td></tr>";
    $email_body .= "</table></body></html>";
    
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Maid It Easy Careers <no-reply@maiditeasy.in>\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    foreach ($recipients as $to_email) {
        if (filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            @mail($to_email, $subject, $email_body, $headers, "-fno-reply@maiditeasy.in");
        }
    }
    
    // 2. Trigger Webhook API
    $webhook_url = get_env_var('APPLICATION_WEBHOOK_URL');
    if (!empty($webhook_url) && filter_var($webhook_url, FILTER_VALIDATE_URL)) {
        $payload = json_encode([
            'event' => 'new_application',
            'timestamp' => date('c'),
            'data' => [
                'name' => $name,
                'phone' => $phone,
                'alternate_phone' => $alternate_phone,
                'city' => $city,
                'age' => $age,
                'gender' => $gender,
                'role' => $role,
                'experience' => $experience,
                'salary' => $salary,
                'work_type' => $work_type,
                'location' => $location,
                'message' => $message
            ]
        ]);
        
        $ch = curl_init($webhook_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    // 3. Forward to Google Sheets Web App
    $sheet_url = get_env_var('GOOGLE_SHEET_WEBHOOK_URL', 'https://script.google.com/macros/s/AKfycbyVJrWK6xbv2BONHiob2PJ-ijspDU8pQbgys11LurIjvOaqYaFe4EJJiyPndU-02_KrOw/exec');
    if (!empty($sheet_url) && filter_var($sheet_url, FILTER_VALIDATE_URL)) {
        $post_fields = [
            'name' => $name,
            'phone' => $phone,
            'email' => "Alt: $alternate_phone | Age: $age | $gender",
            'city' => $city,
            'service' => "Job: $role",
            'urgency' => "Exp: $experience | Sal: $salary | $work_type",
            'referrer' => $location,
            'message' => $message
        ];
        
        $ch = curl_init($sheet_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
        curl_exec($ch);
        curl_close($ch);
    }
    
    header("Location: pages/book-now-thank-you.php");
    exit;
} else {
    header("Location: pages/career.php");
    exit;
}
?>
