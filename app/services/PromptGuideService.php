<?php

declare(strict_types=1);

namespace App\services;

/**
 * PromptGuideService — Programmatic SEO (pSEO) engine for FamilyPlatform.
 *
 * Powers themed, high-intent oral history guides (e.g. /prompts/[slug]) designed to
 * capture long-tail organic and AI-driven search traffic ("questions to ask grandmother",
 * "how to record family heirloom recipes", etc.) and convert visitors into private
 * family archive creators.
 */
final class PromptGuideService
{
    /**
     * Complete repository of thematic oral history prompt guides.
     *
     * @return array<string, array{
     *     slug: string,
     *     title: string,
     *     category: string,
     *     read_time: string,
     *     meta_description: string,
     *     summary: string,
     *     audio_tip: string,
     *     phases: array<int, array{phase_title: string, questions: array<int, string>}>,
     *     related_slugs: array<int, string>
     * }>
     */
    public static function all(): array
    {
        $static = [
            'questions-to-ask-grandmother-about-childhood' => [
                'slug' => 'questions-to-ask-grandmother-about-childhood',
                'title' => '35 Heartfelt Questions to Ask Your Grandmother About Her Childhood & Youth',
                'category' => 'Grandparents & Elders',
                'read_time' => '6 min read',
                'meta_description' => 'Gentle, evocative questions to ask your grandmother about growing up, schoolyard games, early friendships, and childhood lessons before her stories fade.',
                'summary' => 'Our grandmothers hold the living heartbeat of family origins. This guided interview sequence is crafted to gently unlock childhood memories without fatiguing your elder.',
                'audio_tip' => 'Place your recording device or phone on a soft cushion roughly 12 inches away from her favorite armchair to avoid clothing rustle.',
                'phases' => [
                    [
                        'phase_title' => 'Phase 1: Early Childhood Sensations & Home Life',
                        'questions' => [
                            'What was the very first bedroom or house you remember living in?',
                            'What sounds or scents woke you up on a Saturday morning as a young girl?',
                            'Who lived in your home when you were small, and who cooked the main meals?',
                            'What was your most cherished toy or possession, and where did it come from?',
                            'Did your family have a garden or keep animals, and what chores were yours?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 2: School Days, Friendships & Mischief',
                        'questions' => [
                            'How did you travel to school each day, and what did you carry your lunch in?',
                            'Who was your closest childhood companion, and what games did you invent together?',
                            'What was your favorite subject or teacher, and who was the strictest?',
                            'Did you ever get into playful trouble at school or at home?',
                            'What songs, radio broadcasts, or childhood games were popular in your neighborhood?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 3: Teenage Years, First Work & Courtship',
                        'questions' => [
                            'At what age did you leave school, and what was your very first paid job?',
                            'What did you do with your first paycheck or wage packet?',
                            'Where did young people gather on Friday and Saturday evenings?',
                            'How and where did you first meet grandfather, and what caught your attention?',
                            'What was your wedding or courtship like, and what did you wear?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 4: Reflections, Hardships & Ancestral Wisdom',
                        'questions' => [
                            'What historical national event made the deepest impression on your early life?',
                            'What was a difficult period your family endured, and how did you pull through?',
                            'What family trait or habit do you recognise in your children and grandchildren today?',
                            'If you could revisit one afternoon from your youth, which day would you choose?',
                            'What life lesson do you hope our family remembers 100 years from today?',
                        ],
                    ],
                ],
                'related_slugs' => [
                    'family-recipes-preservation-guide',
                    'oral-history-interview-questions-for-parents',
                    'questions-to-ask-grandfather-about-career',
                ],
            ],

            'questions-to-ask-grandfather-about-career' => [
                'slug' => 'questions-to-ask-grandfather-about-career',
                'title' => '30 Inspiring Questions to Ask Your Grandfather About His Trade, Career & Craft',
                'category' => 'Grandparents & Elders',
                'read_time' => '5 min read',
                'meta_description' => 'Unpack your grandfather\'s work ethic, early apprenticeships, mentorship, career triumphs, and practical craftsmanship with this structured interview guide.',
                'summary' => 'Many grandfathers express their devotion to family through a lifetime of work, discipline, and tradecraft. These questions invite him to speak with pride about his professional journey.',
                'audio_tip' => 'Ask him to hold or show you an old tool, badge, photograph, or union card while speaking; tactile objects instantly jog deep episodic recall.',
                'phases' => [
                    [
                        'phase_title' => 'Phase 1: Apprenticeship & First Employment',
                        'questions' => [
                            'What did your father or mother do for work, and did they expect you to follow them?',
                            'What was your very first job interview or first day of work like?',
                            'Who took you under their wing and taught you the ropes of your trade?',
                            'What mistake did you make as a beginner that taught you an unforgettable lesson?',
                            'What were your working hours, conditions, and weekly take-home pay back then?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 2: Master Craft, Tools & Comradeship',
                        'questions' => [
                            'What tools, machines, or skills did you use every day that younger people don\'t know about today?',
                            'Who was the most talented or colorful character you worked alongside?',
                            'What was the proudest project, product, or building you ever contributed to?',
                            'Did you ever have to take a major financial or career gamble to protect the family?',
                            'How did technology change your trade or industry over your working decades?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 3: Balance, Retirement & Legacy',
                        'questions' => [
                            'How did you manage to balance grueling working hours with being a father at home?',
                            'What did retirement feel like on that final Friday afternoon?',
                            'What gave you the deepest sense of purpose across your entire career?',
                            'What piece of advice would you give to a young family member starting their career today?',
                        ],
                    ],
                ],
                'related_slugs' => [
                    'questions-to-ask-grandmother-about-childhood',
                    'military-service-family-history-questions',
                    'oral-history-interview-questions-for-parents',
                ],
            ],

            'family-recipes-preservation-guide' => [
                'slug' => 'family-recipes-preservation-guide',
                'title' => 'The Complete Guide to Preserving Handwritten Family Heirloom Recipes',
                'category' => 'Heirlooms & Culture',
                'read_time' => '7 min read',
                'meta_description' => 'Step-by-step methods to chronicle grandmother\'s secret spices, handwritten recipe cards, and holiday culinary rituals into a private multi-generational digital archive.',
                'summary' => 'Heirloom family recipes are rarely recorded with metric precision—they exist in pinches, handfuls, and the rhythm of a wooden spoon. This guide ensures your family flavors live forever.',
                'audio_tip' => 'Record in the kitchen while preparing the dish together. The sizzle of oil, boiling water, and kitchen laughter create an irreplaceable audio time capsule.',
                'phases' => [
                    [
                        'phase_title' => 'Phase 1: Deciphering the Recipe Origin',
                        'questions' => [
                            'Who taught you how to prepare this dish, and what era does it date back to?',
                            'Was this an everyday comfort meal or reserved for religious feasts and celebrations?',
                            'Where did your family source the ingredients when you were growing up?',
                            'Did the recipe change when you moved or when certain staples weren\'t available?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 2: Translating "A Pinch" into Real Measurements',
                        'questions' => [
                            'How do you know when the dough, sauce, or stew has reached the correct texture?',
                            'What is the secret seasoning or step that commercial cookbooks always miss?',
                            'What specific pots, pans, or spoons have been used to cook this over the decades?',
                            'How long should it simmer, rest, or bake before serving at the table?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 3: The Memories Around the Dining Table',
                        'questions' => [
                            'Who always asked for seconds or fought over the crust at Sunday supper?',
                            'What stories were usually told around the table when this meal was served?',
                            'What does the aroma of this dish make you feel when you smell it today?',
                        ],
                    ],
                ],
                'related_slugs' => [
                    'holiday-traditions-recording-guide',
                    'questions-to-ask-grandmother-about-childhood',
                    'immigrant-family-origin-story-guide',
                ],
            ],

            'holiday-traditions-recording-guide' => [
                'slug' => 'holiday-traditions-recording-guide',
                'title' => 'Documenting Holiday Rituals, New Year Customs & Family Traditions',
                'category' => 'Family Customs',
                'read_time' => '5 min read',
                'meta_description' => 'A structured guide to recording unique annual family customs, religious ceremonies, New Year rituals, and quirky holiday games across generations.',
                'summary' => 'Every family possesses unwritten holiday laws—who carved the roast, what song was sung at midnight, and the funny gifts that made everyone weep with laughter. Archive them today.',
                'audio_tip' => 'Ask a young grandchild to ask the questions; elders soften and provide far richer, more animated answers when speaking to the youngest generation.',
                'phases' => [
                    [
                        'phase_title' => 'Phase 1: Holiday Morning Rituals',
                        'questions' => [
                            'What was the wake-up routine on holiday mornings in your childhood house?',
                            'What was the traditional holiday breakfast that never changed year after year?',
                            'Did your family attend church, temple, or community gatherings before opening gifts?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 2: Iconic Traditions & Funny Mishaps',
                        'questions' => [
                            'What was the most memorable holiday surprise or gift you ever received?',
                            'Tell me about a holiday dinner or gathering that went comically wrong.',
                            'What bizarre family game, quiz, or tradition exists only in our household?',
                            'Who was the relative that always stayed up latest talking by the fire?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 3: The True Spirit of Generosity',
                        'questions' => [
                            'How did your parents teach you about giving back or helping neighbors during the holidays?',
                            'What holiday traditions do you hope our children continue after we are gone?',
                        ],
                    ],
                ],
                'related_slugs' => [
                    'family-recipes-preservation-guide',
                    'oral-history-interview-questions-for-parents',
                    'childhood-home-and-neighbourhood-prompts',
                ],
            ],

            'oral-history-interview-questions-for-parents' => [
                'slug' => 'oral-history-interview-questions-for-parents',
                'title' => '40 Questions to Ask Your Parents Before Family Lore is Lost',
                'category' => 'Parents & Origins',
                'read_time' => '7 min read',
                'meta_description' => 'Powerful, intimate questions to ask your mother and father about their early dreams, hardships, parenting fears, and the day you were born.',
                'summary' => 'We often forget our parents were full human beings with wild ambitions, heartbreaks, and secret fears long before we arrived. This guide bridges that generation gap with reverence.',
                'audio_tip' => 'Conduct this in two separate 30-minute sessions rather than one marathon. Emotional depth peaks between minute 10 and 25.',
                'phases' => [
                    [
                        'phase_title' => 'Phase 1: Their Lives Before You Existed',
                        'questions' => [
                            'What were your biggest personal dreams in your early twenties before you had children?',
                            'How did you meet each other, and what was your very first date really like?',
                            'What was the hardest financial challenge you had to overcome as young adults?',
                            'Who was your biggest role model, and did you ever doubt yourself?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 2: The Day I Was Born & Early Parenthood',
                        'questions' => [
                            'What went through your mind when you first found out you were going to be parents?',
                            'What do you remember most about the day I was brought home from the hospital?',
                            'What was your biggest fear as young parents in that first year?',
                            'What was the funniest or most exhausting habit I had as a toddler?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 3: Family Core Values & Ancestral Pride',
                        'questions' => [
                            'What is one sacrifice you made for this family that you never told us about?',
                            'What is the proudest moment you have experienced seeing our family grow?',
                            'What does our family surname or heritage mean to you personally?',
                            'What do you understand about life now that you wish you knew at 25?',
                        ],
                    ],
                ],
                'related_slugs' => [
                    'questions-to-ask-grandmother-about-childhood',
                    'questions-to-ask-grandfather-about-career',
                    'immigrant-family-origin-story-guide',
                ],
            ],

            'military-service-family-history-questions' => [
                'slug' => 'military-service-family-history-questions',
                'title' => 'Recording a Veteran Relative\'s Service Memories with Dignity & Respect',
                'category' => 'Honour & Service',
                'read_time' => '6 min read',
                'meta_description' => 'A sensitive, dignified interview framework for capturing military service, letters home, wartime resilience, and comradeship without triggering trauma.',
                'summary' => 'Veterans often keep service memories locked in private vaults. This gentle framework allows them to chronicle their service, duty, comradeship, and civilian return safely.',
                'audio_tip' => 'Never press for combat specifics. Always focus on friendship, mail from home, rations, training, and the feeling of coming home.',
                'phases' => [
                    [
                        'phase_title' => 'Phase 1: Induction, Enlistment & Basic Training',
                        'questions' => [
                            'Were you drafted or did you volunteer, and how old were you when you enlisted?',
                            'What was boot camp or basic training like, and who was your drill instructor?',
                            'What was the food, barracks, and physical routine like each day?',
                            'What did your family say to you on the day you departed?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 2: Comradeship, Letters & Daily Routine',
                        'questions' => [
                            'Who became your closest friend or battle buddy in your unit?',
                            'How often did mail from home arrive, and what did you look forward to most?',
                            'What songs or pastimes kept spirits up during long waits or downtime?',
                            'What was the proudest citation, badge, or unit honor you received?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 3: The Return to Civilian Life',
                        'questions' => [
                            'What was the first meal you ate when you finally arrived back in your hometown?',
                            'How did your service shape how you raised your children and viewed your country?',
                            'What message of peace and resilience would you share with future generations?',
                        ],
                    ],
                ],
                'related_slugs' => [
                    'questions-to-ask-grandfather-about-career',
                    'oral-history-interview-questions-for-parents',
                    'childhood-home-and-neighbourhood-prompts',
                ],
            ],

            'immigrant-family-origin-story-guide' => [
                'slug' => 'immigrant-family-origin-story-guide',
                'title' => 'Chronicling Migration, Heritage & Homeland Journeys with Your Elders',
                'category' => 'Heritage & Migration',
                'read_time' => '6 min read',
                'meta_description' => 'Preserve the courage, courage, suitcases, language transitions, and homeland memories of your immigrant ancestors in their own voice.',
                'summary' => 'Every family with an immigrant journey owes its present comfort to the brave elders who packed their lives into two suitcases. Chronicle their extraordinary migration saga.',
                'audio_tip' => 'If your elder is more expressive in their native tongue, encourage them to answer in their mother tongue. FamilyPlatform preserves authentic dialects effortlessly.',
                'phases' => [
                    [
                        'phase_title' => 'Phase 1: Life in the Homeland',
                        'questions' => [
                            'What was your hometown, village, or city like before you departed?',
                            'What did the landscape, mountains, sea, or streets look like from your window?',
                            'What forced or inspired the brave decision to journey to a new country?',
                            'Who was left behind, and what was that bittersweet farewell like?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 2: The Journey & First Impressions',
                        'questions' => [
                            'Did you travel by ship, train, or plane, and how much money did you have in your pocket?',
                            'What was the very first thing you noticed when you stepped onto British soil?',
                            'How did you learn the language, find shelter, and land your first job?',
                            'What was the kindest gesture a stranger showed you in those early weeks?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 3: Dual Identity & Future Generations',
                        'questions' => [
                            'What cultural traditions or foods did you insist on keeping alive in your new home?',
                            'Did you ever feel torn between two cultures, and how did you resolve that?',
                            'What does it feel like to see your grandchildren thrive because of your courage?',
                        ],
                    ],
                ],
                'related_slugs' => [
                    'family-recipes-preservation-guide',
                    'oral-history-interview-questions-for-parents',
                    'childhood-home-and-neighbourhood-prompts',
                ],
            ],

            'childhood-home-and-neighbourhood-prompts' => [
                'slug' => 'childhood-home-and-neighbourhood-prompts',
                'title' => 'Mapping Childhood Homes, Neighbours & Growing Up in Past Decades',
                'category' => 'Origins & Community',
                'read_time' => '5 min read',
                'meta_description' => 'Interview prompts to map ancestral streets, corner shops, neighborhood personalities, and childhood home blueprints before they vanish.',
                'summary' => 'Streets change and old houses get demolished, but the memories of climbing the apple tree, buying penny sweets, and hearing the milkman on cobblestones remain vivid.',
                'audio_tip' => 'Ask your elder to sketch a rough floor plan of their childhood home on a piece of paper while speaking; it triggers memories of specific rooms and closets.',
                'phases' => [
                    [
                        'phase_title' => 'Phase 1: The Street & Corner Shop',
                        'questions' => [
                            'What was the name of your street, and what vehicles or horses traveled down it?',
                            'Who was the local shopkeeper, butcher, or baker where your mother shopped?',
                            'What was the soundscape of your neighborhood on an average Tuesday afternoon?',
                            'Did all the neighbors know each other, and could doors be left unlocked?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 2: Inside the Childhood House',
                        'questions' => [
                            'How was the house heated in winter, and was there an indoor bathroom or outdoor washhouse?',
                            'Where did everyone congregate when it was dark outside?',
                            'What was on the parlor walls, and did you have books or a wireless radio?',
                            'What was your favorite secret hiding spot inside or outside the house?',
                        ],
                    ],
                    [
                        'phase_title' => 'Phase 3: Outdoor Games & Freedom',
                        'questions' => [
                            'How far from home were you allowed to roam on summer days?',
                            'What street games did you play until the streetlamps flickered on?',
                            'What was the biggest adventure you had with the neighborhood children?',
                        ],
                    ],
                ],
                'related_slugs' => [
                    'questions-to-ask-grandmother-about-childhood',
                    'holiday-traditions-recording-guide',
                    'oral-history-interview-questions-for-parents',
                ],
            ],
        ];

        $dynamic = self::getDynamicGuides();
        return array_merge($static, $dynamic);
    }

    /**
     * Locate a guide by its URL slug.
     *
     * @return array{
     *     slug: string,
     *     title: string,
     *     category: string,
     *     read_time: string,
     *     meta_description: string,
     *     summary: string,
     *     audio_tip: string,
     *     phases: array<int, array{phase_title: string, questions: array<int, string>}>,
     *     related_slugs: array<int, string>
     * }|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $all = self::all();
        $slug = trim(strtolower($slug));
        return $all[$slug] ?? null;
    }

    /**
     * Get user-defined/CMS dynamic prompt guides from storage.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getDynamicGuides(): array
    {
        $file = dirname(__DIR__, 2) . '/storage/dynamic_prompt_guides.json';
        if (!file_exists($file)) {
            return [];
        }
        $raw = @file_get_contents($file);
        if (!$raw) {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Save or update a dynamic prompt guide via the Admin CMS.
     *
     * @param array{
     *     slug: string,
     *     title: string,
     *     category: string,
     *     read_time?: string,
     *     meta_description: string,
     *     summary: string,
     *     audio_tip?: string,
     *     phases: array<int, array{phase_title: string, questions: array<int, string>}>,
     *     related_slugs?: array<int, string>
     * } $guideData
     */
    public static function saveGuide(array $guideData): bool
    {
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($guideData['slug'] ?? '')));
        if (empty($slug)) {
            return false;
        }

        $existing = self::getDynamicGuides();
        $existing[$slug] = [
            'slug' => $slug,
            'title' => trim((string)($guideData['title'] ?? '')),
            'category' => trim((string)($guideData['category'] ?? 'Heritage & Customs')),
            'read_time' => trim((string)($guideData['read_time'] ?? '5 min read')) ?: '5 min read',
            'meta_description' => trim((string)($guideData['meta_description'] ?? '')),
            'summary' => trim((string)($guideData['summary'] ?? '')),
            'audio_tip' => trim((string)($guideData['audio_tip'] ?? 'Position recording microphone 12 inches away at chest level.')),
            'phases' => $guideData['phases'] ?? [],
            'related_slugs' => $guideData['related_slugs'] ?? [],
            'created_at' => date('Y-m-d H:i:s'),
            'custom' => true,
        ];

        $storageDir = dirname(__DIR__, 2) . '/storage';
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }
        $file = $storageDir . '/dynamic_prompt_guides.json';
        return (bool)@file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}
