@extends('layouts.admin')
@section('title', 'Import Guru dari Excel')

@section('content')
<style>
.import-card { padding: 24px; }
.import-info { background: var(--bg-elevated); border-radius: 10px; padding: 16px; margin-bottom: 20px; }
.import-info h4 { font-size: 13px; color: var(--text-primary); margin-bottom: 10px; }
.import-info ul { font-size: 12px; color: var(--text-muted); padding-left: 18px; margin: 0; }
.import-info ul li { margin-bottom: 4px; }
.required { color: #C62828; font-weight: 600; }
.error-list { max-height: 300px; overflow-y: auto; }
.error-item { padding: 8px 12px; border-bottom: 1px solid var(--border); font-size: 12px; color: #A85A52; }
.error-item:last-child { border-bottom: none; }
.jenis-toggle { display: flex; gap: 8px; margin-bottom: 20px; }
.jenis-btn {
    padding: 8px 16px; border-radius: 8px; border: 1px solid var(--border);
    background: var(--bg-elevated); color: var(--text-muted); font-size: 13px;
    text-decoration: none; cursor: pointer; transition: all .15s ease;
}
.jenis-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
</style>

<div>
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Import Guru</h1>
            <p class="page-subtitle">Unggah file Excel untuk menambahkan guru reguler atau guru tartil</p>
        </div>
    </div>

    {{-- Pilihan Jenis Guru --}}
    <div class="jenis-toggle">
        <a href="{{ route('admin.guru.import', ['jenis' => 'tartil']) }}" class="jenis-btn {{ $jenis === 'tartil' ? 'active' : '' }}">Guru Tartil</a>
        <a href="{{ route('admin.guru.import', ['jenis' => 'reguler']) }}" class="jenis-btn {{ $jenis === 'reguler' ? 'active' : '' }}">Guru Reguler</a>
    </div>

    {{-- Info --}}
    <div class="import-info">
        <h4>Format Kolom Wajib (Header Baris Pertama) — Import Guru {{ $jenis === 'tartil' ? 'Tartil' : 'Reguler' }}</h4>
        <ul>
            <li><span class="required">NAMA*</span> — Nama lengkap guru</li>
            <li><span class="required">EMAIL*</span> — Email unik (digunakan untuk login jika guru tartil)</li>
            <li><span class="required">NO_HP*</span> — Nomor HP aktif</li>
            <li><span class="required">JENIS_KELAMIN*</span> — L atau P</li>
            <li>NIP — Nomor induk pegawai (opsional, unik)</li>
            <li>ALAMAT — Alamat lengkap (opsional)</li>
        </ul>
        <div style="margin-top: 10px; font-size: 11px; color: var(--text-muted);">
            @if($jenis === 'tartil')
                Guru tartil akan otomatis mendapatkan akun login dengan password default <strong>guru123</strong>.
            @else
                Guru reguler hanya dicatat sebagai data guru pengampu kelas reguler, tanpa akun login.
            @endif
            Pastikan email belum pernah digunakan untuk guru {{ $jenis }} lain.
            <br><br>
            <strong>Catatan:</strong> File akan diunggah dan diproses di background oleh queue worker. Hasil bisa dicek di log aplikasi.
        </div>
    </div>

    {{-- Download Template --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.guru.import.template', ['jenis' => $jenis]) }}" class="btn-tartil-outline" style="text-decoration: none; font-size: 12px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Template Excel
        </a>
    </div>

    {{-- Upload Form --}}
    <div class="card-tartil import-card">
        <form method="POST" action="{{ route('admin.guru.import.proses') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="font-size: 12px;">Pilih File Excel</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-input" style="padding: 8px; font-size: 13px;" required>
                @error('file')
                <div style="color: #C62828; font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
                @error('jenis')
                <div style="color: #C62828; font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn-tartil" style="font-size: 13px; padding: 10px 24px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import Data
            </button>
        </form>
    </div>

    {{-- Error List --}}
    @if(session('import_errors'))
    <div class="card-tartil" style="padding: 0; margin-top: 20px; overflow: hidden;">
        <div style="padding: 12px 16px; background: #FFEBEE; border-bottom: 1px solid #E8A0A0;">
            <strong style="font-size: 13px; color: #A85A52;">Detail Error ({{ count(session('import_errors')) }} baris):</strong>
        </div>
        <div class="error-list">
            @foreach(session('import_errors') as $err)
            <div class="error-item">{{ $err }}</div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Log Import --}}
    <div class="card-tartil" style="padding: 0; margin-top: 20px; overflow: hidden;">
        <div style="padding: 12px 16px; background: var(--bg-elevated); border-bottom: 1px solid var(--border);">
            <strong style="font-size: 13px; color: var(--text-primary);">Riwayat Import Guru {{ $jenis === 'tartil' ? 'Tartil' : 'Reguler' }}</strong>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                <thead>
                    <tr style="background: var(--bg-elevated); color: var(--text-muted);">
                        <th style="padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border);">Waktu Upload (WIB)</th>
                        <th style="padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border);">File</th>
                        <th style="padding: 10px 12px; text-align: center; border-bottom: 1px solid var(--border);">Status</th>
                        <th style="padding: 10px 12px; text-align: center; border-bottom: 1px solid var(--border);">Sukses</th>
                        <th style="padding: 10px 12px; text-align: center; border-bottom: 1px solid var(--border);">Gagal</th>
                        <th style="padding: 10px 12px; text-align: center; border-bottom: 1px solid var(--border);">Durasi Proses</th>
                        <th style="padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border);">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($importLogs as $log)
                    @php
                        $createdJakarta = $log->created_at->timezone('Asia/Jakarta');
                        $finishedAt = $log->processed_at ?? now();
                        $diffSeconds = $log->created_at->diffInSeconds($finishedAt);
                        $diffMinutes = ceil($diffSeconds / 60);
                        $durasi = $log->processed_at
                            ? ($diffMinutes < 1 ? '<1 menit' : $diffMinutes . ' menit')
                            : 'sedang diproses';
                    @endphp
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px 12px; color: var(--text-primary);">{{ $createdJakarta->format('d/m/Y H:i') }}</td>
                        <td style="padding: 10px 12px; color: var(--text-primary);">{{ $log->file_name }}</td>
                        <td style="padding: 10px 12px; text-align: center;">
                            @if($log->status === 'success')
                                <span style="padding: 3px 8px; border-radius: 4px; background: #E8F5E9; color: #2E7D32; font-size: 11px; font-weight: 600;">Berhasil</span>
                            @elseif($log->status === 'failed')
                                <span style="padding: 3px 8px; border-radius: 4px; background: #FFEBEE; color: #C62828; font-size: 11px; font-weight: 600;">Gagal</span>
                            @elseif($log->status === 'processing')
                                <span style="padding: 3px 8px; border-radius: 4px; background: #FFF3E0; color: #EF6C00; font-size: 11px; font-weight: 600;">Diproses</span>
                            @else
                                <span style="padding: 3px 8px; border-radius: 4px; background: #E3F2FD; color: #1565C0; font-size: 11px; font-weight: 600;">Menunggu</span>
                            @endif
                        </td>
                        <td style="padding: 10px 12px; text-align: center; color: var(--text-primary);">{{ $log->sukses }}</td>
                        <td style="padding: 10px 12px; text-align: center; color: var(--text-primary);">{{ $log->gagal }}</td>
                        <td style="padding: 10px 12px; text-align: center; color: var(--text-primary);">{{ $durasi }}</td>
                        <td style="padding: 10px 12px; color: var(--text-primary);">
                            @if(!empty($log->errors))
                                @if(is_array($log->errors))
                                    {{ $log->errors[0] }}
                                    @if(count($log->errors) > 1)
                                        <span style="color: var(--text-muted);">(+{{ count($log->errors) - 1 }} error lain)</span>
                                    @endif
                                @else
                                    {{ $log->errors }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 16px; text-align: center; color: var(--text-muted);">Belum ada riwayat import.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
