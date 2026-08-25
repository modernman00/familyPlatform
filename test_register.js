const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: 'new' });
    const page = await browser.newPage();
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('pageerror', err => console.log('PAGE ERROR:', err.toString()));
    
    await page.goto('http://localhost:8000/register', { waitUntil: 'networkidle0' });
    
    // Fill the form
    await page.type('#firstName', 'Test');
    await page.type('#lastName', 'User');
    await page.type('#famCode', 'CODE123');
    await page.type('#email', 'test@example.com');
    await page.select('#country', 'United Kingdom');
    await page.type('#mobile', '1234567890');
    await page.type('#password', 'password123');
    await page.type('#confirm_password', 'password123');
    await page.click('#checkbox');
    // await page.click('#ai_consent'); // skip this to see if it fails without it? Wait, ai_consent is optional now.
    
    console.log('Clicking submit...');
    await page.click('#btnSubmit');
    
    // Wait a bit to see what happens
    await new Promise(r => setTimeout(r, 2000));
    
    // Check if there are any visible errors
    const errors = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('.help.is-danger, .help.error'))
            .map(el => el.id + ': ' + el.innerText)
            .filter(t => t.split(': ')[1].trim() !== '');
    });
    console.log('Visible form errors:', errors);
    
    await browser.close();
})();
