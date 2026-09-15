@extends('layouts.w3s_admin')

@section('title', 'Admin CMS Dashboard')

@section('content')

<div class="w3-container" style="padding-top:16px">
    <div class="w3-card-4 w3-white w3-margin-bottom w3-padding-16 w3-round">
        <div class="w3-row-padding">
            <div class="w3-twothird">
                <h2><b><i class="fa fa-dashboard text-primary"></i> Administrative CMS Dashboard</b></h2>
                <p class="w3-text-grey">Zero-Trust Enterprise Control Panel &amp; System Health Monitoring</p>
            </div>
            <div class="w3-third w3-right-align" style="padding-top:10px">
                <span class="w3-tag w3-green w3-padding w3-round"><i class="fa fa-shield"></i> 2-FA Protected</span>
                <span class="w3-tag w3-blue w3-padding w3-round"><i class="fa fa-server"></i> System Normal</span>
            </div>
        </div>
    </div>
</div>

<!-- Stats Counter Grid -->
<div class="w3-row-padding w3-margin-bottom">
    <div class="w3-quarter">
        <div class="w3-container w3-blue w3-padding-16 w3-round shadow-sm">
            <div class="w3-left"><i class="fa fa-users w3-xxxlarge"></i></div>
            <div class="w3-right">
                <h3>@php echo \Src\Select::combineSelect(['selection'=>'SELECT_COUNT_ONE','table'=>'account','identifier1'=>'status','bind'=>['active']], 'selectCountFn2', 'ONE_IDENTIFIER'); @endphp</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Active Members</h4>
        </div>
    </div>
    <div class="w3-quarter">
        <div class="w3-container w3-orange w3-text-white w3-padding-16 w3-round shadow-sm">
            <div class="w3-left"><i class="fa fa-id-card w3-xxxlarge"></i></div>
            <div class="w3-right">
                <h3>@php echo \Src\Select::combineSelect(['selection'=>'SELECT_COUNT_ONE','table'=>'account','identifier1'=>'status','bind'=>['new']], 'selectCountFn2', 'ONE_IDENTIFIER'); @endphp</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Pending Approvals</h4>
        </div>
    </div>
    <div class="w3-quarter">
        <div class="w3-container w3-teal w3-padding-16 w3-round shadow-sm">
            <div class="w3-left"><i class="fa fa-rss w3-xxxlarge"></i></div>
            <div class="w3-right">
                <h3>@php echo \Src\Select::combineSelect(['selection'=>'SELECT_COUNT_ONE','table'=>'post','identifier1'=>'id','bind'=>['*']], 'selectCountFn2', 'ONE_IDENTIFIER'); @endphp</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>CMS Blog &amp; Posts</h4>
        </div>
    </div>
    <div class="w3-quarter">
        <div class="w3-container w3-dark-grey w3-padding-16 w3-round shadow-sm">
            <div class="w3-left"><i class="fa fa-lock w3-xxxlarge"></i></div>
            <div class="w3-right">
                <h3>100%</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Security Gate Health</h4>
        </div>
    </div>
</div>

<!-- Quick Action CMS Control Panel -->
<div class="w3-container w3-margin-bottom">
    <div class="w3-card-4 w3-white w3-padding-16 w3-round">
        <h4 class="w3-border-bottom w3-padding-16"><b><i class="fa fa-cogs"></i> Administrative Quick Actions</b></h4>
        <div class="w3-row-padding">
            <div class="w3-quarter w3-margin-bottom">
                <a href="/admin/reviewApps" class="w3-button w3-block w3-blue w3-padding-large w3-round">
                    <i class="fa fa-check-circle"></i> Review Applications
                </a>
            </div>
            <div class="w3-quarter w3-margin-bottom">
                <a href="/admin/setup-2fa" class="w3-button w3-block w3-purple w3-padding-large w3-round">
                    <i class="fa fa-shield"></i> Set Up Google 2-FA
                </a>
            </div>
            <div class="w3-quarter w3-margin-bottom">
                <a href="/admin/telemetry" class="w3-button w3-block w3-dark-grey w3-padding-large w3-round">
                    <i class="fa fa-line-chart"></i> View Telemetry
                </a>
            </div>
            <div class="w3-quarter w3-margin-bottom">
                <a href="/admin/erasure" class="w3-button w3-block w3-red w3-padding-large w3-round">
                    <i class="fa fa-user-times"></i> GDPR Erasure
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Audit Log & System Overview -->
<div class="w3-row-padding">
    <div class="w3-half">
        <div class="w3-card-4 w3-white w3-padding-16 w3-round">
            <h5><b><i class="fa fa-shield text-danger"></i> System Security Overview</b></h5>
            <ul class="w3-ul w3-hoverable">
                <li class="w3-padding-16">
                    <i class="fa fa-check-circle w3-text-green w3-large"></i> <b>Google Authenticator (2-FA):</b> Active &amp; Enforced on Admin Routes.
                </li>
                <li class="w3-padding-16">
                    <i class="fa fa-check-circle w3-text-green w3-large"></i> <b>Anti-Session Hijacking:</b> IP Subnet &amp; UA Fingerprint Bound.
                </li>
                <li class="w3-padding-16">
                    <i class="fa fa-check-circle w3-text-green w3-large"></i> <b>Rate Limiting:</b> Fail-Closed Brute Force Throttler Running.
                </li>
            </ul>
        </div>
    </div>
    <div class="w3-half">
        <div class="w3-card-4 w3-white w3-padding-16 w3-round">
            <h5><b><i class="fa fa-server text-info"></i> Environment Details</b></h5>
            <table class="w3-table w3-striped w3-bordered">
                <tr>
                    <td><b>Application Environment:</b></td>
                    <td><span class="w3-tag w3-light-grey">{{ getenv('APP_ENV') ?: 'production' }}</span></td>
                </tr>
                <tr>
                    <td><b>PHP Runtime Version:</b></td>
                    <td>{{ phpversion() }}</td>
                </tr>
                <tr>
                    <td><b>Admin Secret Path:</b></td>
                    <td><code>{{ getenv('ADMIN_SECRET_PATH') ?: '/lasu' }}</code></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<br>

@endsection

