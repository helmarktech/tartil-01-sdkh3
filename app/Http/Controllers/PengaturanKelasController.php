<?php

namespace App\Http\Controllers;

use App\Models\IndikatorPenilaian;
use Illuminate\Http\Request;

class PengaturanKelasController extends Controller
{
    // ═══════════════════════════════════════════════════
    // HALAMAN UTAMA: Pengaturan Indikator Penilaian
    // ═══════════════════════════════════════════════════
    public function index(Request $request)
    {
        $jenisList = IndikatorPenilaian::jenisKelasList();
        $jenisAktif = $request->get('jenis', $jenisList[0] ?? 'BQ 1');

        if (!in_array($jenisAktif, $jenisList)) {
            $jenisAktif = $jenisList[0] ?? 'BQ 1';
        }

        $indikators = IndikatorPenilaian::byJenis($jenisAktif);

        return view('admin.pengaturan-kelas.index', compact('jenisList', 'jenisAktif', 'indikators'));
    }

    // ═══════════════════════════════════════════════════
    // TAMBAH INDIKATOR
    // ═══════════════════════════════════════════════════
    public function storeIndikator(Request $request)
    {
        $validated = $request->validate([
            'jenis_kelas' => 'required|string|max:20',
            'nama_indikator' => 'required|string|max:100',
            'urutan' => 'required|integer|min:1',
        ]);

        $jenisList = IndikatorPenilaian::jenisKelasList();
        if (!in_array($validated['jenis_kelas'], $jenisList)) {
            return redirect()->back()->with('error', 'Jenis kelas tidak valid.');
        }

        IndikatorPenilaian::create([
            'jenis_kelas' => $validated['jenis_kelas'],
            'nama_indikator' => $validated['nama_indikator'],
            'urutan' => $validated['urutan'],
            'is_default' => false,
        ]);

        return redirect()->route('admin.pengaturan-kelas.index', ['jenis' => $validated['jenis_kelas']])
            ->with('success', 'Indikator penilaian berhasil ditambahkan.');
    }

    // ═══════════════════════════════════════════════════
    // UPDATE INDIKATOR
    // ═══════════════════════════════════════════════════
    public function updateIndikator(Request $request, $id)
    {
        $indikator = IndikatorPenilaian::findOrFail($id);

        $validated = $request->validate([
            'nama_indikator' => 'required|string|max:100',
            'urutan' => 'required|integer|min:1',
        ]);

        $indikator->update($validated);

        return redirect()->route('admin.pengaturan-kelas.index', ['jenis' => $indikator->jenis_kelas])
            ->with('success', 'Indikator penilaian berhasil diperbarui.');
    }

    // ═══════════════════════════════════════════════════
    // HAPUS INDIKATOR
    // ═══════════════════════════════════════════════════
    public function destroyIndikator($id)
    {
        $indikator = IndikatorPenilaian::findOrFail($id);
        $jenis = $indikator->jenis_kelas;

        // Cek apakah sudah digunakan di penilaian rapor internal
        $terpakai = \App\Models\PenilaianRaporNilai::where('indikator_penilaian_id', $indikator->id)->exists();
        if ($terpakai) {
            return redirect()->back()->with('error', 'Indikator sudah digunakan dalam penilaian rapor internal dan tidak dapat dihapus.');
        }

        $indikator->delete();

        return redirect()->route('admin.pengaturan-kelas.index', ['jenis' => $jenis])
            ->with('success', 'Indikator penilaian berhasil dihapus.');
    }
}
