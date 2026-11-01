<?php

namespace App\Modules\Product\Http\Controllers;

use App\Concerns\HasAuth;
use App\Concerns\Table\Paginates;
use App\Http\Controllers\Controller;
use App\Modules\Member\Domain\Exceptions\MemberException;
use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use App\Modules\Product\Application\Queries\GetHistoryRoQuery;
use App\Modules\Product\Application\Queries\GetHistorySendProductQuery;
use App\Modules\Product\Application\Queries\GetMemberProdutQuery;
use App\Modules\Product\Application\Queries\GetOrderToCompanyQuery;
use App\Modules\Product\Application\Queries\GetStockProductQuery;
use App\Modules\Product\Application\Queries\GetTotalProductQuery;
use App\Modules\Product\Application\Queries\GetUsageProductQuery;
use App\Modules\Product\Domain\Enums\OrderStatus;
use App\Modules\Product\Domain\Enums\OrderTab;
use App\Modules\Product\Domain\Enums\OrderType;
use App\Modules\Product\Domain\Enums\PaymentMethode;
use App\Modules\Product\Domain\Enums\PinStatus;
use App\Modules\Product\Domain\Enums\PinTransfer;
use App\Modules\Product\Domain\Enums\PinType;
use App\Modules\Product\Domain\Interfaces\ProductGetInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use HasAuth, Paginates;

    /**
     * Tampilan Form Kirim Produk (Admin & Member).
     */
    public function sendProduct(MemberGetInterface $getMember, ProductGetInterface $getProduct, ?string $username = null): Response
    {
        $auth = $this->getAuthMember();
        $targetMember = null;
        $memberSuggestions = [];
        $search = $username ?? $this->request->query('search') ?? $this->request->query('username');

        if (! empty($search)) {
            if ($auth['isAdmin']) {
                // 1. [MODE 1 - KHUSUS ADMIN] Bebas mencari member manapun
                $exactMember = $getMember->findByUsername($search);

                if ($exactMember !== null) {
                    $targetMember = [
                        'id' => $exactMember->id,
                        'username' => $exactMember->username,
                        'name' => $exactMember->name,
                    ];
                }

                $memberSuggestions = $getMember->searchMembers($search, 10);
            } else {
                // 2. [MODE 2 - KHUSUS MEMBER] Hanya boleh mencari & mengirim ke jaringan downline sendiri
                $exactDownline = $getMember->getDownlineByUsername($search, $auth['member']);

                if ($exactDownline !== null) {
                    $targetMember = [
                        'id' => $exactDownline->id,
                        'username' => $exactDownline->username,
                        'name' => $exactDownline->name,
                    ];
                }

                $memberSuggestions = $getMember->searchDownlineMembers($auth['member'], $search, 10);
            }
        }

        $products = $getProduct->getAllProduct(! $auth['isAdmin']);

        return Inertia::render('product/send', [
            'targetMember' => $targetMember,
            'memberSuggestions' => $memberSuggestions,
            'products' => $products,
            'username' => $targetMember['username'] ?? $search ?? null,
            'isAdmin' => $auth['isAdmin'],
        ]);
    }

    public function historySendProduct(MemberGetInterface $getMember, GetHistorySendProductQuery $query): Response|RedirectResponse
    {
        $auth = $this->getAuthMember();
        $networkId = null;
        if (! $auth['isAdmin']) {
            $username = $auth['member']->username;
            $memberData = $getMember->findByUsername($username);
            if ($memberData === null || (! $auth['isAdmin'] && $memberData->id !== $auth['member']->id)) {
                throw MemberException::getUsername($username);
            }

            $networkId = $memberData->network?->id ?? 0;
        }

        $data = $query->execute($this->request, $networkId);

        return Inertia::render('product/history', [
            'history' => $data['history'],
            'types' => PinTransfer::options(),
            'filters' => $this->getPaginatePayload($this->request, [
                'sender_username',
                'recipient_username',
                'product_name',
                'type',
                'description',
                'created_at_from',
                'created_at_to',
            ], $data),
        ]);
    }

    public function stockProduct(ProductGetInterface $getProduct, GetStockProductQuery $query): Response|RedirectResponse
    {
        $data = $query->execute($this->request);

        return Inertia::render('product/stock', [
            'stockist' => $data['stockist'],
            'filters' => $this->getPaginatePayload($this->request, [
                'username',
                'name',
                'total_products_min',
                'total_products_max',
                'stock_products_min',
                'stock_products_max',
            ], $data),
        ]);
    }

    public function memberProduct(MemberGetInterface $getMember, ProductGetInterface $getProduct, GetMemberProdutQuery $query, ?string $username = null): Response|RedirectResponse
    {
        $auth = $this->getAuthMember();

        if (! $auth['isAdmin'] && $username === null) {
            $username = $auth['member']->username;
        }

        $targetMember = null;

        // 2. [UNTUK MEMBER LOGIN & ADMIN DENGAN REQ USERNAME]
        if ($username !== null) {
            $memberData = $getMember->findByUsername($username);
            if ($memberData === null || (! $auth['isAdmin'] && $memberData->id !== $auth['member']->id)) {
                throw MemberException::getUsername($username);
            }

            $targetMember = $memberData;
            $networkId = $targetMember->network?->id ?? 0;
        }

        $totalCount = $getProduct->getStockMember($networkId); // all
        $stockCount = $getProduct->getTotalProductMember($networkId); // available
        $usedCount = $getProduct->getProductUsedMember($networkId); // used

        $data = $query->execute($networkId, $this->request);

        return Inertia::render('product/member', [
            'targetMember' => $targetMember,
            'totalCount' => $totalCount,
            'stockCount' => $stockCount,
            'usedCount' => $usedCount,
            'product' => $data['product'],
            'statuses' => PinStatus::options(),
            'filters' => $this->getPaginatePayload($this->request, [
                'id_pin',
                'stockist_username',
                'product_name',
                'varian_name',
                'status',
                'created_ad_from',
                'created_ad_to',
                'transfer_at_from',
                'transfer_at_to',
            ], $data),
        ]);
    }

    public function usageProduct(MemberGetInterface $getMember, ProductGetInterface $getProduct, GetUsageProductQuery $query, ?string $username = null): Response|RedirectResponse
    {
        $auth = $this->getAuthMember();
        if ($username === null || $username === '') {
            $username = $this->request->query('username');
        }

        if ($username !== null && ! is_string($username)) {
            throw MemberException::invalid('username');
        }

        $username = $username === '' ? null : $username;
        $networkId = null;
        $targetMember = null;
        $totalCount = null;
        $stockCount = null;
        $usedCount = null;

        if (! $auth['isAdmin']) {
            $authenticatedMember = $auth['member'];

            if ($username !== null && $username !== $authenticatedMember->username) {
                throw MemberException::getMemberNotFound($username);
            }

            $targetMember = $authenticatedMember;
            $networkId = $authenticatedMember->network?->id ?? 0;
        } elseif ($username !== null) {
            $memberData = $getMember->findByUsername($username);

            if ($memberData === null) {
                throw MemberException::getUsername($username);
            }

            $targetMember = $memberData;
            $networkId = $targetMember->network?->id ?? 0;
        }

        if ($networkId !== null) {
            $totalCount = $getProduct->getStockMember($networkId); // all
            $stockCount = $getProduct->getTotalProductMember($networkId); // available
            $usedCount = $getProduct->getProductUsedMember($networkId); // used
        }

        $data = $query->execute($this->request, $networkId);

        return Inertia::render('product/usage', [
            'targetMember' => $targetMember,
            'totalCount' => $totalCount,
            'stockCount' => $stockCount,
            'usedCount' => $usedCount,
            'usage' => $data['usage'],
            'types' => PinType::options(),
            'filters' => $this->getPaginatePayload($this->request, [
                'id_pin',
                'product_name',
                'varian_name',
                'stockist_username',
                'receiver_username',
                'receiver_name',
                'type',
                'dateused_from',
                'dateused_to',
            ], $data),
        ]);
    }

    public function totalProduct(MemberGetInterface $getMember, ProductGetInterface $getProduct, GetTotalProductQuery $query, ?string $username = null): Response|RedirectResponse
    {
        $auth = $this->getAuthMember();
        if ($username === null || $username === '') {
            $username = $this->request->query('username');
        }

        if ($username !== null && ! is_string($username)) {
            throw MemberException::invalid('username');
        }

        $username = $username === '' ? null : $username;
        $networkId = null;
        $targetMember = null;
        $totalCount = null;
        $stockCount = null;
        $usedCount = null;

        if (! $auth['isAdmin']) {
            $authenticatedMember = $auth['member'];

            if ($username !== null && $username !== $authenticatedMember->username) {
                throw MemberException::getMemberNotFound($username);
            }

            $targetMember = $authenticatedMember;
            $networkId = $authenticatedMember->network?->id ?? 0;
        } elseif ($username !== null) {
            $memberData = $getMember->findByUsername($username);

            if ($memberData === null) {
                throw MemberException::getUsername($username);
            }

            $targetMember = $memberData;
            $networkId = $targetMember->network?->id ?? 0;
        }

        if ($networkId !== null) {
            $totalCount = $getProduct->getStockMember($networkId); // all
            $stockCount = $getProduct->getTotalProductMember($networkId); // available
            $usedCount = $getProduct->getProductUsedMember($networkId); // used
        }

        $data = $query->execute($this->request, $networkId);

        return Inertia::render('product/total', [
            'targetMember' => $targetMember,
            'totalCount' => $totalCount,
            'stockCount' => $stockCount,
            'usedCount' => $usedCount,
            'total' => $data['total'],
            'filters' => $this->getPaginatePayload($this->request, [
                'product_name',
                'varian_name',
                'price_min',
                'price_max',
                'stock_in_min',
                'stock_in_max',
                'total_stock_min',
                'total_stock_max',
                'stock_out_min',
                'stock_out_max',
            ], $data),
        ]);
    }

    public function orderToCompany(GetOrderToCompanyQuery $query): Response|RedirectResponse
    {
        $validated = $this->request->validate([
            'tab' => ['sometimes', 'string', 'in:pending,confirm,done,cancel'],
        ]);
        $tab = OrderTab::from($validated['tab'] ?? OrderTab::PENDING->value);

        $data = $query->execute($this->request, $tab);

        return Inertia::render('product/order-company', [
            'order' => $data['order'],
            'tab' => $tab->value,
            'types' => OrderType::options(),
            'payment' => PaymentMethode::options(),
            'filters' => $this->getPaginatePayload($this->request, [
                'invoice',
                'name',
                'username',
                'type_order',
                'product_name',
                'total_checkout_min',
                'total_checkout_max',
                'resi',
                'payment_method',
                'created_at_from',
                'created_at_to',
                'cancelled_at_from',
                'cancelled_at_to',
                'cancelled_by',
                'dateconfirmed_from',
                'dateconfirmed_to',
                'dateexpired_from',
                'dateexpired_to',
                'confirmed_by',
            ], $data),
        ]);
    }

    public function historyRo(GetHistoryRoQuery $query): Response
    {
        $auth = $this->getAuthMember();
        $data = $query->execute($this->request, $auth['member'], $auth['isAdmin']);

        return Inertia::render('product/history-ro', [
            'ro' => $data['ro'],
            'types' => OrderType::options(),
            'statuses' => OrderStatus::options(),
            'payment' => PaymentMethode::options(),
            'filters' => $this->getPaginatePayload($this->request, [
                'created_at_from',
                'created_at_to',
                'invoice',
                'created_by',
                'username',
                'product_list',
                'total_checkout_min',
                'total_checkout_max',
                'payment_method',
                'status',
                'dateconfirmed_from',
                'dateconfirmed_to',
            ], $data),
        ]);
    }
}
