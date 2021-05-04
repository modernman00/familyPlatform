import { id, log } from '../global'
import { distinctValue } from '../helper/general'
import { getAllData } from '../api/index'
import { autocomplete } from '../helper/autocomplete'


const getData = getAllData()
let firstNameData = []
let generalData = []
let fatherName = []
let motherName = []
let cities = ['Lagos', 'Oyo', 'New York', 'London', 'Wiltshire']



getData.then(el =>
    el.map(element => {
        generalData.push(element)
        firstNameData.push(element.firstName)
        fatherName.push(element.alias)
        motherName.push(element.motherName)
    })
)
log(fatherName)
log(generalData)

const lastAutoComplete = id('firstName_id')
const fatherAutoComplete = id('fatherName_id')
const motherAutoComplete = id('motherName_id')

lastAutoComplete.setAttribute('autocomplete', 'off')
fatherAutoComplete.setAttribute('autocomplete', 'off')
motherAutoComplete.setAttribute('autocomplete', 'off')


// autoCompleter(lastNameAutoComplete, firstNameData)
const result = distinctValue(fatherName)
log(distinctValue([12, 12, 45, 21, 10]))
log(result)



autocomplete(lastAutoComplete, firstNameData)
autocomplete(fatherAutoComplete, fatherName)
autocomplete(motherAutoComplete, motherName)



