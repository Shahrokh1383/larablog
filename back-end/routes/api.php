<?php

// This file only loads module route files.
require base_path('src/Modules/Identity/Routes/api.php');
require base_path('src/Modules/Identity/Routes/admin.php');

require base_path('src/Modules/Content/Routes/admin.php');
require base_path('src/Modules/Content/Routes/api.php');

require base_path('src/Modules/Administration/Routes/admin.php');

// Profile Module Routes
require base_path('src/Modules/Profile/Routes/api.php');

// Comment Module Routes
require base_path('src/Modules/Engagement/Routes/api.php');
require base_path('src/Modules/Engagement/Routes/admin.php');

// Notification Module Routes
require base_path('src/Modules/Notification/Routes/api.php');

// ReaderExperience Module Routes
require base_path('src/Modules/ReaderExperience/Routes/api.php');