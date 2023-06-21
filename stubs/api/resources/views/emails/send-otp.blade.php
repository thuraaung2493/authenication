<x-mail::message>
  Dear {{$user ? $user->name: 'Customer'}},

  Thank you for registering for our application. To complete your registration process, please use the following OTP
  code:

  ## [{{$otp->otp}}]

  This code is valid for 1 minute. Please enter it in the otp confirm form to verify your email address and activate
  your account.

  If you did not request this code, please ignore this email.

  Thank you for using our application.

  Best regards,<br>
  {{ config('app.name') }}
</x-mail::message>