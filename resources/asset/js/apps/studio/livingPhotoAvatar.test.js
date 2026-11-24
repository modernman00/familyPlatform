import { describe, it, expect, vi, beforeEach } from 'vitest';
import { AtgLivingPhotoAvatarStage, ASPECT_RATIOS } from '../../components/livingPhotoAvatar.js';
import { DeformablePhotoPuppet } from '../../components/atgPuppetMeshEngine.js';
import { encodeAnimatedGif } from '../../components/atgGifEncoder.js';

function createMockCanvas() {
  const ctx = {
    clearRect: vi.fn(),
    drawImage: vi.fn(),
    save: vi.fn(),
    restore: vi.fn(),
    beginPath: vi.fn(),
    moveTo: vi.fn(),
    lineTo: vi.fn(),
    closePath: vi.fn(),
    arc: vi.fn(),
    arcTo: vi.fn(),
    rect: vi.fn(),
    clip: vi.fn(),
    fill: vi.fn(),
    stroke: vi.fn(),
    fillRect: vi.fn(),
    fillText: vi.fn(),
    measureText: vi.fn(() => ({ width: 80 })),
    translate: vi.fn(),
    rotate: vi.fn(),
    scale: vi.fn(),
    transform: vi.fn(),
    setTransform: vi.fn(),
    createRadialGradient: vi.fn(() => ({ addColorStop: vi.fn() })),
    createLinearGradient: vi.fn(() => ({ addColorStop: vi.fn() })),
  };
  return {
    width: 512,
    height: 512,
    hidden: false,
    getContext: vi.fn(() => ctx),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    getBoundingClientRect: vi.fn(() => ({ left: 0, top: 0, width: 256, height: 256 })),
  };
}

describe('DeformablePhotoPuppet & AtgLivingPhotoAvatarStage', () => {
  let canvas;
  let viewport;
  let stage;

  beforeEach(() => {
    vi.spyOn(document, 'createElement').mockImplementation(() => createMockCanvas());
    canvas = createMockCanvas();
    viewport = createMockCanvas();
    stage = new AtgLivingPhotoAvatarStage({
      canvas,
      viewport,
      photoUrl: '/resources/images/profile/test.jpg',
    });
  });

  it('calibrates eye & mouth rig from MediaPipe face box and detects full-body framing', () => {
    stage.applyFaceBox({ x: 0.3, y: 0.1, w: 0.25, h: 0.25 });
    expect(stage.primaryPuppet.rig.isFullBody).toBe(true);
    expect(stage.heirloom.faceMesh.leftEye.x).toBeCloseTo(0.425 - 0.25 * 0.2);
    expect(stage.primaryPuppet.rig.mouth.y).toBeCloseTo(0.1 + 0.25 * 0.74);
  });

  it('deforms jaw/mouth vertices when voiceLevel > 0 and pinches eyelids on blink', () => {
    const puppet = new DeformablePhotoPuppet({ gridCols: 12, gridRows: 12 });
    puppet.applyFaceBox({ x: 0.25, y: 0.15, w: 0.5, h: 0.5 });

    for (let f = 0; f < 6; f++) {
      puppet.updateDeformation({
        elapsedSec: 1.0 + f * 0.016,
        mode: 'talk',
        intensity: 0.8,
        voiceLevel: 0.9,
        blinkAmount: 0.95,
        tiltX: 0,
        tiltY: 0,
      });
    }

    const maxDelta = puppet.deformedVerts.reduce((acc, v, idx) => {
      const r = puppet.restVerts[idx];
      return Math.max(acc, Math.hypot(v.x - r.u, v.y - r.v));
    }, 0);
    expect(maxDelta).toBeGreaterThan(0.01);
  });

  it('deforms full-body S-curve dance kinematics and converges spring physics', () => {
    stage.spring.targetX = 1.0;
    stage.triggerReaction('dance');
    expect(stage.shockwaves.length).toBe(1);

    for (let i = 0; i < 30; i++) {
      stage._stepPhysics(performance.now() + i * 16);
    }
    expect(stage.spring.x).toBeGreaterThan(0.5);
  });

  it('unlocks aura packs progressively and builds deep-linked challenge URLs', () => {
    expect(stage.isPackUnlocked('royal_crown')).toBe(false);
    stage.triggerReaction('laugh');
    stage.triggerReaction('wow');
    expect(stage.isPackUnlocked('royal_crown')).toBe(true);
    expect(stage.setPack('royal_crown')).toBe(true);
    stage.setFrame('birthday');
    stage.senderName = 'Wale';
    const url = stage._buildChallengeUrl();
    expect(url).toContain('/living-avatar?');
    expect(url).toContain('reaction=wow');
    expect(url).toContain('frame=birthday');
    expect(url).toContain('pack=royal_crown');
    expect(url).toContain('from=Wale');
  });

  it('supports all 11 OpenArt aspect ratios and encodes valid looping GIF89a blobs', async () => {
    expect(Object.keys(ASPECT_RATIOS)).toHaveLength(11);
    expect(stage.setAspectRatio('9:16')).toBe(true);
    expect(canvas.width).toBe(360);
    expect(canvas.height).toBe(640);

    const frame = new Uint8ClampedArray(4 * 4 * 4).fill(200);
    const gifBlob = encodeAnimatedGif([frame, frame], 4, 4, 10);
    expect(gifBlob.type).toBe('image/gif');
    const bytes = new Uint8Array(await gifBlob.arrayBuffer());
    const header = String.fromCharCode(...bytes.slice(0, 6));
    expect(header).toBe('GIF89a');
    expect(bytes[bytes.length - 1]).toBe(0x3b);
  });
});
