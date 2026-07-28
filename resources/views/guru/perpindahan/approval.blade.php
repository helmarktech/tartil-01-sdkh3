@extends('layouts.admin')
@section('title', 'Approval Perpindahan')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title-display">Approval Perpindahan</h1>
            <p class="page-subtitle">Persetujuan perpindahan kelas tartil ke kelas yang Anda ajar</p>
        </div>
    </div>

    @if($perpindahans->count() > 0)
    <div style="margin-bottom: 12px;">
        <span class="badge-subject" style="background: #E8D5B5;">{{ $perpindahans->count() }} menunggu persetujuan</span>
    </div>
    @endif

    <div class="card-tartil table-responsive">
        <table class="table-tartil">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Kelas Lama</th>
                    <th>Kelas Baru (Anda)</th>
                    <th>Pengaju</th>
                    <th>Alasan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($perpindahans as $p)
                <tr>
                    <td>{{ $p->created_at->format('d/m/Y') }}</td>
                    <td style="font-weight: 500;">{{ $p->siswa->nama ?? '-' }}</td>
                    <td>{{ $p->kelasLama->nama ?? '-' }}</td>
                    <td><span class="badge-subject" style="background: #E8D5B5;">{{ $p->kelasBaru->nama ?? '-' }}</span></td>
                    <td>{{ $p->pengaju->nama ?? '-' }}</td>
                    <td>{{ $p->alasan ?? '-' }}</td>
                    <td>
                        <div style="display: flex; gap: 4px;">
                            <form method="POST" action="{{ route('guru.perpindahan.guru.approve', $p->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-tartil-success" style="padding: 6px 12px; font-size: 12px;" onclick="return confirm('Setujui perpindahan siswa ini ke kelas Anda?')">Setuju</button>
                            </form>
                            <button onclick="document.getElementById('tolak-{{ $p->id }}').style.display='block'" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;">Tolak</button>
                        </div>
                        <div id="tolak-{{ $p->id }}" style="display: none; margin-top: 8px; padding: 12px; background: #ffebee; border-radius: 8px;">
                            <form method="POST" action="{{ route('guru.perpindahan.guru.tolak', $p->id) }}">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Alasan Penolakan</label>
                                    <textarea name="catatan" class="form-input" rows="2" required></textarea>
                                </div>
                                <div style="display: flex; gap: 8px; margin-top: 8px;">
                                    <button type="submit" class="btn-tartil-danger" style="padding: 6px 12px; font-size: 12px;">Tolak</button>
                                    <button type="button" onclick="document.getElementById('tolak-{{ $p->id }}').style.display='none'" class="btn-tartil-outline" style="padding: 6px 12px; font-size: 12px;">Batal</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Tidak ada pengajuan perpindahan menunggu persetujuan Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
