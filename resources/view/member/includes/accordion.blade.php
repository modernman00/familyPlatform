 <div class="w3-card w3-round">
        <div class="w3-white">
          <button onclick="myFunction('Demo2')" class="w3-button w3-block w3-theme-l1 w3-left-align"><i
              class="fa fa-calendar-check-o fa-fw w3-margin-right"></i> My Events</button>
          <div id="Demo2" class="w3-hide w3-container">
            <p>Some other text..</p>
          </div>
          <button onclick="myFunction('Demo3')" class="w3-button w3-block w3-theme-l1 w3-left-align"><i
              class="fa fa-users fa-fw w3-margin-right"></i> My Photos</button>
          <div id="Demo3" class="w3-hide w3-container">
            <div class="w3-row-padding">
              <br>

             
            @foreach($pics2Id as $images)
            @for ($i = 0; $i < 6; $i++)
              @if($images["post_img$i"])
                <div class="w3-half">
                <img src="/img/post/{{ $images["post_img$i"] }}" style="width:100%" class="w3-margin-bottom" alt="{{ $images["post_img$i"] }}">
              </div>
              @endif
              
            @endfor
            @endforeach

            </div>
          </div>
        </div>
      </div>