# Klonlanan Repository için Gereklilikler

## PHP Require Hatası Alıyorsanız

PHP ile ilgili bağımlılık eksikliği hatası alıyorsanız:

```bash
composer install
# veya
composer update
```

## 500 Sunucu Hatası Alıyorsanız

Sunucu yapılandırmasıyla ilgili hata alıyorsanız, `.env` dosyasını oluşturun.

```bash
cp .env.example .env
```
```cmd
copy .env.example .env
```

## "No Application Encryption Key Has Been Specified" Hatası Alıyorsanız

Uygulama anahtarı eksik veya yanlışsa, yeni bir anahtar oluşturun.

```bash
php artisan key:generate
```

## "Database File at Path" Hatası Alıyorsanız

Veritabanı dosyasını oluşturmak için aşağıdaki komutu çalıştırın.

```bash
php artisan migrate --seed
```

## "Vite manifest not found at:..." Hatası Alıyorsanız

front-end yapılandırmalarını tamamlamak için aşağıdaki komutları çalıştırın.

```bash
npm install
npm run build
npm run dev
```


## "last changes add gemni api key required"
```bash
composer dump-autoload
```
npm packages
"dompurify": "^3.2.3",
"html2canvas": "^1.4.1",
"jspdf": "^2.5.2",
"mathjax": "^3.2.2"
