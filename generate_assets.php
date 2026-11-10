<?php
/**
 * BPM Asset Generator: Creates dark neon cyberpunk SVG artworks for products and logos
 */

$productsDir = __DIR__ . '/assets/images/products';
$logoDir = __DIR__ . '/assets/images/logo';

if (!is_dir($productsDir)) mkdir($productsDir, 0777, true);
if (!is_dir($logoDir)) mkdir($logoDir, 0777, true);

// 1. Logo
$logoSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 80" width="320" height="80">
  <defs>
    <filter id="neon-glow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="3" result="blur" />
      <feMerge>
        <feMergeNode in="blur" />
        <feMergeNode in="SourceGraphic" />
      </feMerge>
    </filter>
    <linearGradient id="cyan-violet" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#00f0ff" />
      <stop offset="50%" stop-color="#a855f7" />
      <stop offset="100%" stop-color="#c8ff00" />
    </linearGradient>
  </defs>
  <!-- Background transparent -->
  <text x="5" y="55" font-family="'Unbounded', 'Montserrat', 'Arial Black', sans-serif" font-size="48" font-weight="900" fill="#ffffff" letter-spacing="2">
    BPM<tspan fill="#ccff00">.</tspan>
  </text>
  <!-- 144 BPM Equalizer Badge -->
  <g transform="translate(185, 26)">
    <rect x="0" y="0" width="110" height="30" rx="4" fill="#0d0d16" stroke="#00f0ff" stroke-width="1.5" />
    <!-- Equalizer bars -->
    <rect x="12" y="16" width="3" height="8" rx="1.5" fill="#ccff00" />
    <rect x="18" y="10" width="3" height="14" rx="1.5" fill="#00f0ff" />
    <rect x="24" y="6" width="3" height="18" rx="1.5" fill="#a855f7" />
    <text x="36" y="20" font-family="'JetBrains Mono', 'Consolas', monospace" font-size="12" font-weight="700" fill="#00f0ff" letter-spacing="1">144 BPM</text>
  </g>
</svg>
SVG;
file_put_contents("$logoDir/bpm-logo.svg", $logoSvg);

