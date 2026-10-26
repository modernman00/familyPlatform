// Curated family GIFs, shared by the post composer and the comment box.
// Hosts must stay within the Giphy allowlist enforced by ProfilePage::sanitizeCommentGifUrl().
export const FAMILY_GIFS = [
    // Celebrate
    { id: 'g_cel_1', cat: 'celebrate', title: 'Party Confetti Celebration', url: 'https://media.giphy.com/media/artj92V8o75VPL7AeQ/giphy.gif' },
    { id: 'g_cel_2', cat: 'celebrate', title: 'Cheers Toast Champagne', url: 'https://media.giphy.com/media/g9582DNuQppxC/giphy.gif' },
    { id: 'g_cel_3', cat: 'celebrate', title: 'Celebration Fireworks', url: 'https://media.giphy.com/media/26tOZ42Mg6pbTUPHW/giphy.gif' },
    { id: 'g_cel_4', cat: 'celebrate', title: 'High Five Teamwork', url: 'https://media.giphy.com/media/3oEjHV0z8S7WM4MwnK/giphy.gif' },

    // Love & Hugs
    { id: 'g_lov_1', cat: 'love', title: 'Warm Big Hug', url: 'https://media.giphy.com/media/l8ooTMA4bGUgs/giphy.gif' },
    { id: 'g_lov_3', cat: 'love', title: 'Family Love Heart', url: 'https://media.giphy.com/media/M90mJvfWfd5mbUuULX/giphy.gif' },
    { id: 'g_lov_4', cat: 'love', title: 'Sending Hugs Bear', url: 'https://media.giphy.com/media/3bqtLDeiDtwhq/giphy.gif' },

    // Family
    { id: 'g_fam_1', cat: 'family', title: 'We Are Family Fast', url: 'https://media.giphy.com/media/amg2hcfGDkKt4Q3mV7/giphy.gif' },
    { id: 'g_fam_2', cat: 'family', title: 'Family Dinner Gathering', url: 'https://media.giphy.com/media/xT0xeMA62E1URCXqYo/giphy.gif' },
    { id: 'g_fam_3', cat: 'family', title: 'Proud Parents Cheer', url: 'https://media.giphy.com/media/3ohzdIuqJoo8QdKlnW/giphy.gif' },
    { id: 'g_fam_4', cat: 'family', title: 'Group Family Hug', url: 'https://media.giphy.com/media/VGtdt3dVAbQYTk0fnu/giphy.gif' },

    // Laughs
    { id: 'g_lau_1', cat: 'laugh', title: 'Laughing Out Loud LOL', url: 'https://media.giphy.com/media/10JhviFuU2gWD6/giphy.gif' },
    { id: 'g_lau_2', cat: 'laugh', title: 'Giggle Chuckle Cute', url: 'https://media.giphy.com/media/1d5Zn8FqmJqApu4hNU/giphy.gif' },
    { id: 'g_lau_3', cat: 'laugh', title: 'Rolling On Floor Laughing', url: 'https://media.giphy.com/media/CoDp6NnSmItoY/giphy.gif' },
    { id: 'g_lau_4', cat: 'laugh', title: 'Happy Tears Joy', url: 'https://media.giphy.com/media/26n6Gx9moCpHU1HO8/giphy.gif' },

    // Birthday
    { id: 'g_bdy_1', cat: 'birthday', title: 'Happy Birthday Cake', url: 'https://media.giphy.com/media/KdC9XVrVYO0LZhv1cU/giphy.gif' },
    { id: 'g_bdy_2', cat: 'birthday', title: 'Birthday Candles Wish', url: 'https://media.giphy.com/media/MCeIiRETfwBK2rtVAM/giphy.gif' },
    { id: 'g_bdy_3', cat: 'birthday', title: 'Birthday Balloons Celebration', url: 'https://media.giphy.com/media/GvjRy6pUMUjQxiKNlh/giphy.gif' },
    { id: 'g_bdy_4', cat: 'birthday', title: 'Birthday Dance Party', url: 'https://media.giphy.com/media/l2YWvIqfPvXgN2Q2k/giphy.gif' },

    // Joy / Dance
    { id: 'g_dnc_1', cat: 'dance', title: 'Happy Dance Groove', url: 'https://media.giphy.com/media/blSTtZehjAZ8I/giphy.gif' },
    { id: 'g_dnc_2', cat: 'dance', title: 'Pure Joy Dancing Snoopy', url: 'https://media.giphy.com/media/chzz1FQgqhytWRWbp3/giphy.gif' },
    { id: 'g_dnc_3', cat: 'dance', title: 'Excited Celebration Dance', url: 'https://media.giphy.com/media/5GoVLqeAOo6PK/giphy.gif' },
    { id: 'g_dnc_4', cat: 'dance', title: 'Victory Groove Celebration', url: 'https://media.giphy.com/media/l3q2wJsC23ikJg9xe/giphy.gif' }
];

export const GIF_CATEGORIES = [
    { key: 'all', label: '🌟 All' },
    { key: 'celebrate', label: '🎉 Celebrate' },
    { key: 'love', label: '❤️ Love' },
    { key: 'family', label: '👨‍👩‍👧‍👦 Family' },
    { key: 'laugh', label: '😂 Laughs' },
    { key: 'birthday', label: '🎂 Birthday' },
    { key: 'dance', label: '💃 Joy' },
];

export const isAllowedGifUrl = (url) => /^https:\/\/(media\d*\.)?giphy\.com\//.test(String(url || ''));
