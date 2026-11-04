<?php

declare(strict_types=1);

namespace App\services;

/**
 * Global Enterprise Name & Cultural Title Normalization Service
 *
 * Ensures all person names across FamilyPlatform (especially the Family Tree,
 * Organogram, and Lineage Studio) adhere strictly to Capital-First ("Sentence Casing" /
 * Title Case) for first names, middle names, and surnames.
 *
 * Truly Global & Culturally Inclusive:
 * Honors titles and honorific traditions from families across all nations and cultures worldwide:
 * - Standard & Universal: Mr, Mrs, Ms, Miss, Master, Mx
 * - Academic & Professional: Dr, Doctor, Prof, Professor, Engr, Engineer, Architect, Barrister, Advocate, Counsel
 * - Royalty & Nobility: King, Queen, Prince, Princess, Emperor, Empress, Sultan, Sultana, Emir, Raja, Rani, Maharaja, Tsar, Duke, Duchess, Earl, Count, Countess, Lord, Lady, Sir, Dame, Baron, Baroness
 * - Spiritual & Religious: Reverend, Rev, Pastor, Father, Mother, Brother, Sister, Bishop, Archbishop, Cardinal, Rabbi, Cantor, Imam, Sheikh, Mufti, Pandit, Swami, Monk, Lama, Deacon, Elder, Alhaji, Alhaja
 * - Traditional, Indigenous & Clan: Chief, High Chief, Chieftain, Clan Head, Elder, Baale, Oba, Eze, Igwe, Otunba, Oloye, Laird
 * - Civil, Diplomatic & Judicial: Honourable, Hon, Judge, Justice, Senator, Governor, Ambassador, Chancellor, President, Minister, Mayor
 * - Multilingual Honorifics: Monsieur, Madame, Herr, Frau, Don, Doña, Senhor, Senhora, Signor, Signora
 * - Post-nominals & Generational Suffixes: Jr, Sr, II, III, IV, V, PhD, MD, MBBS, OBE, MBE, CBE, KC, QC
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class NameFormatterService
{
    /**
     * Recognized multi-word and compound cultural titles that should be formatted together
     * @var array<string, string> Lowercase key => canonical formatted string
     */
    public const COMPOUND_TITLES = [
        'high chief'     => 'High Chief',
        'assoc prof'     => 'Assoc. Prof.',
        'assoc. prof.'   => 'Assoc. Prof.',
        'rev dr'         => 'Rev. Dr.',
        'rev. dr.'       => 'Rev. Dr.',
        'rev father'     => 'Rev. Father',
        'rev. father'    => 'Rev. Father',
        'pastor mrs'     => 'Pastor (Mrs)',
        'pastor (mrs)'   => 'Pastor (Mrs)',
        'dr mrs'         => 'Dr (Mrs)',
        'dr (mrs)'       => 'Dr (Mrs)',
        'chief mrs'      => 'Chief (Mrs)',
        'chief (mrs)'    => 'Chief (Mrs)',
        'prof mrs'       => 'Prof. (Mrs)',
        'prof (mrs)'     => 'Prof. (Mrs)',
        'clan head'      => 'Clan Head',
        'tribal chief'   => 'Tribal Chief',
        'al-hajj'        => 'Al-Hajj',
        'al hajj'        => 'Al-Hajj',
    ];

    /**
     * Recognized single-word cultural, academic, royal, religious, and honorific titles globally
     * @var array<string, string> Lowercase key => canonical formatted string
     */
    public const SINGLE_TITLES = [
        // Standard & Everyday Global Honorifics
        'mr'          => 'Mr',
        'mr.'         => 'Mr',
        'mrs'         => 'Mrs',
        'mrs.'        => 'Mrs',
        'ms'          => 'Ms',
        'ms.'         => 'Ms',
        'miss'        => 'Miss',
        'master'      => 'Master',
        'mx'          => 'Mx',

        // European & Romance Honorifics
        'monsieur'    => 'Monsieur',
        'madame'      => 'Madame',
        'mademoiselle'=> 'Mademoiselle',
        'herr'        => 'Herr',
        'frau'        => 'Frau',
        'don'         => 'Don',
        'dona'        => 'Dona',
        'doña'        => 'Doña',
        'senhor'      => 'Senhor',
        'senhora'     => 'Senhora',
        'signor'      => 'Signor',
        'signora'     => 'Signora',

        // Academic & Professional
        'prof'        => 'Prof.',
        'prof.'       => 'Prof.',
        'professor'   => 'Professor',
        'dr'          => 'Dr.',
        'dr.'         => 'Dr.',
        'doctor'      => 'Doctor',
        'engr'        => 'Engr.',
        'engr.'       => 'Engr.',
        'engineer'    => 'Engineer',
        'barr'        => 'Barr.',
        'barr.'       => 'Barr.',
        'barrister'   => 'Barrister',
        'advocate'    => 'Advocate',
        'counsel'     => 'Counsel',
        'pharm'       => 'Pharm.',
        'pharm.'      => 'Pharm.',
        'pharmacist'  => 'Pharmacist',
        'arch'        => 'Arch.',
        'arch.'       => 'Arch.',
        'architect'   => 'Architect',
        'surv'        => 'Surv.',
        'surv.'       => 'Surv.',
        'surveyor'    => 'Surveyor',

        // Global Royalty & Nobility
        'king'        => 'King',
        'queen'       => 'Queen',
        'prince'      => 'Prince',
        'princess'    => 'Princess',
        'emperor'     => 'Emperor',
        'empress'     => 'Empress',
        'sultan'      => 'Sultan',
        'sultana'     => 'Sultana',
        'emir'        => 'Emir',
        'raja'        => 'Raja',
        'rani'        => 'Rani',
        'maharaja'    => 'Maharaja',
        'maharani'    => 'Maharani',
        'tsar'        => 'Tsar',
        'tsarina'     => 'Tsarina',
        'archduke'    => 'Archduke',
        'archduchess' => 'Archduchess',
        'duke'        => 'Duke',
        'duchess'     => 'Duchess',
        'marquess'    => 'Marquess',
        'marchioness' => 'Marchioness',
        'marquis'     => 'Marquis',
        'earl'        => 'Earl',
        'count'       => 'Count',
        'countess'    => 'Countess',
        'viscount'    => 'Viscount',
        'viscountess' => 'Viscountess',
        'baron'       => 'Baron',
        'baroness'    => 'Baroness',
        'lord'        => 'Lord',
        'lady'        => 'Lady',
        'sir'         => 'Sir',
        'dame'        => 'Dame',
        'laird'       => 'Laird',

        // Global Religious & Spiritual
        'father'      => 'Father',
        'mother'      => 'Mother',
        'brother'     => 'Brother',
        'sister'      => 'Sister',
        'pastor'      => 'Pastor',
        'rev'         => 'Rev.',
        'rev.'        => 'Rev.',
        'reverend'    => 'Reverend',
        'bishop'      => 'Bishop',
        'archbishop'  => 'Archbishop',
        'cardinal'    => 'Cardinal',
        'rabbi'       => 'Rabbi',
        'cantor'      => 'Cantor',
        'imam'        => 'Imam',
        'sheikh'      => 'Sheikh',
        'mufti'       => 'Mufti',
        'pandit'      => 'Pandit',
        'swami'       => 'Swami',
        'guru'        => 'Guru',
        'monk'        => 'Monk',
        'lama'        => 'Lama',
        'apostle'     => 'Apostle',
        'prophet'     => 'Prophet',
        'prophetess'  => 'Prophetess',
        'evangelist'  => 'Evangelist',
        'elder'       => 'Elder',
        'deacon'      => 'Deacon',
        'deaconess'   => 'Deaconess',
        'alhaji'      => 'Alhaji',
        'alhaja'      => 'Alhaja',
        'mallam'      => 'Mallam',

        // Traditional, Indigenous & Cultural Chieftaincy
        'chief'       => 'Chief',
        'chieftain'   => 'Chieftain',
        'oba'         => 'Oba',
        'otunba'      => 'Otunba',
        'kabiyesi'    => 'Kabiyesi',
        'oloye'       => 'Oloye',
        'baale'       => 'Baale',
        'asiwaju'     => 'Asiwaju',
        'yeye'        => 'Yeye',
        'iyalode'     => 'Iyalode',
        'igwe'        => 'Igwe',
        'obi'         => 'Obi',
        'eze'         => 'Eze',
        'ichie'       => 'Ichie',
        'nna'         => 'Nna',

        // Civil, State & Diplomatic
        'hon'         => 'Hon.',
        'hon.'        => 'Hon.',
        'honourable'  => 'Honourable',
        'sen'         => 'Senator',
        'senator'     => 'Senator',
        'gov'         => 'Governor',
        'governor'    => 'Governor',
        'amb'         => 'Ambassador',
        'ambassador'  => 'Ambassador',
        'president'   => 'President',
        'premier'     => 'Premier',
        'minister'    => 'Minister',
        'chancellor'  => 'Chancellor',
        'mayor'       => 'Mayor',
        'justice'     => 'Justice',
        'judge'       => 'Judge',
        'gen'         => 'Gen.',
        'gen.'        => 'Gen.',
        'general'     => 'General',
        'col'         => 'Col.',
        'col.'        => 'Col.',
        'colonel'     => 'Colonel',
        'capt'        => 'Capt.',
        'capt.'       => 'Capt.',
        'captain'     => 'Captain',
        'major'       => 'Major'
    ];

    /**
     * Suffixes and post-nominals that must preserve uppercase representation
     * @var array<string, string> Lowercase key => canonical uppercase
     */
    public const POST_NOMINALS = [
        'jr'     => 'Jr.',
        'jr.'    => 'Jr.',
        'sr'     => 'Sr.',
        'sr.'    => 'Sr.',
        'ii'     => 'II',
        'iii'    => 'III',
        'iv'     => 'IV',
        'v'      => 'V',
        'vi'     => 'VI',
        'vii'    => 'VII',
        'viii'   => 'VIII',
        'san'    => 'SAN',
        'kc'     => 'KC',
        'qc'     => 'QC',
        'phd'    => 'PhD',
        'md'     => 'MD',
        'mbbs'   => 'MBBS',
        'cfr'    => 'CFR',
        'con'    => 'CON',
        'ofr'    => 'OFR',
        'oon'    => 'OON',
        'mfr'    => 'MFR',
        'mon'    => 'MON',
        'obe'    => 'OBE',
        'mbe'    => 'MBE',
        'cbe'    => 'CBE',
        'kbe'    => 'KBE',
        'dbe'    => 'DBE',
    ];

    /**
     * Format a single or multi-word person name into sentence/title case with Capital-First.
     *
     * Handles:
     * - All uppercase ("OLAWALE OLAOGUN" -> "Olawale Olaogun")
     * - All lowercase ("olawale olaogun" -> "Olawale Olaogun")
     * - Compound titles ("HIGH CHIEF ADEBAYO" -> "High Chief Adebayo")
     * - Single cultural titles ("CHIEF FOLORUNSHO" -> "Chief Folorunsho")
     * - Hyphenated names ("MARY-JANE" -> "Mary-Jane")
     * - Apostrophes ("O'CONNOR" -> "O'Connor", "D'ANGELO" -> "D'Angelo")
     * - Suffixes ("JOHN DOE III" -> "John Doe III")
     *
     * @param string|null $raw
     * @return string
     */
    public static function formatName(?string $raw): string
    {
        if ($raw === null) {
            return '';
        }

        $trimmed = trim(preg_replace('/\s+/u', ' ', $raw) ?? '');
        if ($trimmed === '') {
            return '';
        }

        // 1. Check for compound titles at the beginning (e.g. "High Chief ...")
        $prefix = '';
        $remainder = $trimmed;

        foreach (self::COMPOUND_TITLES as $lowerPattern => $canonical) {
            $len = strlen($lowerPattern);
            if (strncasecmp($remainder, $lowerPattern, $len) === 0) {
                // Must be followed by space or end of string
                if (strlen($remainder) === $len || substr($remainder, $len, 1) === ' ') {
                    $prefix = $canonical . ' ';
                    $remainder = trim(substr($remainder, $len));
                    break;
                }
            }
        }

        // 2. Tokenize remainder by space
        $words = $remainder !== '' ? explode(' ', $remainder) : [];
        $formattedWords = [];

        foreach ($words as $w) {
            $w = trim($w);
            if ($w === '') {
                continue;
            }

            $wLower = strtolower($w);

            // Check if word is a post-nominal / generational suffix
            if (isset(self::POST_NOMINALS[$wLower])) {
                $formattedWords[] = self::POST_NOMINALS[$wLower];
                continue;
            }

            // Check if word is a recognized single title
            if (isset(self::SINGLE_TITLES[$wLower])) {
                $formattedWords[] = self::SINGLE_TITLES[$wLower];
                continue;
            }

            // Handle hyphenated names (e.g., Mary-Jane, Al-Mansoor)
            if (str_contains($w, '-')) {
                $subParts = explode('-', $w);
                $formattedSubs = array_map(static function (string $sp): string {
                    return self::capitalizeWord($sp);
                }, $subParts);
                $formattedWords[] = implode('-', $formattedSubs);
                continue;
            }

            // Handle apostrophe names (e.g., O'Connor, D'Angelo, N'Diaye)
            if (str_contains($w, "'") || str_contains($w, "’")) {
                $delim = str_contains($w, "'") ? "'" : "’";
                $subParts = explode($delim, $w);
                $formattedSubs = array_map(static function (string $sp): string {
                    return self::capitalizeWord($sp);
                }, $subParts);
                $formattedWords[] = implode($delim, $formattedSubs);
                continue;
            }

            // Standard word capitalization
            $formattedWords[] = self::capitalizeWord($w);
        }

        $result = trim($prefix . implode(' ', $formattedWords));
        return $result !== '' ? $result : $trimmed;
    }

    /**
     * Capitalize a single word: first character uppercase, rest lowercase.
     * Preserves Celtic 'Mc' / 'Mac' when appropriate.
     *
     * @param string $word
     * @return string
     */
    private static function capitalizeWord(string $word): string
    {
        $word = trim($word);
        if ($word === '') {
            return '';
        }

        $lower = mb_strtolower($word, 'UTF-8');

        // Special handling for Irish/Scottish "Mc" prefix (e.g. McDonald)
        if (str_starts_with($lower, 'mc') && mb_strlen($lower, 'UTF-8') > 2) {
            return 'Mc' . mb_strtoupper(mb_substr($lower, 2, 1, 'UTF-8'), 'UTF-8') . mb_substr($lower, 3, null, 'UTF-8');
        }

        // Standard Capital-First
        $first = mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8');
        $rest = mb_substr($lower, 1, null, 'UTF-8');
        return $first . $rest;
    }

    /**
     * Format first name and surname cleanly into individual and combined strings,
     * guarding against redundant repetitions (e.g. first_name="Wale Olaogun", last_name="Olaogun").
     *
     * @param string|null $first
     * @param string|null $last
     * @return array{first_name: string, last_name: string, full_name: string}
     */
    public static function formatFirstAndLast(?string $first, ?string $last): array
    {
        $cFirst = self::formatName($first);
        $cLast = self::formatName($last);

        if ($cFirst === '' && $cLast === '') {
            return [
                'first_name' => '',
                'last_name' => '',
                'full_name' => 'Relative'
            ];
        }

        if ($cFirst === '') {
            return [
                'first_name' => '',
                'last_name' => $cLast,
                'full_name' => $cLast
            ];
        }

        if ($cLast === '') {
            return [
                'first_name' => $cFirst,
                'last_name' => '',
                'full_name' => $cFirst
            ];
        }

        // If last_name is identical to first_name, or first_name ends with last_name
        if (strcasecmp($cFirst, $cLast) === 0) {
            $fullName = $cFirst;
        } elseif (str_ends_with(strtolower($cFirst), ' ' . strtolower($cLast))) {
            $fullName = $cFirst;
        } else {
            $fullName = $cFirst . ' ' . $cLast;
        }

        // Remove duplicate adjacent words
        $fullName = trim((string)preg_replace('/\b(\w+)\s+\1\b/iu', '$1', $fullName));

        return [
            'first_name' => $cFirst,
            'last_name'  => $cLast,
            'full_name'  => $fullName
        ];
    }

    /**
     * Curated global titles and honorifics for UI dropdowns
     * @return array<string, array<int, string>>
     */
    public static function getCuratedTitleOptions(): array
    {
        return [
            'General & Everyday' => [
                'Mr', 'Mrs', 'Ms', 'Miss', 'Master', 'Mx'
            ],
            'Academic & Professional' => [
                'Dr.', 'Doctor', 'Prof.', 'Professor', 'Engr.', 'Barrister', 'Advocate', 'Architect'
            ],
            'Royalty & Nobility' => [
                'King', 'Queen', 'Prince', 'Princess', 'Lord', 'Lady', 'Sir', 'Dame', 'Duke', 'Duchess', 'Baron', 'Baroness', 'Count', 'Countess', 'Sultan', 'Emir', 'Raja'
            ],
            'Spiritual & Religious' => [
                'Father', 'Sister', 'Brother', 'Rev.', 'Reverend', 'Pastor', 'Bishop', 'Rabbi', 'Imam', 'Sheikh', 'Elder', 'Deacon', 'Deaconess', 'Alhaji', 'Alhaja'
            ],
            'Traditional & Cultural' => [
                'Chief', 'High Chief', 'Elder', 'Clan Head', 'Chieftain', 'Oba', 'Eze', 'Igwe', 'Otunba', 'Oloye'
            ],
            'Civil & Diplomatic' => [
                'Hon.', 'Judge', 'Justice', 'Senator', 'Governor', 'Ambassador', 'President'
            ]
        ];
    }
}
