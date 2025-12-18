import React from 'react';
import { motion } from 'framer-motion';
import carImage from '../assets/porsche_car.png';

interface CustomerPageTopProps {
    customerName: string;
}

const CustomerPageTop: React.FC<CustomerPageTopProps> = ({ customerName }) => {
    return (
        <div className="relative h-[400px] md:h-[450px] lg:h-[500px] overflow-hidden">
            {/* Gradient Background */}
            <div className="absolute inset-0" />

            {/* Overlay Pattern */}
            <div
                className="absolute inset-0 opacity-10"
                style={{
                    backgroundImage: 'radial-gradient(circle at 2px 2px, rgba(255,255,255,0.15) 1px, transparent 0)',
                    backgroundSize: '32px 32px'
                }}
            />

            {/* Content Container */}
            <div className="relative max-w-4xl mx-auto px-4 h-full">
                {/* Logo */}
                <motion.img
                    initial={{ opacity: 0, y: -20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.5 }}
                    src="/images/olex-logo-yatay.svg"
                    alt="OLEX®"
                    className="w-40  md:pt-8 lg:w-64 md:w-48"
                />

                {/* Welcome Text */}
                <motion.div
                    initial={{ opacity: 0, x: -30 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ duration: 0.5, delay: 0.2 }}
                    className="absolute top-1/3 transform -translate-y-1/3"
                >
                    <h1 className="text-white text-4xl font-light mb-2">
                        Merhaba,
                    </h1>
                    <h2 className="text-white text-6xl font-bold tracking-wide tracking-tighter">
                        {customerName}
                    </h2>
                </motion.div>

                {/* Car Image */}
                <motion.div
                    initial={{ opacity: 0, x: 100 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ duration: 0.8, delay: 0.3 }}
                    className="absolute bottom-0 right-0 flex justify-end"
                >
                    <img
                        src={carImage}
                        alt="OLEX Vehicle"
                        className="h-[230px] lg:h-[430px] md:h-[360px]"
                        style={{
                            filter: 'drop-shadow(0 4px 20px rgba(0,0,0,0.3))'
                        }}
                    />
                </motion.div>
            </div>
        </div>
    );
};

export default CustomerPageTop;

