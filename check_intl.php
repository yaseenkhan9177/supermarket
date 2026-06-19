<?php
echo "Loaded INI: " . php_ini_loaded_file() . "\n";
echo "Intl Extension Loaded: " . (extension_loaded('intl') ? 'YES' : 'NO') . "\n";
if (class_exists('NumberFormatter')) {
    echo "NumberFormatter Class Exists: YES\n";
} else {
    echo "NumberFormatter Class Exists: NO\n";
}