// Helper for Product Card SVGs
function createHoodieSvg($title, $subtitle, $color1, $color2, $badge, $hasPrint = true) {
    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700" width="600" height="700">
  <defs>
    <radialGradient id="bg-glow" cx="50%" cy="40%" r="60%">
      <stop offset="0%" stop-color="$color1" stop-opacity="0.25" />
      <stop offset="60%" stop-color="#0a0a14" stop-opacity="0.9" />
      <stop offset="100%" stop-color="#050508" stop-opacity="1" />
    </radialGradient>
    <linearGradient id="fabric-grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#191924" />
      <stop offset="50%" stop-color="#101018" />
      <stop offset="100%" stop-color="#09090d" />
    </linearGradient>
    <linearGradient id="neon-acc" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="$color1" />
      <stop offset="100%" stop-color="$color2" />
    </linearGradient>
    <filter id="glow">
      <feGaussianBlur stdDeviation="8" result="coloredBlur"/>
      <feMerge>
        <feMergeNode in="coloredBlur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <!-- Background -->
  <rect width="600" height="700" fill="url(#bg-glow)" />

  <!-- Cyber grid lines -->
  <g stroke="rgba(255,255,255,0.03)" stroke-width="1">
    <line x1="0" y1="100" x2="600" y2="100" />
    <line x1="0" y1="200" x2="600" y2="200" />
    <line x1="0" y1="300" x2="600" y2="300" />
    <line x1="0" y1="400" x2="600" y2="400" />
    <line x1="0" y1="500" x2="600" y2="500" />
    <line x1="0" y1="600" x2="600" y2="600" />
    <line x1="100" y1="0" x2="100" y2="700" />
    <line x1="200" y1="0" x2="200" y2="700" />
    <line x1="300" y1="0" x2="300" y2="700" />
    <line x1="400" y1="0" x2="400" y2="700" />
    <line x1="500" y1="0" x2="500" y2="700" />
  </g>

  <!-- Technical Marks -->
  <text x="35" y="45" font-family="'JetBrains Mono', monospace" font-size="11" fill="rgba(255,255,255,0.4)" letter-spacing="2">BPM SOUND SYSTEM // SPEC-500GSM</text>
  <text x="565" y="45" font-family="'JetBrains Mono', monospace" font-size="11" fill="$color2" text-anchor="end" font-weight="700">$badge</text>

  <!-- Hoodie Silhouette -->
  <g transform="translate(0, 20)">
    <!-- Shoulders & Torso -->
    <path d="M 170 140 
             Q 220 120 300 120 
             Q 380 120 430 140 
             L 530 260 
             L 470 300 
             L 435 240 
             L 435 540 
             Q 300 560 165 540 
             L 165 240 
             L 130 300 
             L 70 260 Z" 
          fill="url(#fabric-grad)" 
          stroke="rgba(255,255,255,0.12)" 
          stroke-width="2" />

    <!-- Hood -->
    <path d="M 215 130 
             C 210 70 250 40 300 40 
             C 350 40 390 70 385 130 
             Q 300 170 215 130 Z" 
          fill="#141420" 
          stroke="rgba(255,255,255,0.15)" 
          stroke-width="2" />
    <!-- Hood opening / collar -->
    <ellipse cx="300" cy="135" rx="55" ry="30" fill="#0b0b10" stroke="$color1" stroke-width="1.5" stroke-dasharray="4 2" />

    <!-- Drawstrings -->
    <path d="M 275 145 Q 265 210 270 260" stroke="$color2" stroke-width="2" fill="none" />
    <circle cx="270" cy="265" r="4" fill="$color2" />
    <path d="M 325 145 Q 335 210 330 260" stroke="$color2" stroke-width="2" fill="none" />
    <circle cx="330" cy="265" r="4" fill="$color2" />

    <!-- Kangaroo Pocket -->
    <path d="M 200 430 L 400 430 L 415 510 L 185 510 Z" fill="#151522" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" />

    <!-- VELCRO CONTACT PANEL (Modular print base) -->
    <g transform="translate(190, 205)">
      <!-- Velcro base rectangle with micro-texture -->
      <rect x="0" y="0" width="220" height="155" rx="8" fill="#1e1e2d" stroke="$color2" stroke-width="1.5" stroke-dasharray="6 3" />
      
      <!-- Velcro Corner Targets -->
      <circle cx="8" cy="8" r="3" fill="$color2" />
      <circle cx="212" cy="8" r="3" fill="$color2" />
      <circle cx="8" cy="147" r="3" fill="$color2" />
      <circle cx="212" cy="147" r="3" fill="$color2" />

      <!-- Active Mounted Print Preview or Velcro Base Text -->
      <rect x="10" y="10" width="200" height="135" rx="5" fill="#0d0d16" />
      
      <!-- Graphic Inside Velcro Patch -->
      <g transform="translate(10, 10)">
        <!-- Art: Psychedelic Matrix or Cyber Glitch -->
        <circle cx="100" cy="67" r="45" fill="none" stroke="$color1" stroke-width="2" filter="url(#glow)"/>
        <circle cx="100" cy="67" r="30" fill="none" stroke="$color2" stroke-width="1.5"/>
        <circle cx="100" cy="67" r="14" fill="$color2" />
        <!-- Radiating rays -->
        <line x1="100" y1="12" x2="100" y2="30" stroke="$color1" stroke-width="2" />
        <line x1="100" y1="104" x2="100" y2="122" stroke="$color1" stroke-width="2" />
        <line x1="45" y1="67" x2="63" y2="67" stroke="$color1" stroke-width="2" />
        <line x1="137" y1="67" x2="155" y2="67" stroke="$color1" stroke-width="2" />
        <text x="100" y="125" font-family="'JetBrains Mono', monospace" font-size="9" fill="$color2" text-anchor="middle" font-weight="700" letter-spacing="2">VELCRO PATCH // 144 BPM</text>
      </g>

      <!-- Label tag below patch -->
      <rect x="40" y="160" width="140" height="18" rx="3" fill="rgba(0,240,255,0.1)" stroke="$color2" stroke-width="0.8" />
      <text x="110" y="172" font-family="'JetBrains Mono', monospace" font-size="9" fill="#ffffff" text-anchor="middle">СМЕННАЯ ВЕЛКРО-БАЗА</text>
    </g>

    <!-- Hem and Cuffs with neon stitch accents -->
    <line x1="165" y1="535" x2="435" y2="535" stroke="$color1" stroke-width="2" stroke-dasharray="5 3" />
  </g>

  <!-- Bottom Spec Bar -->
  <rect x="30" y="625" width="540" height="50" rx="8" fill="rgba(15, 15, 25, 0.85)" stroke="rgba(255,255,255,0.1)" />
  <text x="50" y="655" font-family="'Unbounded', sans-serif" font-size="16" font-weight="800" fill="#ffffff">$title</text>
  <text x="550" y="655" font-family="'JetBrains Mono', monospace" font-size="14" font-weight="700" fill="$color2" text-anchor="end">VELCRO READY</text>
</svg>
SVG;
}

