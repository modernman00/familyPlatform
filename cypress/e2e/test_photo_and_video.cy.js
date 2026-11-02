import { loginFully } from '../support/login';
import { openModal } from '../support/ui';

describe('Mixed Media & Multiple Video Posts: Photos and Videos in One Post', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
        cy.get('#openPostModalTrigger', { timeout: 10000 }).should('be.visible');
    });

    it('allows a user to select photos and video in a single post and displays both in the feed', () => {
        const testCaption = `Family celebration with photo and video ${Date.now()}`;

        // 1. Open the post composer modal
        openModal('postModal');

        // 2. Type caption
        cy.get('textarea#postMessage').type(testCaption);

        // 3. Attach a photo via #imageUpload
        cy.get('#imageUpload').selectFile('cypress/fixtures/test_photo.png', { force: true });

        // 4. Attach a video via #imageUpload
        cy.get('#imageUpload').selectFile('cypress/fixtures/test_video.webm', { force: true });

        // Wait for and confirm the video auto-trim prompt
        cy.get('.swal2-confirm', { timeout: 10000 }).should('be.visible').click();

        // 5. Verify the media preview container shows both photo and video
        cy.get('#imagePreviewContainer', { timeout: 15000 }).scrollIntoView().should('be.visible');
        cy.get('#postModalImgFileNames').should('contain', 'photo').and('contain', 'video');
        cy.get('#imagePreviewList .composer-thumb--video').should('exist');

        // 6. Submit the post
        cy.get('#submitPost').click();

        // 7. Verify modal closes
        cy.get('#postModal', { timeout: 25000 }).should('not.be.visible');

        // 8. Verify the post with testCaption appears in the feed and contains both photo and video
        cy.contains(testCaption, { timeout: 15000 }).should('be.visible');
        cy.contains(testCaption).closest('.feed-post-card').within(() => {
            cy.get('img.post-media-first-img, img.card-img-top, picture img').should('exist');
            cy.get('.video-embed-container video, .video-embed-container iframe').should('exist');
        });

        cy.screenshot('mixed_media_single_video_verified_success');
    });

    it('allows uploading multiple videos (up to 3) with photos and renders videos horizontally in feed', () => {
        const testCaption = `Multiple videos horizontal celebration ${Date.now()}`;

        // 1. Open the post composer modal
        openModal('postModal');

        // 2. Type caption
        cy.get('textarea#postMessage').type(testCaption);

        // 3. Attach a photo
        cy.get('#imageUpload').selectFile('cypress/fixtures/test_photo.png', { force: true });

        // 4. Attach first video
        cy.get('#imageUpload').selectFile('cypress/fixtures/test_video.webm', { force: true });
        cy.get('.swal2-confirm', { timeout: 10000 }).should('be.visible').click();
        cy.get('.swal2-popup', { timeout: 10000 }).should('not.exist');

        // 5. Attach second video
        cy.get('#imageUpload').selectFile('cypress/fixtures/test_video_2.webm', { force: true });
        cy.get('.swal2-confirm', { timeout: 10000 }).should('be.visible').click();
        cy.get('.swal2-popup', { timeout: 10000 }).should('not.exist');

        // 6. Verify preview shows 1 photo, 2 videos
        cy.get('#imagePreviewContainer', { timeout: 15000 }).scrollIntoView().should('be.visible');
        cy.get('#postModalImgFileNames').should('contain', '1 photo, 2 videos');
        cy.get('#imagePreviewList .composer-thumb--video').should('have.length', 2);

        // 7. Submit post
        cy.get('#submitPost').click();

        // 8. Verify modal closes
        cy.get('#postModal', { timeout: 25000 }).should('not.be.visible');

        // 9. Verify post appears in feed with horizontal multi-video track
        cy.contains(testCaption, { timeout: 15000 }).should('be.visible');
        cy.contains(testCaption).closest('.feed-post-card').within(() => {
            cy.get('img.post-media-first-img, img.card-img-top, picture img').should('exist');
            cy.get('.multi-video-scroll-track').should('exist').and('be.visible');
            cy.get('.multi-video-card').should('have.length', 2);
        });

        cy.screenshot('multiple_videos_horizontal_verified_success');
    });
});
