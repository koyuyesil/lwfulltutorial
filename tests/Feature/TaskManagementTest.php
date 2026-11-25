<?php

use App\Enums\Recurrence;
use App\Enums\StatusType;
use App\Enums\TaskCategory;
use App\Livewire\NotificationBell;
use App\Livewire\Tasks\TasksForm;
use App\Livewire\Tasks\TasksList;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Support\QuickTaskParser;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

afterEach(fn () => Carbon::setTestNow());

test('monthly recurrence keeps the anchor day across short months', function () {
    $task = Task::factory()->recurring(Recurrence::MONTHLY)->create(['due_at' => Carbon::parse('2027-01-31 10:00')]);

    $feb = $task->complete();
    $mar = $feb->complete();

    expect($feb->due_at->toDateTimeString())->toBe('2027-02-28 10:00:00')
        ->and($mar->due_at->toDateTimeString())->toBe('2027-03-31 10:00:00')
        ->and($task->fresh()->status)->toBe(StatusType::DONE)
        ->and($mar->series_id)->toBe($task->series_id);
});

test('weekly interval and end date are respected', function () {
    $task = Task::factory()->recurring(Recurrence::WEEKLY, 2)->create([
        'due_at' => Carbon::parse('2027-03-01 09:00'),
        'recurrence_until' => '2027-03-20',
    ]);

    $next = $task->complete();
    expect($next->due_at->toDateString())->toBe('2027-03-15')
        ->and($next->complete())->toBeNull(); // 29 Mart bitiş tarihinden sonra
});

test('reopening a completed recurring task removes the auto-created next one', function () {
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->recurring(Recurrence::DAILY)->create(['due_at' => now()->addHour()]);

    Livewire::actingAs($user)->test(TasksList::class)->call('complete', $task->id)->assertDispatched('toast');
    expect($user->tasks()->count())->toBe(2);

    Livewire::actingAs($user)->test(TasksList::class)->call('reopen', $task->id);
    expect($user->tasks()->count())->toBe(1)
        ->and($task->fresh()->status)->toBe(StatusType::OPEN);
});

test('reminder time follows due date and reminder offset', function () {
    $task = Task::factory()->create(['due_at' => Carbon::parse('2027-05-10 10:00'), 'remind_before' => 1440]);

    expect($task->remind_at->toDateTimeString())->toBe('2027-05-09 10:00:00');

    $task->update(['due_at' => Carbon::parse('2027-05-20 15:00')]);
    expect($task->fresh()->remind_at->toDateTimeString())->toBe('2027-05-19 15:00:00');
});

test('quick add understands turkish dates, recurrence, amount and category', function () {
    Carbon::setTestNow('2027-04-10 08:00');

    $rent = QuickTaskParser::parse('Kira öde her ay ayın 1\'i 10:00 15.000 tl');
    expect($rent['title'])->toBe('Kira öde')
        ->and($rent['recurrence'])->toBe(Recurrence::MONTHLY)
        ->and($rent['due_at']->toDateTimeString())->toBe('2027-05-01 10:00:00')
        ->and($rent['amount'])->toBe(15000.0)
        ->and($rent['category'])->toBe(TaskCategory::PAYMENT);

    $collect = QuickTaskParser::parse('Ahmet Bey tahsilat yarın 2500 tl');
    expect($collect['title'])->toBe('Ahmet Bey tahsilat')
        ->and($collect['due_at']->toDateString())->toBe('2027-04-11')
        ->and($collect['category'])->toBe(TaskCategory::COLLECTION);

    $friday = QuickTaskParser::parse('Kasa sayımı cuma saat 18');
    expect($friday['due_at']->toDateTimeString())->toBe('2027-04-16 18:00:00')
        ->and($friday['title'])->toBe('Kasa sayımı');
});

test('quick add creates the task for the current user', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(TasksList::class)
        ->set('quick', 'KDV beyannamesi her ay ayın 28\'i')
        ->call('quickAdd')
        ->assertSet('quick', '');

    $task = $user->tasks()->first();
    expect($task->category)->toBe(TaskCategory::TAX)
        ->and($task->recurrence)->toBe(Recurrence::MONTHLY)
        ->and($task->due_at->day)->toBe(28);
});

