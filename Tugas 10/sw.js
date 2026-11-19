const C='sigap-v1';self.addEventListener('install',e=>{e.waitUntil(caches.open(C).then(c=>c.addAll(['./','icon.svg'])));self.skipWaiting()});
self.addEventListener('fetch',e=>{if(e.request.method!=='GET'||e.request.url.includes('api.php'))return;e.respondWith(fetch(e.request).catch(()=>caches.match(e.request)))});
