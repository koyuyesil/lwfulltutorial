<?php

namespace App\Support;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

/**
 * Müşteri bildirim şablonları (WhatsApp / SMS / e-posta). Firma ayarlarından düzenlenir.
 * Yer tutucular: {ad} {kod} {cihaz} {durum} {link} {firma} {tutar} {tarih} {anket} {telefon}
 */
class NotificationTemplates
{
    public const PLACEHOLDERS = [
        '{ad}' => 'Müşterinin adı',
        '{kod}' => 'Takip kodu',
        '{cihaz}' => 'Marka model',
        '{durum}' => 'Güncel durum',
        '{link}' => 'Arıza takip bağlantısı',
        '{tutar}' => 'Teklif tutarı',
        '{tarih}' => 'Tahmini teslim tarihi',
        '{anket}' => 'Memnuniyet anketi bağlantısı',
        '{firma}' => 'Firma adı',
        '{telefon}' => 'Firma telefonu',
    ];

    /** @return array<string, string> durum → varsayılan metin */
    public static function defaults(): array
    {
        return [
            TicketStatus::RECEIVED->value => "Merhaba {ad}, {cihaz} cihazınız servisimize kabul edildi. Takip kodunuz: {kod}\nDurumu buradan izleyebilirsiniz: {link}\n{firma}",
            TicketStatus::DIAGNOSING->value => "Merhaba {ad}, {cihaz} cihazınızın incelemesine başladık. Gelişmeleri {link} adresinden takip edebilirsiniz.\n{firma}",
            TicketStatus::AWAITING_APPROVAL->value => "Merhaba {ad}, {cihaz} cihazınız için onarım teklifimiz: {tutar}.\nTeklifi incelemek ve onaylamak için: {link} (takip kodu {kod})\n{firma}",
            TicketStatus::AWAITING_PARTS->value => "Merhaba {ad}, {cihaz} cihazınız için gerekli parça siparişi verildi. Parça gelir gelmez onarıma başlayacağız. Tahmini teslim: {tarih}\n{firma}",
            TicketStatus::IN_REPAIR->value => "Merhaba {ad}, {cihaz} cihazınızın onarımına başlandı. Tahmini teslim: {tarih}\n{firma}",
            TicketStatus::TESTING->value => "Merhaba {ad}, {cihaz} cihazınızın onarımı tamamlandı, son testler yapılıyor.\n{firma}",
            TicketStatus::READY->value => "Merhaba {ad}, {cihaz} cihazınız hazır! 🎉 Servisimizden teslim alabilirsiniz. Takip kodu: {kod}\n{firma} · {telefon}",
            TicketStatus::DELIVERED->value => "Merhaba {ad}, {cihaz} cihazınızı teslim ettik, bizi tercih ettiğiniz için teşekkürler.\nDeneyiminizi 10 saniyede puanlar mısınız? {anket}\n{firma}",
            TicketStatus::CANCELLED->value => "Merhaba {ad}, {kod} numaralı servis kaydınız kapatıldı. Ayrıntı için bize ulaşabilirsiniz: {telefon}\n{firma}",
        ];
    }

    /** Firmanın düzenlediği (yoksa varsayılan) şablonlar */
    public static function all(?User $user): array
    {
        $saved = (array) ($user?->setting('notify.templates', []) ?? []);

        return array_replace(self::defaults(), array_filter($saved, fn ($v) => is_string($v) && trim($v) !== ''));
    }

    public static function for(Ticket $ticket, ?TicketStatus $status = null): string
    {
        $status ??= $ticket->status;

        return self::render($ticket, self::all($ticket->user)[$status->value] ?? '');
    }

    public static function render(Ticket $ticket, string $template): string
    {
        $ticket->loadMissing('clientProduct.client', 'clientProduct.product', 'user');
        $client = $ticket->clientProduct?->client;
        $product = $ticket->clientProduct?->product;
        $user = $ticket->user;

        $values = [
            '{ad}' => $client?->first_name ?: 'Değerli müşterimiz',
            '{kod}' => $ticket->tracking_code,
            '{cihaz}' => trim(($product?->brand ?? '').' '.($product?->model_name ?? '')) ?: 'cihaz',
            '{durum}' => $ticket->status->label(),
            '{link}' => $ticket->trackingUrl(),
            '{tutar}' => $ticket->quote_total !== null ? Ticket::money((float) $ticket->quote_total) : Ticket::money($ticket->loadMissing('items')->itemsTotal()),
            '{tarih}' => $ticket->due_at?->locale('tr')->translatedFormat('d F l') ?? '—',
            '{anket}' => $ticket->surveyUrl(),
            '{firma}' => $user?->companyName() ?? config('app.name'),
            '{telefon}' => (string) ($user?->setting('company.phone') ?? ''),
        ];

        return trim(strtr($template, $values));
    }

    /** Türkiye numarasını wa.me biçimine çevirir (05xx → 905xx). Geçersizse null. */
    public static function waNumber(?string $phone): ?string
    {
        $d = preg_replace('/\D/', '', (string) $phone);
        if ($d === '') {
            return null;
        }
        if (str_starts_with($d, '0')) {
            $d = '9'.$d;
        } elseif (strlen($d) === 10 && str_starts_with($d, '5')) {
            $d = '90'.$d;
        }

        return strlen($d) >= 11 ? $d : null;
    }
}
