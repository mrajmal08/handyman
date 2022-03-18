<?php

if(env('IOS_USER_ENV')=='development'){
    $crt_user_path=app_path().'/apns/user/xuber_user_dev.pem';
    $crt_provider_path=app_path().'/apns/provider/xuber_provider_dev.pem';
}
else{
    $crt_user_path=app_path().'/apns/user/xuber_user_live.pem';
    $crt_provider_path=app_path().'/apns/provider/xuber_provider_live.pem';
}

return array(

    'IOSUser'     => array(
        'environment' => env('IOS_USER_ENV', 'development'),
        'certificate' => $crt_user_path,
        'passPhrase'  => env('IOS_USER_PUSH_PASS', 'appoets123$'),
        'service'     => 'apns'
    ),
    'IOSProvider' => array(
        'environment' => env('IOS_PROVIDER_ENV', 'development'),
        'certificate' => $crt_provider_path,
        'passPhrase'  => env('IOS_PROVIDER_PUSH_PASS', 'appoets123$'),
        'service'     => 'apns'
    ),
    'AndroidUser' => array(
        'environment' =>'production',
        'apiKey'      => env('ANDROID_PROVIDER_PUSH_KEY','yourAPIKey'),
        'service'     =>'gcm'
    ),
    'AndroidProvider' => array(
        'environment' =>'production',
        'apiKey'      => env('ANDROID_USER_PUSH_KEY','yourAPIKey'),
        'service'     =>'gcm'
    )

);