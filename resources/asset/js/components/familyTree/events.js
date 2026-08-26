import { id } from "@modernman00/shared-js-lib";

const zoomInBtn = id('zoomIn');
const zoomOutBtn = id('zoomOut');
const resetZoomBtn = id('resetZoom');
const fitScreenBtn = id('fitScreenBtn');
const recenterRootBtn = id('recenterRootBtn');
const familyTree = id('familyTree');
const treeWrapper = id('treeWrapper');

// Initial zoom scale & position
let scale = 1;
let position = { x: 0, y: 0 };
let startPosition = { x: 0, y: 0 };
let isDragging = false;

// Applies zoom and pan transformations
export function updateTransform() {
  if (!familyTree) return;
  familyTree.style.transform = `translate(${position.x}px, ${position.y}px) scale(${scale})`;
}

export function setPosition(x, y, newScale = null) {
  position.x = x;
  position.y = y;
  if (newScale !== null) scale = newScale;
  updateTransform();
}

// Center the viewport on a specific node element
export function centerOnElement(element) {
  if (!element || !treeWrapper || !familyTree) return;

  const wrapperRect = treeWrapper.getBoundingClientRect();
  const elemRect = element.getBoundingClientRect();

  // Reset scale moderately to 1 for focus
  scale = 1;
  const currentTransform = familyTree.getBoundingClientRect();

  const offsetX = (wrapperRect.width / 2) - (elemRect.left - currentTransform.left + (elemRect.width / 2));
  const offsetY = (wrapperRect.height / 3) - (elemRect.top - currentTransform.top + (elemRect.height / 2));

  position.x = offsetX;
  position.y = offsetY;
  updateTransform();

  // Add highlight pulse
  element.classList.add('highlighted');
  setTimeout(() => element.classList.remove('highlighted'), 3000);
}

// Fit tree to screen
export function fitToScreen() {
  if (!familyTree || !treeWrapper) return;
  const wrapperRect = treeWrapper.getBoundingClientRect();
  const treeRect = familyTree.scrollWidth || 1200;

  scale = Math.min(Math.max(0.4, (wrapperRect.width - 80) / treeRect), 1.2);
  position = { x: 40, y: 30 };
  updateTransform();
}

// Event Listeners for Interaction
const addEventListeners = () => {
  zoomInBtn?.addEventListener('click', () => {
    scale = Math.min(3.0, scale + 0.15);
    updateTransform();
  });

  zoomOutBtn?.addEventListener('click', () => {
    scale = Math.max(0.3, scale - 0.15);
    updateTransform();
  });

  resetZoomBtn?.addEventListener('click', () => {
    scale = 1;
    position = { x: 0, y: 0 };
    updateTransform();
  });

  fitScreenBtn?.addEventListener('click', fitToScreen);

  recenterRootBtn?.addEventListener('click', () => {
    const rootNode = document.querySelector('.tree-node[data-role="Me"]') || document.querySelector('.tree-node');
    if (rootNode) centerOnElement(rootNode);
  });

  // Mouse wheel zoom
  treeWrapper?.addEventListener('wheel', (e) => {
    e.preventDefault();
    const delta = -e.deltaY * 0.0015;
    scale = Math.min(Math.max(0.3, scale + delta), 3.0);
    updateTransform();
  }, { passive: false });

  // Mouse Dragging
  treeWrapper?.addEventListener('mousedown', (e) => {
    // Only drag if left click and not interacting with a button
    if (e.button !== 0 || e.target.closest('button') || e.target.closest('input')) return;
    isDragging = true;
    startPosition = {
      x: e.clientX - position.x,
      y: e.clientY - position.y
    };
    treeWrapper.classList.add('grabbing');
  });

  document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    position.x = e.clientX - startPosition.x;
    position.y = e.clientY - startPosition.y;
    updateTransform();
  });

  document.addEventListener('mouseup', () => {
    if (isDragging) {
      isDragging = false;
      treeWrapper?.classList.remove('grabbing');
    }
  });

  // Touch Events for Mobile
  treeWrapper?.addEventListener('touchstart', (e) => {
    if (e.touches.length === 1) {
      isDragging = true;
      startPosition = {
        x: e.touches[0].clientX - position.x,
        y: e.touches[0].clientY - position.y
      };
    }
  }, { passive: true });

  document.addEventListener('touchmove', (e) => {
    if (!isDragging || e.touches.length !== 1) return;
    position.x = e.touches[0].clientX - startPosition.x;
    position.y = e.touches[0].clientY - startPosition.y;
    updateTransform();
  }, { passive: true });

  document.addEventListener('touchend', () => {
    isDragging = false;
  });
};

export const initTree = () => {
  updateTransform();
  addEventListeners();
};