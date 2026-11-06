@extends('layouts.profileBase')

@section('content')
<div class="container py-4">
    <div class="mb-4 text-center">
        <span style="font-size:12px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#b45309; background:rgba(217,119,6,0.1); border:1px solid rgba(217,119,6,0.25); padding:4px 14px; border-radius:999px;">
            🏛️ Heritage & Archives Studio
        </span>
        <h1 style="font-size:2rem; font-weight:800; color:#0f172a; margin-top:8px;">
            Living Heirloom Portrait Studio
        </h1>
        <p style="color:#64748b; font-size:15px; max-width:640px; margin:0 auto;">
            Preserve ancestral heritage across generations. Bring historical family photographs to life with breathing depth, lifelike eye blinks, and interactive parallax movement.
        </p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            @include('components.living_heirloom_card')
        </div>
    </div>

    @if(!empty($heirlooms))
    <div class="mt-5">
        <h3 style="font-size:1.35rem; font-weight:800; color:#0f172a; margin-bottom:16px;">
            Family Heirloom Gallery ({{ count($heirlooms) }})
        </h3>
        <div class="row g-4">
            @foreach($heirlooms as $item)
            <div class="col-md-4 col-sm-6">
                <div class="card p-3 shadow-sm rounded-4 h-100" style="background:#ffffff; border:1px solid #e2e8f0;">
                    <img src="{{ $item['image_url'] }}" alt="{{ $item['ancestor_name'] }}" style="width:100%; height:200px; object-fit:cover; border-radius:12px;">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 style="font-size:1.05rem; font-weight:700; color:#0f172a; margin:0;">{{ $item['ancestor_name'] }}</h5>
                            <span class="badge bg-light text-dark">{{ $item['era_year'] }}</span>
                        </div>
                        <p style="color:#64748b; font-size:13px; margin:6px 0 0 0;">{{ $item['story_note'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
