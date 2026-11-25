<?php

namespace App\Livewire\Forms;

use App\Enums\DividerType;
use App\Enums\RepairMethod;
use App\Models\BoardId;
use App\Support\Si;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BoardIdForm extends Form
{
    public ?BoardId $boardId = null;

    public ?int $product_id = null;

    public string $build_name = '';

    public string $divider_type = 'adc';

    /** Okuyan entegre: pmic | soc (yalnızca görselde seçilir) */
    public string $chip = 'pmic';

    public string $vref = '1.8';

    /** SI önekli yazılabilir: 100k, 4k7, 22k1. Boş = takılı değil (NC). */
    public string $r1 = '100k';

    public string $r2 = '10k';

    /** Orijinal değerler: yalnızca PCB çiziminde gösterilir, formda düzenlenmez (kayıtta korunur). */
    public ?float $r1_org = null;

    public ?float $r2_org = null;

    public string $mass_production_hwid = '';

    public string $pre_production_hwid = '';

    public array $repair_methods = [];

    public string $description = '';

    public function rules(): array
    {
        $resistor = fn () => function (string $attribute, mixed $value, \Closure $fail) {
            if (trim((string) $value) !== '' && ((Si::parse($value) ?? -1) < 0)) {
                $fail('Geçerli bir direnç girin (örn. 100k, 4k7, 22k1).');
            }
        };

        return [
            'product_id' => ['required', Rule::exists('products', 'id')->where('user_id', Auth::id())],
            'build_name' => ['required', 'string', 'min:2', 'max:80'],
            'divider_type' => ['required', Rule::enum(DividerType::class)],
            'chip' => ['required', Rule::enum(\App\Enums\ChipType::class)],
            'vref' => ['required', 'numeric', 'min:0.5', 'max:5'],
            'r1' => ['nullable', 'string', 'max:20', $resistor()],
            'r2' => ['nullable', 'string', 'max:20', $resistor()],
            'mass_production_hwid' => ['nullable', 'string', 'max:80'],
            'pre_production_hwid' => ['nullable', 'string', 'max:80'],
            'repair_methods' => ['array'],
            'repair_methods.*' => [Rule::enum(RepairMethod::class)],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Cihaz modelini seçin.',
            'build_name.required' => 'Build / kart adı zorunludur.',
            'vref.*' => 'Referans gerilim 0,5 – 5 V arasında olmalıdır.',
        ];
    }

    public function isEditing(): bool
    {
        return $this->boardId !== null;
    }

    public function setContent(BoardId $boardId): void
    {
        $this->boardId = $boardId;
        $this->product_id = $boardId->product_id;
        $this->build_name = (string) $boardId->build_name;
        $this->divider_type = $boardId->divider_type?->value ?? 'adc';
        $this->chip = $boardId->chip?->value ?? 'pmic';
        $this->vref = rtrim(rtrim(number_format((float) ($boardId->vref ?: 1.8), 3, '.', ''), '0'), '.');
        $this->r1 = $this->display($boardId->r1());
        $this->r2 = $this->display($boardId->r2());
        $this->r1_org = $boardId->r1Original();
        $this->r2_org = $boardId->r2Original();
        $this->mass_production_hwid = (string) $boardId->mass_production_hwid;
        $this->pre_production_hwid = (string) $boardId->pre_production_hwid;
        $this->repair_methods = array_map(fn ($m) => $m->value, $boardId->methods());
        $this->description = (string) $boardId->description;
    }

    /** 4700 → "4k7" (formda kısa yazım) */
    protected function display(?float $ohms): string
    {
        if ($ohms === null) {
            return '';
        }
        foreach ([[1e6, 'M'], [1e3, 'k']] as [$f, $p]) {
            if ($ohms >= $f) {
                $v = rtrim(rtrim(number_format($ohms / $f, 3, '.', ''), '0'), '.');

                return str_contains($v, '.') ? str_replace('.', $p, $v) : $v.$p;
            }
        }

        return rtrim(rtrim(number_format($ohms, 2, '.', ''), '0'), '.');
    }

    public function store(): BoardId
    {
        $data = $this->validate();
        $type = DividerType::from($data['divider_type']);

        $attributes = [
            'product_id' => $data['product_id'],
            'build_name' => trim($data['build_name']),
            'divider_type' => $type,
            'chip' => $data['chip'],
            'vref' => (float) $data['vref'],
            'resistances' => [
                'R1' => trim((string) $data['r1']) === '' ? null : Si::parse($data['r1']),
                'R2' => trim((string) $data['r2']) === '' ? null : Si::parse($data['r2']),
            ],
            'mass_production_hwid' => trim($data['mass_production_hwid']) ?: null,
            'pre_production_hwid' => trim($data['pre_production_hwid']) ?: null,
            'repair_methods' => array_values(array_unique($data['repair_methods'])),
            'description' => trim((string) $data['description']) ?: null,
        ];

        if ($this->boardId) {
            // Formda olmayan ek değerler (R1_ORG, R2_ORG) korunur
            $attributes['resistances'] += array_diff_key((array) $this->boardId->resistances, ['R1' => 1, 'R2' => 1]);
            $this->boardId->update($attributes);
            $board = $this->boardId;
        } else {
            $board = Auth::user()->boardIds()->create($attributes);
        }

        $this->reset();

        return $board;
    }
}
