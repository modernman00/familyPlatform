 <div class="w3-col m2">
    <div class="w3-card w3-round w3-white w3-center">
      <div class="w3-container">
        <p>Upcoming Events:</p>
        <img src="/w3images/forest.jpg" alt="Forest" style="width:100%;">
        <p><strong>Holiday</strong></p>
        <p>Friday 15:00</p>
        <p><button class="w3-button w3-block w3-theme-l4">Info</button></p>
      </div>
    </div>
    <br>

    <br>

         <form action="/photos/postImages" method="POST" enctype="multipart/form-data">
          <div class="custom-file-container" data-upload-id="myUniqueUploadId">
            <label>Upload File <span class="material-icons">photo</span>
              <a href="javascript:void(0)" class="custom-file-container__image-clear" title="Clear Image">&times;</a>
            </label>

            <label for="postMessage_uploadPics" class="custom-file-container__custom-file">
              <input type="file" accept="image/*, image/heif, image/heic, video/*"
                class="custom-file-container__custom-file__custom-file-input" aria-label="Choose File"
                id="postMessage_uploadPics" name="photo[]" multiple />
              {{-- <input type="hidden" name="MAX_FILE_SIZE" value="10485760" /> --}}
              <span class="custom-file-container__custom-file__custom-file-control"></span>
            </label>

            <div class="custom-file-container__image-preview"></div>
            <button class="w3-btn w3-round w3-red" name="uploadPhotos" id="uploadPhotos" style="width: 240px;">Upload
              pictures </button>
          </div>

        </form>


    <!-- End Right Column -->
  </div>

  <script src="https://unpkg.com/file-upload-with-preview@4.1.0/dist/file-upload-with-preview.min.js"></script>
<script>
  const upload = new FileUploadWithPreview("myUniqueUploadId", {
                showDeleteButtonOnImages: true
            });
</script>