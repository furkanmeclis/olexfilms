<?php

return [
    'password_confirm' => [
        'heading' => 'Şifreyi Doğrula',
        'description' => 'Bu işlemi tamamlamak için lütfen şifrenizi onaylayın.',
        'current_password' => 'Mevcut Şifre',
    ],
    'two_factor' => [
        'heading' => 'İki Adımlı Doğrulama',
        'description' => 'Lütfen kimlik doğrulayıcı uygulamanız tarafından sağlanan kimlik doğrulama kodunu girerek hesabınıza erişimi onaylayın.',
        'code_placeholder' => 'XXX-XXX',
        'recovery' => [
            'heading' => 'İki Adımlı Doğrulama',
            'description' => 'Lütfen acil durum kurtarma kodlarınızdan birini girerek hesabınıza erişimi onaylayın.',
        ],
        'recovery_code_placeholder' => 'abcdef-98765',
        'recovery_code_text' => 'Kayıp cihaz?',
        'recovery_code_link' => 'Bir kurtarma kodu kullanın',
        'back_to_login_link' => 'Girişe geri dön',
    ],
    'profile' => [
        'account' => 'Hesap',
        'profile' => 'Profil',
        'my_profile' => 'Profilim',
        'subheading' => 'Kullanıcı profilinizi buradan yönetin.',
        'personal_info' => [
            'heading' => 'Kişisel Bilgiler',
            'subheading' => 'Kişisel bilgilerinizi yönetin.',
            'submit' => [
                'label' => 'Güncelle',
            ],
            'notify' => 'Profil başarıyla güncellendi!',
        ],
        'password' => [
            'heading' => 'Şifre',
            'subheading' => '8 karakter olmalıdır.',
            'submit' => [
                'label' => 'Güncelle',
            ],
            'notify' => 'Şifre başarıyla güncellendi!',
        ],
        '2fa' => [
            'title' => 'İki Adımlı Doğrulama',
            'description' => 'Hesabınız için iki adımlı kimlik doğrulamayı yönetin (önerilir).',
            'actions' => [
                'enable' => 'Etkinleştir',
                'regenerate_codes' => 'Kodları Yeniden Oluştur',
                'disable' => 'Devredışı bırak',
                'confirm_finish' => 'Onayla & bitir',
                'cancel_setup' => 'Kurulumu iptal et',
                'confirm' => 'Onayla',
            ],
            'setup_key' => 'Kurulum anahtarı',
            'must_enable' => 'Bu uygulamayı kullanmak için iki faktörlü kimlik doğrulamayı etkinleştirmeniz gerekir.',
            'not_enabled' => [
                'title' => 'İki adımlı kimlik doğrulamayı etkinleştirmediniz.',
                'description' => 'İki adımlı kimlik doğrulaması etkinleştirildiğinde, kimlik doğrulaması sırasında güvenli, rasgele bir belirteç istenir. Bu belirteci telefonunuzun Google Authenticator uygulamasından alabilirsiniz.',
            ],
            'finish_enabling' => [
                'title' => 'İki adımlı kimlik doğrulamayı etkinleştirmeyi bitirin.',
                'description' => 'İki adımlı kimlik doğrulamayı etkinleştirmeyi bitirmek için telefonunuzun kimlik doğrulayıcı uygulamasını kullanarak aşağıdaki QR kodunu tarayın veya kurulum anahtarını girin ve oluşturulan OTP kodunu girin.',
            ],
            'enabled' => [
                'notify' => 'İki faktörlü kimlik doğrulama etkin.',
                'title' => 'İki adımlı kimlik doğrulamayı etkinleştirdiniz!',
                'description' => 'İki adımlı kimlik doğrulama artık etkin. Telefonunuzun kimlik doğrulayıcı uygulamasını kullanarak aşağıdaki QR kodunu tarayın veya kurulum anahtarını girin.',
                'store_codes' => 'Bu kurtarma kodlarını güvenli bir şifre yöneticisinde saklayın. İki adımlı kimlik doğrulama cihazınız kaybolursa hesabınıza erişimi kurtarmak için kullanılabilirler.',
            ],
            'disabling' => [
                'notify' => 'İki faktörlü kimlik doğrulama devre dışı bırakıldı.',
            ],
            'regenerate_codes' => [
                'notify' => 'Yeni kurtarma kodları oluşturuldu.',
            ],
            'confirmation' => [
                'success_notification' => 'Kod doğrulandı. İki adımlı kimlik doğrulaması etkin.',
                'invalid_code' => 'Girdiğiniz kod geçersiz.',
            ],
        ],
        'sanctum' => [
            'title' => 'API Belirteçleri',
            'description' => 'Üçüncü taraf hizmetlerinin sizin adınıza bu uygulamaya erişmesine izin veren API belirteçlerini yönetin. NOT: Belirteciniz oluşturulduktan sonra bir kez gösterilir. Belirtecinizi kaybederseniz, onu silmeniz ve yeni bir tane oluşturmanız gerekir.',
            'create' => [
                'notify' => 'Belirteç başarıyla oluşturuldu!',
                'message' => 'Belirteciniz oluşturulduktan sonra yalnızca bir kez gösterilir. Belirtecinizi kaybederseniz, onu silmeniz ve yeni bir tane oluşturmanız gerekecektir.',
                'submit' => [
                    'label' => 'Oluştur',
                ],
            ],
            'update' => [
                'notify' => 'Belirteç başarıyla güncellendi!',
                'submit' => [
                    'label' => 'Güncelle',
                ],
            ],
            'copied' => [
                'label' => 'Belirtecimi kopyaladım',
            ],
        ],
        'browser_sessions' => [
            'heading' => 'Tarayıcı Oturumları',
            'subheading' => 'Aktif oturumlarınızı yönetin.',
            'label' => 'Tarayıcı Oturumları',
            'content' => 'Gerekirse, tüm cihazlarınızdaki diğer tüm tarayıcı oturumlarından çıkış yapabilirsiniz. Son oturumlarınızdan bazıları aşağıda listelenmiştir; ancak bu liste kapsamlı olmayabilir. Hesabınızın güvenliğinin ihlal edildiğini düşünüyorsanız, şifrenizi de güncellemelisiniz.',
            'device' => 'Bu cihaz',
            'last_active' => 'Son aktif',
            'logout_other_sessions' => 'Diğer Tarayıcı Oturumlarından Çıkış Yap',
            'logout_heading' => 'Diğer Tarayıcı Oturumlarından Çıkış Yap',
            'logout_description' => 'Lütfen tüm cihazlarınızdaki diğer tarayıcı oturumlarından çıkış yapmak istediğinizi onaylamak için şifrenizi girin.',
            'logout_action' => 'Diğer Tarayıcı Oturumlarından Çıkış Yap',
            'incorrect_password' => 'Girdiğiniz şifre yanlıştı. Lütfen tekrar deneyin.',
            'logout_success' => 'Diğer tüm tarayıcı oturumları başarıyla kapatıldı.',
        ],
    ],
    'clipboard' => [
        'link' => 'Panoya kopyala',
        'tooltip' => 'Kopyalandı!',
    ],
    'fields' => [
        'avatar' => 'Avatar',
        'email' => 'E-posta',
        'login' => 'Giriş',
        'name' => 'İsim',
        'password' => 'Şifre',
        'password_confirm' => 'Şifre Doğrulama',
        'new_password' => 'Yeni Şifre',
        'new_password_confirmation' => 'Şifre Doğrulama',
        'token_name' => 'Belirteç adı',
        'token_expiry' => 'Belirteç sona erişi',
        'abilities' => 'Yetenekler',
        '2fa_code' => 'Kod',
        '2fa_recovery_code' => 'Kurtarma Kodu',
        'created' => 'Oluşturuldu',
        'expires' => 'Sona eriyor',
    ],
    'permissions' => [
        'create' => 'Oluştur',
        'view' => 'Görüntüle',
        'update' => 'Güncelle',
        'delete' => 'Sil',
    ],
    'or' => 'Veya',
    'cancel' => 'Vazgeç',
    'login' => [
        'username_or_email' => 'Kullanıcı Adı veya E-posta',
        'forgot_password_link' => 'Şifrenizi mi Unuttunuz?',
        'create_an_account' => 'Hesap Oluştur',
    ],
    'registration' => [
        'title' => 'Kayıt Ol',
        'heading' => 'Yeni Hesap Oluştur',
        'submit' => [
            'label' => 'Kayıt Ol',
        ],
        'notification_unique' => 'Bu e-posta adresiyle zaten bir hesap mevcut. Lütfen giriş yapın.',
    ],
    'reset_password' => [
        'title' => 'Şifrenizi mi Unuttunuz?',
        'heading' => 'Şifrenizi Sıfırlayın',
        'submit' => [
            'label' => 'Gönder',
        ],
        'notification_error' => 'Hata: Lütfen daha sonra tekrar deneyin.',
        'notification_error_link_text' => 'Tekrar Dene',
        'notification_success' => 'Daha fazla talimat için e-postanızı kontrol edin!',
    ],
    'verification' => [
        'title' => 'E-postanızı Doğrulayın',
        'heading' => 'E-posta Doğrulaması Gerekli',
        'submit' => [
            'label' => 'Çıkış Yap',
        ],
        'notification_success' => 'Daha fazla talimat için e-postanızı kontrol edin!',
        'notification_resend' => 'Yeni bir doğrulama e-postası gönderildi.',
        'before_proceeding' => 'Devam etmeden önce, lütfen e-postanızda bir doğrulama bağlantısı olup olmadığını kontrol edin.',
        'not_receive' => 'E-postayı almadıysanız,',
        'request_another' => 'başka bir tane istemek için buraya tıklayın',
    ],
];
