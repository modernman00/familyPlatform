    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="text-center mb-4">
                            <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Profile" class="rounded-circle mb-3" width="100" height="100">
                            <button type="button" class="btn btn-outline-primary btn-sm d-block mx-auto">Change Avatar</button>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" value="{{$data['firstName']}}">
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" value="{{$data['lastName']}}">
                            </div>
                        </div>
                        
                        {{-- <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" rows="3">Software Developer | Photographer | Travel Enthusiast</textarea>
                        </div> --}}
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" value="{{$data['location']}}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="work" class="form-label">Work</label>
                            <input type="text" class="form-control" id="work" value="Senior Developer at TechCorp">
                        </div>
                        
                        <div class="mb-3">
                            <label for="education" class="form-label">Education</label>
                            <input type="text" class="form-control" id="education" value="Stanford University">
                        </div>
                        
                        <div class="mb-3">
                            <label for="relationship" class="form-label">Relationship Status</label>
                            <select class="form-select" id="relationship">
                                <option selected>Single</option>
                                <option>In a relationship</option>
                                <option>Married</option>
                                <option>Divorced</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>