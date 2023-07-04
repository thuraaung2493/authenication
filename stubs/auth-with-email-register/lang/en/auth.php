<?php

declare(strict_types=1);

return [

    'registered' => 'Register successful.',

    'login_failed' => 'User credentials did not match!',

    'email_not_verified' => 'This user is already register but still need verify process.',

    'otp_resend' => 'A new OTP code is sent to the registered email.',

    'forgot_password' => 'Password reset successful. An OTP code has been sent to your registered email.',

    'permission_denied' => 'You do not have permission to access it.',

    'invalid_app_keys' => 'Your keys are mismatch.',

    'invalid_otp' => 'Your OTP code is invalid.',

    'otp_expired' => 'Your OTP code is expired.',

    'logout' => [

        'success' => 'Logout successful.',

        'fail' => 'Logout failed.',
    ],

    'exceptions' => [

        'titles' => [

            'not_found' => 'Not Found Exception!',

            'unauthenticated' => 'Unauthenticated!',

            'unauthorized' => 'Unauthorized!',

            'method_not_allowed' => 'Unsupported HTTP Method for Requested Route!',

            'outdated' => 'Outdated!',

            'validation' => 'Validation Error!',

            'email_not_verified' => 'Email is not verified!',
        ],
    ],
];
