import React, { useState, useEffect, useMemo } from 'react';
import { motion } from 'framer-motion';
import { Head, usePage } from "@inertiajs/react";
import { Bell, UserPen, Trash2, X } from 'lucide-react';
import CustomerPageTop from '@/components/CustomerPageTop';
import VehicleCard from '@/components/VehicleCard';
import ProductServiceStatusWidget from '@/components/ProductServiceStatusWidget';
import { useToast } from '@/components/ui/toast';
import { Drawer, DrawerContent, DrawerHeader, DrawerFooter, DrawerTitle, DrawerDescription } from '@/components/ui/drawer';
import { Input } from '@/components/ui/input';
import { PasswordInput } from '@/components/ui/password-input';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

declare global {
    function route(name: string, params?: any): string;
}

interface NotificationSetting {
    key: string;
    value: boolean;
}

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string;
    notification_settings: string;
}

interface Service {
    car: {
        brand: string;
        model: string;
        generation: string;
        year: number;
        plate: string;
        brand_logo: string;
    };
    created_at: string;
    service_no: string;
}

interface Product {
    product: {
        name: string;
    };
    product_code: string;
    warranty: {
        start_date: string;
        end_date: string;
        rate: number;
    };
    car_plate: string;
}

interface NewDesignProps {
    customerB: Customer;
    csrf_token: string;
    hash: string;
    services: Service[];
    products: Product[];
}

