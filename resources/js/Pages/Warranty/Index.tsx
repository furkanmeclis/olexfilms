import { Head } from '@inertiajs/react';
import React, { useState } from 'react';
import { IPhoneMockup } from 'react-device-mockup';
import OlexLogo from '@/components/OlexLogo';
import useServiceTemplate from '@/hooks/useServiceTemplate';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

interface AppliedService {
    category: string;
    name: string;
    warranty: string;
    multiple?: boolean;
    warrantyMultiple?: boolean;
}

interface MeasurementPosition {
    1: string | null;
    2: string | null;
    3: string | null;
    4: string | null;
    5: string | null;
}

interface Measurement {
    part_type: string;
    part_label: string;
    substrate_type: string;
    min_value: number;
    max_value: number;
    avg_value: number;
    positions: MeasurementPosition;
}

interface MeasurementPlaceGroup {
    place_id: string;
    place_label: string;
    measurements: Measurement[];
}

interface MeasurementData {
    measurements: MeasurementPlaceGroup[];
    unit_of_measure: string;
}

interface MeasurementsResponse {
    before?: MeasurementData;
    after?: MeasurementData;
}

interface ServiceData {
    service_no: string;
    brand: string;
    brand_logo: string | null;
    show_brand_name?: boolean;
    model: string;
    generation: string;
    year: number;
    plate: string;
    applied_services: AppliedService[];
    measurements?: MeasurementsResponse;
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
    const [visible, setVisible] = useState(false);
    const [measurementsVisible, setMeasurementsVisible] = useState(false);
    const { handlePrint, ServiceTemplate } = useServiceTemplate({
        onAfterPrint: () => {
            setTimeout(() => {
                setVisible(false);
            }, 1500);
        },
        serviceData,
    });

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
        return serviceData ? (
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
                                            {serviceData.brand_logo && (
                                                <img
                                                    src={serviceData.brand_logo}
                                                    className={serviceData.show_brand_name ? 'h-[20px] mr-2 object-contain inline-flex' : 'h-[25px] object-contain inline-flex'}
                                                    alt="brandLogo"
                                                />
                                            )}
                                            {serviceData.show_brand_name !== false && serviceData.brand} <br />
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
                                                        src="/images/olex-logo-yatay.svg"
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
                                                                    '<i class="pi pi-exclamation-triangle text-red-600"></i>'
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
                                            src="/images/olex-logo-yatay.svg"
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
                <div className="w-full flex flex-row flex-wrap justify-center items-center gap-3">
                    <button
                        onClick={() => {
                            window.open(`/warranty/${serviceNo}/pdf`, '_blank');
                            // setVisible(true);
                        }}
                        className="py-2 px-6 bg-white rounded-lg text-black font-avaganti tracking-tight hover:font-bold delay-100 ring-0 outline-0 focus:outline-none focus:ring-2 focus:ring-green-300 focus:font-bold"
                    >
                        Dijital Sertifikayı Görüntüle
                    </button>
                    {serviceData?.measurements && (serviceData.measurements.before || serviceData.measurements.after) && (
                        <button
                            onClick={() => setMeasurementsVisible(true)}
                            className="py-2 px-6 bg-[#47a27d] rounded-lg text-white font-avaganti tracking-tight hover:bg-[#3d8a6a] delay-100 ring-0 outline-0 focus:outline-none focus:ring-2 focus:ring-green-300"
                        >
                            Araç Ölçüm Sonuçları
                        </button>
                    )}
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
                <div className="hidden sm:block">
                    <IPhoneMockup
                        screenWidth={400}
                        frameColor="#000"
                        hideStatusBar
                        transparentNavBar
                    >
                        <Content />
                    </IPhoneMockup>
                </div>
            </div>
            <Dialog open={visible} onOpenChange={setVisible}>
                <DialogContent className="max-w-[50vw] max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Dijital Sertifika</DialogTitle>
                    </DialogHeader>
                    <div>
                        <ServiceTemplate />
                    </div>
                    <DialogFooter>
                        <Button onClick={handlePrint}>Yazdır</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={measurementsVisible} onOpenChange={setMeasurementsVisible}>
                <DialogContent className="max-w-[90vw] max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Araç Ölçüm Sonuçları</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-6">
                        {serviceData?.measurements && (serviceData.measurements.before || serviceData.measurements.after) ? (
                            <>
                                {/* Before Measurements */}
                                {serviceData.measurements.before && serviceData.measurements.before.measurements.length > 0 && (
                                    <div className="space-y-4">
                                        <h2 className="text-2xl font-bold text-center text-[#003f26] mb-4">
                                            Hizmet Öncesi Ölçümler
                                            {serviceData.measurements.before.unit_of_measure && (
                                                <span className="text-sm font-normal ml-2">
                                                    ({serviceData.measurements.before.unit_of_measure})
                                                </span>
                                            )}
                                        </h2>
                                        {serviceData.measurements.before.measurements.map((placeGroup, placeIndex) => (
                                            <div key={placeIndex} className="space-y-4">
                                                <h3 className="text-xl font-bold text-center text-[#003f26]">
                                                    {placeGroup.place_label.toUpperCase()} TARAF
                                                </h3>
                                                <div className="border-2 border-[#47a27d] rounded-lg overflow-hidden">
                                                    <div className="overflow-x-auto">
                                                        <table className="w-full border-collapse">
                                                            <thead className="bg-[#003821] text-white">
                                                                <tr>
                                                                    <th className="border border-[#00482b] px-2 py-2 text-xs text-left" style={{ width: '12%' }}>
                                                                        Part Adı
                                                                    </th>
                                                                    <th className="border border-[#00482b] px-2 py-2 text-xs text-center" style={{ width: '8%' }}>
                                                                        Kaplama
                                                                    </th>
                                                                    <th className="border border-[#00482b] px-2 py-2 text-xs text-center" style={{ width: '6%' }}>
                                                                        Min
                                                                    </th>
                                                                    <th className="border border-[#00482b] px-2 py-2 text-xs text-center" style={{ width: '6%' }}>
                                                                        Max
                                                                    </th>
                                                                    <th className="border border-[#00482b] px-2 py-2 text-xs text-center" style={{ width: '14%' }}>
                                                                        Orta Değer
                                                                    </th>
                                                                    {[1, 2, 3, 4, 5].map((pos) => (
                                                                        <th key={pos} className="border border-[#00482b] px-2 py-2 text-xs text-center" style={{ width: '5%' }}>
                                                                            <br />{pos}.
                                                                        </th>
                                                                    ))}
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {placeGroup.measurements.map((measurement, measIndex) => (
                                                                    <tr key={measIndex} className="bg-[#002315] text-white">
                                                                        <td className="border border-[#00482b] px-2 py-2 text-xs text-left">
                                                                            {measurement.part_label}
                                                                        </td>
                                                                        <td className="border border-[#00482b] px-2 py-2 text-xs text-left">
                                                                            {measurement.substrate_type}
                                                                        </td>
                                                                        <td className="border border-[#00482b] px-2 py-2 text-xs text-center">
                                                                            {Math.round(measurement.min_value).toLocaleString('tr-TR')}
                                                                        </td>
                                                                        <td className="border border-[#00482b] px-2 py-2 text-xs text-center">
                                                                            {Math.round(measurement.max_value).toLocaleString('tr-TR')}
                                                                        </td>
                                                                        <td className="border border-[#00482b] px-2 py-2 text-xs text-center">
                                                                            {measurement.avg_value.toFixed(1)}
                                                                        </td>
                                                                        {[1, 2, 3, 4, 5].map((pos) => {
                                                                            const positionValue = measurement.positions[pos as keyof MeasurementPosition];
                                                                            return (
                                                                                <td
                                                                                    key={pos}
                                                                                    className={`border border-[#00482b] px-2 py-2 text-xs text-center ${positionValue === null ? 'text-gray-500' : ''
                                                                                        }`}
                                                                                >
                                                                                    {positionValue !== null
                                                                                        ? Math.round(parseFloat(positionValue)).toLocaleString('tr-TR')
                                                                                        : '-'}
                                                                                </td>
                                                                            );
                                                                        })}
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                {/* After Measurements */}
                                {serviceData.measurements.after && serviceData.measurements.after.measurements.length > 0 && (
                                    <div className="space-y-4">
                                        <h2 className="text-2xl font-bold text-center text-[#003f26] mb-4">
                                            Hizmet Sonrası Ölçümler
                                            {serviceData.measurements.after.unit_of_measure && (
                                                <span className="text-sm font-normal ml-2">
                                                    ({serviceData.measurements.after.unit_of_measure})
                                                </span>
                                            )}
                                        </h2>
                                        {serviceData.measurements.after.measurements.map((placeGroup, placeIndex) => (
                                            <div key={placeIndex} className="space-y-4">
                                                <h3 className="text-xl font-bold text-center text-[#003f26]">
                                                    {placeGroup.place_label.toUpperCase()} TARAF
                                                </h3>
                                                <div className="border-2 border-[#47a27d] rounded-lg overflow-hidden">
                                                    <div className="overflow-x-auto">
                                                        <table className="w-full border-collapse">
                                                            <thead className="bg-[#003821] text-white">
                                                                <tr>
                                                                    <th className="border border-[#00482b] px-2 py-2 text-xs text-left" style={{ width: '12%' }}>
                                                                        Part Adı
                                                                    </th>
                                                                    <th className="border border-[#00482b] px-2 py-2 text-xs text-center" style={{ width: '8%' }}>
                                                                        Kaplama
                                                                    </th>
                                                                    <th className="border border-[#00482b] px-2 py-2 text-xs text-center" style={{ width: '6%' }}>
                                                                        Min
                                                                    </th>
                                                                    <th className="border border-[#00482b] px-2 py-2 text-xs text-center" style={{ width: '6%' }}>
                                                                        Max
                                                                    </th>
                                                                    <th className="border border-[#00482b] px-2 py-2 text-xs text-center" style={{ width: '14%' }}>
                                                                        Orta Değer
                                                                    </th>
                                                                    {[1, 2, 3, 4, 5].map((pos) => (
                                                                        <th key={pos} className="border border-[#00482b] px-2 py-2 text-xs text-center" style={{ width: '5%' }}>
                                                                            <br />{pos}.
                                                                        </th>
                                                                    ))}
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {placeGroup.measurements.map((measurement, measIndex) => (
                                                                    <tr key={measIndex} className="bg-[#002315] text-white">
                                                                        <td className="border border-[#00482b] px-2 py-2 text-xs text-left">
                                                                            {measurement.part_label}
                                                                        </td>
                                                                        <td className="border border-[#00482b] px-2 py-2 text-xs text-left">
                                                                            {measurement.substrate_type}
                                                                        </td>
                                                                        <td className="border border-[#00482b] px-2 py-2 text-xs text-center">
                                                                            {Math.round(measurement.min_value).toLocaleString('tr-TR')}
                                                                        </td>
                                                                        <td className="border border-[#00482b] px-2 py-2 text-xs text-center">
                                                                            {Math.round(measurement.max_value).toLocaleString('tr-TR')}
                                                                        </td>
                                                                        <td className="border border-[#00482b] px-2 py-2 text-xs text-center">
                                                                            {measurement.avg_value.toFixed(1)}
                                                                        </td>
                                                                        {[1, 2, 3, 4, 5].map((pos) => {
                                                                            const positionValue = measurement.positions[pos as keyof MeasurementPosition];
                                                                            return (
                                                                                <td
                                                                                    key={pos}
                                                                                    className={`border border-[#00482b] px-2 py-2 text-xs text-center ${positionValue === null ? 'text-gray-500' : ''
                                                                                        }`}
                                                                                >
                                                                                    {positionValue !== null
                                                                                        ? Math.round(parseFloat(positionValue)).toLocaleString('tr-TR')
                                                                                        : '-'}
                                                                                </td>
                                                                            );
                                                                        })}
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </>
                        ) : (
                            <div className="text-center text-gray-500 py-8">
                                Ölçüm sonucu bulunmamaktadır.
                            </div>
                        )}
                    </div>
                    <DialogFooter>
                        <Button variant="destructive" onClick={() => setMeasurementsVisible(false)}>Kapat</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
};

export default WarrantyIndex;

