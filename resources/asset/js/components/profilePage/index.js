"use strict";

localStorage.removeItem('redirect')

  // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;
        
        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
        }
        
        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
            } else {
                localStorage.setItem('darkMode', null);
                darkModeToggle.innerHTML = '<i class="bi bi-moon-fill"></i>';
            }
        });

import "./loadPost"
import "./modal"
import "./img"
import "./rsvpBtn"
import "./allEvents"
import "./registerPushNotification"
import "./periodicSync"
import "./createEvent"
import "./friendRequestCard"
import "../navbar"
import "./editProfile"
import "./emojiPicker"
// import "./newPage"





