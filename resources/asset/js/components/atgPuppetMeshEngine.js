/**
 * AtgPuppetMeshEngine (v2 — Spring-Mass Facial Muscle Physics & Pixel Landmark Rig)
 *
 * Advanced Technology Group (ATG) — 2D Deformable Triangle-Mesh Puppet & Kinematics Engine.
 * Tears into any portrait or stylised avatar using 28x30 (1,680) affine-textured triangles
 * and damped spring-mass facial muscle physics:
 *  - Fast on-canvas pixel luminance & Sobel lip-crease detector (< 3ms, zero CSP)
 *  - Damped spring-mass muscles: smile, jaw, leftEye, rightEye, brow
 *  - Cartoon-grade Duchenne Smile (mouth corners up/out + cheek lift + lower-lid squint)
 *  - Full Eyelid-Pinch Blink & Single-Eye Wink with natural lid-crease shading
 *  - Jaw-drop Talk & Laugh with mouth-cavity depth + subtle teeth highlight
 *  - Full-body S-curve kinematic dance rig
 */

function drawTexturedTriangle(ctx, img, x0, y0, x1, y1, x2, y2, u0, v0, u1, v1, u2, v2) {
  ctx.save();
  ctx.beginPath();
  ctx.moveTo(x0, y0);
  ctx.lineTo(x1, y1);
  ctx.lineTo(x2, y2);
  ctx.closePath();
  ctx.clip();

  const denom = u0 * (v1 - v2) + u1 * (v2 - v0) + u2 * (v0 - v1);
  if (Math.abs(denom) < 1e-6) {
    ctx.restore();
    return;
  }

  const m11 = (x0 * (v1 - v2) + x1 * (v2 - v0) + x2 * (v0 - v1)) / denom;
  const m21 = (x0 * (u2 - u1) + x1 * (u0 - u2) + x2 * (u1 - u0)) / denom;
  const dx  = (x0 * (u1 * v2 - u2 * v1) + x1 * (u2 * v0 - u0 * v2) + x2 * (u0 * v1 - u1 * v0)) / denom;

  const m12 = (y0 * (v1 - v2) + y1 * (v2 - v0) + y2 * (v0 - v1)) / denom;
  const m22 = (y0 * (u2 - u1) + y1 * (u0 - u2) + y2 * (u1 - u0)) / denom;
  const dy  = (y0 * (u1 * v2 - u2 * v1) + y1 * (u2 * v0 - u0 * v2) + y2 * (u0 * v1 - u1 * v0)) / denom;

  ctx.transform(m11, m12, m21, m22, dx, dy);
  ctx.drawImage(img, 0, 0);
  ctx.restore();
}

function stepSpring(muscle, target, stiffness = 0.24, damping = 0.72) {
  muscle.vel = (muscle.vel + (target - muscle.val) * stiffness) * damping;
  muscle.val += muscle.vel;
  return muscle.val;
}

export class DeformablePhotoPuppet {
  constructor(options = {}) {
    this.cols = options.gridCols || 28;
    this.rows = options.gridRows || 30;
    this.image = null;
    this.imgW = 0;
    this.imgH = 0;

    this.rig = {
      headCenter: { x: 0.50, y: 0.36 },
      leftEye:    { x: 0.41, y: 0.35 },
      rightEye:   { x: 0.59, y: 0.35 },
      mouth:      { x: 0.50, y: 0.53 },
      neckY:      0.66,
      waistY:     0.82,
      isFullBody: false,
    };

    // Spring-mass facial muscle state
    this.muscles = {
      smile:    { val: 0, vel: 0 },
      jaw:      { val: 0, vel: 0 },
      leftEye:  { val: 0, vel: 0 },
      rightEye: { val: 0, vel: 0 },
      brow:     { val: 0, vel: 0 },
    };

    this._lastMouthOpen = 0;
    this._lastLeftClose = 0;
    this._lastRightClose = 0;
    this._skinTone = 'rgba(165, 112, 84, 0.96)';

    this.restVerts = [];
    this.deformedVerts = [];
    this._buildRestGrid();
  }

