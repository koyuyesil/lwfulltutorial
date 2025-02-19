<div>
<header>
    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ __('Voltage Divider') }}
    </h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ __(' Gerilim Bölücü Devresi (Voltage Divider Circuit), bir gerilimi iki veya daha fazla direnç arasında paylaştırmak amacıyla kullanılan basit bir devre elemanıdır. Bu devre, genellikle bir gerilim kaynağını daha düşük bir gerilime düşürmek için kullanılır.') }}
    </p>
</header>
<div id="app" class="max-w-3xl mx-auto p-6">
    <!-- Başlık -->
    <h1 class="text-3xl font-semibold text-center mb-4">Gerilim Bölücü Simülatörü</h1>
    <h2 class="text-xl text-center mb-6">Örsan NUHOĞLU</h2>

    <!-- Ana içerik -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- 1. Alan: Geriye kalan kontroller ve simülasyon -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="mb-6">
                <label for="r1" class="block text-lg mb-2">R1: <span id="r1-label">100.00</span> kΩ</label>
                <input type="range" id="r1" min="0" max="100000" step="500" value="100000"
                    class="w-full" oninput="updateSimulation()">
            </div>

            <div class="mb-6">
                <label for="r2" class="block text-lg mb-2">R2: <span id="r2-label">0.00</span> kΩ</label>
                <input type="range" id="r2" min="0" max="100000" step="500" value="0"
                    class="w-full" oninput="updateSimulation()">
            </div>

            <div class="text-lg font-bold text-red-500">
                Çıkış Voltajı: <span id="output-voltage">0.00</span> V
            </div>
            <div class="text-lg font-semibold text-blue-500 mt-2">
                HWID: <span id="hwid-number">GB-MP-0.0.0</span>
            </div>
        </div>

        <!-- 2. Alan: SVG Grafik ve Matematiksel Formül -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <!-- SVG Grafik -->
            <div class="mb-6 text-center">
                <x-koyu-svg-voltage-dvider />
            </div>

            <!-- MathJax Formül -->
            <div class="text-center mb-4">
                <div id="math-formula">
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

<script>
    // JavaScript kodları burada
    const inputVoltage = 1.8;
    const voltageList = [0, 0.05, 0.10, 0.15, 0.20, 0.25, 0.30, 0.35, 0.40, 0.45, 0.50, 0.55, 0.60, 0.65, 0.70, 0.75,
        0.80, 0.85, 0.90, 0.95, 1.00
    ];
    const hwidList = [
        "GB-MP-0.0.1", "GB-MP-0.0.2", "GB-MP-0.0.3", "GB-MP-0.0.4", "GB-MP-0.0.5",
        "GB-MP-0.0.6", "GB-MP-0.0.7", "GB-MP-0.0.8", "GB-MP-0.0.9", "GB-MP-0.1.0",
        "GB-MP-0.1.1", "GB-MP-0.1.2", "GB-MP-0.1.3", "GB-MP-0.1.4", "GB-MP-0.1.5",
        "GB-MP-0.1.6", "GB-MP-0.1.7", "GB-MP-0.1.8", "GB-MP-0.1.9", "GB-MP-0.2.0", "GB-MP-0.2.1"
    ];

    function updateSimulation() {
        const r1 = parseFloat(document.getElementById('r1').value);
        const r2 = parseFloat(document.getElementById('r2').value);


        const r1Label = document.getElementById('r1-label');
        const r2Label = document.getElementById('r2-label');
        const outputVoltageLabel = document.getElementById('output-voltage');
        const hwidLabel = document.getElementById('hwid-number');

        // Çıkış voltajını hesapla
        const outputVoltage = inputVoltage * (r2 / (r1 + r2));


        r1Label.textContent = (r1 / 1000).toFixed(2);
        r2Label.textContent = (r2 / 1000).toFixed(2);
        outputVoltageLabel.textContent = outputVoltage.toFixed(2);

        let matchedHwid = "GB-MP-0.0.0";
        for (let i = 0; i < voltageList.length; i++) {
            if (outputVoltage >= voltageList[i]) {
                matchedHwid = hwidList[i];
            }
        }
        hwidLabel.textContent = matchedHwid;
    }

    function toggleVisibility(id) {
        const element = document.getElementById(id);
        element.classList.toggle('hidden');
        updateSimulation();
    }

    updateSimulation();
</script>
</div>
