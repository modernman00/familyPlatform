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

  // Helper to update the UI and input files
  const updatePreviews = (files) => {
    previewList.innerHTML = ''; // Clear previous previews

    if (files.length === 0) {
      previewContainer.classList.add('d-none');
      fileNamesDisplay.textContent = '';
      imageInput.value = ''; // Clear input if no files
      return;
    }

    // Create a new DataTransfer to update the file input
    const dataTransfer = new DataTransfer();

    files.forEach((file, index) => {
      dataTransfer.items.add(file);

      // Create wrapper for image and remove button
      const wrapper = document.createElement('div');
      wrapper.className = 'position-relative d-inline-block';

      const img = document.createElement('img');
      const reader = new FileReader();
      reader.onload = e => {
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);

      img.alt = 'Preview';
      img.className = 'img-thumbnail';
      img.style.maxWidth = '100px';
      img.style.maxHeight = '100px';

      // Create remove button
      const removeBtn = document.createElement('button');
      removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 p-0 rounded-circle d-flex align-items-center justify-content-center';
      removeBtn.style.width = '20px';
      removeBtn.style.height = '20px';
      removeBtn.style.transform = 'translate(30%, -30%)';
      removeBtn.innerHTML = '<i class="bi bi-x"></i>';
      removeBtn.onclick = (e) => {
        e.preventDefault(); // Prevent form submission if inside form
        const newFiles = files.filter((_, i) => i !== index);
        updatePreviews(newFiles);
      };

      wrapper.appendChild(img);
      wrapper.appendChild(removeBtn);
      previewList.appendChild(wrapper);
    });

    // Update the file input with the new list of files
    imageInput.files = dataTransfer.files;

    // Show file names and reveal preview container
    fileNamesDisplay.textContent = files.map(f => f.name).join(', ');
    previewContainer.classList.remove('d-none');
  };

  imageInput.addEventListener('change', () => {
    const files = Array.from(imageInput.files);

    // Check for file size limit
    const validFiles = files.filter(file => {
      if (file.size > 3 * 1024 * 1024) { // 3MB limit
        alert(`File ${file.name} is too large. Max 3MB allowed.`);
        return false;
      }
      return true;
    });

    updatePreviews(validFiles);
  });

  if (closePreviewBtnId) {
    const closePreviewBtn = id(closePreviewBtnId); // Button to clear image previews
    closePreviewBtn.addEventListener('click', () => {
      updatePreviews([]); // Clear all
    });
  }
}

