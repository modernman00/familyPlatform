
import('../helper/general').then(res => res.matchInput('password_id', 'confirm_password_id', 'changePasswordErr'))

import('../global').then(response => {

    response.id("setLoader").style.display = "none";

    const submitChangePW = (e) => {
    try {
        e.preventDefault();

        const password = response.id('password_id').value

        // just in case there was an earlier error notification - remove it

        response.id('changePassword_notification').classList.remove('is-danger')

        response.id('error').innerHTML = ""

        if (password !== null) {
            response.id("setLoader").style.display = "block";

            response.id('loader').classList.add('loader')

            import('../helper/http').then(res=> res.postFormData("/login/changePW", "changePassword", "/login"))

            // window.location.replace("/login")
        }
    } catch (error) {
        response.showError(error)
    }
}

response.id('submit').addEventListener('click', submitChangePW)

})










