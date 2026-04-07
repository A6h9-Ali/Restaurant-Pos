<?php
/**
 * includes/license.php
 * Checks whether the app is in trial, active (paid), or expired.
 * Called from config.php on every page load.
 *
 * License states stored in the `settings` table:
 *   license_status  → 'trial' | 'active' | 'expired'
 *   install_date    → '2025-01-01 00:00:00'
 *   license_key     → Stripe Payment Intent ID (set on successful payment)
 */

define('TRIAL_DAYS', 30);

function getLicenseStatus(PDO $pdo): array {
    $stmt = $pdo->query("SELECT key, value FROM settings WHERE key IN ('license_status','install_date','license_key')");
    $rows = [];
    while ($r = $stmt->fetch()) $rows[$r['key']] = $r['value'];

    $status      = $rows['license_status'] ?? 'trial';
    $installDate = $rows['install_date']   ?? date('Y-m-d H:i:s');
    $licenseKey  = $rows['license_key']    ?? '';

    // Already paid — always active
    if ($status === 'active') {
        return ['status' => 'active', 'days_left' => null, 'license_key' => $licenseKey];
    }

    // Calculate trial days remaining
    $installed  = new DateTime($installDate);
    $now        = new DateTime();
    $daysUsed   = (int)$installed->diff($now)->days;
    $daysLeft   = max(0, TRIAL_DAYS - $daysUsed);

    if ($daysLeft <= 0) {
        // Mark expired in DB (only once)
        if ($status !== 'expired') {
            $pdo->prepare("INSERT OR REPLACE INTO settings (key,value) VALUES ('license_status','expired')")->execute();
        }
        return ['status' => 'expired', 'days_left' => 0, 'license_key' => ''];
    }

    return ['status' => 'trial', 'days_left' => $daysLeft, 'license_key' => ''];
}

/**
 * Call this from every protected page.
 * Redirects to the expired wall if the license has lapsed.
 */
function requireLicense(PDO $pdo): array {
    $license = getLicenseStatus($pdo);

    if ($license['status'] === 'expired') {
        header('Location: ' . BASE_URL . '/license_expired.php');
        exit;
    }

    return $license;
}
