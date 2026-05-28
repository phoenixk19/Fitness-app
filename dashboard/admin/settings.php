<?php
// File: dashboard/admin/settings.php
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $settingKey = str_replace('setting_', '', $key);
            $settingValue = sanitize($value);
            $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('$settingKey', '$settingValue') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        }
    }
    echo '<script>alert("Settings saved");</script>';
}

$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM site_settings");
while($row = $result->fetch_assoc()) { $settings[$row['setting_key']] = $row['setting_value']; }
?>
<div class="page-title"><i class="bi bi-gear"></i><h2>System Settings</h2></div>

<div class="data-table">
    <form method="POST">
        <div class="mb-3"><label class="form-label">Site Name</label><input type="text" name="setting_site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'FitCoach Pro'); ?>"></div>
        <div class="mb-3"><label class="form-label">Admin Email</label><input type="email" name="setting_admin_email" class="form-control" value="<?php echo htmlspecialchars($settings['admin_email'] ?? 'admin@fitcoach.com'); ?>"></div>
        <div class="mb-3"><label class="form-label">Contact Email</label><input type="email" name="setting_contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email'] ?? 'support@fitcoachpro.com'); ?>"></div>
        <div class="mb-3"><label class="form-label">Contact Phone</label><input type="text" name="setting_contact_phone" class="form-control" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? '+1 (555) 123-4567'); ?>"></div>
        <div class="mb-3">
            <label class="form-label">Timezone</label>
            <select name="setting_timezone" class="form-select">
                <optgroup label="North America">
                    <option value="America/New_York" <?php echo ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : ''; ?>>Eastern Time (ET)</option>
                    <option value="America/Chicago" <?php echo ($settings['timezone'] ?? '') == 'America/Chicago' ? 'selected' : ''; ?>>Central Time (CT)</option>
                    <option value="America/Denver" <?php echo ($settings['timezone'] ?? '') == 'America/Denver' ? 'selected' : ''; ?>>Mountain Time (MT)</option>
                    <option value="America/Los_Angeles" <?php echo ($settings['timezone'] ?? '') == 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time (PT)</option>
                    <option value="America/Anchorage" <?php echo ($settings['timezone'] ?? '') == 'America/Anchorage' ? 'selected' : ''; ?>>Alaska (AK)</option>
                    <option value="America/Honolulu" <?php echo ($settings['timezone'] ?? '') == 'America/Honolulu' ? 'selected' : ''; ?>>Hawaii (HI)</option>
                    <option value="America/Toronto" <?php echo ($settings['timezone'] ?? '') == 'America/Toronto' ? 'selected' : ''; ?>>Toronto</option>
                    <option value="America/Vancouver" <?php echo ($settings['timezone'] ?? '') == 'America/Vancouver' ? 'selected' : ''; ?>>Vancouver</option>
                </optgroup>
                <optgroup label="Europe">
                    <option value="Europe/London" <?php echo ($settings['timezone'] ?? '') == 'Europe/London' ? 'selected' : ''; ?>>London (GMT)</option>
                    <option value="Europe/Paris" <?php echo ($settings['timezone'] ?? '') == 'Europe/Paris' ? 'selected' : ''; ?>>Paris (CET)</option>
                    <option value="Europe/Berlin" <?php echo ($settings['timezone'] ?? '') == 'Europe/Berlin' ? 'selected' : ''; ?>>Berlin (CET)</option>
                    <option value="Europe/Rome" <?php echo ($settings['timezone'] ?? '') == 'Europe/Rome' ? 'selected' : ''; ?>>Rome (CET)</option>
                    <option value="Europe/Madrid" <?php echo ($settings['timezone'] ?? '') == 'Europe/Madrid' ? 'selected' : ''; ?>>Madrid (CET)</option>
                    <option value="Europe/Amsterdam" <?php echo ($settings['timezone'] ?? '') == 'Europe/Amsterdam' ? 'selected' : ''; ?>>Amsterdam (CET)</option>
                    <option value="Europe/Athens" <?php echo ($settings['timezone'] ?? '') == 'Europe/Athens' ? 'selected' : ''; ?>>Athens (EET)</option>
                    <option value="Europe/Helsinki" <?php echo ($settings['timezone'] ?? '') == 'Europe/Helsinki' ? 'selected' : ''; ?>>Helsinki (EET)</option>
                    <option value="Europe/Moscow" <?php echo ($settings['timezone'] ?? '') == 'Europe/Moscow' ? 'selected' : ''; ?>>Moscow (MSK)</option>
                </optgroup>
                <optgroup label="Asia">
                    <option value="Asia/Dubai" <?php echo ($settings['timezone'] ?? '') == 'Asia/Dubai' ? 'selected' : ''; ?>>Dubai (GST)</option>
                    <option value="Asia/Karachi" <?php echo ($settings['timezone'] ?? '') == 'Asia/Karachi' ? 'selected' : ''; ?>>Karachi (PKT)</option>
                    <option value="Asia/Kolkata" <?php echo ($settings['timezone'] ?? '') == 'Asia/Kolkata' ? 'selected' : ''; ?>>Kolkata (IST)</option>
                    <option value="Asia/Dhaka" <?php echo ($settings['timezone'] ?? '') == 'Asia/Dhaka' ? 'selected' : ''; ?>>Dhaka (BST)</option>
                    <option value="Asia/Bangkok" <?php echo ($settings['timezone'] ?? '') == 'Asia/Bangkok' ? 'selected' : ''; ?>>Bangkok (ICT)</option>
                    <option value="Asia/Singapore" <?php echo ($settings['timezone'] ?? '') == 'Asia/Singapore' ? 'selected' : ''; ?>>Singapore (SGT)</option>
                    <option value="Asia/Tokyo" <?php echo ($settings['timezone'] ?? '') == 'Asia/Tokyo' ? 'selected' : ''; ?>>Tokyo (JST)</option>
                    <option value="Asia/Seoul" <?php echo ($settings['timezone'] ?? '') == 'Asia/Seoul' ? 'selected' : ''; ?>>Seoul (KST)</option>
                    <option value="Asia/Shanghai" <?php echo ($settings['timezone'] ?? '') == 'Asia/Shanghai' ? 'selected' : ''; ?>>Shanghai (CST)</option>
                    <option value="Asia/Hong_Kong" <?php echo ($settings['timezone'] ?? '') == 'Asia/Hong_Kong' ? 'selected' : ''; ?>>Hong Kong (HKT)</option>
                    <option value="Asia/Taipei" <?php echo ($settings['timezone'] ?? '') == 'Asia/Taipei' ? 'selected' : ''; ?>>Taipei (CST)</option>
                    <option value="Asia/Manila" <?php echo ($settings['timezone'] ?? '') == 'Asia/Manila' ? 'selected' : ''; ?>>Manila (PHT)</option>
                    <option value="Asia/Jakarta" <?php echo ($settings['timezone'] ?? '') == 'Asia/Jakarta' ? 'selected' : ''; ?>>Jakarta (WIB)</option>
                    <option value="Asia/Colombo" <?php echo ($settings['timezone'] ?? '') == 'Asia/Colombo' ? 'selected' : ''; ?>>Colombo (IST)</option>
                </optgroup>
                <optgroup label="Australia & Pacific">
                    <option value="Australia/Perth" <?php echo ($settings['timezone'] ?? '') == 'Australia/Perth' ? 'selected' : ''; ?>>Perth (AWST)</option>
                    <option value="Australia/Adelaide" <?php echo ($settings['timezone'] ?? '') == 'Australia/Adelaide' ? 'selected' : ''; ?>>Adelaide (ACST)</option>
                    <option value="Australia/Sydney" <?php echo ($settings['timezone'] ?? '') == 'Australia/Sydney' ? 'selected' : ''; ?>>Sydney (AEST)</option>
                    <option value="Australia/Brisbane" <?php echo ($settings['timezone'] ?? '') == 'Australia/Brisbane' ? 'selected' : ''; ?>>Brisbane (AEST)</option>
                    <option value="Australia/Melbourne" <?php echo ($settings['timezone'] ?? '') == 'Australia/Melbourne' ? 'selected' : ''; ?>>Melbourne (AEST)</option>
                    <option value="Pacific/Auckland" <?php echo ($settings['timezone'] ?? '') == 'Pacific/Auckland' ? 'selected' : ''; ?>>Auckland (NZST)</option>
                    <option value="Pacific/Fiji" <?php echo ($settings['timezone'] ?? '') == 'Pacific/Fiji' ? 'selected' : ''; ?>>Fiji (FJT)</option>
                </optgroup>
                <optgroup label="South America">
                    <option value="America/Sao_Paulo" <?php echo ($settings['timezone'] ?? '') == 'America/Sao_Paulo' ? 'selected' : ''; ?>>Sao Paulo (BRT)</option>
                    <option value="America/Buenos_Aires" <?php echo ($settings['timezone'] ?? '') == 'America/Buenos_Aires' ? 'selected' : ''; ?>>Buenos Aires (ART)</option>
                    <option value="America/Bogota" <?php echo ($settings['timezone'] ?? '') == 'America/Bogota' ? 'selected' : ''; ?>>Bogota (COT)</option>
                    <option value="America/Santiago" <?php echo ($settings['timezone'] ?? '') == 'America/Santiago' ? 'selected' : ''; ?>>Santiago (CLT)</option>
                </optgroup>
                <optgroup label="Africa">
                    <option value="Africa/Cairo" <?php echo ($settings['timezone'] ?? '') == 'Africa/Cairo' ? 'selected' : ''; ?>>Cairo (EET)</option>
                    <option value="Africa/Johannesburg" <?php echo ($settings['timezone'] ?? '') == 'Africa/Johannesburg' ? 'selected' : ''; ?>>Johannesburg (SAST)</option>
                    <option value="Africa/Lagos" <?php echo ($settings['timezone'] ?? '') == 'Africa/Lagos' ? 'selected' : ''; ?>>Lagos (WAT)</option>
                    <option value="Africa/Nairobi" <?php echo ($settings['timezone'] ?? '') == 'Africa/Nairobi' ? 'selected' : ''; ?>>Nairobi (EAT)</option>
                </optgroup>
            </select>
        </div>
        <div class="mb-3"><label class="form-label">Date Format</label><select name="setting_date_format" class="form-select"><option value="M d, Y">Jan 15, 2024</option><option value="Y-m-d">2024-01-15</option><option value="d/m/Y">15/01/2024</option></select></div>
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>