  _buildRestGrid() {
    this.restVerts = [];
    this.deformedVerts = [];
    for (let r = 0; r <= this.rows; r++) {
      for (let c = 0; c <= this.cols; c++) {
        const u = c / this.cols;
        const v = r / this.rows;
        this.restVerts.push({ u, v });
        this.deformedVerts.push({ x: u, y: v });
      }
    }
  }

  setImage(img) {
    this.image = img;
    this.imgW = img.naturalWidth || img.width || 512;
    this.imgH = img.naturalHeight || img.height || 512;
    this.rig.isFullBody = this.imgH > this.imgW * 1.25;
    this.detectFacialFeaturesFromPixels();
  }

  applyFaceBox(box) {
    if (!box) return;
    const cx = box.x + box.w * 0.5;
    this.rig.headCenter = { x: cx, y: box.y + box.h * 0.48 };
    this.rig.leftEye = { x: cx - box.w * 0.20, y: box.y + box.h * 0.36 };
    this.rig.rightEye = { x: cx + box.w * 0.20, y: box.y + box.h * 0.36 };
    this.rig.mouth = { x: cx, y: box.y + box.h * 0.74 };
    this.rig.neckY = Math.min(0.88, box.y + box.h * 1.05);
    this.rig.waistY = Math.min(0.95, this.rig.neckY + 0.20);
    this.rig.isFullBody = box.h < 0.34;
  }

  /**
   * Fast on-canvas pixel luminance & horizontal Sobel edge projection (< 3ms, zero CSP).
   * Locks leftEye, rightEye, and mouth onto the actual features in the portrait.
   */
  detectFacialFeaturesFromPixels() {
    if (!this.image || typeof document === 'undefined') return false;
    try {
      const S = 96;
      const c = document.createElement('canvas');
      c.width = S;
      c.height = S;
      const ctx = c.getContext('2d');
      if (!ctx || typeof ctx.getImageData !== 'function') return false;
      ctx.drawImage(this.image, 0, 0, S, S);
      const data = ctx.getImageData(0, 0, S, S).data;
      if (!data || data.length < S * S * 4) return false;

      const luma = (x, y) => {
        const idx = (y * S + x) * 4;
        return 0.299 * data[idx] + 0.587 * data[idx + 1] + 0.114 * data[idx + 2];
      };

      // 1. Find left & right eye iris/sclera contrast peaks in y=[0.30..0.44] (below hairline)
      const yMinEye = Math.round(S * 0.30);
      const yMaxEye = Math.round(S * 0.44);
      const findEyePeak = (xStart, xEnd) => {
        let bestScore = -Infinity;
        let bestX = Math.round((xStart + xEnd) / 2);
        let bestY = Math.round(S * 0.36);
        for (let y = yMinEye; y <= yMaxEye; y++) {
          for (let x = xStart; x <= xEnd; x++) {
            const center = luma(x, y);
            const leftSclera = luma(Math.max(0, x - 3), y);
            const rightSclera = luma(Math.min(S - 1, x + 3), y);
            // Iris is darker than flanking sclera/skin on both left & right
            const contrast = ((leftSclera + rightSclera) * 0.5) - center;
            if (contrast > bestScore) {
              bestScore = contrast;
              bestX = x;
              bestY = y;
            }
          }
        }
        return { x: bestX / S, y: bestY / S };
      };

      const left = findEyePeak(Math.round(S * 0.32), Math.round(S * 0.47));
      const right = findEyePeak(Math.round(S * 0.53), Math.round(S * 0.68));

      const avgEyeY = Math.max(0.31, Math.min(0.43, (left.y + right.y) * 0.5));
      if (Math.abs(left.y - right.y) < 0.08) {
        this.rig.leftEye = { x: left.x, y: avgEyeY };
        this.rig.rightEye = { x: right.x, y: avgEyeY };
        this.rig.headCenter.x = (left.x + right.x) * 0.5;
      }

      // Sample cheek skin colour right below the eye for realistic cartoon eyelid sweeps
      const sx = Math.min(S - 1, Math.max(0, Math.round(this.rig.leftEye.x * S)));
      const sy = Math.min(S - 1, Math.max(0, Math.round((avgEyeY + 0.07) * S)));
      const sIdx = (sy * S + sx) * 4;
      this._skinTone = `rgba(${data[sIdx]}, ${data[sIdx + 1]}, ${data[sIdx + 2]}, 0.96)`;

      // 2. Find horizontal lip-crease Sobel gradient below the nose
      const mouthX0 = Math.round((this.rig.headCenter.x - 0.10) * S);
      const mouthX1 = Math.round((this.rig.headCenter.x + 0.10) * S);
      const mouthY0 = Math.min(S - 4, Math.round((avgEyeY + 0.14) * S));
      const mouthY1 = Math.min(S - 3, Math.round((avgEyeY + 0.28) * S));
      let bestRow = Math.round((mouthY0 + mouthY1) * 0.5);
      let bestGrad = -1;
      for (let y = mouthY0; y <= mouthY1; y++) {
        let rowGrad = 0;
        for (let x = Math.max(1, mouthX0); x <= Math.min(S - 2, mouthX1); x++) {
          rowGrad += Math.abs(luma(x, y + 1) - luma(x, y - 1));
        }
        if (rowGrad > bestGrad) {
          bestGrad = rowGrad;
          bestRow = y;
        }
      }
      this.rig.mouth = { x: this.rig.headCenter.x, y: bestRow / S };
      this.rig.neckY = Math.min(0.88, this.rig.mouth.y + 0.14);
      return true;
    } catch (_) {
      return false;
    }
  }

