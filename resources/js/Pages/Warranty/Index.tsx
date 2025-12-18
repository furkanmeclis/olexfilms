import { Head } from '@inertiajs/react';
import React from 'react';
import OlexLogo from '@/components/OlexLogo';

declare global {
    function route(name: string, params?: any): string;
}

interface AppliedService {
    category: string;
    name: string;
    warranty: string;
    multiple?: boolean;
    warrantyMultiple?: boolean;
}

interface ServiceData {
    service_no: string;
    brand: string;
    brand_logo: string | null;
    model: string;
    generation: string;
    year: number;
    plate: string;
    applied_services: AppliedService[];
    dealer: {
        company_name: string;
        company_city: string;
        company_country: string;
    };
}

interface WarrantyIndexProps {
    serviceNo: string;
    serviceData: ServiceData;
}

const WarrantyIndex: React.FC<WarrantyIndexProps> = ({ serviceNo, serviceData }) => {
    if (!serviceData) {
        return (
            <>
                <Head title="Hata" />
                <div className="flex justify-center items-center min-h-screen">
                    <div className="text-center">
                        <h1 className="text-2xl font-bold text-red-600">Hata</h1>
                        <p className="mt-4">Hizmet verileri yüklenemedi.</p>
                    </div>
                </div>
            </>
        );
    }

    const Content = ({ mobile = false }: { mobile?: boolean }) => {
        return serviceData?.brand_logo ? (
            <div className={`bg-gradient-to-b pt-10 h-full from-[#008951] to-[#003d24] ${mobile ? 'pb-10 pt-15' : ''}`}>
                <div className="flex justify-center">
                    <OlexLogo text={true} dark className="w-1/2" />
                </div>
                <h1 className="font-avaganti text-center mt-4 mb-2 text-white">
                    Araç Bilgileri
                </h1>
                <div className="mx-4 rounded-[14px] overflow-hidden border border-[#47a27d] font-avaganti">
                    <table className="w-full rounded-lg">
                        <thead className="bg-[#003821] text-white">
                            <tr className="border-b-2 border-[#00482b]">
                                <td colSpan={2}>
                                    <div className="flex justify-between items-center py-2 px-4">
                                        <span className="text-center tracking-wide title w-full">
                                            <img
                                                src={serviceData.brand_logo}
                                                className="h-[20px] mr-2 object-contain inline-flex"
                                                alt="brandLogo"
                                            />
                                            {serviceData.brand} <br />
                                            {serviceData.model}{serviceData.generation ? ' ' + serviceData.generation : ''} ({serviceData.year})
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr className="border-b-1 border-[#00482b] bg-[#003821]">
                                <td className="border-r-2 border-[#00482b] w-1/2">
                                    <div className="flex justify-between items-center py-2 px-4">
                                        <div className="flex justify-between flex-col">
                                            <span className="text-sm">Hizmet Numarası:</span>
                                            <span>{serviceData.service_no}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div className="flex justify-between items-center py-2 px-4">
                                        <div className="flex justify-between flex-col">
                                            <span className="text-sm">Plaka:</span>
                                            <div className="flex justify-start gap-x-2">
                                                <span className="h-6 w-6 rounded bg-blue-800 p-1 text-sm flex justify-center items-center">
                                                    TR
                                                </span>
                                                <span>{serviceData.plate}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </thead>
                    </table>
                </div>
                {serviceData?.applied_services?.length > 0 && (
                    <>
                        <h1 className="font-avaganti text-center mt-4 mb-2 text-white">
                            Hizmet Bilgileri
                        </h1>
                        {serviceData.applied_services.map((product, index) => (
                            <div key={index} className="mb-2 mx-4 rounded-[14px] overflow-hidden border border-[#47a27d] font-avaganti">
                                <table className="w-full rounded-lg">
                                    <thead className="bg-[#003a22] text-white">
                                        <tr className="border-b-2 border-[#00482b]">
                                            <td colSpan={2}>
                                                <div className="flex justify-between items-center py-2 px-4 gap-x-2">
                                                    <img
                                                        src="/uploads/olex-logo-yatay.svg"
                                                        className="w-1/4"
                                                        alt="olex logo"
                                                    />
                                                    <span className="text-end tracking-wide w-full">
                                                        {product.category}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr className="border-b-1 border-[#00482b] bg-[#002315]">
                                            <td className="border-r-2 border-[#00482b] w-1/2">
                                                <div className="flex justify-between items-center py-2 px-4">
                                                    <div className="flex justify-between flex-col text-sm">
                                                        <span className="text-sm">Ürün:</span>
                                                        <span className={product?.multiple === true ? 'text-sm' : ''}>
                                                            {product.name}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div className="flex justify-between items-center py-2 px-4">
                                                    <div className="flex justify-between flex-col text-sm">
                                                        <span className="text-sm">Garanti:</span>
                                                        <span
                                                            className={product?.warrantyMultiple === true ? 'text-sm' : ''}
                                                            dangerouslySetInnerHTML={{
                                                                __html: product.warranty.replace(
                                                                    'X',
                                                                    '<span class="text-red-600">⚠️</span>'
                                                                ),
                                                            }}
                                                        />
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        ))}
                    </>
                )}
                <h1 className="font-avaganti text-center mt-4 mb-2 text-white">
                    Hizmet Veren Şube
                </h1>
                <div className="mb-8 mx-4 rounded-[14px] overflow-hidden border border-[#47a27d] font-avaganti">
                    <table className="w-full rounded-lg">
                        <thead className="bg-[#002315] text-white">
                            <tr className="border border-[#00482b]">
                                <td colSpan={2}>
                                    <div className="flex justify-between items-center py-2 px-4 gap-x-2">
                                        <img
                                            src="/uploads/olex-logo-yatay.svg"
                                            className="w-1/4"
                                            alt="olex logo"
                                        />
                                        <span className="flex justify-between text-md items-end flex-col">
                                            <span>{serviceData.dealer.company_name} Şubesi</span>
                                            <span className="text-sm">
                                                {serviceData.dealer.company_city} - {serviceData.dealer.company_country}
                                            </span>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div className="w-full flex justify-center items-center">
                    <button
                        onClick={() => {
                            // PDF route'u eklenecek
                            // window.open(route('warranty.pdf', serviceNo), '_blank');
                            console.log('PDF açılacak:', serviceNo);
                        }}
                        className="py-2 px-6 bg-white rounded-lg text-black font-avaganti tracking-tight hover:font-bold delay-100 ring-0 outline-0 focus:outline-none focus:ring-2 focus:ring-green-300 focus:font-bold"
                    >
                        Dijital Sertifikayı Görüntüle
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
        ) : (
            <></>
        );
    };

    return (
        <>
            <Head title="Garanti Sorgulama" />
            <div className="flex justify-center items-center lg:min-h-screen lg:bg-gradient-to-r from-green-900 via-green-600 to-green-900">
                <div className="sm:hidden w-full h-screen overflow-y-auto">
                    <Content mobile />
                </div>
                <div className="hidden sm:block max-w-[400px] w-full">
                    {/* iPhone mockup yerine basit bir container */}
                    <div className="bg-black rounded-[40px] p-2 shadow-2xl">
                        <div className="bg-white rounded-[32px] overflow-hidden" style={{ height: '812px' }}>
                            <div className="h-full overflow-y-auto">
                                <Content />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default WarrantyIndex;

