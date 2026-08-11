@extends('layouts.admin')
@section('title', 'Tahun Ajaran')

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title-display">Tahun Ajaran</h1>
            <p class="page-subtitle">Tambah TA baru untuk otomatis kenaikan kelas, buat semester, dan snapshot data</p>
        </div>
    </div>

    {{-- TA Aktif --}}
    @if($taAktif)
    <div class="card-tartil" style="margin-bottom: 20px; padding: 16px; background: rgba(90,125,90,0.04); border-left: 4px solid #5A7D5A;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="font-size: 12px; color: var(--text-muted); margin: 0;">Tahun Ajaran Aktif</p>
                <p style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin: 4px 0 0;">{{ $taAktif->nama }}</p>
                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0;">{{ $taAktif->tanggal_mulai->format('d/m/Y') }} - {{ $taAktif->tanggal_selesai->format('d/m/Y') }}</p>
            </div>
            <span class="badge-success">Aktif</span>
        </div>
    </div>
    @endif

    {{-- Form Tambah TA Baru --}}
    <div class="card-tartil" style="max-width: 600px; padding: 24px; margin-bottom: 24px;">
        <h2 style="font-size: 16px; font-weight: 600; margin: 0 0 20px; color: var(--text-primary);">Tambah Tahun Ajaran Baru</h2>

        <div style="background: #fff3e0; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
            <p style="font-size: 12px; color: #6B5E51; margin: 0;">
                <strong>Proses otomatis saat menambah TA:</strong>
            </p>
            <ol style="font-size: 12px; color: #6B5E51; margin: 6px 0 0; padding-left: 16px; line-height: 1.6;">
                <li>Tutup TA lama dan semua semester aktif</li>
                <li>Kenaikan kelas reguler: Kelas 6 &rarr; Lulus, Kelas 1-5 &rarr; Naik (rombel tetap)</li>
                <li>Buat semester Ganjil (aktif) + Genap (nonaktif)</li>
                <li>Snapshot semua kelas tartil dan siswa aktif ke semester Ganjil</li>
            </ol>
        </div>

        <form method="POST" action="{{ route('admin.tahun-ajaran.store') }}">
            @csrf
            <div style="display: grid; gap: 16px;">
                <div>
                    <label class="form-label">Tahun Ajaran *</label>
                    <input type="text" name="nama" class="form-input" required placeholder="Contoh: 2025/2026">
                </div>
                <div>
                    <label class="form-label">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" class="form-input" required>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Tanggal pertama TA dimulai (biasanya 1 Juli). Semester Ganjil dimulai dari tanggal ini.</p>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn-tartil" onclick="return confirm('Yakin buat Tahun Ajaran baru? Operasi ini akan:\n• Menutup TA lama\n• Naikkan kelas semua siswa (6=lulus, 1-5=naik)\n• Buat semester ganjil + genap\n• Snapshot data ke semester baru\n\nTidak bisa dibatalkan.')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Buat Tahun Ajaran Baru
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar TA --}}
    <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-primary); font-weight: 600;">Daftar Tahun Ajaran</h3>
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Periode</th>
                    <th>Semester</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tahunAjarans as $i => $ta)
                <tr style="{{ $ta->status == 'aktif' ? 'background: rgba(90,125,90,0.04);' : 'background: #f5f5f5;' }}">
                    <td>{{ $tahunAjarans->firstItem() + $i }}</td>
                    <td style="font-weight: 500;">{{ $ta->nama }}</td>
                    <td>{{ $ta->tanggal_mulai->format('d/m/Y') }} - {{ $ta->tanggal_selesai->format('d/m/Y') }}</td>
                    <td>{{ $ta->semesters_count }} semester</td>
                    <td>
                        @if($ta->status == 'aktif')
                            <span class="badge-success">Aktif</span>
                        @else
                            <span class="badge-error">Ditutup</span>
                        @endif
                    </td>
                    <td>
                        @if($ta->status == 'aktif')
                        <form method="POST" action="{{ route('admin.tahun-ajaran.tutup', $ta->id) }}" style="display: inline;" onsubmit="return confirm({{ json_encode('Tutup TA '.$ta->nama.'? Semua semester akan ditutup juga.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }})">
                            @csrf
                            <button type="submit" class="btn-tartil-danger" style="padding: 4px 10px; font-size: 11px;">Tutup TA</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Belum ada tahun ajaran.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $tahunAjarans->links() }}
    </div>

    {{-- Daftar Semester --}}
    <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-primary); font-weight: 600;">Daftar Semester</h3>
    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>TA</th>
                    <th>Periode</th>
                    <th>Kelas</th>
                    <th>Siswa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semesters as $i => $s)
                <tr style="{{ $s->is_aktif ? 'background: rgba(90,125,90,0.04);' : ($s->status == 'ditutup' ? 'background: #f5f5f5;' : '') }}">
                    <td>{{ $semesters->firstItem() + $i }}</td>
                    <td style="font-weight: 500;">{{ $s->nama }}</td>
                    <td>{{ $s->tahunAjaran->nama ?? $s->tahun_ajaran }}</td>
                    <td style="font-size: 13px; color: var(--text-secondary);">
                        {{ $s->tanggal_mulai->format('d M Y') }} - {{ $s->tanggal_selesai->format('d M Y') }}
                    </td>
                    <td style="text-align: center;">{{ $s->kelas_tartils_count ?? 0 }}</td>
                    <td style="text-align: center;">{{ $s->siswas_count ?? 0 }}</td>
                    <td>
                        @if($s->status == 'aktif')
                            <span class="badge-success">Aktif</span>
                        @elseif($s->status == 'ditutup')
                            <span class="badge-error">Ditutup</span>
                        @else
                            <span class="badge-warning">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                            <a href="{{ route('admin.semester.detail', $s->id) }}" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Detail</a>
                            @if($s->status != 'ditutup' && !$s->is_aktif)
                            <form method="POST" action="{{ route('admin.semester.aktifkan', $s->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-tartil-success" style="padding: 6px 12px; font-size: 12px;">Aktifkan</button>
                            </form>
                            @endif
                            @if($s->status != 'ditutup')
                            <form method="POST" action="{{ route('admin.semester.tutup', $s->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;" onclick="return confirm({{ json_encode('Tutup semester '.$s->nama.'?', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }})">Tutup</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted);">Belum ada semester.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $semesters->links() }}
    </div>
</div>
@endsection
