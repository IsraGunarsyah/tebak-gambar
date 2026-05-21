<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bionic Tiger Quiz</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env("REVERB_APP_KEY") }}',
            wsHost: window.location.hostname,
            wsPort: {{ env("REVERB_SERVER_PORT", 8080) }},
            wssPort: {{ env("REVERB_SERVER_PORT", 8080) }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });
    </script>
</head>
<body class="bg-gray-900 text-white h-screen flex flex-col items-center justify-center overflow-hidden">

    <div id="join-screen" class="w-full max-w-md p-6 text-center transition-all duration-300">
        <h1 class="text-3xl font-bold text-yellow-500 mb-6">BIONIC TIGER 2026</h1>
        <input type="text" id="player_name" placeholder="Nama & Divisi (Misal: Budi - IT)"
               class="w-full p-4 rounded-lg text-black mb-4 focus:outline-none focus:ring-4 focus:ring-yellow-500">
        <button onclick="joinGame()" class="w-full bg-yellow-500 text-black font-bold text-xl py-4 rounded-lg active:scale-95 transition-transform">
            MASUK ROOM
        </button>
    </div>

    <div id="waiting-screen" class="hidden w-full p-6 text-center text-gray-400">
        <svg class="animate-spin h-12 w-12 mx-auto mb-4 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <h2 class="text-2xl font-semibold">Tunggu aba-aba MC...</h2>
        <p class="mt-2 text-sm">Perhatikan layar utama di depan!</p>
    </div>

    <div id="buzzer-screen" class="hidden w-full h-full flex items-center justify-center p-6 bg-blue-600">
        <button onclick="hitBuzzer()" id="btn-buzzer" class="w-64 h-64 bg-white text-blue-600 rounded-full text-4xl font-black shadow-2xl active:scale-90 transition-transform">
            TEBAK!
        </button>
    </div>

    <div id="manual-screen" class="hidden w-full p-6 text-center text-yellow-400">
        <h1 class="text-6xl mb-6">🙋‍♂️</h1>
        <h2 class="text-3xl font-bold uppercase leading-tight">Perhatikan Layar!<br>Siap-siap angkat tangan!</h2>
    </div>

    <div id="result-screen" class="hidden w-full h-full flex flex-col items-center justify-center p-6 text-center">
        <h1 id="result-title" class="text-5xl font-black mb-2 uppercase"></h1>
        <p id="result-message" class="text-xl"></p>
    </div>

    <script>
        let playerName = '';

        function switchScreen(screenId) {
            document.querySelectorAll('body > div').forEach(el => el.classList.add('hidden'));
            document.getElementById(screenId).classList.remove('hidden');
        }

        function joinGame() {
            playerName = document.getElementById('player_name').value;
            if(playerName.trim() === '') { alert('Tulis nama dulu ya!'); return; }

            let btn = document.querySelector('button[onclick="joinGame()"]');
            btn.innerText = "MEMUAT..."; btn.disabled = true;

            axios.post('/api/join-room', { player_name: playerName })
                 .then(() => switchScreen('waiting-screen'))
                 .catch(err => { console.error(err); btn.innerText = "MASUK ROOM"; btn.disabled = false; });
        }

        function hitBuzzer() {
            document.getElementById('btn-buzzer').innerText = '...';
            document.getElementById('btn-buzzer').disabled = true;

            axios.post('/api/hit-buzzer', { player_name: playerName })
                 .catch(error => console.error(error));
        }

        document.addEventListener('DOMContentLoaded', () => {
            Echo.channel('quiz-channel')
                .listen('GameStateChanged', (e) => {
                    if (playerName.trim() === '') return; // Abaikan jika belum masuk

                    document.getElementById('btn-buzzer').innerText = 'TEBAK!';
                    document.getElementById('btn-buzzer').disabled = false;

                    if (e.state === 'BUZZER_OPEN') switchScreen('buzzer-screen');
                    else if (e.state === 'MANUAL_MODE') switchScreen('manual-screen');
                    else if (e.state === 'STANDBY') switchScreen('waiting-screen');
                    else if (e.state === 'WE_HAVE_A_WINNER') {
                        switchScreen('result-screen');
                        let resultScreen = document.getElementById('result-screen');

                        if (e.data.winner === playerName) {
                            resultScreen.className = "w-full h-full flex flex-col items-center justify-center p-6 text-center bg-green-500 text-white";
                            document.getElementById('result-title').innerText = "ANDA TERCEPAT!";
                            document.getElementById('result-message').innerText = "Silakan bicara ke MC!";
                        } else {
                            resultScreen.className = "w-full h-full flex flex-col items-center justify-center p-6 text-center bg-red-600 text-white";
                            document.getElementById('result-title').innerText = "TERLAMBAT!";
                            document.getElementById('result-message').innerText = "Dikalahkan oleh: " + (e.data.winner || 'Sistem');
                        }
                    }
                });
        });
    </script>
</body>
</html>
