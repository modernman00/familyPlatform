        
        // Emoji Picker
        const emojiToggle = document.getElementById('emojiToggle');
        const emojiPicker = document.getElementById('emojiPicker');
        
        emojiToggle.addEventListener('click', (e) => {
            e.preventDefault();
            emojiPicker.style.display = emojiPicker.style.display === 'grid' ? 'none' : 'grid';
        });
        
        // Add emoji to textarea
        const emojiButtons = document.querySelectorAll('.emoji-btn');
        const postTextarea = document.querySelector('#postModal textarea');
        
        emojiButtons.forEach(button => {
            button.addEventListener('click', () => {
                postTextarea.value += button.textContent;
            });
        });
        
        // Close emoji picker when clicking outside
        document.addEventListener('click', (e) => {
            if (!emojiToggle.contains(e.target) && !emojiPicker.contains(e.target)) {
                emojiPicker.style.display = 'none';
            }
        });
        