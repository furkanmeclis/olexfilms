import React from 'react';
import { motion } from 'framer-motion';
import { cn } from '@/lib/utils';
import { Skeleton } from '@/components/ui/skeleton';
import checkSvg from '../assets/kontrol-randevusu.svg';

interface VehicleCardProps {
    brandLogo?: string;
    serieName: string;
    modelName: string;
    modelYear: number;
    carPlate: string;
    serviceDayMonth: string;
    serviceYear: number;
    serviceUrl: string;
    isSkeleton?: boolean;
}

const VehicleCard: React.FC<VehicleCardProps> = ({
    brandLogo = "https://www.carlogos.org/car-logos/bmw-logo-2020-blue-white.png",
    serieName,
    modelName,
    modelYear,
    carPlate,
    serviceDayMonth,
    serviceYear,
    serviceUrl,
    isSkeleton = false
}) => {
    if (isSkeleton) {
        return (
            <div className={cn('bg-gradient-to-b from-[#2e2f31] to-[#1a1b1d] rounded-xl border border-[#373739] overflow-hidden mb-2')}>
                <div className="flex items-center justify-between p-4">
                    <div className="flex items-center space-x-4">
                        {/* Brand Logo */}
                        <div className="w-12 h-12 flex-shrink-0">
                            <Skeleton className="w-full h-full rounded" />
                        </div>

                        {/* Vehicle Info */}
                        <div>
                            <div className="flex items-baseline">
                                <Skeleton className="mb-2 w-40 h-5" />
                            </div>
                            <div className="flex items-center space-x-1">
                                <Skeleton className="w-8 h-4" />
                                <span className="text-gray-400">|</span>
                                <Skeleton className="w-8 h-4" />
                                <span className="text-gray-400">|</span>
                                <Skeleton className="w-16 h-4" />
                            </div>
                        </div>
                    </div>
                    {/* Service Date & Button */}
                    <div className="flex items-center space-x-3">
                        <span className="h-10 border border-green-900/30"></span>
                        <div className="text-right">
                            <div className="text-gray-400 text-sm mb-1">
                                <Skeleton className="w-8 h-4" />
                            </div>
                            <div className="text-gray-400 font-medium text-sm">
                                <Skeleton className="w-8 h-4" />
                            </div>
                        </div>
                        <div className="bg-green-800/30 w-10 h-10 rounded-lg border border-green-700/30 flex justify-center items-center">
                            <Skeleton className="w-5 h-5" />
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <a href={serviceUrl} target="_blank" rel="noopener noreferrer">
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                whileHover={{ scale: 1.02 }}
                transition={{ duration: 0.3 }}
                className="bg-gradient-to-b from-[#2e2f31] cursor-pointer to-[#1a1b1d] rounded-xl border border-[#373739] overflow-hidden mb-2"
            >
                <div className="flex items-center justify-between p-4">
                    <div className="flex items-center space-x-4">
                        {/* Brand Logo */}
                        <div className="w-12 h-12 flex-shrink-0">
                            <img
                                src={brandLogo}
                                alt="Brand Logo"
                                className="w-full h-full object-contain"
                            />
                        </div>

                        {/* Vehicle Info */}
                        <div>
                            <div className="flex items-baseline">
                                <h3 className="text-white font-semibold">{serieName}</h3>
                            </div>
                            <div className="flex items-center space-x-1">
                                <span className="text-gray-400 text-xs">{modelYear}</span>
                                <span className="text-gray-400">|</span>
                                <span className="text-gray-400 text-xs">{modelName}</span>
                                <span className="text-gray-400">|</span>
                                <span className="text-gray-200 font-medium text-xs">{carPlate}</span>
                            </div>
                        </div>
                    </div>
                    {/* Service Date & Button */}
                    <div className="flex items-center space-x-3">
                        <span className="h-10 border border-green-900/30"></span>
                        <div className="text-right">
                            <div className="text-gray-400 text-sm">{serviceDayMonth}</div>
                            <div className="text-gray-400 font-medium text-sm">
                                {serviceYear}
                            </div>
                        </div>
                    </div>
                </div>
            </motion.div>
        </a>
    );
};

export default VehicleCard;

