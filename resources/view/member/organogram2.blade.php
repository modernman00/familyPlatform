@extends ('base2')
@section('title', 'ORGANOGRAM')

<style>
    p.substitle {
        text-align: center;
    }


</style>

@section('data-page-id', 'Organogram')
@section('content')
na
<br><br><br><br>


<h2 class="myFamilyTree"> {{ $data['firstName'] }} {{ $data['lastName'] }}'s  family Tree</h2>
<p class="organogram_subtitle"> This family tree represents every member of the family who share the same father and mother, symbolising the strong bond and connection within the immediate family. In future updates, we will expand the tree to include other family members who are linked solely through either the mother's or father's side, further encompassing the broader family network. </p>
<hr>

<div class="tree">
    <ul>
        <li>
            <a href="#"><b>Father: </b>{{ $data['fatherName'] }}<br></a>
            <a href="#"><b>Mother: </b>{{ $data['motherName'] }}</a>
            {{--  THE CHILDREN AND GRANDKIDS  --}}
            <ul>
                <li>
                    {{--  check if there is a spouse  --}}
                    @if($data['spouseName'])
                    <a href="#"> 
                      <b>Spouse:</b>  {{ $data['spouseName'] }}
                    </a>
                    @else

                    @endif

                    {{--  first and last name  --}}
                    <a href="#">
                        {{ $data['firstName'] }} 
                        {{ $data['lastName'] }}
                    </a>

                    {{--  check if there are kids  --}}
                    <ul>
                        @if(isset($getKids) &&  $getKids !== null)
                            @foreach($getKids as $child)
                                <li>
                                    <a href="#">{{ $child['kid_name'] }}</a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                    
                </li>

                {{--  check if there are siblings  --}}

                @isset($getSibling)
                    @foreach($getSibling as $siblings)
                    @isset($siblings['sibling_name'])
                         <li> <a href="#">{{ $siblings['sibling_name'] }}</a>
                    @endisset
                       

                            @isset($siblingKid)
                                @if($siblingKid['sibling_name'] == $siblings['sibling_name'])

                                    <ul>
                                    @for ($x = 0; $x < $siblingKid['kidCount']; $x++)
                                        <li>  <a href="#">{{ $siblingKid[$x]['kid_name'] }}</a> </li>   
                                    @endfor
                                    </ul>
                                @endif
                            @endisset
                        </li>
                    @endforeach
                @endisset
            </ul>
        </li>
    </ul>
</div>





@endsection