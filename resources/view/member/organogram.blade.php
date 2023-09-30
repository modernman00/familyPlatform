@extends ('base2')
@section('title', 'ORGANOGRAM')

<style>
    p.substitle {
        text-align: center;
    }


</style>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
    const dataPull = @json($data, JSON_PRETTY_PRINT);
    const kids = @json($getSibling, JSON_PRETTY_PRINT);

       // console.log(kids);
    

        google.charts.load('current', {packages:["orgchart"]});
        
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Manager');
            data.addColumn('string', 'ToolTip');

            // For each orgchart box, provide the name, manager, and tooltip to show.
            data.addRows([
              // father
              [
                  {
                      'v':dataPull.fatherName, 
                      'f': dataPull.fatherName + '<div style="color:red; font-style:italic">Father</div>'
                  }, "", ""
              ],
              // mother
              [
                  {
                      'v':dataPull.motherName, 
                      'f': '<a href=/setProfile?id='+dataPull.id +' >'+ dataPull.motherName + '</a><div style="color:red; font-style:italic">Mother</div>'
                  }, dataPull.fatherName, 'The Mother'
              ],
              // you and your siblings
              [
                  {
                      'v':dataPull.firstName, 'f': dataPull.firstName + '<div style="color:red; font-style:italic">Myself</div>'
                  }, dataPull.motherName, ''
              ],
              [ dataPull.sibling_name, dataPull.motherName, ""],
              // spouse
               [ dataPull.spouseName, dataPull.firstName, ""],

              // your children
              [ dataPull.kid_name, dataPull.spouseName, ""],
            ]);

            // Create the chart.
            var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
            // Draw the chart, setting the allowHtml option to true for the tooltips.
            chart.draw(data, {'allowHtml':true, width: 1000, height: 1000, title: `${dataPull.firstName} Family Tree`});
        }
</script>

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
                        {{ $data['spouseName'] }}
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

                @if(isset($getSibling) !== null)
                @foreach($getSibling as $siblings)
                <li>
                    <a href="#">{{ $siblings['sibling_name'] }}</a>

                    @if($siblingKid['sibling_name'] == $siblings['sibling_name'])

                    <ul>
                        @for ($x = 0; $x < $siblingKid['kidCount']; $x++)
                           <li>  
                            <a href="#">{{ $siblingKid[$x]['kid_name'] }}</a> </li>   
                        @endfor
                        </ul>
                    @endif
                </li>
                @endforeach
                @endif
            </ul>
        </li>
    </ul>
</div>





@endsection