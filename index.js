const idValue = element => document.getElementById(element)
const idInput = element => document.getElementById(element).value 
const data = {
    user : idInput('userName'),
    fullN : idInput('fullName'),
    email : idInput('email'),
    pass1 : idInput('pw1'),
    pass2 : idInput('pw'),
}



function checkP(pw,pw1){
    pw = idInput('pw1')
    pw1 = idInput('pw')

    if (pw != pw1){
        console.log("Your passwords do not match")
        document.getElementById("notPw").innerHTML = "Your passwords do not match";
    }else{
        document.getElementById("notPw").innerHTML = "";

    }
}