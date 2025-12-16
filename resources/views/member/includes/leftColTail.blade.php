   <section
            class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden relative">
            <div class="h-24 bg-gradient-to-r from-blue-900 to-slate-800 relative">
                <div
                    class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
                </div>
            </div>
            <div class="px-5 pb-5 text-center relative">
                <div class="relative -mt-10 mb-3 inline-block">
                    <div
                        class="w-20 h-20 rounded-full border-4 border-surface-light dark:border-surface-dark overflow-hidden bg-gray-200">
                        <img alt="{{ $data['firstName'] }}
                        {{ $data['lastName'] }} " class="w-full h-full object-cover"
                            src="{{ $data['profilePicture'] }}" />
                    </div>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">{{ $data['firstName'] }}
                        {{ $data['lastName'] }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $data['country'] }}</p>
                <div
                    class="bg-gray-50 dark:bg-background-dark/50 rounded-lg p-3 mb-4 border border-border-light dark:border-border-dark">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Family Code</p>
                    <p class="text-primary font-mono font-bold text-lg">{{ $data['famCode'] }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        class="bg-primary hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition shadow-lg shadow-blue-500/20" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        Edit Profile
                    </button>
                    <button
                        class="bg-secondary hover:bg-red-700 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition shadow-lg shadow-red-500/20"  onclick="window.location.href='{{ route('images') }}'">
                        Pictures
                    </button>
                </div>
                <button
                    class="mt-3 w-full border border-primary text-primary dark:text-blue-400 dark:border-blue-500/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 font-medium py-2.5 px-4 rounded-lg text-sm flex items-center justify-center gap-2 transition" data-bs-toggle="modal"
                        data-bs-target="#createEventModal">
                    <span class="material-icons-round text-lg">add_circle_outline</span> Create Event
                </button>
            </div>
        </section>