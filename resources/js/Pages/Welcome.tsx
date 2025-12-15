import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Button } from '@/components/ui/button';

export default function Welcome({ auth }: PageProps) {
    return (
        <>
            <Head title="Hoş Geldiniz" />
            <div className="min-h-screen bg-gray-100">
                <div className="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-center bg-gray-100 selection:bg-red-500 selection:text-white">
                    <div className="max-w-7xl mx-auto p-6 lg:p-8">
                        <div className="flex justify-center">
                            <h1 className="text-4xl font-bold text-gray-900">
                                {import.meta.env.VITE_APP_NAME || 'Laravel'}
                            </h1>
                        </div>

                        <div className="mt-16">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                                <div className="scale-100 p-6 bg-white from-gray-700/50 via-transparent rounded-lg shadow-2xl shadow-gray-500/20 flex motion-safe:hover:scale-[1.01] transition-all duration-250">
                                    <div>
                                        <h2 className="text-xl font-semibold text-gray-900">
                                            Inertia.js + React + TypeScript
                                            <Button variant="destructive">Furkan</Button>
                                        </h2>

                                        <p className="mt-4 text-gray-500 text-sm leading-relaxed">
                                            Inertia.js, React ve TypeScript ile modern bir uygulama geliştirmeye başlayın.
                                            Bu starter kit, Laravel backend'i ile sorunsuz bir şekilde entegre olur.
                                        </p>
                                    </div>
                                </div>

                                <div className="scale-100 p-6 bg-white from-gray-700/50 via-transparent rounded-lg shadow-2xl shadow-gray-500/20 flex motion-safe:hover:scale-[1.01] transition-all duration-250">
                                    <div>
                                        <h2 className="text-xl font-semibold text-gray-900">
                                            Filament Admin Panel
                                        </h2>

                                        <p className="mt-4 text-gray-500 text-sm leading-relaxed">
                                            Filament 4 ile güçlü bir admin panel sistemi. Kullanıcı yönetimi, roller ve
                                            izinler hazır olarak gelir.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-center mt-16 px-0 sm:items-center sm:justify-between">
                            <div className="text-center text-sm text-gray-500 sm:text-left">
                                <div className="flex items-center gap-4">
                                    {auth.user ? (
                                        <Link
                                            href="/admin"
                                            className="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                                        >
                                            Admin Panel
                                        </Link>
                                    ) : (
                                        <>
                                            <Link
                                                href="/login"
                                                className="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                                            >
                                                Giriş Yap
                                            </Link>
                                            <Link
                                                href="/register"
                                                className="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                                            >
                                                Kayıt Ol
                                            </Link>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