// Helper for Velcro Print SVGs (the patches)
function createPrintSvg($title, $symbolType, $c1, $c2) {
    $art = '';
    if ($symbolType === 'eye') {
        $art = <<<ART
        <ellipse cx="250" cy="180" rx="140" ry="75" fill="none" stroke="$c1" stroke-width="4" filter="url(#pglow)"/>
        <circle cx="250" cy="180" r="50" fill="#0d0d18" stroke="$c2" stroke-width="4"/>
        <circle cx="250" cy="180" r="24" fill="$c1" />
        <circle cx="240" cy="172" r="7" fill="#ffffff" />
        <!-- 8 smaller surrounding eyes -->
        <circle cx="160" cy="130" r="14" fill="none" stroke="$c2" stroke-width="2" />
        <circle cx="160" cy="130" r="6" fill="$c1" />
        <circle cx="340" cy="130" r="14" fill="none" stroke="$c2" stroke-width="2" />
        <circle cx="340" cy="130" r="6" fill="$c1" />
        <circle cx="160" cy="230" r="14" fill="none" stroke="$c2" stroke-width="2" />
        <circle cx="160" cy="230" r="6" fill="$c1" />
        <circle cx="340" cy="230" r="14" fill="none" stroke="$c2" stroke-width="2" />
        <circle cx="340" cy="230" r="6" fill="$c1" />
        <text x="250" y="295" font-family="'Unbounded', sans-serif" font-size="16" font-weight="900" fill="$c2" text-anchor="middle" letter-spacing="4">PSYCHO MATRIX</text>
ART;
    } elseif ($symbolType === 'equalizer') {
        $art = <<<ART
        <!-- Sound waves / equalizer -->
        <g transform="translate(70, 110)">
          <rect x="0" y="50" width="16" height="60" rx="4" fill="$c1" />
          <rect x="25" y="30" width="16" height="100" rx="4" fill="$c2" filter="url(#pglow)"/>
          <rect x="50" y="10" width="16" height="140" rx="4" fill="$c1" />
          <rect x="75" y="0" width="16" height="160" rx="4" fill="#ccff00" />
          <rect x="100" y="20" width="16" height="120" rx="4" fill="$c2" />
          <rect x="125" y="45" width="16" height="70" rx="4" fill="$c1" />
          <rect x="150" y="15" width="16" height="130" rx="4" fill="#ffffff" />
          <rect x="175" y="0" width="16" height="160" rx="4" fill="$c2" />
          <rect x="200" y="35" width="16" height="90" rx="4" fill="$c1" />
          <rect x="225" y="20" width="16" height="120" rx="4" fill="#ccff00" />
          <rect x="250" y="60" width="16" height="40" rx="4" fill="$c2" />
          <rect x="275" y="30" width="16" height="100" rx="4" fill="$c1" />
          <rect x="300" y="10" width="16" height="140" rx="4" fill="$c2" />
          <rect x="325" y="50" width="16" height="60" rx="4" fill="$c1" />
          <rect x="350" y="20" width="16" height="120" rx="4" fill="#ccff00" />
        </g>
        <text x="250" y="305" font-family="'JetBrains Mono', monospace" font-size="20" font-weight="900" fill="$c2" text-anchor="middle" letter-spacing="4">144 BPM AUDIO SPECTRUM</text>
ART;
    } elseif ($symbolType === 'skull') {
        $art = <<<ART
        <!-- Cyber Skull Glitch -->
        <path d="M 200 130 C 200 80 300 80 300 130 C 300 150 290 170 280 185 L 285 240 L 215 240 L 220 185 C 210 170 200 150 200 130 Z" fill="#141424" stroke="$c1" stroke-width="4" filter="url(#pglow)"/>
        <!-- Eyes -->
        <circle cx="230" cy="140" r="18" fill="$c2" />
        <circle cx="270" cy="140" r="18" fill="$c2" />
        <!-- Glitch lines -->
        <line x1="150" y1="125" x2="350" y2="125" stroke="#ccff00" stroke-width="3" stroke-dasharray="15 8 30 5" />
        <line x1="170" y1="170" x2="330" y2="170" stroke="$c2" stroke-width="3" stroke-dasharray="25 10 15 8" />
        <!-- Teeth -->
        <rect x="235" y="210" width="6" height="18" fill="#ffffff" />
        <rect x="247" y="210" width="6" height="18" fill="#ffffff" />
        <rect x="259" y="210" width="6" height="18" fill="#ffffff" />
        <text x="250" y="295" font-family="'Unbounded', sans-serif" font-size="16" font-weight="900" fill="#ffffff" text-anchor="middle">CYBER-GLITCH SKULL</text>
ART;
    } elseif ($symbolType === 'acid') {
        $art = <<<ART
        <!-- Melted Acid Smile -->
        <circle cx="250" cy="180" r="95" fill="#ccff00" filter="url(#pglow)"/>
        <!-- Big black eyes -->
        <ellipse cx="215" cy="155" rx="14" ry="24" fill="#0b0b12" />
        <ellipse cx="285" cy="155" rx="14" ry="24" fill="#0b0b12" />
        <!-- Dripping melted smile -->
        <path d="M 195 200 Q 250 260 305 200 Q 300 230 285 240 Q 275 280 265 240 Q 250 290 235 240 Q 215 260 195 200 Z" fill="#0b0b12" />
        <text x="250" y="320" font-family="'Unbounded', sans-serif" font-size="16" font-weight="900" fill="#ccff00" text-anchor="middle" letter-spacing="3">ACID ACID ACID</text>
ART;
    } elseif ($symbolType === 'tokyo') {
        $art = <<<ART
        <!-- Tokyo Kanji / Underground -->
        <circle cx="250" cy="175" r="90" fill="none" stroke="$c1" stroke-width="3" stroke-dasharray="12 6" />
        <text x="250" y="195" font-family="'Arial Black', sans-serif" font-size="80" font-weight="900" fill="$c2" text-anchor="middle" filter="url(#pglow)">音波</text>
        <text x="250" y="240" font-family="'JetBrains Mono', monospace" font-size="14" fill="#ffffff" text-anchor="middle" letter-spacing="6">TOKYO SUB-FREQ</text>
        <text x="250" y="295" font-family="'Unbounded', sans-serif" font-size="14" font-weight="800" fill="$c1" text-anchor="middle">UNDERGROUND RAVE</text>
ART;
    } else { // heart
        $art = <<<ART
        <!-- Biomecha Heart -->
        <path d="M 250 110 C 210 60 140 100 170 170 C 200 230 250 265 250 265 C 250 265 300 230 330 170 C 360 100 290 60 250 110 Z" fill="#18182b" stroke="$c1" stroke-width="4" filter="url(#pglow)"/>
        <!-- Neon pulse wires -->
        <path d="M 180 160 L 230 190 L 270 140 L 320 180" stroke="$c2" stroke-width="3" fill="none" />
        <circle cx="230" cy="190" r="5" fill="#ccff00" />
        <circle cx="270" cy="140" r="5" fill="#00f0ff" />
        <text x="250" y="305" font-family="'Unbounded', sans-serif" font-size="15" font-weight="800" fill="$c2" text-anchor="middle">BIOMECHA PULSE</text>
ART;
    }

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 380" width="500" height="380">
  <defs>
    <radialGradient id="pbg" cx="50%" cy="50%" r="65%">
      <stop offset="0%" stop-color="#141424" />
      <stop offset="70%" stop-color="#0a0a12" />
      <stop offset="100%" stop-color="#040407" />
    </radialGradient>
    <filter id="pglow">
      <feGaussianBlur stdDeviation="6" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
    <pattern id="velcro-hook" width="8" height="8" patternUnits="userSpaceOnUse">
      <circle cx="4" cy="4" r="1" fill="rgba(255,255,255,0.08)" />
    </pattern>
  </defs>

  <!-- Patch background with Velcro border stitches -->
  <rect x="15" y="15" width="470" height="350" rx="14" fill="url(#pbg)" stroke="$c2" stroke-width="2" />
  
  <!-- Outer velcro overlock border -->
  <rect x="22" y="22" width="456" height="336" rx="10" fill="url(#velcro-hook)" stroke="rgba(255,255,255,0.1)" stroke-width="1.5" stroke-dasharray="4 2" />

  <!-- Corner Velcro Grippers -->
  <rect x="28" y="28" width="14" height="14" rx="3" fill="$c1" />
  <rect x="458" y="28" width="14" height="14" rx="3" fill="$c1" />
  <rect x="28" y="338" width="14" height="14" rx="3" fill="$c1" />
  <rect x="458" y="338" width="14" height="14" rx="3" fill="$c1" />

  <!-- Technical Spec Header -->
  <text x="45" y="50" font-family="'JetBrains Mono', monospace" font-size="11" fill="rgba(255,255,255,0.4)" letter-spacing="2">VELCRO PATCH // 200x150MM</text>
  <text x="455" y="50" font-family="'JetBrains Mono', monospace" font-size="11" fill="$c2" text-anchor="end" font-weight="700">SWAPPABLE</text>

  <!-- Artwork center -->
  $art

  <!-- Technical bottom tag -->
  <text x="250" y="348" font-family="'JetBrains Mono', monospace" font-size="10" fill="rgba(255,255,255,0.35)" text-anchor="middle" letter-spacing="3">HOOK &amp; LOOP COMPATIBLE // BPM-04</text>
</svg>
SVG;
}

