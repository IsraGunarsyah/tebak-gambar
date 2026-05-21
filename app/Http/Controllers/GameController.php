<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Events\GameStateChanged;

class GameController extends Controller
{

    public function playerView()
    {
        // Menampilkan resources/views/play.blade.php
        return view('play');
    }

    public function videotronView()
    {
        // Menampilkan resources/views/videotron.blade.php
        return view('videotron');
    }

    public function adminDashboard()
    {
        // Menampilkan resources/views/admin.blade.php
        return view('admin.index');
    }

    public function hitBuzzer(Request $request)
    {
        if (Cache::get('game_status') !== 'BUZZER_OPEN') {
            return response()->json(['status' => 'rejected']);
        }

        $playerName = $request->input('player_name');

        $lock = Cache::lock('buzzer_winner_lock', 5);

        if ($lock->get()) {
            // 1. Simpan status
            Cache::put('current_winner', $playerName);
            Cache::put('game_status', 'BUZZER_CLOSED');

            // 2. TEMBAKKAN EVENT KE SEMUA HP & VIDEOTRON!
            broadcast(new GameStateChanged('WE_HAVE_A_WINNER', [
                'winner' => $playerName
            ]));

            return response()->json(['status' => 'winner']);
        }

        return response()->json(['status' => 'loser']);
    }

    // Fungsi tambahan untuk Admin
    public function setGameMode(Request $request)
    {
        $mode = $request->input('mode'); // 'BUZZER_OPEN', 'MANUAL_MODE', dll
        Cache::put('game_status', $mode);

        // Tembakkan event agar tampilan HP peserta langsung berubah sesuai mode
        broadcast(new GameStateChanged($mode));

        return response()->json(['status' => 'success']);
    }

    public function joinRoom(Request $request)
    {
        $playerName = $request->input('player_name');

        // Ambil daftar pemain yang sudah ada di memori Cache
        $players = Cache::get('joined_players', []);

        // Jika nama belum ada di daftar, masukkan!
        if (!in_array($playerName, $players)) {
            $players[] = $playerName;
            Cache::put('joined_players', $players);
        }

        // Siarkan total terbaru ke layar Admin
        broadcast(new GameStateChanged('PLAYER_JOINED', [
            'total' => count($players)
        ]));

        return response()->json(['status' => 'success']);
    }

    public function getPlayers()
    {
        // Fungsi untuk mengambil total saat layar Admin baru di-refresh
        $players = Cache::get('joined_players', []);
        return response()->json(['total' => count($players)]);
    }

    public function resetPlayers()
    {
        // Kosongkan ruangan (berguna saat pindah sesi / selesai gladi)
        Cache::forget('joined_players');
        broadcast(new GameStateChanged('PLAYER_JOINED', ['total' => 0]));
        return response()->json(['status' => 'success']);
    }

    public function showImage(Request $request)
    {
        $url = $request->input('image_url');
        // Siarkan perintah ganti gambar ke videotron
        broadcast(new GameStateChanged('SHOW_IMAGE', ['url' => $url]));
        return response()->json(['status' => 'success']);
    }
}
