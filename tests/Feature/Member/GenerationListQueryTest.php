<?php

use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Member\Infrastructure\Eloquent\ModelNetwork;
use App\Modules\Member\Infrastructure\Repositories\MemberRepository;
use Inertia\Testing\AssertableInertia as Assert;

function createGenerationFilterFixture(): array
{
    $target = ModelMember::factory()->create();
    $targetNode = new ModelNetwork([
        'member_id' => $target->id,
        'referral_code' => 'target-referral',
        'package' => 'test',
        'gen' => 0,
        'group' => 0,
        'tree' => (string) $target->id,
        'tree_sponsor' => (string) $target->id,
    ]);
    $targetNode->id = $target->id;
    $targetNode->save();

    foreach ([
        ['username' => 'generation-one', 'gen' => 1, 'group' => 1],
        ['username' => 'generation-two', 'gen' => 2, 'group' => 2],
    ] as $index => $attributes) {
        $member = ModelMember::factory()->create([
            'username' => $attributes['username'],
        ]);

        ModelNetwork::create([
            'member_id' => $member->id,
            'referral_code' => 'downline-referral-'.$index,
            'package' => 'test',
            'sponsor_id' => $targetNode->id,
            'parent_id' => $targetNode->id,
            'gen' => $attributes['gen'],
            'group' => $attributes['group'],
            'tree' => $targetNode->tree.'-'.$member->id,
            'tree_sponsor' => $targetNode->tree_sponsor.'-'.$member->id,
        ]);
    }

    return [$target, $targetNode];
}

it('filters generation rows by the generation range', function () {
    [$target] = createGenerationFilterFixture();
    $this->app->bind(MemberGetInterface::class, MemberRepository::class);

    $this->actingAs($target)
        ->get(route('member.generation', ['gen_min' => 2, 'gen_max' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('member/generation')
            ->where('generation.total', 1)
            ->where('generation.data.0.member_username', 'generation-two')
        );
});

it('filters generation rows by the group range', function () {
    [$target] = createGenerationFilterFixture();
    $this->app->bind(MemberGetInterface::class, MemberRepository::class);

    $this->actingAs($target)
        ->get(route('member.generation', ['group_min' => 2, 'group_max' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('member/generation')
            ->where('generation.total', 1)
            ->where('generation.data.0.member_username', 'generation-two')
        );
});
