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
            <th>Loan</th>
            <th>Term</th>
            <th>Repayment</th>
            <th>Monthly </th>
            <th>Interest</th>
            <th>Income</th>
            <th>Referral</th>
            {{-- <th>Decision</th> --}}
            <th>Score</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($result as $data )
        <tr>
            <td> {{ $no++}} </td>
             <td> {{ $data['id'] }} </td>
             {{-- application date --}}
              <td> {{ $data['created_at'] }} </td>
               <td> {{ $data['first_name']}} {{ $data['last_name']}} </td>
                <td> £{{ number_format($data['loanamount']) }} </td>
                 <td> £{{ $data['terms'] }} </td>
                  <td> £{{ number_format($data['repayment']) }} </td>
                   <td> £{{ number_format($data['monthly']) }} </td>
                    <td> £{{ number_format($data['interest']) }} </td>
                     <td> £{{ number_format($data['monthly_income']) }} </td>
                      <td> {{ $data['code'] }} </td>

                       {{-- <td> {{ $data['decide'] }} </td> --}}
                        <td> {{ $data['credit_score'] }} </td>
                        <td>
                    <a href="/admin/loanApproval?id={{ $data['id'] }}"
                    data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to approve this application?');"
                              title="Approve">                               <i class="far fa-thumbs-up fa-lg" style="color:#264A0A">
                                </i>
                            </a>
                        </td>
                          <td>
                              <a href="/admin/loanDecline?id={{ $data['id'] }}" data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to decline this application?');"
                              title="Decline">
                            <i class="far fa-thumbs-up fa-lg fa-rotate-180" style="color:#A21016"></i>
                                </a>
                        </td>
                          <td><a href="/admin/loanCancel?id={{ $data['id'] }}"
                          data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to cancel this application?');"
                              title="Cancel">
                          <i class="far fa-window-close fa-lg" style="color:#F00A0A"></i>
                          </a>
                        </td>
                        <td><a href="/admin/loanDelete?id={{ $data['id'] }}"
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
