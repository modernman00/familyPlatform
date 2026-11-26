#!/usr/bin/env bash
# Generates 1200x630 Open Graph cards (<300 KB) with use-case-specific graphics.
set -euo pipefail
cd "$(dirname "$0")/../public/images"
B="/System/Library/Fonts/Supplemental/Arial Bold.ttf"
R="/System/Library/Fonts/Supplemental/Arial.ttf"
if [[ ! -f "$B" ]]; then
  B="$(command -v fc-match >/dev/null 2>&1 && fc-match -f '%{file}' 'DejaVu Sans:bold' || echo 'DejaVu-Sans-Bold')"
fi
if [[ ! -f "$R" ]]; then
  R="$(command -v fc-match >/dev/null 2>&1 && fc-match -f '%{file}' 'DejaVu Sans' || echo 'DejaVu-Sans')"
fi

# Base card with left typography column and right-hand graphic instructions
make_card() {
  local file="$1" badge="$2" t1="$3" t2="$4" sub1="$5" sub2="$6" chips="$7" accent="$8"
  shift 8
  magick -size 1200x630 gradient:'#0b1220-#172038' \
    -fill "$accent" -draw "rectangle 0,0 14,630" \
    -fill '#1e293b' -stroke '#334155' -strokewidth 2 -draw "roundrectangle 740,70 1140,550 24,24" -stroke none \
    "$@" \
    -fill '#fbbf24' -font "$B" -pointsize 24 -annotate +60+105 "$badge" \
    -fill '#ffffff' -font "$B" -pointsize 60 -annotate +60+195 "$t1" -annotate +60+270 "$t2" \
    -fill '#e2e8f0' -font "$R" -pointsize 26 -annotate +60+340 "$sub1" -annotate +60+376 "$sub2" \
    -fill '#1e293b' -draw "roundrectangle 60,425 680,472 12,12" \
    -fill '#cbd5e1' -font "$R" -pointsize 21 -annotate +80+456 "$chips" \
    -fill '#ffffff' -font "$B" -pointsize 28 -annotate +60+555 "myfamilyplatform.com" \
    -quality 86 "$file"
}

# 1. Living Avatar: Framed portrait + play button + voice waveform
make_card og-living-avatar.jpg "LIVING HERITAGE" "Bring Family" "Portraits to Life" \
  "Animate treasured portraits and" "hear their stories again." \
  "Living portraits  •  Family archive  •  Private" '#f59e0b' \
  -fill '#0f172a' -stroke '#f59e0b' -strokewidth 4 -draw "roundrectangle 820,115 1060,385 20,20" -stroke none \
  -fill '#334155' -draw "circle 940,215 940,262" \
  -fill '#475569' -draw "roundrectangle 865,275 1015,365 40,40" \
  -fill '#f59e0b' -draw "circle 940,385 940,418" \
  -fill '#0f172a' -draw "polygon 930,368 930,402 958,385" \
  -fill '#fbbf24' -draw "roundrectangle 830,465 846,505 6,6" -draw "roundrectangle 860,445 876,515 6,6" \
  -draw "roundrectangle 890,455 906,510 6,6" -draw "roundrectangle 920,435 936,520 6,6" \
  -draw "roundrectangle 950,450 966,512 6,6" -draw "roundrectangle 980,440 996,518 6,6" \
  -draw "roundrectangle 1010,460 1026,508 6,6" -draw "roundrectangle 1040,470 1056,500 6,6"

