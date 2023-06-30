<?php

declare(strict_types=1);

use App\Mail\SendOtpCode;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('The mailable contents are correct with user', function (): void {
    $user = User::factory()->create();
    $otp = Otp::factory()->create(['email' => $user->email]);

    $mailable = new SendOtpCode(user: $user, otp: $otp);

    $mailable->to($user);

    $mailable->assertFrom(
        address: \config('mail.from.address'),
        name: \config('mail.from.name'),
    );

    $mailable->assertTo($user->email);
    $mailable->assertHasSubject('Your OTP code for account registration');
    $mailable->assertSeeInHtml($otp->otp);
    $mailable->assertSeeInHtml('This code is valid for 1 minute');
    $mailable->assertSeeInOrderInHtml(["Dear {$user->name}", $otp->otp]);
});

test('The mailable contents are correct even when using anonymous (unauthenticated) users', function (): void {
    $email = 'test@gmail.com';
    $otp = Otp::factory()->create(['email' => $email]);

    $mailable = new SendOtpCode(otp: $otp);

    $mailable->to($email);

    $mailable->assertFrom(
        address: \config('mail.from.address'),
        name: \config('mail.from.name'),
    );

    $mailable->assertTo($otp->email);
    $mailable->assertHasSubject('Your OTP code for account registration');
    $mailable->assertSeeInHtml($otp->otp);
    $mailable->assertSeeInHtml('This code is valid for 1 minute');
    $mailable->assertSeeInOrderInHtml(["Dear Customer", $otp->otp]);
});

it('sends an email with user', function (): void {
    Mail::fake();

    $user = User::factory()->create();
    $otp = Otp::factory()->create(['email' => $user->email]);

    Mail::to($user)->send(new SendOtpCode(user: $user, otp: $otp));

    Mail::assertSent(SendOtpCode::class, 1);

    Mail::assertSent(function (SendOtpCode $mail) use ($user, $otp) {
        return $mail->otp->otp === $otp->otp &&
            $mail->user->name === $user->name &&
            $mail->user->email === $user->email &&
            $mail->hasFrom(\config('mail.from.address'), \config('mail.from.name')) &&
            $mail->hasTo($user) &&
            $mail->hasSubject('Your OTP code for account registration');
    });
});

it('sends an email without user', function (): void {
    Mail::fake();

    $email = 'test@gmail.com';
    $otp = Otp::factory()->create(['email' => $email]);

    Mail::to($email)->send(new SendOtpCode(otp: $otp));

    Mail::assertSent(SendOtpCode::class, 1);

    Mail::assertSent(function (SendOtpCode $mail) use ($otp) {
        return $mail->otp->otp === $otp->otp &&
            null === $mail->user &&
            $mail->hasFrom(\config('mail.from.address'), \config('mail.from.name')) &&
            $mail->hasTo($otp->email) &&
            $mail->hasSubject('Your OTP code for account registration');
    });
});
