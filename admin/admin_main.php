<?php
// MENU	
include_once 'menu/admin_menu_setup.php';

// Add Skin Page
include_once 'addSkin/admin_add_skin.php';

// Skin Statistics
include_once 'skinStats/admin_skin_stats.php';
// Add Statistics
include_once 'skinStats/admin_statistics.php';

// Export Skins
include_once 'exportSkin/admin_export_skin.php';

// Validation and License Key
// Note: removing this line will actually break the plugin.
include_once 'validation/admin_validation.php';

//include_once 'admin_data.php';
include_once 'admin_functions.php';
include_once 'admin_trash.php';
include_once 'admin_custom.php';
include_once 'admin_settings.php';

?>