<?php

namespace App\Modules\Member\Application\Queries;

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Member\Infrastructure\Eloquent\ModelNetwork;
use Illuminate\Support\Collection;

class GetGenerationTreeQuery
{
    public function __construct(
        //
    ) {}

    public function execute(
        ModelMember $targetMember,
        ModelNetwork $targetNode,
        bool $showInfoCard,
    ): array {
        $tree = $this->getSponsorGenTree($targetNode);

        return [
            'targetMember' => [
                'name' => $targetMember->name,
                'username' => $targetMember->username,
            ],
            'targetNode' => [
                'total_omzet' => $targetNode->total_omzet,
                'rank_id' => $targetNode->rank_id?->value,
            ],
            'stats' => $tree['stats'],
            'treeData' => $tree['treeData'],
            'showInfoCard' => $showInfoCard,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getSponsorGenTree(ModelNetwork $targetNode): array
    {
        $treePrefix = $targetNode->tree_sponsor.'-';
        $allGenerationCounts = ModelNetwork::query()
            ->where('tree_sponsor', 'like', $treePrefix.'%')
            ->where('gen', '>', $targetNode->gen)
            ->selectRaw('gen, count(*) as aggregate')
            ->groupBy('gen')
            ->pluck('aggregate', 'gen');

        $stats = [
            'total_generations' => $allGenerationCounts->count(),
            'total_groups' => ModelNetwork::query()
                ->where('tree_sponsor', 'like', $treePrefix.'%')
                ->where('gen', '>', $targetNode->gen)
                ->whereNotNull('group')
                ->distinct()
                ->count('group'),
        ];

        $descendants = ModelNetwork::with('member')
            ->where('tree_sponsor', 'like', $targetNode->tree_sponsor.'-%')
            ->where('gen', '>', $targetNode->gen)
            ->get();

        $groupedBySponsor = $descendants->groupBy('sponsor_id');

        return [
            'stats' => $stats,
            'treeData' => $this->getTreeChildren($targetNode->id, $groupedBySponsor, $targetNode->gen),
        ];
    }

    /**
     * @param  Collection<int, Collection<int, ModelNetwork>>  $groupedBySponsor
     * @return array<int, array<string, mixed>>
     */
    private function getTreeChildren(int $parentId, Collection $groupedBySponsor, int $rootGen): array
    {
        /** @var Collection<int, ModelNetwork> $children */
        $children = $groupedBySponsor->get($parentId, collect());
        $tree = [];

        foreach ($children as $child) {
            $tree[] = $this->getTreeChild(
                $child,
                $rootGen,
                $this->getTreeChildren($child->id, $groupedBySponsor, $rootGen),
            );
        }

        return $tree;
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     * @return array<string, mixed>
     */
    private function getTreeChild(ModelNetwork $node, int $rootGen, array $children = []): array
    {
        return [
            'id' => $node->id,
            'name' => $node->member?->name ?? '',
            'username' => $node->member?->username ?? '',
            'gen' => $node->gen - $rootGen,
            'rank_id' => $node->rank_id?->value ?? $node->rank_id,
            'children' => $children,
        ];
    }
}
