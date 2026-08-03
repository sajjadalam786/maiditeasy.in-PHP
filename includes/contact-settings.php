<?php
// Dynamic Contact Settings Loader
// Fixed Admin Password comment: maiditeasy2026

$contact_data_file = __DIR__ . '/contact-data.json';

// Default values
$default_contact = [
    'whatsapp_raw' => '919866769832',
    'whatsapp_text' => '+91 9866769832',
    'phone_raw' => '9866769832',
    'phone_text' => '+91 98667 69832',
    'contact_email' => 'maiditeasy21@gmail.com'
];

if (file_exists($contact_data_file)) {
    $file_content = file_get_contents($contact_data_file);
    $decoded = json_decode($file_content, true);
    if (is_array($decoded)) {
        $default_contact = array_merge($default_contact, $decoded);
    }
}

$whatsapp_raw  = $default_contact['whatsapp_raw'];
$whatsapp_text = $default_contact['whatsapp_text'];
$phone_raw     = $default_contact['phone_raw'];
$phone_text    = $default_contact['phone_text'];
$contact_email = $default_contact['contact_email'];
?>
