<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Multiple File Upload</title>
</head>
<body>

<form id="uploadForm" enctype="multipart/form-data">
  <input type="file" id="fileInput" name="files[]" multiple style="display: none;">
  <button type="button" id="uploadButton">Choose Files</button>
  <span id="fileNames"></span>
  <button type="submit">Upload</button>
</form>

<script>
document.getElementById('uploadButton').addEventListener('click', function() {
  document.getElementById('fileInput').click();
});

document.getElementById('fileInput').addEventListener('change', function() {
  var fileNames = Array.from(this.files).map(file => file.name);
  document.getElementById('fileNames').innerText = fileNames.join(', ');
});
</script>

</body>
</html>
