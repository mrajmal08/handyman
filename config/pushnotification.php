<?php

return [
  'gcm' => [
      'priority' => 'high',
      'dry_run' => false,
      'apiKey' => env('ANDROID_USER_PUSH_KEY', 'AAAA2a_CYvc:APA91bHesVLkb7JJ4kXcE0DDrv3ocqAE3xSYHkjWke0ch5UdtaIGSMOGEq4t7dME0AH11N0iAkpemI5BeHRxF4YVmbflVEKdptecnCbeZZ1oKRVxAXT6uxQn8bNxobb9VrQ95K6c2W7H'),
  ],
  'fcm' => [
        'priority' => 'high',
        'dry_run' => false,
        'apiKey' => env('ANDROID_PROVIDER_PUSH_KEY', 'AAAA2a_CYvc:APA91bHesVLkb7JJ4kXcE0DDrv3ocqAE3xSYHkjWke0ch5UdtaIGSMOGEq4t7dME0AH11N0iAkpemI5BeHRxF4YVmbflVEKdptecnCbeZZ1oKRVxAXT6uxQn8bNxobb9VrQ95K6c2W7H'),
  ],
  'apn' => [
      
  ]
];
