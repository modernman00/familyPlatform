{{-- Alpine component driving the family-code check + inviter verification
     modal. Kept out of register.blade.php so the page template stays markup. --}}
<script>
(function() {
    function familyCodeApprovalComponent() {
        return {
            familyCode: '{{ old("famCode", "") }}',
            codeExists: false,
            codeVerified: false,
            checkingCode: false,
            verifying: false,
            temporaryCode: '',
            inviterFirstName: '{{ old("inviter_first_name", "") }}',
            inviterLastName: '{{ old("inviter_last_name", "") }}',
            inviterContact: '{{ old("inviter_email_or_mobile", "") }}',
            debounceTimer: null,
            lastCheckedCode: '',

            init() {
                const self = this;
                const form = document.querySelector('form.register');

                const attachListeners = (input) => {
                    if (!input || input.__familyCodeBound) return;
                    input.__familyCodeBound = true;

                    // Input (Debounced)
                    input.addEventListener('input', (e) => {
                        self.familyCode = (e.target.value || '').trim();
                        clearTimeout(self.debounceTimer);
                        if (self.familyCode.length >= 4) {
                            self.debounceTimer = setTimeout(() => {
                                self.checkFamilyCode();
                            }, 500);
                        }
                    });

                    // Blur
                    input.addEventListener('blur', (e) => {
                        self.familyCode = (e.target.value || '').trim();
                        if (self.familyCode.length >= 4) {
                            self.checkFamilyCode();
                        }
                    });

                    // Change
                    input.addEventListener('change', (e) => {
                        self.familyCode = (e.target.value || '').trim();
                        if (self.familyCode.length >= 4) {
                            self.checkFamilyCode();
                        }
                    });

                    // Paste
                    input.addEventListener('paste', () => {
                        setTimeout(() => {
                            self.familyCode = (input.value || '').trim();
                            if (self.familyCode.length >= 4) {
                                self.checkFamilyCode();
                            }
                        }, 50);
                    });

                    // Initial check if pre-filled
                    if (input.value && input.value.trim().length >= 4) {
                        self.familyCode = input.value.trim();
                        self.checkFamilyCode();
                    }
                };

                            this.familyCode = e.target.value;
                        });
                        
                        famCodeInput.addEventListener('blur', (e) => {
                            this.familyCode = e.target.value;
                            this.checkFamilyCode();
                        });
                        
                        // Initialize Alpine value if already present
                        if (famCodeInput.value) {
                            this.familyCode = famCodeInput.value;
                            this.checkFamilyCode();
                        }
                    }
                });

                // Find input immediately or upon DOM readiness
                const famInput = document.querySelector('input[name="famCode"]') || document.getElementById('famCode');
                if (famInput) {
                    attachListeners(famInput);
                } else {
                    // Fallback: attempt to bind after a short delay in case the input is rendered later.
                    setTimeout(() => {
                        const lateInput = document.querySelector('input[name="famCode"]') || document.getElementById('famCode');
                        if (lateInput) attachListeners(lateInput);
                    }, 200);
                }

                if (form) {
                    form.addEventListener('input', (e) => {
                        if (e.target && (e.target.name === 'famCode' || e.target.id === 'famCode')) {
                            self.familyCode = (e.target.value || '').trim();
                            clearTimeout(self.debounceTimer);
                            if (self.familyCode.length >= 4) {
                                self.debounceTimer = setTimeout(() => {
                                    self.checkFamilyCode();
                                }, 500);
                            }
                        }
                    });
                }
            },

            getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.content ||
                       document.querySelector('input[name="_token"]')?.value ||
                       document.querySelector('input[name="token"]')?.value || '';
            },

            async checkFamilyCode() {
                const famInput = document.querySelector('input[name="famCode"]') || document.getElementById('famCode');
                const rawCode = (famInput ? famInput.value : this.familyCode || '').trim();
                const cleanCode = rawCode.replace(/^#\s*/, '').trim();

                if (!cleanCode || cleanCode.length < 4) {
                    this.codeExists = false;
                    this.codeVerified = false;
                    return;
                }

                // Skip duplicate checks if already checked this exact code and verified
                if (this.lastCheckedCode === cleanCode && this.codeVerified) {
                    return;
                }

                this.lastCheckedCode = cleanCode;
                this.checkingCode = true;

                try {
                    const response = await fetch('/api/family-code/check', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-XSRF-TOKEN': this.getCsrfToken(),
                            'X-CSRF-TOKEN': this.getCsrfToken()
                        },
                        body: JSON.stringify({
                            family_code: cleanCode
                        })
                    });

                    const data = await response.json();
                    this.codeExists = (data && data.exists === true);

                    if (this.codeExists) {
                        this.temporaryCode = data.temporary_code || '';
                        // Focus on first inviter field when modal opens
                        setTimeout(() => {
                            const firstField = document.getElementById('inviter_first_name');
                            if (firstField) firstField.focus();
                        }, 200);
                    }
                } catch (error) {
                    console.error('Error checking family code:', error);
                    this.codeExists = false;
                } finally {
                    this.checkingCode = false;
                }
            },

            async verifyInviter() {
                if (!this.inviterFirstName.trim() || !this.inviterLastName.trim() || !this.inviterContact.trim()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please fill in all inviter details (First Name, Last Name, and Email or Mobile).',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                this.verifying = true;

                try {
                    const response = await fetch('/api/family-code/verify-inviter', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-XSRF-TOKEN': this.getCsrfToken(),
                            'X-CSRF-TOKEN': this.getCsrfToken()
                        },
                        body: JSON.stringify({
                            family_code: this.lastCheckedCode || this.familyCode,
                            inviter_first_name: this.inviterFirstName.trim(),
                            inviter_last_name: this.inviterLastName.trim(),
                            inviter_email_or_mobile: this.inviterContact.trim()
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.verified) {
                        this.codeVerified = true;

                        // Give user visual feedback then close modal
                        setTimeout(() => {
                            this.codeExists = false;
                        }, 1600);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Verification Failed',
                            text: data.message || 'Unable to find a matching family member with those details in this family network. Please check the information and try again.',
                            confirmButtonText: 'Try Again'
                        });
                    }
                } catch (error) {
                    console.error('Error verifying inviter:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Error',
                        text: 'A temporary network error occurred. Please try again.',
                        confirmButtonText: 'OK'
                    });
                } finally {
                    this.verifying = false;
                }
            }
        };
    }

    // Expose globally for direct Alpine instantiation
    window.familyCodeApprovalForm = familyCodeApprovalComponent;

    // Register with Alpine if Alpine is already loaded
    if (window.Alpine) {
        window.Alpine.data('familyCodeApprovalForm', familyCodeApprovalComponent);
    }

    // Also register on alpine:init
    document.addEventListener('alpine:init', () => {
        if (window.Alpine) {
            window.Alpine.data('familyCodeApprovalForm', familyCodeApprovalComponent);
        }
    });
})();
</script>
