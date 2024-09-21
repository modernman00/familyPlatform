const webpush = require('web-push');
// generate VAPID KEYS 
const vapidKeys = webpush.generateVAPIDKeys();

// console.log('Public VAPID Key:', vapidKeys.publicKey);
// console.log('Private VAPID Key:', vapidKeys.privateKey);