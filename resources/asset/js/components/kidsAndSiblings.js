import { checkEmailObj } from "../data/checkEmailObj";
import { id, showError } from "../components/global";
import { checkBox } from "./helper/general";
import axios from "axios";

export const processKidsSiblings = (emailData, firstName, famCode = null) => {
  // normalise email list once
  const emailSet = new Set(
    (emailData || []).filter(Boolean).map(e => String(e).toLowerCase().trim())
  );

  const getFamCode = () => id("famCode_id")?.value ?? famCode ?? "";

  // debounce so it doesn't fire too aggressively
  let t = null;
  const debounce = (fn, wait = 200) => (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), wait);
  };

  const onInput = debounce((e) => {
    try {
      const el = e.target;
      if (!el || el.tagName !== "INPUT" || el.type !== "email") return;

      const elementId = el.id;
      if (!elementId) return;

      // Only handle the ids we generate
      const isKid = checkEmailObj.kidEmailInput.includes(elementId);
      const isSib = checkEmailObj.siblingEmail.includes(elementId);
      if (!isKid && !isSib) return;

      const emailInput = (el.value || "").toLowerCase().trim();
      const helpEl = id(`${elementId}_help`);
      if (!helpEl) return;

      const chooseEmail = isKid ? checkEmailObj.kidEmailInput : checkEmailObj.siblingEmail;
      const chooseName = isKid ? checkEmailObj.childrenNameInput : checkEmailObj.siblingName;

      const index = chooseEmail.indexOf(elementId);
      const nameId = chooseName[index];
      const nameValue = id(nameId)?.value ?? "";

      if (emailInput.length > 0 && emailInput.length < 7) {
        helpEl.style.display = "block";
        helpEl.innerHTML = "Email may be too short";
        return;
      }

      const exists = emailInput !== "" && emailSet.has(emailInput);

      helpEl.style.display = "block";
      helpEl.dataset.email = emailInput;
      helpEl.dataset.name = nameValue;
      helpEl.dataset.familyCode = getFamCode();

      helpEl.innerHTML = exists
        ? `Great news! ${nameValue || "This person"} is already registered on the platform`
        : `${nameValue || "This person"} is not on the platform. Do you want us to send an email invite? ${checkBox(elementId)}`;

    } catch (err) {
      showError(err);
    }
  }, 250);

  const onClick = async (e) => {
    try {
      const target = e.target;
      if (!target || !target.id) return;

      const isYes = target.id.endsWith("Yes");
      const isNo = target.id.endsWith("No");
      if (!isYes && !isNo) return;

      const baseId = target.id.replace(/(Yes|No)$/, "");
      const helpEl = id(`${baseId}_help`);
      if (!helpEl) return;

      if (isNo) {
        helpEl.style.display = "none";
        return;
      }

      // prevent double sending
      if (helpEl.dataset.sending === "1") return;
      helpEl.dataset.sending = "1";

      const email = helpEl.dataset.email;
      const name = helpEl.dataset.name;
      const familyCode = helpEl.dataset.familyCode;

      if (!email || !name) {
        helpEl.dataset.sending = "";
        return;
      }

      const postObj = {
        mobile: "",
        viewPath: "msg/contactNewMember",
        data: {
          email,
          name,
          yourName: firstName,
          familyCode,
        },
        subject: `${firstName} wants you to join the family network`,
      };

      const response = await axios.post("/register/contactNewMember", postObj);
      helpEl.innerHTML = response.data.message || "Invite sent";

      setTimeout(() => {
        helpEl.style.display = "none";
      }, 5000);

      helpEl.dataset.sending = "";

    } catch (err) {
      showError(err);
    }
  };

  document.addEventListener("input", onInput, true);
  document.addEventListener("click", onClick, true);

  // optional cleanup (if you ever re-init this)
  return () => {
    document.removeEventListener("input", onInput, true);
    document.removeEventListener("click", onClick, true);
  };
};
