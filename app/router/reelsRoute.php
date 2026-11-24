<?php
declare(strict_types=1);

// Immersive Reels Experience View
$router->map('GET', '/reels', 'App\controller\members\ReelsController@index', 'family_reels_view');

// Reels JSON Feed
$router->map('GET', '/api/reels/feed', 'App\controller\members\ReelsController@getFeed', 'family_reels_feed_api');

// Create & Upload Reel
$router->map('POST', '/api/reels/upload', 'App\controller\members\ReelsController@uploadReel', 'family_reels_upload_api');

// Reactions (Heart/Celebrate)
$router->map('POST', '/api/reels/react', 'App\controller\members\ReelsController@toggleReaction', 'family_reels_react_api');

// Comments List
$router->map('GET', '/api/reels/comments', 'App\controller\members\ReelsController@getComments', 'family_reels_get_comments_api');

// Add Comment (supports 1-level threaded reply & curated GIFs)
$router->map('POST', '/api/reels/comment', 'App\controller\members\ReelsController@addComment', 'family_reels_add_comment_api');

// Update Comment (Author-only)
$router->map('POST', '/api/reels/comment/update', 'App\controller\members\ReelsController@updateComment', 'family_reels_update_comment_api');
$router->map('PUT', '/api/reels/comment/[i:commentId]', 'App\controller\members\ReelsController@updateComment', 'family_reels_update_comment_by_id_api');

// Comment Reaction (Toggle Like, Love, Haha, Wow, Sad, Angry)
$router->map('POST', '/api/reels/comment/react', 'App\controller\members\ReelsController@toggleCommentReaction', 'family_reels_react_comment_api');

// Delete Comment (Author of comment OR account holder of reel)
$router->map('POST', '/api/reels/comment/delete', 'App\controller\members\ReelsController@deleteComment', 'family_reels_delete_comment_api');
$router->map('DELETE', '/api/reels/comment/[i:commentId]', 'App\controller\members\ReelsController@deleteComment', 'family_reels_delete_comment_by_id_api');

// Delete Reel
$router->map('POST', '/api/reels/delete', 'App\controller\members\ReelsController@deleteReel', 'family_reels_delete_api');

// Record Reel View
$router->map('POST', '/api/reels/view', 'App\controller\members\ReelsController@recordView', 'family_reels_view_api');

// Update / Auto-Heal Reel Cover Thumbnail
$router->map('POST', '/api/reels/thumbnail', 'App\controller\members\ReelsController@updateThumbnail', 'family_reels_thumbnail_api');

// Multi-Angle Chrono-Sync Angles
$router->map('GET', '/api/reels/chrono-sync-angles', 'App\controller\members\ReelsController@getChronoSyncAngles', 'family_reels_chrono_sync_angles_api');

// Recent Active Reels for Multi-Angle Linking
$router->map('GET', '/api/reels/recent-clusters', 'App\controller\members\ReelsController@getRecentClusters', 'family_reels_recent_clusters_api');

// Curated Audio Library
$router->map('GET', '/api/reels/audio-library', 'App\controller\members\ReelsController@getAudioLibrary', 'family_reels_audio_library_api');

// Sound-Peer Reels (Viral "Use This Sound")
$router->map('GET', '/api/reels/sound-reels', 'App\controller\members\ReelsController@getReelsBySound', 'family_reels_sound_reels_api');

// Family Living Archives & 4K Reel Cinematic Studio
$router->map('GET', '/family-studio', 'App\controller\members\ReelsController@studio', 'family_video_studio');
$router->map('GET', '/member/family-studio', 'App\controller\members\ReelsController@studio', 'family_video_studio_alias');

// Direct-to-R2 Presigned Upload URL API
$router->map('GET', '/api/reels/presigned-url', 'App\controller\members\ReelsController@presignedUploadUrl', 'family_reels_presigned_url_api');

// Sovereign Studio Licensing & Headless Render API v1
$router->map('POST', '/api/v1/sovereign-studio/job', 'App\controller\api\SovereignStudioApiController@executeJob', 'sovereign_studio_api_job');
$router->map('GET', '/api/v1/sovereign-studio/sdk-manifest', 'App\controller\api\SovereignStudioApiController@sdkManifest', 'sovereign_studio_api_sdk_manifest');
$router->map('GET', '/api/v1/sovereign-studio/openapi.json', 'App\controller\api\SovereignStudioApiController@openApi', 'sovereign_studio_api_openapi');

// Same-Origin R2 Relay Upload Fallback (handles buckets without CORS / local dev)
$router->map('POST', '/api/reels/r2-proxy-upload', 'App\controller\members\ReelsController@r2ProxyUpload', 'family_reels_r2_proxy_upload_api');

// Sovereign Kinship Video Studio 12-Module Server Fallback & Validation API
$router->map('POST', '/api/family-studio/process-module', 'App\controller\members\ReelsController@studioProcessModule', 'family_studio_process_module_api');

// ATG Sovereign Cinematic Engine: Director Mode (member-only; session + CSRF enforced)
$router->map('GET', '/director-mode', 'App\controller\members\DirectorModeController@page', 'family_director_mode');
$router->map('GET', '/member/director-mode', 'App\controller\members\DirectorModeController@page', 'family_director_mode_alias');
$router->map('POST', '/api/director-mode/plan', 'App\controller\members\DirectorModeController@plan', 'family_director_mode_plan_api');
$router->map('POST', '/api/director-mode/render', 'App\controller\members\DirectorModeController@render', 'family_director_mode_render_api');