# 2. Kinship Calculator: 7-generation tree node graph
make_card og-kinship-calculator.jpg "KINSHIP TOOL" "7-Generation" "Kinship Calculator" \
  "Work out exact relationships" "across seven generations." \
  "Cousins  •  Removals  •  Degrees of kinship" '#10b981' \
  -stroke '#475569' -strokewidth 4 \
  -draw "line 940,155 850,255" -draw "line 940,155 1030,255" \
  -draw "line 850,255 805,365" -draw "line 850,255 895,365" \
  -draw "line 1030,255 985,365" -draw "line 1030,255 1075,365" -stroke none \
  -fill '#fbbf24' -draw "circle 940,155 940,185" \
  -fill '#10b981' -draw "circle 850,255 850,283" -draw "circle 1030,255 1030,283" \
  -fill '#38bdf8' -draw "circle 805,365 805,389" -draw "circle 895,365 895,389" \
  -fill '#38bdf8' -draw "circle 985,365 985,389" -fill '#f59e0b' -draw "circle 1075,365 1075,389" \
  -fill '#0f172a' -draw "roundrectangle 790,445 1090,505 14,14" \
  -fill '#10b981' -font "$B" -pointsize 21 -annotate +812+483 "2nd Cousin  •  1x Removed"

# 3. Why FamilyPlatform vs WhatsApp: Cluttered chat vs organised album grid
make_card og-why-familyplatform.jpg "PRIVATE BY DESIGN" "Why FamilyPlatform" "vs WhatsApp" \
  "Keep precious memories out of" "noisy chat threads forever." \
  "Organised albums  •  Family tree  •  Zero noise" '#ec4899' \
  -fill '#0f172a' -draw "roundrectangle 775,105 1105,275 16,16" \
  -fill '#334155' -draw "roundrectangle 795,125 990,158 10,10" -draw "roundrectangle 870,172 1085,205 10,10" -draw "roundrectangle 795,219 1020,252 10,10" \
  -stroke '#ef4444' -strokewidth 5 -draw "line 795,120 1085,260" -draw "line 1085,120 795,260" -stroke none \
  -fill '#0f172a' -stroke '#10b981' -strokewidth 3 -draw "roundrectangle 775,305 1105,515 16,16" -stroke none \
  -fill '#ec4899' -draw "roundrectangle 800,335 890,415 10,10" \
  -fill '#6366f1' -draw "roundrectangle 905,335 995,415 10,10" \
  -fill '#14b8a6' -draw "roundrectangle 1010,335 1080,415 10,10" \
  -fill '#10b981' -draw "roundrectangle 800,432 1080,485 10,10" \
  -fill '#ffffff' -font "$B" -pointsize 22 -annotate +845+467 "Organised & Private"

# 4. FamChat: Private speech bubbles + voice note + lock badge
make_card og-famchat.jpg "FAMCHAT" "Private Family" "Chat & Artefacts" \
  "Share voice notes, memories and" "updates in your family circle." \
  "Private threads  •  Voice notes  •  No strangers" '#22c55e' \
  -fill '#0f172a' -draw "roundrectangle 775,120 1030,210 20,20" \
  -fill '#22c55e' -draw "roundrectangle 840,240 1105,330 20,20" \
  -fill '#0f172a' -draw "roundrectangle 775,360 1055,445 20,20" \
  -fill '#cbd5e1' -draw "roundrectangle 800,148 970,164 6,6" -draw "roundrectangle 800,176 920,190 6,6" \
  -fill '#ffffff' -draw "circle 878,285 878,303" \
  -draw "roundrectangle 915,270 925,300 4,4" -draw "roundrectangle 935,260 945,310 4,4" \
  -draw "roundrectangle 955,268 965,302 4,4" -draw "roundrectangle 975,258 985,312 4,4" \
  -draw "roundrectangle 995,272 1005,298 4,4" -draw "roundrectangle 1015,265 1025,305 4,4" \
  -fill '#cbd5e1' -draw "roundrectangle 800,388 995,404 6,6" -draw "roundrectangle 800,414 940,428 6,6" \
  -fill '#fbbf24' -font "$B" -pointsize 22 -annotate +835+505 "Circle-Only Encrypted Chat"

