<?php
if (!class_exists('PHPConfigManager')) {
    class PHPConfigManager extends HCPP_Hooks
    {
        public function __construct()
        {
            global $hcpp;
            $hcpp->add_custom_page('phpconfig', __DIR__ . '/pages/phpconfig.php');
            $hcpp->add_action('hcpp_list_web_xpath', [$this, 'inject_ui_button']);
            $hcpp->add_action('hcpp_invoke_plugin', [$this, 'handle_invocations']);
        }

        public function inject_ui_button($xpath)
        {
            $addWebButton = $xpath->query("//a[@href='/add/web/']")->item(0);
            if ($addWebButton) {
                $newButton = $xpath->document->createElement('a');
                $newButton->setAttribute('href', '?p=phpconfig');
                $newButton->setAttribute('class', 'button button-secondary');
                $newButton->setAttribute('title', 'PHP Settings');
                $icon = $xpath->document->createElement('i');
                $icon->setAttribute('class', 'fas fa-sliders-h icon-blue');
                $text = $xpath->document->createTextNode(' PHP Settings');
                $newButton->appendChild($icon);
                $newButton->appendChild($text);
                $addWebButton->parentNode->insertBefore($newButton, $addWebButton->nextSibling);
            }
            return $xpath;
        }

        public function handle_invocations($args)
        {
            if ($args[0] === 'phpconfig_get_settings') {
                $user = $args[1];
                $domain = $args[2];
                $ini_path = "/home/{$user}/web/{$domain}/public_html/.user.ini";
                
                if (file_exists($ini_path)) {
                    $settings = parse_ini_file($ini_path);
                    echo json_encode($settings);
                    return $args;
                }

                $domain_details_json = shell_exec("/usr/local/hestia/bin/v-list-web-domain " . escapeshellarg($user) . " " . escapeshellarg($domain) . " json");
                $domain_details = json_decode($domain_details_json, true);
                
                if (isset($domain_details[$domain]['BACKEND'])) {
                    $template = $domain_details[$domain]['BACKEND'];
                    // Use a more robust regex to handle future multi-digit PHP versions (e.g., PHP-10_1)
                    if (preg_match('/PHP-([0-9]+)_([0-9]+)/', $template, $matches)) {
                        $php_version = $matches[1] . '.' . $matches[2];
                        $server_ini_path = "/etc/php/{$php_version}/fpm/php.ini";

                        if (file_exists($server_ini_path)) {
                            $server_settings = parse_ini_file($server_ini_path);
                            $default_settings = [
                                'memory_limit' => $server_settings['memory_limit'] ?? '128M',
                                'max_execution_time' => $server_settings['max_execution_time'] ?? '30',
                                'upload_max_filesize' => $server_settings['upload_max_filesize'] ?? '32M',
                                'post_max_size' => $server_settings['post_max_size'] ?? '32M',
                                'display_errors' => $server_settings['display_errors'] ?? 'Off',
                                'opcache.enable' => $server_settings['opcache.enable'] ?? '1',
                            ];
                            echo json_encode($default_settings);
                            return $args;
                        }
                    }
                }
                
                echo json_encode([]);
            }

            if ($args[0] === 'phpconfig_save_settings') {
                $user = $args[1];
                $domain = $args[2];
                $settings_json = $args[3];
                $settings = json_decode($settings_json, true);

                $ini_path = "/home/{$user}/web/{$domain}/public_html/.user.ini";
                $ini_content = "; Settings managed by HestiaCP PHPConfig Plugin\n";
                foreach ($settings as $key => $value) {
                    $sane_key = preg_replace('/[^a-zA-Z0-9._-]/', '', $key);
                    if (empty($sane_key)) continue;
                    $sane_value = str_replace('"', '\"', $value);
                    $ini_content .= "{$sane_key} = \"{$sane_value}\"\n";
                }

                file_put_contents($ini_path, $ini_content);
                chown($ini_path, $user);
                chgrp($ini_path, $user);
            }
            return $args;
        }
    }
    global $hcpp;
    $hcpp->register_plugin(PHPConfigManager::class);
}