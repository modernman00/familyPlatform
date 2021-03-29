@extends ('layouts.w3s_member')
@section('title', 'PROFILE_PAGE')
@section('data-page-id', 'profilePage')
@section('content')


<!-- Page Container -->
<div class="w3-container w3-content" style="max-width:1400px;margin-top:80px">
  <!-- The Grid -->
 <div class="w3-row">

  @include('member/includes/personal')

  <br>

  {{-- POST MESSAGE --}}
   <div class="w3-col m7">

      @include('member/includes/postMessage')
      {{-- DISPLAY POST --}}

      @foreach ($allData as $allData)
          @include('member/includes/postCard') 
      @endforeach
         
      {{-- @endfor  --}}
  </div>


    @include('member/includes/rightCol')


    {{-- MODAL --}}

    @include('member/includes/profileModal')


  </div>
  </div>

    {{-- MODAL --}}
    <script>
      try {
    const showModal = ()=> {
        return document.getElementById('id01').style.display = 'block'
    }
    document.getElementById('postMsg').addEventListener('click', showModal)
} catch (e) {
    console.log(e.message)
}




// insert comment  

// const checkTest= @php echo json_encode($allData) @endphp;
const checkTest= @json($allData);

console.log(checkTest)

const processComment = (el) => {
      const comment = document.getElementById(el.post_id).value
      document.getElementById(`insertComment-${el.post_id}`).insertAdjacentHTML('beforebegin', comment)
}

// checkTest.map(el => {
//   return document.getElementById(el.post_id).addEventListener('keyup', ()=> processComment(el))
// })

document.getElementById('profilePics').addEventListener('click', ()=> {
  document.getElementById('formProfilePics').style.display ="block"
})

    </script>



    @endsection