# 5. Memory Prompts: Microphone + prompt card
make_card og-memory-prompts.jpg "ORAL HISTORY STUDIO" "Family Memory" "Prompt Studio" \
  "Thoughtful questions to chronicle" "elders before stories fade." \
  "Elder interviews  •  Guided prompts  •  Audio" '#f97316' \
  -fill '#f97316' -draw "circle 940,210 940,285" \
  -fill '#ffffff' -draw "roundrectangle 922,165 958,230 18,18" \
  -stroke '#ffffff' -strokewidth 5 -fill none -draw "arc 905,190 975,250 0,180" -draw "line 940,250 940,270" -stroke none \
  -fill '#0f172a' -draw "roundrectangle 775,325 1105,500 16,16" \
  -fill '#fbbf24' -font "$B" -pointsize 20 -annotate +805+365 "PROMPT #14" \
  -fill '#ffffff' -font "$R" -pointsize 23 -annotate +805+408 "\"What was your childhood" -annotate +805+440 "home really like?\"" \
  -fill '#f97316' -draw "roundrectangle 805,462 1075,478 6,6"

# 6. Prompt Guide: Stacked question cards
make_card og-prompt-guide.jpg "MEMORY PROMPT GUIDE" "Guided Questions" "for Your Family" \
  "Curated prompts to spark" "meaningful family conversations." \
  "Childhood  •  Traditions  •  Milestones" '#eab308' \
  -fill '#334155' -draw "roundrectangle 810,115 1095,380 18,18" \
  -fill '#0f172a' -stroke '#eab308' -strokewidth 3 -draw "roundrectangle 780,145 1065,435 18,18" -stroke none \
  -fill '#eab308' -draw "polygon 995,145 1035,145 1035,210 1015,192 995,210" \
  -fill '#eab308' -font "$B" -pointsize 24 -annotate +810+205 "CONVERSATION GUIDE" \
  -fill '#cbd5e1' -draw "roundrectangle 810,240 1020,256 6,6" -draw "roundrectangle 810,275 990,291 6,6" \
  -draw "roundrectangle 810,310 1010,326 6,6" -draw "roundrectangle 810,345 950,361 6,6" \
  -fill '#ffffff' -font "$B" -pointsize 22 -annotate +810+495 "Ready-to-ask family prompts"

# 7. How to Use: 3-step numbered walkthrough
make_card og-how-to-use.jpg "LIVING PRODUCT GUIDE" "How to Use" "FamilyPlatform" \
  "Step-by-step walkthroughs for" "your tree, vault and albums." \
  "Family tree  •  Photo vault  •  Festive envelopes" '#0ea5e9' \
  -fill '#0ea5e9' -draw "circle 815,170 815,198" -fill '#ffffff' -font "$B" -pointsize 24 -annotate +807+179 "1" \
  -fill '#0f172a' -draw "roundrectangle 860,140 1105,200 12,12" -fill '#e2e8f0' -font "$B" -pointsize 22 -annotate +880+178 "Build your family tree" \
  -fill '#10b981' -draw "circle 815,285 815,313" -fill '#ffffff' -font "$B" -pointsize 24 -annotate +807+294 "2" \
  -fill '#0f172a' -draw "roundrectangle 860,255 1105,315 12,12" -fill '#e2e8f0' -font "$B" -pointsize 22 -annotate +880+293 "Preserve photo albums" \
  -fill '#fbbf24' -draw "circle 815,400 815,428" -fill '#0f172a' -font "$B" -pointsize 24 -annotate +807+409 "3" \
  -fill '#0f172a' -draw "roundrectangle 860,370 1105,430 12,12" -fill '#e2e8f0' -font "$B" -pointsize 22 -annotate +880+408 "Invite your relatives" \
  -fill '#38bdf8' -font "$B" -pointsize 22 -annotate +815+500 "Interactive Walkthrough"

