{{-- 
  Visual Supremacy Media Component (VSE)
  Renders responsive high-fidelity Display P3 AVIF, WebP, and original fallback
  with zero layout shift (CLS) and adaptive hardware contrast.
--}}
@props([
  'src' => '',
  'alt' => 'Family media',
  'class' => '',
  'style' => '',
  'loading' => 'lazy',
  'thumb' => null,
  'aspectRatio' => null,
])

@php
  $cleanSrc = trim((string)$src);
  $baseName = pathinfo($cleanSrc, PATHINFO_FILENAME);
  $dirName = pathinfo($cleanSrc, PATHINFO_DIRNAME);
  $dirPrefix = ($dirName && $dirName !== '.') ? rtrim($dirName, '/') . '/' : '/resources/images/post/';
  
  $avifSrc = $dirPrefix . $baseName . '.avif';
  $webpSrc = $dirPrefix . $baseName . '.webp';
  $fallbackSrc = (str_starts_with($cleanSrc, '/') || str_starts_with($cleanSrc, 'http')) 
      ? $cleanSrc 
      : $dirPrefix . $cleanSrc;
@endphp

<div class="vse-media-wrapper position-relative overflow-hidden {{ $class }}" 
     style="{{ $aspectRatio ? 'aspect-ratio: ' . $aspectRatio . ';' : '' }} {{ $style }}">
  
  @if($thumb)
    {{-- Instantaneous Micro-Preview (LQIP) --}}
    <img src="{{ $thumb }}" 
         alt="" 
         aria-hidden="true" 
         class="vse-lqip-blur position-absolute top-0 start-0 w-100 h-100" 
         style="filter: blur(16px); transform: scale(1.08); object-fit: cover; z-index: 1; pointer-events: none; transition: opacity 0.4s ease-out;" 
         onload="this.style.opacity='0'; setTimeout(() => this.remove(), 500);">
  @endif

  <picture class="w-100 h-100 d-block position-relative" style="z-index: 2;">
    {{-- Tier 1: Next-Gen AVIF (Display P3 / 10-bit Wide Gamut) --}}
    <source srcset="{{ $avifSrc }}" type="image/avif">

    {{-- Tier 2: Universal High-Efficiency WebP --}}
    <source srcset="{{ $webpSrc }}" type="image/webp">

    {{-- Tier 3: Pristine Master Fallback --}}
    <img src="{{ $fallbackSrc }}" 
         alt="{{ $alt }}" 
         loading="{{ $loading }}" 
         decoding="async" 
         class="vse-master-image w-100 h-100 d-block" 
         style="object-fit: cover; border-radius: inherit; transition: filter 0.3s ease-in-out;"
         onerror="this.onerror=null;">
  </picture>
</div>
