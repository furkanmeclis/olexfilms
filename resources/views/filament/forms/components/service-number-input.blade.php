@php
    $statePath = $getStatePath();
    $value = $getState();
    $isDisabled = $isDisabled();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        value: @js($value),
        isDisabled: @js($isDisabled),
        isValid: false,
        isInvalid: false,
        validationMessage: '',
        isScanning: false,
        scanner: null,
        
        
        async validate() {
            if (this.isDisabled) return;
            if (!this.value || this.value.trim() === '') {
                this.isValid = false;
                this.isInvalid = true;
                this.validationMessage = 'Hizmet numarası boş olamaz.';
                return;
            }
            
            // Livewire action'ı çağır (sadece UI feedback için, zorunlu değil)
            try {
                const result = await $wire.validateServiceNumber(this.value.trim());
                
                if (result && result.valid) {
                    this.isValid = true;
                    this.isInvalid = false;
                    this.validationMessage = result.message || 'Hizmet numarası doğrulandı.';
                    // Doğrulandıktan sonra forma ekle
                    $wire.set('{{ $statePath }}', this.value.trim(), false);
                } else {
                    this.isValid = false;
                    this.isInvalid = true;
                    this.validationMessage = result?.message || 'Hizmet numarası doğrulanamadı.';
                    // Hatalıysa da forma ekle (backend validation yapacak)
                    $wire.set('{{ $statePath }}', this.value.trim(), false);
                }
            } catch (error) {
                console.error('Validation error:', error);
                this.isValid = false;
                this.isInvalid = true;
                this.validationMessage = 'Doğrulama sırasında bir hata oluştu.';
                // Hata olsa bile forma ekle (backend validation yapacak)
                $wire.set('{{ $statePath }}', this.value.trim(), false);
            }
        },
        
        async startQRScan() {
            if (this.isDisabled) return;
            if (!('BarcodeDetector' in window) && typeof Html5Qrcode === 'undefined') {
                alert('QR kod okutma özelliği bu tarayıcıda desteklenmiyor. Lütfen modern bir tarayıcı kullanın.');
                return;
            }
            
            this.isScanning = true;
            
            try {
                // Kamera izni al
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } 
                });
                
                // QR kod okutma başlat
                if (typeof Html5Qrcode !== 'undefined') {
                    const qrCodeScanner = new Html5Qrcode('qr-reader');
                    
                    await qrCodeScanner.start(
                        { facingMode: 'environment' },
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        },
                        (decodedText) => {
                            this.handleQRCode(decodedText);
                            qrCodeScanner.stop();
                            this.isScanning = false;
                        },
                        (errorMessage) => {
                            // Hata mesajını görmezden gel, sadece logla
                            console.log('QR scan error:', errorMessage);
                        }
                    );
                    
                    this.scanner = qrCodeScanner;
                } else {
                    // BarcodeDetector API kullan (daha modern)
                    const video = document.createElement('video');
                    video.srcObject = stream;
                    video.play();
                    
                    const detector = new BarcodeDetector({ formats: ['qr_code'] });
                    
                    const detectQR = async () => {
                        try {
                            const barcodes = await detector.detect(video);
                            if (barcodes.length > 0) {
                                this.handleQRCode(barcodes[0].rawValue);
                                stream.getTracks().forEach(track => track.stop());
                                this.isScanning = false;
                                return;
                            }
                        } catch (e) {
                            console.log('Detection error:', e);
                        }
                        
                        if (this.isScanning) {
                            requestAnimationFrame(detectQR);
                        }
                    };
                    
                    detectQR();
                }
            } catch (error) {
                console.error('QR scan error:', error);
                alert('Kamera erişimi reddedildi veya bir hata oluştu.');
                this.isScanning = false;
            }
        },
        
        stopQRScan() {
            if (this.scanner) {
                this.scanner.stop();
                this.scanner = null;
            }
            this.isScanning = false;
        },
        
        handleQRCode(qrText) {
            // QR kod formatı: https://olexfilms.app/warranty/1nrlZmcv
            // URL'den son kısmı (service_no) çıkar
            try {
                const url = qrText.trim();
                const parts = url.split('/');
                const serviceNo = parts[parts.length - 1];
                
                if (serviceNo && serviceNo.length > 0) {
                    this.value = serviceNo;
                    // QR kod okutulduktan sonra otomatik doğrula
                    this.validate();
                } else {
                    this.isValid = false;
                    this.isInvalid = true;
                    this.validationMessage = 'Geçersiz QR kod formatı.';
                }
            } catch (e) {
                console.error('QR parse error:', e);
                this.isValid = false;
                this.isInvalid = true;
                this.validationMessage = 'QR kod işlenirken bir hata oluştu.';
            }
        }
    }">
        <div class="space-y-2">
            <div class="flex gap-2">
                <div class="flex-1">
                    <input
                        type="text"
                        x-model="value"
                        :disabled="isDisabled"
                        @input="isValid = false; isInvalid = false; validationMessage = ''; $wire.set('{{ $statePath }}', $event.target.value, false);"
                        :class="{
                            'border-gray-300': !isValid && !isInvalid,
                            'border-green-500': isValid,
                            'border-red-500': isInvalid,
                            'opacity-50 cursor-not-allowed': isDisabled
                        }"
                        class="fi-input w-full rounded-lg border shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        placeholder="Hizmet numarası girin veya QR kod okutun"
                    >
                </div>
                <button
                    type="button"
                    @click="validate()"
                    :disabled="isDisabled || !value || value.trim() === ''"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Doğrula
                </button>
                <button
                    type="button"
                    @click="isScanning ? stopQRScan() : startQRScan()"
                    :disabled="isDisabled"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2.01M19 8h2M5 20h2m-2-4h2m4-8h2m-2 4h2m-2 4h2"></path>
                    </svg>
                    <span x-text="isScanning ? 'Durdur' : 'QR Okut'"></span>
                </button>
            </div>
            
            <!-- Validation Message -->
            <div x-show="isValid || isInvalid" class="text-sm">
                <p x-show="isValid" class="text-green-600" x-text="validationMessage"></p>
                <p x-show="isInvalid" class="text-red-600" x-text="validationMessage"></p>
            </div>
            
            <!-- QR Scanner -->
            <div x-show="isScanning" x-cloak class="mt-4">
                <div id="qr-reader" class="w-full max-w-md mx-auto"></div>
                <p class="text-sm text-gray-600 text-center mt-2">QR kodu kameraya gösterin</p>
            </div>
        </div>
    </div>
    
    <!-- Html5Qrcode CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</x-dynamic-component>

