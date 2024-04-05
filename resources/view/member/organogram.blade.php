@extends ('layouts.w3s_member')
@section('title', 'Organogram')
  {{-- custom css --}}
  <link rel="stylesheet" type="text/css" href="/public/style.css">



<style>
    p.substitle {
        text-align: center;
    }

</style>


@section('data-page-id', 'Organogram')
@section('content')
na
<br><br><br><br>


<h2 class="myFamilyTree"> {{isset($data['firstName']) ?  $data['firstName'] : "" }} {{ isset($data['lastName'])? $data['lastName'] : "" }}'s  family Tree</h2>

<p class="organogram_subtitle"> This family tree represents every member of the family who share the same father and mother, symbolising the strong bond and connection within the immediate family. In future updates, we will expand the tree to include other family members who are linked solely through either the mother's or father's side, further encompassing the broader family network. </p>
<hr>

<div class="tree">
    <ul>
        <li>
              <a href="#"><b>Father: </b>{{ isset($data['fatherName'])? $data['fatherName'] : "" }}<br></a>
            <a href="#"><b>Mother: </b>{{ isset($data['motherName'])? $data['motherName'] : "" }}</a>
            {{--  THE CHILDREN AND GRANDKIDS  --}}
            <ul>
                <li>
                    {{--  check if there is a spouse  --}}
                    {{-- @if($data['spouseName'] !== "Not Provided" || isset($data['spouseName']))
                    <a href="#"> 
                      <b>Spouse: </b>  {{ $data['spouseName'] }}
                    </a>
                    @else

                    @endif --}}


                    @isset($data['spouseName'])
                        @if ($data['spouseName'] !== "Not Provided")
                            <a href="#"> <b>Spouse: </b>  {{ $data['spouseName'] }} </a>
                        @endif                        
                    @endisset

                    {{--  first and last name  --}}
                     <a href="#">
                        {{ isset($data['firstName']) ?  $data['firstName'] : ""  }} 
                        {{ isset($data['lastName']) ?  $data['lastName'] : "" }}
                    </a>

                    {{--  check if there are kids  --}}
                    <ul>
                        @if(isset($getKids) !== null)
                        @foreach($getKids as $child)
                        <li>
                            <a href="#">{{ $child['kid_name'] }}</a>
                        </li>
                        @endforeach
                        @endif
                    </ul>
                    
                </li>

                 {{--  check if there are siblings  --}}

                @isset($getSiblings)
                    @foreach($getSiblings as $siblings)
                    @isset($siblings['sibling_name'])
                         <li> <a href="#">{{ $siblings['sibling_name'] }}</a>
                    @endisset
                       

                            @isset($siblingKid)
                             @isset($siblingKid['sibling_name'])
                                @if($siblingKid['sibling_name'] == $siblings['sibling_name'])

                                    <ul>
                                    @isset ($siblingKid['kidCount'])

                                      @for ($x = 0; $x < $siblingKid['kidCount']; $x++)
                                        <li>  <a href="#">{{ $siblingKid[$x]['kid_name'] }}</a> </li>   
                                      @endfor
                                        
                                    @endisset
                                  
                                    </ul>
                                @endif
                               @endisset  
                            @endisset
                        </li>
                    @endforeach
                @endisset
            </ul>
        </li>
    </ul>
</div>




@endsection