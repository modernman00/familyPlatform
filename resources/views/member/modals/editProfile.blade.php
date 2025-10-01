    @php

        $token = urlencode(base64_encode(random_bytes(32)));
        $_SESSION['token'] = $token;
        setcookie('XSRF-TOKEN', $token, [
            'expires' => time() + 900,
            'path' => '/',
            'samesite' => 'Lax',
            'secure' => ($_ENV['APP_ENV'] ?? 'production') === 'production',
            'httponly' => false,
        ]);

    @endphp

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <form id="editProfileFormModal" class="editProfileFormModal" enctype="multipart/form-data">

                        <p id="setLoader"></p>

                        <p id="editProfileFormModal_notification"></p>

                        <div class="text-center mb-4">
                            @isset($data['img'])
                                <img src="{{ asset('img/profile/' . $data['img']) }}" alt="Profile"
                                    class="rounded-circle mb-1" width="122" height="122" id="profilePreview">
                                <br>

                                <label for="img" class="btn btn-sm btn-outline-secondary mb-0 mx-auto"
                                    title="Change Profile Picture">
                                    <i class="bi bi-camera-fill text-success"></i>
                                </label>
                                <input type="file" class="form-control" id="img" name="img"
                                    style="display: none;" accept="image/*">
                            @endisset
                        </div>
                        @php
                            $fields = [
                                'personal' => [
                                    [
                                        'label' => 'First Name',
                                        'name' => "personal['firstName']",
                                        'id' => 'firstName',
                                        'type' => 'text',
                                        'value' => $data['firstName'],
                                        'class' => 'form-control',
                                    ],
                                    [
                                        'label' => 'Last Name',
                                        'name' => "personal['lastName']",
                                        'id' => 'lastName',
                                        'type' => 'text',
                                        'value' => $data['lastName'],
                                        'class' => 'form-control',
                                    ],
                                    [
                                        'label' => 'Relationship Status',
                                        'name' => "personal['marital_status']",
                                        'id' => 'marital_status',
                                        'type' => 'select',
                                        'value' => $data['marital_status'],
                                        'options' => ['Single', 'Dating', 'Married', 'Divorced', 'Widowed'],
                                        'class' => 'form-select',
                                    ],
                                ],
                                'contact' => [
                                    [
                                        'label' => 'Location',
                                        'name' => "contact['country']",
                                        'id' => 'country',
                                        'type' => 'text',
                                        'value' => $data['country'],
                                        'class' => 'form-control',
                                    ],
                                    [
                                        'label' => 'Email',
                                        'name' => "contact['email']",
                                        'id' => 'email',
                                        'type' => 'text',
                                        'value' => $data['email'],
                                        'class' => 'form-control',
                                    ],
                                    [
                                        'label' => 'Mobile',
                                        'name' => "contact['mobile']",
                                        'id' => 'mobile',
                                        'type' => 'text',
                                        'value' => $data['mobile'],
                                        'class' => 'form-control',
                                    ],
                                ],
                            ];

                        @endphp

                        {{--  --}}

                        @foreach ($fields as $key => $group)
                            <div class="row mb-3">
                                @foreach ($group as $field)
                                    @include('form-helper-nested', $field)
                                @endforeach
                            </div>
                        @endforeach



<div class="g-recaptcha" data-sitekey="{{ $_ENV['RECAPTCHA_KEY'] }}" data-theme="dark"></div>
                    <br> 

                        {{-- hidden input --}}
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="editProfileBtnModal" name="submit" class="btn btn-primary">Save
                                Changes</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
