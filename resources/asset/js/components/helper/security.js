import { id } from '../global'

export const showPassword = (inputId) => {
    const y = id(inputId);
    if (y.type === "password") {
        y.type = "text";
    } else {
        y.type = "password";
    }
}