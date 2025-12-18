import { createContext, useContext, useState, useCallback, ReactNode } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

type ToastType = 'success' | 'error' | 'info' | 'warning';

interface Toast {
    id: string;
    type: ToastType;
    title: string;
    message: string;
    duration?: number;
}

interface ToastContextType {
    show: (toast: Omit<Toast, 'id'> | { severity: ToastType; summary: string; detail: string; life?: number }) => void;
}

const ToastContext = createContext<ToastContextType | undefined>(undefined);

export const useToast = () => {
    const context = useContext(ToastContext);
    if (!context) {
        throw new Error('useToast must be used within ToastProvider');
    }
    return context;
};

export const ToastProvider = ({ children }: { children: ReactNode }) => {
    const [toasts, setToasts] = useState<Toast[]>([]);

    const show = useCallback((toast: Omit<Toast, 'id'> | { severity: ToastType; summary: string; detail: string; life?: number }) => {
        const id = Math.random().toString(36).substring(7);
        const duration = 'life' in toast ? toast.life : ('duration' in toast ? toast.duration : 3000);
        const newToast: Toast = {
            id,
            type: 'severity' in toast ? toast.severity : toast.type,
            title: 'summary' in toast ? toast.summary : toast.title,
            message: 'detail' in toast ? toast.detail : toast.message,
            duration: duration || 3000,
        };

        setToasts((prev) => [...prev, newToast]);

        if (newToast.duration) {
            setTimeout(() => {
                setToasts((prev) => prev.filter((t) => t.id !== id));
            }, newToast.duration);
        }
    }, []);

    const remove = useCallback((id: string) => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
    }, []);

    const getToastStyles = (type: ToastType) => {
        switch (type) {
            case 'success':
                return 'bg-green-600 border-green-500';
            case 'error':
                return 'bg-red-600 border-red-500';
            case 'warning':
                return 'bg-yellow-600 border-yellow-500';
            case 'info':
                return 'bg-blue-600 border-blue-500';
            default:
                return 'bg-gray-600 border-gray-500';
        }
    };

    const getIcon = (type: ToastType) => {
        switch (type) {
            case 'success':
                return 'pi pi-check-circle';
            case 'error':
                return 'pi pi-times-circle';
            case 'warning':
                return 'pi pi-exclamation-triangle';
            case 'info':
                return 'pi pi-info-circle';
            default:
                return 'pi pi-bell';
        }
    };

    return (
        <ToastContext.Provider value={{ show }}>
            {children}
            <div className="fixed top-4 right-4 z-[9999] flex flex-col gap-2">
                <AnimatePresence>
                    {toasts.map((toast) => (
                        <motion.div
                            key={toast.id}
                            initial={{ opacity: 0, x: 100, scale: 0.9 }}
                            animate={{ opacity: 1, x: 0, scale: 1 }}
                            exit={{ opacity: 0, x: 100, scale: 0.9 }}
                            className={`${getToastStyles(toast.type)} border rounded-lg shadow-lg p-4 min-w-[300px] max-w-[400px] text-white`}
                        >
                            <div className="flex items-start gap-3">
                                <i className={`${getIcon(toast.type)} text-xl mt-0.5`} />
                                <div className="flex-1">
                                    <div className="font-semibold text-sm mb-1">{toast.title}</div>
                                    <div className="text-sm opacity-90">{toast.message}</div>
                                </div>
                                <button
                                    onClick={() => remove(toast.id)}
                                    className="text-white/80 hover:text-white transition-colors"
                                >
                                    <i className="pi pi-times text-sm" />
                                </button>
                            </div>
                        </motion.div>
                    ))}
                </AnimatePresence>
            </div>
        </ToastContext.Provider>
    );
};

