@extends('layouts.admin')
@section('title', 'Pengaturan Kop Surat Rapor')

@push('styles')
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
@endpush

@section('content')
<div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title-display">Pengaturan Kop Surat Rapor</h1>
            <p class="page-subtitle">Atur logo, judul, dan kop surat untuk cetak rapor PDF</p>
        </div>
        <a href="{{ route('admin.cetak-rapor.pilih') }}" class="btn-tartil" style="text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M10 18c0 0 2-3 4-3s4 3 4 3"/><line x1="12" y1="14" x2="12" y2="22"/><line x1="4" y1="22" x2="20" y2="22"/></svg>
            Cetak Rapor
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: #E9F0E9; border: 1px solid #C3D9C3; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #3A633A; display: flex; justify-content: space-between; align-items: center;">
        <span>{{ session('success') }}</span>
        <span style="font-size: 11px; color: #5a8a5a;">Preview diperbarui</span>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #991b1b;">
        <strong>Terjadi kesalahan:</strong>
        <ul style="margin: 4px 0 0 16px; padding: 0;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        {{-- Form Pengaturan --}}
        <div class="card-tartil" style="padding: 24px;">
            <h3 style="font-size: 16px; margin: 0 0 20px; color: var(--text-primary); font-weight: 600;">Edit Kop Surat <span style="font-size:11px;color:#a3a3a3;font-weight:400;">(ID: {{ $kop->id }})</span></h3>
            <form method="POST" action="{{ route('admin.kop-surat-rapor.update') }}" enctype="multipart/form-data">
                @csrf

                <div style="display: grid; gap: 16px;">
                    {{-- Logo --}}
                    <div>
                        <label class="form-label">Logo Sekolah</label>
                        @if($kop->logo_path)
                        <div style="margin-bottom: 8px;">
                            @if($kop->logo_base64)
                            <img src="{{ $kop->logo_base64 }}" alt="Logo" style="max-height: 80px; max-width: 150px; border: 1px solid var(--border); border-radius: 8px; padding: 4px; background: #fff;">
                            @else
                            <div style="padding: 8px 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; font-size: 11px; color: #991b1b;">
                                <strong>File tidak ditemukan.</strong> Path: <code>{{ $kop->logo_full_path }}</code>
                            </div>
                            @endif
                        </div>
                        @endif
                        <input type="file" name="logo" class="form-input" accept="image/png,image/jpg,image/jpeg" style="width: 100%;">
                        <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Format: PNG, JPG, JPEG. Maksimal 2MB. Kosongkan jika tidak ingin mengganti.</p>
                    </div>

                    {{-- Stempel --}}
                    <div>
                        <label class="form-label">Stempel Sekolah</label>
                        @if($kop->stempel_path)
                        <div style="margin-bottom: 8px;">
                            @if($kop->stempel_base64)
                            <img src="{{ $kop->stempel_base64 }}" alt="Stempel" style="max-height: 80px; max-width: 150px; border: 1px solid var(--border); border-radius: 8px; padding: 4px; background: #fff;">
                            @else
                            <div style="padding: 8px 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; font-size: 11px; color: #991b1b;">
                                <strong>File stempel tidak ditemukan.</strong>
                            </div>
                            @endif
                        </div>
                        @endif
                        <input type="file" name="stempel" class="form-input" accept="image/png,image/jpg,image/jpeg" style="width: 100%;">
                        <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Format: PNG dengan background transparan. Maksimal 2MB.</p>
                    </div>

                    {{-- TTD Kepala Sekolah --}}
                    <div>
                        <label class="form-label">Tanda Tangan Kepala Sekolah</label>
                        @if($kop->ttd_path)
                        <div style="margin-bottom: 8px;">
                            @if($kop->ttd_base64)
                            <img src="{{ $kop->ttd_base64 }}" alt="TTD" style="max-height: 60px; max-width: 150px; border: 1px solid var(--border); border-radius: 8px; padding: 4px; background: #fff;">
                            @else
                            <div style="padding: 8px 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; font-size: 11px; color: #991b1b;">
                                <strong>File TTD tidak ditemukan.</strong>
                            </div>
                            @endif
                        </div>
                        @endif
                        <input type="file" name="ttd" class="form-input" accept="image/png,image/jpg,image/jpeg" style="width: 100%;">
                        <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Format: PNG dengan background transparan. Maksimal 2MB.</p>
                    </div>

                    {{-- Judul --}}
                    <div>
                        <label class="form-label">Judul Rapor <span style="color: #C62828;">*</span></label>
                        <input type="text" name="judul" class="form-input" required value="{{ $kop->judul }}" style="width: 100%;">
                    </div>

                    {{-- Sub Judul --}}
                    <div>
                        <label class="form-label">Sub Judul <span style="color: #C62828;">*</span></label>
                        <input type="text" name="sub_judul" class="form-input" required value="{{ $kop->sub_judul }}" style="width: 100%;">
                    </div>

                    {{-- Nama Sekolah --}}
                    <div>
                        <label class="form-label">Nama Sekolah <span style="color: #C62828;">*</span></label>
                        <input type="text" name="nama_sekolah" class="form-input" required value="{{ $kop->nama_sekolah }}" style="width: 100%;">
                    </div>

                    {{-- Baris: Alamat + Telepon --}}
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label">Alamat</label>
                            <input type="text" name="alamat" class="form-input" value="{{ $kop->alamat }}" style="width: 100%;">
                        </div>
                        <div>
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-input" value="{{ $kop->telepon }}" style="width: 100%;">
                        </div>
                    </div>

                    {{-- Baris: Email + Website --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label">Email</label>
                            <input type="text" name="email" class="form-input" value="{{ $kop->email }}" style="width: 100%;">
                        </div>
                        <div>
                            <label class="form-label">Website</label>
                            <input type="text" name="website" class="form-input" value="{{ $kop->website }}" style="width: 100%;">
                        </div>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div>
                        <label class="form-label">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="form-input" value="{{ $kop->tahun_ajaran }}" style="width: 100%;" placeholder="2025/2026">
                    </div>

                    {{-- Tanggal Cetak --}}
                    <div>
                        <label class="form-label">Tanggal Cetak Rapor</label>
                        <input type="date" name="tanggal_cetak" class="form-input" value="{{ $kop->tanggal_cetak ? $kop->tanggal_cetak->format('Y-m-d') : '' }}" style="width: 100%;">
                        <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Tanggal yang tercantum di bagian "Tanggal Cetak" pada rapor PDF. Kosongkan untuk pakai tanggal hari ini.</p>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--border); margin: 4px 0;">

                    {{-- Kepala Sekolah --}}
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label">Nama Kepala Sekolah</label>
                            <input type="text" name="kepala_sekolah" class="form-input" value="{{ $kop->kepala_sekolah }}" style="width: 100%;">
                        </div>
                        <div>
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip_kepala_sekolah" class="form-input" value="{{ $kop->nip_kepala_sekolah }}" style="width: 100%;">
                        </div>
                    </div>

                    {{-- Catatan Kaki --}}
                    <div>
                        <label class="form-label">Catatan Kaki Rapor</label>
                        <textarea name="catatan_kaki" class="form-input" rows="2" style="width: 100%;">{{ $kop->catatan_kaki }}</textarea>
                    </div>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn-tartil" id="btnSimpanKop">
                        <span id="btnSimpanText">Simpan Pengaturan</span>
                        <span id="btnSimpanLoading" style="display:none;">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Preview --}}
        <div class="card-tartil" style="padding: 24px;">
            <h3 style="font-size: 16px; margin: 0 0 20px; color: var(--text-primary); font-weight: 600;">Preview Kop Surat</h3>
            <div style="border: 2px solid var(--border); border-radius: 8px; padding: 24px; background: #fff;">
                {{-- Header Preview --}}
                <div style="display: flex; align-items: center; gap: 16px; padding-bottom: 16px; border-bottom: 2px solid #333; margin-bottom: 16px;">
                    @if($kop->logo_base64)
                    <img src="{{ $kop->logo_base64 }}" alt="Logo" style="height: 70px; width: auto; max-width: 80px; object-fit: contain;">
                    @elseif($kop->logo_path)
                    <div style="height: 70px; width: 80px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #991b1b; font-size: 10px; text-align: center; padding: 4px;">File<br>tidak ada</div>
                    @else
                    <div style="width: 70px; height: 70px; background: var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 11px; text-align: center;">No<br>Logo</div>
                    @endif
                    <div style="flex: 1; text-align: center;">
                        <div style="font-size: 16px; font-weight: 700; color: #333;">{{ $kop->judul }}</div>
                        <div style="font-size: 13px; color: #555; margin-top: 2px;">{{ $kop->sub_judul }}</div>
                        <div style="font-size: 14px; font-weight: 600; color: #333; margin-top: 4px;">{{ $kop->nama_sekolah }}</div>
                        @if($kop->alamat || $kop->telepon)
                        <div style="font-size: 11px; color: #777; margin-top: 2px;">
                            {{ $kop->alamat }}{{ $kop->alamat && $kop->telepon ? ' | ' : '' }}{{ $kop->telepon }}
                        </div>
                        @endif
                        @if($kop->email || $kop->website)
                        <div style="font-size: 10px; color: #999; margin-top: 1px;">
                            {{ $kop->email }}{{ $kop->email && $kop->website ? ' | ' : '' }}{{ $kop->website }}
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Info Preview --}}
                <div style="font-size: 12px; color: #666; margin-bottom: 12px; display: flex; gap: 16px;">
                    <span><strong>Tahun Ajaran:</strong> {{ $kop->tahun_ajaran ?? '-' }}</span>
                    <span><strong>Tanggal Cetak:</strong> {{ $kop->tanggal_cetak?->format('d/m/Y') ?? date('d/m/Y') }}</span>
                </div>

                {{-- Sample content --}}
                <div style="border: 1px solid #ddd; border-radius: 4px; padding: 16px; margin-bottom: 16px;">
                    <div style="font-size: 11px; color: #999; text-align: center; margin-bottom: 12px;">--- Contoh Isi Rapor ---</div>
                    <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                        <tr style="background: #f5f5f5;">
                            <td style="padding: 6px; border: 1px solid #ddd; width: 30%;"><strong>Nama</strong></td>
                            <td style="padding: 6px; border: 1px solid #ddd;">Ahmad Fauzi</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px; border: 1px solid #ddd;"><strong>NIS</strong></td>
                            <td style="padding: 6px; border: 1px solid #ddd;">2025001</td>
                        </tr>
                        <tr style="background: #f5f5f5;">
                            <td style="padding: 6px; border: 1px solid #ddd;"><strong>Kelas Tartil</strong></td>
                            <td style="padding: 6px; border: 1px solid #ddd;">Tartil A</td>
                        </tr>
                    </table>
                </div>

                {{-- Footer Preview --}}
                <div style="border-top: 1px solid #ddd; padding-top: 12px; font-size: 11px; color: #999; text-align: center;">
                    <div>{{ $kop->catatan_kaki }}</div>
                    <div style="margin-top: 16px; display: flex; justify-content: flex-end; gap: 16px; align-items: flex-end;">
                        @if($kop->stempel_base64)
                        <img src="{{ $kop->stempel_base64 }}" alt="Stempel" style="height: 80px; width: auto; opacity: 0.8;">
                        @endif
                        <div style="text-align: center;">
                            <div>Kepala Sekolah,</div>
                            @if($kop->ttd_base64)
                            <img src="{{ $kop->ttd_base64 }}" alt="TTD" style="height: 50px; width: auto; margin: 4px 0;">
                            @else
                            <div style="margin-top: 40px;"></div>
                            @endif
                            <div style="font-weight: 600;">{{ $kop->kepala_sekolah }}</div>
                            <div>NIP. {{ $kop->nip_kepala_sekolah }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // Loading state saat simpan
    var form = document.querySelector('form[action*="kop-surat-rapor"]');
    var btn = document.getElementById('btnSimpanKop');
    var btnText = document.getElementById('btnSimpanText');
    var btnLoading = document.getElementById('btnSimpanLoading');

    if (form && btn) {
        form.addEventListener('submit', function() {
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
        });
    }

    // Kalau ada success message, force reload preview images
    @if(session('success'))
    document.querySelectorAll('.card-tartil img').forEach(function(img) {
        // Force re-render dengan tambahkan hash
        var src = img.getAttribute('src');
        if (src && src.indexOf('?') === -1) {
            img.setAttribute('src', src + '?v={{ time() }}');
        }
    });
    @endif
})();
</script>
@endsection
