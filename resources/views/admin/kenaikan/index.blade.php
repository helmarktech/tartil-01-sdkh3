@extends('layouts.admin')
@section('title', 'Kenaikan Kelas Reguler')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title-display">Kenaikan Kelas Reguler</h1>
            <p class="page-subtitle">Proses kenaikan otomatis setiap tahun ajaran baru</p>
        </div>
    </div>

    {{-- Info Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #A85A52;">{{ $countKelas6 }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Kelas 6 (Akan Lulus)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: var(--accent);">{{ $countKelas1to5 }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Kelas 1-5 (Akan Naik)</div>
        </div>
        <div class="card-tartil" style="text-align: center;">
            <div style="font-size: 24px; font-weight: 600; color: #5A7D5A;">{{ $countSemGanjilAktif }}</div>
            <div style="font-size: 12px; color: var(--text-muted);">Semester Ganjil Aktif</div>
        </div>
    </div>

    {{-- Alur Kenaikan --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary); font-weight: 600;">Alur Kenaikan Kelas</h3>
        <div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #ffebee; border-radius: 8px; border-left: 4px solid #A85A52;">
                <span style="font-size: 20px;">6</span>
                <div>
                    <strong style="color: var(--text-primary);">Kelas 6 → Lulus</strong>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0;">Semua siswa kelas 6 di-status-kan LULUS. Tidak terdaftar di TA baru.</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #E9F0E9; border-radius: 8px; border-left: 4px solid #5A7D5A;">
                <span style="font-size: 20px;">5→6</span>
                <div>
                    <strong style="color: var(--text-primary);">Kelas 5 → Naik ke 6</strong>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0;">Rombel tetap sama (5A → 6A, 5B → 6B)</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #E9F0E9; border-radius: 8px; border-left: 4px solid #5A7D5A;">
                <span style="font-size: 20px;">4→5</span>
                <div>
                    <strong style="color: var(--text-primary);">Kelas 4 → Naik ke 5, dst</strong>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0;">Kelas 1 naik ke 2, 2 ke 3, 3 ke 4, 4 ke 5 (rombel tetap)</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #fff3e0; border-radius: 8px; border-left: 4px solid #C4953A;">
                <span style="font-size: 20px;">⚠</span>
                <div>
                    <strong style="color: var(--text-primary);">Mutasi Keluar</strong>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0;">Siswa mutasi tidak ikut naik. Status tetap "mutasi_keluar".</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Proses Kenaikan --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary); font-weight: 600;">Proses Kenaikan Kelas</h3>



        {{-- Status Validasi --}}
        <div style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                @if($semesterGanjil)
                    <span class="badge-success">✓ Semester Ganjil Aktif: {{ $semesterGanjil->nama }}</span>
                @else
                    <span class="badge-error">✗ Belum ada semester ganjil aktif</span>
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                @if(!$semesterAktifLama)
                    <span class="badge-success">✓ Semester Genap TA lama sudah ditutup</span>
                @else
                    <span class="badge-warning">⚠ Semester Genap TA lama masih aktif: {{ $semesterAktifLama->nama }}</span>
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                @if(!$sudahNaik)
                    <span class="badge-success">✓ Belum pernah kenaikan untuk TA ini</span>
                @else
                    <span class="badge-error">✗ Kenaikan sudah pernah dilakukan untuk TA ini</span>
                @endif
            </div>
        </div>

        @if($semesterGanjil && !$semesterAktifLama && !$sudahNaik)
        <form method="POST" action="{{ route('admin.kenaikan.proses') }}" onsubmit="return confirm('Yakin proses kenaikan kelas? Operasi ini tidak bisa dibatalkan.\\n\\n• Kelas 6 → Lulus\\n• Kelas 5→6, 4→5, 3→4, 2→3, 1→2 (rombel tetap)')">
            @csrf
            <input type="hidden" name="tahun_ajaran_baru" value="{{ $semesterGanjil->tahun_ajaran }}">
            <input type="hidden" name="semester_ganjil_id" value="{{ $semesterGanjil->id }}">
            <button type="submit" class="btn-tartil" style="background: #5A7D5A;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5"/><path d="m5 12 7-7 7 7"/></svg>
                Proses Kenaikan Kelas Otomatis
            </button>
        </form>
        @else
        <div class="alert-tartil alert-warning">
            @if(!$semesterGanjil)
                Buat semester ganjil baru terlebih dahulu untuk memulai tahun ajaran baru.
            @elseif($semesterAktifLama)
                Tutup semester genap TA lama terlebih dahulu.
            @elseif($sudahNaik)
                Kenaikan kelas untuk TA ini sudah pernah dilakukan.
            @endif
        </div>
        @endif
    </div>

    {{-- Form Mutasi Keluar --}}
    <div class="card-tartil" style="margin-bottom: 20px; padding: 24px;">
        <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary); font-weight: 600;">Mutasi Keluar</h3>
        <form method="POST" action="{{ route('admin.kenaikan.mutasi') }}">
            @csrf
            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label">Pilih Siswa (hanya aktif)</label>
                <select name="siswa_ids[]" class="form-input" multiple style="min-height: 120px;">
                    @forelse($siswaAktifList as $s)
                    <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->nis }}) - {{ $s->kelasReguler->nama ?? '-' }}</option>
                    @empty
                    <option disabled>Tidak ada siswa aktif</option>
                    @endforelse
                </select>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">Tahan Ctrl/Cmd untuk memilih multiple.</p>
            </div>
            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label">Keterangan Mutasi</label>
                <input type="text" name="keterangan" class="form-input" placeholder="Contoh: Pindah ke SD Negeri 1, Dikeluarkan, dll" required>
            </div>
            <button type="submit" class="btn-tartil-danger" onclick="return confirm('Yakin mutasi keluar siswa terpilih?')">
                Proses Mutasi Keluar
            </button>
        </form>
    </div>

    {{-- Riwayat Kenaikan --}}
    <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-primary); font-weight: 600;">Riwayat Kenaikan Kelas</h3>
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Kelas Lama</th>
                    <th>Kelas Baru</th>
                    <th>Kategori</th>
                    <th>TA</th>
                    <th>Approver</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $r)
                <tr>
                    <td>{{ $r->created_at->format('d/m/Y') }}</td>
                    <td style="font-weight: 500;">{{ $r->siswa->nama ?? '-' }}</td>
                    <td>{{ $r->kelasLama->nama ?? '-' }} ({{ $r->kelasLama->jenjang ?? '' }})</td>
                    <td>
                        @if($r->kategori == 'lulus')
                            <span class="badge-success">Lulus</span>
                        @elseif($r->kategori == 'mutasi')
                            <span class="badge-error">Mutasi Keluar</span>
                        @else
                            {{ $r->kelasBaru->nama ?? '-' }} ({{ $r->kelasBaru->jenjang ?? '' }})
                        @endif
                    </td>
                    <td>
                        @if($r->kategori == 'naik')
                            <span class="badge-subject" style="background: #E9F0E9; color: #5A7D5A;">Naik</span>
                        @elseif($r->kategori == 'lulus')
                            <span class="badge-success">Lulus</span>
                        @else
                            <span class="badge-error">Mutasi</span>
                        @endif
                    </td>
                    <td>{{ $r->tahun_ajaran }}</td>
                    <td>{{ $r->approver->nama ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Belum ada riwayat kenaikan kelas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $riwayat->links() }}
</div>
@endsection
                  <td>{{ $r->approver->nama ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Belum ada riwayat kenaikan kelas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $riwayat->links() }}
</div>
@endsection
