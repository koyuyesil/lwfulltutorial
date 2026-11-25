<?php

namespace App\Livewire\Tools;

use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Elektronik hesaplayıcılar ve birim dönüştürücüler.
 * Tüm araçlar tek sayfada, birbiriyle ilişkili olanlar aynı grupta alt alta listelenir.
 * Hesaplamalar tarayıcıda (Alpine, resources/js/calculators.js) anlık yapılır.
 */
#[Title('Hesaplayıcılar')]
class ToolsIndex extends Component
{
    /**
     * Gruplar ve araçlar (sayfadaki sıra). tone: grup rengi (Tailwind renk adı).
     *
     * @var array<string, array{title: string, desc: string, tone: string, tools: array<string, array{0: string, 1: string, 2: string, 3: string}>}>
     */
    public const GROUPS = [
        'temel' => [
            'title' => 'Temel devre', 'tone' => 'sky',
            'desc' => 'Ohm kanunundan başlayıp eşdeğer direnç, gerilim bölücü ve LED sürmeye uzanan zincir.',
            'tools' => [
                'ohm' => ['Ohm kanunu', 'Gerilim, akım, direnç ve güçten herhangi ikisini girin.', 'Ω', 'v=ir gerilim akım direnç güç watt'],
                'network' => ['Seri / paralel', 'Direnç, kondansatör ve bobinlerin eşdeğer değeri.', '∥', 'seri paralel eşdeğer direnç kapasite'],
                'divider' => ['Gerilim bölücü', 'Çıkış gerilimi ya da hedef gerilim için R2.', '⫶', 'voltage divider r1 r2 board id'],
                'led' => ['LED ön direnci', 'Besleme, LED gerilimi ve akımından direnç ve güç.', '💡', 'led direnç akım'],
            ],
        ],
        'kodlar' => [
            'title' => 'Bileşen tanıma', 'tone' => 'amber',
            'desc' => 'Kart üzerindeki direnç ve kondansatörlerin renk ve baskı kodlarını değere çevirin.',
            'tools' => [
                'colors' => ['Direnç renk kodu', '4, 5 ve 6 bantlı dirençler; değerden renge de çevirir.', '🎨', 'renk bant color code'],
                'smd' => ['SMD direnç kodu', '3 ve 4 haneli kodlar, R notasyonu ve EIA-96.', '▭', 'smd 103 eia-96 01c'],
                'capcode' => ['Kondansatör kodu', '104, 4n7, 222J gibi kodları değere çevirir.', '⊣⊢', 'kapasitör kod 104 seramik'],
            ],
        ],
        'zaman' => [
            'title' => 'Zaman ve frekans', 'tone' => 'violet',
            'desc' => 'RC şarjı, reaktans ve rezonans, 555 osilatörü ve sinyal seviyeleri.',
            'tools' => [
                'rc' => ['RC zaman sabiti / filtre', 'τ, şarj süreleri ve kesim frekansı.', 'τ', 'rc tau filtre kesim alçak geçiren'],
                'reactance' => ['Reaktans ve rezonans', 'Belirli frekansta bobin ve kondansatör reaktansı, LC rezonansı.', '∿', 'xl xc rezonans lc empedans'],
                'timer555' => ['555 astable', 'Frekans, görev oranı ve darbe süreleri.', '⎍', '555 osilatör astable frekans duty'],
                'db' => ['dB / dBm', 'Güç ve gerilim oranları, dBm ↔ mW, sinyal gücü.', 'dB', 'desibel dbm mw kazanç sinyal'],
            ],
        ],
        'guc' => [
            'title' => 'Güç ve batarya', 'tone' => 'emerald',
            'desc' => 'Batarya ömrü, şarj süresi ve cihazların enerji maliyeti.',
            'tools' => [
                'battery' => ['Batarya süresi ve şarj', 'Çalışma süresi, şarj süresi ve Wh.', '🔋', 'mah wh pil şarj süresi'],
                'power' => ['Güç tüketimi ve maliyet', 'Watt → günlük / aylık kWh ve elektrik maliyeti.', '⚡', 'kwh enerji maliyet fatura watt'],
            ],
        ],
        'pcb' => [
            'title' => 'PCB ve kablo', 'tone' => 'rose',
            'desc' => 'Onarım yolu ve jumper teli: akım taşıma kapasitesi, kesit ve direnç.',
            'tools' => [
                'trace' => ['PCB yol genişliği', 'IPC-2221: akım için gereken iz genişliği.', '〰', 'pcb iz trace ipc 2221 bakır jumper'],
                'awg' => ['AWG ↔ mm²', 'Kablo kesiti, çapı ve metre direnci.', '⌀', 'awg kablo kesit mm2 çap'],
            ],
        ],
        'birim' => [
            'title' => 'Birim dönüştürücü', 'tone' => 'slate',
            'desc' => 'Uzunluk, veri, enerji, sıcaklık, basınç ve elektriksel birimler.',
            'tools' => [
                'convert' => ['Birim dönüştürücü', 'Bir değeri seçili kategorinin tüm birimlerine çevirir.', '⇄', 'çevir dönüştür birim'],
            ],
        ],
    ];

    public string $initial = 'ohm';

    public function mount(): void
    {
        $key = preg_replace('/[^a-z0-9]/', '', (string) request()->query('arac', 'ohm')) ?: 'ohm';
        $this->initial = in_array($key, array_keys(self::tools()), true) ? $key : 'ohm';
    }

    /** @return array<string, array{key: string, group: string, title: string, desc: string, icon: string, keywords: string}> */
    public static function tools(): array
    {
        $tools = [];
        foreach (self::GROUPS as $group => $g) {
            foreach ($g['tools'] as $key => [$title, $desc, $icon, $keywords]) {
                $tools[$key] = compact('key', 'group', 'title', 'desc', 'icon', 'keywords');
            }
        }

        return $tools;
    }

    public function render()
    {
        return view('livewire.tools.tools-index', [
            'groups' => self::GROUPS,
            'tools' => array_values(self::tools()),
        ])->layout('layouts.app');
    }
}
