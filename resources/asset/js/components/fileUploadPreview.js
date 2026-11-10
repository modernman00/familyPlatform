import { id } from '@shared';
import Swal from 'sweetalert2';
import { compressImageFile } from './helper/imageCompressor';
import { scanImageSafety } from './helper/mediaSafetyScanner';

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
  let attachedVideos = [];

  // Helper to update the UI and input files
  const updatePreviews = (files) => {
    accumulatedFiles = files || [];
    selectedFilesStore = accumulatedFiles;
    previewList.innerHTML = ''; // Clear previous previews

    const hasImages = accumulatedFiles.length > 0;
    const hasVideo = attachedVideos.length > 0;

    if (!hasImages && !hasVideo) {
      previewContainer.classList.add('d-none');
      if (fileNamesDisplay) fileNamesDisplay.textContent = '';
      imageInput.value = ''; // Clear input if no files
      return;
    }

    // Create a new DataTransfer to update the file input
    const dataTransfer = new DataTransfer();
    let totalBytes = 0;

    accumulatedFiles.forEach((file, index) => {
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

    // If videos are attached or being uploaded, render video thumbnail cards alongside photos (max 3)
    attachedVideos.forEach((vid, vIdx) => {
      const videoWrapper = document.createElement('div');
      videoWrapper.className = 'composer-thumb composer-thumb--video position-relative';
      videoWrapper.style.backgroundColor = '#0f172a';
      videoWrapper.style.borderRadius = '10px';
      videoWrapper.style.overflow = 'hidden';
      videoWrapper.style.display = 'flex';
      videoWrapper.style.alignItems = 'center';
      videoWrapper.style.justifyContent = 'center';

      if (vid.file) {
        totalBytes += vid.file.size;
      }

      // If we have a local video file, blob URL, or stream URL, render inline video thumbnail
      if (vid.streamUrl || vid.blobUrl || vid.file) {
        const vidPreview = document.createElement('video');
        vidPreview.className = 'atg-4k-video-render';
        vidPreview.muted = true;
        vidPreview.preload = 'metadata';
        vidPreview.playsInline = true;
        vidPreview.style.width = '100%';
        vidPreview.style.height = '100%';
        vidPreview.style.objectFit = 'cover';
        if (vid.streamUrl) {
          vidPreview.src = vid.streamUrl;
        } else if (vid.blobUrl) {
          vidPreview.src = vid.blobUrl;
        } else if (vid.file) {
          try {
            vidPreview.src = URL.createObjectURL(vid.file);
          } catch (_) {}
        }
        videoWrapper.appendChild(vidPreview);
      }

      // Center play icon overlay
      const playIcon = document.createElement('span');
      playIcon.className = 'position-absolute text-white shadow-sm';
      playIcon.style.pointerEvents = 'none';
      playIcon.innerHTML = '<i class="bi bi-play-circle-fill fs-3" style="color: #ffffff; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6));"></i>';
      videoWrapper.appendChild(playIcon);

      // Video badge indicator
      const badge = document.createElement('span');
      badge.id = `composerVidBadge_${vid.id || vIdx}`;
      badge.className = 'badge position-absolute bottom-0 start-0 m-1 px-1.5 py-0.5 rounded-pill';
      badge.style.fontSize = '0.65rem';
      badge.style.backgroundColor = (!vid.uploading && !vid.customising && vid.streamUrl)
        ? 'rgba(5, 150, 105, 0.94)'
        : 'rgba(163, 22, 33, 0.9)';
      badge.style.color = '#ffffff';
      badge.innerHTML = vid.uploading
        ? '<span class="spinner-border spinner-border-sm me-0.5" style="width: 8px; height: 8px;"></span> Uploading...'
        : (vid.customising
            ? '<i class="bi bi-scissors me-0.5"></i> Ready to trim'
            : `<i class="bi bi-stars me-0.5" style="color:#fde047;"></i> 4K Ready${attachedVideos.length > 1 ? ` · #${vIdx + 1}` : ''}`);
      videoWrapper.appendChild(badge);

      // Remove video button
      const removeVidBtn = document.createElement('button');
      removeVidBtn.type = 'button';
      removeVidBtn.className = 'composer-thumb__remove';
      removeVidBtn.setAttribute('aria-label', `Remove video ${vIdx + 1}`);
      removeVidBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
      removeVidBtn.onclick = (e) => {
        e.preventDefault();
        const targetId = vid.id || vid.streamUrl || vIdx;
        if (typeof window.__removeComposerVideo === 'function') {
          window.__removeComposerVideo(targetId);
        } else {
          attachedVideos = attachedVideos.filter((_, i) => i !== vIdx);
          updatePreviews(accumulatedFiles);
        }
      };
      videoWrapper.appendChild(removeVidBtn);

      previewList.appendChild(videoWrapper);
    });

    // "+" tile to add more photos or videos (max 5 images and max 3 videos)
    if (accumulatedFiles.length < 5 || attachedVideos.length < 3) {
      const addTile = document.createElement('label');
      addTile.className = 'composer-thumb composer-thumb--add';
      addTile.htmlFor = fileInputId;
      addTile.setAttribute('aria-label', 'Add more photos or videos');
      addTile.innerHTML = '<i class="bi bi-plus-lg"></i><span>Add</span>';
      previewList.appendChild(addTile);
    }

    // Update the file input with the new list of files
    imageInput.files = dataTransfer.files;

    // Show media count and reveal preview container with British English formatting
    if (fileNamesDisplay) {
      let countLabel = '';
      const vCount = attachedVideos.length;
      const iCount = accumulatedFiles.length;

      const allVideosReady = vCount > 0 && attachedVideos.every(v => !v.uploading && v.streamUrl);
      const savedBytesTotal = attachedVideos.reduce((acc, v) => acc + (v.savedBytes > 0 ? v.savedBytes : 0), 0);
      const atgBadgeHtml = allVideosReady
        ? ` <span class="badge rounded-pill ms-1 px-2 py-0.5" style="background:rgba(5,150,105,0.12);color:#047857;border:1px solid rgba(5,150,105,0.3);font-size:0.68rem;font-weight:700;">⚡ ATG 4K FastStart${savedBytesTotal > 102400 ? ` · Saved ${formatFileSize(savedBytesTotal)}` : ''}</span>`
        : '';

      if (iCount > 0 && vCount > 0) {
        countLabel = `<strong>${iCount} photo${iCount > 1 ? 's' : ''}, ${vCount} video${vCount > 1 ? 's' : ''}</strong> <small>· ${formatFileSize(totalBytes)}</small>${atgBadgeHtml}`;
      } else if (iCount > 0) {
        countLabel = `<strong>${iCount} photo${iCount > 1 ? 's' : ''}</strong> <small>· ${formatFileSize(totalBytes)}</small>`;
      } else if (vCount > 0) {
        countLabel = `<strong>${vCount} video${vCount > 1 ? 's' : ''}</strong>${totalBytes > 0 ? ` <small>· ${formatFileSize(totalBytes)}</small>` : ''}${atgBadgeHtml}`;
      }
      fileNamesDisplay.innerHTML = countLabel;
    }
    previewContainer.classList.remove('d-none');
  };

  // Listen for video lifecycle events from postEmojiImgProcess.js
  window.__onComposerVideoStaged = (data) => {
    if (!data) return;
    const existingIdx = attachedVideos.findIndex(v => (v.id && data.id && v.id === data.id) || (v.file && data.file && v.file.name === data.file.name && v.file.size === data.file.size));
    if (existingIdx >= 0) {
      attachedVideos[existingIdx] = { ...attachedVideos[existingIdx], ...data };
    } else if (attachedVideos.length < 3) {
      attachedVideos.push(data);
    }
    updatePreviews(accumulatedFiles);
  };

  window.__onComposerVideoUpdated = (data) => {
    if (!data) return;
    const existingIdx = attachedVideos.findIndex(v => (v.id && data.id && v.id === data.id) || (v.streamUrl && data.streamUrl && v.streamUrl === data.streamUrl) || (v.file && data.file && v.file.name === data.file.name && v.file.size === data.file.size));
    if (existingIdx >= 0) {
      attachedVideos[existingIdx] = { ...attachedVideos[existingIdx], ...data, uploading: false };
    } else if (attachedVideos.length < 3) {
      attachedVideos.push({ ...data, uploading: false });
    }
    updatePreviews(accumulatedFiles);
  };

  window.__onComposerVideoRemoved = (target) => {
    if (target === undefined || target === null) {
      attachedVideos = [];
    } else if (typeof target === 'number') {
      attachedVideos = attachedVideos.filter((_, i) => i !== target);
    } else {
      attachedVideos = attachedVideos.filter(v => v.id !== target && v.streamUrl !== target);
    }
    updatePreviews(accumulatedFiles);
  };

  imageInput.addEventListener('change', async () => {
    const rawSelectedFiles = Array.from(imageInput.files || []);
    if (!rawSelectedFiles.length) return;

    // Route any video files selected via #imageUpload directly into the Video Auto-Trim & Upload Studio (max 3 videos)
    const videoFiles = rawSelectedFiles.filter(f => (f.type && f.type.startsWith('video/')) || /\.(mp4|webm|mov|ogg)$/i.test(f.name || ''));
    const imageOnlyFiles = rawSelectedFiles.filter(f => !videoFiles.includes(f));

    // Link-first nudge: only prompt for massive (>150MB) raw files since ATG now optimises & fast-uploads standard videos natively.
    const nudgeThresholdBytes = 150 * 1024 * 1024;
    const largeVideo = videoFiles.some(f => f.size >= nudgeThresholdBytes);
    let videoFilesToProcess = videoFiles;
    if (largeVideo) {
      const choice = await Swal.fire({
        icon: 'info',
        title: 'Big video? Share it as a link instead',
        html: 'Paste a <strong>Google Photos</strong>, YouTube or Vimeo link into your post. It appears instantly and plays in full quality, with no waiting for an upload.<br><small>In Google Photos: open the video, tap Share, then Create link, and paste it here.</small>',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Paste a link',
        denyButtonText: 'Upload anyway',
        cancelButtonText: 'Cancel',
        focusConfirm: true,
      });
      const decision = choice.isConfirmed ? 'link' : (choice.isDenied ? 'upload' : 'cancel');
      document.dispatchEvent(new CustomEvent('video-link-nudge', { detail: { decision, count: videoFiles.length } }));
      // Anonymised telemetry (no PII): lets us measure how many people choose a link over a raw upload.
      try {
        const largestMb = Math.round(Math.max(...videoFiles.map(f => f.size)) / (1024 * 1024));
        fetch('/api/telemetry/event', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          keepalive: true,
          body: JSON.stringify({
            event: 'video_link_nudge',
            url: window.location.pathname,
            viewport_width: window.innerWidth,
            viewport_height: window.innerHeight,
            metadata: { decision, count: videoFiles.length, largest_mb: largestMb },
          }),
        }).catch(() => {});
      } catch (e) { /* telemetry must never break posting */ }
      if (decision !== 'upload') {
        videoFilesToProcess = [];
        if (decision === 'link') {
          const editor = document.getElementById('postMessageEditor');
          if (editor) editor.focus();
        }
      }
    }

    if (videoFilesToProcess.length > 0) {
      const remainingAllowance = 3 - attachedVideos.length;
      if (remainingAllowance <= 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Maximum 3 Videos',
          text: 'You can upload up to 3 videos per post.',
          timer: 3000,
          showConfirmButton: false
        });
      } else {
        const allowedVideos = videoFilesToProcess.slice(0, remainingAllowance);
        if (videoFilesToProcess.length > remainingAllowance) {
          Swal.fire({
            icon: 'info',
            title: 'Maximum 3 Videos',
            text: `Only ${remainingAllowance} more video${remainingAllowance > 1 ? 's' : ''} can be attached to this post.`,
            timer: 3500,
            showConfirmButton: false
          });
        }
        await Promise.all(
          allowedVideos.map(vFile =>
            typeof window.__handleComposerVideoFile === 'function'
              ? window.__handleComposerVideoFile(vFile)
              : Promise.resolve()
          )
        );
      }
    }

    if (!imageOnlyFiles.length) {
      if (accumulatedFiles.length === 0) {
        imageInput.value = '';
      } else {
        // Re-sync imageInput.files with accumulatedFiles so imageInput does not drop previously chosen photos!
        const dataTransfer = new DataTransfer();
        accumulatedFiles.forEach(file => dataTransfer.items.add(file));
        imageInput.files = dataTransfer.files;
      }
      if (attachedVideos.length > 0 || accumulatedFiles.length > 0) {
        updatePreviews(accumulatedFiles);
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

    // Client-Side Media Safety Pre-Screening (Option A: Explicit Adult Detection)
    // Run in parallel across selected images to keep multi-photo staging instantaneous!
    const safetyResults = await Promise.all(
      validRawFiles.map(async (file) => ({
        file,
        scanResult: await scanImageSafety(file)
      }))
    );

    const safeFiles = [];
    for (const { file, scanResult } of safetyResults) {
      if (!scanResult.isSafe) {
        Swal.fire({
          icon: 'warning',
          title: 'Family Safety Advisory',
          text: `Photo "${file.name}" appears to contain sexually explicit content or excessive nudity. FamilyPlatform is dedicated to safe, family-friendly memories for all generations.`,
          confirmButtonColor: '#a31621'
        });
      } else {
        safeFiles.push(file);
      }
    }
    if (!safeFiles.length) {
      if (fileNamesDisplay) fileNamesDisplay.textContent = '';
      if (!accumulatedFiles.length) previewContainer.classList.add('d-none');
      return;
    }

    // Show quick optimising status if files are chosen (en-GB)
    if (fileNamesDisplay) {
      fileNamesDisplay.textContent = 'Optimising photos for instant upload...';
      previewContainer.classList.remove('d-none');
    }

    // High-speed parallel client-side compression (balanced 2K Retina clarity at 0.85 quality for instant uploads)
    const compressedFiles = await Promise.all(
      safeFiles.map(file => compressImageFile(file, { maxWidth: 2048, maxHeight: 2048, quality: 0.85 }))
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
        if (typeof window.__removeComposerVideo === 'function') {
          window.__removeComposerVideo();
        }
        updatePreviews([]); // Clear all
      });
    }
  }
};

