<?php

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use Inertia\Testing\AssertableInertia as Assert;

test('confirm password screen can be rendered', function () {
    $member = ModelMember::factory()->create();

    $response = $this->actingAs($member)->get(route('password.confirm'));

    $response->assertOk();

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('auth/confirm-password'),
    );
});

test('password confirmation requires authentication', function () {
    $response = $this->get(route('password.confirm'));

    $response->assertRedirect(route('login'));
});
