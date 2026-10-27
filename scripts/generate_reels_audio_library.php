<?php
declare(strict_types=1);

/**
 * Procedural Audio Soundtrack Generator for Family Reels
 * Generates lightweight, warm, royalty-free audio tracks for the family music library.
 */

$outputDir = __DIR__ . '/../public/audio/reels/library';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

function noteToFreq(string $note): float {
    $notes = ['C' => 0, 'C#' => 1, 'Db' => 1, 'D' => 2, 'D#' => 3, 'Eb' => 3, 'E' => 4, 'F' => 5, 'F#' => 6, 'Gb' => 6, 'G' => 7, 'G#' => 8, 'Ab' => 8, 'A' => 9, 'A#' => 10, 'Bb' => 10, 'B' => 11];
    preg_match('/^([A-G][#b]?)([0-8])$/', $note, $m);
    if (!$m) return 440.0;
    $semitones = $notes[$m[1]] + ((int)$m[2] - 4) * 12 - 9;
    return 440.0 * (2.0 ** ($semitones / 12.0));
}

function writeWav(string $filename, array $samples, int $sampleRate = 22050): void {
    $numSamples = count($samples);
    $dataSize = $numSamples * 2; // 16-bit mono
    $headerSize = 44;
    $fileSize = $headerSize + $dataSize - 8;

    $fp = fopen($filename, 'wb');
    if (!$fp) return;

    // RIFF chunk descriptor
    fwrite($fp, 'RIFF');
    fwrite($fp, pack('V', $fileSize));
    fwrite($fp, 'WAVE');

    // fmt sub-chunk
    fwrite($fp, 'fmt ');
    fwrite($fp, pack('V', 16)); // SubChunk1Size (16 for PCM)
    fwrite($fp, pack('v', 1));  // AudioFormat (1 = PCM)
    fwrite($fp, pack('v', 1));  // NumChannels (1 = Mono)
    fwrite($fp, pack('V', $sampleRate)); // SampleRate
    fwrite($fp, pack('V', $sampleRate * 2)); // ByteRate (SampleRate * NumChannels * BitsPerSample/8)
    fwrite($fp, pack('v', 2));  // BlockAlign (NumChannels * BitsPerSample/8)
    fwrite($fp, pack('v', 16)); // BitsPerSample (16 bits)

    // data sub-chunk
    fwrite($fp, 'data');
    fwrite($fp, pack('V', $dataSize));

    // Write samples
    foreach ($samples as $s) {
        $val = (int)round(max(-1.0, min(1.0, $s)) * 32767);
        fwrite($fp, pack('v', $val & 0xFFFF));
    }
    fclose($fp);
}

$sampleRate = 22050;
$durationSec = 25.0; // 25s loopable sound bed
$totalSamples = (int)($sampleRate * $durationSec);

echo "Generating Curated Family Audio Library...\n";

// 1. Birthday Fanfare (Joyful, brass/bell celebration arpeggio)
$samples = array_fill(0, $totalSamples, 0.0);
$chords = [
    ['C4', 'E4', 'G4', 'C5'],
    ['G3', 'B3', 'D4', 'G4'],
    ['A3', 'C4', 'E4', 'A4'],
    ['F3', 'A3', 'C4', 'F4'],
];
$chordDur = 3.125; // 8 chord bars
for ($t = 0; $t < $totalSamples; $t++) {
    $time = $t / $sampleRate;
    $chordIdx = ((int)($time / $chordDur)) % count($chords);
    $chord = $chords[$chordIdx];
    $localTime = fmod($time, 0.39); // 16th note arpeggiation
    $noteIdx = ((int)($time / 0.39)) % count($chord);
    $freq = noteToFreq($chord[$noteIdx]);
    $env = exp(-$localTime * 4.0); // Bell envelope
    $val = sin(2.0 * M_PI * $freq * $time) * 0.4 + sin(4.0 * M_PI * $freq * $time) * 0.15;
    // Add warm bass root
    $bassFreq = noteToFreq($chord[0]) / 2.0;
    $val += sin(2.0 * M_PI * $bassFreq * $time) * 0.25;
    $samples[$t] = $val * $env * 0.7;
}
writeWav($outputDir . '/birthday_fanfare.wav', $samples, $sampleRate);
echo "  ✅ Generated: birthday_fanfare.wav\n";

// 2. Acoustic Warmth (Gentle, fingerpicked warm folk guitar chords)
$samples = array_fill(0, $totalSamples, 0.0);
$chords = [
    ['D3', 'F#3', 'A3', 'D4', 'F#4'],
    ['G3', 'B3', 'D4', 'G4', 'B4'],
    ['B2', 'D3', 'F#3', 'B3', 'D4'],
    ['A2', 'C#3', 'E3', 'A3', 'C#4'],
];
for ($t = 0; $t < $totalSamples; $t++) {
    $time = $t / $sampleRate;
    $chordIdx = ((int)($time / 3.125)) % count($chords);
    $chord = $chords[$chordIdx];
    $pickTime = fmod($time, 0.25);
    $noteIdx = ((int)($time / 0.25)) % count($chord);
    $freq = noteToFreq($chord[$noteIdx]);
    $env = exp(-$pickTime * 5.0);
    $val = sin(2.0 * M_PI * $freq * $time) * 0.5 + sin(3.0 * M_PI * $freq * $time) * 0.15;
    $samples[$t] = $val * $env * 0.65;
}
writeWav($outputDir . '/acoustic_warmth.wav', $samples, $sampleRate);
echo "  ✅ Generated: acoustic_warmth.wav\n";

