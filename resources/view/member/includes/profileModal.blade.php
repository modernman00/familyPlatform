<div id="id01" class="w3-modal">
  <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">

    <div class="w3-center"><br>
      <span onclick="document.getElementById('id01').style.display='none'"
        class="w3-button w3-xlarge w3-hover-red w3-display-topright" title="Close Modal">&times;</span>


      @isset($data['img'])

      <img src="/img/profile/{{ $data['img'] }}" alt="Avatar" class="w3-circle w3-margin-top profileImg">
      @else
      <img src="/avatar/avatarF.png" alt="Avatar" style="width:30%" class="w3-circle w3-margin-top">

      @endisset


    </div>

      <p id="formPostMessageModal_notification"></p>

    <form class="w3-container" id='formPostMessageModal' method="post" enctype='multipart/form-data'>

      <div class="w3-section">

        <textarea class="w3-input w3-border" style="resize:none" spellcheck="false" name="postMessage" id="postMessage"
          cols="50" rows="10">Post here</textarea> <br>

        <div class="changeProfileDisplay">

          {{--  <button class='w3-button'>    --}}
          <input class="w3-input" type="file" id="post_img" name="post_img[]" multiple>
          {{--  </button>  --}}

          <button type="button" id="submitPost" name="submit" class="w3-button w3-green"><i class="fa fa-pencil"></i>
             Post</button>

          <button onclick="document.getElementById('id01').style.display='none'" type="button"
            class="w3-button w3-red">Cancel</button>

        </div>
    </form>



  </div>
</div>