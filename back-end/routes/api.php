<?php

// This file only loads module route files.
require base_path('src/Modules/Identity/Routes/api.php');
require base_path('src/Modules/Identity/Routes/admin.php');

require base_path('src/Modules/Taxonomy/Routes/admin.php');
require base_path('src/Modules/Taxonomy/Routes/api.php');

// AdminStats Module Routes
require base_path('src/Modules/AdminStats/Routes/admin.php');

require base_path('src/Modules/Home/Routes/api.php');

// Profile Module Routes
require base_path('src/Modules/Profile/Routes/api.php');

// Comment Module Routes
require base_path('src/Modules/Engagement/Routes/api.php');
require base_path('src/Modules/Engagement/Routes/admin.php');

// Notification Module Routes
require base_path('src/Modules/Notification/Routes/api.php');

// ReaderExperience Module Routes
require base_path('src/Modules/ReaderExperience/Routes/api.php');

// Marketing Module Routes
require base_path('src/Modules/Marketing/Routes/api.php');
require base_path('src/Modules/Marketing/Routes/admin.php');

// Search Module Routes
require base_path('src/Modules/Search/Routes/api.php');

// About Module Routes
require base_path('src/Modules/About/Routes/api.php');
require base_path('src/Modules/About/Routes/admin.php');