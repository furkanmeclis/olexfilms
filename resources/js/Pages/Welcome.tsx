import { Link, Head, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import { useToast } from '@/components/ui/toast';
import { applyPhoneMask, removePhoneMask } from '@/lib/phoneMask';
import OtpInput from 'react-otp-input';
import { route } from 'ziggy';

export default function Welcome({ auth, csrf_token }: { auth: any; csrf_token: string }) {
    const toast = useToast();
    const [phone, setPhone] = useState('');
    const [isOpen, setIsOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [isSended, setIsSended] = useState(false);
    const [otp, setOtp] = useState('');
    const [customerId, setCustomerId] = useState('');
    const [resend, setResend] = useState(false);

    const resetStatus = () => {
        setTimeout(() => {
            setResend(true);
        }, 1000 * 10);
        setTimeout(() => {
            setIsSended(false);
            setOtp('');
            setCustomerId('');
        }, 1000 * 60 * 5);
    };

    const otpLogin = (event: React.MouseEvent, custom = false) => {
        event.preventDefault();
        setResend(false);
        setLoading(true);
        let formData = new FormData();
        formData.append('phone', removePhoneMask(phone));
        fetch("/api/customer/otp-login", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf_token,
            },
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status) {
                    toast.show({
                        severity: 'success',
                        summary: 'Başarılı',
                        detail: data.message,
                        life: 3000,
                    });
                    if (!custom) {
                        resetStatus();
                    }
                    setCustomerId(data.customer_id);
                    setIsSended(true);
                } else {
                    toast.show({
                        severity: 'error',
                        summary: 'Hata',
                        detail: data.message,
                        life: 3000,
                    });
                }
            })
            .catch((error) => {
                toast.show({
                    severity: 'error',
                    summary: 'Hata',
                    detail: 'CSRF Token Hatası Lütfen Sayfayı Yenileyiniz..',
                });
            })
            .finally(() => {
                setLoading(false);
            });
    };

    const otpVerify = () => {
        setLoading(true);
        let formData = new FormData();
        formData.append('customer_id', customerId);
        formData.append('otp', otp);
        fetch(('customer-otp-verify'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf_token,
            },
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status) {
                    router.visit(('customer.notify', data.hash));
                } else {
                    toast.show({
                        severity: 'error',
                        summary: 'Hata',
                        detail: data.message,
                        life: 3000,
                    });
                }
            })
            .catch((error) => {
                toast.show({
                    severity: 'error',
                    summary: 'Hata',
                    detail: 'CSRF Token Hatası Lütfen Sayfayı Yenileyiniz..',
                });
            })
            .finally(() => {
                setLoading(false);
            });
    };

    const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        const masked = applyPhoneMask(value);
        setPhone(masked);
    };

    return (
        <>
            <Head title="Hoş Geldiniz">
                <link
                    rel="stylesheet"
                    href="https://cdn.jsdelivr.net/npm/primeicons@latest/primeicons.css"
                />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-[#001a00] via-[#002200] to-[#000a00]">
                {/* Header */}
                <header className="fixed top-0 left-0 right-0 z-50 bg-[#001100]/90 backdrop-blur-lg border-b border-green-900/50">
                    <nav className="container mx-auto px-6 py-4">
                        <div className="flex items-center justify-between">
                            <motion.div
                                initial={{ opacity: 0, x: -20 }}
                                animate={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.5 }}
                            >
                                <img
                                    src="/images/olex-logo-yatay.svg"
                                    alt="Olex Films Logo"
                                    className="h-8 md:h-10"
                                />
                            </motion.div>

                            <motion.div
                                initial={{ opacity: 0, x: 20 }}
                                animate={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.5 }}
                                className="flex items-center gap-4"
                            >
                                {auth.user ? (
                                    <a
                                        href={"/admin"}
                                        className="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-500 transition-all duration-300"
                                    >
                                        <i className="pi pi-home mr-2" /> Yönetim Paneli
                                    </a>
                                ) : (
                                    <>
                                        <a
                                            href={route('filament.admin.auth.login')}
                                            className="relative inline-flex items-center justify-center px-6 py-2 text-base font-medium text-white transition-all duration-300 ease-in-out bg-gradient-to-r from-[#1a1a1a] to-[#333333] rounded-lg hover:from-[#333333] hover:to-[#1a1a1a] border border-[#E6B800]/30 hover:border-[#E6B800] shadow-lg hover:shadow-[#E6B800]/20 group"
                                        >
                                            <span className="relative flex items-center">
                                                <i className="pi pi-lock mr-2 text-[#E6B800] group-hover:animate-pulse" />
                                                Yetkili Girişi
                                            </span>
                                        </a>
                                    </>
                                )}
                            </motion.div>
                        </div>
                    </nav>
                </header>

                {/* OTP Modal */}
                {isOpen && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/50 backdrop-blur-sm"
                    >
                        <motion.div
                            initial={{ scale: 0.9, opacity: 0 }}
                            animate={{ scale: 1, opacity: 1 }}
                            exit={{ scale: 0.9, opacity: 0 }}
                            className="bg-[#001100] border border-green-900/50 rounded-2xl p-8 w-full max-w-md relative"
                        >
                            <button
                                onClick={() => {
                                    setIsOpen(false);
                                    setResend(false);
                                    setOtp('');
                                    setCustomerId('');
                                    setIsSended(false);
                                    setPhone('');
                                }}
                                className="absolute top-4 right-4 text-green-300 hover:text-white transition-colors"
                            >
                                <i className="pi pi-times text-xl" />
                            </button>

                            <div className="text-center mb-8">
                                <h3 className="text-2xl font-bold text-white mb-2">
                                    {isSended ? 'Doğrulama Kodu' : 'Müşteri Girişi'}
                                </h3>
                                <p className="text-green-300/80">
                                    {isSended
                                        ? 'Lütfen telefonunuza gönderilen kodu giriniz'
                                        : 'Telefon numaranız ile giriş yapın'}
                                </p>
                            </div>

                            {!isSended ? (
                                <div className="space-y-4">
                                    <div className="relative">
                                        <input
                                            type="tel"
                                            value={phone}
                                            onChange={handlePhoneChange}
                                            placeholder="Telefon Numarası"
                                            className="w-full bg-[#002200] border border-green-900 text-white rounded-lg px-4 py-3 pr-12 outline-none focus:ring-2 focus:ring-green-500 transition-all duration-300 placeholder-green-500/50"
                                        />
                                        <i className="pi pi-phone absolute right-4 top-1/2 -translate-y-1/2 text-green-500" />
                                    </div>
                                    <button
                                        onClick={(e) => otpLogin(e)}
                                        disabled={loading || removePhoneMask(phone).length !== 11}
                                        className="w-full bg-green-600 text-white rounded-lg py-3 font-medium hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-[#001100] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {loading ? (
                                            <>
                                                <i className="pi pi-spinner animate-spin mr-2" />
                                                Gönderiliyor...
                                            </>
                                        ) : (
                                            <>
                                                <i className="pi pi-sign-in mr-2" />
                                                Giriş Yap
                                            </>
                                        )}
                                    </button>
                                </div>
                            ) : (
                                <div className="space-y-6">
                                    <div className="flex justify-center">
                                        <OtpInput
                                            value={otp}
                                            onChange={setOtp}
                                            numInputs={6}
                                            inputStyle={{
                                                width: '2em',
                                            }}
                                            inputType="tel"
                                            renderInput={(props) => (
                                                <input
                                                    {...props}
                                                    className="w-12 h-12 text-center text-lg font-medium bg-[#002200] border border-green-900 text-[#E6B800] rounded-lg mx-1 focus:ring-2 focus:ring-[#E6B800] focus:border-[#E6B800] outline-none placeholder-[#E6B800]/50"
                                                />
                                            )}
                                        />
                                    </div>

                                    <div className="space-y-4">
                                        <button
                                            onClick={otpVerify}
                                            disabled={loading || otp.length !== 6}
                                            className="w-full bg-green-600 text-white rounded-lg py-3 font-medium hover:bg-green-500 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-[#001100] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            {loading ? (
                                                <>
                                                    <i className="pi pi-spinner animate-spin mr-2" />
                                                    Doğrulanıyor...
                                                </>
                                            ) : (
                                                <>
                                                    <i className="pi pi-check mr-2" />
                                                    Doğrula
                                                </>
                                            )}
                                        </button>

                                        {resend && (
                                            <button
                                                onClick={(e) => otpLogin(e, true)}
                                                className="w-full bg-transparent border border-green-600 text-green-400 rounded-lg py-3 font-medium hover:bg-green-900/30 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-[#001100] transition-all duration-300"
                                            >
                                                <i className="pi pi-refresh mr-2" />
                                                Tekrar Gönder
                                            </button>
                                        )}
                                    </div>
                                </div>
                            )}
                        </motion.div>
                    </motion.div>
                )}

                {/* Hero Section */}
                <section className="pt-32 pb-20 px-6">
                    <div className="container mx-auto">
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.6 }}
                            className="text-center max-w-3xl mx-auto"
                        >
                            <h1 className="text-4xl md:text-6xl font-bold text-white mb-6">
                                Aracınız İçin Profesyonel
                                <span className="bg-gradient-to-r from-[#E6B800] via-[#FFD700] to-[#E6B800] bg-clip-text text-transparent">
                                    {' '}
                                    Koruma Çözümleri
                                </span>
                            </h1>
                            <p className="text-green-300 text-lg md:text-xl mb-12">
                                Olex Films ile aracınızı en iyi şekilde koruyun. PPF ve cam filmi hizmetlerimizle tanışın.
                            </p>
                            <motion.div
                                className="flex flex-col sm:flex-row gap-6 justify-center items-center"
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.3 }}
                            >
                                <button
                                    onClick={() => setIsOpen(true)}
                                    className="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white transition-all duration-300 ease-in-out bg-gradient-to-r from-green-600 via-green-500 to-green-600 rounded-xl hover:from-green-500 hover:via-green-400 hover:to-green-500 shadow-lg hover:shadow-green-500/50 scale-100 hover:scale-105"
                                >
                                    <span className="relative">
                                        <i className="pi pi-user mr-2" />
                                        Müşteri Girişi
                                    </span>
                                    <span className="absolute -top-2 -right-2 w-4 h-4 bg-[#E6B800] rounded-full animate-ping"></span>
                                </button>
                            </motion.div>
                        </motion.div>
                    </div>
                </section>

                {/* Contact Section */}
                <section id="contact" className="py-20 px-6">
                    <div className="container mx-auto">
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.6 }}
                            viewport={{ once: true }}
                            className="max-w-xl mx-auto text-center"
                        >
                            <h2 className="text-3xl md:text-4xl font-bold text-white mb-4">İletişime Geçin</h2>
                            <p className="text-green-300 mb-12">
                                Profesyonel hizmetlerimiz hakkında detaylı bilgi almak için bize ulaşın.
                            </p>
                            <div className="bg-[#001100]/50 backdrop-blur-sm rounded-2xl p-8 border border-green-900/50">
                                <div className="space-y-8">
                                    <div className="space-y-6">
                                        <h3 className="text-xl font-semibold text-white mb-4">Telefon Numaraları</h3>
                                        <div className="flex items-center gap-4 text-green-300 justify-center">
                                            <i className="pi pi-phone text-xl"></i>
                                            <span>TR: +90 (507) 465 34 34</span>
                                        </div>
                                        <div className="flex items-center gap-4 text-green-300 justify-center">
                                            <i className="pi pi-phone text-xl"></i>
                                            <span>US: +1 (410) 844 5381</span>
                                        </div>
                                    </div>

                                    <div className="space-y-6">
                                        <h3 className="text-xl font-semibold text-white mb-4">Mail Adresleri</h3>
                                        <div className="flex items-center gap-4 text-green-300 justify-center">
                                            <i className="pi pi-envelope text-xl"></i>
                                            <span>info@olexfillms.com</span>
                                        </div>
                                        <div className="flex items-center gap-4 text-green-300 justify-center">
                                            <i className="pi pi-envelope text-xl"></i>
                                            <span>bayilik@olexfilms.com</span>
                                        </div>
                                    </div>

                                    <div className="space-y-6">
                                        <h3 className="text-xl font-semibold text-white mb-4">Adres</h3>
                                        <div className="flex items-center gap-4 text-green-300 justify-center">
                                            <i className="pi pi-map-marker text-xl"></i>
                                            <span>Şenlikköy Mh. Florya Cd. No:14 Bakırköy / İstanbul</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </motion.div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="bg-[#000a00] border-t border-green-900/50 py-8">
                    <div className="container mx-auto px-6">
                        <div className="flex flex-col md:flex-row justify-between items-center">
                            <motion.div
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                transition={{ delay: 0.5 }}
                            >
                                <img
                                    src="/images/olex-logo-yatay.svg"
                                    alt="Olex Films Logo"
                                    className="h-8"
                                />
                            </motion.div>
                            <div className="mt-4 md:mt-0 text-green-300 text-sm">
                                &copy; {new Date().getFullYear()} Olex Films. Tüm hakları saklıdır.
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
