<?php

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());
});

test('sends verification notification', function () {
    Notification::fake();

    $member = ModelMember::factory()->unverified()->create();

    $this->actingAs($member)
        ->post(route('verification.send'))
        ->assertRedirect(route('home'));

    Notification::assertSentTo($member, VerifyEmail::class);
});

test('does not send verification notification if email is verified', function () {
    Notification::fake();

    $member = ModelMember::factory()->create();

    $this->actingAs($member)
        ->post(route('verification.send'))
        ->assertRedirect(route('dashboard', absolute: false));

    Notification::assertNothingSent();
});
