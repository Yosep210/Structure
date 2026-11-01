<?php

namespace App\Modules\Member\Application\Queries;

use App\Modules\Member\Infrastructure\Eloquent\ModelMember;
use App\Modules\Member\Infrastructure\Eloquent\ModelNetwork;
use Illuminate\Database\Eloquent\Collection;

class GetBinaryTreeQuery
{
    public function __construct(
        //
    ) {}

    public function execute(ModelNetwork $rootNode): array
    {
        $rootNodeId = $rootNode->id;

        $descendants = ModelNetwork::with('member')
            ->where('tree', 'like', $rootNode->tree.'-%')
            ->where('level', '<=', $rootNode->level + 3)
            ->get();

        // Level 1
        $leftNode = $this->findChildByPosition($descendants, $rootNodeId, 'left');
        $rightNode = $this->findChildByPosition($descendants, $rootNodeId, 'right');

        // Level 2
        $leftLeftNode = $leftNode ? $this->findChildByPosition($descendants, $leftNode->id, 'left') : null;
        $leftRightNode = $leftNode ? $this->findChildByPosition($descendants, $leftNode->id, 'right') : null;
        $rightLeftNode = $rightNode ? $this->findChildByPosition($descendants, $rightNode->id, 'left') : null;
        $rightRightNode = $rightNode ? $this->findChildByPosition($descendants, $rightNode->id, 'right') : null;

        // Level 3
        $leftLeftLeftNode = $leftLeftNode ? $this->findChildByPosition($descendants, $leftLeftNode->id, 'left') : null;
        $leftLeftRightNode = $leftLeftNode ? $this->findChildByPosition($descendants, $leftLeftNode->id, 'right') : null;
        $leftRightLeftNode = $leftRightNode ? $this->findChildByPosition($descendants, $leftRightNode->id, 'left') : null;
        $leftRightRightNode = $leftRightNode ? $this->findChildByPosition($descendants, $leftRightNode->id, 'right') : null;

        $rightLeftLeftNode = $rightLeftNode ? $this->findChildByPosition($descendants, $rightLeftNode->id, 'left') : null;
        $rightLeftRightNode = $rightLeftNode ? $this->findChildByPosition($descendants, $rightLeftNode->id, 'right') : null;
        $rightRightLeftNode = $rightRightNode ? $this->findChildByPosition($descendants, $rightRightNode->id, 'left') : null;
        $rightRightRightNode = $rightRightNode ? $this->findChildByPosition($descendants, $rightRightNode->id, 'right') : null;

        $nodes = [
            'root' => $this->formatNode($rootNode),
            'left' => $this->formatNode($leftNode),
            'right' => $this->formatNode($rightNode),
            'leftLeft' => $this->formatNode($leftLeftNode),
            'leftRight' => $this->formatNode($leftRightNode),
            'rightLeft' => $this->formatNode($rightLeftNode),
            'rightRight' => $this->formatNode($rightRightNode),
            'leftLeftLeft' => $this->formatNode($leftLeftLeftNode),
            'leftLeftRight' => $this->formatNode($leftLeftRightNode),
            'leftRightLeft' => $this->formatNode($leftRightLeftNode),
            'leftRightRight' => $this->formatNode($leftRightRightNode),
            'rightLeftLeft' => $this->formatNode($rightLeftLeftNode),
            'rightLeftRight' => $this->formatNode($rightLeftRightNode),
            'rightRightLeft' => $this->formatNode($rightRightLeftNode),
            'rightRightRight' => $this->formatNode($rightRightRightNode),
        ];

        $allDownlines = ModelNetwork::query()
            ->where('tree', 'like', $rootNode->tree.'-%')
            ->get();

        $rawNodes = [
            'root' => $rootNode,
            'left' => $leftNode,
            'right' => $rightNode,
            'leftLeft' => $leftLeftNode,
            'leftRight' => $leftRightNode,
            'rightLeft' => $rightLeftNode,
            'rightRight' => $rightRightNode,
            'leftLeftLeft' => $leftLeftLeftNode,
            'leftLeftRight' => $leftLeftRightNode,
            'leftRightLeft' => $leftRightLeftNode,
            'leftRightRight' => $leftRightRightNode,
            'rightLeftLeft' => $rightLeftLeftNode,
            'rightLeftRight' => $rightLeftRightNode,
            'rightRightLeft' => $rightRightLeftNode,
            'rightRightRight' => $rightRightRightNode,
        ];

        $nodeStats = [];
        foreach ($rawNodes as $key => $node) {
            if ($node !== null) {
                $leftChild = $this->findChildByPosition($descendants, $node->id, 'left');
                $rightChild = $this->findChildByPosition($descendants, $node->id, 'right');

                $leftDownlines = $leftChild
                    ? $allDownlines->filter(fn (ModelNetwork $n) => $n->id === $leftChild->id || str_starts_with((string) $n->tree, (string) $leftChild->tree.'-'))
                    : collect();

                $rightDownlines = $rightChild
                    ? $allDownlines->filter(fn (ModelNetwork $n) => $n->id === $rightChild->id || str_starts_with((string) $n->tree, (string) $rightChild->tree.'-'))
                    : collect();

                $nodeStats[$key] = [
                    'left_count' => $leftDownlines->count(),
                    'right_count' => $rightDownlines->count(),
                    'left_pp' => (float) $leftDownlines->sum('total_omzet'),
                    'right_pp' => (float) $rightDownlines->sum('total_omzet'),
                    'left_sm' => $leftDownlines->where('sponsor_id', $node->id)->count(),
                    'right_sm' => $rightDownlines->where('sponsor_id', $node->id)->count(),
                    'is_admin' => $node->member?->hasRole('admin') ?? false,
                ];
            }
        }

        return [
            'rootNodeId' => $rootNodeId,
            'nodes' => $nodes,
            'nodeStats' => $nodeStats,
            'deepestLeftDownlines' => $this->resolveDeepestDownlines($rootNode, 'left'),
            'deepestRightDownlines' => $this->resolveDeepestDownlines($rootNode, 'right'),
        ];
    }

