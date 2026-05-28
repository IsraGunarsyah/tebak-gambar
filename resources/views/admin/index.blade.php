<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bionic Quiz</title>
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
</head>
<body class="bg-gray-100 p-10 font-sans">

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-3xl font-bold border-b pb-4 mb-6 text-gray-800">Panel Kendali MC / Operator BIGER</h1>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button onclick="setMode('STANDBY')" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-6 px-4 rounded-lg shadow">
                1. Kunci HP Peserta
                <span class="block text-sm font-normal mt-1">(Mode Standby)</span>
            </button>

            <button onclick="setMode('BUZZER_OPEN')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-6 px-4 rounded-lg shadow">
                2. Buka Buzzer!
                <span class="block text-sm font-normal mt-1">(Rebutan Dimulai)</span>
            </button>

            <button onclick="setMode('BUZZER_OPEN')" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-6 px-4 rounded-lg shadow ring-2 ring-purple-300">
                Ulangi Rebutan
                <span class="block text-sm font-normal mt-1">(Jika Jawaban Salah)</span>
            </button>

            <button onclick="setMode('MANUAL_MODE')" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-6 px-4 rounded-lg shadow">
                Mode Manual
                <span class="block text-sm font-normal mt-1">(Suruh Angkat Tangan)</span>
            </button>
        </div>

        <div class="mt-6 p-5 bg-green-50 rounded-xl border border-green-200 flex justify-between items-center shadow-inner">
            <div>
                <h3 class="text-xl font-bold text-green-800">Peserta di dalam Room</h3>
                <p class="text-sm text-green-600">Total perangkat yang sudah terhubung.</p>
                <button onclick="resetRoom()" class="mt-2 text-xs bg-red-100 text-red-600 px-3 py-1 rounded hover:bg-red-200">Kosongkan Room (Reset)</button>
            </div>
            <div class="text-6xl font-black text-green-600" id="player-count">0</div>
        </div>

        <div class="mt-8 p-6 bg-gray-800 text-white rounded-xl shadow-lg border-2 border-gray-700">
            <div class="flex justify-between items-center mb-6 border-b border-gray-600 pb-4">
                <h2 class="text-xl font-bold text-yellow-400">🖼️ Kontrol Layar Videotron</h2>

                <div class="flex items-center space-x-4">
                    <button onclick="gantiSoal(-1)" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded font-bold shadow">&laquo; Mundur</button>
                    <span id="label-soal" class="text-xl font-black text-white bg-gray-900 px-6 py-2 rounded-lg border border-gray-500 shadow-inner">Soal 1 / 10</span>
                    <button onclick="gantiSoal(1)" class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded font-bold shadow">Lanjut &raquo;</button>
                </div>
            </div>

            <p id="judul-karakter" class="text-center text-yellow-300 mb-4 font-black text-2xl">Jawaban: IRON MAN</p>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
                <button onclick="tembakVideotron(0)" class="col-span-full bg-gray-900 hover:bg-black py-4 rounded font-bold shadow-md border border-gray-600 text-gray-300">
                    Awal Soal (Tutup Semua Clue)
                </button>
                <button onclick="tembakVideotron(1)" class="bg-indigo-600 hover:bg-indigo-500 py-4 rounded font-bold shadow-md">Buka Clue 1 + Puzzle</button>
                <button onclick="tembakVideotron(2)" class="bg-indigo-600 hover:bg-indigo-500 py-4 rounded font-bold shadow-md">Buka Clue 2 + Puzzle</button>
                <button onclick="tembakVideotron(3)" class="bg-indigo-600 hover:bg-indigo-500 py-4 rounded font-bold shadow-md">Buka Clue 3 + Puzzle</button>
                <button onclick="tembakVideotron(4)" class="bg-indigo-600 hover:bg-indigo-500 py-4 rounded font-bold shadow-md">Buka Clue 4 + Puzzle</button>
                <button onclick="tembakVideotron(5)" class="col-span-2 bg-green-600 hover:bg-green-500 py-4 rounded font-bold shadow-md ring-2 ring-green-400 text-white text-xl">🎉 REVEAL JAWABAN BENAR!</button>
            </div>
        </div>
    </div>

    <script>
        const bankSoal = [
            { answer: "IRON MAN" }, { answer: "QUEEN ELSA" }, { answer: "SPIDER-MAN" },
            { answer: "BATMAN" }, { answer: "JACK SPARROW" }, { answer: "JOKER" },
            { answer: "DORAEMON" }, { answer: "HARRY POTTER" }, { answer: "OPTIMUS PRIME" },
            { answer: "WIRO SABLENG" }
        ];

        let indeksSoal = 0;

        function perbaruiLayarAdmin() {
            document.getElementById('label-soal').innerText = `Soal ${indeksSoal + 1} / ${bankSoal.length}`;
            document.getElementById('judul-karakter').innerText = "Jawaban: " + bankSoal[indeksSoal].answer;
        }

        function gantiSoal(arah) {
            let indeksBaru = indeksSoal + arah;
            if (indeksBaru >= 0 && indeksBaru < bankSoal.length) {
                indeksSoal = indeksBaru;
                perbaruiLayarAdmin();
                tembakVideotron(0); // Setel ulang layar ke tertutup
            } else {
                alert("Sudah di ujung soal!");
            }
        }

        // FUNGSI INTI: Kirim instruksi sinkronisasi ke Videotron & HP
        function tembakVideotron(step) {
            // 1. Tembak kode ke videotron (contoh: Q_0_STEP_1)
            let kodeState = `Q_${indeksSoal}_STEP_${step}`;
            axios.post('/api/set-mode', { mode: kodeState }).catch(err => console.error(err));

            // 2. Kendalikan HP Peserta secara otomatis berdasar langkahnya
            if (step >= 1 && step <= 4) {
                // Beri jeda 0.3 detik agar videotron berubah dulu, baru HP peserta buka tombol BUZZER
                setTimeout(() => setMode('BUZZER_OPEN'), 300);
            } else if (step === 5 || step === 0) {
                // Jika reveal jawaban atau tutup soal, kunci HP peserta
                setTimeout(() => setMode('STANDBY'), 300);
            }
        }

        function setMode(mode) {
            axios.post('/api/set-mode', { mode: mode }).catch(err => console.error(err));
        }

        function resetRoom() {
            if(confirm('Yakin ingin mereset jumlah peserta?')) {
                axios.post('/api/reset-players');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            perbaruiLayarAdmin();
            axios.get('/api/get-players').then(res => {
                document.getElementById('player-count').innerText = res.data.total;
            });
            Echo.channel('quiz-channel').listen('.GameStateChanged', (e) => {
                if (e.state === 'PLAYER_JOINED') {
                    document.getElementById('player-count').innerText = e.data.total;
                }
            });
        });
    </script>
</body>
</html>
