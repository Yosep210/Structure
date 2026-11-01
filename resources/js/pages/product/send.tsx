import { sendProduct } from '@/actions/App/Modules/Product/Http/Controllers/ProductController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatCurrency } from '@/lib/format';
import { dashboard } from '@/routes';
import { Head, router, setLayoutProps, useForm } from '@inertiajs/react';
import {
    Box,
    CheckCircle2,
    Home,
    Package,
    Search,
    ShoppingCart,
    User,
    Users,
} from 'lucide-react';
import { useEffect, useRef, useState, type FormEvent } from 'react';

type ProductItem = {
    id: number;
    sku?: string;
    name: string;
    price?: number | string;
    price_member?: number | string;
    stock: number;
    varians?: ProductVariantItem[];
    weight?: number | string;
    description?: string | null;
};

type ProductVariantItem = {
    id: number;
    varian: string;
};

type TargetMember = {
    id?: number;
    username: string;
    name: string;
};

type Props = {
    targetMember?: TargetMember | null;
    memberSuggestions?: TargetMember[];
    products?: ProductItem[];
    username?: string | null;
    isAdmin?: boolean;
};

export default function SendProductPage({
    targetMember,
    memberSuggestions = [],
    products = [],
    username: initialUsername = '',
    isAdmin = false,
}: Props) {
    setLayoutProps({
        breadcrumbs: [
            {
                icon: Home,
                href: dashboard(),
            },
            {
                title: 'Produk Member',
                href: '#',
            },
            {
                title: 'Generate Produk',
                href: sendProduct(),
            },
        ],
    });

    const [searchUsername, setSearchUsername] = useState(
        targetMember?.username ?? initialUsername ?? '',
    );
    const [isSearching, setIsSearching] = useState(false);
    const isFirstRender = useRef(true);

    const { data, setData, post, processing, errors, reset } = useForm({
        username: targetMember?.username ?? initialUsername ?? '',
        product_id: '',
        variant_id: '',
        qty: 0,
        password: '',
    });

    // Auto-search / Live filter saat input diketik dengan debounce
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const trimmed = searchUsername.trim();
        const timer = setTimeout(() => {
            setIsSearching(true);
            router.visit(sendProduct({ username: trimmed || undefined }), {
                preserveState: true,
                preserveScroll: true,
                only: ['targetMember', 'memberSuggestions', 'username'],
                onFinish: () => setIsSearching(false),
            });
        }, 300);

        return () => clearTimeout(timer);
    }, [searchUsername]);

    const selectedProduct = products.find(
        (p) => String(p.id) === String(data.product_id),
    );
    const selectedVariant = selectedProduct?.varians?.find(
        (variant) => String(variant.id) === String(data.variant_id),
    );

    const handleSelectMember = (member: TargetMember) => {
        setSearchUsername(member.username);
        setData('username', member.username);
        router.visit(sendProduct({ username: member.username }), {
            preserveState: true,
            preserveScroll: true,
            only: ['targetMember', 'memberSuggestions', 'username'],
        });
    };

    const handleResetForm = () => {
        setSearchUsername('');
        setData({
            username: '',
            product_id: '',
            variant_id: '',
            qty: 0,
            password: '',
        });
        reset();
        router.visit(sendProduct(), {
            preserveState: true,
            preserveScroll: true,
            only: ['targetMember', 'memberSuggestions', 'username'],
        });
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post(sendProduct.url(), {
            preserveScroll: true,
            onSuccess: () => {
                handleResetForm();
            },
        });
    };

    return (
        <>
            <Head title="Generate Produk" />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header Page */}
                <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground">
                            Form Generate Produk
                        </h1>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    {/* Kolom Kiri: Form Input Pengiriman */}
                    <div className="md:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Package className="size-5 text-primary" />
                                    Informasi Pengiriman
                                </CardTitle>
                                <CardDescription>
                                    Ketik username/nama penerima dan pilih
                                    produk yang akan dikirim.
                                </CardDescription>
                            </CardHeader>

                            <CardContent>
                                <form
                                    onSubmit={handleSubmit}
                                    className="space-y-5"
                                >
                                    {/* 1. Target Member (Live Search / Filter) */}
                                    <div className="space-y-2">
                                        <Label htmlFor="username">
                                            Username{' '}
                                            <span className="text-destructive">
                                                *
                                            </span>
                                        </Label>
                                        <div className="relative">
                                            <Input
                                                id="username"
                                                name="username"
                                                type="text"
                                                placeholder={
                                                    isAdmin
                                                        ? 'Ketik username/nama member...'
                                                        : 'Ketik username/nama downline...'
                                                }
                                                value={searchUsername}
                                                onChange={(e) => {
                                                    setSearchUsername(
                                                        e.target.value,
                                                    );
                                                    setData(
                                                        'username',
                                                        e.target.value,
                                                    );
                                                }}
                                                className="pr-9"
                                                required
                                            />
                                            <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground">
                                                {isSearching ? (
                                                    <Spinner className="size-4" />
                                                ) : (
                                                    <Search className="size-4" />
                                                )}
                                            </div>
                                        </div>
                                        <InputError message={errors.username} />

                                        {/* Daftar Saran Hasil Pencarian (LIKE %) - Hanya tampil saat belum ada member terpilih */}
                                        {!targetMember &&
                                            memberSuggestions.length > 0 && (
                                                <div className="mt-2 space-y-2 rounded-lg border border-border/70 bg-muted/30 p-3">
                                                    <div className="flex items-center gap-1.5 text-xs font-semibold text-muted-foreground">
                                                        <Users className="size-3.5" />
                                                        {isAdmin
                                                            ? 'Pilihan Member Ditemukan'
                                                            : 'Pilihan Downline Ditemukan'}{' '}
                                                        (
                                                        {
                                                            memberSuggestions.length
                                                        }
                                                        ):
                                                    </div>
                                                    <div className="grid max-h-48 gap-1.5 overflow-y-auto pr-1">
                                                        {memberSuggestions.map(
                                                            (m) => (
                                                                <button
                                                                    key={
                                                                        m.id ??
                                                                        m.username
                                                                    }
                                                                    type="button"
                                                                    onClick={() =>
                                                                        handleSelectMember(
                                                                            m,
                                                                        )
                                                                    }
                                                                    className="flex items-center justify-between rounded-md border border-border/60 bg-background p-2 text-left text-xs transition-colors hover:bg-accent hover:text-accent-foreground"
                                                                >
                                                                    <div className="flex items-center gap-2">
                                                                        <span className="font-mono font-semibold text-foreground">
                                                                            {
                                                                                m.username
                                                                            }
                                                                        </span>
                                                                        <span className="text-muted-foreground">
                                                                            |
                                                                        </span>
                                                                        <span className="text-muted-foreground">
                                                                            {
                                                                                m.name
                                                                            }
                                                                        </span>
                                                                    </div>
                                                                    <Badge
                                                                        variant="outline"
                                                                        className="text-[10px]"
                                                                    >
                                                                        Pilih
                                                                    </Badge>
                                                                </button>
                                                            ),
                                                        )}
                                                    </div>
                                                </div>
                                            )}

                                        {/* Pesan Jika Tidak Ditemukan */}
                                        {searchUsername.trim() !== '' &&
                                            !targetMember &&
                                            memberSuggestions.length === 0 &&
                                            !isSearching && (
                                                <div className="rounded-md border border-amber-500/20 bg-amber-500/10 p-2.5 text-xs text-amber-600 dark:text-amber-400">
                                                    {isAdmin
                                                        ? `Tidak ada member yang cocok dengan kata kunci "${searchUsername}".`
                                                        : `Username "${searchUsername}" bukan jaringan downline Anda atau tidak ditemukan.`}
                                                </div>
                                            )}
                                    </div>

                                    {/* 2. Pilihan Produk & Qty (Proporsi 3/4 dan 1/4) */}
                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-4">
                                        <div className="space-y-2 sm:col-span-3">
                                            <Label htmlFor="product_id">
                                                Pilih Produk{' '}
                                                <span className="text-destructive">
                                                    *
                                                </span>
                                            </Label>
                                            <select
                                                id="product_id"
                                                name="product_id"
                                                value={data.product_id}
                                                onChange={(e) => {
                                                    const val = e.target.value;
                                                    setData((prev) => ({
                                                        ...prev,
                                                        product_id: val,
                                                        variant_id: '',
                                                        qty: val
                                                            ? prev.qty > 0
                                                                ? prev.qty
                                                                : 1
                                                            : 0,
                                                    }));
                                                }}
                                                className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-xs focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                                                required
                                            >
                                                <option value="">
                                                    -- Pilih Produk --
                                                </option>
                                                {products.map((product) => (
                                                    <option
                                                        key={product.id}
                                                        value={product.id}
                                                    >
                                                        {product.name}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError
                                                message={errors.product_id}
                                            />
                                        </div>

                                        <div className="space-y-2 sm:col-span-1">
                                            <Label htmlFor="qty">
                                                Jumlah (Qty){' '}
                                                <span className="text-destructive">
                                                    *
                                                </span>
                                            </Label>
                                            <Input
                                                id="qty"
                                                name="qty"
                                                type="number"
                                                min={1}
                                                value={data.qty}
                                                onChange={(e) =>
                                                    setData(
                                                        'qty',
                                                        e.target.value === ''
                                                            ? 0
                                                            : Number(
                                                                  e.target
                                                                      .value,
                                                              ),
                                                    )
                                                }
                                                required
                                            />
                                            <InputError message={errors.qty} />
                                        </div>
                                    </div>

                                    {!isAdmin &&
                                        selectedProduct?.varians &&
                                        selectedProduct.varians.length > 0 && (
                                            <div className="space-y-2">
                                                <Label htmlFor="variant_id">
                                                    Pilih Varian{' '}
                                                    <span className="text-destructive">
                                                        *
                                                    </span>
                                                </Label>
                                                <select
                                                    id="variant_id"
                                                    name="variant_id"
                                                    value={data.variant_id}
                                                    onChange={(e) =>
                                                        setData(
                                                            'variant_id',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-xs focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                                                    required
                                                >
                                                    <option value="">
                                                        -- Pilih Varian --
                                                    </option>
                                                    {selectedProduct.varians.map(
                                                        (variant) => (
                                                            <option
                                                                key={variant.id}
                                                                value={
                                                                    variant.id
                                                                }
                                                            >
                                                                {variant.varian}
                                                            </option>
                                                        ),
                                                    )}
                                                </select>
                                                <InputError
                                                    message={errors.variant_id}
                                                />
                                            </div>
                                        )}

                                    {/* 3. Password Konfirmasi (Khusus Member) */}
                                    {!isAdmin && (
                                        <div className="space-y-2">
                                            <Label htmlFor="password">
                                                Password Konfirmasi{' '}
                                                <span className="text-destructive">
                                                    *
                                                </span>
                                            </Label>
                                            <Input
                                                id="password"
                                                name="password"
                                                type="password"
                                                placeholder="Masukkan password akun Anda"
                                                value={data.password}
                                                onChange={(e) =>
                                                    setData(
                                                        'password',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                            <InputError
                                                message={errors.password}
                                            />
                                        </div>
                                    )}

                                    {/* Tombol Submit */}
                                    <div className="flex justify-center gap-3 pt-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={handleResetForm}
                                            disabled={processing}
                                        >
                                            Reset
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing && (
                                                <Spinner className="mr-2" />
                                            )}
                                            <ShoppingCart className="mr-2 size-4" />
                                            Kirim Produk
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Kolom Kanan: Ringkasan Preview */}
                    <div className="space-y-6">
                        {/* Preview Member Penerima */}
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <User className="size-4 text-primary" />
                                    Member Penerima
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {targetMember ? (
                                    <div className="space-y-2 rounded-lg bg-muted/50 p-3 text-sm">
                                        <div className="flex items-center gap-2 font-semibold text-foreground">
                                            <CheckCircle2 className="size-4 text-emerald-500" />
                                            Member Terverifikasi
                                        </div>
                                        <div className="flex justify-between border-t border-border pt-2">
                                            <span className="text-muted-foreground">
                                                Username:
                                            </span>
                                            <span className="font-mono font-medium uppercase">
                                                {targetMember.username}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Nama:
                                            </span>
                                            <span className="font-medium">
                                                {targetMember.name}
                                            </span>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="rounded-lg border border-dashed p-4 text-center text-xs text-muted-foreground">
                                        Ketik username atau nama member pada
                                        form untuk memverifikasi data penerima.
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Preview Produk Terpilih */}
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Box className="size-4 text-primary" />
                                    Rincian Produk
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {selectedProduct ? (
                                    <div className="space-y-2 rounded-lg bg-muted/50 p-3 text-sm">
                                        <div className="font-semibold text-foreground">
                                            {selectedProduct.name}
                                        </div>
                                        <div className="flex justify-between border-t border-border pt-2">
                                            <span className="text-muted-foreground">
                                                Harga Satuan:
                                            </span>
                                            <span className="font-medium text-foreground">
                                                {formatCurrency(
                                                    selectedProduct.price_member ??
                                                        selectedProduct.price ??
                                                        0,
                                                )}
                                            </span>
                                        </div>
                                        {!isAdmin && (
                                            <div className="space-y-2 border-t border-border pt-2">
                                                <div className="flex justify-between gap-3">
                                                    <span className="text-muted-foreground">
                                                        Varian:
                                                    </span>
                                                    <span className="text-right font-medium">
                                                        {selectedVariant?.varian ??
                                                            '-'}
                                                    </span>
                                                </div>
                                                <div className="flex items-center justify-between gap-3">
                                                    <span className="text-muted-foreground">
                                                        Ketersediaan:
                                                    </span>
                                                    <Badge
                                                        variant={
                                                            selectedProduct.stock >
                                                            0
                                                                ? 'secondary'
                                                                : 'destructive'
                                                        }
                                                    >
                                                        {selectedProduct.stock}
                                                    </Badge>
                                                </div>
                                            </div>
                                        )}
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Jumlah (Qty):
                                            </span>
                                            <span className="font-medium">
                                                {data.qty} pcs
                                            </span>
                                        </div>
                                        <div className="flex justify-between border-t border-border pt-2 font-bold text-foreground">
                                            <span>Estimasi Total:</span>
                                            <span className="font-mono text-primary">
                                                {formatCurrency(
                                                    Number(
                                                        selectedProduct.price_member ??
                                                            selectedProduct.price ??
                                                            0,
                                                    ) * Number(data.qty),
                                                )}
                                            </span>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="rounded-lg border border-dashed p-4 text-center text-xs text-muted-foreground">
                                        Pilih produk pada form untuk melihat
                                        rincian harga.
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
