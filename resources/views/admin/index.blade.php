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
<body class="bg-gray-100 p-10 font-sans">

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-3xl font-bold border-b pb-4 mb-6 text-gray-800">Panel Kendali MC / Operator</h1>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button onclick="setMode('STANDBY')" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-6 px-4 rounded-lg shadow">
                1. Mode Standby
                <span class="block text-sm font-normal mt-1">(Peserta Menunggu)</span>
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
                <p class="text-sm text-green-600">Total perangkat yang sudah memasukkan nama.</p>
                <button onclick="resetRoom()" class="mt-2 text-xs bg-red-100 text-red-600 px-3 py-1 rounded hover:bg-red-200">Kosongkan Room (Reset)</button>
            </div>
            <div class="text-6xl font-black text-green-600" id="player-count">0</div>
        </div>

        <div class="mt-8 p-6 bg-gray-800 text-white rounded-xl shadow-lg border-2 border-gray-700">
            <div class="flex justify-between items-center mb-6 border-b border-gray-600 pb-4">
                <h2 class="text-xl font-bold text-yellow-400">🖼️ Kontrol Layar Videotron</h2>

                <div class="flex items-center space-x-4">
                    <button onclick="gantiSoal(-1)" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded font-bold shadow">&laquo; Mundur</button>
                    <span id="label-soal" class="text-xl font-black text-white bg-gray-900 px-6 py-2 rounded-lg border border-gray-500 shadow-inner">Soal 1 / 5</span>
                    <button onclick="gantiSoal(1)" class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded font-bold shadow">Lanjut &raquo;</button>
                </div>
            </div>

            <p id="judul-karakter" class="text-center text-gray-300 mb-4 font-semibold text-lg">Karakter: Spider-Man</p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <button onclick="tampilkanClue('clue1')" class="bg-gray-600 hover:bg-gray-500 py-4 rounded font-bold shadow-md">1. Tampilkan Clue 1</button>
                <button onclick="tampilkanClue('clue2')" class="bg-gray-600 hover:bg-gray-500 py-4 rounded font-bold shadow-md">2. Tampilkan Clue 2</button>
                <button onclick="tampilkanClue('siluet')" class="bg-gray-600 hover:bg-gray-500 py-4 rounded font-bold shadow-md">3. Tampilkan Siluet</button>
                <button onclick="tampilkanClue('jawaban')" class="bg-green-600 hover:bg-green-500 py-4 rounded font-bold shadow-md ring-2 ring-green-400 text-white">4. Buka JAWABAN!</button>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-600 text-right">
                <button onclick="showImage('')" class="text-red-400 hover:text-red-300 underline font-bold">Tutup Gambar (Kembali ke Layar Hitam)</button>
            </div>
        </div>
    </div>

    <script>
        // ==========================================
        // 1. BANK SOAL (Silakan ganti nama file gambar di sini nanti)
        // ==========================================
        const bankSoal = [
            {
                judul: "Karakter: Iron Man",
                clue1: "/images/ironman_1.jpg",
                clue2: "/images/ironman_2.jpg",
                siluet: "/images/ironman_siluet.jpg",
                jawaban: "/images/ironman_asli.jpg"
            },
            {
                judul: "Karakter: Spider-Man",
                clue1: "/images/spidey_1.jpg",
                clue2: "/images/spidey_2.jpg",
                siluet: "/images/spidey_siluet.jpg",
                jawaban: "/images/spidey_asli.jpg"
            },
            {
                judul: "Karakter: Batman",
                clue1: "/images/batman_1.jpg",
                clue2: "/images/batman_2.jpg",
                siluet: "/images/batman_siluet.jpg",
                jawaban: "/images/batman_asli.jpg"
            }
        ];

        let indeksSoal = 0;

        function perbaruiLayarAdmin() {
            document.getElementById('label-soal').innerText = `Soal ${indeksSoal + 1} / ${bankSoal.length}`;
            document.getElementById('judul-karakter').innerText = bankSoal[indeksSoal].judul;
        }

        function gantiSoal(arah) {
            let indeksBaru = indeksSoal + arah;
            if (indeksBaru >= 0 && indeksBaru < bankSoal.length) {
                indeksSoal = indeksBaru;
                perbaruiLayarAdmin();
                showImage(''); // Kosongkan layar videotron saat pindah soal
            } else {
                alert("Sudah mentok di ujung soal!");
            }
        }

        function tampilkanClue(jenisClue) {
            // 1. Kirim gambar Clue ke layar Videotron
            let urlGambar = bankSoal[indeksSoal][jenisClue];
            showImage(urlGambar);

            // 2. OTOMATIS BUKA BUZZER
            if (jenisClue !== 'jawaban') {
                // Jika yang ditekan adalah Clue 1, Clue 2, atau Siluet:
                // Langsung munculkan tombol TEBAK di layar HP peserta
                setMode('BUZZER_OPEN');
            } else {
                // Jika yang ditekan adalah tombol "Buka JAWABAN":
                // Sesi tebak-tebakan soal ini selesai, kembalikan HP peserta ke mode "Tunggu aba-aba"
                setMode('STANDBY');
            }
        }

        // ==========================================
        // 2. FUNGSI API CONTROLLER
        // ==========================================
        function setMode(mode) {
            axios.post('/api/set-mode', { mode: mode }).catch(err => console.error(err));
        }

        function resetRoom() {
            if(confirm('Yakin ingin mereset semua jumlah peserta ke 0?')) {
                axios.post('/api/reset-players');
            }
        }

        function showImage(url) {
            axios.post('/api/show-image', { image_url: url }).catch(err => console.error(err));
        }

        // ==========================================
        // 3. STARTUP & WEBSOCKET LISTENER
        // ==========================================
        document.addEventListener('DOMContentLoaded', () => {
            // Set teks bank soal
            perbaruiLayarAdmin();

            // Ambil data jumlah peserta saat ini
            axios.get('/api/get-players').then(res => {
                document.getElementById('player-count').innerText = res.data.total;
            });

            // Dengarkan jika ada peserta baru masuk
            Echo.channel('quiz-channel').listen('GameStateChanged', (e) => {
                if (e.state === 'PLAYER_JOINED') {
                    let countElement = document.getElementById('player-count');
                    countElement.innerText = e.data.total;
                    countElement.classList.add('opacity-50');
                    setTimeout(() => countElement.classList.remove('opacity-50'), 200);
                }
            });
        });
    </script>
</body>
</html>
