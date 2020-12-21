import FileUploadWithPreview from "file-upload-with-preview"
import "file-upload-with-preview/dist/file-upload-with-preview.min.css"


const upload = new FileUploadWithPreview('myUniqueUploadId')
upload.cachedFileArray

document.getElementById('testC').addEventListener('click', ()=> { alert('worked')})
document.getElementById('check').innerHTML = " nice and nice"