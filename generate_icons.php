<?php

// Generator ikon TARTIL: buku Al-Quran (feather book-open) putih di atas hijau #0c8a5f.
// Sumber desain: SVG di resources/views/auth/login.blade.php (viewBox 24).

const HIJAU = [12, 138, 95];   // #0c8a5f
const PUTIH = [255, 255, 255];

/**
 * Bangun outline halaman kiri buku dalam grid 24x24 (mengikuti path SVG asli).
 * Halaman kanan = mirror di x=12.
 */
function titikHalamanKiri(): array
{
    $pts = [];
    $pts[] = [2, 3];
    $pts[] = [8, 3];
    // Arc r=4 center (8,7): dari (8,3) ke (12,7) — sudut -90°..0°
    for ($t = -90; $t <= 0; $t += 3) {
        $r = deg2rad($t);
        $pts[] = [8 + 4 * cos($r), 7 + 4 * sin($r)];
    }
    $pts[] = [12, 21];
    // Arc r=3 center (12,18): dari (12,21) ke (9,18) — sudut 90°..180°
    for ($t = 90; $t <= 180; $t += 3) {
        $r = deg2rad($t);
        $pts[] = [12 + 3 * cos($r), 18 + 3 * sin($r)];
    }
    $pts[] = [2, 18];

    return $pts;
}

function gambarIkon(int $size, float $lebarKontenPersen, bool $sudutMembulat): GdImage
{
    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, false);
    imagesavealpha($img, true);

    $transparan = imagecolorallocatealpha($img, 0, 0, 0, 127);
    $hijau = imagecolorallocate($img, ...HIJAU);
    $putih = imagecolorallocate($img, ...PUTIH);

    imagefill($img, 0, 0, $transparan);

    // Latar hijau: penuh (maskable/apple) atau persegi sudut membulat (any/favicon)
    if ($sudutMembulat) {
        $r = (int) round($size * 0.18);
        imagefilledrectangle($img, $r, 0, $size - $r - 1, $size - 1, $hijau);
        imagefilledrectangle($img, 0, $r, $size - 1, $size - $r - 1, $hijau);
        imagefilledellipse($img, $r, $r, $r * 2, $r * 2, $hijau);
        imagefilledellipse($img, $size - $r - 1, $r, $r * 2, $r * 2, $hijau);
        imagefilledellipse($img, $r, $size - $r - 1, $r * 2, $r * 2, $hijau);
        imagefilledellipse($img, $size - $r - 1, $size - $r - 1, $r * 2, $r * 2, $hijau);
    } else {
        imagefill($img, 0, 0, $hijau);
    }

    // Skala: buku menempati x 1..23 (22u) dan y 3..21 (18u) dalam grid 24
    $skala = ($size * $lebarKontenPersen) / 22;
    $ofsX = ($size - 22 * $skala) / 2 - 1 * $skala;
    $ofsY = ($size - 18 * $skala) / 2 - 3 * $skala;

    $proyeksi = fn ($p) => [
        (int) round($ofsX + $p[0] * $skala),
        (int) round($ofsY + $p[1] * $skala),
    ];

    $kiri = titikHalamanKiri();
    $kanan = array_map(fn ($p) => [24 - $p[0], $p[1]], $kiri);

    imagealphablending($img, true);
    foreach ([$kiri, $kanan] as $halaman) {
        $flat = [];
        foreach ($halaman as $p) {
            [$x, $y] = $proyeksi($p);
            $flat[] = $x;
            $flat[] = $y;
        }
        imagefilledpolygon($img, $flat, $putih);
    }

    // Garis punggung buku (spine) hijau di tengah
    [$sx1, $sy1] = $proyeksi([12, 7]);
    [$sx2, $sy2] = $proyeksi([12, 20.5]);
    $tebal = max(2, (int) round(0.8 * $skala));
    imagesetthickness($img, $tebal);
    imageline($img, $sx1, $sy1, $sx2, $sy2, $hijau);
    $d = (int) round($tebal);
    imagefilledellipse($img, $sx1, $sy1, $d, $d, $hijau);
    imagefilledellipse($img, $sx2, $sy2, $d, $d, $hijau);

    return $img;
}

function simpanPng(GdImage $img, string $path): void
{
    imagepng($img, $path, 9);
    imagedestroy($img);
    echo "OK $path\n";
}

function resize(GdImage $src, int $size): GdImage
{
    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $size, $size, imagesx($src), imagesy($src));

    return $dst;
}

// ─── Ikon PWA ───
simpanPng(gambarIkon(512, 0.66, true), 'public/icons/icon-512.png');
simpanPng(resize(gambarIkon(512, 0.66, true), 192), 'public/icons/icon-192.png');
simpanPng(gambarIkon(512, 0.52, false), 'public/icons/icon-maskable-512.png');
simpanPng(gambarIkon(180, 0.62, false), 'public/icons/apple-touch-icon.png');

// ─── Favicon ───
$master32 = resize(gambarIkon(512, 0.72, true), 32);
$master16 = resize(gambarIkon(512, 0.72, true), 16);
simpanPng($master32, 'public/favicon.png');
simpanPng($master16, 'public/favicon-16x16.png');

// favicon.ico = header ICO + PNG 16 & 32 (format PNG-in-ICO, didukung semua browser modern)
ob_start();
imagepng($master32);
$png32 = ob_get_clean();
ob_start();
imagepng($master16);
$png16 = ob_get_clean();
imagedestroy($master32);
imagedestroy($master16);

$entry = fn ($png, $dim) => pack('CCCCvvVV', $dim, $dim, 0, 0, 1, 32, strlen($png), 0);
$e1 = $entry($png16, 16);
$e2 = $entry($png32, 32);
$ofs1 = 6 + 16 * 2;
$ofs2 = $ofs1 + strlen($png16);
$e1 = substr_replace($e1, pack('V', $ofs1), 12, 4);
$e2 = substr_replace($e2, pack('V', $ofs2), 12, 4);
file_put_contents('public/favicon.ico', pack('vvv', 0, 1, 2).$e1.$e2.$png16.$png32);
echo "OK public/favicon.ico\n";
