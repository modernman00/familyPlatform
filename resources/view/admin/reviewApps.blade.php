@extends ('admin.base')

@section('title', 'new applications')

@section('content-title', " New Applications")

@section('content')

<div class="table-container">
    <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
    <thead class="has-background-primary">
        <tr class="has-text-white-bis">
            <th>No</th>
            <th>ID</th>
            <th>Date</th>
            <th>Name</th>
            <th>Alias</th>
            <th>Spouse</th>
            <th>Father</th>
            <th>Mother</th>
            <th>Mobile</th>
            <th>Country</th>
            {{-- <th>Decision</th> --}}
            <th>Email</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($result as $data )
        <tr>
            <td> {{ $no++}} </td>
             <td> {{ $data['id'] }} </td>
             {{-- application date --}}
              <td> {{ $data['created_at'] }} </td>
               <td> {{ $data['firstName']}} 
               {{ $data['lastName'] }} </td>
                <td> {{ $data['alias'] }} </td>
                 <td> {{ $data['spouseName'] }} </td>
                  <td> {{ $data['fatherName'] }} </td>
                   <td> {{ $data['motherName'] }} </td>
                     <td> {{ $data['mobile'] }} </td>
                      <td> {{ $data['country'] }} </td>
                 <td> {{ $data['email'] }} </td>

                   <td>
                    <a href="/admin/reviewApps/approval?id={{ $data['id'] }}"
                    data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to approve this application?');"
                              title="Approve">                               <i class="far fa-thumbs-up fa-lg" style="color:#264A0A">
                                </i>
                            </a>
                        </td>
                          <td>
                              <a href="/admin/reviewApps/decline?id={{ $data['id'] }}" data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to decline this application?');"
                              title="Decline">
                            <i class="far fa-thumbs-up fa-lg fa-rotate-180" style="color:#A21016"></i>
                                </a>
                        </td>
                          <td><a href="/admin/reviewApps/cancel?id={{ $data['id'] }}"
                          data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to cancel this application?');"
                              title="Cancel">
                          <i class="far fa-window-close fa-lg" style="color:#F00A0A"></i>
                          </a>
                        </td>
                        <td><a href="/admin/reviewApps/delete?id={{ $data['id'] }}"
                        data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to delete this application?');"
                              title="Delete">
                            <i class="fas fa-trash-alt fa-lg" style="color:#F00A0A"></i>
                            </a>
                        </td>

        </tr>
        @endforeach




    </tbody>
    </table>


    @endsection
