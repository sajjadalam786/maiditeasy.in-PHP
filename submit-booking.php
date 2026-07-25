<?php
/*
  GOOGLE SHEETS INTEGRATION GUIDE:
  1. Open your Google Sheet: https://docs.google.com/spreadsheets/d/1knPoIfQIcUWNXdbm9Qs6WId0Jxk6LRXi93zOrhSuiS4/edit
  2. Click Extensions > Apps Script.
  3. Delete any default code and paste this script:

     function doPost(e) {
       var sheet = SpreadsheetApp.openById("1knPoIfQIcUWNXdbm9Qs6WId0Jxk6LRXi93zOrhSuiS4").getActiveSheet();
       var rowData = [];
       rowData.push(new Date()); // Timestamp
       rowData.push(e.parameter.name);
       rowData.push(e.parameter.phone);
       rowData.push(e.parameter.email);
       rowData.push(e.parameter.city);
       rowData.push(e.parameter.service);
       rowData.push(e.parameter.urgency);
       rowData.push(e.parameter.referrer);
       rowData.push(e.parameter.message);
       sheet.appendRow(rowData);
       return ContentService.createTextOutput("Success").setMimeType(ContentService.MimeType.TEXT);
     }

  4. Click Deploy > New Deployment.
  5. Select type "Web App". Set "Execute as" to "Me", and "Who has access" to "Anyone".
  6. Deploy, copy the Web App URL, and paste it into GOOGLE_SHEET_WEBHOOK_URL in .env!
  
  * HOW TO BYPASS GOOGLE AUTH WARNING:
    - Click the "Advanced" link in the bottom left of the Google popup.
    - Click "Go to Untitled project (unsafe)" (or your script name) at the bottom.
    - Click "Allow" on the next screen to authorize!
*/

function get_env_var($key, $default = '') {
    $val = '';
    $config_file = __DIR__ . '/config.env';
    if (file_exists($config_file)) {
        $lines = file($config_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $v) = explode('=', $line, 2);
                if (trim($name) === $key && !empty(trim($v))) {
                    $val = trim($v);
                }
            }
        }
    }
    $env_file = __DIR__ . '/.env';
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $v) = explode('=', $line, 2);
                if (trim($name) === $key && !empty(trim($v))) {
                    $val = trim($v);
                }
            }
        }
    }
    if (!empty($val)) {
        return $val;
    }
    if ($key === 'GOOGLE_SHEET_WEBHOOK_URL') {
        return 'https://script.google.com/macros/s/AKfycbyVJrWK6xbv2BONHiob2PJ-ijspDU8pQbgys11LurIjvOaqYaFe4EJJiyPndU-02_KrOw/exec';
    }
    if ($key === 'RECIPIENT_EMAIL') {
        return 'wholesalehouse2016@gmail.com, maiditeasy999@gmail.com';
    }
    return $default;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $phone = strip_tags(trim($_POST["phone"]));
    $alternate_phone = isset($_POST["alternate_phone"]) ? strip_tags(trim($_POST["alternate_phone"])) : '';
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $city = isset($_POST["city"]) ? strip_tags(trim($_POST["city"])) : '';
    $service = strip_tags(trim($_POST["service"]));
    $urgency = isset($_POST["urgency"]) ? strip_tags(trim($_POST["urgency"])) : '';
    $referrer = isset($_POST["referrer"]) ? strip_tags(trim($_POST["referrer"])) : '';
    $message = isset($_POST["message"]) ? strip_tags(trim($_POST["message"])) : '';
    
    if (empty($name) || empty($phone) || empty($email) || empty($service)) {
        header("Location: index.php");
        exit;
    }
    
    // 1. Send Email Notification
    $recipient_str = get_env_var('RECIPIENT_EMAIL', 'wholesalehouse2016@gmail.com, maiditeasy999@gmail.com');
    $recipients = array_filter(array_map('trim', explode(',', $recipient_str)));
    
    $subject = "New Maid It Easy Booking Request from " . $name;
    
    $email_body = "<html><body style='font-family: Arial, sans-serif; color: #333;'>";
    $email_body .= "<h2 style='color:#0e0035; border-bottom: 2px solid #ff890c; padding-bottom: 8px;'>New Booking Request</h2>";
    $email_body .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse; width:100%; max-width:600px; border-color: #ddd;'>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold; width:35%;'>Full Name:</td><td>" . htmlspecialchars($name) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Phone:</td><td>" . htmlspecialchars($phone) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Alternate Phone:</td><td>" . htmlspecialchars($alternate_phone) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Email:</td><td>" . htmlspecialchars($email) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>City:</td><td>" . htmlspecialchars($city) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Service:</td><td>" . htmlspecialchars($service) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Urgency:</td><td>" . htmlspecialchars($urgency) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>How heard:</td><td>" . htmlspecialchars($referrer) . "</td></tr>";
    $email_body .= "<tr><td style='background:#f8f9fa; font-weight:bold;'>Message/Remarks:</td><td>" . nl2br(htmlspecialchars($message)) . "</td></tr>";
    $email_body .= "</table></body></html>";
    
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Maid It Easy Booking <no-reply@maiditeasy.in>\r\n";
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $headers .= "Reply-To: " . $email . "\r\n";
    }
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    foreach ($recipients as $to_email) {
        if (filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            @mail($to_email, $subject, $email_body, $headers, "-fno-reply@maiditeasy.in");
        }
    }
    
    // 2. Trigger Webhook API
    $webhook_url = get_env_var('BOOKING_WEBHOOK_URL');
    if (!empty($webhook_url) && filter_var($webhook_url, FILTER_VALIDATE_URL) && strpos($webhook_url, 'example.com') === false) {
        $payload = json_encode([
            'event' => 'new_booking',
            'timestamp' => date('c'),
            'data' => [
                'name' => $name,
                'phone' => $phone,
                'alternate_phone' => $alternate_phone,
                'email' => $email,
                'city' => $city,
                'service' => $service,
                'urgency' => $urgency,
                'referrer' => $referrer,
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
            'email' => $email,
            'city' => $city,
            'service' => $service,
            'urgency' => $urgency,
            'referrer' => $referrer,
            'message' => $message
        ];
        
        $ch = curl_init($sheet_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Required for Google script redirect
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
    header("Location: index.php");
    exit;
}
?>
