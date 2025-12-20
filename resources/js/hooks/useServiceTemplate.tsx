import React, { useRef } from 'react';
import OlexLogo from '@/components/OlexLogo';

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

interface UseServiceTemplateProps {
    onAfterPrint?: () => void;
    serviceData?: ServiceData;
}

const useServiceTemplate = ({ onAfterPrint, serviceData }: UseServiceTemplateProps = {}) => {
    const printRef = useRef<HTMLDivElement>(null);

    const handlePrint = () => {
        if (!printRef.current) return;

        const printWindow = window.open('', '_blank');
        if (!printWindow) return;

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
                <head>
                    <title>Dijital Sertifika</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        body {
                            font-family: Arial, sans-serif;
                            padding: 20px;
                            background: linear-gradient(to bottom, #008951, #003d24);
                            min-height: 100vh;
                        }
                        .print-content {
                            background: white;
                            padding: 20px;
                            border-radius: 8px;
                            max-width: 800px;
                            margin: 0 auto;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                        }
                        th, td {
                            padding: 10px;
                            border: 1px solid #ddd;
                        }
                        th {
                            background-color: #003821;
                            color: white;
                        }
                        @media print {
                            body {
                                background: white;
                            }
                            .print-content {
                                box-shadow: none;
                            }
                        }
                    </style>
                </head>
                <body>
                    ${printRef.current.innerHTML}
                </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();
        
        setTimeout(() => {
            printWindow.print();
            if (onAfterPrint) {
                setTimeout(() => {
                    onAfterPrint();
                }, 1500);
            }
        }, 250);
    };

    const ServiceTemplate = () => {
        if (!serviceData) return null;

        return (
            <div ref={printRef} className="bg-gradient-to-b pt-10 pb-10 from-[#008951] to-[#003d24] min-h-screen">
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
                                            {serviceData.brand_logo && (
                                                <img
                                                    src={serviceData.brand_logo}
                                                    className="h-[20px] mr-2 object-contain inline-flex"
                                                    alt="brandLogo"
                                                />
                                            )}
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
            </div>
        );
    };

    return { handlePrint, ServiceTemplate };
};

export default useServiceTemplate;

