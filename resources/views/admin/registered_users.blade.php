@extends('layouts.w3s_admin')

@section('title', 'Registered Users')

@section('content')

<div class="w3-container" style="padding-top:20px; padding-bottom:40px;">
    <!-- Page Header Card -->
    <div class="w3-card-4 w3-white w3-padding-24 w3-round w3-margin-bottom" style="box-shadow: 0 4px 14px rgba(0,0,0,0.06);">
        <div class="w3-row-padding">
            <div class="w3-twothird">
                <h2 style="font-size: 26px; font-weight: 800; margin: 0; color: #1e293b;">
                    <i class="fa fa-users text-primary" style="color:#0284c7; margin-right: 8px;"></i> Registered Platform Users
                </h2>
                <p class="w3-text-grey" style="margin-top: 6px; font-size: 15px;">
                    Comprehensive directory of registered members, geographic location, and live connectivity activity.
                </p>
            </div>
            <div class="w3-third w3-right-align" style="padding-top: 8px;">
                <a href="{{ $adminPrefix }}/online-users" class="w3-button w3-teal w3-round w3-medium" style="font-weight: 600; margin-right: 6px;">
                    <i class="fa fa-signal"></i> See Who Is Online Now
                </a>
                <span class="w3-tag w3-blue w3-round w3-medium" style="padding: 8px 14px; font-weight: 700;">
                    Total: {{ count($members) }} Users
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Stats Summary -->
    <div class="w3-row-padding w3-margin-bottom" style="margin: 0 -8px;">
        <div class="w3-quarter" style="padding: 0 8px; margin-bottom: 12px;">
            <div class="w3-card w3-white w3-padding-16 w3-round" style="border-left: 4px solid #0284c7;">
                <span class="w3-text-grey" style="font-size: 13px; font-weight: 600; text-transform: uppercase;">Active Accounts</span>
                <h3 style="font-size: 24px; font-weight: 800; margin: 6px 0 0 0; color: #1e293b;">{{ count($members) }}</h3>
            </div>
        </div>
        <div class="w3-quarter" style="padding: 0 8px; margin-bottom: 12px;">
            <div class="w3-card w3-white w3-padding-16 w3-round" style="border-left: 4px solid #10b981;">
                <span class="w3-text-grey" style="font-size: 13px; font-weight: 600; text-transform: uppercase;">Online Now (15m)</span>
                <h3 style="font-size: 24px; font-weight: 800; margin: 6px 0 0 0; color: #10b981;">
                    {{ count(array_filter($members, fn($m) => !empty($m['is_online']))) }}
                </h3>
            </div>
        </div>
        <div class="w3-quarter" style="padding: 0 8px; margin-bottom: 12px;">
            <div class="w3-card w3-white w3-padding-16 w3-round" style="border-left: 4px solid #f59e0b;">
                <span class="w3-text-grey" style="font-size: 13px; font-weight: 600; text-transform: uppercase;">New Registrations</span>
                <h3 style="font-size: 24px; font-weight: 800; margin: 6px 0 0 0; color: #f59e0b;">
                    <a href="{{ $adminPrefix }}/new-registrations" style="text-decoration:none; color:#f59e0b;">View Pending &rarr;</a>
                </h3>
            </div>
        </div>
        <div class="w3-quarter" style="padding: 0 8px; margin-bottom: 12px;">
            <div class="w3-card w3-white w3-padding-16 w3-round" style="border-left: 4px solid #8b5cf6;">
                <span class="w3-text-grey" style="font-size: 13px; font-weight: 600; text-transform: uppercase;">Security Protocol</span>
                <h3 style="font-size: 20px; font-weight: 700; margin: 8px 0 0 0; color: #8b5cf6;"><i class="fa fa-lock"></i> GDPR / Zero-PII</h3>
            </div>
        </div>
    </div>

    <!-- Members Table Card -->
    <div class="w3-card-4 w3-white w3-round w3-responsive" style="box-shadow: 0 4px 14px rgba(0,0,0,0.06); padding: 12px;">
        <table class="w3-table-all w3-hoverable" style="font-size: 14px;">
            <thead>
                <tr style="background-color: #0f172a; color: #ffffff;">
                    <th style="padding: 14px 12px;">User</th>
                    <th style="padding: 14px 12px;">Contact Info</th>
                    <th style="padding: 14px 12px;">Family Code</th>
                    <th style="padding: 14px 12px;">Location / Country</th>
                    <th style="padding: 14px 12px;">Last Online</th>
                    <th style="padding: 14px 12px;">Status</th>
                    <th style="padding: 14px 12px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $m)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 14px 12px; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                @if(!empty($m['img']))
                                    <img src="/{{ ltrim($m['img'], '/') }}" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0;">
                                @else
                                    <div style="width: 42px; height: 42px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
                                        {{ strtoupper(substr($m['firstName'] ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <span style="font-weight: 700; color: #1e293b; font-size: 15px;">
                                        {{ $m['firstName'] ?? 'Unnamed' }} {{ $m['lastName'] ?? '' }}
                                    </span>
                                    <div style="font-size: 12px; color: #64748b;">ID: #{{ $m['id'] }} &bull; {{ $m['gender'] ?? 'Not specified' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 12px; vertical-align: middle;">
                            <div style="color: #334155; font-weight: 500;">
                                <i class="fa fa-envelope-o text-muted"></i> {{ $m['email'] ?? 'No email' }}
                            </div>
                            @if(!empty($m['mobile']))
                                <div style="font-size: 12px; color: #64748b;">
                                    <i class="fa fa-phone text-muted"></i> {{ $m['mobile'] }}
                                </div>
                            @endif
                        </td>
                        <td style="padding: 14px 12px; vertical-align: middle;">
                            <span class="w3-tag w3-light-grey w3-border w3-round" style="font-family: monospace; font-weight: 700; color: #0369a1;">
                                {{ $m['famCode'] ?? 'N/A' }}
                            </span>
                        </td>
                        <td style="padding: 14px 12px; vertical-align: middle;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; color: #334155;">
                                <i class="fa fa-map-marker" style="color: #ef4444; font-size: 16px;"></i>
                                {{ $m['location'] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td style="padding: 14px 12px; vertical-align: middle;">
                            @if(!empty($m['is_online']))
                                <span class="w3-tag w3-green w3-round" style="font-weight: 700; padding: 4px 8px;">
                                    <i class="fa fa-circle" style="font-size: 8px;"></i> Online Now
                                </span>
                            @else
                                <span class="w3-text-grey" style="font-size: 13px;">
                                    <i class="fa fa-clock-o"></i> {{ $m['last_online_formatted'] ?? 'Offline' }}
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px 12px; vertical-align: middle;">
                            <span class="w3-tag w3-teal w3-round" style="font-size: 12px; font-weight: 600; text-transform: uppercase;">
                                {{ $m['status'] ?? 'Active' }}
                            </span>
                        </td>
                        <td style="padding: 14px 12px; vertical-align: middle; text-align: center;">
                            <a href="/profile/{{ $m['id'] }}" target="_blank" class="w3-button w3-light-grey w3-border w3-round w3-small" style="font-weight: 600;">
                                <i class="fa fa-external-link"></i> Profile
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="w3-center w3-padding-32 w3-text-grey">
                            <i class="fa fa-users w3-xxlarge"></i>
                            <p style="margin-top: 10px; font-size: 16px;">No registered members found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
