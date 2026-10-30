import { id } from '@shared';
import Swal from 'sweetalert2';
import { compressImageFile } from './helper/imageCompressor';

let selectedFilesStore = [];

export const getSelectedPostFiles = () => selectedFilesStore;
export const clearSelectedPostFiles = () => {
  selectedFilesStore = [];
};

/**
 * Formats bytes to human-readable string (e.g., 250 KB)
 */
function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

/**
 * Handles image file selection and previews thumbnails with client-side compression
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

  if (!imageInput || !previewContainer || !previewList) return;

  let accumulatedFiles = [];

  // Helper to update the UI and input files
  const updatePreviews = (files) => {
    accumulatedFiles = files;
    selectedFilesStore = files;
    previewList.innerHTML = ''; // Clear previous previews

    if (files.length === 0) {
      previewContainer.classList.add('d-none');
      if (fileNamesDisplay) fileNamesDisplay.textContent = '';
      imageInput.value = ''; // Clear input if no files
      return;
    }

    // Create a new DataTransfer to update the file input
    const dataTransfer = new DataTransfer();
    let totalBytes = 0;

    files.forEach((file, index) => {
      dataTransfer.items.add(file);
      totalBytes += file.size;

      // Thumbnail tile with its own remove button (styled by .composer-thumb in profilepage.scss)
      const wrapper = document.createElement('div');
      wrapper.className = 'composer-thumb';

      const img = document.createElement('img');
      const reader = new FileReader();
      reader.onload = e => {
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);
      img.alt = `Photo ${index + 1}`;

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'composer-thumb__remove';
      removeBtn.setAttribute('aria-label', `Remove photo ${index + 1}`);
      removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
      removeBtn.onclick = (e) => {
        e.preventDefault(); // Prevent form submission if inside form
        const newFiles = accumulatedFiles.filter((_, i) => i !== index);
        updatePreviews(newFiles);
      };

      wrapper.appendChild(img);
      wrapper.appendChild(removeBtn);
      previewList.appendChild(wrapper);
    });

    // "+" tile to add more photos (max 5)
    if (files.length < 5) {
      const addTile = document.createElement('label');
      addTile.className = 'composer-thumb composer-thumb--add';
      addTile.htmlFor = fileInputId;
      addTile.setAttribute('aria-label', 'Add more photos');
      addTile.innerHTML = '<i class="bi bi-plus-lg"></i><span>Add</span>';
      previewList.appendChild(addTile);
    }

    // Update the file input with the new list of files
    imageInput.files = dataTransfer.files;

    // Show file names and reveal preview container with optimized total size
    if (fileNamesDisplay) {
      fileNamesDisplay.innerHTML = `<strong>${files.length} photo${files.length > 1 ? 's' : ''}</strong> <small>· ${formatFileSize(totalBytes)}</small>`;
    }
    previewContainer.classList.remove('d-none');
  };

  imageInput.addEventListener('change', async () => {
    const rawSelectedFiles = Array.from(imageInput.files || []);
    if (!rawSelectedFiles.length) return;

    // Route any video files selected via #imageUpload directly into the Video Auto-Trim & Upload Studio
    const videoFiles = rawSelectedFiles.filter(f => (f.type && f.type.startsWith('video/')) || /\.(mp4|webm|mov|ogg)$/i.test(f.name || ''));
    const imageOnlyFiles = rawSelectedFiles.filter(f => !videoFiles.includes(f));

    if (videoFiles.length > 0 && typeof window.__handleComposerVideoFile === 'function') {
      window.__handleComposerVideoFile(videoFiles[0]);
    }

    if (!imageOnlyFiles.length) {
      if (accumulatedFiles.length === 0) {
        imageInput.value = '';
      }
      return;
    }

    // Check for raw file size limit (25MB max pre-compression limit)
    const validRawFiles = imageOnlyFiles.filter(file => {
      if (file.size > 25 * 1024 * 1024) {
        Swal.fire({
          icon: 'error',
          title: 'File Too Large',
          text: `File ${file.name} exceeds 25MB. Please choose a smaller image.`,
          timer: 3500,
          showConfirmButton: false
        });
        return false;
      }
      return true;
    });

    if (!validRawFiles.length) return;

    // Show quick optimizing status if files are chosen
    if (fileNamesDisplay) {
      fileNamesDisplay.textContent = 'Optimizing photos for instant upload...';
      previewContainer.classList.remove('d-none');
    }

    // High-speed parallel client-side compression (downscales to max 1920px Retina standard)
    const compressedFiles = await Promise.all(
      validRawFiles.map(file => compressImageFile(file, { maxWidth: 1920, maxHeight: 1920, quality: 0.82 }))
    );

    // Merge newly selected files with existing accumulated files (deduplicating by name and size)
    const existingIdentifiers = new Set(accumulatedFiles.map(f => `${f.name}_${f.size}`));
    const newUniqueFiles = compressedFiles.filter(f => !existingIdentifiers.has(`${f.name}_${f.size}`));
    
    const combinedFiles = [...accumulatedFiles, ...newUniqueFiles];

    if (combinedFiles.length > 5) {
      Swal.fire({
        icon: 'warning',
        title: 'Maximum 5 Images',
        text: 'You can upload up to 5 images per post. Only the first 5 images are kept.',
        timer: 3000,
        showConfirmButton: false
      });
    }

    const finalFiles = combinedFiles.slice(0, 5);
    updatePreviews(finalFiles);
  });

  if (closePreviewBtnId) {
    const closePreviewBtn = id(closePreviewBtnId); // Button to clear image previews
    if (closePreviewBtn) {
      closePreviewBtn.addEventListener('click', () => {
        updatePreviews([]); // Clear all
      });
    }
  }
};

