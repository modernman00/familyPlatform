import { id } from "../global"

// use this in conjunction with the file 
export const showImageFileUploadFn = (uploadBtn, inputId, fileName ) => {
  id(uploadBtn).addEventListener('click', function() {
  id(inputId).click();
});

id(inputId).addEventListener('change', function() {
  const fileNames = Array.from(this.files).map(file => file.name);
  id(fileName).innerText = fileNames.join(', ');
});

}