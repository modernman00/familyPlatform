@extends('email')

@section('title', 'New Post')
@section('subject', 'See what your family is sharing')

@section('content')
<p style="margin-bottom: 20px;">
    Hi <strong>{{ $data['firstName'] ?? 'there' }}</strong>,
</p>

<p style="margin-bottom: 20px; font-size: 18px; color: #1e293b; text-align: center;">
    <strong>{{ $data['authorName'] ?? 'A family member' }}</strong> just posted something new in the family feed!
</p>

@if(!empty($data['postContent']))
<div style="background-color: #f8fafc; border-left: 4px solid #00bfa5; padding: 16px 20px; border-radius: 6px; margin-bottom: 20px; font-style: italic; color: #475569; font-size: 15px; line-height: 1.6;">
    "{{ mb_strlen($data['postContent']) > 150 ? mb_substr($data['postContent'], 0, 150) . '...' : $data['postContent'] }}"
</div>
@endif

@if(!empty($data['video']) && !empty($data['video']['thumbnailUrl']))
<!-- Video Preview Card -->
<div style="margin-bottom: 25px; text-align: center;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 520px; margin: 0 auto; border-radius: 12px; overflow: hidden; background-color: #0f172a; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
        <tr>
            <td align="center" style="padding: 0; background-color: #000000; line-height: 0;">
                <a href="{{ $data['url'] ?? (getenv('MIX_APP_URL2') . 'profilePage') }}" target="_blank" style="display: block; text-decoration: none;">
                    <img src="{{ $data['video']['thumbnailUrl'] }}" alt="Video Preview" width="520" style="display: block; width: 100%; max-width: 520px; height: auto; border: 0;" />
                </a>
            </td>
        </tr>
        <tr>
            <td style="padding: 14px 18px; background-color: #1e293b; text-align: left;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td width="36" valign="middle" style="padding-right: 12px;">
                            <a href="{{ $data['url'] ?? (getenv('MIX_APP_URL2') . 'profilePage') }}" target="_blank" style="text-decoration: none; display: inline-block;">
                                <div style="width: 32px; height: 32px; background-color: #ef4444; border-radius: 50%; text-align: center; line-height: 32px;">
                                    <span style="color: #ffffff; font-size: 13px; font-family: Arial, sans-serif; margin-left: 2px;">&#9658;</span>
                                </div>
                            </a>
                        </td>
                        <td valign="middle">
                            <a href="{{ $data['url'] ?? (getenv('MIX_APP_URL2') . 'profilePage') }}" target="_blank" style="text-decoration: none; color: #ffffff; display: block;">
                                <div style="font-size: 14px; font-weight: 600; line-height: 1.3; color: #ffffff;">
                                    {{ ($data['video']['type'] ?? '') === 'youtube' ? 'YouTube Video' : (($data['video']['type'] ?? '') === 'vimeo' ? 'Vimeo Video' : 'Watch Video') }}
                                </div>
                                <div style="color: #94a3b8; font-size: 12px; margin-top: 2px;">
                                    Click to watch in family feed
                                </div>
                            </a>
                        </td>
                        <td align="right" valign="middle">
                            <a href="{{ $data['url'] ?? (getenv('MIX_APP_URL2') . 'profilePage') }}" target="_blank" style="display: inline-block; background-color: rgba(255,255,255,0.15); color: #ffffff; padding: 5px 12px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; text-decoration: none; letter-spacing: 0.5px;">
                                Watch
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
@elseif(!empty($data['postImage']))
<!-- Post Image Attachment Preview -->
<div style="margin-bottom: 25px; text-align: center;">
    <a href="{{ $data['url'] ?? (getenv('MIX_APP_URL2') . 'profilePage') }}" target="_blank" style="display: inline-block; text-decoration: none; max-width: 520px; width: 100%;">
        <img src="{{ $data['postImage'] }}" alt="Post Photo" width="520" style="display: block; width: 100%; max-width: 520px; height: auto; border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.06);" />
    </a>
</div>
@elseif(empty($data['postContent']))
<div style="background-color: #f8fafc; border-left: 4px solid #00bfa5; padding: 20px; border-radius: 4px; margin-bottom: 25px; font-style: italic; color: #475569;">
    "View the post to see more details..."
</div>
@endif

<div style="text-align: center; margin-top: 25px;">
    <a href="{{ $data['url'] ?? (getenv('MIX_APP_URL2') . 'profilePage') }}" style="background-color: #00bfa5; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block; box-shadow: 0 2px 6px rgba(0, 191, 165, 0.3);">View Post</a>
</div>
@endsection
