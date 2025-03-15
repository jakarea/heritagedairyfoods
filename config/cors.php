<?php

  return [
      'paths' => ['api/*', 'sanctum/csrf-cookie', '/@vite/client', '/resources/js/*'],
      'allowed_methods' => ['*'],
      'allowed_origins' => ['https://heritagedairyfoods.com'], // Allow only production domain
      'allowed_origins_patterns' => [],
      'allowed_headers' => ['*'],
      'exposed_headers' => [],
      'max_age' => 0,
      'supports_credentials' => false,
  ];
