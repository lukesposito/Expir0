# EXPIR0 (su expir0.it)

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)
![Laravel](https://img.shields.io/badge/Laravel-10.x%2F11.x-FF2D20?style=flat-square&logo=laravel)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker)
![MariaDB](https://img.shields.io/badge/MariaDB-10.11-003545?style=flat-square&logo=mariadb)
![Security](https://img.shields.io/badge/WebAuthn-Passkey-success?style=flat-square)

Un'applicazione web enterprise sviluppata in Laravel per la centralizzazione, il monitoraggio e la notifica automatizzata di **qualsiasi tipologia di scadenza**. 

Nato dall'esigenza di gestire date critiche senza vincoli di categoria, il sistema si adatta al tracciamento di asset fisici (veicoli, immobili), documentazione (patenti, passaporti), contrattualistica, polizze assicurative, abbonamenti software e adempimenti fiscali. Progettato con un'architettura containerizzata pronta per la produzione, pone un forte accento sulla sicurezza dei dati e sull'affidabilità delle notifiche asincrone.

## ⚙️ Funzionalità Principali (MVP)

* **Gestione Agnostica delle Scadenze:** Motore flessibile che permette l'inserimento e la categorizzazione di eventi a termine di qualsiasi natura.
* **Sistema di Alert Proattivo:** Worker in background dedicato che valuta quotidianamente le scadenze imminenti e invia notifiche email automatizzate agli utenti interessati.
* **Autenticazione Biometrica (Passwordless):** Implementazione dello standard **WebAuthn / Passkey**. Consente l'accesso tramite riconoscimento facciale (FaceID/Windows Hello) o impronta digitale, azzerando i rischi legati al furto di credenziali.
* **Dashboard Analitica:** Interfaccia utente pulita per una rapida visualizzazione dello stato delle scadenze (in regola, in scadenza, scadute).

## 🏗️ Architettura e Infrastruttura

Il progetto non è solo un'applicazione, ma un'infrastruttura completa definita via codice (`Infrastructure as Code`) tramite **Docker** e **Docker Compose**. 
L'ambiente di produzione è composto dai seguenti container isolati:

* `app`: Motore PHP-FPM contenente il core Laravel. Eseguito con privilegi ridotti (utente non-root) per la massima sicurezza.
* `web`: Server web **Nginx** (basato su Alpine Linux). Agisce da reverse proxy, forza il reindirizzamento da HTTP a HTTPS e gestisce i certificati SSL.
* `db`: Database **MariaDB 10.11**. Deliberatamente non esposto alla rete pubblica, in ascolto esclusivo sull'interfaccia di loopback (`127.0.0.1`).
* `scheduler`: Un worker containerizzato che esegue in modo continuo `php artisan schedule:work`, garantendo l'esecuzione dei task programmati senza alcuna dipendenza dal demone cron del sistema operativo host.

## 🔒 Sicurezza

La sicurezza è un pilastro fondamentale di questo progetto, implementata su più livelli:

1. **Traffico Criptato:** Supporto nativo per certificati Let's Encrypt tramite Certbot, garantendo la crittografia TLS (HTTPS) end-to-end.
2. **Isolamento del Database:** Il DB MariaDB rifiuta connessioni esterne. L'accesso per la manutenzione o l'ispezione dei dati (es. tramite HeidiSQL/DBeaver) è possibile esclusivamente stabilendo un **Tunnel SSH** cifrato verso il server host.
3. **Sessioni e Accessi Sicuri:** Utilizzo di driver di sessione sicuri e adozione dello standard FIDO2 per l'accesso tramite Passkey, bloccando alla radice gli attacchi di phishing e brute-force.

## 🚀 Requisiti e Installazione

### Prerequisiti
* Docker e Docker Compose installati sul sistema host.
* (Solo per Produzione) Un dominio registrato e puntato all'IP del server per la generazione dei certificati SSL.

## Setup Ambiente (Sviluppo / Produzione)

### 1. Clonare il repository:
   ```bash
   git clone [https://github.com/tuo-username/nome-repo.git](https://github.com/tuo-username/nome-repo.git)
   cd nome-repo
   ```

### 2. Copiare il file di configurazione ambientale e impostare le variabili (inclusi i parametri SMTP per le mail):
   ```bash
   cp .env.example .env
   ```

### 3.  Avvio in Ambiente Isolato tramite Docker

Il progetto è definito via codice (`Infrastructure as Code`) tramite **Docker Compose**, garantendo parità assoluta tra lo sviluppo e il server in produzione (VPS OVH). I container isolati includono Nginx, PHP-FPM, MariaDB e un container Scheduler dedicato.

**Installazione Docker**
Se Docker non è presente sul sistema:
```bash
# (Esempio per Ubuntu/WSL)
sudo apt install docker.io docker-compose -y
sudo usermod -aG docker $USER
```

**Build e Avvio dell'Infrastruttura**
Posizionati nella cartella contenente il file `docker-compose.yaml` (o `docker/`) e avvia i container:
```bash
docker-compose up -d --build
```

**Comandi all'interno del Container**
Per eseguire operazioni su Laravel (migrazioni, test, tinker), "entra" nel container dell'applicazione:
```bash
docker exec -it scadenze_app php artisan migrate
docker exec -it scadenze_app php artisan storage:link
docker exec -it scadenze_app php artisan tinker
```

### 4. Installare le dipendenze PHP ed eseguire le migrazioni del database (FACOLTATIVO):
   ```bash
   docker exec -it scadenze_app composer install --no-dev --optimize-autoloader
   docker exec -it scadenze_app php artisan key:generate
   docker exec -it scadenze_app php artisan migrate --seed
   docker exec -it scadenze_app php artisan storage:link
   ```

## 📧 Gestione Notifiche Email (Testing)

Le notifiche sono gestite automaticamente dal container `scheduler` che esegue in background `php artisan schedule:work`. 

Per testare l'invio delle mail (sia in locale che in produzione) e verificare la coda delle scadenze, puoi lanciare il comando forzato:
```bash
# Se usi il server locale PHP:
php artisan app:send-deadline-reminders

# Se stai usando Docker:
docker exec -it scadenze_app php artisan app:send-deadline-reminders
```

## N.B.: Se in locale sul WSL usare questo comando per gestire autenticazione con PAsskey/Authenticator app:
```bash
# Avvia il server rendendolo accessibile in locale
php artisan serve --host=localhost
```
