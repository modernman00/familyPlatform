@extends('layouts.w3s_admin')

@section('title', 'New Member Registrations')

@section('content')

<div class="w3-container" style="padding-top:20px;">
    <div class="w3-card-4 w3-white w3-padding-16 w3-round w3-margin-bottom">
        <div class="w3-row-padding">
            <div class="w3-threequarter">
                <h2><b><i class="fa fa-user-plus text-primary"></i> New Member Registrations</b></h2>
                <p class="w3-text-grey">Pending applications requiring review and onboarding verification</p>
            </div>
            <div class="w3-quarter w3-right-align" style="padding-top:10px;">
                <span class="w3-tag w3-orange w3-large w3-padding w3-round"><i class="fa fa-clock-o"></i> {{ count($newRegistrations) }} Pending</span>
            </div>
        </div>
    </div>

    @if(empty($newRegistrations))
        <div class="w3-panel w3-light-blue w3-padding-16 w3-round">
            <h4><i class="fa fa-check-circle"></i> No Pending Registrations</h4>
            <p>All submitted member registrations have been processed.</p>
        </div>
    @else
        <div class="w3-responsive w3-card-4 w3-white w3-round">
            <table class="w3-table-all w3-hoverable align-middle">
                <thead>
                    <tr class="w3-dark-grey">
                        <th>ID</th>
                        <th>Date Registered</th>
                        <th>Full Name</th>
                        <th>FamCode</th>
                        <th>Email</th>
                        <th>Country / Location</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($newRegistrations as $reg)
                        <tr>
                            <td><b>#{{ $reg['id'] }}</b></td>
                            <td>{{ !empty($reg['created_at']) ? date('M d, Y H:i', strtotime($reg['created_at'])) : 'N/A' }}</td>
                            <td><b>{{ $reg['firstName'] ?? 'N/A' }} {{ $reg['lastName'] ?? '' }}</b></td>
                            <td><span class="w3-tag w3-blue w3-round">{{ $reg['famCode'] ?? 'N/A' }}</span></td>
                            <td>{{ $reg['email'] ?? 'N/A' }}</td>
                            <td><i class="fa fa-map-marker w3-text-red"></i> {{ $reg['country'] ?? 'Unknown' }}</td>
                            <td><span class="w3-tag w3-orange w3-round">Pending Review</span></td>
                            <td>
                                <a href="{{ $adminPrefix }}/reviewApps/approval?id={{ $reg['id'] }}" class="w3-button w3-green w3-small w3-round" onclick="return confirm('Approve this member registration?');">
                                    <i class="fa fa-check"></i> Approve
                                </a>
                                <a href="{{ $adminPrefix }}/reviewApps/decline?id={{ $reg['id'] }}" class="w3-button w3-red w3-small w3-round" onclick="return confirm('Decline this application?');">
                                    <i class="fa fa-times"></i> Decline
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
