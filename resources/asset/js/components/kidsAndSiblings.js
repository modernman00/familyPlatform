import { checkEmailObj } from "../data/kidsSibling";
import { id, showError } from "../components/global";
import { checkBox } from "./helper/general";
import axios from "axios";

export const processKidsSiblings = (checkEmailExists, firstName, lastName, famCode = null) => {

  let chooseEmail = [];
  let chooseName = [];
  let helpHTML = "";
  // let errorHTML = ""; // Show error if applicant's email is registered


  document.onkeydown = (e) => {
    //. use the onclick to get the id of the element that was clicked
    // 2. use event listener to get the email value (if it is not empty)
    // 3. use the email value to check if it is in the array
    // 4. if it is in the array, show the Yes or No Radio
    // 5. click yes to send email to the kid or sibling

    try {
      // create an object with the data to check
      const elementId = e.target.id; // id of the element that was clicked or press down
      const emailInput = e.target.value;
      // this phase checks the id of what is being typed
      if (!elementId) throw new Error("target id is null and empty");

      // if the elementId indicate that it is a kid, then choosemail inherits all the kids array from the checkEmailObj and vis a versa

      if (checkEmailObj.kidEmailInput.includes(elementId)) {
        chooseEmail = checkEmailObj.kidEmailInput;
        chooseName = checkEmailObj.kidNameInput;
        helpHTML = id(`${elementId}_help`);

      } else if (checkEmailObj.siblingEmail.includes(elementId)) {
        chooseEmail = checkEmailObj.siblingEmail;
        chooseName = checkEmailObj.siblingName;
        helpHTML = id(`${elementId}_help`);

      }

      const checkFamilyEmail = () => {
        //this checks the value of what is being typed

        helpHTML.innerHTML = (emailInput.length > 5 && emailInput.length < 7) ? "Email may be too small" : "";

        // use the elementid to find the exact email value and name value
        const index = chooseEmail.indexOf(elementId);
        const email = id(chooseEmail[index]);
        const emailValue = email.value;
        const name = id(chooseName[index]);
        const nameValue = name.value;

        // if (!emailValue)
        //     throw new Error("another round of email is empty");
        // if (!nameValue) throw new Error("name is empty");
        // if (!getData.length) throw new Error("data is faulty");
        // checking family email 
        helpHTML.style.display = "block";
        helpHTML.innerHTML = checkEmailExists.includes(emailInput) ?
          `Great news! ${nameValue} is already registered on the platform` :
          `${nameValue} is not on the platform.Do you want us to send ${nameValue} a email to register to the platform ? ${checkBox(elementId)}`;

        // send the email to family membersa


        let setFamCode;
        const famCodeElement = id('famCode_id');
        if (famCodeElement) {
          setFamCode = famCodeElement.value;
        } else {
          // Handle the case where the element is not found or not yet loaded
          setFamCode = famCode; // Use a default value (famCode) or handle the situation accordingly
        }


        const processKidRadio = () => {
          const postObj = {
            mobile: "", // is this needed?
            viewPath: "msg/contactNewMember",

            data: {
              email: emailValue,
              name: nameValue,
              yourName: `${firstName} ${lastName}`,
              familyCode: setFamCode,
            },

            subject: `${firstName} ${lastName} Wants You: Experience the Magic of your Family Network Today!`,
          };

          axios
            .post("/register/contactNewMember", postObj)
            .then((response) => {
              helpHTML.innerHTML = response.data.message;

              setTimeout(() => {
                helpHTML.style.display = "none";
              }, 5000);
            })
            .catch((error) => {
              showError(error);
            });
        };

        id(`${elementId}Yes`).addEventListener("click", processKidRadio);

        id(`${elementId}No`).addEventListener("click", () => (id(`${elementId}No`).style.display = "none"));


      };

      if (chooseEmail.includes(elementId)) {

        checkFamilyEmail()

        // id(elementId).addEventListener("keyup", checkFamilyEmail);
      }
    } catch (error) {
      showError(error);
    }
  };
}