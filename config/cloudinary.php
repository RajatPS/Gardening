<?php

return [
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    'cloud_url' => env('CLOUDINARY_URL', env('cloudinary_URL', 'cloudinary://' . env('CLOUDINARY_API_KEY', env('cloudinary_API_KEY')) . ':' . env('CLOUDINARY_API_SECRET', env('cloudinary_API_SECRET')) . '@' . env('CLOUDINARY_CLOUD_NAME', env('CLOUDINARY_NAME', env('cloudinary_NAME'))))),

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),

    'upload_route' => env('CLOUDINARY_UPLOAD_ROUTE'),

    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),
];
