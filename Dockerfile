# Usa l'immagine ufficiale di PHP 8.2 (o 8.3)
FROM php:8.3-fpm

# Installa le dipendenze di sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    cron

# Pulisce la cache apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installa le estensioni PHP necessarie per Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Ottieni Composer dall'immagine ufficiale
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Imposta la directory di lavoro
WORKDIR /var/www

# Crea l'utente per far girare i comandi (per sicurezza)
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copia i file dell'applicazione
COPY . /var/www

# Imposta i permessi corretti per le cartelle di Laravel
RUN chown -R www:www /var/www
RUN chmod -R 775 /var/www/storage
RUN chmod -R 775 /var/www/bootstrap/cache

# Copia e imposta il Cron Job per le notifiche delle email!
RUN echo "* * * * * cd /var/www && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/laravel-cron
RUN chmod 0644 /etc/cron.d/laravel-cron
RUN crontab /etc/cron.d/laravel-cron

# Cambia utente corrente in 'www' (tranne per il cron che gira da root)
USER www

# Per vedere le immagini e pdf pubblici
RUN php artisan storage:link

# Esponi la porta 9000 e avvia il server PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]