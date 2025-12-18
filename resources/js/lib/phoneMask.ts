/**
 * Telefon numarası maskesi uygular
 * Format: 0(999) 999-9999
 */
export const applyPhoneMask = (value: string): string => {
    // Sadece rakamları al
    const numbers = value.replace(/\D/g, '');

    // İlk karakter 0 olmalı
    if (numbers.length === 0) return '';
    if (numbers[0] !== '0') return '0';

    let formatted = '0';

    if (numbers.length > 1) {
        formatted += '(' + numbers.substring(1, 4);
    }
    if (numbers.length >= 4) {
        formatted += ') ' + numbers.substring(4, 7);
    }
    if (numbers.length >= 7) {
        formatted += '-' + numbers.substring(7, 11);
    }

    return formatted;
};

/**
 * Maskelenmiş telefon numarasından sadece rakamları çıkarır
 */
export const removePhoneMask = (value: string): string => {
    return value.replace(/\D/g, '');
};

