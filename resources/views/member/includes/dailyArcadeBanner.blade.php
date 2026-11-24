{{-- Daily Family Arcade Banner on the Family Feed --}}
@php
    $arcadeDate = date('Y-m-d');
    $arcadeDateFormatted = date('D, j M Y', strtotime($arcadeDate));
    $arcadeGame = \App\services\FamilyArcadeService::getDailyFeaturedGame($arcadeDate);
    $arcadeGameLabel = match ($arcadeGame) {
        'kingrid' => 'KinGrid',
        'kinword' => 'KinWord',
        'kinpulse' => 'KinPulse',
        'wordie' => 'Wordie',
        'cheeseboard' => 'Cheeseboard (Queens)',
        'chess' => 'Family Chess',
        'ayo' => 'Ayò Olópon',
        'minesweeper' => 'Minesweeper',
        default => 'KinZip',
    };
@endphp
<div class="card border-0 shadow-sm mb-3" style="border-radius: 18px; background: linear-gradient(135deg, #0f172a 0%, #3b0712 58%, #1e1b4b 100%); color: #ffffff; border: 1px solid rgba(251, 191, 36, 0.45) !important;">
    <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3 min-w-0">
            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 46px; height: 46px; background: rgba(251, 191, 36, 0.2); border: 1.5px solid #fbbf24; color: #fde68a; font-size: 1.25rem;">
                <i class="bi bi-trophy-fill" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge rounded-pill" style="background: rgba(251, 191, 36, 0.22); color: #fef3c7; border: 1px solid rgba(251, 191, 36, 0.5); font-size: 0.7rem;">DAILY FAMILY ARCADE</span>
                    <span style="font-size: 0.78rem; color: #fde68a; font-weight: 600;">{{ $arcadeDateFormatted }}</span>
                </div>
                <div class="fw-bold mt-1" style="font-size: 0.98rem; color: #ffffff;">
                    Today’s Featured Challenge: {{ $arcadeGameLabel }}
                </div>
                <div style="font-size: 0.82rem; color: #e2e8f0;">
                    Race the ATG Bot or beat your family’s best time to claim the crown!
                </div>
            </div>
        </div>
        <a href="/arcade?game={{ $arcadeGame }}" class="btn fw-bold d-inline-flex align-items-center gap-2" style="background: #fbbf24; color: #0f172a !important; border-radius: 12px; min-height: 44px; white-space: nowrap; flex-shrink: 0;">
            <i class="bi bi-controller" aria-hidden="true"></i>
            <span>Play {{ $arcadeGameLabel }}</span>
        </a>
    </div>
</div>
