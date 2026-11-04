<?php

namespace App\Services;

class SignatureService
{
    /**
     * Memproses gambar scan tanda tangan:
     * - Mengubah background kertas (putih / abu-abu / krem terang) menjadi transparan murni (Alpha channel).
     * - Menjaga coretan tinta (hitam / biru / warna gelap) agar tetap tajam dan bersih.
     * - Menyimpan hasil akhir dalam format PNG transparan berkualitas tinggi.
     */
    public static function processTransparentSignature(string $sourcePath, string $destinationPath): bool
    {
        if (! extension_loaded('gd')) {
            // Jika GD tidak aktif, copy file asli apa adanya
            return copy($sourcePath, $destinationPath);
        }

        $imageInfo = @getimagesize($sourcePath);
        if (! $imageInfo) {
            return copy($sourcePath, $destinationPath);
        }

        $mime = $imageInfo['mime'];
        $srcImg = null;

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $srcImg = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $srcImg = @imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $srcImg = @imagecreatefromwebp($sourcePath);
                }
                break;
        }

        if (! $srcImg) {
            return copy($sourcePath, $destinationPath);
        }

        $width = imagesx($srcImg);
        $height = imagesy($srcImg);

        // Buat canvas truecolor baru dengan alpha channel
        $destImg = imagecreatetruecolor($width, $height);
        imagealphablending($destImg, false);
        imagesavealpha($destImg, true);

        // Background default transparan
        $transparent = imagecolorallocatealpha($destImg, 0, 0, 0, 127);
        imagefilledrectangle($destImg, 0, 0, $width, $height, $transparent);

        // Thresholding untuk menghilangkan background kertas putih/abu
        // Skala kecerahan 0 - 255. Background kertas biasanya > 200 - 220
        $threshold = 210;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($srcImg, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $a = ($rgba >> 24) & 0x7F;

                // Jika piksel sumber sudah transparan (pada PNG)
                if ($a >= 120) {
                    continue;
                }

                // Hitung luminansi / tingkat terang warna (grayscale)
                $brightness = (int) (0.299 * $r + 0.587 * $g + 0.114 * $b);

                if ($brightness >= $threshold) {
                    // Piksel ini adalah background kertas -> jadikan transparan penuh
                    continue;
                }

                // Untuk piksel semi-terang (tepian coretan pulpen), berikan transparansi halus (anti-aliasing)
                if ($brightness > ($threshold - 50)) {
                    $fadeFactor = ($brightness - ($threshold - 50)) / 50; // 0 s.d 1
                    $alpha = (int) (127 * $fadeFactor);
                    if ($alpha > 120) {
                        continue;
                    }
                    $color = imagecolorallocatealpha($destImg, $r, $g, $b, $alpha);
                } else {
                    // Coretan tinta pekat -> pertahankan warna asli dengan alpha 0 (solid)
                    $color = imagecolorallocatealpha($destImg, $r, $g, $b, 0);
                }

                imagesetpixel($destImg, $x, $y, $color);
            }
        }

        // Simpan sebagai file PNG transparan
        $result = imagepng($destImg, $destinationPath, 9);

        imagedestroy($srcImg);
        imagedestroy($destImg);

        return $result;
    }
}
