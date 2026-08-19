import { postFormData, id } from "@modernman00/shared-js-lib";
import Swal from 'sweetalert2';

const btn = id('editProfileBtnModal');
if (btn) {
    btn.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
            await postFormData(
                '/member/profilePage/editProfile',
                'editProfileFormModal',
                '/profilePage',
                'bootstrap'
            );
        } catch (error) {
            console.error('Edit Profile Submit Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error?.response?.data?.message || 'Failed to update profile.',
                confirmButtonColor: '#3085d6'
            });
        }
    });
}