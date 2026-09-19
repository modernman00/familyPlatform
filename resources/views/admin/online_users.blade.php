@extends('layouts.w3s_admin')

@section('title', 'Users Online Now')

@section('content')

<div class="w3-container" style="padding-top:20px; padding-bottom:40px;">
    <!-- Page Header Card -->
    <div class="w3-card-4 w3-white w3-padding-24 w3-round w3-margin-bottom" style="box-shadow: 0 4px 14px rgba(0,0,0,0.06);">
        <div class="w3-row-padding">
            <div class="w3-twothird">
                <h2 style="font-size: 26px; font-weight: 800; margin: 0; color: #1e293b;">
                    <i class="fa fa-signal text-success" style="color:#10b981; margin-right: 8px;"></i> Live Active Users (Online Now)
                </h2>
                <p class="w3-text-grey" style="margin-top: 6px; font-size: 15px;">
                    Users actively browsing or authenticated within the last 15 minutes across FamilyPlatform.
                </p>
            </div>
            <div class="w3-third w3-right-align" style="padding-top: 8px;">
                <a href="{{ $adminPrefix }}/members" class="w3-button w3-light-grey w3-border w3-round w3-medium" style="font-weight: 600; margin-right: 6px;">
                    <i class="fa fa-users"></i> All Registered Users
                </a>
                <span class="w3-tag w3-green w3-round w3-medium" style="padding: 8px 14px; font-weight: 700;">
                    <i class="fa fa-circle" style="font-size:9px;"></i> {{ $totalOnline }} Active Now
                </span>
            </div>
        </div>
    </div>

    @if(empty($onlineUsers))
        <div class="w3-panel w3-white w3-card-2 w3-round w3-center" style="padding: 48px 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.05);">
            <i class="fa fa-clock-o w3-text-grey" style="font-size: 54px; opacity: 0.6;"></i>
            <h3 style="font-weight: 700; color: #334155; margin-top: 16px;">No Users Currently Online</h3>
            <p class="w3-text-grey" style="max-width: 500px; margin: 8px auto 0;">
                No active member session or login events have been registered in the last 15 minutes.
            </p>
            <div style="margin-top: 24px;">
                <a href="{{ $adminPrefix }}/members" class="w3-button w3-blue w3-round" style="font-weight: 600;">
                    <i class="fa fa-users"></i> Browse All Registered Users
                </a>
            </div>
        </div>
    @else
        <div class="w3-card-4 w3-white w3-round w3-responsive" style="box-shadow: 0 4px 14px rgba(0,0,0,0.06); padding: 12px;">
            <table class="w3-table-all w3-hoverable" style="font-size: 14px;">
                <thead>
                    <tr style="background-color: #0f172a; color: #ffffff;">
                        <th style="padding: 14px 12px;">User</th>
                        <th style="padding: 14px 12px;">Email</th>
                        <th style="padding: 14px 12px;">Family Code</th>
                        <th style="padding: 14px 12px;">Location</th>
                        <th style="padding: 14px 12px;">Active Since</th>
                        <th style="padding: 14px 12px;">Live Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($onlineUsers as $user)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 14px 12px; vertical-align: middle;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    @if(!empty($user['img']))
                                        <img src="/{{ ltrim($user['img'], '/') }}" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #10b981;">
                                    @else
                                        <div style="width: 42px; height: 42px; border-radius: 50%; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
                                            {{ strtoupper(substr($user['firstName'] ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <span style="font-weight: 700; color: #1e293b; font-size: 15px;">
                                            {{ $user['firstName'] ?? 'Unnamed' }} {{ $user['lastName'] ?? '' }}
                                        </span>
                                        <div style="font-size: 12px; color: #64748b;">ID: #{{ $user['id'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 14px 12px; vertical-align: middle;">
                                <span style="color: #334155; font-weight: 500;">
                                    {{ $user['email'] ?? 'N/A' }}
                                </span>
                            </td>
                            <td style="padding: 14px 12px; vertical-align: middle;">
                                <span class="w3-tag w3-light-grey w3-border w3-round" style="font-family: monospace; font-weight: 700; color: #0369a1;">
                                    {{ $user['famCode'] ?? 'N/A' }}
                                </span>
                            </td>
                            <td style="padding: 14px 12px; vertical-align: middle;">
                                <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; color: #334155;">
                                    <i class="fa fa-map-marker" style="color: #ef4444; font-size: 16px;"></i>
                                    {{ $user['location'] ?? 'Unknown' }}
                                </span>
                            </td>
                            <td style="padding: 14px 12px; vertical-align: middle;">
                                <span class="w3-text-grey">
                                    <i class="fa fa-clock-o"></i> {{ $user['last_online_formatted'] ?? 'Active just now' }}
                                </span>
                            </td>
                            <td style="padding: 14px 12px; vertical-align: middle;">
                                <span class="w3-tag w3-green w3-round" style="font-weight: 700; padding: 4px 10px;">
                                    <i class="fa fa-circle" style="font-size: 8px;"></i> Live Now
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
