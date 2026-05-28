<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videotron - Bionic Tiger Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { width: 100vw; height: 100vh; overflow: hidden; background-color: #020105; font-family: 'Montserrat', sans-serif; }
        .stage-container { width: 100vw; height: 100vh; position: relative; background: radial-gradient(circle at center, #160726 0%, #020105 100%); display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding-top: 4vh; overflow: hidden; }
        #quiz-bg-canvas { position: absolute; inset: 0; z-index: 2; pointer-events: none; }

        .flash-overlay { position: absolute; inset: 0; background: #fff; z-index: 100; opacity: 0; pointer-events: none; }
        .trigger-flash { animation: flashBoom 0.5s ease-out forwards; }
        @keyframes flashBoom { 0% { opacity: 1; } 100% { opacity: 0; } }

        /* HEADER */
        .game-header { z-index: 10; text-align: center; margin-bottom: 3vh; }
        .game-title { font-family: 'Cinzel', serif; font-size: 3vw; font-weight: 900; background: linear-gradient(to right, #ffcc00, #ffffff, #ffaa00); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 0.5vw; text-transform: uppercase; filter: drop-shadow(0 0 15px rgba(255, 204, 0, 0.4)); }
        .question-counter { font-size: 1.6vw; color: #ffffff; font-weight: 700; background: rgba(255, 204, 0, 0.15); border: 2px solid #ffcc00; padding: 0.5vh 3vw; border-radius: 50px; margin-top: 1vh; display: inline-block; letter-spacing: 0.2vw; }

        /* GRID MAIN */
        .quiz-main-grid { width: 92vw; height: 68vh; display: flex; align-items: center; justify-content: center; gap: 4vw; z-index: 10; transition: opacity 0.5s ease; }
        .photo-mystery-frame { width: 38vw; height: 60vh; border: 5px solid #ffcc00; border-image: linear-gradient(to bottom, #ffffff, #ffcc00, #b38000) 1; background-color: #080312; box-shadow: 0 0 35px rgba(255, 204, 0, 0.3), inset 0 0 20px rgba(0,0,0,0.8); position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; }

        /* FIX: Ubah object-fit menjadi contain agar gambar tidak terpotong */
        .photo-mystery-frame img { width: 100%; height: 100%; object-fit: contain; transition: all 0.5s ease; }

        .puzzle-grid { position: absolute; inset: 0; display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; z-index: 5; }
        .puzzle-piece { background: #0d061a; border: 1px solid rgba(255, 204, 0, 0.4); display: flex; align-items: center; justify-content: center; font-size: 4vw; color: #ffcc00; font-weight: 900; font-family: 'Cinzel', serif; transition: all 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275); background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,204,0,0.05) 10px, rgba(255,204,0,0.05) 20px); }
        .puzzle-piece.revealed { opacity: 0; transform: scale(0.5) rotate(15deg); pointer-events: none; }

        /* CLUES & ANSWER */
        .clues-panel { width: 48vw; height: 60vh; display: flex; flex-direction: column; justify-content: space-between; }
        .clues-box-container { display: grid; grid-template-columns: 1fr; gap: 1.8vh; width: 100%; }
        .clue-card { background: rgba(15, 6, 28, 0.75); border: 2px solid rgba(255, 204, 0, 0.15); border-radius: 8px; padding: 1.8vh 2vw; color: #ffffff; font-size: 1.5vw; font-weight: 500; text-align: left; display: flex; align-items: center; gap: 1.5vw; opacity: 0; transform: translateX(50px); transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .clue-card.reveal-active { opacity: 1; transform: translateX(0); border-color: #ffcc00; background: rgba(36, 15, 64, 0.5); box-shadow: 0 0 15px rgba(255, 204, 0, 0.2); }
        .clue-number { background: #ffcc00; color: #000; font-weight: 900; width: 2.5vw; height: 2.5vw; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 1.3vw; flex-shrink: 0; }

        .answer-container { width: 100%; height: 11vh; position: relative; }
        .answer-box { position: absolute; inset: 0; background: linear-gradient(135deg, #b38000, #ffcc00, #ffe680); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #000000; font-size: 3vw; font-weight: 900; text-transform: uppercase; letter-spacing: 0.3vw; box-shadow: 0 10px 30px rgba(255, 204, 0, 0.4); opacity: 0; transform: scale(0.7); filter: blur(5px); transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.4); }
        .answer-box.reveal-active { opacity: 1; transform: scale(1); filter: blur(0); animation: answerImpact 0.4s ease-out; }
        @keyframes answerImpact { 0% { transform: scale(1.4); filter: brightness(3); } 100% { transform: scale(1); filter: brightness(1); } }

        /* OVERLAY PEMENANG TERCEPAT */
        #winner-overlay { position: absolute; inset: 0; z-index: 150; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.5s; }
        #winner-overlay.show { opacity: 1; }
        .winner-title { font-size: 2.5vw; color: #fff; font-weight: 700; margin-bottom: 2vh; letter-spacing: 0.5vw; }
        .winner-name { font-family: 'Cinzel', serif; font-size: 8vw; color: #00ffaa; font-weight: 900; text-transform: uppercase; text-shadow: 0 0 40px rgba(0, 255, 170, 0.6); line-height: 1; text-align: center; }
    </style>

    <script>
        window.Echo = new Echo({ broadcaster: 'pusher', key: '61986c81fb9cda8437d6', cluster: 'ap1', forceTLS: true });
    </script>
</head>
<body>

    <div class="flash-overlay" id="flash-overlay"></div>

    <div id="winner-overlay">
        <div class="winner-title">TEBAKAN TERCEPAT OLEH:</div>
        <div class="winner-name" id="winner-name-display">BUDI - IT</div>
    </div>

    <div class="stage-container">
        <canvas id="quiz-bg-canvas"></canvas>

        <div class="game-header">
            <div class="game-title">Guess The Character!</div>
            <div class="question-counter" id="q-counter">ROUND 1 / 10</div>
        </div>

        <div class="quiz-main-grid">
            <div class="photo-mystery-frame">
                <img src="" id="quiz-image" alt="Mystery">
                <div class="puzzle-grid" id="puzzle-grid">
                    <div class="puzzle-piece" id="piece-1">?</div>
                    <div class="puzzle-piece" id="piece-2">?</div>
                    <div class="puzzle-piece" id="piece-3">?</div>
                    <div class="puzzle-piece" id="piece-4">?</div>
                </div>
            </div>

            <div class="clues-panel">
                <div class="clues-box-container">
                    <div class="clue-card" id="clue-1"><div class="clue-number">1</div><span id="text-clue-1">Clue 1</span></div>
                    <div class="clue-card" id="clue-2"><div class="clue-number">2</div><span id="text-clue-2">Clue 2</span></div>
                    <div class="clue-card" id="clue-3"><div class="clue-number">3</div><span id="text-clue-3">Clue 3</span></div>
                    <div class="clue-card" id="clue-4"><div class="clue-number">4</div><span id="text-clue-4">Clue 4</span></div>
                </div>
                <div class="answer-container"><div class="answer-box" id="answer-box">JAWABAN</div></div>
            </div>
        </div>
    </div>

    <script>
        const gameData = [
            { img: "images/ironman.jpg", clues: ["Miliarder Marvel", "Armor merah-emas", "Reaktor nuklir di dada", "'I love you 3000'"], answer: "IRON MAN" },
            { img: "images/elsa.jpg", clues: ["Disney Princess", "Sihir pengendali es", "Kakak Putri Anna", "Menyanyikan 'Let It Go'"], answer: "QUEEN ELSA" },
            { img: "images/spiderman.jpg", clues: ["Pahlawan laba-laba", "Identitas asli Peter Parker", "Digigit laba-laba radioaktif", "Musuh Green Goblin"], answer: "SPIDER-MAN" },
            { img: "images/batman.jpg", clues: ["Pahlawan pelindung kota Gotham", "Identitas aslinya miliarder Bruce Wayne", "Mengandalkan bela diri & gadget canggih", "Simbol kelelawar & punya Batmobile"], answer: "BATMAN" },
            { img: "images/jacksparrow.jpeg", clues: ["Kapten bajak laut eksentrik", "Pemimpin kapal Black Pearl", "Kompas ajaib tidak menunjuk utara", "Diperankan Johnny Depp"], answer: "JACK SPARROW" },
            { img: "images/joker.jpg", clues: ["Musuh utama Batman", "Riasan badut tersenyum", "'The Crime Prince of Gotham'", "'Why so serious?'"], answer: "JOKER" },
            { img: "images/doraemon.jpg", clues: ["Robot kucing biru", "Takut tikus, suka dorayaki", "Punya kantong ajaib", "Menjaga anak SD Nobita"], answer: "DORAEMON" },
            { img: "images/harrypotter.jpeg", clues: ["Penyihir yatim piatu legendaris", "Luka sambaran petir di dahi", "Bersekolah di Hogwarts", "Burung hantu salju Hedwig"], answer: "HARRY POTTER" },
            { img: "images/optimusprime.jpg", clues: ["Pemimpin faksi Autobots", "Berubah wujud truk trailer", "Pedang energi oranye", "Musuh utamanya Megatron"], answer: "OPTIMUS PRIME" },
            { img: "images/wiroslabeng.jpg", clues: ["Pendekar silat fiksi nusantara", "Murid Sinto Gendeng", "Tato '212' di dada", "Kapak Maut Naga Geni 212"], answer: "WIRO SABLENG" }
        ];

        let selebrasiAktif = false;

        function triggerScreenFlash() {
            const overlay = document.getElementById('flash-overlay');
            overlay.classList.remove('trigger-flash'); void overlay.offsetWidth; overlay.classList.add('trigger-flash');
        }

        // FUNGSI INTI: Merubah status layar berdasarkan instruksi Admin
        function executeVideotronStep(index, step) {
            const data = gameData[index];
            document.getElementById('q-counter').textContent = `ROUND ${index + 1} / 10`;
            document.getElementById('quiz-image').src = data.img;
            document.getElementById('answer-box').textContent = data.answer;
            for (let i=1; i<=4; i++) document.getElementById(`text-clue-${i}`).textContent = data.clues[i-1];

            // Tutup overlay pemenang jika layar berpindah soal
            document.getElementById('winner-overlay').classList.remove('show');
            selebrasiAktif = false;

            if (step === 0) {
                // Sembunyikan Semua Clue dan Jawaban
                document.getElementById('answer-box').classList.remove('reveal-active');
                for (let i=1; i<=4; i++) {
                    document.getElementById(`clue-${i}`).classList.remove('reveal-active');
                    document.getElementById(`piece-${i}`).classList.remove('revealed');
                }
            }
            else if (step >= 1 && step <= 4) {
                // Buka Clue Teks dan Kotak Puzzle secara berurutan
                for(let i=1; i<=step; i++) {
                    document.getElementById(`clue-${i}`).classList.add('reveal-active');
                    document.getElementById(`piece-${i}`).classList.add('revealed');
                }
            }
            else if (step === 5) {
                // Buka Semua Puzzle dan Tampilkan Jawaban dengan Ledakan!
                for(let i=1; i<=4; i++) document.getElementById(`piece-${i}`).classList.add('revealed');
                triggerScreenFlash();
                document.getElementById('answer-box').classList.add('reveal-active');
                selebrasiAktif = true;
                triggerMassiveConfetti();
            }
        }

        // DENGARKAN WEBSOCKET DARI LARAVEL/PUSHER
        document.addEventListener('DOMContentLoaded', () => {
            executeVideotronStep(0, 0); // Load default

            Echo.channel('quiz-channel').listen('.GameStateChanged', (e) => {
                // Membaca Kode Perintah Admin (Contoh: Q_2_STEP_1)
                if (e.state.startsWith('Q_')) {
                    let parts = e.state.split('_');
                    let qIndex = parseInt(parts[1]);
                    let stepNum = parseInt(parts[3]);
                    executeVideotronStep(qIndex, stepNum);
                }
                else if (e.state === 'WE_HAVE_A_WINNER') {
                    // Munculkan layar megah pemenang tercepat buzzer!
                    document.getElementById('winner-name-display').innerText = e.data.winner;
                    document.getElementById('winner-overlay').classList.add('show');
                }
            });
        });

        // =====================================
        // ENGINE PARTIKEL EMAS SELEBRASI
        // =====================================
        const canvas = document.getElementById('quiz-bg-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];

        function resizeCanvas() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        window.addEventListener('resize', resizeCanvas); resizeCanvas();

        class GoldParticle {
            constructor(x, y, type) {
                this.type = type; this.x = x; this.y = y;
                if (type === 'ambient') {
                    this.vx = (Math.random() - 0.5) * 0.4; this.vy = Math.random() * 0.8 + 0.3;
                    this.size = Math.random() * 2 + 0.5; this.alpha = Math.random() * 0.5 + 0.2; this.decay = 0;
                } else {
                    const angle = Math.random() * Math.PI * 2; const speed = Math.random() * 8 + 2;
                    this.vx = Math.cos(angle) * speed; this.vy = Math.sin(angle) * speed;
                    this.size = Math.random() * 3 + 1.5; this.alpha = 1; this.decay = Math.random() * 0.015 + 0.01;
                }
            }
            update() {
                this.x += this.vx; this.y += this.vy;
                if (this.type === 'burst') { this.vy += 0.05; this.alpha -= this.decay; }
                else if (this.y > canvas.height) { this.y = -10; this.x = Math.random() * canvas.width; }
            }
            draw() {
                ctx.save(); ctx.globalAlpha = Math.max(0, this.alpha); ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = this.type === 'ambient' ? '#ffcc00' : (Math.random() < 0.5 ? '#ffcc00' : '#ffffff');
                ctx.shadowBlur = this.type === 'ambient' ? 6 : 12; ctx.shadowColor = '#ffcc00';
                ctx.fill(); ctx.restore();
            }
        }

        function triggerMassiveConfetti() {
            for (let i = 0; i < 150; i++) particles.push(new GoldParticle(canvas.width / 2, canvas.height * 0.5, 'burst'));
        }
        for (let i = 0; i < 70; i++) particles.push(new GoldParticle(Math.random() * canvas.width, Math.random() * canvas.height, 'ambient'));

        function loop() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (selebrasiAktif && Math.random() < 0.08) {
                for(let i=0; i<8; i++) particles.push(new GoldParticle(Math.random() * canvas.width, Math.random() * (canvas.height * 0.4), 'burst'));
            }
            particles.forEach((p, index) => {
                p.update(); p.draw();
                if (p.type === 'burst' && p.alpha <= 0) particles.splice(index, 1);
            });
            requestAnimationFrame(loop);
        }
        loop();
    </script>
</body>
</html>
