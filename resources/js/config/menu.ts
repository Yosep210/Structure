import {
    CreditCard,
    Flag,
    Home,
    Network,
    ShoppingBag,
    Users,
} from "lucide-react";
import type { NavGroup } from "@/types";
import {
    binaryMember,
    generationList,
    memberList,
    reportReg,
    sponsorList,
} from "@/actions/App/Modules/Member/Http/Controllers/MemberController";
import {
    list as bonusList,
    statements as bonusStatement,
} from "@/routes/bonus";
import { eWalletList } from "@/actions/App/Modules/EWallet/Http/Controllers/EWalletController";
import {
    autoRoHistory,
    autoRoList,
    autoRoMonthly,
} from "@/actions/App/Modules/AutoRo/Http/Controllers/AutoRoController";
import { withdrawList } from "@/actions/App/Modules/Withdraw/Http/Controllers/WithdrawController";
import {
    historySendProduct,
    memberProduct,
    orderToCompany,
    sendProduct,
    stockProduct,
    totalProduct,
    usageProduct,
} from "@/actions/App/Modules/Product/Http/Controllers/ProductController";
import { historyRo } from "@/routes/product";
import {
    bonusHistory,
    statementMember,
} from "@/actions/App/Modules/Bonus/Http/Controllers/BonusController";
import { dashboard } from "@/routes";

export const adminMenu: NavGroup[] = [
    {
        items: [
            {
                title: "Dashboard",
                href: dashboard(),
                icon: Home,
            },
        ],
    },
    {
        label: "Member",
        items: [
            {
                title: "Data Member",
                icon: Users,
                items: [
                    {
                        title: "Tambah Member Baru",
                        href: "#",
                    },
                    {
                        title: "List Member",
                        href: memberList(),
                    },
                    {
                        title: "List Sponsor",
                        href: sponsorList(),
                    },
                    {
                        title: "Jaringan Generasi",
                        href: generationList(),
                    },
                    {
                        title: "Jaringan Binary",
                        href: binaryMember(),
                    },
                ],
            },
            {
                title: "Komisi",
                icon: CreditCard,
                items: [
                    {
                        title: "Bonus Detail",
                        href: bonusList(),
                        activeRoutes: [bonusHistory()],
                    },
                    {
                        title: "Statement Komisi",
                        href: bonusStatement(),
                        activeRoutes: [statementMember()],
                    },
                    {
                        title: "eWallet",
                        href: eWalletList(),
                    },
                    {
                        title: "Auto RO",
                        href: autoRoList(),
                    },
                    {
                        title: "Withdraw",
                        href: withdrawList(),
                    },
                ],
            },
            {
                title: "Data Product Order",
                icon: ShoppingBag,
                items: [
                    {
                        title: "Kirim Product",
                        href: sendProduct(),
                    },
                    {
                        title: "Stock Product Member",
                        href: stockProduct(),
                        activeRoutes: [usageProduct(), totalProduct()],
                    },
                    {
                        title: "History Kirim Product",
                        href: historySendProduct(),
                    },
                    {
                        title: "Orderan Ke Perusahaan",
                        href: orderToCompany(),
                    },
                ],
            },
            {
                title: "Laporan",
                icon: Flag,
                items: [
                    {
                        title: "Pendaftaran",
                        href: reportReg(),
                    },
                    {
                        title: "History RO",
                        href: historyRo(),
                    },
                ],
            },
        ],
    },
];

export const memberMenu: NavGroup[] = [
    {
        items: [
            {
                title: "Dashboard",
                href: dashboard(),
                icon: Home,
            },
        ],
    },
    {
        label: "Menu",
        items: [
            {
                title: "Member",
                icon: Users,
                items: [
                    {
                        title: "Tambah Member Baru",
                        href: "#",
                    },
                    {
                        title: "Manual RO",
                        href: "#",
                    },
                ],
            },
            {
                title: "Jaringan",
                icon: Network,
                items: [
                    {
                        title: "Jaringan Generasi",
                        href: generationList(),
                    },
                    {
                        title: "Jaringan Binary",
                        href: binaryMember(),
                    },
                ],
            },
            {
                title: "Komisi",
                icon: CreditCard,
                items: [
                    {
                        title: "Bonus Detail",
                        href: bonusHistory(),
                    },
                    {
                        title: "Statement Komisi",
                        href: statementMember(),
                    },
                    {
                        title: "eWallet",
                        href: eWalletList(),
                    },
                    {
                        title: "Auto RO",
                        href: autoRoMonthly(),
                        activeRoutes: [autoRoHistory()],
                    },
                    {
                        title: "Saldo Auto RO",
                        href: "#",
                    },
                    {
                        title: "Withdraw",
                        href: withdrawList(),
                    },
                ],
            },
            {
                title: "Product Member",
                icon: ShoppingBag,
                items: [
                    {
                        title: "Data Product",
                        href: memberProduct(),
                        activeRoutes: [usageProduct(), totalProduct()],
                    },
                    {
                        title: "Kirim Product",
                        href: sendProduct(),
                    },
                    {
                        title: "History Kirim Product",
                        href: historySendProduct(),
                    },
                ],
            },
            {
                title: "Laporan",
                icon: Flag,
                items: [
                    {
                        title: "Pendaftaran",
                        href: reportReg(),
                    },
                    {
                        title: "History RO",
                        href: historyRo(),
                    },
                ],
            },
        ],
    },
];
