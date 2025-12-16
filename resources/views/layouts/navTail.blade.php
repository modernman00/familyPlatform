  <nav
        class="fixed bottom-0 left-0 right-0 bg-surface-light/95 dark:bg-surface-dark/95 backdrop-blur-md border-t border-border-light dark:border-border-dark pb-safe">
        <div class="flex justify-around items-center h-16 max-w-md mx-auto">
            <a class="flex flex-col items-center justify-center w-full h-full text-primary" href="#">
                <span class="material-icons-round text-2xl">home</span>
                <span class="text-[10px] font-medium mt-0.5">Home</span>
            </a>
            <a class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                href="/organogram/{{$_SESSION['id'] }}">
                <span class="material-icons-round text-2xl">family_restroom</span>
                <span class="text-[10px] font-medium mt-0.5">Tree</span>
            </a>
            <a class="flex flex-col items-center justify-center w-full h-full relative -top-5" href="#">
                <div
                    class="w-14 h-14 bg-primary rounded-full flex items-center justify-center text-white shadow-lg shadow-blue-600/30 hover:scale-105 transition-transform">
                    <span class="material-icons-round text-3xl">add</span>
                </div>
            </a>
            <a class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                href="#eventHeader">
                <span class="material-icons-round text-2xl">event</span>
                <span class="text-[10px] font-medium mt-0.5">Events</span>
            </a>
            <a class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                href="/profilePage">
                <span class="material-icons-round text-2xl">person</span>
                <span class="text-[10px] font-medium mt-0.5">My Page</span>
            </a>
        </div>
    </nav>