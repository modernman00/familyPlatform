@component('mail::message')

# New Family Network Request

Hello {{ $approverName }},

**{{ $requesterName }}** is requesting to join your family network using your family code.

## Request Details

- **Name:** {{ $requesterName }}
- **Email:** {{ $requesterEmail }}
- **Request ID:** {{ $requestId }}
- **Expires in:** 7 days

## Action Required

Please review this request and either approve or deny it. Once approved, they will be connected to your family network.

@component('mail::button', ['url' => $approvalUrl . '?token=' . $approvalToken, 'color' => 'success'])
Review & Approve Request
@endcomponent

### Deny Request

If you don't recognize this person or don't want to approve this request, you can [deny it here]({{ $denyUrl }}?token={{ $approvalToken }}).

---

**Note:** If you didn't authorize this request or don't recognize the person, please deny it immediately.

**Security:** Your approval link includes a security token and is unique to this request.

@endcomponent
