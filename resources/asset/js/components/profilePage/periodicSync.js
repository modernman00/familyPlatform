// utility for periodic sync  
if ('periodicSync' in navigator) {
    (async () => {
        // Request permission for background sync
        const status = await navigator.permissions.query({ name: 'periodic-background-sync' });

        if (status.state === 'granted') {
            console.log('Background sync permission granted');
        } else {
            console.log('Background sync permission denied');
        }

        try {
            await navigator.periodicSync.register('content-sync', {
                minInterval: 24 * 60 * 60 * 1000, // Minimum interval in milliseconds (e.g., 1 day)
            });
            console.log('Periodic Sync registered');
        } catch (error) {
            console.error('Periodic Sync registration failed:', error);
        }
    })(); // Immediately invoke the async function
}