<?php
$dir = __DIR__ . '/../public/assets/images';
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

function createPwaIcon($size, $outputPath) {
    $img = imagecreatetruecolor($size, $size);
    $bgColor = imagecolorallocate($img, 16, 32, 51); // Dark blue #102033
    $accentColor = imagecolorallocate($img, 31, 111, 235); // Blue #1f6feb
    $textColor = imagecolorallocate($img, 255, 255, 255);
    
    imagefill($img, 0, 0, $bgColor);
    
    // Draw box
    $margin = (int)($size * 0.15);
    imagefilledrectangle($img, $margin, $margin, $size - $margin, $size - $margin, $accentColor);
    
    // Simple text string drawing
    $text = "MAMY";
    $font = 5; // Built-in GD font
    $fontWidth = imagefontwidth($font);
    $fontHeight = imagefontheight($font);
    $x = (int)(($size - ($fontWidth * strlen($text))) / 2);
    $y = (int)(($size - $fontHeight) / 2);
    imagestring($img, $font, $x, $y, $text, $textColor);
    
    imagepng($img, $outputPath);
    imagedestroy($img);
}

createPwaIcon(192, $dir . '/icon-192.png');
createPwaIcon(512, $dir . '/icon-512.png');

echo "Icons created cleanly!\n";
