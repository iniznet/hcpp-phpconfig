<?php
$effective_user = (isset($_SESSION['look']) && !empty($_SESSION['look']))
    ? $_SESSION['look']
    : $_SESSION['user'];

if (isset($_POST['action']) && $_POST['action'] === 'save') {
    $domain = $_POST['domain'];
    $settings = [
        'memory_limit' => $_POST['memory_limit'],
        'max_execution_time' => $_POST['max_execution_time'],
        'upload_max_filesize' => $_POST['upload_max_filesize'],
        'post_max_size' => $_POST['post_max_size'],
        'display_errors' => $_POST['display_errors'],
        'opcache.enable' => $_POST['opcache_enable'],
    ];
    $settings_json = json_encode($settings);
    $hcpp->run("v-invoke-plugin phpconfig_save_settings " . escapeshellarg($effective_user) . " " . escapeshellarg($domain) . " " . escapeshellarg($settings_json));
    header("Location: ?p=phpconfig&domain=" . urlencode($domain) . "&saved=true");
    exit();
}

$domains = $hcpp->run("v-list-web-domains {$effective_user} json");
if (!is_array($domains)) $domains = [];
$domain_list = array_keys($domains);

$selected_domain = '';
if (!empty($domain_list)) {
    $selected_domain = (isset($_GET['domain']) && in_array($_GET['domain'], $domain_list))
        ? $_GET['domain']
        : $domain_list[0];
}

$current_settings = [];
if (!empty($selected_domain)) {
    $settings_json = $hcpp->run("v-invoke-plugin phpconfig_get_settings " . escapeshellarg($effective_user) . " " . escapeshellarg($selected_domain));
    $current_settings = json_decode($settings_json, true);
    if (!is_array($current_settings)) $current_settings = [];
}

$get_val = fn($key) => htmlspecialchars($current_settings[$key] ?? '');
?>

<!-- Toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back" href="/list/web/">
				<i class="fas fa-arrow-left icon-blue"></i>Back to Web
			</a>
		</div>
	</div>
</div>

<!-- Main Content -->
<div class="container">
    <h1 class="u-mb20">PHP Settings Manager</h1>

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-info u-mb20">Settings have been saved successfully for <?= htmlspecialchars($selected_domain) ?>.</div>
    <?php endif; ?>

    <?php if (empty($domain_list)): ?>
        <div class="alert alert-warning">You do not have any web domains to configure.</div>
    <?php else: ?>
        <!-- Domain Selection Form -->
        <form method="get" class="u-mb20">
            <input type="hidden" name="p" value="phpconfig">
            <div class="form-group">
                <label for="domain-select" class="form-label">Select a Domain to Configure</label>
                <div style="display: flex;">
                    <select name="domain" id="domain-select" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($domain_list as $domain): ?>
                            <option value="<?= $domain ?>" <?= ($domain === $selected_domain) ? 'selected' : '' ?>><?= $domain ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <hr>

        <!-- PHP Settings Form -->
        <form method="post">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="domain" value="<?= $selected_domain ?>">
            
            <div class="form-group u-mb10">
                <label for="memory_limit" class="form-label">memory_limit</label>
                <input type="text" class="form-control" name="memory_limit" id="memory_limit" value="<?= $get_val('memory_limit') ?>">
            </div>
            <div class="form-group u-mb10">
                <label for="max_execution_time" class="form-label">max_execution_time</label>
                <input type="text" class="form-control" name="max_execution_time" id="max_execution_time" value="<?= $get_val('max_execution_time') ?>">
            </div>
            <div class="form-group u-mb10">
                <label for="upload_max_filesize" class="form-label">upload_max_filesize</label>
                <input type="text" class="form-control" name="upload_max_filesize" id="upload_max_filesize" value="<?= $get_val('upload_max_filesize') ?>">
            </div>
            <div class="form-group u-mb10">
                <label for="post_max_size" class="form-label">post_max_size</label>
                <input type="text" class="form-control" name="post_max_size" id="post_max_size" value="<?= $get_val('post_max_size') ?>">
            </div>
            <div class="form-group u-mb10">
                <label for="display_errors" class="form-label">display_errors</label>
                <select name="display_errors" id="display_errors" class="form-select">
                    <option value="On" <?= ($get_val('display_errors') === 'On') ? 'selected' : '' ?>>On</option>
                    <option value="Off" <?= ($get_val('display_errors') === 'Off') ? 'selected' : '' ?>>Off</option>
                </select>
            </div>
            <div class="form-group u-mb20">
                <label for="opcache_enable" class="form-label">opcache.enable</label>
                <select name="opcache_enable" id="opcache_enable" class="form-select">
                    <option value="1" <?= ($get_val('opcache.enable') == '1') ? 'selected' : '' ?>>On</_option>
                    <option value="0" <?= ($get_val('opcache.enable') == '0') ? 'selected' : '' ?>>Off</option>
                </select>
            </div>

            <button type="submit" class="button">Save Settings</button>
        </form>
    <?php endif; ?>
</div>