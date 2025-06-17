# HestiaCP PHP Settings Manager (hcpp-phpconfig)

This is a plugin for the [Hestia Control Panel](https://hestiacp.com) that provides a simple, powerful graphical interface for managing domain-specific PHP settings. It is built upon the `hestiacp-pluginable` framework.

Manually editing `.user.ini` files via SSH or the File Manager is tedious and error-prone. This plugin solves that problem by allowing users to view and modify common PHP directives for any of their web domains directly from the Hestia UI.

---

## Key Features

-   **User-Friendly Interface:** A clean, integrated page with a dropdown to select a domain and a form to edit its PHP settings.
-   **Intelligent Default Loading:** If a domain doesn't have a custom `.user.ini` file, the plugin intelligently detects the domain's active PHP version and pre-fills the form with the **actual server-wide defaults** from the correct `php.ini` file.
-   **Domain-Specific Configuration:** Each domain's settings are managed independently in its own `.user.ini` file, located in the `public_html` directory.
-   **Seamless Integration:** Adds a convenient "PHP Settings" button directly to the "Web" tab for easy access.
-   **Safe & Secure:** The plugin ensures that when settings are saved, the resulting `.user.ini` file is created with the correct user ownership, allowing the PHP-FPM process to read it without permission issues.

## How It Works

1.  A user navigates to the "PHP Settings" page and selects a domain from the dropdown.
2.  The plugin's backend logic is triggered. It first checks for an existing `.user.ini` file in the domain's `public_html` directory.
3.  **If `.user.ini` exists**, its values are parsed and used to populate the form.
4.  **If `.user.ini` does not exist**, the plugin checks the domain's configuration to find its active PHP version (e.g., `PHP-8.3`). It then reads the main server configuration file (e.g., `/etc/php/8.3/fpm/php.ini`) to get the current server defaults and populates the form with those values.
5.  When the user saves the form, the plugin writes the settings to the domain's `.user.ini` file, creating it if necessary, and sets the correct `user:user` ownership.

## Requirements

-   Hestia Control Panel v1.9.X or greater.
-   **[hestiacp-pluginable](https://github.com/virtuosoft-dev/hestiacp-pluginable)** must be installed first.
-   Ubuntu or Debian Linux OS.
-   `root` or `sudo` access to the server.

## Installation

1.  SSH into your HestiaCP server.
2.  Navigate to the Hestia plugins directory:
    ```bash
    cd /usr/local/hestia/plugins
    ```
3.  Clone this repository:
    ```bash
    sudo git clone https://github.com/iniznet/hcpp-phpconfig.git phpconfig
    ```
4.  **Set Permissions:** Ensure the install/uninstall scripts are executable.
    ```bash
    sudo chmod +x phpconfig/install phpconfig/uninstall
    ```
5.  The plugin will be active immediately. Log in to HestiaCP to see the new button and page.

## Usage

1.  Log in to the Hestia Control Panel as any user.
2.  Navigate to the **WEB** tab.
3.  Click the new **"PHP Settings"** button.
4.  On the settings page, select the domain you wish to configure from the dropdown menu.
5.  The form will load with the current settings for that domain (either from its `.user.ini` or the server defaults).
6.  Modify the values as needed and click "Save Settings".

## Uninstallation

This plugin does not install any system-wide packages or create persistent services. Uninstallation is as simple as removing the plugin directory.

1.  SSH into your server.
2.  Remove the plugin directory:
    ```bash
    sudo rm -rf /usr/local/hestia/plugins/phpconfig
    ```
The plugin will be removed instantly.