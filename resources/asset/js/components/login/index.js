import {Login} from '../../data/Login';
import { Input } from '../formBuilder'
import {showError} from '../../global';

try {
  Input(Login, 'login')
} catch (error) {
    showError(error)
}
