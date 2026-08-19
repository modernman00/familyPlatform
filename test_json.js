const axios = require('axios');
// Mocking axios response when the body is not valid JSON
let data = "<pre>foo</pre>{\"status\":\"success\",\"message\":123}";
console.log(data.message);
