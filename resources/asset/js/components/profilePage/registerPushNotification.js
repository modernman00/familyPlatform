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

//// Check if service workers and push notifications are supported by the browser


if ('serviceWorker' in navigator && 'PushManager' in window) {
    navigator.serviceWorker.register('/service-worker.js').then((swReg) => {

        // Check if the user is already subscribed
       checkSubscription(swReg);

    }).catch((error) => {
        console.error('Service Worker registration failed', error);
    });
} 

function checkSubscription(swReg) {
    swReg.pushManager.getSubscription().then((subscription) => {
        if (subscription === null) {
            // Request permission for notifications
            requestPermission(swReg);
        } else {
            console.log('User  is already subscribed:', subscription);

            postSubscriptionToServer(subscription);
        }
    });
}

function requestPermission(swReg) {
    // This call must be made in response to a user action
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            subscribeUser (swReg);
        } else {
            console.log('Push notifications permission denied.');
        }
    });
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
        id: localStorage.getItem('requesterId'),
        endpoint: subscription.endpoint,
        keys: {
            p256dh: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))),
            auth: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth'))))
        }
    };
    
    // Send subscription to the server using axios
    axios.post('/pushNotification/subscription', subscriptionData)
        .then(response => {
            console.log('Subscription data successfully sent to the server:', response);
        })
        .catch(error => {
            console.error('Failed to send subscription data to the server:', error);
        });

}



