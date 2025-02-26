import axios from 'axios';

const VAPID_PUBLIC_KEY = process.env.MIX_VAPID_PUBLIC_KEY;


function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}


if ('serviceWorker' in navigator && 'PushManager' in window) {
    navigator.serviceWorker.register('/service-worker.js', { scope: '/' }).then((swReg) => {
       console.log('Service Worker registered with scope:', swReg.scope);
        // Check if the user is already subscribed
        swReg.pushManager.getSubscription().then((subscription) => {
            if (subscription === null) {
                // Request user permission for notifications
                Notification.requestPermission().then((permission) => {
                    if (permission === 'granted') {
                        subscribeUser(swReg);
                    } else {
                        // subscribeUser(swReg);
                        console.log('Push notifications permission denied.' );
                // Handle the case where the user denied permission

                    }
                });
            } else {
                console.log('User is already subscribed:', subscription);
                // If already subscribed, send the subscription to the server
                postSubscriptionToServer(subscription);
            }
        });
    }).catch((error) => {
        console.error('Service Worker registration failed', error);
    });
} else{
    console.error('Service Worker is not supported in this browser');
}

function subscribeUser(swReg) {
    const applicationServerKey = urlBase64ToUint8Array(VAPID_PUBLIC_KEY);  // Access public VAPID key

    swReg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
    }).then((subscription) => {
        console.log('User is subscribed:', subscription);

        // Send subscription object to the server to store it
        postSubscriptionToServer(subscription);
    }).catch((error) => {
        console.error('Failed to subscribe the user: ', error);
    });
}

function postSubscriptionToServer(subscription) {
  
    // Prepare the subscription data to be sent to the server
    const subscriptionData = {
        id: localStorage.getItem('requesterId'),  // STILL CHECK IT
        subscription: {
            endpoint: subscription.endpoint,
            keys: {
                p256dh: subscription.keys.p256dh,
                auth: subscription.keys.auth
            }
        }
    }
        // Send subscription to the server using axios
        axios.post('/pushNotification/subscription', subscriptionData)
            .then(response => {
                console.log('Subscription data successfully sent to the server:', response.data);
        })
            .catch(error => {
                console.error('Failed to send subscription data to the server:', error);
        });
    
}

// utility for periodic sync  
if ('periodicSync' in navigator) {
  navigator.periodicSync.register('content-sync', {
    minInterval: 24 * 60 * 60 * 1000, // Minimum interval in milliseconds (e.g., 1 day)
  }).then(() => {
    console.log('Periodic Sync registered');
  }).catch((error) => {
    console.error('Periodic Sync registration failed:', error);
  });
}