    /**
     * @param  Collection<int, ModelNetwork>  $collection
     */
    private function findChildByPosition(Collection $collection, int $parentId, string $position): ?ModelNetwork
    {
        return $collection
            ->where('parent_id', $parentId)
            ->first(function (ModelNetwork $item) use ($position) {
                $itemPos = $item->position?->value ?? $item->position;

                return $itemPos === $position;
            });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatNode(?ModelNetwork $node): ?array
    {
        if ($node === null) {
            return null;
        }

        return [
            'id' => $node->id,
            'member_id' => $node->member_id,
            'username' => $node->member?->username ?? '',
            'position' => $node->position?->value ?? $node->position,
            'rank_id' => $node->rank_id?->value ?? $node->rank_id,
            'total_omzet' => $node->total_omzet,
            'package' => $node->package,
            'level' => $node->level,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveDeepestDownlines(ModelNetwork $rootNode, string $position): array
    {
        $child = ModelNetwork::query()
            ->where('parent_id', $rootNode->id)
            ->where('position', $position)
            ->first();

        if (! $child) {
            return [];
        }

        $deepestLevel = ModelNetwork::query()
            ->where(function ($q) use ($child) {
                $q->where('id', $child->id)
                    ->orWhere('tree', 'like', $child->tree.'-%');
            })
            ->whereHas('member', function ($q) {
                $q->whereDoesntHave('roles', function ($rq) {
                    $rq->where('name', 'admin');
                });
            })
            ->max('level');

        if (! $deepestLevel || $deepestLevel <= $rootNode->level + 1) {
            return [];
        }

        return ModelNetwork::with('member')
            ->where(function ($q) use ($child) {
                $q->where('id', $child->id)
                    ->orWhere('tree', 'like', $child->tree.'-%');
            })
            ->whereHas('member', function ($q) {
                $q->whereDoesntHave('roles', function ($rq) {
                    $rq->where('name', 'admin');
                });
            })
            ->where('level', $deepestLevel)
            ->orderBy('id', 'asc')
            ->limit(2)
            ->get()
            ->map(fn (ModelNetwork $n) => [
                'id' => $n->id,
                'username' => $n->member?->username ?? '',
            ])
            ->all();
    }

    // private function getByUsername(string $username, ?ModelMember $member = null): ?ModelMember
    // {
    //     $query = ModelMember::query()
    //         ->where('username', 'like', '%' . $username . '%');

    //     if ($member !== null) {
    //         $authNode = $this->getByHU($member->id);
    //         if ($authNode !== null) {
    //             $query->where(function ($q) use ($member, $authNode) {
    //                 $q->where('members.id', $member->id)
    //                     ->orWhereHas('network', function ($nq) use ($authNode) {
    //                         $nq->where('hu_index', 1)
    //                             ->where('tree', 'like', $authNode->tree . '-%');
    //                     });
    //             });
    //         }
    //     }

    //     return $query
    //         ->orderByRaw('CASE WHEN username = ? THEN 0 WHEN username LIKE ? THEN 1 ELSE 2 END', [$username, $username . '%'])
    //         ->first();
    // }

    // private function getByHU(int $memberId): ?ModelNetwork
    // {
    //     return ModelNetwork::with(['member', 'parent.member'])
    //         ->where('member_id', $memberId)
    //         ->where('hu_index', 1)
    //         ->first();
    // }
}
