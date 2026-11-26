<?php

namespace App\Support;

use App\Models\BoardId;
use App\Models\Client;
use App\Models\Part;
use App\Models\Product;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Çöp kutusu: geri alınabilir silinen kayıtların türleri, etiketleri ve geri yükleme / kalıcı silme.
 * Yalnızca "kök" kayıtlar listelenir: üst kaydıyla birlikte silinmiş alt kayıtlar (ör. müşteriyle silinen
 * servis kayıtları) üst kayıt geri alınınca kendiliğinden döner.
 */
class Trash
{
    /** Çöp kutusunda saklama süresi (gün); sonra model:prune kalıcı siler. */
    public const RETENTION_DAYS = 30;

    /** tür => [model, tekil etiket, emoji] */
    public const TYPES = [
        'ticket' => [Ticket::class, 'Servis kaydı', '🛠️'],
        'client' => [Client::class, 'Müşteri', '👤'],
        'product' => [Product::class, 'Cihaz modeli', '📱'],
        'part' => [Part::class, 'Parça', '📦'],
        'task' => [Task::class, 'Görev', '✅'],
        'board' => [BoardId::class, 'Board ID', '🔌'],
    ];

    public static function typeOf(Model $model): string
    {
        foreach (self::TYPES as $type => [$class]) {
            if ($model instanceof $class) {
                return $type;
            }
        }

        throw new \InvalidArgumentException('Çöp kutusunda desteklenmeyen model: '.$model::class);
    }

    /** Kullanıcının çöpteki kök kayıtları (üst kaydı da çöpte olanlar hariç). */
    public static function query(User $user, string $type): Builder
    {
        [$class] = self::TYPES[$type] ?? abort(404);

        return $class::onlyTrashed()
            ->where('user_id', $user->id)
            ->when($type === 'ticket', fn (Builder $q) => $q->whereHas('clientProduct', fn (Builder $d) => $d->whereHas('client')->whereHas('product')))
            ->when($type === 'board', fn (Builder $q) => $q->whereHas('product'));
    }

    public static function find(User $user, string $type, int $id): Model
    {
        return self::query($user, $type)->findOrFail($id);
    }

    public static function restore(User $user, string $type, int $id): Model
    {
        $model = self::find($user, $type, $id);
        $model->restore();

        return $model;
    }

    public static function forceDelete(User $user, string $type, int $id): void
    {
        self::find($user, $type, $id)->forceDelete();
    }

    /** Tüm türlerdeki çöp: [tür => sayı] */
    public static function counts(User $user): array
    {
        return collect(array_keys(self::TYPES))->mapWithKeys(fn ($t) => [$t => self::query($user, $t)->count()])->all();
    }

    /** Kaydın kullanıcıya gösterilecek adı. */
    public static function title(Model $model): string
    {
        return match (true) {
            $model instanceof Ticket => trim(($model->tracking_code ?? '#'.$model->id).' · '.str($model->problem ?? '')->limit(40)),
            $model instanceof Client => $model->full_name,
            $model instanceof Product => $model->display_name,
            $model instanceof Part => $model->label(),
            $model instanceof Task => (string) $model->title,
            $model instanceof BoardId => (string) $model->build_name,
            default => '#'.$model->getKey(),
        };
    }

    public static function label(string $type): string
    {
        return self::TYPES[$type][1] ?? $type;
    }
}
