/**
 * Creates a local database in the browser
 * Stores reactions using a unique comment number
 * Lets you save and retrieve reactions without needing a server 
 */


/**
 * This opens (or creates) a database called reactions.
 * The 1 is the version number — useful when upgrading the database structure later.
 */
const dbPromise = indexedDB.open('reactions', 1);



/**
 * This runs only when the database is new or its version changes.
 * It creates a store (like a table) called 'reactions'.
 * Each item in this store will use commentNo as its unique ID (called a keyPath).
 */
dbPromise.onupgradeneeded = e => {
  const db = e.target.result;
  db.createObjectStore('reactions', { keyPath: 'commentNo' });
};



/**
 * This function retrieves a reaction from the database using its commentNo
 * It opens a read-only transaction and fetches the matching record.} commentNo 
 */
export async function cacheReaction(commentNo, data) {
  const db = (await dbPromise).result;
  const tx = db.transaction('reactions', 'readwrite');
  tx.objectStore('reactions').put({ commentNo, data });
}


