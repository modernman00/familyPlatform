<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
      <title>@yield('title')</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />

     {{-- set base url  --}}
    @base

   <!-- OpenGraph meta tags for better sharing on social media -->
    <!-- OpenGraph meta tags for better sharing on social media -->
    <meta property="og:title" content="OUR FAMILY NETWORK">
    <meta property="og:description"
        content="The Ultimate Social Platform for Your Family - Social media sites for Families to connect, strengthen Bonds, share Memories, and know the family Tree.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ getenv('IMG_CONTRACT') }}">
    <!-- Replace with the URL to your website's logo or featured image -->
    <meta property="og:url" content="{{ getenv('APP_URL') }}"> <!-- Replace with your website URL -->


    <!-- Twitter Card meta tags for better sharing on Twitter -->
    <meta name="twitter:title" content="OUR FAMILY NETWORK">
    <meta name="twitter:description"
        content="The Ultimate Social Platform for Your Family - Social media sites for Families to connect, strengthen Bonds, share Memories, and know the family Tree.">
    <meta name="twitter:image" content="{{ getenv('IMG_CONTRACT') }}">
    <meta name="twitter:card" content="summary_large_image">

   <script src="https://www.google.com/recaptcha/api.js" async defer></script>


    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>

    <link rel="stylesheet" href="/public/noscript.css" />
    </noscript>

    <link rel="icon" type="image/png" sizes="32x32" href={{ $_ENV['APP_LOGO'] }}>

    <link rel="manifest" href="/PWA_Manifest.json" type="application/manifest+json">



    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#1d4ed8", // A rich blue inspired by the header/buttons
                        secondary: "#dc2626", // Red for key actions
                        "background-light": "#f3f4f6",
                        "background-dark": "#111111", // Deep dark background
                        "surface-light": "#ffffff",
                        "surface-dark": "#1e1e1e", // Slightly lighter dark for cards
                        "border-light": "#e5e7eb",
                        "border-dark": "#333333",
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"],
                        body: ["Inter", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.75rem",
                        'xl': "1rem",
                        '2xl': "1.5rem",
                    },
                },
            },
        };
    </script>
    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    <style>
        body {
            min-height: max(884px, 100dvh);
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark font-body text-gray-900 dark:text-gray-100 min-h-screen pb-24 antialiased selection:bg-primary selection:text-white">
    <header
        class="sticky top-0 z-50 bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md border-b border-border-light dark:border-border-dark px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-2">
            <div class="bg-secondary p-1.5 rounded-lg">
                <span class="material-icons-round text-white text-lg">diversity_1</span>
            </div>
            <h1 class="font-bold text-lg tracking-tight text-blue-600 dark:text-blue-500">FamilyPlatform</h1>
        </div>
        <div class="flex items-center gap-4">
            <button class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <span class="material-icons-round text-gray-600 dark:text-gray-300">notifications</span>
                <span
                    class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-surface-light dark:border-surface-dark"></span>
            </button>
            <div
                class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-primary overflow-hidden border border-gray-200 dark:border-gray-700">
                <img alt="User Profile" class="w-full h-full object-cover"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1W3gUAXkAf9NRdaSPDWcutk0UNl6IhJNuvcD9PWIvemocH2FrRmSEOACYlbCLTeqi7sL7cq7WLiYZAzytxf-XKSUMEYx9ua2va05R1vTXUU46lQ_hwjEhJ8SOgj11YFKtO1oqlDiFa_j_enL1CYwUtOQf3WgKXFXp2vhtt1OnsSfXkySTHUH_bvAtWDSAfFuG6HerXPsCnUYX_tZnmhPFkh6LACycl5R5uYnn9T-L1ww89yiBoC58TE313fPE8FJhn8tdPp0Bvp3n" />
            </div>
        </div>
    </header>
    <main class="max-w-md mx-auto px-4 pt-6 space-y-6">
     
   
        <section>
            <div class="flex justify-between items-end mb-3 px-1">
                <h3 class="font-bold text-gray-800 dark:text-gray-200">Upcoming Events</h3>
                <a class="text-xs font-semibold text-primary hover:text-blue-400" href="#">See All</a>
            </div>
            <div class="flex gap-4 overflow-x-auto hide-scrollbar pb-2">
                <div
                    class="flex-shrink-0 w-64 bg-surface-light dark:bg-surface-dark rounded-xl border border-border-light dark:border-border-dark p-4 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-green-500"></div>
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wide">
                                Birthday</p>
                            <h4 class="font-semibold text-gray-900 dark:text-white mt-1">Grandma's 80th</h4>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-center min-w-[3rem]">
                            <span class="block text-xs font-bold text-gray-500 uppercase">OCT</span>
                            <span class="block text-lg font-bold text-gray-900 dark:text-white leading-none">12</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">Celebration at the main hall.
                        Don't forget gifts!</p>
                    <div class="flex -space-x-2 overflow-hidden">
                        <img alt="" class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-surface-dark"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuCkQRMTFQK40-OmAgvTFa6B2h07pbpIM8wZFYrZiziypR8gwpdMX2dTx5ki5QXb2BYwIAsJ1KxaefIkoD26ZFaqN7TD9Y6PCs2czg-xsyErQLI7_P4Nh55re_LtZs52_mJjbDXCI0D9lMq5BXkHqM3UO1ItwTy_W2yDlAAu-VE_bh5t0BH1EAYbXsGQnW5AtIvuE-V65r35pcpi7FwI5EmmnrwcBHzJUDzeOTZ62zEbcu40rt4b-cQVcT79VEe78GVugVW-sjc2bARs" />
                        <img alt="" class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-surface-dark"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuADL5xnLnTabFY2_8Ah9pdSF8L6FXiTgYlaBDt5PRfySUeTEgfrCyjlmse46s6wjBMB6A44aRRM2PLzPM-unjOOdEbd-Nn-m0doxMeZ9AmSSLIkd1fXNajBVYmH1coWVRRN95Eco01KeaNV8aNPaamdnYxHg5pbwrDgQiWZbYrCStTIMPyqkmzTiZqrIN8YxLH-ULkSKPKFaGHotWxQf_dv_8H6NAu24hk7pWLxlffDAhdrbp56LWPqdtyUJrXgYdp011zQWKUZ3vOs" />
                        <img alt="" class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-surface-dark"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDCXsWrBs7CgAaC6fHI69781ks8Ne7AIPLyDJgZXYJC75BeZDzkYtxCGuH52N45t3NEeQBQTOjChfm7zUNjGLX6DXd-IWsh2jByLSdyqfgsPHyrG8y3DXT8xxuFg30ETgexjNEol7jYtOjJZXUMLTTdx1pXE7dIsRspJSGOE1Zv0hubg54W3Usnlc37ROidGxrNNUEL0DZ4fsadZ-lWUBud3RYIhUmEhaCx5ZjrN2b_ImFin_1CrvQQwLNLpl54hRLZkv9GTTNA5YqR" />
                        <div
                            class="h-6 w-6 rounded-full bg-gray-100 dark:bg-gray-700 ring-2 ring-white dark:ring-surface-dark flex items-center justify-center text-[10px] text-gray-500">
                            +4</div>
                    </div>
                </div>
                <div
                    class="flex-shrink-0 w-64 bg-surface-light dark:bg-surface-dark rounded-xl border border-border-light dark:border-border-dark p-4 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-purple-500"></div>
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wide">
                                Reunion</p>
                            <h4 class="font-semibold text-gray-900 dark:text-white mt-1">Annual Picnic</h4>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-center min-w-[3rem]">
                            <span class="block text-xs font-bold text-gray-500 uppercase">NOV</span>
                            <span class="block text-lg font-bold text-gray-900 dark:text-white leading-none">05</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">Hyde Park corner near the
                        fountain.</p>
                    <div class="flex -space-x-2 overflow-hidden">
                        <img alt="" class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-surface-dark"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDCb34ijqqQIj2pf-5qLySYvZ1HInOH2RwI_4HETXwy7V8NbrdyzoiLWcs91xeP8gu-y_f7rsdGC74WsU9o-gWgO-mjLTevlQI-wxF2TVSe3NKnGZ5C327asCneK_dc0WqgaKAAmHT0AEE3cgkqiIPQ_FT9ZDioUI1bcPYzU4DCVkAX6NqqolJ-X7FVMVnUC5kl0ybjipQcqqCOHz-dJVcMxXxJtV0ovKXyFsir64uZcU9p8JvC2v3nadhA9VXqBSBJ9o7-eLPIa814" />
                        <img alt="" class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-surface-dark"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAUpt-u14xLLthLUUNu6AfdSmD5B5ISL5OSFe3-cMCmlvqRlMQvA2VFkcmSEmnC8KEhiSbiUFL0mrocb_Krdw7jXEioafcqkMxejkyrp8jn4j-uF_26W5lQgKeHdyNDEpprfPQlC3zo6I9I1BYxBNeivZ_g-yG3crI68yS2F06ydRetwsrwsVBECYUrLltqYNsu3bsKTVxP25UymC--DNDw7CuiiiXDANw9rkzFQG00A1pT8-b0lOX-ua-dKH1ZvFQlBroOo0vkuZy9" />
                    </div>
                </div>
            </div>
        </section>
        <section class="space-y-6">
       
            <article
                class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-700 overflow-hidden">
                            <img alt="Femi" class="w-full h-full object-cover"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuC0j6xnj8gzYrhvornNpsXaw3X53D2OgBTX9lkExf5g9jmFzHUWmnbGNziEH1koChef3PTwZHQsTtSD-xpUvEDbtpFhP3L0pQWZ-EVX7UZdMrv564c_yzPxaOIAkAaIFp6ReA06BTZCAzVbQc76OD56raIEMWgqL4qWLEkUBMuAuxNkPW9WBg2UCNkRgyqrJbJNALRE1DYVaGvUGwqpojGPi-kXSiSSdRI4JZaAsSr-u-no11a2Tr-CLZKm9TmaCD-DjvNbRYW5kRLX" />
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Femi Olaogun</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">5 hours ago</p>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <span class="material-icons-round">more_horiz</span>
                    </button>
                </div>
                <div class="px-4 pb-2">
                    <p class="text-gray-800 dark:text-gray-200 text-base leading-relaxed">
                        Are we looking at the time of the event? I think we should start a bit earlier to catch the
                        sunlight for photos 📸
                    </p>
                </div>
                <div
                    class="px-4 py-3 mt-2 border-t border-border-light dark:border-border-dark flex items-center justify-between text-gray-500 dark:text-gray-400 text-sm">
                    <button class="flex items-center gap-2 text-primary font-medium transition group">
                        <span class="material-icons-round text-xl">thumb_up</span>
                        <span>Like <span class="ml-1 text-gray-900 dark:text-white">6</span></span>
                    </button>
                    <button class="flex items-center gap-2 hover:text-blue-400 transition group">
                        <span
                            class="material-icons-round text-xl group-hover:scale-110 transition-transform">chat_bubble_outline</span>
                        <span>Comment <span class="ml-1 font-semibold text-gray-700 dark:text-gray-300">1</span></span>
                    </button>
                    <button class="flex items-center gap-2 hover:text-green-400 transition group">
                        <span
                            class="material-icons-round text-xl group-hover:scale-110 transition-transform">share</span>
                    </button>
                </div>
                <div class="bg-gray-50 dark:bg-black/20 px-4 py-3 border-t border-border-light dark:border-border-dark">
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-gray-600 overflow-hidden flex-shrink-0 mt-1">
                            <img alt="Femi Reply" class="w-full h-full object-cover"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuBnuBAfk4jejACB4_CngPuDuKYEaLSSs2xIfZI7H2_XcBXjQZzCW0KuYUGd7WPyc6vuEpieGcchUyH93_0RhKK52h7ClUuAsZ5MTA7urh7HSNb4rXWyV5FVQXDfLya3RwferM5XaMZFbJb1rhknmvQh2s0xG0eQ2jsGmPHuawn5ZYfF-EnHwf4nkGNAm8EmLnxOy-q7vW_ACAz1il7Td_Nvby59Z4CS3A92pgDRztr5dZ7TKtuTXg0kPDMUJJFZtB_H8xI0Oeg8QmuF" />
                        </div>
                        <div class="flex-grow">
                            <div
                                class="bg-white dark:bg-gray-800 p-3 rounded-tr-xl rounded-br-xl rounded-bl-xl shadow-sm border border-border-light dark:border-border-dark">
                                <p class="text-xs font-bold text-gray-900 dark:text-white mb-0.5">Femi Olaogun</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">are you sure? We agreed on 2 PM last
                                    week.</p>
                            </div>
                            <div class="flex items-center gap-4 mt-1 ml-1">
                                <button
                                    class="text-xs font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Like</button>
                                <button
                                    class="text-xs font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Reply</button>
                                <span class="text-xs text-gray-400">1h</span>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <div
                class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800 dark:text-gray-200">Friend Requests</h3>
                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">1</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gray-300 overflow-hidden flex-shrink-0">
                        <img alt="Request" class="w-full h-full object-cover"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuCleLUL5a0Ol28tC6CBXkDTJ8_UFH0uHN7Iu0S7ObjyDd9LNCYjJSXxBBNxJRpzNZYr0U0gLTu3w76Axk_26mN98LZJhUch2e7RjhTjhvGljlenqNyNpm2qehYDKmGsTMOCppe1NYnSJgBLXRw6kZ2fHQRGURAdbktVmdau6DyVZ_2hvejLStGbWVvXS76GJOrSW5SsZZNk7CracdaBJO2caPdm5hmoN0A87hKzbYHyaWxx2FAhVzWArS1YdpyqWy5xcN8xPEBIFJ42" />
                    </div>
                    <div class="flex-grow">
                        <h4 class="font-bold text-sm text-gray-900 dark:text-white">Sarah Jenkins</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Mutual friend: Swindon</p>
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <button
                        class="flex-1 bg-primary text-white text-sm font-medium py-2 rounded-lg hover:bg-blue-700 transition">Confirm</button>
                    <button
                        class="flex-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">Delete</button>
                </div>
            </div>
        </section>
    </main>
      @include('layouts.navTail')
    <div class="h-20"></div>

        <div class="footer">
        <div class="content has-text-centered">

            <p>Website developed and maintained by Olawale Olaogun </p>
            <button id="subscribeButton">Subscribe to Notifications</button>


            <a href="@yield('data-page-id')" title="To Top">
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-bar-up" fill="currentColor"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M11.354 5.854a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L8 3.207l2.646 2.647a.5.5 0 0 0 .708 0z" />
                    <path fill-rule="evenodd"
                        d="M8 10a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-1 0v6.5a.5.5 0 0 0 .5.5zm-4.8 1.6c0-.22.18-.4.4-.4h8.8a.4.4 0 0 1 0 .8H3.6a.4.4 0 0 1-.4-.4z" />
                </svg> back to top
            </a>
            <br>
            <a href="/privacy">Privacy</a>| <a href="/terms">Terms</a>| <a href="/contact">Contact</a>| <a
                href="/aboutus">About us</a>
        </div>
    </div>
    <script>
        // Simple toggle for dark mode demonstration (optional, usually handled by system pref)
        // Checks system preference on load
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
    </script>

      <script type="text/javascript" nonce="{{ $nonce }}" src="public/js/manifest.js" defer></script>
    <script type="text/javascript" nonce="{{ $nonce }}" src="public/js/vendor.js" defer></script>
    <script type="text/javascript" nonce="{{ $nonce }}" src="public/js/index.js" defer></script>

</body>

</html>