// Generate Clothes SVGs
$clothes = [
    'hoodie-psy.svg' => createHoodieSvg('PSY-MATRIX HOODIE', '500 GSM French Terry', '#a855f7', '#00f0ff', '#04 DROP', true),
    'hoodie-cyber.svg' => createHoodieSvg('CYBER-TURQUOISE HOODIE', '520 GSM Heavyweight', '#00f0ff', '#ccff00', 'LIMITED 50', true),
    'sweatshirt-violet.svg' => createHoodieSvg('SUB-BASS SWEATSHIRT', '480 GSM Organic Cotton', '#9333ea', '#00f0ff', 'VELCRO READY', false),
    'hoodie-zip.svg' => createHoodieSvg('DARK TECHNO ZIP-HOODIE', '500 GSM Double Zipper', '#00f0ff', '#a855f7', 'NEW DROP', true),
];

foreach ($clothes as $file => $content) {
    file_put_contents("$productsDir/$file", $content);
    // Also create .jpg alias so existing DB seeds work immediately
    file_put_contents("$productsDir/" . str_replace('.svg', '.jpg', $file), $content);
}

// Generate Prints SVGs
$prints = [
    'print-psy.svg' => createPrintSvg('PSY-MATRIX', 'eye', '#a855f7', '#00f0ff'),
    'print-equalizer.svg' => createPrintSvg('144 BPM EQUALIZER', 'equalizer', '#9333ea', '#00f0ff'),
    'print-skull.svg' => createPrintSvg('CYBER SKULL GLITCH', 'skull', '#00f0ff', '#ccff00'),
    'print-acid.svg' => createPrintSvg('ACID SMILE', 'acid', '#a855f7', '#ccff00'),
    'print-tokyo.svg' => createPrintSvg('TOKYO UNDERGROUND', 'tokyo', '#9333ea', '#00f0ff'),
    'print-heart.svg' => createPrintSvg('BIOMECHA PULSE', 'heart', '#a855f7', '#00f0ff'),
];

foreach ($prints as $file => $content) {
    file_put_contents("$productsDir/$file", $content);
    file_put_contents("$productsDir/" . str_replace('.svg', '.jpg', $file), $content);
}

echo "All high-aesthetic SVG & mock assets generated successfully!\n";
