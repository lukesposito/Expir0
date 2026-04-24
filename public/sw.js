// public/sw.js


self.addEventListener('install', function(event) {
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    event.waitUntil(clients.claim());
});


// Ascolta gli eventi Push in arrivo dal server
self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if (e.data) {
        // Leggiamo il JSON inviato da Laravel
        var msg = e.data.json();
        
        e.waitUntil(self.registration.showNotification(msg.title, {
            body: msg.body,
            icon: msg.icon || '/images/logo.png', // Icona di default se non passata
            badge: '/images/logo.png', // Piccola icona monocromatica per Android (opzionale)
            data: msg.data || { url: '/' }, // Dati extra, come l'URL da aprire
            vibrate: [200, 100, 200] // Vibrazione del telefono
        }));
    }
});

// Cosa succede quando l'utente clicca fisicamente sulla notifica?
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    // Legge l'URL dai dati della notifica, se non c'è apre la home
    const urlToOpen = event.notification.data.url || '/';
    
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            // Se c'è già una scheda aperta del sito, mettila a fuoco
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            // Altrimenti apri una nuova scheda
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});