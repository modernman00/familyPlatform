<!DOCTYPE html>
<html lang="en">
  
{{-- head --}}
@include('layouts.head')


<body class="w3-theme-l5" id="@yield('data-page-id')">

<!-- Navbar -->
@include('layouts.nav')

@yield('content')





<!-- Footer -->
@include('layouts.footer')

{{--  <script src="/index.js" type="text/javascript"></script>  --}}
<script>
// Accordion
function myFunction(id) {
  var x = document.getElementById(id);
  if (x.className.indexOf("w3-show") == -1) {
    x.className += " w3-show";
    x.previousElementSibling.className += " w3-theme-d1";
  } else { 
    x.className = x.className.replace("w3-show", "");
    x.previousElementSibling.className = 
    x.previousElementSibling.className.replace(" w3-theme-d1", "");
  }
}

// Used to toggle the menu on smaller screens when clicking on the menu button


</script>
   <script type="text/javascript" src="/public/manifest.js" defer></script>
  <script type="text/javascript" src="/public/vendor.js" defer></script>
  <script type="text/javascript" src="/public/index.js" defer></script>
      
<script>


    // Utility function to convert VAPID public key to Uint8Array
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
    navigator.serviceWorker.register('/service-worker.js').then((swReg) => {

        // Check if the user is already subscribed
        swReg.pushManager.getSubscription().then((subscription) => {
            if (subscription === null) {
                // Request user permission for notifications
                Notification.requestPermission().then((permission) => {
                    if (permission === 'granted') {
                        subscribeUser(swReg);
                    } else {
                        subscribeUser(swReg);
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
}

function subscribeUser(swReg) {
    const applicationServerKey = urlBase64ToUint8Array("{{ env('MIX_VAPID_PUBLIC_KEY') }}");  // Access public VAPID key

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



</script>
      

</body>
</html> 