# 8. Playbook: Open book / chapters
make_card og-playbook.jpg "INTERACTIVE PLAYBOOK" "The FamilyPlatform" "Playbook" \
  "Practical workflows to preserve" "stories, trees and traditions." \
  "Oral history  •  Kinship tree  •  Memory prompts" '#8b5cf6' \
  -fill '#0f172a' -stroke '#8b5cf6' -strokewidth 3 \
  -draw "roundrectangle 775,140 935,440 12,12" -draw "roundrectangle 945,140 1105,440 12,12" -stroke none \
  -fill '#8b5cf6' -draw "roundrectangle 800,175 910,200 6,6" \
  -fill '#475569' -draw "roundrectangle 800,225 910,240 4,4" -draw "roundrectangle 800,258 890,273 4,4" -draw "roundrectangle 800,291 905,306 4,4" \
  -fill '#10b981' -draw "roundrectangle 970,175 1080,200 6,6" \
  -fill '#475569' -draw "roundrectangle 970,225 1080,240 4,4" -draw "roundrectangle 970,258 1060,273 4,4" -draw "roundrectangle 970,291 1075,306 4,4" \
  -fill '#fbbf24' -draw "polygon 1055,140 1085,140 1085,205 1070,188 1055,205" \
  -fill '#c4b5fd' -font "$B" -pointsize 22 -annotate +825+500 "Step-by-step Playbook"

# 9. Journal: Editorial article layout
make_card og-journal.jpg "FAMILY JOURNAL" "Stories, Heritage" "& Family Insights" \
  "Essays and guides on preserving" "family memory across generations." \
  "Oral history  •  Traditions  •  Digital heirlooms" '#3b82f6' \
  -fill '#0f172a' -draw "roundrectangle 775,110 1105,510 16,16" \
  -fill '#3b82f6' -draw "roundrectangle 800,135 1080,265 12,12" \
  -fill '#ffffff' -font "$B" -pointsize 24 -annotate +825+210 "HERITAGE ESSAYS" \
  -fill '#e2e8f0' -draw "roundrectangle 800,295 1065,318 6,6" -draw "roundrectangle 800,335 1020,355 6,6" \
  -fill '#64748b' -draw "roundrectangle 800,380 1075,396 4,4" -draw "roundrectangle 800,410 1045,426 4,4" -draw "roundrectangle 800,440 980,456 4,4" \
  -fill '#fbbf24' -font "$B" -pointsize 20 -annotate +800+490 "Read the latest stories"

# 10. About: Protective shield & connected family nodes
make_card og-about.jpg "ABOUT US" "Your Private" "Family Sanctuary" \
  "Built to keep generations connected" "without ads or algorithms." \
  "Private circles  •  Living tree  •  UK-hosted" '#6366f1' \
  -fill '#0f172a' -stroke '#6366f1' -strokewidth 5 \
  -draw "polygon 940,120 1085,175 1065,355 940,475 815,355 795,175" -stroke none \
  -fill '#6366f1' -draw "circle 940,235 940,268" \
  -fill '#10b981' -draw "circle 880,315 880,342" \
  -fill '#fbbf24' -draw "circle 1000,315 1000,342" \
  -fill '#e2e8f0' -font "$B" -pointsize 22 -annotate +835+510 "Zero Ads  •  Family Owned"

# 11. Contact: Envelope & message card
make_card og-contact.jpg "GET IN TOUCH" "Contact" "FamilyPlatform" \
  "Questions about your family vault," "tree or privacy? Write to us." \
  "Support  •  Privacy enquiries  •  Onboarding" '#14b8a6' \
  -fill '#0f172a' -stroke '#14b8a6' -strokewidth 4 -draw "roundrectangle 790,175 1090,395 18,18" \
  -draw "polyline 795,182 940,305 1085,182" -stroke none \
  -fill '#14b8a6' -draw "roundrectangle 830,435 1050,490 26,26" \
  -fill '#0f172a' -font "$B" -pointsize 24 -annotate +875+472 "Send a Message"

ls -lh og-*.jpg
