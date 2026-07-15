export const renderPoll = (poll) => {
    if (!poll || !poll.options || !Array.isArray(poll.options)) return '';
    
    // Safely fallback the question text
    const questionText = poll.question || '';
    
    let html = `<div class="poll-container mt-3 p-3 bg-light rounded border border-light-subtle">
        <h6 class="fw-bold mb-3"><i class="fa fa-bar-chart me-2 text-primary"></i>${questionText}</h6>
        <div class="poll-options">`;
        
    poll.options.forEach(opt => {
        // Multi-select is supported on backend. user_voted_option_id is an array now
        const isVoted = poll.user_voted_option_id && Array.isArray(poll.user_voted_option_id) && poll.user_voted_option_id.includes(opt.option_id);
        const percentage = opt?.percentage || 0;
        const barWidth = percentage + '%';
        const bgClass = isVoted ? 'bg-primary' : 'bg-secondary';
        const optionText = opt?.option_text || '';
        
        html += `
            <div class="poll-option mb-2 position-relative">
                <button class="poll-option-btn w-100 text-start btn btn-outline-${isVoted ? 'primary' : 'secondary'} position-relative overflow-hidden" 
                        data-option-id="${opt?.option_id || ''}" style="z-index: 1;">
                    <span class="position-relative" style="z-index: 2; mix-blend-mode: difference; color: white;">
                        ${optionText}
                    </span>
                    <span class="float-end position-relative fw-bold" style="z-index: 2; mix-blend-mode: difference; color: white;">
                        ${percentage}%
                    </span>
                    <div class="position-absolute top-0 start-0 h-100 ${bgClass}" 
                         style="width: ${barWidth}; opacity: 0.5; transition: width 0.5s ease; z-index: 0;"></div>
                </button>
            </div>
        `;
    });
    
    html += `</div>
        <small class="text-muted mt-2 d-block">${poll?.total_votes || 0} votes</small>
    </div>`;
    
    return html;
}

export const renderReactions = (el) => {
    // Emojis: Like 👍, Love ❤️, Haha 😂, Shock 😲, Sad 😢
    const emojis = {
        'like': '👍',
        'love': '❤️',
        'haha': '😂',
        'shock': '😲',
        'sad': '😢'
    };
    
    const reactions = el?.reactions || [];
    const userReaction = el?.user_reaction || null;
    const postNo = el?.post_no || '';
    
    if (!postNo) return '';
    
    let summaryHtml = '';
    let totalReactions = 0;
    let counts = { like: 0, love: 0, haha: 0, shock: 0, sad: 0 };
    
    reactions.forEach(r => {
        if (r && r.reaction_type && r.count) {
            counts[r.reaction_type] = parseInt(r.count, 10) || 0;
            totalReactions += counts[r.reaction_type];
        }
    });
    
    if (totalReactions > 0) {
        summaryHtml = `<div class="reaction-summary mt-2 mb-2 px-2 py-1 bg-light rounded d-inline-block shadow-sm">
            ${counts.like > 0 ? `👍 ${counts.like}` : ''}
            ${counts.love > 0 ? `❤️ ${counts.love}` : ''}
            ${counts.haha > 0 ? `😂 ${counts.haha}` : ''}
            ${counts.shock > 0 ? `😲 ${counts.shock}` : ''}
            ${counts.sad > 0 ? `😢 ${counts.sad}` : ''}
        </div>`;
    }
    
    let barHtml = `<div class="reaction-bar d-flex gap-2 mt-2 align-items-center">`;
    for (const [type, emoji] of Object.entries(emojis)) {
        const isActive = (userReaction === type);
        barHtml += `
            <button class="btn btn-sm reaction-btn ${isActive ? 'btn-primary' : 'btn-light text-dark'} rounded-pill shadow-sm" 
                    data-post-no="${postNo}" data-reaction="${type}"
                    style="transition: transform 0.2s; ${isActive ? 'transform: scale(1.1);' : ''}">
                ${emoji} <span class="d-none d-md-inline ms-1 text-capitalize" style="font-size: 0.8rem;">${type}</span>
            </button>
        `;
    }
    barHtml += `</div>`;
    
    return `<div class="engagement-section mt-3">
        ${summaryHtml}
        ${barHtml}
    </div>`;
}
