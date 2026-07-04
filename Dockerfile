# Usa l'immagine ufficiale PHP 8.3 con PHP-FPM
# PHP-FPM esegue Laravel dietro Nginx.
FROM php:8.3-fpm

# Installa le dipendenze di sistema necessarie al progetto:
# - git, curl: utili per Composer e operazioni generali
# - libpng-dev: necessaria per GD
# - libonig-dev: necessaria per mbstring
# - libxml2-dev: supporto XML
# - zip, unzip: gestione archivi
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installa le estensioni PHP necessarie per Laravel e il progetto:
# - pdo_mysql: connessione a MariaDB/MySQL
# - mbstring: gestione stringhe UTF-8
# - exif: metadata immagini
# - pcntl: utile per processi e worker
# - bcmath: calcoli numerici precisi
# - gd: gestione immagini
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Copia Composer dall'immagine ufficiale stabile.
# Composer v2 è più prevedibile rispetto a latest.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Imposta la directory di lavoro principale del container.
WORKDIR /var/www

# Crea gruppo e utente non-root per far girare l'applicazione.
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copia tutti i file del progetto dentro il container.
COPY . /var/www

# Assegna ownership del progetto all'utente applicativo.
RUN chown -R www:www /var/www

# Imposta i permessi corretti per le directory usate da Laravel
# per cache, log, sessioni e file runtime.
RUN chmod -R 775 /var/www/storage
RUN chmod -R 775 /var/www/bootstrap/cache

# Passa all'utente non privilegiato per eseguire i comandi applicativi.
USER www

# Crea automaticamente il link simbolico Laravel per rendere pubblici
# file presenti in storage/app/public.
# "|| true" evita che la build fallisca se il link esiste già.
RUN php artisan storage:link || true

# Espone la porta interna usata da PHP-FPM.
EXPOSE 9000

# Avvia PHP-FPM come processo principale del container.
CMD ["php-fpm"]