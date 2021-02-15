import { id, qSel, log } from "../global"

try {
    // id('spouse_div').style.display="none";
id('spouse_div').style.display = "none";

alert("it worked")
id("firstName_help").innerHTML = "checking"
} catch (error) {
    log(error.message)
}

