<?php

// This file only loads module route files.
require base_path('src/Modules/Identity/Routes/api.php');
require base_path('src/Modules/Identity/Routes/admin.php');

require base_path('src/Modules/Content/Routes/admin.php');

// Future modules will be loaded similarly.