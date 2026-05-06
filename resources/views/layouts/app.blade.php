<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gestionale Veicoli') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="manifest" href="/manifest.json">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <script src="https://cdn.tailwindcss.com"></script>
        <script type="module" src="https://unpkg.com/@lottiefiles/dotlottie-wc@latest/dist/dotlottie-wc.js"></script>
        
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Figtree', 'sans-serif'],
                        }
                    }
                }
            }
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- @webauthnScripts -->
    </head>
    <body class="font-sans antialiased bg-slate-50">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow-sm z-10 relative">
                    <div class="w-full px-4 sm:px-6 lg:px-8 py-4">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1 w-full">
                {{ $slot }}
            </main>
        </div>

        <!-- WIDGET AGENTE AI  -->
        <style>
            /* Dimensioni del Lottie Grande (con !important per forzare le dimensioni esatte) */
            #robot-box {
                width: 250px !important;
                height: 250px !important;
                min-height: 250px !important; /* Blocca l'altezza */
                flex-shrink: 0; /* Vieta lo schiacciamento */
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* Animazioni Chat */
            @keyframes slideInUp 
            { 
                from { 
                    opacity: 0; 
                    transform: translateY(10px); 
                } to { 
                    opacity: 1; 
                    transform: translateY(0); 
                } 
            }
            .msg-animate { 
                animation: slideInUp 0.3s ease-out forwards; 
            }
            
            /* Pallini che pensano */
            .typing-dot { 
                width: 6px; 
                height: 6px; 
                background: #6B7280; 
                border-radius: 50%; 
                display: inline-block; 
                animation: typingBounce 1.4s infinite ease-in-out both; 
            }
            .typing-dot:nth-child(1) { 
                animation-delay: -0.32s; 
            }
            .typing-dot:nth-child(2) { 
                animation-delay: -0.16s; 
            }
            @keyframes typingBounce {
                0%, 80%, 100% { 
                    transform: scale(0); 
                } 
                40% { 
                    transform: scale(1); 
                } 
            }

            /* Finestra Elastica */
            #ai-chat-window {
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                transform: translateY(60px); 
                opacity: 0; 
                pointer-events: none;
                visibility: hidden;
            }
            #ai-chat-window.chat-open { 
                transform: translateY(0); 
                opacity: 1; 
                pointer-events: auto; 
                visibility: visible;
            }

            /* Cursore Macchina da scrivere */
            .cursor-blink { 
                animation: blink 1s step-end infinite; 
                border-right: 2px solid #4F46E5; 
                padding-right: 4px; }
            @keyframes blink { 
                50% { 
                    border-color: transparent; 
                } 
            }

            /* Scrollbar */
            #chat-messages-view::-webkit-scrollbar { width: 5px; }
            #chat-messages-view::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }

            /* MEDIA QUERY MOBILE (Responsive) */
            @media (max-width: 640px) {
                #typewriter-text {
                    font-size: 13px !important;
                }
                /* Questo assicura che su mobile sia 200x200 */
                #robot-box {
                    width: 250px !important; 
                    height: 250px !important;
                }
            }
        </style>

        <!-- Bottone FAB (La GIF piccola classica) -->
        <button id="ai-chat-fab" class="hover:scale-110 transition-transform duration-300 flex items-center justify-center p-1" style="position: fixed; bottom: 20px; right: 20px; width: 65px; height: 65px; border-radius: 50%; background-color: white; border: 3px solid #4F46E5; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4); cursor: pointer; z-index: 9999;">
            <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f916/512.gif" alt="AI Bot" class="w-full h-full object-contain">
        </button>

        <!-- Finestra della Chat -->
       <div id="ai-chat-window" class="fixed bottom-20 right-4 sm:bottom-24 sm:right-5 w-[calc(100%-32px)] sm:w-[420px] h-[75vh] sm:h-[550px] max-h-[700px] bg-white rounded-2xl flex flex-col z-[9998] overflow-hidden border border-gray-200 shadow-2xl">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-blue-500 text-white px-5 py-3 flex justify-between items-center z-20 shadow-sm relative">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white rounded-full p-1 flex justify-center items-center">
                        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f916/512.gif" class="w-full h-full object-contain rounded-full">
                    </div>
                    <div>
                        <div class="font-bold text-[15px] leading-tight">Assistente AI</div>
                        <div class="text-xs opacity-90 flex items-center gap-1">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full"></span> Online
                        </div>
                    </div>
                </div>
                <button id="ai-chat-close" class="text-white hover:text-gray-200 text-2xl font-bold transition-transform hover:rotate-90">&times;</button>
            </div>

            <!-- Contenitore Centrale (Ospita le due viste) -->
            <div class="flex-1 relative bg-slate-50 overflow-hidden">
                <!-- VISTA 1: LA PRESENTAZIONE (Il Lottie Grande) -->
                <div id="presentation-view" class="absolute inset-0 flex flex-col items-center justify-center p-6 bg-white z-10 transition-opacity duration-500 opacity-100">
                    <!-- LA SCATOLA PROTETTIVA -->
                    <div id="robot-box">
                        <dotlottie-wc src="{{ asset('images/robot.lottie') }}" autoplay loop style="width: 100%; height: 100%;"></dotlottie-wc>
                    </div>
                    <div class="mt-4 h-24 w-full">
                        <p id="typewriter-text" class="text-center text-gray-700 text-[15px] font-medium leading-relaxed"></p>
                    </div>
                </div>

                <!-- VISTA 2: LA CHAT VERA E PROPRIA (Inizialmente Nascosta) -->
                <div id="chat-messages-view" class="absolute inset-0 p-5 overflow-y-auto flex-col gap-4 bg-slate-50 z-0 opacity-0 pointer-events-none transition-opacity duration-500 flex">
                    <!-- I messaggi andranno qui -->
                </div>
            </div>

            <!-- Area Input (Sempre visibile) -->
            <div class="p-4 border-t border-gray-200 bg-white flex gap-3 items-center relative z-20">
                <input type="text" id="ai-chat-input" placeholder="Chiedi qualcosa all'AI..." class="flex-1 px-4 py-3 bg-slate-50 border border-slate-300 rounded-full text-[14px] outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                <button id="ai-chat-send" class="w-11 h-11 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full flex items-center justify-center transition-transform hover:scale-105 shadow-md flex-shrink-0">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="-ml-0.5"><path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/></svg>
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- SCRIPT WIDGET (CON MEMORIA DI SESSIONE)    -->
        <!-- ========================================== -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fab = document.getElementById('ai-chat-fab');
            const chatWindow = document.getElementById('ai-chat-window');
            const closeBtn = document.getElementById('ai-chat-close');
            const sendBtn = document.getElementById('ai-chat-send');
            const inputField = document.getElementById('ai-chat-input');
            
            const presentationView = document.getElementById('presentation-view');
            const chatMessagesView = document.getElementById('chat-messages-view');
            const typewriterElement = document.getElementById('typewriter-text');

            const smallAvatarUrl = 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f916/512.gif';
            
            // --- 1. GESTIONE MEMORIA E LOGOUT ---
            // // Prendo l'ID utente attuale da Laravel. Se fa logout diventa 'guest'
            // const currentUserId = "{{ auth()->check() ? auth()->id() : 'guest' }}";
            // const savedUserId = sessionStorage.getItem('ai_chat_user');
            
            // // Se l'utente è cambiato, resetto tutta la memoria della chat!
            // if (savedUserId !== currentUserId) {
            //     sessionStorage.removeItem('ai_chat_history');
            //     sessionStorage.removeItem('ai_chat_state');
            //     sessionStorage.removeItem('ai_chat_open');
            //     sessionStorage.setItem('ai_chat_user', currentUserId);
            // }

            // Sfruttiamo l'ID di Sessione univoco di Laravel. Cambia ad ogni Login/Logout!
            const currentSessionId = "{{ session()->getId() }}";
            const savedSessionId = sessionStorage.getItem('ai_chat_session_id');
            
            // Se la sessione è cambiata (es. ti sei appena riloggato), amnesia totale!
            if (savedSessionId !== currentSessionId) {
                sessionStorage.removeItem('ai_chat_history');
                sessionStorage.removeItem('ai_chat_state');
                sessionStorage.removeItem('ai_chat_open');
                sessionStorage.setItem('ai_chat_session_id', currentSessionId);
            }

            // Carico i dati salvati in memoria (o metto i valori di default)
            let chatHistory = JSON.parse(sessionStorage.getItem('ai_chat_history')) || [];
            let chatState = sessionStorage.getItem('ai_chat_state') || 'presentation'; // 'presentation' o 'chat'
            let chatIsOpen = sessionStorage.getItem('ai_chat_open') === 'true';

            let presentationStarted = false;

            // Funzione per salvare le modifiche nel browser
            function saveState() {
                sessionStorage.setItem('ai_chat_history', JSON.stringify(chatHistory));
                sessionStorage.setItem('ai_chat_state', chatState);
                sessionStorage.setItem('ai_chat_open', chatWindow.classList.contains('chat-open'));
            }

            // --- 2. RIPRISTINO INTERFACCIA AL CARICAMENTO DELLA PAGINA ---
            if (chatIsOpen) {
                chatWindow.classList.add('chat-open');
            }

            if (chatState === 'chat') {
                // Se eravamo già in fase chat, nascondo il robot gigante istantaneamente
                presentationView.style.opacity = '0';
                presentationView.style.zIndex = '0';
                chatMessagesView.style.opacity = '1';
                chatMessagesView.style.zIndex = '10';
                chatMessagesView.style.pointerEvents = 'auto';
                presentationStarted = true;

                // Ristampo tutti i vecchi messaggi della conversazione
                chatHistory.forEach(msg => {
                    appendMessage(msg.text, msg.isUser, false); // false = non risalvarli di nuovo
                });
            } else if (chatIsOpen && !presentationStarted) {
                // Se è la primissima volta che apre, avvio la macchina da scrivere
                presentationStarted = true;
                typeWriterEffect("Ciao! Sono l'Agente AI del gestionale.\nPosso aiutarti a monitorare costi, scadenze e novità.\nScrivimi qui sotto!", typewriterElement);
            }

            // --- 3. EFFETTO MACCHINA DA SCRIVERE ---
            function typeWriterEffect(text, element) {
                element.innerHTML = '<span class="cursor-blink"></span>';
                const cursor = element.querySelector('.cursor-blink');
                let i = 0;
                function type() {
                    if (i < text.length) {
                        cursor.insertAdjacentText('beforebegin', text.charAt(i));
                        i++;
                        setTimeout(type, 35);
                    } else {
                        setTimeout(() => { if(cursor) cursor.style.display = 'none'; }, 2000);
                    }
                }
                setTimeout(type, 500);
            }

            // --- 4. CLICK SUI BOTTONI (Apertura/Chiusura) ---
            fab.addEventListener('click', () => {
                chatWindow.classList.toggle('chat-open');
                saveState(); // Memorizzo che ha aperto la tendina
                
                if(chatWindow.classList.contains('chat-open') && chatState === 'presentation' && !presentationStarted) {
                    presentationStarted = true;
                    typeWriterEffect("Ciao! Sono l'Agente AI del gestionale.\nPosso aiutarti a monitorare costi, scadenze e novità.\nScrivimi qui sotto!", typewriterElement);
                }
                
                if(chatWindow.classList.contains('chat-open')) {
                    setTimeout(() => inputField.focus(), 300);
                }
            });

            closeBtn.addEventListener('click', () => { 
                chatWindow.classList.remove('chat-open'); 
                saveState(); // Memorizzo che l'ha chiusa
            });

            // --- 5. LOGICA DEI MESSAGGI ---
            sendBtn.addEventListener('click', sendMessage);
            inputField.addEventListener('keypress', function (e) { if (e.key === 'Enter') sendMessage(); });

            function appendMessage(text, isUser, saveToMemory = true) {
                const wrapperDiv = document.createElement('div');
                wrapperDiv.className = 'msg-animate flex gap-3 max-w-[90%]';
                wrapperDiv.style.alignItems = 'flex-end';

                const bubbleDiv = document.createElement('div');
                bubbleDiv.className = 'px-4 py-3 text-[14px] leading-relaxed shadow-sm border border-slate-200';

                if (isUser) {
                    wrapperDiv.style.alignSelf = 'flex-end';
                    wrapperDiv.style.flexDirection = 'row-reverse';
                    bubbleDiv.style.backgroundColor = '#4F46E5';
                    bubbleDiv.style.color = 'white';
                    bubbleDiv.style.borderRadius = '16px 16px 4px 16px';
                    bubbleDiv.style.borderColor = '#4338CA';
                    bubbleDiv.innerText = text;
                    wrapperDiv.appendChild(bubbleDiv);
                } else {
                    wrapperDiv.style.alignSelf = 'flex-start';

                    const botAvatar = document.createElement('img');
                    botAvatar.src = smallAvatarUrl;
                    botAvatar.className = 'w-8 h-8 rounded-full bg-indigo-50 border border-indigo-100 object-contain flex-shrink-0';
                    wrapperDiv.appendChild(botAvatar);

                    bubbleDiv.style.backgroundColor = 'white';
                    bubbleDiv.style.color = '#1E293B';
                    bubbleDiv.style.borderRadius = '16px 16px 16px 4px';
                    
                    let formattedText = text.replace(/\n/g, '<br>');
                    formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    bubbleDiv.innerHTML = formattedText;
                    wrapperDiv.appendChild(bubbleDiv);
                }

                chatMessagesView.appendChild(wrapperDiv);
                setTimeout(() => { chatMessagesView.scrollTop = chatMessagesView.scrollHeight; }, 50);

                // Salvo il messaggio in memoria solo se è un messaggio "nuovo"
                if (saveToMemory) {
                    chatHistory.push({ text: text, isUser: isUser });
                    saveState();
                }
            }

            function sendMessage() {
                const text = inputField.value.trim();
                if (!text) return;

                // Transizione: Addio presentazione, entra la chat!
                if (chatState === 'presentation') {
                    presentationView.style.opacity = '0';
                    presentationView.style.zIndex = '0';
                    
                    chatMessagesView.style.opacity = '1';
                    chatMessagesView.style.zIndex = '10';
                    chatMessagesView.style.pointerEvents = 'auto';
                    
                    chatState = 'chat';
                    saveState();
                }

                appendMessage(text, true);
                inputField.value = '';

                // Aggiungo i pallini di caricamento (non vengono salvati in memoria)
                const loadingId = 'loading-' + Date.now();
                const loadingWrapper = document.createElement('div');
                loadingWrapper.id = loadingId;
                loadingWrapper.className = 'msg-animate flex gap-3 max-w-[90%] items-end self-start';
                loadingWrapper.innerHTML = `
                    <img src="${smallAvatarUrl}" class="w-8 h-8 rounded-full bg-indigo-50 border border-indigo-100 object-contain flex-shrink-0">
                    <div class="bg-white px-4 py-3 rounded-[16px_16px_16px_4px] border border-slate-200 shadow-sm flex gap-1 items-center h-[46px]">
                        <span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>
                    </div>
                `;
                chatMessagesView.appendChild(loadingWrapper);
                chatMessagesView.scrollTop = chatMessagesView.scrollHeight;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('/ai-assistant/ask', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ prompt: text })
                })
                .then(response => {
                    if (response.status === 429) throw new Error("Hai superato il limite di domande gratuite per oggi. Torna domani!");
                    if (!response.ok) throw new Error("Errore di connessione.");
                    return response.json();
                })
                .then(data => {
                    document.getElementById(loadingId).remove();
                    if(data.status === 'success') appendMessage(data.response, false);
                    else appendMessage("Mmh, si è verificato un errore.", false);
                })
                .catch(error => {
                    document.getElementById(loadingId).remove();
                    appendMessage("⚠️ " + error.message, false); 
                });
            }
        });
        </script>
    </body>