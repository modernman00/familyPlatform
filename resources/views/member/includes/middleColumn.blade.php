        <!-- Middle Column - Feed -->
        <div class="feed-column">
            <!-- Post Composer -->
            <div class="card post-composer mb-4" data-bs-toggle="modal" data-bs-target="#postModal">
                <div class="card-body d-flex align-items-center">
                    @isset($data['img'])
                        <img src="/resources/images/profile/{{ $data['img'] }}" alt="profile"
                            class="rounded-circle me-3 " width="40" height="40">
                    @else
                        <img src="/public/avatar/avatarF.png" alt="profile" width="40" height="40"
                            class="rounded-circle me-3">
                    @endisset

                    <div class="flex-grow-1">
                        <span class="text-muted">What's on your mind, {{ $data['firstName'] }} ?</span>
                    </div>
                </div>
            </div>

            <!-- Posts -->
            <div class="postIt" id="postIt">
               
            </div>

        <!-- The Modal Structure (Hidden by default) for the post image modal -->
          @includeIf('member.modals.postImg')


            <!-- Pagination -->
            <nav aria-label="Feed pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>


<script>
   
  const reactionBars = document.querySelectorAll('.reaction-bar');
  
  // Show/hide reaction bar on hover
  document.querySelectorAll('[id^="like-button-"]').forEach(button => {
    const commentNo = button.getAttribute('data-comment-no');
    const bar = document.getElementById(`reaction-bar-${commentNo}`);
    
    button.addEventListener('mouseenter', () => {
      bar.classList.add('show');
    });
    
    button.addEventListener('mouseleave', () => {
      setTimeout(() => bar.classList.remove('show'), 600);
    });

    bar.addEventListener('mouseenter', () => {
      bar.classList.add('show');
    });
    bar.addEventListener('mouseleave', () => {
      bar.classList.remove('show');
    });
  });

  // Handle reaction click
  document.querySelectorAll('.reaction-option').forEach(option => {
    option.addEventListener('click', async (e) => {
      const reaction = option.getAttribute('data-reaction');
      const commentDiv = option.closest('[data-commentDiv-no]');
      const commentNo = commentDiv.getAttribute('data-commentDiv-no');
      const button = document.getElementById(`like-button-${commentNo}`);
      const countEl = document.getElementById(`like-count-${commentNo}`);
      const preview = document.getElementById(`reaction-preview-${commentNo}`);

      // Optimistic UI update
      preview.innerHTML = `<span class="reaction-emoji">${option.querySelector('.reaction-emoji').textContent}</span>`;
      button.querySelector('span').textContent = option.getAttribute('data-label');
      
      let count = parseInt(countEl.textContent || '0', 10);
      countEl.textContent = count + 1;

      try {
        const res = await fetch('/api/reactions/add', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({
            comment_no: commentNo,
            reaction,
          }),
        });

        const data = await res.json();
        if (data.status !== 'success') throw new Error(data.message);
      } catch (error) {
        console.error('Failed to record reaction:', error);
        // revert optimistic UI change
        countEl.textContent = count;
        preview.innerHTML = '';
      }
    });
  });


</script>