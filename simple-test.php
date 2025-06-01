<?php

echo "Starting Environment Manager Test...\n";

// Define ABSPATH to prevent WordPress security check from exiting
define('ABSPATH', '/fake/wordpress/path/');

// Mock WordPress function
function site_url() {
    return 'http://localhost';
}

// Try to include the class
echo "Including Environment Manager class...\n";
require_once 'includes/class-environment-manager.php';

echo "Creating Environment Manager instance...\n";
$env = WooPreProduct_Environment_Manager::get_instance();

echo "Testing methods...\n";
echo "Environment: " . $env->get_environment() . "\n";
echo "Is Development: " . ($env->is_development() ? 'Yes' : 'No') . "\n";
echo "Script URL: " . $env->get_script_url() . "\n";

echo "Test completed!\n"; 