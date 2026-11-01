<?php

use App\Enums\MemberRole;
use App\Modules\Member\Domain\Enums\StatusMember;
use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use App\Modules\Member\Domain\Interfaces\MemberPostInterface;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Member\Infrastructure\Repositories\MemberRepository;
use Spatie\Permission\Models\Role;

it('flashes a success toast when an admin changes a member status', function () {
    $adminRole = Role::create([
        'name' => MemberRole::ADMIN->value,
        'guard_name' => 'web',
    ]);
    $admin = ModelMember::factory()->create();
    $admin->assignRole($adminRole);
    $member = ModelMember::factory()->create();

    $this->app->bind(MemberGetInterface::class, MemberRepository::class);
    $this->app->bind(MemberPostInterface::class, MemberRepository::class);

    $response = $this
        ->actingAs($admin)
        ->from(route('member.list'))
        ->patch(route('member.status', $member->username), [
            'status' => StatusMember::INACTIVE->value,
        ]);

    $response
        ->assertRedirect(route('member.list'))
        ->assertInertiaFlash('toast', [
            'type' => 'success',
            'message' => "Member ({$member->username}) berhasil di Nonaktifkan.",
        ]);

    $this->assertDatabaseHas('members', [
        'id' => $member->id,
        'status' => StatusMember::INACTIVE->value,
    ]);
});

it('flashes an error toast when the requested member does not exist', function () {
    $adminRole = Role::create([
        'name' => MemberRole::ADMIN->value,
        'guard_name' => 'web',
    ]);
    $admin = ModelMember::factory()->create();
    $admin->assignRole($adminRole);

    $this->app->bind(MemberGetInterface::class, MemberRepository::class);

    $response = $this
        ->actingAs($admin)
        ->from(route('member.list'))
        ->get(route('member.generation', ['username' => 'missing-member']));

    $response
        ->assertRedirect(route('member.list'))
        ->assertInertiaFlash('toast', [
            'type' => 'error',
            'message' => 'Member (missing-member) tidak ditemukan.',
        ]);
});
