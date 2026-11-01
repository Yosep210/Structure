<?php

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());
});

test('email verification screen can be rendered', function () {
    $member = ModelMember::factory()->unverified()->create();

    $response = $this->actingAs($member)->get(route('verification.notice'));

    $response->assertOk();
});

test('unverified members are redirected to the email verification prompt', function () {
    $member = ModelMember::factory()->unverified()->create();

    $response = $this->actingAs($member)->get(route('appearance.edit'));

    $response->assertRedirect(route('verification.notice'));
});

test('email can be verified', function () {
    $member = ModelMember::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $member->id, 'hash' => sha1($member->email)],
    );

    $response = $this->actingAs($member)->get($verificationUrl);

    Event::assertDispatched(Verified::class);

    expect($member->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $member = ModelMember::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $member->id, 'hash' => sha1('wrong-email')],
    );

    $this->actingAs($member)->get($verificationUrl);

    Event::assertNotDispatched(Verified::class);
    expect($member->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('email is not verified with invalid member id', function () {
    $member = ModelMember::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => 123, 'hash' => sha1($member->email)],
    );

    $this->actingAs($member)->get($verificationUrl);

    Event::assertNotDispatched(Verified::class);
    expect($member->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verified member is redirected to dashboard from verification prompt', function () {
    $member = ModelMember::factory()->create();

    Event::fake();

    $response = $this->actingAs($member)->get(route('verification.notice'));

    Event::assertNotDispatched(Verified::class);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('already verified member visiting verification link is redirected without firing event again', function () {
    $member = ModelMember::factory()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $member->id, 'hash' => sha1($member->email)],
    );

    $this->actingAs($member)->get($verificationUrl)
        ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

    Event::assertNotDispatched(Verified::class);
    expect($member->fresh()->hasVerifiedEmail())->toBeTrue();
});
