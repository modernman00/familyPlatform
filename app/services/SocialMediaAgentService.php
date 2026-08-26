<?php

declare(strict_types=1);

namespace App\services;

/**
 * SocialMediaAgentService
 *
 * Handles publishing to Facebook, LinkedIn, and Instagram using native cURL.
 * Ported from PartyPlatform and optimized for FamilyPlatform blogs.
 */
class SocialMediaAgentService
{
    private string $fbToken;
    private string $fbPageId;
    private string $igToken;
    private string $igAccountId;
    private string $linkedinToken;
    private string $linkedinUrn;

    public function __construct()
    {
        $this->fbToken       = getenv('FACEBOOK_PAGE_ACCESS_TOKEN') ?: '';
        $this->fbPageId      = getenv('FACEBOOK_PAGE_ID') ?: '';
        $this->igToken       = getenv('INSTAGRAM_ACCESS_TOKEN') ?: '';
        $this->igAccountId   = getenv('INSTAGRAM_ACCOUNT_ID') ?: '';
        $this->linkedinToken = getenv('LINKEDIN_ACCESS_TOKEN') ?: '';
        $this->linkedinUrn   = getenv('LINKEDIN_AUTHOR_URN') ?: '';
    }

    /**
     * Helper method to execute cURL requests
     */
    private function curlRequest(string $url, string $method = 'GET', array $data = [], array $headers = []): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($headers) && in_array('Content-Type: application/json', $headers)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => 'error', 'reason' => $error];
        }

        return json_decode((string) $response, true) ?? [];
    }

    /**
     * Publishes a post to all configured platforms
     *
     * @param string $message The content of the post
     * @param string|null $imageUrl The URL of the cover image
     * @param array $platforms Array of platforms to post to (e.g., ['facebook', 'linkedin', 'instagram'])
     * @return array Result indicating success/failure per platform
     */
    public function publish(string $message, ?string $imageUrl, array $platforms): array
    {
        $results = ['platforms' => []];
        $errors = [];

        // Facebook
        if (in_array('facebook', $platforms) && !empty($this->fbToken) && !empty($this->fbPageId)) {
            $fbResult = $this->postToFacebook($message, $imageUrl);
            $results['platforms']['facebook'] = $fbResult;
            if ($fbResult['status'] !== 'success') {
                $errors[] = 'Facebook: ' . ($fbResult['reason'] ?? 'unknown error');
            }
        }

        // Instagram (requires image)
        if (in_array('instagram', $platforms) && !empty($this->igToken) && !empty($this->igAccountId) && $imageUrl) {
            $igResult = $this->postToInstagram($message, $imageUrl);
            $results['platforms']['instagram'] = $igResult;
            if ($igResult['status'] !== 'success') {
                $errors[] = 'Instagram: ' . ($igResult['reason'] ?? 'unknown error');
            }
        }

        // LinkedIn
        if (in_array('linkedin', $platforms) && !empty($this->linkedinToken) && !empty($this->linkedinUrn)) {
            $liResult = $this->postToLinkedIn($message);
            $results['platforms']['linkedin'] = $liResult;
            if ($liResult['status'] !== 'success') {
                $errors[] = 'LinkedIn: ' . ($liResult['reason'] ?? 'unknown error');
            }
        }

        if (!empty($errors)) {
            $results['status'] = 'partial';
            $results['message'] = 'Some platforms failed to post.';
            $results['errors'] = $errors;
        } else {
            $results['status'] = 'success';
            $results['message'] = 'Posted successfully.';
        }

        return $results;
    }

    /**
     * Posts the content and optional image to the Facebook Page using Graph API.
     */
    private function postToFacebook(string $message, ?string $imageUrl): array
    {
        $endpoint = $imageUrl 
            ? "https://graph.facebook.com/v19.0/{$this->fbPageId}/photos" 
            : "https://graph.facebook.com/v19.0/{$this->fbPageId}/feed";

        $params = [
            'message'      => $message,
            'access_token' => $this->fbToken,
        ];

        if ($imageUrl) {
            $params['url'] = $imageUrl;
        }

        $data = $this->curlRequest($endpoint, 'POST', $params);

        if (isset($data['id'])) {
            return ['status' => 'success', 'post_id' => $data['id']];
        }

        return ['status' => 'error', 'reason' => $data['error']['message'] ?? 'Unknown response from Facebook.'];
    }

    /**
     * Posts the content and image to Instagram using Graph API.
     */
    private function postToInstagram(string $message, string $imageUrl): array
    {
        // Step 1: Create media container
        $mediaEndpoint = "https://graph.facebook.com/v19.0/{$this->igAccountId}/media";
        $mediaParams = [
            'image_url'    => $imageUrl,
            'caption'      => $message,
            'access_token' => $this->igToken,
        ];

        $mediaData = $this->curlRequest($mediaEndpoint, 'POST', $mediaParams);

        if (isset($mediaData['id'])) {
            // Step 2: Publish the media container
            $publishEndpoint = "https://graph.facebook.com/v19.0/{$this->igAccountId}/media_publish";
            $publishParams = [
                'creation_id'  => $mediaData['id'],
                'access_token' => $this->igToken,
            ];

            $publishData = $this->curlRequest($publishEndpoint, 'POST', $publishParams);
            return ['status' => 'success', 'post_id' => $publishData['id'] ?? null];
        }

        return ['status' => 'error', 'reason' => $mediaData['error']['message'] ?? 'Failed to create Instagram media container.'];
    }

    /**
     * Posts the content to LinkedIn.
     */
    private function postToLinkedIn(string $message): array
    {
        $endpoint = 'https://api.linkedin.com/v2/ugcPosts';
        $headers = [
            'Authorization: Bearer ' . $this->linkedinToken,
            'Content-Type: application/json',
            'X-Restli-Protocol-Version: 2.0.0',
        ];

        $params = [
            'author' => $this->linkedinUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $message],
                    'shareMediaCategory' => 'NONE'
                ]
            ],
            'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC']
        ];

        $data = $this->curlRequest($endpoint, 'POST', $params, $headers);

        if (isset($data['id'])) {
            return ['status' => 'success', 'post_id' => $data['id']];
        }

        return ['status' => 'error', 'reason' => $data['message'] ?? 'Unknown response from LinkedIn.'];
    }
}
