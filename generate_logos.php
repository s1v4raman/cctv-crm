<?php

$colors = [
    '2563eb' => [37, 99, 235],   // Royal Blue
    '1e293b' => [30, 41, 59],   // Navy
    '0d9488' => [13, 148, 136], // Teal
    '16a34a' => [22, 163, 74],  // Forest Green
    '65a30d' => [101, 163, 13], // Olive Green
    'd97706' => [217, 119, 6],  // Amber Gold
    'c2410c' => [194, 65, 12],  // Terracotta Rust
    'be123c' => [190, 18, 60],  // Ruby Red
    '831843' => [131, 24, 67],  // Plum Magenta
    '581c87' => [88, 28, 135],  // Deep Purple
    '4338ca' => [67, 56, 202],  // Indigo
    '0284c7' => [2, 132, 199],  // Ocean Blue
    '78350f' => [120, 53, 15],  // Espresso Brown
];

if (!is_dir(__DIR__ . '/public/logos')) {
    mkdir(__DIR__ . '/public/logos', 0755, true);
}

$orig = imagecreatefrompng(__DIR__ . '/public/logo.png');
$origW = imagesx($orig);
$origH = imagesy($orig);

// Resize to crisp 256x256 for fast web delivery and super sharp display
$targetSize = 256;
$scaled = imagecreatetruecolor($targetSize, $targetSize);
imagealphablending($scaled, false);
imagesavealpha($scaled, true);
$transparent = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
imagefill($scaled, 0, 0, $transparent);
imagecopyresampled($scaled, $orig, 0, 0, 0, 0, $targetSize, $targetSize, $origW, $origH);

foreach ($colors as $hex => [$tr, $tg, $tb]) {
    $dest = imagecreatetruecolor($targetSize, $targetSize);
    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    imagefill($dest, 0, 0, $transparent);

    for ($x = 0; $x < $targetSize; $x++) {
        for ($y = 0; $y < $targetSize; $y++) {
            $rgba = imagecolorat($scaled, $x, $y);
            $a = ($rgba >> 24) & 0x7F;
            if ($a >= 120) {
                // fully transparent
                imagesetpixel($dest, $x, $y, $transparent);
                continue;
            }

            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;

            // Preserve pure white & near-white highlights (camera, precision text, stars, inner ring)
            if ($r > 200 && $g > 200 && $b > 200 && abs($r - $g) < 30 && abs($g - $b) < 30) {
                $col = imagecolorallocatealpha($dest, $r, $g, $b, $a);
                imagesetpixel($dest, $x, $y, $col);
                continue;
            }

            // Calculate luminance / ink strength of the blue line work
            $lum = ($r * 0.299 + $g * 0.587 + $b * 0.114) / 255.0;
            $ink = max(0.0, min(1.0, 1.0 - $lum));

            // Map ink to the target theme color
            $nr = (int) round(255 - $ink * (255 - $tr));
            $ng = (int) round(255 - $ink * (255 - $tg));
            $nb = (int) round(255 - $ink * (255 - $tb));

            $nr = max(0, min(255, $nr));
            $ng = max(0, min(255, $ng));
            $nb = max(0, min(255, $nb));

            $col = imagecolorallocatealpha($dest, $nr, $ng, $nb, $a);
            imagesetpixel($dest, $x, $y, $col);
        }
    }

    $outPath = __DIR__ . '/public/logos/logo_' . $hex . '.png';
    imagepng($dest, $outPath, 9);
    imagedestroy($dest);
    echo "Generated: logo_{$hex}.png (" . filesize($outPath) . " bytes)\n";
}

imagedestroy($scaled);
imagedestroy($orig);
echo "All 13 logos generated successfully!\n";
