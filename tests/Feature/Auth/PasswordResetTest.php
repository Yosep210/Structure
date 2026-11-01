<?php

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $member = ModelMember::factory()->create();

    $response = $this->post(route('password.username'), [
        'username' => $member->username,
        'email' => $member->email,
    ]);

    $response->assertSessionHasNoErrors();

    Notification::assertSentTo($member, ResetPasswordNotification::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $member = ModelMember::factory()->create();

    $this->post(route('password.username'), [
        'username' => $member->username,
        'email' => $member->email,
    ]);

    Notification::assertSentTo($member, ResetPasswordNotification::class, function ($notification) use ($member) {
        $response = $this->get(route('password.reset', [
            'token' => $notification->token,
            'username' => $member->username,
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('auth/reset-password')
            ->where('token', $notification->token)
            ->where('username', $member->username));

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $member = ModelMember::factory()->create();
    $otherMember = ModelMember::factory()->create(['email' => $member->email]);

    $this->post(route('password.username'), [
        'username' => $member->username,
        'email' => $member->email,
    ]);

    Notification::assertSentTo($member, ResetPasswordNotification::class, function ($notification) use ($member, $otherMember) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'username' => $member->username,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        expect(Hash::check('new-password-123', $member->fresh()->password))->toBeTrue();
        expect(Hash::check('password', $otherMember->fresh()->password))->toBeTrue();

        return true;
    });
});

test('password cannot be reset with invalid token', function () {
    $member = ModelMember::factory()->create();

    $response = $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'username' => $member->username,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrors('email');
});
