<?php

namespace App\Enums;

enum NotificationEventEnum: string
{
    case ORDER_CREATED = 'order.created';
    case ORDER_CANCELLED = 'order.cancelled';
    case ORDER_PENDING = 'order.pending';
    case ORDER_PROCESSING = 'order.processing';
    case ORDER_SHIPPED = 'order.shipped';
    case ORDER_DELIVERED = 'order.delivered';
    case DEALER_DEACTIVATED = 'dealer.deactivated';
    case DEALER_ACTIVATED = 'dealer.activated';
    case USER_DEACTIVATED = 'user.deactivated';
    case USER_CREATED = 'user.created';
    case STOCK_CRITICAL_LOW = 'stock.critical_low';
    case STOCK_INSUFFICIENT_FOR_ORDER = 'stock.insufficient_for_order';
    case STOCK_IMPORTED = 'stock.imported';
    case STOCK_TRANSFER_TO_DEALER = 'stock.transfer_to_dealer';
    case STOCK_RECEIVED = 'stock.received';
    case STOCK_USED_IN_SERVICE = 'stock.used_in_service';
    case STOCK_IMPORT_FAILED = 'stock.import_failed';
    case SERVICE_ASSIGNED = 'service.assigned';
    case SERVICE_READY = 'service.ready';
    case SERVICE_COMPLETED = 'service.completed';
    case SERVICE_CANCELLED = 'service.cancelled';
    case SERVICE_PENDING = 'service.pending';
    case WARRANTY_EXPIRING_SOON = 'warranty.expiring_soon';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::ORDER_CREATED->value => 'Sipariş Oluşturuldu',
            self::ORDER_CANCELLED->value => 'Sipariş İptal Edildi',
            self::ORDER_PENDING->value => 'Sipariş Beklemede',
            self::ORDER_PROCESSING->value => 'Sipariş Hazırlanıyor',
            self::ORDER_SHIPPED->value => 'Sipariş Kargoya Verildi',
            self::ORDER_DELIVERED->value => 'Sipariş Teslim Edildi',
            self::DEALER_DEACTIVATED->value => 'Bayi Pasif Yapıldı',
            self::DEALER_ACTIVATED->value => 'Bayi Aktif Yapıldı',
            self::USER_DEACTIVATED->value => 'Kullanıcı Pasif Yapıldı',
            self::USER_CREATED->value => 'Kullanıcı Oluşturuldu',
            self::STOCK_CRITICAL_LOW->value => 'Kritik Stok Seviyesi',
            self::STOCK_INSUFFICIENT_FOR_ORDER->value => 'Sipariş İçin Yetersiz Stok',
            self::STOCK_IMPORTED->value => 'Stok Girişi Yapıldı',
            self::STOCK_TRANSFER_TO_DEALER->value => 'Stok Bayiye Transfer Edildi',
            self::STOCK_RECEIVED->value => 'Stok Teslim Alındı',
            self::STOCK_USED_IN_SERVICE->value => 'Stok Serviste Kullanıldı',
            self::STOCK_IMPORT_FAILED->value => 'Stok Import Hatası',
            self::SERVICE_ASSIGNED->value => 'Servis Atandı',
            self::SERVICE_READY->value => 'Servis Hazır',
            self::SERVICE_COMPLETED->value => 'Servis Tamamlandı',
            self::SERVICE_CANCELLED->value => 'Servis İptal Edildi',
            self::SERVICE_PENDING->value => 'Servis Beklemede',
            self::WARRANTY_EXPIRING_SOON->value => 'Garanti Yakında Bitiyor',
        ];
    }
}
