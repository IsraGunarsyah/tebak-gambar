<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videotron Display</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '61986c81fb9cda8437d6', // Kunci Pusher Anda
            cluster: 'ap1',
            forceTLS: true
        });
    </script>
</head>
<body class="bg-black text-white h-screen flex flex-col items-center justify-center overflow-hidden relative">

    <div id="image-container" class="hidden w-full max-w-5xl flex justify-center mt-10">
        <img id="character-img" src="" class="max-h-[60vh] object-contain rounded-xl border-4 border-yellow-500 shadow-[0_0_50px_rgba(234,179,8,0.5)]">
    </div>

    <h1 id="status-text" class="text-6xl font-bold text-gray-400 uppercase tracking-widest text-center mt-10">
        BERSIAPLAH!
    </h1>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Echo.channel('quiz-channel').listen('.GameStateChanged', (e) => {
                let statusText = document.getElementById('status-text');
                let imgContainer = document.getElementById('image-container');
                let charImg = document.getElementById('character-img');

                // Logika ganti gambar
                if (e.state === 'SHOW_IMAGE') {
                    if (e.data.url === '') {
                        imgContainer.classList.add('hidden'); // Sembunyikan gambar jika URL kosong
                    } else {
                        charImg.src = e.data.url;
                        imgContainer.classList.remove('hidden'); // Tampilkan gambar
                    }
                }

                // Logika teks status
                if (e.state === 'BUZZER_OPEN') {
                    statusText.innerText = "TEBAK SEKARANG!";
                    statusText.className = "text-6xl font-bold text-blue-500 uppercase tracking-widest text-center animate-pulse mt-10";
                }
                else if (e.state === 'MANUAL_MODE') {
                    statusText.innerText = "ANGKAT TANGANMU!";
                    statusText.className = "text-6xl font-bold text-yellow-500 uppercase tracking-widest text-center mt-10";
                }
                else if (e.state === 'STANDBY') {
                    statusText.innerText = "BERSIAPLAH!";
                    statusText.className = "text-6xl font-bold text-gray-400 uppercase tracking-widest text-center mt-10";
                    imgContainer.classList.add('hidden'); // Sembunyikan gambar saat standby
                }
                else if (e.state === 'WE_HAVE_A_WINNER') {
                    statusText.innerHTML = "PEMENANG TERCEPAT:<br><span class='text-8xl text-green-500'>" + e.data.winner + "</span>";
                    statusText.className = "text-5xl font-bold text-white uppercase tracking-widest text-center animate-bounce mt-10";
                }
            });
        });
    </script>
</body>
</html>
