    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareModalLabel">Share Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="share-modal-content">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Profile" class="rounded-circle me-3" width="40" height="40">
                            <div>
                                <h6 class="mb-0">John Doe</h6>
                            </div>
                        </div>
                        <textarea class="form-control mb-3" placeholder="Write something about this post..." rows="3"></textarea>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Who can see your share?</span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="shareAudienceDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-people-fill"></i> Friends
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="shareAudienceDropdown">
                                    <li><a class="dropdown-item" href="#">Public</a></li>
                                    <li><a class="dropdown-item" href="#">Friends</a></li>
                                    <li><a class="dropdown-item" href="#">Only me</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100">Share Now</button>
                </div>
            </div>
        </div>
    </div>