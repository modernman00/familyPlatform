import { id } from '@shared';

/**
 * Handles image file selection and previews thumbnails
 * 
 * @param {string} fileInputId - The id of the hidden file input element
 * @param {string} previewListId - The id of the container where preview thumbnails are shown
 * @param {string} fileNamesDisplayId - The id of the text element where selected file names are displayed
 * @param {string} previewContainerId - The id of the wrapper element for image previews
 * @param {string} closePreviewBtnId - The id of the button to clear image previews if provided
 */
export const imagePreview = (fileInputId, previewListId, fileNamesDisplayId, previewContainerId, closePreviewBtnId = null) => {
  const imageInput = id(fileInputId); // Hidden file input for image uploads
  const previewContainer = id(previewContainerId); // Wrapper for image previews
  const previewList = id(previewListId); // Where preview thumbnails are shown
  const fileNamesDisplay = id(fileNamesDisplayId); // Text display of selected 

  imageInput.addEventListener('change', () => {
    const files = Array.from(imageInput.files); // Convert FileList to array
    previewList.innerHTML = ''; // Clear previous previews
    fileNamesDisplay.textContent = ''; // Clear file name display

    if (files.length === 0) {
      previewContainer.classList.add('d-none'); // Hide preview section
      return;
    }

    // For each selected image, create a thumbnail preview
    files.forEach(file => {
      if (file.size > 3 * 1024 * 1024) { // 3MB limit
        alert('File too large. Max 10MB allowed.');
      }

      const reader = new FileReader();
      reader.onload = e => {
        const img = document.createElement('img');
        img.src = e.target.result; // Base64 image data
        img.alt = 'Preview';
        img.className = 'img-thumbnail';
        img.style.maxWidth = '100px';
        img.style.maxHeight = '100px';
        previewList.appendChild(img);
      };
      reader.readAsDataURL(file); // Convert file to Base64
    });

    // Show file names and reveal preview container
    fileNamesDisplay.textContent = files.map(f => f.name).join(', ');
    previewContainer.classList.remove('d-none');
  });

  if (closePreviewBtnId) {
    const closePreviewBtn = id(closePreviewBtnId); // Button to clear image previews
    closePreviewBtn.addEventListener('click', () => {
      imageInput.value = ''; // Reset file input
      previewList.innerHTML = ''; // Clear thumbnails
      fileNamesDisplay.textContent = ''; // Clear file names
      previewContainer.classList.add('d-none'); // Hide preview section
    });
  }
}

