import axios from 'axios';
import Swal from 'sweetalert2';
import { id, showError, qSel, msgException, log } from '@shared';
import { deleteNotification, getCsrfToken } from '../global.js';
import { addToNotificationTab, increaseNotificationCount } from '../navbar';
import { friendRequestCard } from '../profilePage/htmlFolder/friendRequestCard';

// Attach a click event listener to the document
/**
 * Attach a click event listener to the document. When a button with the id `addFamily<userId>` is clicked, send a family request to the user identified by the userId and update the button's HTML and disable it.
 it returns the notification details for the approvers tab
 * 
 * @param {MouseEvent} e - The event object.
 */
document.onclick = async (e) => {
  try {
    // Get the target element's ID (defensively checking closest button or element with ID)
    const btn = e.target.closest('button') || e.target.closest('[id]');
    const targetId = btn ? btn.id : (e.target.id || '');
    if (!targetId) return;

    // Check if the ID includes 'addFamily'
    if (targetId.includes('addFamily')) {
      // Extract the user ID from the target ID
      const userId = targetId.replace('addFamily', '');

      // Fetch approver details for the user
      const approverDetails = await fetchApproverData(userId);

      const familyRequestData = {
        approver: approverDetails,
        emailPath: 'msg/request_premium',
      };

      // Send the family request data to the server for processing which returns the notification details for the approvers tab
      const result = await sendFamilyRequest(familyRequestData);

      if (result.data.status === 'success' && result.data.message === 'Request sent') {
        // Update the button's HTML and disable it
        updateButton(targetId, 'Request Sent');
      } else if (result.data.status === 'error' && result.data.message === 'Request already pending') {
        // Update the button's HTML and disable it
        updateButton(targetId, 'Request Pending');
      } else {
        // Update the button's HTML and disable it
        updateButton(targetId, 'Request Failed');
      }        


    } else if (targetId.includes('removeProfile')) {
      // Extract the user ID from the target ID
      const userId = targetId.replace('removeProfile', '');
      const url = `/allMembers/removeProfile/${userId}`;

      // Confirm with SweetAlert warning modal
      const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'You will no longer see this profile and associated posts. Are you sure you want to remove this connection?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, remove connection!'
      });

      if (result.isConfirmed) {
        const memberCard = qSel(`.member_profile_${userId}`) || id(userId) || (btn ? btn.closest('.member-card') : null);

        try {
          const csrfToken = getCsrfToken();
          const response = await axios.delete(url, {
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'X-XSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
            },
            params: {
              token: csrfToken
            },
            data: {
              token: csrfToken
            }
          });

          if (response.data && (response.data.status === 'success' || response.data.message === 'success')) {
            if (memberCard) {
              memberCard.remove();
            }

            // Update member count badge
            const container = id('allMembers');
            if (container) {
              const remaining = container.querySelectorAll('.member-card').length;
              const countStr = remaining.toLocaleString();
              const memberCountBadge = id('memberCount');
              const memberCountDisplay = id('memberCountDisplay');
              if (memberCountBadge) memberCountBadge.textContent = countStr;
              if (memberCountDisplay) memberCountDisplay.textContent = countStr;

              if (remaining === 0) {
                container.innerHTML = `
                  <div class="col-12 text-center py-5 bg-white rounded-4 border w-100" style="grid-column: 1 / -1; border-radius: var(--stitch-radius-lg);">
                      <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; font-size: 1.8rem; background: var(--stitch-primary-container); color: var(--stitch-primary); border-radius: 50%;">
                          <i class="bi bi-people"></i>
                      </div>
                      <h5 class="fw-bold text-dark mb-1">No Members in View</h5>
                      <p class="text-muted small mb-3" style="max-width: 440px; margin: 0 auto;">
                          There are no matching relatives in this directory category yet. Invite or connect with your family members to build your network.
                      </p>
                      <a href="/familyStudio" class="btn btn-primary btn-sm fw-bold px-4 py-2" style="border-radius: var(--stitch-radius-pill);">
                          <i class="bi bi-plus-circle-fill me-1"></i> Open Family Studio
                      </a>
                  </div>
                `;
              }
            }

            Swal.fire({
              title: 'Removed!',
              text: 'Member connection has been removed successfully.',
              icon: 'success',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            const errText = response.data?.message || 'Error deleting profile';
            msgException(errText);
            Swal.fire('Error', errText, 'error');
          }
        } catch (err) {
          showError(err);
          const errText = err.response?.data?.message || err.message || 'Error deleting profile';
          Swal.fire('Error', errText, 'error');
        }
      }
    } else if (targetId.includes('seeProfile')) {

      // Extract the user ID from the target ID
      const userId = targetId.replace('seeProfile', '');

      // redirect to 'allMembers/setProfile/'+userId
      window.location.href = `/allMembers/seeProfile/${userId}`;

    } else if (targetId.includes('familyTree')) {

      // Extract the user ID from the target ID
      const userId = targetId.replace('familyTree', '');

      // redirect to 'allMembers/setProfile/'+userId
      window.location.href = `/organogram/${userId}`;
    } else if (targetId.includes('nudgeMigrate')) {
      const targetUserId = btn.getAttribute('data-target-id') || targetId.replace('nudgeMigrate', '');
      const targetName = btn.getAttribute('data-target-name') || 'this relative';
      const targetCode = btn.getAttribute('data-target-code') || '';

      const { value: note, isConfirmed } = await Swal.fire({
        title: `Nudge ${targetName}?`,
        html: `
          <p class="text-muted small mb-3">
            They are currently registered under family code <strong>#${targetCode}</strong>.
            Invite them to migrate to your family network so you can share your family tree, events, and memories.
          </p>
          <div class="form-group text-start">
            <label for="swal-nudge-note" class="form-label small fw-semibold text-secondary">Optional Note:</label>
            <textarea id="swal-nudge-note" class="form-control form-control-sm" rows="2" placeholder="e.g. Hi! I saw your profile in the directory. Let's unite our family trees!"></textarea>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-send-check me-1"></i> Send Nudge',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
          const noteEl = document.getElementById('swal-nudge-note');
          return noteEl ? noteEl.value.trim() : '';
        }
      });

      if (isConfirmed) {
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

        try {
          const csrfToken = getCsrfToken();
          const response = await axios.post('/api/family-migration/nudge', {
            target_user_id: targetUserId,
            note: note || null
          }, {
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'X-XSRF-TOKEN': csrfToken,
              'Content-Type': 'application/json'
            }
          });

          if (response.data && response.data.success) {
            btn.innerHTML = '<i class="bi bi-check2-circle text-success me-1"></i> Nudge Sent';
            btn.classList.remove('btn-stitch-tonal');
            btn.classList.add('btn-outline-success');
            Swal.fire({
              icon: 'success',
              title: 'Nudge Sent!',
              text: `We have sent a migration invitation to ${targetName}. They will receive a notification to review and accept.`,
              confirmButtonColor: '#10b981'
            });
          } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
            Swal.fire({
              icon: 'warning',
              title: 'Notice',
              text: response.data?.message || response.data?.error || 'Unable to send nudge.',
              confirmButtonColor: '#f59e0b'
            });
          }
        } catch (err) {
          btn.disabled = false;
          btn.innerHTML = originalText;
          const errMsg = err.response?.data?.error || err.response?.data?.message || 'A network error occurred. Please try again.';
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errMsg,
            confirmButtonColor: '#ef4444'
          });
        }
      }
    } else if (targetId.includes('requestMigrate')) {
      const targetUserId = btn.getAttribute('data-target-id') || targetId.replace('requestMigrate', '');
      const targetName = btn.getAttribute('data-target-name') || 'this family';
      const targetCode = btn.getAttribute('data-target-code') || '';

      const { value: note, isConfirmed } = await Swal.fire({
        title: `Join Family #${targetCode}?`,
        html: `
          <p class="text-muted small mb-3">
            Request to migrate your account into the <strong>#${targetCode}</strong> family network (${targetName}).
            <br><br>
            <span class="badge bg-primary text-white px-2 py-1"><i class="bi bi-info-circle me-1"></i> Subject to Family Approval</span>
            <br>
            <small class="text-muted mt-2 d-block">Existing members of family #${targetCode} will be notified to approve your transfer.</small>
          </p>
          <div class="form-group text-start">
            <label for="swal-req-note" class="form-label small fw-semibold text-secondary">Optional Note / Kinship explanation:</label>
            <textarea id="swal-req-note" class="form-control form-control-sm" rows="2" placeholder="e.g. Hello, I am relative to ${targetName}, registering to connect with our family!"></textarea>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-box-arrow-in-right me-1"></i> Submit Request',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
          const noteEl = document.getElementById('swal-req-note');
          return noteEl ? noteEl.value.trim() : '';
        }
      });

      if (isConfirmed) {
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

        try {
          const csrfToken = getCsrfToken();
          const response = await axios.post('/api/family-migration/request', {
            family_code: targetCode,
            target_user_id: targetUserId,
            note: note || null
          }, {
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'X-XSRF-TOKEN': csrfToken,
              'Content-Type': 'application/json'
            }
          });

          if (response.data && response.data.success) {
            btn.innerHTML = '<i class="bi bi-hourglass-split text-primary me-1"></i> Request Pending';
            btn.classList.remove('btn-stitch-tonal');
            btn.classList.add('btn-outline-primary');
            Swal.fire({
              icon: 'success',
              title: 'Request Submitted!',
              text: `Your migration request to family #${targetCode} has been sent! You will be notified as soon as a family member approves.`,
              confirmButtonColor: '#2563eb'
            });
          } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
            Swal.fire({
              icon: 'warning',
              title: 'Notice',
              text: response.data?.message || response.data?.error || 'Unable to submit request.',
              confirmButtonColor: '#f59e0b'
            });
          }
        } catch (err) {
          btn.disabled = false;
          btn.innerHTML = originalText;
          const errMsg = err.response?.data?.error || err.response?.data?.message || 'A network error occurred. Please try again.';
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errMsg,
            confirmButtonColor: '#ef4444'
          });
        }
      }
    }
    // else if (targetId.includes('deleteNotification')) {
    //   // Call the deleteNotification function to remove the notification
    //   deleteNotification(targetId);
    // }
    // Extract the user ID from the target ID
  } catch (error) {
    // Handle any errors that occur during execution
    showError(error);
  }
};

// Function to fetch approver data based on user ID
async function fetchApproverData(userId) {
  try {
    const result = await axios.get(
      `/members/familyRequestMgt/getApprover?id=${userId}`,
    );
    const approverDetails = {
      approverFirstName: result.data.message.firstName,
      approverLastName: result.data.message.lastName,
      approverEmail: result.data.message.email,
      approverId: result.data.message.id,
      approverCode: result.data.message.famCode,
    };
    return approverDetails;
  } catch (error) {
    console.error(error);
    throw error;
  }
}


// Function to send family request data to the server
function sendFamilyRequest(data) {
  try {
    return axios.post('/members/familyRequestMgt', data);
  } catch (error) {
    showError(error);
  }
}

// Function to update the button's HTML and disable it
function updateButton(targetId, newHTML) {
  const theTargetId = id(targetId);
  theTargetId.innerHTML = newHTML;
  theTargetId.disabled = true;
}

// ADD THE NEW EVENT TO THE NOTIFICATION TAB
