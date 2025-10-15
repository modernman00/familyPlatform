        <!-- Middle Column - Feed -->
        <div class="feed-column">
            <!-- Post Composer -->
            <div class="card post-composer mb-4" data-bs-toggle="modal" data-bs-target="#postModal">
                <div class="card-body d-flex align-items-center">
                    @isset($data['img'])
                        <img src="/public/img/profile/{{ $data['img'] }}" alt="profile"
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

