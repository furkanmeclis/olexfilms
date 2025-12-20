import { Head } from '@inertiajs/react';
import React from 'react';
import OlexLogo from '@/components/OlexLogo';

interface WarrantyErrorProps {
    serviceNo: string;
}

const WarrantyError: React.FC<WarrantyErrorProps> = ({ serviceNo }) => {
    return (
        <>
            <Head title="Hizmet Bulunamadı" />
            <div className="flex justify-center items-center min-h-screen bg-gradient-to-r from-green-900 via-green-600 to-green-900">
                <div className="w-full max-w-md mx-4">
                    <div className="bg-black rounded-[20px] p-8 shadow-2xl">
                        <div className="flex justify-center mb-6">
                            <OlexLogo text={true} dark className="w-1/2" />
                        </div>

                        <div className="text-center mb-8">
                            <div className="mb-4">
                                <span className="text-6xl text-yellow-400">⚠️</span>
                            </div>
                            <h1 className="font-avaganti text-2xl font-bold text-white mb-4">
                                Hizmet Bulunamadı
                            </h1>
                            <p className="text-green-100 mb-2">
                                Hizmet numarası <span className="font-bold text-white">{serviceNo}</span> ile eşleşen bir kayıt bulunamadı.
                            </p>
                            <p className="text-green-200 text-sm mt-4">
                                Lütfen hizmet numaranızı kontrol edip tekrar deneyiniz.
                            </p>
                        </div>

                        <div className="bg-[#002315] rounded-lg p-4 mb-6 border border-[#00482b]">
                            <div className="text-center">
                                <p className="text-green-300 text-sm mb-2">Aradığınız Hizmet Numarası:</p>
                                <p className="text-white font-bold text-lg">{serviceNo}</p>
                            </div>
                        </div>

                        <div className="text-center">
                            <button
                                onClick={() => window.location.href = '/'}
                                className="py-2 px-6 bg-white rounded-lg text-black font-avaganti tracking-tight hover:font-bold delay-100 ring-0 outline-0 focus:outline-none focus:ring-2 focus:ring-green-300 focus:font-bold transition-all"
                            >
                                Ana Sayfaya Dön
                            </button>
                        </div>

                        <div className="w-full flex justify-center items-center mt-8">
                            <span className="font-thin text-[12px]">
                                <b className="font-bold text-white">OLEX Films ©</b>{' '}
                                <span className="text-gray-200 dark:text-gray-400">
                                    {new Date().getFullYear()} All Rights Reserved.
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default WarrantyError;

