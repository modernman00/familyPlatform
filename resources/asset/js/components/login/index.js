import {Login} from '../../data/Login';
import { Input } from '../formBuilder'

try {
  Input(Login, 'login')
} catch (error) {
    console.log(error)
}
