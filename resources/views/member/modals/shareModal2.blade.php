<div class="modal fade" id="shareModal2" tabindex="-1" aria-labelledby="shareModal2Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModal2Label">Share Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Share your profile link with others:</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="profileLink" value="{{ getenv('APP_URL') }}/profile/{{ $data['username'] }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="copyButton">Copy</button>
                </div>
            </div>
        </div>
    </div>

    <div id="copyAlert" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 1050;">
        Profile link copied to clipboard!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>