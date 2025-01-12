<?php

namespace App\Models;

use App\Models\User;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientProduct extends Model
{
    /** @use HasFactory<\Database\Factories\ClientProductFactory> */
    use HasFactory;
    protected $fillable = ['user_id', 'product_id', 'client_id', 'serial', 'imei', 'color'];
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

     // Tablo işlemi yapıldığında çağrılır
     protected static function booted()
     {
         //Silme işlemi yapıldığında çağrılır
         static::deleting(function ($clientProduct) {
             // Config dosyasından stratejiyi al, yoksa 'delete' varsayılanını kullan
             $strategy = config('tickets.deletion_strategy', 'delete');//TODO config

             switch ($strategy) {
                 case 'delete':
                     // Cihazları tamamen sil
                     $clientProduct->tickets()->delete();
                     break;

                 case 'reassign':
                     // Cihazları başka bir kullanıcıya ata
                     $defaultUserId = config('client_product.default_user_id');
                     $clientProduct->tickets()->update(['user_id' => $defaultUserId]);
                     break;

                 case 'archive':
                     // Cihazları arşiv tablosuna taşı
                     foreach ($clientProduct->tickets as $ticket) {
                         DB::table('archived_tickets')->insert($ticket->toArray());
                         $ticket->delete(); // Orijinal cihazı sil
                     }
                     break;

                 default:
                     throw new \Exception("Unknown deletion strategy: $strategy");
             }
         });
     }

}
