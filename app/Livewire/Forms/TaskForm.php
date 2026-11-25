<?php

namespace App\Livewire\Forms;

use App\Enums\PriorityType;
use App\Enums\Recurrence;
use App\Enums\TaskCategory;
use App\Models\Task;
use App\Support\TaskTemplates;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class TaskForm extends Form
{
    public ?Task $task = null;

    public string $title = '';

    public string $description = '';

    public string $category = 'general';

    public string $due_date = '';

    public string $due_time = '09:00';

    public string $remind_before = '';

    public string $recurrence = 'none';

    public int $recurrence_interval = 1;

    public string $recurrence_until = '';

    public string $amount = '';

    public ?int $client_id = null;

    public string $priority = 'normal';

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::enum(TaskCategory::class)],
            'due_date' => ['nullable', 'date', 'required_unless:recurrence,none'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'remind_before' => ['nullable', Rule::in(array_filter(array_keys(Task::REMIND_OPTIONS), fn ($k) => $k !== ''))],
            'recurrence' => ['required', Rule::enum(Recurrence::class)],
            'recurrence_interval' => ['required', 'integer', 'min:1', 'max:365'],
            'recurrence_until' => ['nullable', 'date', 'after_or_equal:due_date'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('user_id', Auth::id())],
            'priority' => ['required', Rule::enum(PriorityType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Görev başlığı zorunludur.',
            'title.min' => 'Başlık en az 3 karakter olmalıdır.',
            'due_date.required_unless' => 'Tekrarlayan görevler için başlangıç tarihi gerekir.',
            'recurrence_until.after_or_equal' => 'Bitiş tarihi başlangıçtan önce olamaz.',
            'amount.numeric' => 'Tutar sayı olmalıdır.',
        ];
    }

    public function isEditing(): bool
    {
        return $this->task !== null;
    }

    public function setTask(Task $task): void
    {
        $this->task = $task;
        $this->title = (string) $task->title;
        $this->description = (string) $task->description;
        $this->category = $task->category->value;
        $this->due_date = $task->due_at?->format('Y-m-d') ?? '';
        $this->due_time = $task->due_at?->format('H:i') ?? '09:00';
        $this->remind_before = $task->remind_before === null ? '' : (string) $task->remind_before;
        $this->recurrence = $task->recurrence->value;
        $this->recurrence_interval = $task->recurrence_interval ?: 1;
        $this->recurrence_until = $task->recurrence_until?->format('Y-m-d') ?? '';
        $this->amount = $task->amount === null ? '' : (string) $task->amount;
        $this->client_id = $task->client_id;
        $this->priority = $task->priority?->value ?? 'normal';
    }

    public function applyTemplate(string $key): void
    {
        $template = TaskTemplates::all()[$key] ?? null;
        if (! $template) {
            return;
        }

        $due = TaskTemplates::firstDueAt($template);
        $this->title = $template['title'];
        $this->description = $template['description'] ?? '';
        $this->category = $template['category']->value;
        $this->recurrence = $template['recurrence']->value;
        $this->recurrence_interval = 1;
        $this->due_date = $due->format('Y-m-d');
        $this->due_time = $due->format('H:i');
        $this->remind_before = isset($template['remind_before']) ? (string) $template['remind_before'] : '';
        $this->priority = $template['priority']->value;
    }

    public function save(): Task
    {
        $data = $this->validate();

        $dueAt = $data['due_date']
            ? Carbon::parse($data['due_date'].' '.($data['due_time'] ?: '09:00'))
            : null;

        $attributes = [
            'title' => trim($data['title']),
            'description' => trim((string) $data['description']) ?: null,
            'category' => $data['category'],
            'due_at' => $dueAt,
            'remind_before' => $data['remind_before'] === '' || $data['remind_before'] === null ? null : (int) $data['remind_before'],
            'recurrence' => $data['recurrence'],
            'recurrence_interval' => $data['recurrence'] === 'none' ? 1 : $data['recurrence_interval'],
            'recurrence_until' => $data['recurrence'] === 'none' ? null : ($data['recurrence_until'] ?: null),
            'amount' => $data['amount'] === '' || $data['amount'] === null ? null : $data['amount'],
            'client_id' => $data['client_id'] ?: null,
            'priority' => $data['priority'],
        ];

        if ($this->task) {
            $this->task->update($attributes);
            $task = $this->task;
        } else {
            $task = Auth::user()->tasks()->create($attributes);
        }

        $this->reset();

        return $task;
    }
}