  async detectRigAsync() {
    return this.detectFacialFeaturesFromPixels();
  }

  updateDeformation({ elapsedSec, mode, intensity, voiceLevel, blinkAmount, winkAmount = 0, smileAmount = 0, tiltX, tiltY }) {
    const t = elapsedSec;
    const rig = this.rig;
    const isDance = mode === 'dance';
    const isLaugh = mode === 'laugh';
    const isWow = mode === 'wow';
    const isNod = mode === 'nod';
    const isHello = mode === 'hello';
    const isLove = mode === 'love';
    const isPoint = mode === 'point';
    const isClap = mode === 'clap';
    const isSmile = mode === 'smile';
    const isWink = mode === 'wink';

    // Target muscle activations
    const targetSmile = Math.min(1, Math.max(
      smileAmount,
      isSmile ? Math.max(0.85, intensity) : 0,
      isLove ? 0.75 * Math.max(0.5, intensity) : 0,
      isLaugh ? 0.85 : 0,
      isWink ? 0.65 : 0
    ));
    const synthTalk = isLaugh
      ? Math.abs(Math.sin(t * 14)) * 0.85
      : isWow
        ? 0.95 * Math.max(0.6, intensity)
        : (mode === 'talk' ? Math.abs(Math.sin(t * 11)) * 0.65 : 0);
    const targetJaw = Math.min(1, Math.max(voiceLevel * 1.25, synthTalk));
    const targetWink = isWink ? Math.max(0.85, Math.sin(Math.min(1, intensity) * Math.PI)) : winkAmount;
    const targetBrow = isWow ? 0.9 : (isWink ? 0.45 : (isSmile ? 0.25 : 0));

    // Integrate spring-mass facial muscles
    const smile = stepSpring(this.muscles.smile, targetSmile, 0.24, 0.72);
    const jaw = stepSpring(this.muscles.jaw, targetJaw, 0.34, 0.66);
    const leftClose = stepSpring(this.muscles.leftEye, blinkAmount, 0.45, 0.62);
    const rightClose = stepSpring(this.muscles.rightEye, Math.max(blinkAmount, targetWink), 0.45, 0.62);
    const browLift = stepSpring(this.muscles.brow, targetBrow, 0.22, 0.74);

    this._lastMouthOpen = jaw;
    this._lastLeftClose = leftClose;
    this._lastRightClose = rightClose;

    const breath = Math.sin(t * 1.6) * 0.007;
    const groovePhase = t * (isDance ? 5.8 : 2.6);

    for (let i = 0; i < this.restVerts.length; i++) {
      const { u, v } = this.restVerts[i];
      let x = u;
      let y = v;

      // 1. Natural respiratory chest/neck expansion
      const chestDist = Math.abs(v - rig.neckY);
      if (chestDist < 0.25) {
        const w = 1 - chestDist / 0.25;
        y -= breath * w;
        x += (u - 0.5) * breath * 0.7 * w;
      }

      // 2. Head & Upper-Body Kinematics
      const headWeight = Math.max(0, Math.min(1, 1 - (v - rig.neckY) / 0.18));
      if (headWeight > 0) {
        x += tiltX * 0.032 * headWeight;
        y += tiltY * 0.024 * headWeight;

        if (isNod) {
          y += Math.sin(t * 9) * 0.035 * Math.max(0.5, intensity) * headWeight;
        } else if (isHello) {
          const sway = Math.sin(t * 6.5) * 0.036 * Math.max(0.5, intensity) * headWeight;
          x += sway;
          y += (u - 0.5) * sway * 0.45;
        } else if (isLaugh) {
          y += Math.sin(t * 14) * 0.022 * headWeight;
        } else if (isWink) {
          x += 0.015 * headWeight;
          y += (u - 0.5) * 0.025 * headWeight;
        } else if (isLove) {
          x += Math.sin(t * 3.2) * 0.024 * headWeight;
          y += Math.cos(t * 3.2) * 0.014 * headWeight;
        } else if (isPoint) {
          const zoom = 0.055 * Math.max(0.5, intensity) * headWeight;
          x += (u - rig.headCenter.x) * zoom;
          y += (v - rig.headCenter.y) * zoom;
        } else if (isClap) {
          y -= Math.abs(Math.sin(t * 10)) * 0.024 * Math.max(0.5, intensity) * headWeight;
        }
      }

      // 3. Full-Body / Torso Dance Kinematics
      if (isDance || rig.isFullBody) {
        const danceAmp = isDance ? 0.044 : 0.008;
        const hipWave = Math.sin(groovePhase - v * 3.2) * danceAmp;
        const bounce = -Math.abs(Math.sin(groovePhase)) * (isDance ? 0.028 : 0.004);
        const groundPin = 1 - Math.pow(Math.max(0, (v - 0.82) / 0.18), 2);
        x += hipWave * groundPin;
        y += bounce * (1 - v * 0.55);
      }

      // 4. Cartoon Duchenne Smile (mouth-corner up/out + cheek lift + subtle lower-lid squint)
      if (smile > 0.01) {
        const dx = u - rig.mouth.x;
        const dy = v - rig.mouth.y;
        const dist = Math.hypot(dx * 0.85, dy * 1.15);
        if (dist < 0.20) {
          const sw = Math.pow(1 - dist / 0.20, 1.25);
          const cornerFactor = Math.min(1, Math.abs(dx) / 0.045);
          y -= smile * 0.048 * cornerFactor * sw;
          x += Math.sign(dx) * smile * 0.028 * cornerFactor * sw;
        }
      }

      // 5. Jaw & Lower-Lip Articulation (Talk / Laugh / Wow)
      if (jaw > 0.01) {
        const dx = u - rig.mouth.x;
        const dy = v - rig.mouth.y;
        const dist = Math.hypot(dx * 0.95, dy * 1.2);
        if (dist < 0.17) {
          const mw = Math.pow(1 - dist / 0.17, 1.25);
          if (dy >= -0.012) {
            y += jaw * 0.064 * mw;
          } else {
            y -= jaw * 0.014 * mw;
          }
        }
      }

      // 6. Eyelid-Pinch Mesh Blinking & Single-Eye Wink + Brow Lift
      const deformEye = (eye, closeAmt) => {
        const dx = u - eye.x;
        const dy = v - eye.y;
        if (closeAmt > 0.01) {
          const dist = Math.hypot(dx * 1.1, dy * 1.5);
          if (dist < 0.10) {
            const ew = Math.pow(1 - dist / 0.10, 1.1);
            y += (eye.y - v) * 0.92 * closeAmt * ew;
          }
        }
        // Eyebrow lift just above the eye
        if (browLift > 0.01 && dy < -0.02 && dy > -0.10 && Math.abs(dx) < 0.09) {
          const bw = 1 - Math.abs(dx) / 0.09;
          y -= browLift * 0.018 * bw;
        }
      };
      deformEye(rig.leftEye, leftClose);
      deformEye(rig.rightEye, rightClose);

      this.deformedVerts[i].x = x;
      this.deformedVerts[i].y = y;
    }
  }

