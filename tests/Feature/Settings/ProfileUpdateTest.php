<?php

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;

test('profile page is displayed', function () {
    $member = ModelMember::factory()->create();

    $response = $this
        ->actingAs($member)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $member = ModelMember::factory()->create();

    $response = $this
        ->actingAs($member)
        ->patch(route('profile.update'), [
            'name' => 'Test member',
            'username' => 'Test member',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $member->refresh();

    expect($member->name)->toBe('Test member');
    expect($member->email)->toBe('test@example.com');
    expect($member->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $member = ModelMember::factory()->create();

    $response = $this
        ->actingAs($member)
        ->patch(route('profile.update'), [
            'name' => 'Test member',
            'username' => 'Test member',
            'email' => $member->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($member->refresh()->email_verified_at)->not->toBeNull();
});

test('member can delete their account', function () {
    $member = ModelMember::factory()->create();

    $response = $this
        ->actingAs($member)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($member->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $member = ModelMember::factory()->create();

    $response = $this
        ->actingAs($member)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($member->fresh())->not->toBeNull();
});
