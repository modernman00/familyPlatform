@extends ('base2')
@section('title', 'ORGANOGRAM')

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
    const dataPull = @json($data, JSON_PRETTY_PRINT);
    const kids = @json($getSibling, JSON_PRETTY_PRINT);

        console.log(kids);
    

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

<br><br><br><br>

{{-- <div style="margin-left: 30px">
    <div id="chart_div"></div>
</div> --}}

<h2 class="myFamilyTree">My family tree</h2>
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
                        {{ $data['firstName'] }} {{ $data['lastName'] }}
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

                </li>

                @endforeach
                @endif

            </ul>
        </li>
    </ul>
</div>




@endsection