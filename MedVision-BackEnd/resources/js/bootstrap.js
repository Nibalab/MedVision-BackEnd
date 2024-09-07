// Import Axios
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import Echo and Socket.IO
import Echo from 'laravel-echo';
import io from 'socket.io-client';

// This assumes your server.js is running on port 3000
window.io = io;

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ':3000'  // or specify the exact host 'http://localhost:3000'
});
