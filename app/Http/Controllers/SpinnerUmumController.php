<?php
use App\Models\SpinnerUmum;

$spinner = SpinnerUmum::create([
    'id_event' => $request->id_event,
    'jumlah_hadiah' => $request->jumlah_hadiah,
]);

// Mendapatkan kandidat yang belum menang
$kandidatBelumMenang = $spinner->kandidatBelumMenang()->get();