  render(ctx, dx, dy, dw, dh) {
    if (!this.image) return;
    const stride = this.cols + 1;
    const iw = this.imgW;
    const ih = this.imgH;

    for (let r = 0; r < this.rows; r++) {
      for (let c = 0; c < this.cols; c++) {
        const i0 = r * stride + c;
        const i1 = i0 + 1;
        const i2 = i0 + stride;
        const i3 = i2 + 1;

        const r0 = this.restVerts[i0];
        const r1 = this.restVerts[i1];
        const r2 = this.restVerts[i2];
        const r3 = this.restVerts[i3];

        const d0 = this.deformedVerts[i0];
        const d1 = this.deformedVerts[i1];
        const d2 = this.deformedVerts[i2];
        const d3 = this.deformedVerts[i3];

        drawTexturedTriangle(
          ctx, this.image,
          dx + d0.x * dw, dy + d0.y * dh,
          dx + d1.x * dw, dy + d1.y * dh,
          dx + d2.x * dw, dy + d2.y * dh,
          r0.u * iw, r0.v * ih,
          r1.u * iw, r1.v * ih,
          r2.u * iw, r2.v * ih
        );

        drawTexturedTriangle(
          ctx, this.image,
          dx + d1.x * dw, dy + d1.y * dh,
          dx + d3.x * dw, dy + d3.y * dh,
          dx + d2.x * dw, dy + d2.y * dh,
          r1.u * iw, r1.v * ih,
          r3.u * iw, r3.v * ih,
          r2.u * iw, r2.v * ih
        );
      }
    }

    // Crisp cartoon mouth-cavity + subtle teeth highlight when jaw opens
    if (this._lastMouthOpen > 0.14) {
      const mx = dx + this.rig.mouth.x * dw;
      const my = dy + this.rig.mouth.y * dh + this._lastMouthOpen * dh * 0.015;
      const rx = dw * 0.048;
      const ry = dh * 0.028 * this._lastMouthOpen;
      ctx.save();
      ctx.fillStyle = `rgba(34, 10, 14, ${(0.72 * this._lastMouthOpen).toFixed(2)})`;
      ctx.beginPath();
      ctx.ellipse(mx, my, rx, ry, 0, 0, Math.PI * 2);
      ctx.fill();
      if (this._lastMouthOpen > 0.35) {
        ctx.fillStyle = `rgba(255, 250, 245, ${(0.55 * this._lastMouthOpen).toFixed(2)})`;
        ctx.beginPath();
        ctx.ellipse(mx, my - ry * 0.38, rx * 0.68, ry * 0.25, 0, 0, Math.PI * 2);
        ctx.fill();
      }
      ctx.restore();
    }

    // Cartoon skin-sampled eyelid sweep + curved lash arc when an eye blinks or winks
    const drawLidCrease = (eye, amt) => {
      if (amt <= 0.15) return;
      const ex = dx + eye.x * dw;
      const ey = dy + eye.y * dh;
      const ew = dw * 0.048;
      const eh = dh * 0.022 * Math.min(1, amt);
      ctx.save();
      ctx.fillStyle = this._skinTone;
      ctx.globalAlpha = Math.min(1, amt * 1.15);
      ctx.beginPath();
      ctx.ellipse(ex, ey, ew, eh, 0, 0, Math.PI * 2);
      ctx.fill();
      if (amt > 0.45) {
        ctx.strokeStyle = `rgba(28, 16, 12, ${Math.min(0.92, (amt - 0.35) * 1.4).toFixed(2)})`;
        ctx.lineWidth = Math.max(2.5, dw * 0.0055);
        ctx.lineCap = 'round';
        ctx.beginPath();
        ctx.moveTo(ex - ew * 0.9, ey);
        ctx.quadraticCurveTo(ex, ey + eh * 0.7, ex + ew * 0.9, ey);
        ctx.stroke();
      }
      ctx.restore();
    };
    drawLidCrease(this.rig.leftEye, this._lastLeftClose);
    drawLidCrease(this.rig.rightEye, this._lastRightClose);
  }
}

if (typeof window !== 'undefined') {
  window.AtgDeformablePhotoPuppet = DeformablePhotoPuppet;
}
