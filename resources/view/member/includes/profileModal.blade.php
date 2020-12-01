<div id="id01" class="w3-modal">
  <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">

    <div class="w3-center"><br>
      <span onclick="document.getElementById('id01').style.display='none'"
        class="w3-button w3-xlarge w3-hover-red w3-display-topright" title="Close Modal">&times;</span>
      <img src="/img/seyi/seyi1.jpeg" alt="Avatar" style="width:30%" class="w3-circle w3-margin-top">
    </div>

    <form class="w3-container" action="/member/profilePage/post" id='formPostMessageModal' method="post" enctype='multiple/form-data'>
      <div class="w3-section">

        <textarea class="w3-input w3-border" style="resize:none" spellcheck="false" name="postMessage" id="postMessage"
          cols="50" rows="10">Post here</textarea> <br>


        {{-- <button class='w3-button'>
          <input class="w3-input" type="file" id="postPicture" name='postPicture[]' multiple="true">
        </button> --}}

        <button type="submit" name="submit" class="w3-button w3-theme w3-button w3-green w3-large"><i
            class="fa fa-pencil"></i>
           Post</button>

      </div>
      <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
        <button onclick="document.getElementById('id01').style.display='none'" type="button"
          class="w3-button w3-red">Cancel</button>

      </div>
    </form>



  </div>
</div>