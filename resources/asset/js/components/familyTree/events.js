 import { id } from '@shared'
 import {showPersonDetails} from './showModal'

         const zoomInBtn = id('zoomIn');
         const zoomOutBtn = id('zoomOut');
         const resetZoomBtn = id('resetZoom');
         const personModal = id('personModal');
         const modalBody = id('modalBody');
         const closeModal = id('closeModal');
        const familyTree = id('familyTree');
        const treeWrapper = id('treeWrapper');
 

       // Initial zoom scale for the family tree
let scale = 1;
// Current position of the tree (used for panning)
let position = { x: 0, y: 0 };
// Starting position when dragging begins
let startPosition = { x: 0, y: 0 };
// Flag to track whether the user is currently dragging
let isDragging = false;

// Applies zoom and pan transformations to the tree container
function updateTransform() {
  familyTree.style.transform = `translate(${position.x}px, ${position.y}px) scale(${scale})`;
}

// Attaches all event listeners for zooming, dragging, and interaction
 const addEventListeners = () => {
  // Zoom in when the "+" button is clicked
  zoomInBtn.addEventListener('click', () => {
    scale += 0.1;
    updateTransform();
  });

  // Zoom out when the "−" button is clicked, but not below 0.5x
  zoomOutBtn.addEventListener('click', () => {
    scale = Math.max(0.5, scale - 0.1);
    updateTransform();
  });

  // Reset zoom and position to default
  resetZoomBtn.addEventListener('click', () => {
    scale = 1;
    position = { x: 0, y: 0 };
    updateTransform();
  });

  // Zoom using mouse wheel (scroll up/down)
  treeWrapper.addEventListener('wheel', (e) => {
    e.preventDefault(); // Prevent page scroll
    const delta = -e.deltaY * 0.01;
    scale = Math.min(Math.max(0.5, scale + delta), 3); // Clamp between 0.5 and 3
    updateTransform();
  });

  // Start dragging when mouse is pressed
  treeWrapper.addEventListener('mousedown', (e) => {
    isDragging = true;
    startPosition = {
      x: e.clientX - position.x,
      y: e.clientY - position.y
    };
    treeWrapper.style.cursor = 'grabbing';
  });

  // Update position while dragging
  document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    position.x = e.clientX - startPosition.x;
    position.y = e.clientY - startPosition.y;
    updateTransform();
  });

  // Stop dragging when mouse is released
  document.addEventListener('mouseup', () => {
    isDragging = false;
    treeWrapper.style.cursor = 'grab';
  });

  // Touch start for mobile drag
  treeWrapper.addEventListener('touchstart', (e) => {
    if (e.touches.length === 1) {
      isDragging = true;
      startPosition = {
        x: e.touches[0].clientX - position.x,
        y: e.touches[0].clientY - position.y
      };
    }
  });

  // Touch move for mobile drag
  document.addEventListener('touchmove', (e) => {
    if (!isDragging || e.touches.length !== 1) return;
    position.x = e.touches[0].clientX - startPosition.x;
    position.y = e.touches[0].clientY - startPosition.y;
    updateTransform();
  });

  // Touch end to stop dragging
  document.addEventListener('touchend', () => {
    isDragging = false;
  });

}

// Entry point: initializes the tree when the page loads
export const initTree =() => {
  updateTransform();     // Apply initial zoom and position
  addEventListeners();   // Attach all interaction handlers
}