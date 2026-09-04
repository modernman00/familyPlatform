{{-- Scoped Lineage Wizard Component --}}
<div class="modal fade" id="addRelativeModal" tabindex="-1" aria-labelledby="addRelativeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="backdrop-filter: blur(8px);">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; background: rgba(255, 255, 255, 0.95);">
            <div class="modal-header border-0 bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addRelativeModalLabel"><i class="bi bi-diagram-3-fill"></i> Add Relative</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="addRelativeError" class="alert alert-danger d-none" style="border-radius: 8px;"></div>
                
                <!-- Step 1: Select Type -->
                <div id="step1">
                    <h6 class="fw-bold mb-3 text-secondary">Who are you adding for <span id="addRelativeBaseName" class="text-primary"></span>?</h6>
                    
                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-outline-primary text-start p-3" onclick="selectRelativeType('partner')" style="border-radius: 12px;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-heart-fill fs-3 me-3"></i>
                                <div>
                                    <div class="fw-bold fs-5">Partner / Spouse</div>
                                    <div class="text-muted small">Current spouse, ex-partner, or co-parent.</div>
                                </div>
                            </div>
                        </button>
                        
                        <button type="button" class="btn btn-outline-success text-start p-3" onclick="selectRelativeType('child')" style="border-radius: 12px;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-emoji-smile-fill fs-3 me-3"></i>
                                <div>
                                    <div class="fw-bold fs-5">Child</div>
                                    <div class="text-muted small">Son or daughter (requires selecting a union).</div>
                                </div>
                            </div>
                        </button>
                        
                        <button type="button" class="btn btn-outline-info text-start p-3" onclick="selectRelativeType('sibling')" style="border-radius: 12px;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people-fill fs-3 me-3"></i>
                                <div>
                                    <div class="fw-bold fs-5">Sibling</div>
                                    <div class="text-muted small">Brother or sister (requires selecting their parents).</div>
                                </div>
                            </div>
                        </button>
                        
                        <button type="button" class="btn btn-outline-secondary text-start p-3" onclick="selectRelativeType('parents')" style="border-radius: 12px;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-hearts fs-3 me-3"></i>
                                <div>
                                    <div class="fw-bold fs-5">Parents</div>
                                    <div class="text-muted small">Father and/or Mother.</div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Partner Form -->
                <div id="step2-partner" class="d-none">
                    <form id="addPartnerForm">
                        <input type="hidden" name="base_node_id" id="partnerBaseNodeId">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Relationship Status</label>
                            <select name="is_current" id="partnerStatus" class="form-select" onchange="toggleDivorceYear()" required>
                                <option value="yes">Current Partner / Married</option>
                                <option value="no">Divorced / Past Union</option>
                            </select>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" required placeholder="Jane">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last Name</label>
                                <input type="text" name="last_name" class="form-control" placeholder="Doe">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                            </select>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email (Optional)</label>
                                <input type="email" name="email" class="form-control" placeholder="Email for auto-link">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mobile (Optional)</label>
                                <input type="text" name="mobile" class="form-control" placeholder="Phone number">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marriage/Start Year</label>
                                <input type="number" name="marriage_year" class="form-control" placeholder="e.g. 2010" min="1900" max="{{ date('Y') }}">
                            </div>
                            <div class="col-md-6" id="divorceYearWrapper" style="display: none;">
                                <label class="form-label fw-bold">Divorce/End Year</label>
                                <input type="number" name="divorce_year" class="form-control" placeholder="e.g. 2020" min="1900" max="{{ date('Y') }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-light fw-bold" onclick="backToStep1()">Back</button>
                            <button type="submit" class="btn btn-primary fw-bold" id="submitPartnerBtn">Save Partner</button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Child Form -->
                <div id="step2-child" class="d-none">
                    <form id="addChildForm">
                        <input type="hidden" name="base_node_id" id="childBaseNodeId">
                        
                        <div class="alert alert-warning small py-2">
                            To add a child, you must select which partnership/union they belong to.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Union <span class="text-danger">*</span></label>
                            <select name="union_id" id="childUnionSelect" class="form-select" required>
                                <option value="">Loading unions...</option>
                            </select>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" required placeholder="John">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last Name</label>
                                <input type="text" name="last_name" class="form-control" placeholder="Doe">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email (Optional)</label>
                                <input type="email" name="email" class="form-control" placeholder="Email for auto-link">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mobile (Optional)</label>
                                <input type="text" name="mobile" class="form-control" placeholder="Phone number">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-light fw-bold" onclick="backToStep1()">Back</button>
                            <button type="submit" class="btn btn-success fw-bold" id="submitChildBtn">Save Child</button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Parents Form -->
                <div id="step2-parents" class="d-none">
                    <form id="addParentsForm">
                        <input type="hidden" name="base_node_id" id="parentsBaseNodeId">
                        
                        <div class="alert alert-info small py-2">
                            Provide details for one or both parents. Leave blank if unknown.
                        </div>
                        
                        <!-- Father Section -->
                        <h6 class="fw-bold text-primary mt-3 border-bottom pb-2">Father's Details</h6>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">First Name</label>
                                <input type="text" name="father_first_name" class="form-control form-control-sm" placeholder="John">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Last Name</label>
                                <input type="text" name="father_last_name" class="form-control form-control-sm" placeholder="Doe">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Email (Optional)</label>
                                <input type="email" name="father_email" class="form-control form-control-sm" placeholder="Email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Mobile (Optional)</label>
                                <input type="text" name="father_mobile" class="form-control form-control-sm" placeholder="Mobile">
                            </div>
                        </div>

                        <!-- Mother Section -->
                        <h6 class="fw-bold text-primary mt-4 border-bottom pb-2">Mother's Details</h6>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">First Name</label>
                                <input type="text" name="mother_first_name" class="form-control form-control-sm" placeholder="Jane">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Last Name</label>
                                <input type="text" name="mother_last_name" class="form-control form-control-sm" placeholder="Doe">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Email (Optional)</label>
                                <input type="email" name="mother_email" class="form-control form-control-sm" placeholder="Email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Mobile (Optional)</label>
                                <input type="text" name="mother_mobile" class="form-control form-control-sm" placeholder="Mobile">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-light fw-bold" onclick="backToStep1()">Back</button>
                            <button type="submit" class="btn btn-secondary fw-bold" id="submitParentsBtn">Save Parents</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
