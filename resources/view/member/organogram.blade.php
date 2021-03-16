@extends ('base2')
@section('title', 'ORGANOGRAM')
@section('data-page-id', 'Organogram')
@section('content')
<br><br><br><br>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<div style="margin-left: 30px; background-color: black;">

    <div id="chart_div"></div>

</div>


<script>
    // variable 

    // const dataPull = <?php echo json_encode($data) ?>
    const dataPull = @json($data, JSON_PRETTY_PRINT);
    

    google.charts.load('current', {packages:["orgchart"]});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('string', 'Manager');
        data.addColumn('string', 'ToolTip');

        // For each orgchart box, provide the name, manager, and tooltip to show.

        
        data.addRows([
          [
              {
                  'v':dataPull.fatherName, 
                  'f': dataPull.fatherName + '<div style="color:red; font-style:italic">Father</div>'
              },
           '', 'The Father'],
               [
              {
                  'v':dataPull.motherName, 
                  'f': '<a href=/setProfile?id='+dataPull.id+' >'+ dataPull.motherName + '</a><div style="color:red; font-style:italic">Mother</div>'
              },
           '', 'The Mother'],

          [{'v':dataPull.firstName, 'f': dataPull.firstName + '<div style="color:red; font-style:italic">Vice President</div>'},
           dataPull.motherName, 'VP'
           ],

          [dataPull.sibling_name, dataPull.fatherName, ''],
          ['Carol', dataPull.motherName, ''],
          ['hhhh', dataPull.fatherName, ''],
        ]);

        // Create the chart.
        var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
        // Draw the chart, setting the allowHtml option to true for the tooltips.
        chart.draw(data, {'allowHtml':true});
      }
</script>

@endsection