const NewDesign: React.FC<NewDesignProps> = ({ customerB, csrf_token, hash, services, products }) => {
    const page = usePage();
    const toast = useToast();

    const [customer, setCustomer] = useState<Customer>(customerB);
    const [notifications, setNotifications] = useState<NotificationSetting[]>(
        Object.entries(customer.notification_settings || {}).map(([key, value]) => ({
            key,
            value: value as boolean
        })).filter(n => n.key !== 'push') // Push notification'ı kaldır
    );
    const [editSidebarVisible, setEditSidebarVisible] = useState(false);
    const [deleteSidebarVisible, setDeleteSidebarVisible] = useState(false);
    const [notificationSidebarVisible, setNotificationSidebarVisible] = useState(false);
    const [deletePassword, setDeletePassword] = useState('');
    const [loading, setLoading] = useState(false);

    const [contact, setContact] = useState<string[]>(
        Object.entries(customer.notification_settings || {})
            .map(([key, value]) => {
                if (value && key !== 'push') { // Push notification'ı hariç tut
                    return key;
                }
                return null;
            })
            .filter((item): item is string => item !== null)
    );

    const [unSaved, setUnSaved] = useState(false);

    useEffect(() => {
        const oldContact = Object.entries(customer.notification_settings || {})
            .map(([key, value]) => {
                if (value && key !== 'push') {
                    return key;
                }
                return null;
            })
            .filter((item): item is string => item !== null);

        const sortedOldContact = [...oldContact].sort();
        const sortedContact = [...contact].sort();

        if (JSON.stringify(sortedOldContact) !== JSON.stringify(sortedContact)) {
            setUnSaved(true);
        } else {
            setUnSaved(false);
        }
    }, [contact, customer.notification_settings]);

    const toggleNotification = (key: string) => {
        setNotifications(notifications.map(notification =>
            notification.key === key
                ? { ...notification, value: !notification.value }
                : notification
        ));

        // Contact state'ini güncelle
        if (contact.includes(key)) {
            setContact(contact.filter(item => item !== key));
        } else {
            setContact([...contact, key]);
        }
    };

    const saveNotificationSettings = () => {
        setLoading(true);
        const defaultSettings: Record<string, boolean> = {
            sms: false,
            email: false,
        };

        contact.forEach(key => {
            if (key in defaultSettings) {
                defaultSettings[key] = true;
            }
        });

        const formData = new FormData();
        formData.append('settings', JSON.stringify(contact));
        formData.append('action', 'settings');

        fetch(('customer.notifyUpdate', hash), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf_token
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    toast.show({
                        severity: 'success',
                        summary: 'Başarılı',
                        detail: data.message,
                        life: 3000
                    });
                    setCustomer(data.customer);
                    setNotificationSidebarVisible(false);
                    setUnSaved(false);
                } else {
                    toast.show({
                        severity: 'error',
                        summary: 'Hata',
                        detail: data.message
                    });
                }
            })
            .catch((error) => {
                toast.show({
                    severity: 'error',
                    summary: 'Hata',
                    detail: "CSRF Token Hatası Lütfen Sayfayı Yenileyiniz.."
                });
            })
            .finally(() => {
                setLoading(false);
            });
    };

    const saveProfile = () => {
        // Profil kaydetme işlemi burada yapılacak
        setEditSidebarVisible(false);
        toast.show({
            severity: 'info',
            summary: 'Bilgi',
            detail: 'Profil kaydetme özelliği yakında eklenecek',
            life: 3000
        });
    };

    const deleteAccount = () => {
        if (!deletePassword) {
            toast.show({
                severity: 'error',
                summary: 'Hata',
                detail: 'Lütfen şifrenizi girin',
                life: 3000
            });
            return;
        }

        // Hesap silme işlemi burada yapılacak
        setDeleteSidebarVisible(false);
        toast.show({
            severity: 'info',
            summary: 'Bilgi',
            detail: 'Hesap silme özelliği yakında eklenecek',
            life: 3000
        });
    };

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.3 }}
            className="min-h-screen bg-gradient-to-b font-avaganti from-[#005d37] via-30% via-[#121f1c] to-[#000] pb-4"
        >
            <Head title="Müşteri Bilgileri" />
            <CustomerPageTop customerName={customer.name} />

            <div className="max-w-4xl mx-auto px-4 py-8">
                <div>
                    <div className='flex justify-center text-lg font-semibold text-white'>
                        Hizmetler
                    </div>
                    <div className='my-2'>
                        {services.map((service, index) => (
                            <VehicleCard
                                key={index}
                                serieName={service.car.brand + " " + service.car.model}
                                modelName={service.car.generation}
                                modelYear={service.car.year}
                                carPlate={service.car.plate}
                                serviceDayMonth={("0" + new Date(service.created_at).getDate()).slice(-2) + "." + ("0" + (new Date(service.created_at).getMonth() + 1)).slice(-2)}
                                serviceYear={new Date(service.created_at).getFullYear()}
                                serviceUrl={("warranty.index", service.service_no)}
                                brandLogo={service.car.brand_logo}
                            />
                        ))}
                    </div>
                </div>
                <div className='flex justify-center text-lg font-semibold text-white'>
                    Garanti Süreleri
                </div>
                <div className='mt-2 mb-2'>
                    {products.map((product, index) => (
                        <ProductServiceStatusWidget
                            key={index}
                            productName={product.product.name}
                            productCode={product.product_code}
                            startDate={product.warranty.start_date}
                            endDate={product.warranty.end_date}
                            progress={product.warranty.rate}
                            carPlate={product.car_plate}
                        />
                    ))}
                </div>
                <div className="max-w-4xl mx-auto flex flex-row gap-2 justify-between">
                    <button
                        onClick={() => setNotificationSidebarVisible(true)}
                        className="flex-1 group relative overflow-hidden bg-gradient-to-br from-green-600/20 to-green-900/40 hover:from-green-500/30 hover:to-green-800/50 backdrop-blur-sm border border-green-900/30 text-white p-3 md:p-4 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-[1.02]"
                    >
                        <div className="absolute inset-0 flex items-center justify-center">
                            <div className="w-24 h-24 bg-green-500/20 rounded-full blur-2xl transform group-hover:scale-150 transition-transform duration-500"></div>
                        </div>
                        <div className="relative flex items-center gap-2">
                            <Bell className="text-xl md:text-2xl text-green-400" />
                            <span className="md:inline">İletişim Seçenekleri</span>
                        </div>
                    </button>

                    <button
                        onClick={() => setEditSidebarVisible(true)}
                        className="flex-1 group relative overflow-hidden hidden bg-gradient-to-br from-blue-600/20 to-blue-900/40 hover:from-blue-500/30 hover:to-blue-800/50 backdrop-blur-sm border border-blue-900/30 text-white p-3 md:p-4 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-[1.02]"
                    >
                        <div className="absolute inset-0 flex items-center justify-center">
                            <div className="w-24 h-24 bg-blue-500/20 rounded-full blur-2xl transform group-hover:scale-150 transition-transform duration-500"></div>
                        </div>
                        <div className="relative flex items-center gap-2">
                            <UserPen className="text-xl md:text-2xl text-blue-400" />
                            <span className="hidden md:inline">Düzenle</span>
                        </div>
                    </button>
                </div>
            </div>

            {/* İletişim Tercihleri Drawer */}
            <Drawer open={notificationSidebarVisible} onOpenChange={setNotificationSidebarVisible}>
                <DrawerContent side="right" className="w-full md:w-[450px]">
                    <DrawerHeader>
                        <DrawerTitle>İletişim Tercihleri</DrawerTitle>
                    </DrawerHeader>
                    <div className="flex-grow overflow-y-auto px-6">
                        <div className="space-y-4">
                            {notifications.map((notification) => (
                                <div key={notification.key} className="bg-[#002200]/50 p-4 rounded-lg border border-green-900/20">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h3 className="text-white font-medium capitalize">
                                                {notification.key === 'email' ? 'E-posta Bildirimleri' :
                                                    'SMS Bildirimleri'}
                                            </h3>
                                            <p className="text-gray-400 text-sm mt-1">
                                                {notification.key === 'email' ? 'Önemli güncellemeler ve teklifler için' :
                                                    'Acil durumlar ve önemli hatırlatmalar'}
                                            </p>
                                        </div>
                                        <button
                                            onClick={() => toggleNotification(notification.key)}
                                            className={cn(
                                                "relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none",
                                                notification.value ? 'bg-green-600' : 'bg-gray-700'
                                            )}
                                        >
                                            <span
                                                className={cn(
                                                    "inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out",
                                                    notification.value ? 'translate-x-6' : 'translate-x-1'
                                                )}
                                            />
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                        {unSaved && (
                            <div className="mt-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-3">
                                <p className="text-yellow-400 text-sm">
                                    Kaydedilmemiş değişiklikler var
                                </p>
                            </div>
                        )}
                    </div>
                    <DrawerFooter>
                        <Button
                            onClick={() => setNotificationSidebarVisible(false)}
                            variant="outline"
                            className="flex-1 bg-[#002200]/50 hover:bg-[#003300]/50 text-white border-green-900/30"
                        >
                            Kapat
                        </Button>
                        <Button
                            onClick={saveNotificationSettings}
                            disabled={loading}
                            className="flex-1 bg-green-600/20 hover:bg-green-600/30 text-green-400"
                        >
                            {loading ? 'Kaydediliyor...' : 'Kaydet'}
                        </Button>
                    </DrawerFooter>
                </DrawerContent>
            </Drawer>

            {/* Profil Düzenleme Drawer */}
            <Drawer open={editSidebarVisible} onOpenChange={setEditSidebarVisible}>
                <DrawerContent side="right" className="w-full md:w-[450px]">
                    <DrawerHeader>
                        <DrawerTitle>Profil Düzenle</DrawerTitle>
                    </DrawerHeader>
                    <div className="flex-grow overflow-y-auto px-6">
                        <div className="space-y-4">
                            <div className="flex flex-col">
                                <label className="text-gray-400 mb-2">Ad Soyad</label>
                                <Input
                                    value={customer.name}
                                    className="bg-[#002200]/50 border-green-900/30 text-white"
                                    readOnly
                                />
                            </div>
                            <div className="flex flex-col">
                                <label className="text-gray-400 mb-2">E-posta</label>
                                <Input
                                    value={customer.email}
                                    className="bg-[#002200]/50 border-green-900/30 text-white"
                                    readOnly
                                />
                            </div>
                            <div className="flex flex-col">
                                <label className="text-gray-400 mb-2">Telefon</label>
                                <Input
                                    value={customer.phone}
                                    className="bg-[#002200]/50 border-green-900/30 text-white"
                                    readOnly
                                />
                            </div>

                            <div className="mt-8 pt-4 border-t border-green-900/30">
                                <button
                                    onClick={() => setDeleteSidebarVisible(true)}
                                    className="w-full bg-red-900/20 hover:bg-red-900/30 text-red-400 p-3 rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
                                >
                                    <Trash2 className="w-4 h-4" />
                                    <span>Hesabı Sil</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <DrawerFooter>
                        <Button
                            onClick={() => setEditSidebarVisible(false)}
                            variant="outline"
                            className="flex-1 bg-[#002200]/50 hover:bg-[#003300]/50 text-white border-green-900/30"
                        >
                            Kapat
                        </Button>
                        <Button
                            onClick={saveProfile}
                            className="flex-1 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400"
                        >
                            Kaydet
                        </Button>
                    </DrawerFooter>
                </DrawerContent>
            </Drawer>

            {/* Hesap Silme Drawer */}
            <Drawer open={deleteSidebarVisible} onOpenChange={setDeleteSidebarVisible}>
                <DrawerContent side="right" className="w-full md:w-[450px]">
                    <DrawerHeader>
                        <DrawerTitle>Hesabı Sil</DrawerTitle>
                    </DrawerHeader>
                    <div className="flex-grow overflow-y-auto px-6">
                        <div className="bg-red-500/10 border border-red-500/30 rounded-lg p-4 mb-6">
                            <p className="text-red-400 flex items-center gap-2">
                                <X className="w-4 h-4" />
                                Dikkat! Bu işlem geri alınamaz. Hesabınızı sildiğinizde:
                            </p>
                            <ul className="list-disc list-inside text-red-400 mt-2 space-y-1">
                                <li>Tüm hizmetleriniz silinecek</li>
                                <li>Garanti kayıtlarınız silinecek</li>
                                <li>İletişim tercihleriniz silinecek</li>
                            </ul>
                        </div>
                        <div className="space-y-4">
                            <div className="flex flex-col">
                                <label className="text-gray-400 mb-2">Şifrenizi Girin</label>
                                <PasswordInput
                                    value={deletePassword}
                                    onChange={(e) => setDeletePassword(e.target.value)}
                                    className="bg-[#002200]/50 border-green-900/30 text-white"
                                />
                            </div>
                        </div>
                    </div>
                    <DrawerFooter>
                        <Button
                            onClick={() => setDeleteSidebarVisible(false)}
                            variant="outline"
                            className="flex-1 bg-[#002200]/50 hover:bg-[#003300]/50 text-white border-green-900/30"
                        >
                            Vazgeç
                        </Button>
                        <Button
                            onClick={deleteAccount}
                            disabled={!deletePassword}
                            className="flex-1 bg-red-900/20 hover:bg-red-900/30 text-red-400 disabled:opacity-50"
                        >
                            Hesabı Sil
                        </Button>
                    </DrawerFooter>
                </DrawerContent>
            </Drawer>
        </motion.div>
    );
};

export default NewDesign;

