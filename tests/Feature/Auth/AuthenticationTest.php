<?php

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('members can authenticate using the login screen', function () {
    $member = ModelMember::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $member->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('members with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $member = ModelMember::factory()->withTwoFactor()->create();

    $response = $this->post(route('login'), [
        'email' => $member->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $member->id);
    $this->assertGuest();
});

test('members can not authenticate with invalid password', function () {
    $member = ModelMember::factory()->create();

    $this->post(route('login.store'), [
        'email' => $member->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('members can logout', function () {
    $member = ModelMember::factory()->create();

    $response = $this->actingAs($member)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});

test('members are rate limited', function () {
    $member = ModelMember::factory()->create();

    RateLimiter::increment(md5('login'.implode('|', [$member->email, '127.0.0.1'])), amount: 5);

    $response = $this->post(route('login.store'), [
        'email' => $member->email,
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});
