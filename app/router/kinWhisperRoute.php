<?php

declare(strict_types=1);

/**
 * 🎙️ Kin Whispers & Contextual Artifact Messaging Routes (Option B)
 */

$router->map('GET', '/kin-whispers', 'App\controller\KinWhisperController@index', 'KIN_WHISPERS_INDEX');
$router->map('GET', '/api/kin-whispers/unread-count', 'App\controller\KinWhisperController@apiUnreadCount', 'API_KIN_WHISPERS_UNREAD_COUNT');
$router->map('GET', '/api/kin-whispers/threads', 'App\controller\KinWhisperController@apiListThreads', 'API_KIN_WHISPERS_LIST');

// Sub-resources for threads (MUST come before generic /threads/[*:id])
$router->map('GET', '/api/kin-whispers/threads/[*:id]/whatsapp', 'App\controller\KinWhisperController@apiWhatsAppBridge', 'API_KIN_WHISPERS_WHATSAPP');
$router->map('POST', '/api/kin-whispers/threads/[*:id]/share/request', 'App\controller\KinWhisperController@apiShareRequest', 'API_KIN_WHISPERS_SHARE_REQUEST');
$router->map('POST', '/api/kin-whispers/threads/[*:id]/share/respond', 'App\controller\KinWhisperController@apiShareRespond', 'API_KIN_WHISPERS_SHARE_RESPOND');
$router->map('POST', '/api/kin-whispers/threads/[*:id]/screenshot', 'App\controller\KinWhisperController@apiRecordScreenshot', 'API_KIN_WHISPERS_SCREENSHOT');
$router->map('GET', '/api/kin-whispers/threads/[*:id]/events', 'App\controller\KinWhisperController@apiGetThreadEvents', 'API_KIN_WHISPERS_EVENTS');
$router->map('GET', '/api/kin-whispers/threads/[*:id]/media', 'App\controller\KinWhisperController@apiGetThreadMedia', 'API_KIN_WHISPERS_MEDIA');
$router->map('POST', '/api/kin-whispers/threads/[*:id]/pin', 'App\controller\KinWhisperController@apiTogglePin', 'API_KIN_WHISPERS_PIN');
$router->map('POST', '/api/kin-whispers/threads/[*:id]/mute', 'App\controller\KinWhisperController@apiToggleMute', 'API_KIN_WHISPERS_MUTE');
$router->map('POST', '/api/kin-whispers/threads/[*:id]/read', 'App\controller\KinWhisperController@apiMarkThreadRead', 'API_KIN_WHISPERS_MARK_READ');

// Presence (online / last seen) & privacy
$router->map('POST', '/api/kin-whispers/presence/sync', 'App\controller\KinWhisperController@apiPresenceSync', 'API_KIN_WHISPERS_PRESENCE_SYNC');
$router->map('POST', '/api/kin-whispers/presence/privacy', 'App\controller\KinWhisperController@apiSetPresencePrivacy', 'API_KIN_WHISPERS_PRESENCE_PRIVACY');

// Generic thread route
$router->map('GET', '/api/kin-whispers/threads/[*:id]', 'App\controller\KinWhisperController@apiGetThread', 'API_KIN_WHISPERS_GET');
$router->map('POST', '/api/kin-whispers/threads', 'App\controller\KinWhisperController@apiCreateThread', 'API_KIN_WHISPERS_CREATE');
$router->map('POST', '/api/kin-whispers/messages', 'App\controller\KinWhisperController@apiSendMessage', 'API_KIN_WHISPERS_SEND');
$router->map('POST', '/api/kin-whispers/audio-upload', 'App\controller\KinWhisperController@apiUploadAudio', 'API_KIN_WHISPERS_AUDIO_UPLOAD');
$router->map('POST', '/api/kin-whispers/media-upload', 'App\controller\KinWhisperController@apiUploadMedia', 'API_KIN_WHISPERS_MEDIA_UPLOAD');

// Message actions
$router->map('POST', '/api/kin-whispers/messages/[*:id]/reaction', 'App\controller\KinWhisperController@apiToggleReaction', 'API_KIN_WHISPERS_REACTION');
$router->map('POST', '/api/kin-whispers/messages/[*:id]/star', 'App\controller\KinWhisperController@apiToggleStar', 'API_KIN_WHISPERS_STAR');
$router->map('PATCH', '/api/kin-whispers/messages/[*:id]', 'App\controller\KinWhisperController@apiEditMessage', 'API_KIN_WHISPERS_EDIT_MESSAGE');
$router->map('DELETE', '/api/kin-whispers/messages/[*:id]', 'App\controller\KinWhisperController@apiDeleteMessage', 'API_KIN_WHISPERS_DELETE_MESSAGE');

// Group Whispers Roster Routes
$router->map('POST', '/api/kin-whispers/groups', 'App\controller\KinWhisperController@apiCreateGroup', 'API_KIN_WHISPERS_CREATE_GROUP');
$router->map('POST', '/api/kin-whispers/groups/[*:id]/participants', 'App\controller\KinWhisperController@apiAddGroupParticipants', 'API_KIN_WHISPERS_ADD_PARTICIPANTS');
$router->map('DELETE', '/api/kin-whispers/groups/[*:id]/participants/[*:userId]', 'App\controller\KinWhisperController@apiRemoveGroupParticipant', 'API_KIN_WHISPERS_REMOVE_PARTICIPANT');

// Calling Engine & WebRTC Signaling Routes
$router->map('POST', '/api/kin-whispers/threads/[*:id]/call/initiate', 'App\controller\KinWhisperController@apiInitiateCall', 'API_KIN_WHISPERS_CALL_INITIATE');
$router->map('POST', '/api/kin-whispers/threads/[*:id]/call/signal', 'App\controller\KinWhisperController@apiSendCallSignal', 'API_KIN_WHISPERS_CALL_SIGNAL');
$router->map('POST', '/api/kin-whispers/threads/[*:id]/call/end', 'App\controller\KinWhisperController@apiEndCall', 'API_KIN_WHISPERS_CALL_END');
$router->map('GET', '/api/kin-whispers/threads/[*:id]/call/info', 'App\controller\KinWhisperController@apiGetCallInfo', 'API_KIN_WHISPERS_CALL_INFO');