test('tasks are grouped into an agenda', function () {
    Carbon::setTestNow('2027-06-09 12:00'); // Çarşamba
    $user = User::factory()->create();
    $overdue = Task::factory()->for($user)->create(['due_at' => now()->subDays(2)]);
    $today = Task::factory()->for($user)->create(['due_at' => now()->setTime(17, 0)]);
    $none = Task::factory()->for($user)->create(['due_at' => null]);

    Livewire::actingAs($user)->test(TasksList::class)
        ->assertViewHas('agenda', fn ($a) => $a['overdue'][0]->id === $overdue->id
            && $a['today'][0]->id === $today->id
            && $a['none'][0]->id === $none->id);
});

test('snooze moves reminder and overdue date', function () {
    Carbon::setTestNow('2027-06-09 12:00');
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->create(['due_at' => now()->subDay()]);

    Livewire::actingAs($user)->test(TasksList::class)->call('snooze', $task->id, 'tomorrow');

    expect($task->fresh()->due_at->toDateTimeString())->toBe('2027-06-10 09:00:00')
        ->and($task->fresh()->remind_at->toDateTimeString())->toBe('2027-06-10 09:00:00');
});

test('calendar shows projected recurrences and supports drag to reschedule', function () {
    Carbon::setTestNow('2027-06-01 08:00');
    $user = User::factory()->create();
    $daily = Task::factory()->for($user)->recurring(Recurrence::WEEKLY)->create(['due_at' => Carbon::parse('2027-06-02 09:00')]);

    $component = Livewire::actingAs($user)->test(TasksList::class)->set('view', 'calendar')->set('month', '2027-06')
        ->assertViewHas('calendar', fn ($c) => isset($c['byDay']['2027-06-02'], $c['projected']['2027-06-09'], $c['projected']['2027-06-30']));

    $component->call('reschedule', $daily->id, '2027-06-04');
    expect($daily->fresh()->due_at->toDateTimeString())->toBe('2027-06-04 09:00:00');
});

test('deleting a series removes only its open occurrences', function () {
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->recurring(Recurrence::DAILY)->create(['due_at' => now()->addHour()]);
    $next = $task->complete();

    Livewire::actingAs($user)->test(TasksList::class)->call('delete', $next->id, true);

    expect(Task::find($next->id))->toBeNull()
        ->and(Task::find($task->id))->not->toBeNull(); // tamamlanan geçmiş korunur
});

test('task form applies templates and validates recurring tasks', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(TasksForm::class)
        ->set('form.title', 'Yıllık sigorta')
        ->set('form.recurrence', 'yearly')
        ->set('form.due_date', '')
        ->call('save')
        ->assertHasErrors('form.due_date');

    Livewire::actingAs($user)->test(TasksForm::class)
        ->call('useTemplate', 'vat')
        ->assertSet('form.category', 'tax')
        ->assertSet('form.recurrence', 'monthly')
        ->set('form.amount', '1250.50')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('task-saved');

    $task = $user->tasks()->first();
    expect($task->title)->toBe('KDV beyannamesi')
        ->and($task->due_at->day)->toBe(28)
        ->and($task->remind_at->equalTo($task->due_at->copy()->subMinutes(4320)))->toBeTrue()
        ->and($task->formattedAmount())->toBe('₺1.250,50');
});

test('task form cannot link another users client or edit another users task', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(TasksForm::class)
        ->set('form.title', 'Arama')
        ->set('form.client_id', \App\Models\Client::factory()->create()->id)
        ->call('save')
        ->assertHasErrors('form.client_id');

    Livewire::actingAs($user)->test(TasksForm::class)
        ->call('editTask', Task::factory()->create()->id)
        ->assertNotFound();
});

test('notification bell counts tasks needing attention and overdue tickets', function () {
    $user = User::factory()->create();
    Task::factory()->for($user)->create(['due_at' => now()->subDays(2)]);                       // gecikmiş
    Task::factory()->for($user)->create(['due_at' => now()->addDays(5), 'remind_at' => now()->subMinute()]); // hatırlatma
    Task::factory()->for($user)->create(['due_at' => now()->addDays(5)]);                        // bildirim yok
    Task::factory()->create(['due_at' => now()->subDay()]);                                      // başka kullanıcı
    Ticket::factory()->for($user)->status(\App\Enums\TicketStatus::IN_REPAIR)->create(['due_at' => now()->subHour()]);

    Livewire::actingAs($user)->test(NotificationBell::class)
        ->assertViewHas('taskCount', 2)
        ->assertViewHas('ticketCount', 1)
        ->assertViewHas('total', 3);

    $this->actingAs($user)->get(route('tasks.index'))->assertOk()->assertSee('Görevler');
});
