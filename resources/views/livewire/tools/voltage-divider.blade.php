<div>
    {{-- MathJax sadece bu araçta gerekli: tüm sayfalara CDN'den yüklenmek yerine bir kez burada yüklenir --}}
    @assets
        <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js" async></script>
    @endassets

    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Voltage Divider') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __(' Gerilim Bölücü Devresi (Voltage Divider Circuit), bir gerilimi iki veya daha fazla direnç arasında paylaştırmak amacıyla kullanılan basit bir devre elemanıdır. Bu devre, genellikle bir gerilim kaynağını daha düşük bir gerilime düşürmek için kullanılır.') }}
        </p>
    </header>

    {{--
        Eskiden global <script> + const tanımları kullanılıyordu; wire:navigate ile sayfaya
        tekrar gelindiğinde "already declared" hatası veriyordu. Artık durum Alpine ile tutuluyor.
    --}}
    <div class="max-w-3xl mx-auto p-6"
        x-data="{
            inputVoltage: 1.8,
            r1: 100000,
            r2: 0,
            voltageList: [0, 0.05, 0.10, 0.15, 0.20, 0.25, 0.30, 0.35, 0.40, 0.45, 0.50, 0.55, 0.60, 0.65, 0.70, 0.75, 0.80, 0.85, 0.90, 0.95, 1.00],
            get outputVoltage() {
                const total = Number(this.r1) + Number(this.r2);
                return total === 0 ? 0 : this.inputVoltage * (Number(this.r2) / total);
            },
            get hwid() {
                let match = 'GB-MP-0.0.0';
                this.voltageList.forEach((voltage, i) => {
                    if (this.outputVoltage >= voltage) {
                        const n = String(i + 1).padStart(3, '0');
                        match = `GB-MP-${n[0]}.${n[1]}.${n[2]}`;
                    }
                });
                return match;
            },
        }"
        x-init="window.MathJax?.typesetPromise?.([$refs.formula])">
        <!-- Başlık -->
        <h1 class="text-3xl font-semibold text-center mb-4">Gerilim Bölücü Simülatörü</h1>
        <h2 class="text-xl text-center mb-6">Örsan NUHOĞLU</h2>

        <!-- Ana içerik -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- 1. Alan: Kontroller ve simülasyon -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="mb-6">
                    <label for="r1" class="block text-lg mb-2">R1: <span x-text="(r1 / 1000).toFixed(2)">100.00</span> kΩ</label>
                    <input type="range" id="r1" min="0" max="100000" step="500" x-model.number="r1" class="w-full">
                </div>

                <div class="mb-6">
                    <label for="r2" class="block text-lg mb-2">R2: <span x-text="(r2 / 1000).toFixed(2)">0.00</span> kΩ</label>
                    <input type="range" id="r2" min="0" max="100000" step="500" x-model.number="r2" class="w-full">
                </div>

                <div class="text-lg font-bold text-red-500">
                    Çıkış Voltajı: <span x-text="outputVoltage.toFixed(2)">0.00</span> V
                </div>
                <div class="text-lg font-semibold text-blue-500 mt-2">
                    HWID: <span x-text="hwid">GB-MP-0.0.1</span>
                </div>
            </div>

            <!-- 2. Alan: SVG Grafik ve Matematiksel Formül -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="mb-6 text-center">
                    <x-koyu-svg-voltage-dvider />
                </div>

                <!-- MathJax Formül -->
                <div class="text-center mb-4">
                    <div x-ref="formula">
                        $$ V_{out} = V_{in} \times \frac{R_2}{R_1 + R_2} $$
                    </div>
                </div>

                <!-- Açıklama -->
                <div class="text-sm text-gray-600">
                    Bu formül, gerilim bölücü devresindeki çıkış voltajını hesaplamak için kullanılır. Burada:
                    <ul class="list-disc pl-5">
                        <li><strong>V<sub>in</sub></strong>: Giriş voltajı</li>
                        <li><strong>R<sub>1</sub></strong>: Direnç</li>
                        <li><strong>R<sub>2</sub></strong>: Direnç</li>
                        <li><strong>V<sub>out</sub></strong>: Çıkış Voltajı</li>
                    </ul>
                    Çıkış voltajı, giriş voltajının direnç oranına göre hesaplanır.
                </div>
            </div>
        </div>
    </div>
</div>
