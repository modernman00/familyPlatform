export const handleReaction = async (postNo, reactionType) => {
    try {
        const formData = new FormData()
        formData.append('post_no', postNo)
        formData.append('reaction_type', reactionType)
        
        const response = await fetch('/api/engagement/react', {
            method: 'POST',
            body: formData
        })
        const data = await response.json()
        return data
    } catch (error) {
        console.error('Reaction error:', error)
        return null
    }
}

export const handlePollVote = async (optionId) => {
    try {
        const formData = new FormData()
        formData.append('option_id', optionId)
        
        const response = await fetch('/api/engagement/vote', {
            method: 'POST',
            body: formData
        })
        const data = await response.json()
        return data
    } catch (error) {
        console.error('Vote error:', error)
        return null
    }
}

export const loadMemories = async () => {
    try {
        const response = await fetch('/api/engagement/memories')
        const data = await response.json()
        return data?.data || []
    } catch (error) {
        console.error('Memory load error:', error)
        return []
    }
}

import { html } from './html'

export const initEngagementListeners = () => {
    document.addEventListener('click', async (e) => {
        // Handle Poll Voting
        if (e.target.closest('.poll-option-btn')) {
            const btn = e.target.closest('.poll-option-btn')
            const optionId = btn.getAttribute('data-option-id')
            if (optionId) {
                btn.disabled = true;
                const res = await handlePollVote(optionId)
                if (res?.status === 'success') {
                    // Trigger a reload of the post or re-fetch to show new bars
                    window.dispatchEvent(new Event('refresh-feed'))
                    // For immediate visual feedback, just reload page for now or wait for pusher
                    window.location.reload(); 
                }
                btn.disabled = false;
            }
        }
        
        // Handle Reactions
        if (e.target.closest('.reaction-btn')) {
            const btn = e.target.closest('.reaction-btn')
            const postNo = btn.getAttribute('data-post-no')
            const reaction = btn.getAttribute('data-reaction')
            
            if (postNo && reaction) {
                const res = await handleReaction(postNo, reaction)
                if (res?.status === 'success') {
                    // Trigger a reload of the post or re-fetch to show new bars
                    window.location.reload();
                }
            }
        }
    })
}

export const initMemories = async () => {
    const container = document.getElementById('memories-container');
    const content = document.getElementById('memories-content');
    
    if (!container || !content) return;
    
    try {
        const memories = await loadMemories();
        if (memories && memories.length > 0) {
            container.style.display = 'block';
            let htmlStr = '';
            memories.forEach(memory => {
                // Pass an empty array for comments to the html function since memories don't eager load comments initially
                htmlStr += html(memory, []);
            });
            content.innerHTML = htmlStr;
        }
    } catch (e) {
        console.error('Failed to load memories:', e);
    }
}