// 3. Gospel & Faith (Warm Hammond/church organ chords with vibrato)
$samples = array_fill(0, $totalSamples, 0.0);
$chords = [
    ['F3', 'A3', 'C4', 'E4'],
    ['Bb3', 'D4', 'F4', 'A4'],
    ['C3', 'E3', 'G3', 'Bb3'],
    ['F3', 'C4', 'F4', 'A4'],
];
for ($t = 0; $t < $totalSamples; $t++) {
    $time = $t / $sampleRate;
    $chordIdx = ((int)($time / 3.125)) % count($chords);
    $chord = $chords[$chordIdx];
    $vibrato = sin(2.0 * M_PI * 5.5 * $time) * 0.015; // 5.5Hz vibrato
    $val = 0.0;
    foreach ($chord as $n) {
        $freq = noteToFreq($n) * (1.0 + $vibrato);
        $val += sin(2.0 * M_PI * $freq * $time) * 0.15;
        $val += sin(4.0 * M_PI * $freq * $time) * 0.05; // 2nd harmonic
    }
    // Smooth swell envelope
    $chordTime = fmod($time, 3.125);
    $swell = sin((M_PI * $chordTime) / 3.125);
    $samples[$t] = $val * (0.6 + 0.4 * $swell);
}
writeWav($outputDir . '/gospel_faith.wav', $samples, $sampleRate);
echo "  ✅ Generated: gospel_faith.wav\n";

// 4. Afrobeats Celebration (Syncopated log-drum and rhythmic upbeat groove)
$samples = array_fill(0, $totalSamples, 0.0);
$notes = ['G3', 'Bb3', 'C4', 'D4', 'F4', 'G4'];
for ($t = 0; $t < $totalSamples; $t++) {
    $time = $t / $sampleRate;
    $beatTime = fmod($time, 0.5); // 120 BPM beat
    $drumEnv = exp(-$beatTime * 12.0);
    $drum = sin(2.0 * M_PI * 80.0 * (1.0 - $beatTime * 2.0) * $time) * $drumEnv * 0.5;
    
    // Syncopated marimba melody
    $stepTime = fmod($time, 0.25);
    $stepIdx = ((int)($time / 0.25)) % count($notes);
    $freq = noteToFreq($notes[$stepIdx]);
    $synthEnv = exp(-$stepTime * 8.0);
    $synth = (sin(2.0 * M_PI * $freq * $time) + 0.5 * sin(4.0 * M_PI * $freq * $time)) * $synthEnv * 0.35;
    $samples[$t] = ($drum + $synth) * 0.75;
}
writeWav($outputDir . '/afrobeats_groove.wav', $samples, $sampleRate);
echo "  ✅ Generated: afrobeats_groove.wav\n";

// 5. Lo-Fi Piano Nostalgia (Gentle Rhodes/piano chords with subtle vinyl warmth)
$samples = array_fill(0, $totalSamples, 0.0);
$chords = [
    ['E3', 'G3', 'B3', 'D4', 'F#4'], // Em9
    ['C3', 'E3', 'G3', 'B3', 'D4'],  // Cmaj9
    ['A2', 'C3', 'E3', 'G3', 'B3'],  // Am9
    ['B2', 'D#3', 'F#3', 'A3', 'C#4'], // B7alt
];
for ($t = 0; $t < $totalSamples; $t++) {
    $time = $t / $sampleRate;
    $chordIdx = ((int)($time / 3.125)) % count($chords);
    $chord = $chords[$chordIdx];
    $chordTime = fmod($time, 3.125);
    $val = 0.0;
    foreach ($chord as $n) {
        $freq = noteToFreq($n);
        $decay = exp(-$chordTime * 1.5);
        $val += sin(2.0 * M_PI * $freq * $time) * 0.14 * $decay;
        $val += sin(3.0 * M_PI * $freq * $time) * 0.04 * $decay;
    }
    // Soft noise bed (warm vinyl)
    $vinylNoise = ((mt_rand() / mt_getrandmax()) - 0.5) * 0.012;
    $samples[$t] = ($val + $vinylNoise) * 0.8;
}
writeWav($outputDir . '/lofi_nostalgia.wav', $samples, $sampleRate);
echo "  ✅ Generated: lofi_nostalgia.wav\n";

// 6. Wedding & Anniversary (Romantic sweeping orchestral bells & strings)
$samples = array_fill(0, $totalSamples, 0.0);
$chords = [
    ['D3', 'A3', 'D4', 'F#4'],
    ['A2', 'E3', 'A3', 'C#4'],
    ['B2', 'F#3', 'B3', 'D4'],
    ['G2', 'D3', 'G3', 'B3'],
];
for ($t = 0; $t < $totalSamples; $t++) {
    $time = $t / $sampleRate;
    $chordIdx = ((int)($time / 3.125)) % count($chords);
    $chord = $chords[$chordIdx];
    $val = 0.0;
    foreach ($chord as $n) {
        $freq = noteToFreq($n);
        $val += sin(2.0 * M_PI * $freq * $time) * 0.16;
        $val += sin(3.0 * M_PI * $freq * $time) * 0.06;
    }
    // High chimes
    $chimeTime = fmod($time, 1.56);
    $chimeFreq = noteToFreq($chord[3]) * 2.0;
    $chime = sin(2.0 * M_PI * $chimeFreq * $time) * exp(-$chimeTime * 6.0) * 0.18;
    $samples[$t] = ($val * 0.6 + $chime) * 0.75;
}
writeWav($outputDir . '/wedding_reunion.wav', $samples, $sampleRate);
echo "  ✅ Generated: wedding_reunion.wav\n";

echo "All 6 Curated Family Audio Tracks successfully created!\n";
