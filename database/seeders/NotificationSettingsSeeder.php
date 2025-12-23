<?php

namespace Database\Seeders;

use App\Enums\NotificationEventEnum;
use App\Enums\NotificationPriorityEnum;
use App\Enums\NotificationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\NotificationSetting;
use Illuminate\Database\Seeder;

class NotificationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [
            // SUPER_ADMIN
            [
                'event' => NotificationEventEnum::ORDER_CREATED->value,
                'role' => UserRoleEnum::SUPER_ADMIN->value,
                'message_template' => 'Yeni sipariş oluşturuldu: {dealer_name} - #{order_id}',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_CANCELLED->value,
                'role' => UserRoleEnum::SUPER_ADMIN->value,
                'message_template' => 'Sipariş iptal edildi: #{order_id} - {dealer_name}',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::DEALER_DEACTIVATED->value,
                'role' => UserRoleEnum::SUPER_ADMIN->value,
                'message_template' => 'Bayi pasif yapıldı: {dealer_name} - Tüm kullanıcıların erişimi kesildi',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::DEALER_ACTIVATED->value,
                'role' => UserRoleEnum::SUPER_ADMIN->value,
                'message_template' => 'Bayi aktif yapıldı: {dealer_name}',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::USER_DEACTIVATED->value,
                'role' => UserRoleEnum::SUPER_ADMIN->value,
                'message_template' => 'Kullanıcı pasif yapıldı: {user_name} ({user_email})',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::STOCK_CRITICAL_LOW->value,
                'role' => UserRoleEnum::SUPER_ADMIN->value,
                'message_template' => 'Kritik stok seviyesi: {product_name} - Merkez stokunda {count} adet kaldı',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_SHIPPED->value,
                'role' => UserRoleEnum::SUPER_ADMIN->value,
                'message_template' => 'Sipariş kargoya verildi: #{order_id} - {dealer_name} - Takip: {tracking_number}',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_DELIVERED->value,
                'role' => UserRoleEnum::SUPER_ADMIN->value,
                'message_template' => 'Sipariş teslim edildi: #{order_id} - {dealer_name}',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::SERVICE_COMPLETED->value,
                'role' => UserRoleEnum::SUPER_ADMIN->value,
                'message_template' => 'Servis tamamlandı: #{service_id} - {customer_name}',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::STOCK_IMPORT_FAILED->value,
                'role' => UserRoleEnum::SUPER_ADMIN->value,
                'message_template' => 'Stok import hatası: {error_message}',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],

            // CENTER_STAFF
            [
                'event' => NotificationEventEnum::ORDER_CREATED->value,
                'role' => UserRoleEnum::CENTER_STAFF->value,
                'message_template' => 'Yeni sipariş oluşturuldu: {dealer_name} - #{order_id} - Hazırlanmayı bekliyor',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_PENDING->value,
                'role' => UserRoleEnum::CENTER_STAFF->value,
                'message_template' => 'Sipariş hazırlanmayı bekliyor: #{order_id} - {dealer_name}',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::STOCK_CRITICAL_LOW->value,
                'role' => UserRoleEnum::CENTER_STAFF->value,
                'message_template' => 'Kritik stok seviyesi: {product_name} - Merkez stokunda {count} adet kaldı',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::STOCK_INSUFFICIENT_FOR_ORDER->value,
                'role' => UserRoleEnum::CENTER_STAFF->value,
                'message_template' => 'Sipariş için yetersiz stok: #{order_id} - {product_name} - İstenen: {quantity}, Mevcut: {available}',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_SHIPPED->value,
                'role' => UserRoleEnum::CENTER_STAFF->value,
                'message_template' => 'Sipariş kargoya verildi: #{order_id} - {dealer_name} - Takip: {tracking_number}',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_CANCELLED->value,
                'role' => UserRoleEnum::CENTER_STAFF->value,
                'message_template' => 'Sipariş iptal edildi: #{order_id} - {dealer_name} - Rezerve stoklar serbest bırakıldı',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::STOCK_IMPORTED->value,
                'role' => UserRoleEnum::CENTER_STAFF->value,
                'message_template' => 'Stok girişi yapıldı: {count} adet {product_name} eklendi',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_DELIVERED->value,
                'role' => UserRoleEnum::CENTER_STAFF->value,
                'message_template' => 'Sipariş teslim edildi: #{order_id} - {dealer_name}',
                'priority' => NotificationPriorityEnum::LOW->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::SERVICE_READY->value,
                'role' => UserRoleEnum::CENTER_STAFF->value,
                'message_template' => 'Servis hazır: #{service_id} - {customer_name} - Müşteriye teslim edilebilir',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::STOCK_TRANSFER_TO_DEALER->value,
                'role' => UserRoleEnum::CENTER_STAFF->value,
                'message_template' => 'Stok bayiye transfer edildi: {product_name} - {dealer_name} - {count} adet',
                'priority' => NotificationPriorityEnum::LOW->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],

            // DEALER_OWNER
            [
                'event' => NotificationEventEnum::ORDER_CREATED->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Yeni sipariş oluşturuldu: #{order_id} - Toplam: {total_amount} TL',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_PROCESSING->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Siparişiniz hazırlanıyor: #{order_id} - Stoklar rezerve edildi',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_SHIPPED->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Siparişiniz kargoya verildi: #{order_id} - Kargo: {cargo_company} - Takip: {tracking_number}',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_DELIVERED->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Siparişiniz teslim edildi: #{order_id} - Stoklar envanterinize eklendi',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_CANCELLED->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Siparişiniz iptal edildi: #{order_id} - Sebep: {reason}',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::DEALER_DEACTIVATED->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Bayiniz pasif yapıldı - Sistem erişiminiz kesildi. Lütfen yönetici ile iletişime geçin',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::SERVICE_COMPLETED->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Servis tamamlandı: #{service_id} - {customer_name} - Garanti başladı',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::SERVICE_READY->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Servis hazır: #{service_id} - {customer_name} - Müşteriye teslim edilebilir',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::STOCK_RECEIVED->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Stok teslim alındı: {product_name} - {count} adet envanterinize eklendi',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::USER_CREATED->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Yeni çalışan eklendi: {user_name} ({user_email})',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::USER_DEACTIVATED->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Çalışan pasif yapıldı: {user_name} - Sistem erişimi kesildi',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::SERVICE_CANCELLED->value,
                'role' => UserRoleEnum::DEALER_OWNER->value,
                'message_template' => 'Servis iptal edildi: #{service_id} - {customer_name}',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],

            // DEALER_STAFF
            [
                'event' => NotificationEventEnum::ORDER_SHIPPED->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Sipariş kargoya verildi: #{order_id} - Kargo: {cargo_company} - Takip: {tracking_number}',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::ORDER_DELIVERED->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Sipariş teslim edildi: #{order_id} - Stoklar envanterinize eklendi',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::SERVICE_ASSIGNED->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Yeni servis atandı: #{service_id} - {customer_name} - {car_brand} {car_model}',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::SERVICE_READY->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Servis hazır: #{service_id} - {customer_name} - Müşteriye teslim edilebilir',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::SERVICE_COMPLETED->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Servis tamamlandı: #{service_id} - {customer_name} - Garanti başladı',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::SERVICE_CANCELLED->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Servis iptal edildi: #{service_id} - {customer_name}',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::STOCK_RECEIVED->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Stok teslim alındı: {product_name} - {count} adet envanterinize eklendi',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::STOCK_USED_IN_SERVICE->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Stok serviste kullanıldı: {product_name} - Servis: #{service_id}',
                'priority' => NotificationPriorityEnum::LOW->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::DEALER_DEACTIVATED->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Bayiniz pasif yapıldı - Sistem erişiminiz kesildi. Lütfen yönetici ile iletişime geçin',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::USER_DEACTIVATED->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Hesabınız pasif yapıldı - Sistem erişiminiz kesildi',
                'priority' => NotificationPriorityEnum::CRITICAL->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::WARRANTY_EXPIRING_SOON->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Garanti yakında bitiyor: {product_name} - Servis: #{service_id} - {days_left} gün kaldı',
                'priority' => NotificationPriorityEnum::MEDIUM->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
            [
                'event' => NotificationEventEnum::SERVICE_PENDING->value,
                'role' => UserRoleEnum::DEALER_STAFF->value,
                'message_template' => 'Yeni servis beklemede: #{service_id} - {customer_name}',
                'priority' => NotificationPriorityEnum::HIGH->value,
                'status' => NotificationStatusEnum::ACTIVE->value,
            ],
        ];

        foreach ($notifications as $notification) {
            NotificationSetting::updateOrCreate(
                [
                    'event' => $notification['event'],
                    'role' => $notification['role'],
                ],
                $notification
            );
        }
    }
}

