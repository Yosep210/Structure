// Components
import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { login } from '@/routes';
import CopyRight from '@/components/copyright';
import { store } from '@/actions/App/Modules/Auth/Http/Controllers/PasswordResetLinkController';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="Forgot password" />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <div className="space-y-6">
                <Form {...store.form()}>
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-6">
                                <div className="grid gap-2">
                                    <Input
                                        id="username"
                                        name="username"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="username"
                                        placeholder="Username"
                                    />
                                    <InputError message={errors.username} />
                                </div>

                                <div className="grid gap-2">
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        autoComplete="off"
                                        autoFocus
                                        placeholder="Email"
                                    />

                                    <InputError message={errors.email} />
                                </div>

                                <div className="my-6 flex items-center justify-start">
                                    <Button
                                        className="w-full"
                                        disabled={processing}
                                        data-test="email-password-reset-link-button"
                                    >
                                        {processing && (
                                            <LoaderCircle className="h-4 w-4 animate-spin" />
                                        )}
                                        Email password reset link
                                    </Button>
                                </div>
                            </div>
                        </>
                    )}
                </Form>

                <div className="space-x-1 text-center text-sm text-muted-foreground">
                    {/* <span>Or, return to</span> */}
                    <TextLink href={login()}>Kembali Login</TextLink>
                </div>
                <CopyRight />
            </div>
        </>
    );
}

ForgotPassword.layout = {
    title: 'Forgot password',
    description: 'Enter your email to receive a password reset link',
};
