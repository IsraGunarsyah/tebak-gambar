<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bionic Tiger Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '61986c81fb9cda8437d6',
            cluster: 'ap1',
            forceTLS: true
        });
    </script>
    <style> body { font-family: 'Montserrat', sans-serif; background-color: #020105; } </style>
</head>
<body class="text-white h-screen flex flex-col items-center justify-center overflow-hidden">

    <div id="join-screen" class="w-full max-w-md p-6 text-center transition-all duration-300">
        <h1 class="text-4xl font-black text-yellow-500 mb-2">BIONIC TIGER</h1>
        <h2 class="text-xl font-bold text-gray-400 mb-8 tracking-widest">TRIVIA QUIZ</h2>
        <input type="text" id="player_name" placeholder="Nama & Divisi (Misal: Budi - IT)"
               class="w-full p-4 rounded-xl text-black mb-4 font-bold text-center focus:outline-none focus:ring-4 focus:ring-yellow-500 shadow-xl">
        <button onclick="joinGame()" class="w-full bg-yellow-500 text-black font-black text-2xl py-4 rounded-xl shadow-[0_5px_0_#b38000] active:translate-y-1 active:shadow-none transition-all">
            MASUK ROOM
        </button>
    </div>

    <div id="waiting-screen" class="hidden w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gray-900 border-8 border-red-900">
        <svg class="animate-spin h-16 w-16 mx-auto mb-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <h2 class="text-3xl font-black text-red-500 uppercase">Terkunci</h2>
        <p class="mt-2 text-lg text-gray-300 font-bold">Perhatikan Clue di Videotron!</p>
    </div>

    <div id="buzzer-screen" class="hidden w-full h-full flex items-center justify-center p-6 bg-blue-700">
        <button onclick="hitBuzzer()" id="btn-buzzer" class="w-72 h-72 bg-white text-blue-700 rounded-full text-5xl font-black shadow-[0_15px_0_#94a3b8] active:translate-y-4 active:shadow-none transition-all">
            TEBAK!
        </button>
    </div>

    <div id="manual-screen" class="hidden w-full h-full flex flex-col items-center justify-center p-6 text-center bg-yellow-500 text-black">
        <h1 class="text-8xl mb-6 animate-bounce">🙋‍♂️</h1>
        <h2 class="text-4xl font-black uppercase leading-tight">Siap-siap<br>Angkat Tangan!</h2>
    </div>

    <div id="result-screen" class="hidden w-full h-full flex flex-col items-center justify-center p-6 text-center">
        <h1 id="result-title" class="text-5xl font-black mb-4 uppercase"></h1>
        <p id="result-message" class="text-2xl font-bold"></p>
    </div>

    <script>
        let playerName = '';

        function switchScreen(screenId) {
            document.querySelectorAll('body > div').forEach(el => el.classList.add('hidden'));
            document.getElementById(screenId).classList.remove('hidden');
        }

        function joinGame() {
            playerName = document.getElementById('player_name').value;
            if(playerName.trim() === '') { alert('Tulis nama dulu ya Bosku!'); return; }

            let btn = document.querySelector('button[onclick="joinGame()"]');
            btn.innerText = "MEMUAT..."; btn.disabled = true;

            axios.post('/api/join-room', { player_name: playerName })
                 .then(() => switchScreen('waiting-screen'))
                 .catch(err => { console.error(err); btn.innerText = "MASUK ROOM"; btn.disabled = false; });
        }

        function hitBuzzer() {
            document.getElementById('btn-buzzer').innerText = '...';
            document.getElementById('btn-buzzer').disabled = true;
            axios.post('/api/hit-buzzer', { player_name: playerName }).catch(err => console.error(err));
        }

        document.addEventListener('DOMContentLoaded', () => {
            Echo.channel('quiz-channel').listen('.GameStateChanged', (e) => {
                if (playerName.trim() === '') return;

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
                        document.getElementById('result-title').innerText = "KAMU TERCEPAT!";
                        document.getElementById('result-message').innerText = "Tunjuk tangan dan bicara ke MC!";
                    } else {
                        resultScreen.className = "w-full h-full flex flex-col items-center justify-center p-6 text-center bg-red-600 text-white";
                        document.getElementById('result-title').innerText = "KEDULUAN!";
                        document.getElementById('result-message').innerText = "Diambil oleh: " + (e.data.winner || 'Sistem');
                    }
                }
            });
        });
    </script>
</body>
</html>
