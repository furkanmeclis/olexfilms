import { PropsWithChildren } from 'react';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <Link href="/">
                    <h1 className="text-2xl font-bold text-gray-900">
                        {import.meta.env.VITE_APP_NAME || 'Laravel'}
                    </h1>
                </Link>
            </div>

            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {children}
            </div>
        </div>
    );
}

