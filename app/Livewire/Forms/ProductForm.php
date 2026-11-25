<?php

namespace App\Livewire\Forms;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class ProductForm extends Form
{
    public ?Product $product = null;

    public string $brand = '';

    public string $model_name = '';

    public string $model_number = '';

    public string $manufacturer = '';

    public string $description = '';

    public string $codename = '';

    public string $device_type = 'phone';

    public string $release_year = '';

    public string $chipset = '';

    /** data:image/...;base64,... */
    public ?string $image = null;

    public function rules(): array
    {
        return [
            'brand' => ['required', 'string', 'min:2', 'max:60'],
            'model_name' => ['required', 'string', 'max:80'],
            'model_number' => ['nullable', 'string', 'max:80'],
            'manufacturer' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'codename' => ['nullable', 'string', 'max:80'],
            'device_type' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\DeviceType::class)],
            'release_year' => ['nullable', 'integer', 'min:1990', 'max:'.(date('Y') + 2)],
            'chipset' => ['nullable', 'string', 'max:80'],
            'image' => ['nullable', 'string', 'starts_with:data:image/'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand.required' => 'Marka zorunludur.',
            'brand.min' => 'Marka en az 2 karakter olmalıdır.',
            'model_name.required' => 'Model adı zorunludur.',
        ];
    }

    public function isEditing(): bool
    {
        return $this->product !== null;
    }

    public function setContent(Product $product): void
    {
        $this->product = $product;
        $this->brand = (string) $product->brand;
        $this->model_name = (string) $product->model_name;
        $this->model_number = (string) $product->model_number;
        $this->manufacturer = (string) $product->manufacturer;
        $this->description = (string) $product->description;
        $this->codename = (string) $product->codename;
        $this->device_type = $product->device_type ?: 'phone';
        $this->release_year = (string) $product->release_year;
        $this->chipset = (string) $product->chipset;
        $this->image = $product->image;
    }

    public function store(): Product
    {
        $data = $this->validate();
        $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data);
        // Üretici çoğu zaman markayla aynıdır (Apple, Samsung…)
        $data['manufacturer'] = $data['manufacturer'] ?: $data['brand'];
        $data['model_number'] = $data['model_number'] ?: null;
        $data['description'] = $data['description'] ?: null;
        $data['codename'] = $data['codename'] ?: null;
        $data['chipset'] = $data['chipset'] ?: null;
        $data['release_year'] = $data['release_year'] ?: null;

        if ($this->product) {
            $this->product->update($data);
            $product = $this->product;
        } else {
            $product = Auth::user()->products()->create($data);
        }

        $this->reset();

        return $product;
    }
}
