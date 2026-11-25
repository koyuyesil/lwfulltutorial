<?php

namespace App\Livewire\Dashboard;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Oturumdaki firmanın operasyon panosu. Tüm veriler yalnızca kendi kayıtlarından hesaplanır.
 */
#[Title('Dashboard')]
class DashboardIndex extends Component
{
    public const TREND_DAYS = 14;

    public function render()
    {
        $user = Auth::user();
        $tickets = fn () => $user->tickets();

        $closed30 = $tickets()->whereNotNull('closed_at')->where('closed_at', '>=', now()->subDays(30))
            ->get(['id', 'created_at', 'closed_at', 'due_at', 'status']);
        $delivered30 = $closed30->where('status', TicketStatus::DELIVERED);
        $met = $delivered30->filter(fn (Ticket $t) => $t->metSla())->count();

        return view('livewire.dashboard.dashboard-index', [
            'kpi' => [
                'open' => $tickets()->open()->count(),
                'overdue' => $tickets()->overdue()->count(),
                'atRisk' => $tickets()->atRisk()->count(),
                'ready' => $tickets()->where('status', TicketStatus::READY)->count(),
                'dueToday' => $tickets()->open()->whereBetween('due_at', [now()->startOfDay(), now()->endOfDay()])->count(),
                'compliance' => $delivered30->isEmpty() ? null : (int) round($met / $delivered30->count() * 100),
                'delivered30' => $delivered30->count(),
                'avgHours' => $delivered30->isEmpty() ? null
                    : round($delivered30->avg(fn (Ticket $t) => $t->created_at->diffInMinutes($t->closed_at)) / 60, 1),
                'clients' => $user->clients()->count(),
            ],
            'trendChart' => $this->trendChart($user),
            'statusChart' => $this->statusChart($user),
            'technicianChart' => $this->technicianChart($user),
            'brandChart' => $this->brandChart($user),
            'urgent' => $tickets()->open()->with('clientProduct.client', 'clientProduct.product')
                ->orderBy('due_at')->limit(6)->get(),
            'activity' => TicketEvent::query()
                ->whereHas('ticket', fn ($q) => $q->where('user_id', $user->id))
                ->with('user:id,name', 'ticket:id,tracking_code')
                ->latest('id')->limit(8)->get(),
        ])->layout('layouts.app');
    }

    /** Son N günde açılan ve kapanan işler. */
    protected function trendChart(User $user): array
    {
        $from = now()->subDays(self::TREND_DAYS - 1)->startOfDay();
        $days = collect(range(0, self::TREND_DAYS - 1))->map(fn ($i) => $from->copy()->addDays($i));

        $opened = $user->tickets()->where('created_at', '>=', $from)->pluck('created_at')
            ->countBy(fn (Carbon $d) => $d->toDateString());
        $closed = $user->tickets()->where('closed_at', '>=', $from)->pluck('closed_at')
            ->countBy(fn ($d) => Carbon::parse($d)->toDateString());

        return [
            'chart' => ['type' => 'area', 'height' => 280],
            'series' => [
                ['name' => 'Açılan', 'data' => $days->map(fn ($d) => $opened[$d->toDateString()] ?? 0)->all()],
                ['name' => 'Kapanan', 'data' => $days->map(fn ($d) => $closed[$d->toDateString()] ?? 0)->all()],
            ],
            'xaxis' => [
                'categories' => $days->map(fn ($d) => $d->locale('tr')->isoFormat('D MMM'))->all(),
                'axisBorder' => ['show' => false], 'axisTicks' => ['show' => false],
                'labels' => ['rotate' => 0, 'hideOverlappingLabels' => true],
            ],
            'yaxis' => ['min' => 0, 'forceNiceScale' => true, 'decimalsInFloat' => 0],
            'colors' => ['#6366f1', '#10b981'],
            'fill' => ['type' => 'gradient', 'gradient' => ['opacityFrom' => 0.35, 'opacityTo' => 0.02]],
        ];
    }

    /** Açık işlerin iş akışındaki dağılımı. */
    protected function statusChart(User $user): array
    {
        $counts = $user->tickets()->open()->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');
        $statuses = collect(TicketStatus::cases())->reject->isClosed()->values();
        $hex = [
            'received' => '#94a3b8', 'diagnosing' => '#0ea5e9', 'awaiting_approval' => '#f59e0b',
            'awaiting_parts' => '#f97316', 'in_repair' => '#6366f1', 'testing' => '#8b5cf6', 'ready' => '#10b981',
        ];

        return [
            'chart' => ['type' => 'donut', 'height' => 280],
            'series' => $statuses->map(fn ($s) => (int) ($counts[$s->value] ?? 0))->all(),
            'labels' => $statuses->map->label()->all(),
            'colors' => $statuses->map(fn ($s) => $hex[$s->value])->all(),
            'legend' => ['position' => 'bottom', 'horizontalAlign' => 'center'],
            'stroke' => ['width' => 0],
            'plotOptions' => ['pie' => ['donut' => ['size' => '68%', 'labels' => [
                'show' => true,
                'total' => ['show' => true, 'label' => 'Açık iş', 'fontWeight' => 700],
            ]]]],
        ];
    }

    /** Son 30 günde teknisyenlerin tamamladığı (teslime hazır / teslim) adımlar ve toplam işlem sayısı. */
    protected function technicianChart(User $user): array
    {
        $events = TicketEvent::query()
            ->whereHas('ticket', fn ($q) => $q->where('user_id', $user->id))
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('user_id')
            ->with('user:id,name')
            ->get(['id', 'user_id', 'type', 'to_status']);

        $byUser = $events->groupBy('user_id')->map(fn ($rows) => [
            'name' => $rows->first()->user?->name ?? 'Bilinmeyen',
            'done' => $rows->filter(fn ($e) => in_array($e->to_status, [TicketStatus::READY, TicketStatus::DELIVERED], true))->count(),
            'actions' => $rows->count(),
        ])->sortByDesc('done')->take(8)->values();

        return [
            'chart' => ['type' => 'bar', 'height' => max(200, 70 + $byUser->count() * 48)],
            'series' => [
                ['name' => 'Tamamlanan', 'data' => $byUser->pluck('done')->all()],
                ['name' => 'Toplam işlem', 'data' => $byUser->pluck('actions')->all()],
            ],
            'xaxis' => ['categories' => $byUser->pluck('name')->all()],
            'plotOptions' => ['bar' => ['horizontal' => true, 'borderRadius' => 4, 'barHeight' => '60%']],
            'colors' => ['#10b981', '#c7d2fe'],
        ];
    }

    /** En çok servise gelen markalar. */
    protected function brandChart(User $user): array
    {
        $brands = $user->tickets()->with('clientProduct.product:id,brand')->get(['id', 'client_product_id'])
            ->countBy(fn (Ticket $t) => $t->clientProduct?->product?->brand ?: 'Diğer')
            ->sortDesc()->take(6);

        return [
            'chart' => ['type' => 'bar', 'height' => 260],
            'series' => [['name' => 'Servis kaydı', 'data' => $brands->values()->all()]],
            'xaxis' => ['categories' => $brands->keys()->all(), 'axisBorder' => ['show' => false], 'axisTicks' => ['show' => false]],
            'plotOptions' => ['bar' => ['borderRadius' => 6, 'columnWidth' => '45%', 'distributed' => true]],
            'legend' => ['show' => false],
            'colors' => ['#0ea5e9', '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'],
        ];
    }
}
