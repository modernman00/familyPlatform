     <div
            class="bg-surface-light dark:bg-surface-dark rounded-xl p-4 shadow-sm border border-border-light dark:border-border-dark flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
            @isset($data['profilePics'])
            <img src="/resources/images/profile/{{ $data['profilePics'] }}" alt="profile" class="w-full h-full object-cover " width="40"
              height="40">
          @else
            <img src="/public/avatar/avatarF.png" alt="profile" width="40" height="40" class="w-full h-full object-cover">
          @endisset
            </div>
            <div
                class="flex-grow bg-gray-100 dark:bg-background-dark rounded-full px-4 py-2.5 text-gray-500 text-sm cursor-text hover:bg-gray-200 dark:hover:bg-gray-800 transition">
                What's on your mind {{ $data['firstName'] }}?
            </div>
        </div>

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