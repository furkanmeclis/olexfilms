import React, { useState, useEffect } from 'react';

const CarBody = ({ availableParts = [], selectedParts = [], onChange }) => {
    const [selected, setSelected] = useState(selectedParts || []);

    useEffect(() => {
        setSelected(selectedParts || []);
    }, [selectedParts]);

    const handleToggle = (part) => {
        const newSelected = selected.includes(part)
            ? selected.filter(p => p !== part)
            : [...selected, part];
        
        setSelected(newSelected);
        if (onChange) {
            onChange(newSelected);
        }
    };

    // Filter available parts if provided
    const partsToShow = availableParts.length > 0 
        ? availableParts 
        : Object.keys(CarPartLabels);

    const CarPartLabels = {
        'body_tavan': 'Tavan',
        'body_kaput': 'Kaput',
        'body_bagaj': 'Bagaj',
        'body_arka_tampon': 'Arka Tampon',
        'body_on_tampon': 'Ön Tampon',
        'body_sol_arka_camurluk': 'Sol Arka Çamurluk',
        'body_sol_on_camurluk': 'Sol Ön Çamurluk',
        'body_sol_arka_kapi': 'Sol Arka Kapı',
        'body_sol_on_kapi': 'Sol Ön Kapı',
        'body_sag_arka_camurluk': 'Sağ Arka Çamurluk',
        'body_sag_on_camurluk': 'Sağ Ön Çamurluk',
        'body_sag_arka_kapi': 'Sağ Arka Kapı',
        'body_sag_on_kapi': 'Sağ Ön Kapı',
    };

    return (
        <div className="car-body-selector">
            <div className="grid grid-cols-2 gap-4">
                {partsToShow.map((part) => (
                    <label
                        key={part}
                        className={`flex items-center space-x-2 p-3 border rounded cursor-pointer ${
                            selected.includes(part)
                                ? 'bg-blue-100 border-blue-500'
                                : 'bg-white border-gray-300'
                        }`}
                    >
                        <input
                            type="checkbox"
                            checked={selected.includes(part)}
                            onChange={() => handleToggle(part)}
                            className="w-4 h-4 text-blue-600"
                        />
                        <span>{CarPartLabels[part] || part}</span>
                    </label>
                ))}
            </div>
        </div>
    );
};

export default CarBody;

