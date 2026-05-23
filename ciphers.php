<?php
// ============================================================
// ciphers.php — All cipher logic (included by index.php)
// ============================================================

// ── AFFINE CIPHER ───────────────────────────────────────────

function gcd($a, $b) {
    return $b == 0 ? $a : gcd($b, $a % $b);
}

function modInverse($a, $m) {
    for ($x = 1; $x < $m; $x++)
        if (($a * $x) % $m == 1) return $x;
    return -1;
}

function affineEncrypt($text, $a, $b) {
    if (gcd($a, 26) !== 1) return "Error: 'a' must be coprime with 26.";
    $result = '';
    foreach (str_split(strtoupper($text)) as $ch) {
        if (ctype_alpha($ch)) {
            $result .= chr(((($a * (ord($ch) - 65)) + $b) % 26) + 65);
        } else {
            $result .= $ch;
        }
    }
    return $result;
}

function affineDecrypt($text, $a, $b) {
    if (gcd($a, 26) !== 1) return "Error: 'a' must be coprime with 26.";
    $aInv = modInverse($a, 26);
    if ($aInv === -1) return "Error: Modular inverse does not exist.";
    $result = '';
    foreach (str_split(strtoupper($text)) as $ch) {
        if (ctype_alpha($ch)) {
            $result .= chr((($aInv * ((ord($ch) - 65 - $b + 26 * 100)) % 26)) % 26 + 65);
        } else {
            $result .= $ch;
        }
    }
    return $result;
}

// ── PIGPEN CIPHER ───────────────────────────────────────────

function pigpenEncode($text) {
    $map = [
        'A'=>'⊓','B'=>'⊔','C'=>'⌐','D'=>'¬','E'=>'⊏','F'=>'⊐',
        'G'=>'⌐̣','H'=>'⊓̣','I'=>'⊔̣','J'=>'△','K'=>'▽','L'=>'▷',
        'M'=>'◁','N'=>'△̣','O'=>'▽̣','P'=>'▷̣','Q'=>'◁̣',
        'R'=>'⊠','S'=>'⊡','T'=>'⊞','U'=>'⊟',
        'V'=>'◇','W'=>'◆','X'=>'◈','Y'=>'◉','Z'=>'◊',
        ' '=>' '
    ];
    $result = '';
    foreach (str_split(strtoupper($text)) as $ch) {
        $result .= isset($map[$ch]) ? $map[$ch] : $ch;
    }
    return $result;
}

function pigpenDecode($text) {
    $map = [
        '⊓'=>'A','⊔'=>'B','⌐'=>'C','¬'=>'D','⊏'=>'E','⊐'=>'F',
        '⌐̣'=>'G','⊓̣'=>'H','⊔̣'=>'I','△'=>'J','▽'=>'K','▷'=>'L',
        '◁'=>'M','△̣'=>'N','▽̣'=>'O','▷̣'=>'P','◁̣'=>'Q',
        '⊠'=>'R','⊡'=>'S','⊞'=>'T','⊟'=>'U',
        '◇'=>'V','◆'=>'W','◈'=>'X','◉'=>'Y','◊'=>'Z',
        ' '=>' '
    ];
    $result = '';
    preg_match_all('/./us', $text, $chars);
    foreach ($chars[0] as $ch) {
        $result .= isset($map[$ch]) ? $map[$ch] : $ch;
    }
    return $result;
}

// ── PLAYFAIR CIPHER ─────────────────────────────────────────

function buildPlayfairMatrix($key) {
    $key = strtoupper(preg_replace('/[^A-Za-z]/', '', $key));
    $key = str_replace('J', 'I', $key);
    $used = [];
    $matrix = [];
    foreach (str_split($key) as $ch) {
        if (!in_array($ch, $used)) { $used[] = $ch; $matrix[] = $ch; }
    }
    foreach (range('A', 'Z') as $ch) {
        if ($ch === 'J') continue;
        if (!in_array($ch, $used)) { $used[] = $ch; $matrix[] = $ch; }
    }
    return $matrix;
}

function playfairPosition($matrix, $ch) {
    $idx = array_search($ch, $matrix);
    return [$idx % 5, intdiv($idx, 5)]; // [col, row]
}

function playfairEncrypt($text, $key) {
    $matrix = buildPlayfairMatrix($key);
    $text = strtoupper(preg_replace('/[^A-Za-z]/', '', $text));
    $text = str_replace('J', 'I', $text);
    $pairs = [];
    $i = 0;
    while ($i < strlen($text)) {
        $a = $text[$i];
        $b = ($i + 1 < strlen($text)) ? $text[$i + 1] : 'X';
        if ($a === $b) { $b = 'X'; $i++; } else { $i += 2; }
        $pairs[] = [$a, $b];
    }
    $result = '';
    foreach ($pairs as [$a, $b]) {
        [$ac, $ar] = playfairPosition($matrix, $a);
        [$bc, $br] = playfairPosition($matrix, $b);
        if ($ar === $br) {
            $result .= $matrix[$ar * 5 + ($ac + 1) % 5];
            $result .= $matrix[$br * 5 + ($bc + 1) % 5];
        } elseif ($ac === $bc) {
            $result .= $matrix[(($ar + 1) % 5) * 5 + $ac];
            $result .= $matrix[(($br + 1) % 5) * 5 + $bc];
        } else {
            $result .= $matrix[$ar * 5 + $bc];
            $result .= $matrix[$br * 5 + $ac];
        }
    }
    return $result;
}

function playfairDecrypt($text, $key) {
    $matrix = buildPlayfairMatrix($key);
    $text = strtoupper(preg_replace('/[^A-Za-z]/', '', $text));
    $pairs = [];
    for ($i = 0; $i < strlen($text); $i += 2) {
        $pairs[] = [$text[$i], $text[$i + 1] ?? 'X'];
    }
    $result = '';
    foreach ($pairs as [$a, $b]) {
        [$ac, $ar] = playfairPosition($matrix, $a);
        [$bc, $br] = playfairPosition($matrix, $b);
        if ($ar === $br) {
            $result .= $matrix[$ar * 5 + ($ac + 4) % 5];
            $result .= $matrix[$br * 5 + ($bc + 4) % 5];
        } elseif ($ac === $bc) {
            $result .= $matrix[(($ar + 4) % 5) * 5 + $ac];
            $result .= $matrix[(($br + 4) % 5) * 5 + $bc];
        } else {
            $result .= $matrix[$ar * 5 + $bc];
            $result .= $matrix[$br * 5 + $ac];
        }
    }
    return $result;
}

// ── PROCESS FORM ────────────────────────────────────────────

$output    = null;
$activeTab = $_POST['cipher'] ?? 'affine';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $cipher    = $_POST['cipher'];
    $inputText = $_POST['input_text'] ?? '';
    $action    = $_POST['action'];

    if ($cipher === 'affine') {
        $a      = intval($_POST['affine_a'] ?? 1);
        $b      = intval($_POST['affine_b'] ?? 0);
        $output = $action === 'encrypt'
            ? affineEncrypt($inputText, $a, $b)
            : affineDecrypt($inputText, $a, $b);

    } elseif ($cipher === 'pigpen') {
        $output = $action === 'encrypt'
            ? pigpenEncode($inputText)
            : pigpenDecode($inputText);

    } elseif ($cipher === 'playfair') {
        $pfKey  = $_POST['playfair_key'] ?? 'KEY';
        $output = $action === 'encrypt'
            ? playfairEncrypt($inputText, $pfKey)
            : playfairDecrypt($inputText, $pfKey);
    }
}
